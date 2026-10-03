<?php
/**
 * Base Controller
 */

require_once __DIR__ . '/Database.php';

class Controller {
    /** Walk-in verification code lifetime (seconds) — the UI says 5 minutes. */
    private const WALKIN_CODE_TTL = 300;
    /** Minimum gap between two code emails for the same address (seconds). */
    private const WALKIN_CODE_RESEND_COOLDOWN = 45;
    /** Wrong guesses tolerated before the code is invalidated. */
    private const WALKIN_CODE_MAX_ATTEMPTS = 5;

    protected $db;

    public function __construct() {
        if (date_default_timezone_get() !== 'Asia/Manila') {
            date_default_timezone_set('Asia/Manila');
        }
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->runPaymentReminders();
    }

    /**
     * Run payment due-date reminders at most once per hour.
     * Uses a DB-backed lock so it works across all sessions/users.
     */
    private function runPaymentReminders(): void {
        $lockRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'payment_reminder_last_run'");
        $lastRun = $lockRow ? $lockRow['setting_value'] : null;
        $now = serverTimestamp();

        if ($lastRun && ($now - (int)$lastRun) < 3600) {
            return; // ran less than 1 hour ago
        }

        // Acquire lock (update or insert)
        if ($lastRun === null) {
            $this->db->insert('system_settings', [
                'setting_key' => 'payment_reminder_last_run',
                'setting_value' => (string)$now,
            ]);
        } else {
            $this->db->update('system_settings', [
                'setting_value' => (string)$now,
            ], "setting_key = 'payment_reminder_last_run'");
        }

        require_once __DIR__ . '/Services/PaymentReminderService.php';
        $reminder = new PaymentReminderService();
        $reminder->run();

        $this->processMonthlyPayments();

        $this->cleanupExpiredData();
        $this->expireOldReservations();
        $this->reconcileRoomStatuses();
    }

    private function cleanupExpiredData(): void {
        $this->db->query("DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $this->db->query("DELETE FROM password_resets WHERE expires_at < NOW()");
    }

    private function expireOldReservations(): void {
        $expiryRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'reservation_expiry_days'");
        $expiryDays = $expiryRow ? max(1, (int)$expiryRow['setting_value']) : 5;

        $cutoff = serverNow()->modify("-{$expiryDays} days")->format('Y-m-d');

        $staleReservations = $this->db->fetchAll(
            "SELECT r.id, r.student_id, s.first_name, s.last_name, s.user_id, rm.room_number, rm.room_name
             FROM reservations r
             JOIN students s ON r.student_id = s.id
             JOIN rooms rm ON r.room_id = rm.id
             WHERE r.status = 'pending' AND r.created_at < ?",
            [$cutoff . ' 23:59:59']
        );

        foreach ($staleReservations as $res) {
            $this->db->update('reservations', ['status' => 'cancelled'], "id = ? AND status = 'pending'", [$res['id']]);
            $this->updateRoomOccupancy((int)$res['room_id']);

            $houseName = ($res['room_name'] ?? '') . ' - ' . ($res['room_number'] ?? '');
            if (!empty($res['user_id'])) {
                $this->db->insert('notifications', [
                    'user_id' => (int)$res['user_id'],
                    'title' => 'Reservation Expired',
                    'message' => "Your reservation for {$houseName} has been automatically cancelled after {$expiryDays} days without approval. Please submit a new reservation if you still wish to stay.",
                    'type' => 'reservation',
                    'reference_id' => $res['id'],
                    'reference_type' => 'reservation',
                ]);
            }
            $this->logActivity('reservation_expired', "Reservation #{$res['id']} auto-expired for {$res['first_name']} {$res['last_name']} after {$expiryDays} days");
        }
    }

    protected function view(string $view, array $data = [], string $layout = ''): void {
        if ($layout && in_array($layout, ['admin', 'manager', 'student']) && !isset($data['unreadNotifications'])) {
            $data['unreadNotifications'] = $this->db->count('notifications', "user_id = ? AND is_read = 0", [$_SESSION['user_id'] ?? 0]);
        }
        if ($layout === 'admin' && !isset($data['sidebarCounts'])) {
            $data['sidebarCounts'] = $this->getAdminSidebarCounts();
        }
        if ($layout === 'manager' && !isset($data['sidebarCounts'])) {
            $data['sidebarCounts'] = $this->getManagerSidebarCounts();
        }
        if ($layout === 'student' && !isset($data['sidebarCounts'])) {
            $data['sidebarCounts'] = $this->getStudentSidebarCounts();
        }
        if ($layout === 'student' && !isset($data['isTenant'])) {
            $studentRow = $this->db->fetch("SELECT id FROM students WHERE user_id = ?", [$_SESSION['user_id'] ?? 0]);
            $data['isTenant'] = $studentRow ? (bool)$this->db->fetch("SELECT id FROM reservations WHERE student_id = ? AND status = 'approved' LIMIT 1", [(int)$studentRow['id']]) : false;
        }
        extract($data);
        $viewPath = __DIR__ . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            die("View not found: {$view}");
        }
        if ($layout) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require __DIR__ . '/views/layouts/' . $layout . '.php';
        } else {
            require $viewPath;
        }
    }

    protected function redirect(string $url): void {
        header('Location: ' . SITE_URL . $url);
        exit;
    }

    protected function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function back(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
    }

    protected function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function sanitize(string $value): string {
        // Fully decode nested HTML entities from previously-stored values first
        // (e.g. Admin re-saving text repeatedly: &#039; -> &amp;#039; -> &amp;amp;#039;)
        // so a value is only ever encoded ONCE and never compound-encodes again.
        $decoded = trim($value);
        for ($i = 0; $i < 10; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES, 'UTF-8');
            if ($next === $decoded) break;
            $decoded = $next;
        }
        return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
    }

    protected function validateUrl(string $url): string {
        $url = trim($url);
        if (empty($url)) return '';
        if (!filter_var($url, FILTER_VALIDATE_URL)) return '';
        return $url;
    }

    /**
     * Strict whole-number validation. Rejects decimals (1., .1), letters, spaces, etc.
     * Returns the validated int on success, null on failure.
     */
    protected function validateWholeNumber($value, ?int $min = null, ?int $max = null): ?int {
        $raw = trim((string)$value);
        if ($raw === '') return null;
        if (!preg_match('/^\d+$/', $raw)) return null;
        $intVal = (int)$raw;
        if ($min !== null && $intVal < $min) return null;
        if ($max !== null && $intVal > $max) return null;
        return $intVal;
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth(): void {
        if (!$this->isLoggedIn()) {
            $_SESSION['flash_error'] = 'Please login to continue.';
            $this->redirect('/login');
        }
    }

    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        if (!in_array($_SESSION['user_role'], $roles)) {
            $_SESSION['flash_error'] = 'You do not have permission to access this page.';
            $this->redirect('/');
        }
    }

    protected function generateCsrfToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    protected function validateCsrf(): bool {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
    }

    protected function requireCsrf(): void {
        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }

    protected function csrfField(): string {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $this->generateCsrfToken() . '">';
    }

    protected function csrfToken(): string {
        return $this->generateCsrfToken();
    }

    protected function flash(string $type, string $message): void {
        $_SESSION["flash_{$type}"] = $message;
    }

    protected function getFlash(string $type): ?string {
        $message = $_SESSION["flash_{$type}"] ?? null;
        unset($_SESSION["flash_{$type}"]);
        return $message;
    }

    protected function getFlashMessages(): array {
        $messages = [];
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            if (!empty($_SESSION["flash_{$type}"])) {
                $messages[$type] = $_SESSION["flash_{$type}"];
                unset($_SESSION["flash_{$type}"]);
            }
        }
        return $messages;
    }

    protected function uploadFile(array $file, string $directory, array $allowedTypes = [], int $maxSize = 5242880): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($maxSize > 0 && $file['size'] > $maxSize) {
            return null;
        }

        if (!empty($allowedTypes)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedTypes)) {
                return null;
            }
        }

        $uploadDir = UPLOAD_PATH . $directory;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        $destination = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $directory . '/' . $filename;
        }

        return null;
    }

    protected function logActivity(string $action, string $description = ''): void {
        $this->db->insert('activity_logs', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    }

    private const MODULE_NOTIFICATION_TYPES = [
        'rooms'          => [],
        'reservations'   => ['reservation'],
        'students'       => [],
        'payments'       => ['payment'],
        'announcements'  => ['announcement'],
        'maintenance'    => ['maintenance'],
        'complaints'     => ['complaint'],
        'feedback'       => [],
        'contact_messages' => [],
        'notifications'  => ['reservation', 'payment', 'announcement', 'maintenance', 'complaint', 'system'],
    ];

    public function markModuleRead(string $module): void {
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) return;
        $this->db->query(
            "INSERT INTO user_module_reads (user_id, module, last_read_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE last_read_at = NOW()",
            [$userId, $module]
        );

        $types = self::MODULE_NOTIFICATION_TYPES[$module] ?? [];
        if ($types) {
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $this->db->query(
                "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0 AND type IN ({$placeholders})",
                array_merge([$userId], $types)
            );
        }
    }

    private function getUserModuleReads(array $modules): array {
        $userId = $_SESSION['user_id'] ?? 0;
        $result = [];
        foreach ($modules as $m) {
            $result[$m] = '1970-01-01 00:00:00';
        }
        if (!$userId) return $result;
        $rows = $this->db->fetchAll(
            "SELECT module, last_read_at FROM user_module_reads WHERE user_id = ? AND module IN (" . implode(',', array_fill(0, count($modules), '?')) . ")",
            array_merge([$userId], $modules)
        );
        foreach ($rows as $row) {
            $result[$row['module']] = $row['last_read_at'];
        }
        return $result;
    }

    private function getAdminSidebarCounts(): array {
        $lr = $this->getUserModuleReads(['rooms','reservations','students','payments','announcements','maintenance','complaints','feedback','contact_messages']);
        $userId = $_SESSION['user_id'] ?? 0;
        return [
            'rooms_available'      => $this->db->count('rooms', "status = 'available' AND updated_at > ?", [$lr['rooms']]),
            'pending_reservations' => $this->db->count('reservations', "status = 'pending' AND created_at > ?", [$lr['reservations']]),
            'total_students'       => $this->db->count('students', "created_at > ?", [$lr['students']]),
            'total_tenants'        => $this->db->count('reservations', "status = 'approved' AND approved_at > ?", [$lr['students']]),
            'pending_payments'     => $this->db->count('payments', "status = 'pending' AND created_at > ?", [$lr['payments']]),
            'total_announcements'  => $this->db->count('announcements', "created_at > ?", [$lr['announcements']]),
            'open_maintenance'     => $this->db->count('maintenance_requests', "status IN ('pending','in_progress') AND created_at > ?", [$lr['maintenance']]),
            'open_complaints'      => $this->db->count('complaints', "status IN ('open','under_review') AND created_at > ?", [$lr['complaints']]),
            'new_feedback'         => $this->db->count('feedback', "status = 'new' AND created_at > ?", [$lr['feedback']]),
            'new_messages'         => $this->db->count('contact_messages', "status = 'new' AND created_at > ?", [$lr['contact_messages']]),
            'pending_refunds'      => $this->db->count('refund_requests', "status = 'pending'", []),
            'unread_notifications' => $this->db->count('notifications', "user_id = ? AND is_read = 0", [$userId]),
        ];
    }

    private function getManagerSidebarCounts(): array {
        $lr = $this->getUserModuleReads(['rooms','reservations','students','payments','announcements','maintenance','complaints','feedback','contact_messages']);
        $userId = $_SESSION['user_id'] ?? 0;
        return [
            'rooms_available'      => $this->db->count('rooms', "status = 'available' AND updated_at > ?", [$lr['rooms']]),
            'pending_reservations' => $this->db->count('reservations', "status = 'pending' AND created_at > ?", [$lr['reservations']]),
            'total_students'       => $this->db->count('students', "created_at > ?", [$lr['students']]),
            'total_tenants'        => $this->db->count('reservations', "status = 'approved' AND approved_at > ?", [$lr['students']]),
            'pending_payments'     => $this->db->count('payments', "status = 'pending' AND created_at > ?", [$lr['payments']]),
            'total_announcements'  => $this->db->count('announcements', "created_at > ?", [$lr['announcements']]),
            'open_maintenance'     => $this->db->count('maintenance_requests', "status IN ('pending','in_progress') AND created_at > ?", [$lr['maintenance']]),
            'open_complaints'      => $this->db->count('complaints', "status IN ('open','under_review') AND created_at > ?", [$lr['complaints']]),
            'new_feedback'         => $this->db->count('feedback', "status = 'new' AND created_at > ?", [$lr['feedback']]),
            'new_messages'         => $this->db->count('contact_messages', "status = 'new' AND created_at > ?", [$lr['contact_messages']]),
            'pending_refunds'      => $this->db->count('refund_requests', "status = 'pending'", []),
            'unread_notifications' => $this->db->count('notifications', "user_id = ? AND is_read = 0", [$userId]),
        ];
    }

    private function getStudentSidebarCounts(): array {
        $userId = $_SESSION['user_id'] ?? 0;
        $student = $this->db->fetch("SELECT id FROM students WHERE user_id = ?", [$userId]);
        $sid = $student['id'] ?? 0;
        if (!$sid) return [];
        $lr = $this->getUserModuleReads(['payments','announcements','maintenance','complaints','feedback']);
        return [
            'pending_payments'   => $this->db->count('payments', "student_id = ? AND status = 'pending' AND created_at > ?", [$sid, $lr['payments']]),
            'unpaid_payments'    => $this->db->count('payments', "student_id = ? AND status = 'overdue' AND created_at > ?", [$sid, $lr['payments']]),
            'new_announcements'  => $this->db->count('announcements', "is_published = 1 AND created_at > ?", [$lr['announcements']]),
            'open_maintenance'   => $this->db->count('maintenance_requests', "student_id = ? AND status IN ('pending','in_progress') AND created_at > ?", [$sid, $lr['maintenance']]),
            'open_complaints'    => $this->db->count('complaints', "student_id = ? AND status IN ('open','under_review') AND created_at > ?", [$sid, $lr['complaints']]),
            'pending_feedback'   => $this->db->count('feedback', "student_id = ? AND status = 'new' AND created_at > ?", [$sid, $lr['feedback']]),
            'pending_refunds'    => $this->db->count('refund_requests', "student_id = ? AND status = 'pending'", [$sid]),
            'unread_notifications' => $this->db->count('notifications', "user_id = ? AND is_read = 0", [$userId]),
        ];
    }

    public function getSidebarCountsForRole(string $role): array {
        if ($role === 'admin' || $role === 'super_admin') return $this->getAdminSidebarCounts();
        if ($role === 'manager') return $this->getManagerSidebarCounts();
        if ($role === 'student') return $this->getStudentSidebarCounts();
        return [];
    }

    // â”€â”€â”€ Archive & Recovery (shared by AdminController & ManagerController) â”€â”€â”€

    protected const ARCHIVE_MODULES = [
        'room'                 => ['table' => 'rooms',                 'label' => 'Room',                 'icon' => 'fa-door-open'],
        'student'              => ['table' => 'students',              'label' => 'Student',              'icon' => 'fa-user-graduate'],
        'reservation'          => ['table' => 'reservations',          'label' => 'Reservation',          'icon' => 'fa-calendar-check'],
        'payment'              => ['table' => 'payments',              'label' => 'Payment',              'icon' => 'fa-money-bill-wave'],
        'receipt'              => ['table' => 'receipts',              'label' => 'Receipt',              'icon' => 'fa-receipt'],
        'announcement'         => ['table' => 'announcements',         'label' => 'Announcement',         'icon' => 'fa-bullhorn'],
        'maintenance_request'  => ['table' => 'maintenance_requests',  'label' => 'Maintenance Request',  'icon' => 'fa-tools'],
        'complaint'            => ['table' => 'complaints',            'label' => 'Complaint',            'icon' => 'fa-exclamation-triangle'],
        'feedback'             => ['table' => 'feedback',              'label' => 'Feedback',             'icon' => 'fa-comment-dots'],
        'contact_message'      => ['table' => 'contact_messages',      'label' => 'Contact Message',      'icon' => 'fa-envelope'],
        'gallery'              => ['table' => 'gallery',               'label' => 'Gallery',              'icon' => 'fa-images'],
        'amenity'              => ['table' => 'amenities',             'label' => 'Amenity',              'icon' => 'fa-couch'],
        'manager'              => ['table' => 'managers',              'label' => 'Manager',              'icon' => 'fa-user-tie'],
        'refund_request'       => ['table' => 'refund_requests',       'label' => 'Refund Request',       'icon' => 'fa-hand-holding-usd'],
    ];

    protected const ARCHIVE_FILE_COLUMNS = [
        'room_images' => ['image_path'],
        'payments'    => ['proof_of_payment'],
        'students'    => ['profile_picture', 'valid_id_path', 'school_id_path'],
        'managers'    => ['profile_picture'],
        'gallery'     => ['image_path'],
    ];

    protected const ARCHIVE_UNIQUE_COLUMNS = [
        'rooms'                  => ['room_number'],
        'users'                  => ['email', 'username'],
        'reservations'           => ['reservation_code'],
        'payments'               => ['payment_code'],
        'receipts'               => ['receipt_number'],
        'maintenance_requests'   => ['request_code'],
        'complaints'             => ['complaint_code'],
        'refund_requests'        => ['refund_code'],
    ];

    protected function archiveRecord(string $module, int $recordId, array $primaryData, array $relatedData = []): bool {
        $table = self::ARCHIVE_MODULES[$module]['table'] ?? null;
        if (!$table) {
            return false;
        }
        // Uploaded files are kept on disk so restored records resolve their
        // image_path/proof columns; embedded files are only kept for legacy
        // archives created before this change. File cleanup happens on
        // permanent delete (see cleanupArchivedFiles).
        $payload = ['primary' => $primaryData, 'related' => $relatedData, 'files' => []];
        $json = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            return false;
        }
        try {
            $this->db->insert('archived_records', [
                'module' => $module,
                'record_id' => $recordId,
                'data' => $json,
                'deleted_by' => $_SESSION['user_id'] ?? null,
            ]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function restoreArchivedRecord(int $archiveId): array {
        $archive = $this->db->fetch("SELECT * FROM archived_records WHERE id = ?", [$archiveId]);
        if (!$archive) {
            return ['ok' => false, 'message' => 'Archived record not found.'];
        }
        $module = $archive['module'];
        $label = self::ARCHIVE_MODULES[$module]['label'] ?? $module;
        $data = json_decode($archive['data'], true);
        if (!is_array($data)) {
            return ['ok' => false, 'message' => 'Archived data is invalid and cannot be restored.'];
        }

        $primaryTable = self::ARCHIVE_MODULES[$module]['table'] ?? null;
        if (!$primaryTable) {
            return ['ok' => false, 'message' => 'Unsupported module.'];
        }

        $primary = $data['primary'] ?? null;
        $related = $data['related'] ?? [];
        $files = $data['files'] ?? [];
        if (!is_array($primary)) {
            if ($module === 'student' && isset($data['students']) && isset($data['users'])) {
                $primary = $data['students'];
                $related = ['users' => [$data['users']]];
            } else {
                $primary = $data;
                $related = [];
            }
        }
        if (!is_array($files)) {
            $files = [];
        }

        $pdo = $this->db->getConnection();
        $fkDisabled = false;
        $pdo->beginTransaction();
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            $fkDisabled = true;

            // Rooms referenced by the archived record (e.g. the room a tenant
            // occupied): reuse an existing room if one is still present (by id
            // or room_number) instead of failing the whole restore, and
            // re-create it (with images/amenities) if it was deleted too.
            $roomRemap = [];   // archived room id => room id to link reservations to
            $roomsToSkip = []; // archived room id => true (room already exists)
            $archivedRooms = isset($related['rooms']) && is_array($related['rooms']) ? $related['rooms'] : [];
            foreach ($archivedRooms as $room) {
                if (!is_array($room)) {
                    continue;
                }
                $archivedRoomId = (int)($room['id'] ?? 0);
                $target = null;
                if ($archivedRoomId > 0 && $this->db->fetch("SELECT id FROM rooms WHERE id = ?", [$archivedRoomId])) {
                    $target = $archivedRoomId;
                } elseif (!empty($room['room_number'])) {
                    $existing = $this->db->fetch("SELECT id FROM rooms WHERE room_number = ?", [$room['room_number']]);
                    if ($existing) {
                        $target = (int)$existing['id'];
                    }
                }
                if ($target !== null) {
                    $roomRemap[$archivedRoomId] = $target;
                    $roomsToSkip[$archivedRoomId] = true;
                }
            }

            if ($roomRemap) {
                $reservations = $related['reservations'] ?? [];
                if (is_array($reservations)) {
                    foreach ($reservations as &$resv) {
                        if (is_array($resv) && isset($resv['room_id']) && isset($roomRemap[(int)$resv['room_id']])) {
                            $resv['room_id'] = $roomRemap[(int)$resv['room_id']];
                        }
                    }
                    unset($resv);
                    $related['reservations'] = $reservations;
                }
            }

            $tables = array_values(array_unique(array_merge([$primaryTable], array_keys($related))));
            foreach ($tables as $table) {
                $rows = $table === $primaryTable ? [$primary] : ($related[$table] ?? []);
                foreach ($rows as $row) {
                    if ($table === 'rooms' && isset($row['id']) && isset($roomsToSkip[(int)$row['id']])) {
                        continue;
                    }
                    if (in_array($table, ['room_images', 'room_amenities'], true) && isset($row['room_id']) && isset($roomsToSkip[(int)$row['room_id']])) {
                        continue;
                    }
                    $rowId = isset($row['id']) ? (int)$row['id'] : 0;
                    if ($rowId > 0 && $this->db->fetch("SELECT id FROM `{$table}` WHERE id = ?", [$rowId])) {
                        throw new \RuntimeException("Cannot restore {$label}: a {$table} record with ID {$rowId} already exists.");
                    }
                    foreach (self::ARCHIVE_UNIQUE_COLUMNS[$table] ?? [] as $col) {
                        $val = $row[$col] ?? null;
                        if ($val === null || $val === '') {
                            continue;
                        }
                        $exists = $this->db->fetch("SELECT id FROM `{$table}` WHERE {$col} = ? AND id != ?", [$val, $rowId]);
                        if ($exists) {
                            throw new \RuntimeException("Cannot restore {$label}: {$col} \"{$val}\" is already in use.");
                        }
                    }
                }
            }

            foreach ($files as $f) {
                $this->restoreArchivedFile($f);
            }

            $this->db->insert($primaryTable, $primary);
            foreach ($related as $table => $rows) {
                if (!is_array($rows)) {
                    continue;
                }
                foreach ($rows as $row) {
                    if ($table === 'rooms' && isset($row['id']) && isset($roomsToSkip[(int)$row['id']])) {
                        continue;
                    }
                    if (in_array($table, ['room_images', 'room_amenities'], true) && isset($row['room_id']) && isset($roomsToSkip[(int)$row['room_id']])) {
                        continue;
                    }
                    if ($table === 'users') {
                        // Restored accounts were verified when archived; keep them
                        // usable so a restore never locks anyone out.
                        $row['email_verified'] = 1;
                        if (empty($row['email_verified_at'])) {
                            $row['email_verified_at'] = serverDateTime();
                        }
                    }
                    $this->db->insert($table, $row);
                }
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            $fkDisabled = false;
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                try { $pdo->rollBack(); } catch (\Throwable $ignored) {}
            }
            if ($fkDisabled) {
                try { $pdo->exec('SET FOREIGN_KEY_CHECKS = 1'); } catch (\Throwable $ignored) {}
            }
            return ['ok' => false, 'message' => 'Restore failed: ' . $e->getMessage()];
        }

        // Recompute occupancy/status for every room linked to this restore
        // (both re-created rooms and existing rooms referenced by reservations).
        $roomIdsToUpdate = [];
        foreach ($archivedRooms as $room) {
            if (!is_array($room)) {
                continue;
            }
            $archivedRoomId = (int)($room['id'] ?? 0);
            $roomIdsToUpdate[$roomRemap[$archivedRoomId] ?? $archivedRoomId] = true;
        }
        $restoredReservations = $related['reservations'] ?? [];
        if (is_array($restoredReservations)) {
            foreach ($restoredReservations as $resv) {
                if (is_array($resv) && !empty($resv['room_id'])) {
                    $roomIdsToUpdate[(int)$resv['room_id']] = true;
                }
            }
        }
        foreach (array_keys($roomIdsToUpdate) as $roomId) {
            if ($roomId > 0) {
                $this->updateRoomOccupancy($roomId);
            }
        }

        $this->db->delete('archived_records', "id = ?", [$archiveId]);
        $this->logActivity('restore_record', "Restored archived {$module} record #{$archive['record_id']}");
        $this->notifyArchiveAction(
            'restored',
            $module,
            $this->archiveSummary($module, $data),
            (string)($_SESSION['user_role'] ?? ''),
            (string)($_SESSION['user_email'] ?? '')
        );
        return ['ok' => true, 'message' => $label . ' record restored successfully.'];
    }

    protected function restoreArchivedFile(array $f): void {
        $rel = $f['orig'] ?? null;
        $b64 = $f['base64'] ?? null;
        if (!$rel || !$b64) {
            return;
        }
        $dest = UPLOAD_PATH . $rel;
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $content = base64_decode($b64, true);
        if ($content !== false) {
            @file_put_contents($dest, $content);
        }
    }

    protected function permanentlyDeleteArchivedRecord(int $archiveId): array {
        $archive = $this->db->fetch("SELECT * FROM archived_records WHERE id = ?", [$archiveId]);
        if (!$archive) {
            return ['ok' => false, 'message' => 'Archived record not found.'];
        }
        $data = json_decode($archive['data'], true);
        if (is_array($data)) {
            $this->cleanupArchivedFiles($archiveId, $archive['module'], $data);
        }
        $this->db->delete('archived_records', "id = ?", [$archiveId]);
        $this->logActivity('permanent_delete_record', "Permanently deleted archived {$archive['module']} record #{$archive['record_id']}");
        $summary = is_array($data) ? $this->archiveSummary($archive['module'], $data) : ('#' . ($archive['record_id'] ?? ''));
        $this->notifyArchiveAction(
            'permanently deleted',
            $archive['module'],
            $summary,
            (string)($_SESSION['user_role'] ?? ''),
            (string)($_SESSION['user_email'] ?? '')
        );
        return ['ok' => true, 'message' => 'Record permanently deleted.'];
    }

    protected function cleanupArchivedFiles(int $archiveId, string $module, array $data): void {
        $paths = [];
        $primaryTable = self::ARCHIVE_MODULES[$module]['table'] ?? null;
        $primary = $data['primary'] ?? null;
        $related = $data['related'] ?? [];

        if ($primaryTable && is_array($primary)) {
            foreach (self::ARCHIVE_FILE_COLUMNS[$primaryTable] ?? [] as $col) {
                if (!empty($primary[$col])) {
                    $paths[] = $primary[$col];
                }
            }
        }
        if (is_array($related)) {
            foreach ($related as $table => $rows) {
                if (!is_array($rows)) {
                    continue;
                }
                foreach (self::ARCHIVE_FILE_COLUMNS[$table] ?? [] as $col) {
                    foreach ($rows as $row) {
                        if (is_array($row) && !empty($row[$col])) {
                            $paths[] = $row[$col];
                        }
                    }
                }
            }
        }
        $files = $data['files'] ?? [];
        if (is_array($files)) {
            foreach ($files as $f) {
                if (is_array($f) && !empty($f['orig'])) {
                    $paths[] = $f['orig'];
                }
            }
        }

        foreach (array_unique(array_filter($paths)) as $rel) {
            // Never remove a file still referenced by another archived record.
            // JSON-encoded data escapes "/" as "\/", so match on the unique
            // basename instead of the full path.
            $needle = basename($rel);
            $other = $this->db->fetch("SELECT id FROM archived_records WHERE id != ? AND data LIKE ? LIMIT 1", [$archiveId, '%' . addcslashes($needle, '\\%_') . '%']);
            if ($other) {
                continue;
            }
            $full = UPLOAD_PATH . $rel;
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }

    protected function archiveSummary(string $module, array $data): string {
        $primary = $data['primary'] ?? $data;
        if (!is_array($primary)) {
            return '#' . ($data['id'] ?? '');
        }
        switch ($module) {
            case 'room':                return $primary['room_number'] ?? ('#' . ($primary['id'] ?? ''));
            case 'student':             return trim(($primary['first_name'] ?? '') . ' ' . ($primary['last_name'] ?? '')) ?: ('#' . ($primary['id'] ?? ''));
            case 'reservation':         return $primary['reservation_code'] ?? ('#' . ($primary['id'] ?? ''));
            case 'payment':             return $primary['payment_code'] ?? ('#' . ($primary['id'] ?? ''));
            case 'receipt':             return $primary['receipt_number'] ?? ('#' . ($primary['id'] ?? ''));
            case 'announcement':        return $primary['title'] ?? ('#' . ($primary['id'] ?? ''));
            case 'maintenance_request': return $primary['request_code'] ?? ($primary['title'] ?? ('#' . ($primary['id'] ?? '')));
            case 'complaint':           return $primary['complaint_code'] ?? ($primary['subject'] ?? ('#' . ($primary['id'] ?? '')));
            case 'feedback':            return $primary['subject'] ?? ('#' . ($primary['id'] ?? ''));
            case 'contact_message':     return $primary['subject'] ?? ($primary['name'] ?? ('#' . ($primary['id'] ?? '')));
            case 'gallery':             return $primary['title'] ?? ('#' . ($primary['id'] ?? ''));
            case 'amenity':             return $primary['name'] ?? ('#' . ($primary['id'] ?? ''));
            case 'manager':             return trim(($primary['first_name'] ?? '') . ' ' . ($primary['last_name'] ?? '')) ?: ('#' . ($primary['id'] ?? ''));
            default:                    return '#' . ($primary['id'] ?? '');
        }
    }

    protected function notifyArchiveAction(string $action, string $module, string $summary, string $actorRole, string $actorEmail): int {
        $actor = (string)$actorRole;

        $recipients = $this->db->fetchAll(
            "SELECT DISTINCT email, role FROM users
             WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1
               AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (!$recipients) return 0;

        require_once __DIR__ . '/Services/MailService.php';

        $actionLabels = [
            'archived'           => 'Archived',
            'restored'           => 'Restored',
            'permanently deleted' => 'Permanently Deleted',
        ];
        $actionLabel = $actionLabels[$action] ?? ucfirst($action);
        $color = $action === 'restored' ? '#16a34a' : ($action === 'archived' ? '#d97706' : '#dc2626');
        $moduleLabel = self::ARCHIVE_MODULES[$module]['label'] ?? ucwords(str_replace('_', ' ', $module));
        $actorLabel = ($actor === 'super_admin') ? 'Admin' : ($actor === 'manager' ? 'Manager' : ucfirst($actor));
        $byLine = $actorLabel . (($actorEmail !== '') ? ' (' . $actorEmail . ')' : '');

        $subject = 'Archive & Recovery: ' . $moduleLabel . ' ' . $actionLabel;
        $sent = 0;
        $mailer = new MailService();

        foreach ($recipients as $recipient) {
            $archiveUrl = $recipient['role'] === 'super_admin' ? url('/admin/archive') : url('/manager/archive');
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:' . $color . ';margin:0 0 16px;">' . e($actionLabel) . ' Record: ' . e($moduleLabel) . '</h2>'
                . '<p>Hello,</p>'
                . '<p>A <strong>' . e($moduleLabel) . '</strong> record has been <strong>' . e(strtolower($actionLabel)) . '</strong> by ' . e($byLine) . '.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Module</td><td style="padding:6px 8px;font-weight:600;">' . e($moduleLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Record</td><td style="padding:6px 8px;">' . e($summary) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Action</td><td style="padding:6px 8px;">' . e($actionLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Done By</td><td style="padding:6px 8px;">' . e($byLine) . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="' . e($archiveUrl) . '" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Open Archive &amp; Recovery</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($recipient['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyArchiveAction: failed to notify ' . $recipient['email'] . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected const PASSWORD_HISTORY_LIMIT = 5;

    protected function isPasswordReused(int $userId, string $newPassword): bool {
        $history = $this->db->fetchAll(
            "SELECT password_hash FROM password_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, self::PASSWORD_HISTORY_LIMIT]
        );
        foreach ($history as $record) {
            if (password_verify($newPassword, $record['password_hash'])) {
                return true;
            }
        }
        return false;
    }

    protected function savePasswordHistory(int $userId, string $passwordHash): void {
        $this->db->insert('password_history', [
            'user_id' => $userId,
            'password_hash' => $passwordHash,
        ]);
        $count = $this->db->count('password_history', "user_id = ?", [$userId]);
        if ($count > self::PASSWORD_HISTORY_LIMIT) {
            $this->db->query(
                "DELETE FROM password_history WHERE user_id = ? ORDER BY created_at ASC LIMIT ?",
                [$userId, $count - self::PASSWORD_HISTORY_LIMIT]
            );
        }
    }

    protected function updateRoomOccupancy(int $roomId): void {
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$roomId]);
        if (!$room) return;
        $approved = (int)$this->db->fetch(
            "SELECT COUNT(*) as c FROM reservations WHERE room_id = ? AND status = 'approved'",
            [$roomId]
        )['c'];
        $maxCap = (int)$room['max_capacity'];
        $occupancy = min($approved, $maxCap);

        if ($room['status'] === 'under_maintenance') {
            $status = 'under_maintenance';
        } elseif ($occupancy > 0) {
            $status = 'occupied';
        } elseif ($room['status'] === 'reserved') {
            $status = 'reserved';
        } else {
            $status = 'available';
        }
        $this->db->query(
            "UPDATE rooms SET current_occupancy = ?, status = ? WHERE id = ?",
            [$occupancy, $status, $roomId]
        );
    }

    /**
     * Recomputes current_occupancy and status for EVERY room from the live
     * approved reservations, keeping the stored columns truthful and in sync
     * with the actual tenants. Idempotent â€” safe to run on any request.
     *
     *   * under_maintenance is preserved (manual override)
     *   * rooms with boarders (approved reservations) -> occupied
     *   * reserved is preserved only when the room is empty
     *   * everything else -> available
     */
    protected function reconcileRoomStatuses(): void {
        $this->db->query(
            "UPDATE rooms r
             LEFT JOIN (
                 SELECT room_id, COUNT(*) AS cnt
                 FROM reservations
                 WHERE status = 'approved'
                 GROUP BY room_id
             ) a ON a.room_id = r.id
             SET r.current_occupancy = LEAST(r.max_capacity, IFNULL(a.cnt, 0)),
                 r.status = IF(
                     r.status = 'under_maintenance',
                     'under_maintenance',
                     IF(
                         IFNULL(a.cnt, 0) > 0,
                         'occupied',
                         IF(r.status = 'reserved', 'reserved', 'available')
                     )
                 )"
        );
    }

    protected function deleteUploadedFile(?string $relativePath): void {
        if (!empty($relativePath) && file_exists(UPLOAD_PATH . $relativePath)) {
            @unlink(UPLOAD_PATH . $relativePath);
        }
    }

    /**
     * Permanently removes a tenant account and EVERY related record and file:
     * reservations, payments (incl. walk-in), payment history, receipts,
     * guardians, complaints, maintenance requests, feedback, notifications,
     * password history/resets, module reads, activity/audit logs, and login
     * attempts. Runs before the core student/user rows are deleted so room
     * occupancy can be recomputed accurately.
     */
    protected function deleteStudentAccount(int $studentId, int $userId, string $email, array $studentFilePaths = [], bool $keepFiles = false): void {
        // --- Reservation + payment files and room references ---
        $reservations = $this->db->fetchAll(
            "SELECT id, room_id, valid_id_path FROM reservations WHERE student_id = ?",
            [$studentId]
        );
        $allRoomIds = [];
        foreach ($reservations as $res) {
            if ($res['room_id']) {
                $allRoomIds[(int)$res['room_id']] = true;
            }
            if (!$keepFiles) {
                $this->deleteUploadedFile($res['valid_id_path'] ?? null);
            }
        }

        $payments = $this->db->fetchAll(
            "SELECT id, proof_of_payment FROM payments WHERE student_id = ?",
            [$studentId]
        );
        $paymentIds = [];
        foreach ($payments as $pay) {
            $paymentIds[] = (int)$pay['id'];
            if (!$keepFiles) {
                $this->deleteUploadedFile($pay['proof_of_payment'] ?? null);
            }
        }

        // --- Tenant files ---
        if (!$keepFiles) {
            foreach ($studentFilePaths as $path) {
                $this->deleteUploadedFile($path);
            }
        }

        // --- Delete tenant records explicitly (children first, FK-safe) ---
        if ($paymentIds) {
            $ph = implode(',', array_fill(0, count($paymentIds), '?'));
            $this->db->query("DELETE FROM receipts WHERE payment_id IN ({$ph})", $paymentIds);
            $this->db->query("DELETE FROM payment_history WHERE payment_id IN ({$ph})", $paymentIds);
        }
        $this->db->delete('payments', "student_id = ?", [$studentId]);
        $this->db->delete('reservations', "student_id = ?", [$studentId]);
        $this->db->delete('guardians', "student_id = ?", [$studentId]);
        $this->db->delete('complaints', "student_id = ?", [$studentId]);
        $this->db->delete('maintenance_requests', "student_id = ?", [$studentId]);
        $this->db->delete('feedback', "student_id = ?", [$studentId]);

        $this->db->delete('notifications', "user_id = ?", [$userId]);
        $this->db->delete('password_history', "user_id = ?", [$userId]);
        $this->db->delete('user_module_reads', "user_id = ?", [$userId]);
        $this->db->delete('password_resets', "email = ?", [$email]);
        $this->db->delete('activity_logs', "user_id = ?", [$userId]);
        $this->db->deleteIfExists('audit_logs', "user_id = ?", [$userId]);
        $this->db->deleteIfExists('login_attempts', "email = ?", [$email]);

        $this->db->delete('students', "id = ?", [$studentId]);
        $this->db->delete('users', "id = ?", [$userId]);

        foreach (array_keys($allRoomIds) as $rid) {
            $this->updateRoomOccupancy($rid);
        }
    }

    protected function permanentDeleteStudent(array $student, array $user, bool $keepFiles = false): void {
        $this->deleteStudentAccount(
            (int)$student['id'],
            (int)$user['id'],
            (string)($user['email'] ?? ''),
            array_filter([
                $student['profile_picture'] ?? null,
                $student['valid_id_path'] ?? null,
                $student['school_id_path'] ?? null,
            ]),
            $keepFiles
        );
    }

    protected function notifyPaymentEvent(int $studentUserId, string $tenantTitle, string $tenantMessage, string $managerMessage, string $adminMessage, ?int $referenceId = null): void {
        $payload = [
            'type' => 'payment',
            'reference_id' => $referenceId,
            'reference_type' => 'payment',
        ];
        $this->db->insert('notifications', array_merge($payload, [
            'user_id' => $studentUserId,
            'title' => $tenantTitle,
            'message' => $tenantMessage,
        ]));
        $managers = $this->db->fetchAll("SELECT user_id FROM managers");
        foreach ($managers as $m) {
            $this->db->insert('notifications', array_merge($payload, [
                'user_id' => (int)$m['user_id'],
                'title' => $tenantTitle,
                'message' => $managerMessage,
            ]));
        }
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role = 'super_admin'");
        foreach ($admins as $a) {
            $this->db->insert('notifications', array_merge($payload, [
                'user_id' => (int)$a['id'],
                'title' => $tenantTitle,
                'message' => $adminMessage,
            ]));
        }
    }

    protected function notifyStudentsNewRoom(array $room): int {
        require_once __DIR__ . '/Services/MailService.php';

        $recipients = $this->db->fetchAll(
            "SELECT s.first_name, s.last_name, u.email
             FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE u.email IS NOT NULL AND TRIM(u.email) != ''"
        );
        if (empty($recipients)) return 0;

        $roomLabel = trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? ''));
        $roomType  = ucfirst($room['room_type'] ?? 'Bedspacer');
        $rent      = formatCurrency((float)($room['monthly_rent'] ?? 0));
        $subject   = 'New Room Available: ' . $roomLabel;
        $sent      = 0;

        $mailer = new MailService();

        foreach ($recipients as $recipient) {
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 16px;">A New Room Is Available</h2>'
                . '<p>Hi ' . e((string)($recipient['first_name'] ?? '')) . ',</p>'
                . '<p>Great news! A new room is now open for reservation at Alondes Dorm.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Room</td><td style="padding:6px 8px;font-weight:600;">' . $roomLabel . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Type</td><td style="padding:6px 8px;">' . $roomType . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Monthly Rent</td><td style="padding:6px 8px;">' . $rent . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;">Available</td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="' . url('/rooms') . '" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Browse Rooms</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($recipient['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyStudentsNewRoom: failed to notify ' . $recipient['email'] . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function notifyGalleryUpload(string $title, int $count): int {
        require_once __DIR__ . '/Services/MailService.php';

        $recipients = $this->db->fetchAll(
            "SELECT s.first_name, s.last_name, u.email
             FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE u.email IS NOT NULL AND TRIM(u.email) != ''"
        );
        if (empty($recipients)) return 0;

        $subject = 'New Photos Added to the Gallery';
        $sent    = 0;
        $mailer  = new MailService();

        foreach ($recipients as $recipient) {
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 16px;">New Photos in the Gallery</h2>'
                . '<p>Hi ' . e((string)($recipient['first_name'] ?? '')) . ',</p>'
                . '<p>' . $count . ' new photo(s) have been added to the gallery' . (!empty($title) ? ' under <strong>' . e($title) . '</strong>' : '') . ' at Alondes Dorm. Come and take a look!</p>'
                . '<p style="margin-top:16px;"><a href="' . url('/gallery') . '" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View Gallery</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($recipient['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyGalleryUpload: failed to notify ' . $recipient['email'] . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function notifyRoomUpdate(array $room, array $oldRoom = []): int {
        require_once __DIR__ . '/Services/MailService.php';

        $recipients = [];
        $students = $this->db->fetchAll(
            "SELECT s.first_name, s.last_name, u.email
             FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE u.email IS NOT NULL AND TRIM(u.email) != ''"
        );
        foreach ($students as $st) {
            $recipients[$st['email']] = $st['first_name'] ?? '';
        }
        $staff = $this->db->fetchAll(
            "SELECT u.email, m.first_name
             FROM users u
             LEFT JOIN managers m ON m.user_id = u.id
             WHERE u.role IN ('super_admin','manager') AND u.status = 'active' AND u.email_verified = 1
               AND u.email IS NOT NULL AND TRIM(u.email) != ''"
        );
        foreach ($staff as $sf) {
            $recipients[$sf['email']] = $sf['first_name'] ?? '';
        }
        if (!$recipients) return 0;

        $roomNumber = (string)($room['room_number'] ?? '');
        $roomName   = (string)($room['room_name'] ?? '');
        $roomLabel  = trim($roomNumber . ' ' . $roomName);
        $roomType   = ucfirst($room['room_type'] ?? 'Bedspacer');
        $rent       = formatCurrency((float)($room['monthly_rent'] ?? 0));
        $capacity   = (int)($room['max_capacity'] ?? 1);
        $status     = ucwords(str_replace('_', ' ', (string)($room['status'] ?? 'available')));

        $changeRows = '';
        if (!empty($oldRoom)) {
            $oldRent    = (float)($oldRoom['monthly_rent'] ?? 0);
            $oldStatus  = (string)($oldRoom['status'] ?? '');
            if ($oldRent != (float)($room['monthly_rent'] ?? 0)) {
                $changeRows .= '<tr><td style="padding:6px 8px;color:#475569;">Monthly Rent</td><td style="padding:6px 8px;">' . formatCurrency($oldRent) . ' &rarr; <b>' . $rent . '</b></td></tr>';
            }
            if ($oldStatus !== (string)($room['status'] ?? '')) {
                $changeRows .= '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;">' . ucwords(str_replace('_', ' ', $oldStatus)) . ' &rarr; <b>' . $status . '</b></td></tr>';
            }
        }

        $subject = 'Room Updated: ' . ($roomLabel !== '' ? $roomLabel : 'Dorm Room');
        $sent    = 0;
        $mailer  = new MailService();

        foreach ($recipients as $email => $firstName) {
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 16px;">Room Updated: ' . $roomLabel . '</h2>'
                . '<p>Hi ' . e((string)$firstName) . ',</p>'
                . '<p>Details for ' . ($roomLabel !== '' ? $roomLabel : 'the room') . ' have been updated.</p>'
                . ($changeRows !== '' ? '<table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:14px;"><tr><td colspan="2" style="padding:6px 8px;font-weight:700;color:#0f172a;">What changed</td></tr>' . $changeRows . '</table>' : '')
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Room</td><td style="padding:6px 8px;font-weight:600;">' . $roomLabel . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Type</td><td style="padding:6px 8px;">' . $roomType . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Monthly Rent</td><td style="padding:6px 8px;">' . $rent . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Max Capacity</td><td style="padding:6px 8px;">' . $capacity . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;">' . $status . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="' . url('/rooms') . '" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View Rooms</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyRoomUpdate: failed to notify ' . $email . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function recordDeletedFlash(string $summary, int $notified): string {
        $msg = 'You successfully Deleted ' . $summary . '.';
        if ($notified > 0) {
            $msg .= ' Admin and Manager notified by email.';
        }
        return $msg;
    }

    protected function notifyRecordDeleted(string $module, string $summary): int {
        $actorRole = (string)($_SESSION['user_role'] ?? '');

        $recipients = $this->db->fetchAll(
            "SELECT DISTINCT email, role FROM users
             WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1
               AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (!$recipients) return 0;

        require_once __DIR__ . '/Services/MailService.php';

        $moduleLabel = self::ARCHIVE_MODULES[$module]['label'] ?? ucwords(str_replace('_', ' ', $module));
        $actorLabel = ($actorRole === 'manager') ? 'Manager' : ($actorRole === 'super_admin' ? 'Admin' : ucfirst($actorRole));
        $actorEmail = (string)($_SESSION['user_email'] ?? '');
        $deletedBy = $actorLabel . (($actorEmail !== '') ? ' (' . $actorEmail . ')' : '');

        $subject = 'Record Deleted: ' . $moduleLabel;
        $sent = 0;
        $mailer = new MailService();

        foreach ($recipients as $recipient) {
            $listUrl = $recipient['role'] === 'super_admin' ? url('/admin/archive') : url('/manager/archive');
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#dc2626;margin:0 0 16px;">' . e($moduleLabel) . ' Deleted</h2>'
                . '<p>Hello,</p>'
                . '<p>A <strong>' . e($moduleLabel) . '</strong> record has been <strong>deleted</strong> by ' . e($deletedBy) . '.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Module</td><td style="padding:6px 8px;font-weight:600;">' . e($moduleLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Record</td><td style="padding:6px 8px;">' . e($summary) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Deleted By</td><td style="padding:6px 8px;">' . e($deletedBy) . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="' . e($listUrl) . '" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Open Archive &amp; Recovery</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($recipient['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyRecordDeleted: failed to notify ' . $recipient['email'] . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function notifyAnnouncement(array $announcement): int {
        if (empty($announcement['is_published'])) return 0;

        require_once __DIR__ . '/Services/MailService.php';

        $title    = e((string)($announcement['title'] ?? 'Announcement'));
        $content  = (string)($announcement['content'] ?? '');
        $type     = ucfirst((string)($announcement['type'] ?? 'general'));
        $priority = ucfirst((string)($announcement['priority'] ?? 'medium'));

        $recipients = [];
        $all = $this->db->fetchAll(
            "SELECT u.email, COALESCE(s.first_name, m.first_name, '') AS first_name
             FROM users u
             LEFT JOIN students s ON s.user_id = u.id
             LEFT JOIN managers m ON m.user_id = u.id
             WHERE u.status = 'active'
               AND u.email IS NOT NULL AND TRIM(u.email) != ''
               AND (u.role = 'student'
                    OR (u.role IN ('super_admin','manager') AND u.email_verified = 1))"
        );
        foreach ($all as $a) {
            $recipients[$a['email']] = $a['first_name'] ?? '';
        }
        if (!$recipients) return 0;

        $subject = 'New Announcement: ' . $title;
        $sent    = 0;
        $mailer  = new MailService();

        foreach ($recipients as $email => $firstName) {
            $greeting = ($firstName !== '') ? 'Hi ' . e((string)$firstName) . ',' : 'Hi there,';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 4px;">' . $title . '</h2>'
                . '<p style="font-size:12px;color:#64748b;margin:0 0 12px;">' . $type . ' &middot; Priority: ' . $priority . '</p>'
                . '<p style="color:#334155;font-size:14px;line-height:1.6;">' . nl2br($content) . '</p>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">' . $greeting . ' This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyAnnouncement: failed to notify ' . $email . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function notifyWalkInRegistration(array $student, array $room, float $received, string $method, string $reservationCode, string $moveInDate): int {
        require_once __DIR__ . '/Services/MailService.php';

        $studentEmail = (string)($student['email'] ?? '');
        $recipients = [];
        if ($studentEmail !== '') {
            $recipients[$studentEmail] = (string)($student['first_name'] ?? '');
        }
        $staff = $this->db->fetchAll(
            "SELECT u.email, COALESCE(m.first_name, '') AS first_name
             FROM users u
             LEFT JOIN managers m ON m.user_id = u.id
             WHERE u.role IN ('super_admin','manager') AND u.status = 'active' AND u.email_verified = 1
               AND u.email IS NOT NULL AND TRIM(u.email) != ''"
        );
        foreach ($staff as $sf) {
            $recipients[$sf['email']] = $sf['first_name'] ?? '';
        }
        if (!$recipients) return 0;

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $roomLabel   = "Room {$room['room_number']} ({$room['room_name']})";
        $methodLabel = strtoupper((string)$method);
        $amountLabel = formatCurrency($received);
        $subject     = 'Walk-In Registration & Payment Confirmed';

        $sent   = 0;
        $mailer = new MailService();

        foreach ($recipients as $email => $firstName) {
            $greeting = ($firstName !== '') ? 'Hi ' . e((string)$firstName) . ',' : 'Hi,';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 16px;">Walk-In Registration &amp; Payment</h2>'
                . '<p>' . $greeting . '</p>'
                . '<p>' . e($studentName) . ' has been registered as a tenant and the payment has been recorded.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Assigned Room</td><td style="padding:6px 8px;">' . $roomLabel . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Reservation Code</td><td style="padding:6px 8px;">' . e($reservationCode) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Move-in Date</td><td style="padding:6px 8px;">' . e($moveInDate) . '</td></tr>'
                . ($received > 0 ? '<tr><td style="padding:6px 8px;color:#475569;">Payment Method</td><td style="padding:6px 8px;">' . e($methodLabel) . '</td></tr>' : '')
                . ($received > 0 ? '<tr><td style="padding:6px 8px;color:#475569;">Amount Received</td><td style="padding:6px 8px;font-weight:600;">' . $amountLabel . '</td></tr>' : '')
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                if ($mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyWalkInRegistration: failed to notify ' . $email . ' - ' . $e->getMessage());
            }
        }

        return $sent;
    }

    protected function notifyTenantWalkInPayment(array $student, string $roomLabel, float $amount, string $method): bool {
        $email = (string)($student['email'] ?? '');
        if ($email === '') return false;

        require_once __DIR__ . '/Services/MailService.php';

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $subject = 'Payment Confirmation - Alondes Dorm';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Walk-In Payment Confirmed</h2>'
            . '<p>Hi ' . e((string)($student['first_name'] ?? '')) . ',</p>'
            . '<p>This is to confirm that your payment has been received over the counter.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Tenant</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;">' . e($roomLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Amount Paid</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Payment Method</td><td style="padding:6px 8px;">' . e(strtoupper($method)) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">Thank you and this is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyTenantWalkInPayment: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyContactMessageResponse(array $message): bool {
        $email = (string)($message['email'] ?? '');
        if (trim($email) === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        require_once __DIR__ . '/Services/MailService.php';

        $senderName = (string)($message['name'] ?? '');
        $subjectText = (string)($message['subject'] ?? 'Your Inquiry');
        $adminResponse = (string)($message['admin_response'] ?? '');
        $status = (string)($message['status'] ?? 'read');
        $statusLabel = ucfirst(str_replace('_', ' ', $status));

        $subject = 'Re: ' . $subjectText . ' - Alondes Dorm';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Update on Your Message</h2>'
            . '<p>Hi ' . e($senderName) . ',</p>'
            . '<p>Thank you for contacting Alondes Dorm. Your message regarding <strong>' . e($subjectText) . '</strong> has been updated.</p>'
            . ($adminResponse !== '' ? '<div style="padding:16px;border-left:4px solid #8fa61b;background:#f8fafc;border-radius:6px;line-height:1.7;"><strong>Our response:</strong><br>' . nl2br(e($adminResponse)) . '</div>' : '')
            . '<p style="color:#64748b;font-size:13px;margin-top:12px;">Status: <strong>' . e($statusLabel) . '</strong></p>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">If you have more questions, feel free to reply to this email.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyContactMessageResponse: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyProfileUpdate(string $email, string $firstName): bool {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        require_once __DIR__ . '/Services/MailService.php';

        $subject = 'Your Profile Was Updated - Alondes Dorm';
        $greeting = ($firstName !== '') ? 'Hi ' . e($firstName) . ',' : 'Hi,';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Profile Updated</h2>'
            . '<p>' . $greeting . '</p>'
            . '<p>Your profile on Alondes Dorm has been changed successfully.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Account Email</td><td style="padding:6px 8px;font-weight:600;">' . e($email) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">If you did not make this change, please contact the administrator immediately.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyProfileUpdate: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyTenantRemoved(array $student, string $roomLabel, string $reservationCode): bool {
        $email = (string)($student['email'] ?? '');
        if (trim($email) === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        require_once __DIR__ . '/Services/MailService.php';

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $firstName = (string)($student['first_name'] ?? '');
        $subject = 'Tenant Status Update - Alondes Dorm';

        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Account Status Update</h2>'
            . '<p>' . ($firstName !== '' ? 'Hi ' . e($firstName) . ',' : 'Hi,') . '</p>'
            . '<p>This is to inform you that you have been removed as a tenant at Alondes Dorm.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Tenant</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;">' . e($roomLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Reservation Code</td><td style="padding:6px 8px;">' . e($reservationCode) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">Your reservation has been cancelled. If you have questions or believe this was done in error, please contact the administration.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyTenantRemoved: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyReservationApproved(string $studentEmail, string $studentName, string $roomLabel, string $reservationCode): bool {
        $email = strtolower(trim($studentEmail));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        require_once __DIR__ . '/Services/MailService.php';

        $subject = 'Your Reservation Is Approved!';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#8fa61b;margin:0 0 16px;">Congratulations, Your Reservation Is Approved!</h2>'
            . '<p>Hi ' . e($studentName) . ',</p>'
            . '<p>Great news! Your reservation at Alondes Dorm has been approved.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Room</td><td style="padding:6px 8px;font-weight:600;">' . e($roomLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Reservation Code</td><td style="padding:6px 8px;">' . e($reservationCode) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">You can now proceed. If you have any questions, please contact the administration.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyReservationApproved: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyPaymentApproved(string $studentEmail, string $studentName, string $paymentCode, float $amount): bool {
        $email = strtolower(trim($studentEmail));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        require_once __DIR__ . '/Services/MailService.php';

        $subject = 'Your Payment Has Been Approved!';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#8fa61b;margin:0 0 16px;">Payment Approved!</h2>'
            . '<p>Hi ' . e($studentName) . ',</p>'
            . '<p>Great news! Your payment on Alondes Dorm has been approved.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Payment Code</td><td style="padding:6px 8px;font-weight:600;">' . e($paymentCode) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">If you have any questions, please contact the administration.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyPaymentApproved: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification to staff (managers/admins) when a payment is verified
     */
    protected function notifyStaffPaymentVerified(array $payment, string $studentName, string $newStatus, float $amount): void {
        require_once __DIR__ . '/Services/MailService.php';

        $staff = $this->db->fetchAll(
            "SELECT u.email, COALESCE(m.first_name, '') AS first_name
             FROM users u
             LEFT JOIN managers m ON m.user_id = u.id
             WHERE u.role IN ('super_admin','manager') AND u.status = 'active' AND u.email_verified = 1
               AND u.email IS NOT NULL AND TRIM(u.email) != ''"
        );
        if (empty($staff)) return;

        $paymentType = ucwords(str_replace('_', ' ', $payment['payment_type']));
        $paymentCode = $payment['payment_code'];
        $isPaid = $newStatus === 'paid';
        $isPartial = $newStatus === 'partially_paid';

        if ($isPaid) {
            $subject = "Payment Approved: {$paymentCode} - {$paymentType}";
            $heading = 'Payment Approved';
            $statusColor = '#8fa61b';
        } elseif ($isPartial) {
            $subject = "Payment Partially Paid: {$paymentCode} - {$paymentType}";
            $heading = 'Payment Partially Paid';
            $statusColor = '#f59e0b';
        } else {
            $subject = "Payment Cancelled: {$paymentCode} - {$paymentType}";
            $heading = 'Payment Cancelled';
            $statusColor = '#dc2626';
        }

        $mailer = new MailService();

        foreach ($staff as $sf) {
            $firstName = $sf['first_name'] ?? '';
            $email = $sf['email'];
            $greeting = ($firstName !== '') ? 'Hi ' . e((string)$firstName) . ',' : 'Hi,';

            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:' . $statusColor . ';margin:0 0 16px;">' . $heading . '</h2>'
                . '<p>' . $greeting . '</p>'
                . '<p>A payment has been ' . strtolower($newStatus) . ' by admin/manager.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Payment Code</td><td style="padding:6px 8px;">' . e($paymentCode) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Type</td><td style="padding:6px 8px;">' . e($paymentType) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">New Status</td><td style="padding:6px 8px;"><span style="background:' . $statusColor . ';color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;">' . e(ucfirst($newStatus)) . '</span></td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';

            try {
                $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyStaffPaymentVerified: failed to notify ' . $email . ' - ' . $e->getMessage());
            }
        }
    }

    protected function notifyPendingPaymentsEmail(int $studentUserId, array $room, array $payments): bool {
        $user = $this->db->fetch("SELECT u.email, s.first_name, s.last_name FROM users u JOIN students s ON s.user_id = u.id WHERE u.id = ?", [$studentUserId]);
        if (!$user) return false;

        $email = strtolower(trim((string)($user['email'] ?? '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        $studentName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $roomLabel = $room ? trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? '')) : 'the room';

        $rows = '';
        $total = 0.0;
        foreach ($payments as $p) {
            $label  = ucwords(str_replace('_', ' ', (string)($p['payment_type'] ?? 'payment')));
            $amount = (float)($p['amount'] ?? 0);
            $due    = !empty($p['due_date']) ? 'on ' . formatDate((string)$p['due_date']) : 'as soon as possible';
            $period = !empty($p['billing_period']) ? ' <span style="color:#475569;">(' . e((string)$p['billing_period']) . ')</span>' : '';
            $rows  .= '<tr>'
                . '<td style="padding:8px 8px;color:#475569;">' . e($label) . $period . '</td>'
                . '<td style="padding:8px 8px;font-weight:700;color:#dc2626;">' . formatCurrency($amount) . '</td>'
                . '<td style="padding:8px 8px;color:#475569;">' . $due . '</td>'
                . '</tr>';
            $total += $amount;
        }

        require_once __DIR__ . '/Services/MailService.php';

        $subject = 'Action Required: Payment Pending for ' . ($roomLabel !== 'the room' ? $roomLabel : 'Your Room') . ' - ' . formatCurrency($total);
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#b45309;margin:0 0 16px;">Payment Pending!</h2>'
            . '<p>Hi ' . e($studentName) . ',</p>'
            . '<p>Your reservation has been approved and there are payment(s) still <strong>pending</strong> on your account. Please settle them to complete your move-in:</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr style="background:#f8fafc;">'
            . '<th style="padding:8px;text-align:left;color:#0f172a;">Payment</th>'
            . '<th style="padding:8px;text-align:left;color:#0f172a;">Amount</th>'
            . '<th style="padding:8px;text-align:left;color:#0f172a;">Due</th>'
            . '</tr>'
            . $rows
            . '<tr>'
            . '<td style="padding:8px;color:#0f172a;font-weight:700;">Total</td>'
            . '<td style="padding:8px;color:#dc2626;font-weight:800;">' . formatCurrency($total) . '</td>'
            . '<td style="padding:8px;"></td>'
            . '</tr>'
            . '</table>'
            . '<p><a href="https://localhost/student_boarding_house/student/payments" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View My Payments</a></p>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">If you already paid, please ignore this email.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyPendingPaymentsEmail: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function notifyRefundDecision(string $studentEmail, string $studentName, string $refundCode, string $status, float $amount, string $adminNotes = ''): bool {
        $email = strtolower(trim($studentEmail));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        require_once __DIR__ . '/Services/MailService.php';

        $isApproved = $status === 'approved';
        $subject = $isApproved ? 'Your Refund Request Has Been Approved!' : 'Your Refund Request Has Been Rejected';
        $heading = $isApproved ? 'Refund Approved!' : 'Refund Request Rejected';
        $intro = $isApproved
            ? '<p>Great news! Your refund request on Alondes Dorm has been <strong>approved</strong>.</p>'
            : '<p>We are sorry, but your refund request on Alondes Dorm has been <strong>rejected</strong>.</p>';
        $notesHtml = $adminNotes !== '' ? '<p style="padding:10px 12px;background:#f1f5f9;border-radius:6px;color:#0f172a;">Note: ' . e($adminNotes) . '</p>' : '';

        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:' . ($isApproved ? '#8fa61b' : '#dc2626') . ';margin:0 0 16px;">' . $heading . '</h2>'
            . '<p>Hi ' . e($studentName) . ',</p>'
            . $intro
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Refund Code</td><td style="padding:6px 8px;font-weight:600;">' . e($refundCode) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;font-weight:600;">' . e(ucfirst($status)) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
            . '</table>'
            . $notesHtml
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">If you have any questions, please contact the administration.</p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('notifyRefundDecision: failed to notify ' . $email . ' - ' . $e->getMessage());
            return false;
        }
    }

    protected function sendWalkInVerificationCode(int $userId, string $email, string $firstName): bool {
        return $this->generateWalkInCode($email, $firstName) !== null;
    }

    protected function generateWalkInCode(string $email, string $firstName): ?string {
        require_once __DIR__ . '/Services/MailService.php';

        $email = strtolower(trim($email));
        if (session_status() === PHP_SESSION_NONE) session_start();

        // A refresh or an accidental double-click re-posts the Send button.
        // Keep the code that is still fresh instead of issuing (and mailing) a
        // new one, so the code the tenant already received stays valid.
        $existing = $_SESSION['walkin_verify'][$email] ?? null;
        if (
            is_array($existing)
            && (int)($existing['expires_at'] ?? 0) >= time()
            && (int)($existing['sent_at'] ?? 0) >= time() - self::WALKIN_CODE_RESEND_COOLDOWN
            && (int)($existing['attempts'] ?? 0) < self::WALKIN_CODE_MAX_ATTEMPTS
        ) {
            return (string)$existing['code'];
        }

        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $_SESSION['walkin_verify'][$email] = [
            'code'       => $code,
            'expires_at' => time() + self::WALKIN_CODE_TTL,
            'sent_at'    => time(),
            'attempts'   => 0,
        ];

        $subject = 'Walk-In Registration Verification Code';
        $body = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Your Walk-In Registration Code</h2>'
            . '<p>Hi ' . e($firstName) . ',</p>'
            . '<p>To complete your walk-in registration at Alondes Dorm, use this verification code:</p>'
            . '<p style="text-align:center;margin:20px 0;"><span style="display:inline-block;padding:14px 28px;background:#f0f9ff;border:2px dashed #8fa61b;border-radius:10px;font-size:32px;font-weight:800;letter-spacing:6px;color:#0f172a;">' . $code . '</span></p>'
            . '<p>This code is 6 digits and expires after 5 minutes. Do not share it with anyone.</p>'
            . '<p style="color:#64748b;font-size:13px;">If you did not request this, you can safely ignore this email.</p>'
            . '</div>';

        $mailer = new MailService();
        try {
            $ok = $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            if (!$ok) {
                unset($_SESSION['walkin_verify'][$email]);
                return null;
            }
        } catch (\Throwable $e) {
            unset($_SESSION['walkin_verify'][$email]);
            error_log('generateWalkInCode: email send failed for ' . $email . ' - ' . $e->getMessage());
            return null;
        }

        return $code;
    }

    protected function verifyWalkInCode(string $email, string $code): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $email = strtolower(trim($email));
        $entry = $_SESSION['walkin_verify'][$email] ?? null;
        if (!is_array($entry)) return false;
        if ((int)$entry['expires_at'] < time()) {
            unset($_SESSION['walkin_verify'][$email]);
            return false;
        }
        if ((int)($entry['attempts'] ?? 0) >= self::WALKIN_CODE_MAX_ATTEMPTS) {
            unset($_SESSION['walkin_verify'][$email]);
            return false;
        }
        if (!isset($entry['code']) || !hash_equals((string)$entry['code'], trim($code))) {
            $_SESSION['walkin_verify'][$email]['attempts'] = (int)($entry['attempts'] ?? 0) + 1;
            return false;
        }
        return true;
    }

    protected function processMonthlyPayments(): void {
        require_once __DIR__ . '/Services/MonthlyBillingService.php';
        $monthly = new MonthlyBillingService();
        $monthly->run();

        require_once __DIR__ . '/Services/PaymentReminderService.php';
        $reminders = new PaymentReminderService();
        $reminders->run();
    }

    /**
     * Remove duplicate Monthly Rent records from a payment list so each
     * student shows only ONE Monthly Rent row per billing period. The
     * period falls back to the due-date month and then to the created
     * month for manually recorded bills (walk-in/custom) that have no
     * billing_period. When duplicates exist the most meaningful record
     * wins: active over cancelled/refunded, paid/acted over untouched,
     * newer over older.
     */
    protected function dedupeMonthlyRentPayments(array $payments): array {
        $result = [];
        $slotByKey = [];
        $rankByKey = [];

        foreach ($payments as $p) {
            if (($p['payment_type'] ?? '') !== 'monthly_rent') {
                $result[] = $p;
                continue;
            }

            $period = !empty($p['billing_period'])
                ? substr((string)$p['billing_period'], 0, 7)
                : (!empty($p['due_date'])
                    ? substr((string)$p['due_date'], 0, 7)
                    : (!empty($p['created_at']) ? substr((string)$p['created_at'], 0, 7) : ''));

            if ($period === '' || !isset($p['student_id'])) {
                $result[] = $p;
                continue;
            }

            $key = (int)$p['student_id'] . '|' . $period;
            $status = (string)($p['status'] ?? '');
            $rank = [
                in_array($status, ['cancelled', 'refunded'], true) ? 0 : 1,
                ((float)($p['amount_paid'] ?? 0) > 0 || in_array($status, ['paid', 'partially_paid', 'overdue', 'due_today', 'upcoming'], true)) ? 1 : 0,
                strtotime((string)($p['created_at'] ?? '')) ?: 0,
            ];

            if (!isset($slotByKey[$key])) {
                $slotByKey[$key] = count($result);
                $rankByKey[$key] = $rank;
                $result[] = $p;
            } elseif ($rank > $rankByKey[$key]) {
                $result[$slotByKey[$key]] = $p;
                $rankByKey[$key] = $rank;
            }
        }

        return $result;
    }

    // â”€â”€â”€ Shared Student Methods (used by AdminController & ManagerController) â”€â”€â”€

    protected function getStudents(string $search = '', string $status = ''): array {
        $where = "1";
        $params = [];
        if ($search) {
            $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ? OR u.email LIKE ?)";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        }
        if ($status && in_array($status, ['active', 'inactive', 'suspended'])) {
            $where .= " AND u.status = ?";
            $params[] = $status;
        }

        $students = $this->db->fetchAll(
            "SELECT s.*, u.email, u.status as user_status
             FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE {$where}
             ORDER BY s.first_name ASC, s.last_name ASC",
            $params
        );

        $stats = $this->db->fetchAll(
            "SELECT u.status, COUNT(*) as cnt FROM students s JOIN users u ON s.user_id = u.id GROUP BY u.status"
        );
        $counts = ['total' => 0, 'active' => 0, 'inactive' => 0, 'suspended' => 0];
        foreach ($stats as $row) {
            $counts[$row['status']] = (int)$row['cnt'];
            $counts['total'] += (int)$row['cnt'];
        }

        return ['students' => $students, 'counts' => $counts];
    }

    protected function getTenants(string $search = ''): array {
        $where = "r.status = 'approved'";
        $params = [];
        if ($search) {
            $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ? OR u.email LIKE ?)";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        }

        $tenants = $this->db->fetchAll(
            "SELECT s.*, u.email, u.status as user_status,
                    rm.room_number, rm.room_name,
                    r.id as reservation_id, r.reservation_code, r.move_in_date, r.approved_at
             FROM reservations r
             JOIN students s ON r.student_id = s.id
             JOIN users u ON s.user_id = u.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE {$where}
             ORDER BY r.approved_at ASC, s.first_name ASC, s.last_name ASC",
            $params
        );

        return ['tenants' => $tenants, 'total' => count($tenants)];
    }

    /**
     * Automatically deduct (mark as refunded) the tenant's paid payments of the
     * type matching the refund request type, up to the refund amount.
     */
    protected function applyRefundDeduction(array $refund): array {
        $refundType = $refund['refund_type'] ?? 'monthly';
        $paymentTypes = $refundType === 'advance' ? ['advance_payment'] : ($refundType === 'all' ? ['monthly_rent', 'advance_payment'] : ['monthly_rent']);
        $placeholders = implode(',', array_fill(0, count($paymentTypes), '?'));
        $remaining = (float)$refund['amount'];
        $deducted = 0;
        $deductedAmount = 0.0;
        $refundCode = $refund['refund_code'] ?? '';
        $refundId = isset($refund['id']) ? (int)$refund['id'] : null;

        $payments = $this->db->fetchAll(
            "SELECT * FROM payments
             WHERE student_id = ? AND payment_type IN ({$placeholders}) AND status = 'paid'
             ORDER BY paid_at ASC, id ASC",
            array_merge([$refund['student_id']], $paymentTypes)
        );

        foreach ($payments as $p) {
            if ($remaining <= 0.001) break;

            $amountPaid = (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
            $alreadyRefunded = (float)($p['refunded_amount'] ?? 0);
            $refundable = max(0, $amountPaid - $alreadyRefunded);
            if ($refundable <= 0.001) continue;

            $applied = min($remaining, $refundable);
            $newRefunded = round($alreadyRefunded + $applied, 2);
            $fullyRefunded = $newRefunded >= $amountPaid - 0.001;

            $this->db->update('payments', [
                'status' => $fullyRefunded ? 'refunded' : 'paid',
                'refunded_amount' => $newRefunded,
                'refund_request_id' => $refundId,
                'notes' => 'Refunded ' . formatCurrency($applied) . ' via ' . $refundCode . ' (approved refund)',
            ], "id = ?", [$p['id']]);

            $this->db->insert('payment_history', [
                'payment_id' => $p['id'],
                'action' => 'refund',
                'old_status' => 'paid',
                'new_status' => $fullyRefunded ? 'refunded' : 'paid',
                'notes' => 'Refunded ' . formatCurrency($applied) . ' via ' . $refundCode . ($fullyRefunded ? '' : ' (partial)'),
                'performed_by' => $_SESSION['user_id'] ?? null,
            ]);

            $deducted++;
            $deductedAmount += $applied;
            $remaining -= $applied;
        }

        return ['count' => $deducted, 'amount' => round($deductedAmount, 2)];
    }

    /**
     * Terminates a tenant's stay after an All Payments Refund is approved:
     * cancels every active (pending/approved) reservation using the same
     * rules as a manual cancellation (status = cancelled + cancelled_at),
     * then recomputes each affected room's current occupancy, availability
     * status and free slots from the live approved reservations.
     */
    protected function terminateTenantOnAllPaymentRefund(array $refund): array {
        $studentId = (int)$refund['student_id'];
        $refundCode = (string)($refund['refund_code'] ?? '');

        $active = $this->db->fetchAll(
            "SELECT * FROM reservations WHERE student_id = ? AND status IN ('pending','approved') ORDER BY id ASC",
            [$studentId]
        );

        $cancelledCount = 0;
        $roomIds = [];
        foreach ($active as $res) {
            $wasApproved = ($res['status'] ?? '') === 'approved';

            $note = 'Automatically terminated: All Payments Refund ' . $refundCode . ' approved.';
            $existingNotes = trim((string)($res['admin_notes'] ?? ''));
            if ($existingNotes !== '') {
                $note = $existingNotes . "\n" . $note;
            }

            $this->db->update('reservations', [
                'status' => 'cancelled',
                'cancelled_at' => serverDateTime(),
                'admin_notes' => $note,
            ], "id = ?", [(int)$res['id']]);

            $cancelledCount++;
            if ($wasApproved && !empty($res['room_id'])) {
                $roomIds[(int)$res['room_id']] = true;
            }
        }

        foreach (array_keys($roomIds) as $roomId) {
            $this->updateRoomOccupancy($roomId);
        }

        return ['reservations' => $cancelledCount, 'rooms' => array_keys($roomIds)];
    }

    /**
     * Cancels every still-unpaid bill of a departing tenant (All Payments
     * Refund approved) so Manage Payments / My Payments show truthful
     * statuses instead of collectible debts that no longer exist. Billing
     * services already skip cancelled payments, so late fees and reminders
     * stop immediately.
     */
    protected function cancelUnpaidPaymentsAfterTermination(int $studentId, string $refundCode): array {
        $unpaid = $this->db->fetchAll(
            "SELECT * FROM payments WHERE student_id = ? AND status IN ('pending','upcoming','due_today','overdue')",
            [$studentId]
        );

        $count = 0;
        $amount = 0.0;
        foreach ($unpaid as $pay) {
            $note = 'Automatically cancelled: tenant terminated via All Payments Refund ' . $refundCode . '.';
            $existingNotes = trim((string)($pay['notes'] ?? ''));
            if ($existingNotes !== '') {
                $note = $existingNotes . "\n" . $note;
            }

            $this->db->update('payments', [
                'status' => 'cancelled',
                'notes' => $note,
            ], "id = ?", [(int)$pay['id']]);

            $this->db->insert('payment_history', [
                'payment_id' => $pay['id'],
                'action' => 'cancel',
                'old_status' => $pay['status'],
                'new_status' => 'cancelled',
                'notes' => 'Cancelled automatically: All Payments Refund ' . $refundCode . ' approved.',
                'performed_by' => $_SESSION['user_id'] ?? null,
            ]);

            $count++;
            $amount += (float)$pay['amount'] + (float)($pay['late_fee'] ?? 0);
        }

        return ['count' => $count, 'amount' => round($amount, 2)];
    }

    /**
     * Archives and permanently removes a departing tenant's boarding records
     * (reservations, payments, receipts, payment history and their
     * notifications) after an All Payments Refund is approved, so My
     * Reservations / My Payments / My Receipts start completely clean.
     * Records are archived first and stay restorable via the Archive module;
     * uploaded files remain on disk so restored records keep working. The
     * tenant account and the approved refund request itself are kept.
     */
    protected function deleteTenantRecordsAfterAllRefund(int $studentId): array {
        $reservations = $this->db->fetchAll("SELECT * FROM reservations WHERE student_id = ?", [$studentId]);
        $payments = $this->db->fetchAll("SELECT * FROM payments WHERE student_id = ?", [$studentId]);

        $result = ['reservations' => 0, 'payments' => 0, 'receipts' => 0];
        if (!$reservations && !$payments) {
            return $result;
        }

        $paymentIds = array_map('intval', array_column($payments, 'id'));
        if ($paymentIds) {
            $ph = implode(',', array_fill(0, count($paymentIds), '?'));
            $result['receipts'] = (int)$this->db->fetch(
                "SELECT COUNT(*) as c FROM receipts WHERE payment_id IN ({$ph})",
                $paymentIds
            )['c'];
        }

        // Archive everything first (recoverable via the Archive module).
        foreach ($reservations as $res) {
            $this->archiveRecord('reservation', (int)$res['id'], $res);
        }
        foreach ($payments as $pay) {
            $this->archiveRecord('payment', (int)$pay['id'], $pay, [
                'payment_history' => $this->db->fetchAll("SELECT * FROM payment_history WHERE payment_id = ?", [$pay['id']]),
                'receipts'        => $this->db->fetchAll("SELECT * FROM receipts WHERE payment_id = ?", [$pay['id']]),
            ]);
        }

        // Delete children first, then parents (FK-safe).
        if ($paymentIds) {
            $ph = implode(',', array_fill(0, count($paymentIds), '?'));
            $this->db->query("DELETE FROM receipts WHERE payment_id IN ({$ph})", $paymentIds);
            $this->db->query("DELETE FROM payment_history WHERE payment_id IN ({$ph})", $paymentIds);
            $this->db->query("DELETE FROM notifications WHERE reference_type = 'payment' AND reference_id IN ({$ph})", $paymentIds);
            $this->db->delete('payments', "student_id = ?", [$studentId]);
        }
        if ($reservations) {
            $resIds = array_map('intval', array_column($reservations, 'id'));
            $rh = implode(',', array_fill(0, count($resIds), '?'));
            $this->db->query("DELETE FROM notifications WHERE reference_type = 'reservation' AND reference_id IN ({$rh})", $resIds);
            $this->db->delete('reservations', "student_id = ?", [$studentId]);
        }

        $result['reservations'] = count($reservations);
        $result['payments'] = count($payments);
        return $result;
    }

    protected function getStudentById(int $id): ?array {
        return $this->db->fetch(
            "SELECT s.*, u.email, u.status as user_status FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$id]
        );
    }

    protected function getStudentDetailData(int $id): array {
        $reservation = $this->db->fetch(
            "SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent, rm.advance_payment FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.student_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 1",
            [$id]
        );

        // Calculate due dates for active reservation
        $nextDueDate = null;
        $finalDueDate = null;
        $moveOutMonth = null;
        if ($reservation && !empty($reservation['move_in_date'])) {
            $studentId = $reservation['student_id'];
            $monthlyRent = (float)($reservation['monthly_rent'] ?? 0);
            $advancePayment = (float)($reservation['advance_payment'] ?? 0);
            $moveInDate = new DateTime($reservation['move_in_date']);
            $expectedDuration = (int)($reservation['expected_duration'] ?? 0);
            
            // Get all payments for this student for this reservation
            $paymentsForCalc = $this->db->fetchAll(
                "SELECT * FROM payments WHERE student_id = ? AND reservation_id = ? AND payment_type IN ('monthly_rent', 'advance_payment') AND status IN ('paid', 'partially_paid') ORDER BY paid_at ASC",
                [$studentId, $reservation['id']]
            );
            
            $totalMonthlyPaid = 0;
            $totalAdvancePaid = 0;
            
            foreach ($paymentsForCalc as $p) {
                $amountPaid = (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
                if ($p['payment_type'] === 'advance_payment') {
                    $totalAdvancePaid += $amountPaid;
                }
                if ($p['payment_type'] === 'monthly_rent') {
                    $totalMonthlyPaid += $amountPaid;
                }
            }
            
            // Calculate months covered by advance payment (advance covers first month(s))
            $monthsFromAdvance = $monthlyRent > 0 ? floor($totalAdvancePaid / $monthlyRent) : 0;
            
            // Calculate months covered by monthly rent payments
            $monthsFromMonthly = $monthlyRent > 0 ? floor($totalMonthlyPaid / $monthlyRent) : 0;
            
            // Total months fully paid = advance months + monthly rent months
            $totalMonthsPaid = $monthsFromAdvance + $monthsFromMonthly;
            
            // ============================================================
            // NEXT DUE DATE: When the NEXT monthly payment is due
            // Logic: move_in_date -> first day of next month -> add months fully paid
            // Example: Move-in June 15, 3 months paid -> Next due Oct 1
            // ============================================================
            $nextDue = clone $moveInDate;
            $nextDue->modify('first day of next month'); // First rent due date
            $nextDue->modify("+{$totalMonthsPaid} months"); // Add months already paid
            
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            // If calculated due date is in the past, next payment is due next month
            if ($nextDue <= $today) {
                $nextDue->modify('first day of next month');
            }
            
            $nextDueDate = $nextDue->format('Y-m-d');
            
            // ============================================================
            // FINAL DUE DATE (Lease End): Contractual end of tenancy
            // Logic: move_in_date + expected_duration months
            // This is when the lease contractually ends
            // ============================================================
            if ($expectedDuration > 0) {
                $finalDue = clone $moveInDate;
                $finalDue->modify("+{$expectedDuration} months"); // Lease end date
                // Move-out is typically the last day of the lease month
                // But for display, show the first day of the month AFTER lease ends
                $finalDueDate = $finalDue->format('Y-m-d');
                $moveOutMonth = $finalDue->format('F Y');
            } else {
                // No expected duration set - fallback to next due date logic
                $finalDueDate = $nextDueDate;
                $moveOutMonth = $nextDue->format('F Y');
            }
            
            // Add calculated fields to reservation
            $reservation['next_due_date'] = $nextDueDate;
            $reservation['final_due_date'] = $finalDueDate;
            $reservation['move_out_month'] = $moveOutMonth;
            $reservation['months_from_advance'] = $monthsFromAdvance;
            $reservation['months_from_monthly'] = $monthsFromMonthly;
            $reservation['total_months_paid'] = $totalMonthsPaid;
        }

        // Get all payments for payment history with totals
        $allPayments = $this->db->fetchAll(
            "SELECT p.*, COALESCE(rm.room_name, (SELECT arn.room_name FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_name,
                    COALESCE(rm.room_number, (SELECT arn.room_number FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_number
             FROM payments p
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE p.student_id = ? ORDER BY p.created_at DESC",
            [$id]
        );
        
        $recentPayments = $this->db->fetchAll(
            "SELECT p.*, COALESCE(rm.room_name, (SELECT arn.room_name FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_name,
                    COALESCE(rm.room_number, (SELECT arn.room_number FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_number
             FROM payments p
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE p.student_id = ? ORDER BY p.created_at DESC LIMIT 10",
            [$id]
        );
        
        // Calculate payment totals
        $totalPaid = 0;
        $totalPending = 0;
        $totalOverdue = 0;
        $totalRefunded = 0;
        foreach ($allPayments as $p) {
            $amt = (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
            if (in_array($p['status'], ['paid', 'partially_paid'])) {
                $totalPaid += $amt;
            } elseif ($p['status'] === 'pending') {
                $totalPending += $amt;
            } elseif ($p['status'] === 'overdue') {
                $totalOverdue += $amt;
            } elseif ($p['status'] === 'refunded') {
                $totalRefunded += $amt;
            }
        }
        $netTotal = $totalPaid - $totalRefunded;

        $guardian = $this->db->fetch(
            "SELECT * FROM guardians WHERE student_id = ? ORDER BY id ASC LIMIT 1",
            [$id]
        );
        return [
            'reservation' => $reservation, 
            'payments' => $recentPayments,
            'all_payments' => $allPayments,
            'payment_totals' => [
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'total_overdue' => $totalOverdue,
                'total_refunded' => $totalRefunded,
                'net_total' => $netTotal,
                'count' => count($allPayments)
            ],
            'guardian' => $guardian
        ];
    }

    protected function handleStudentStatusUpdate(array $student, string $baseUrl): void {
        $id = $student['id'];
        $newStatus = $this->input('user_status', '');
        $validStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->flash('error', 'Invalid status.');
            $this->redirect("{$baseUrl}/student/{$id}");
            return;
        }

        $oldStatus = $student['user_status'];
        $this->db->update('users', ['status' => $newStatus], "id = ?", [$student['user_id']]);

        if ($newStatus !== $oldStatus) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Account Status Updated',
                'message' => "Your account status has been changed to " . ucfirst($newStatus) . ".",
                'type' => 'system',
            ]);
        }

        $this->logActivity('update_student_status', "Student {$student['first_name']} {$student['last_name']} status: {$oldStatus} â†’ {$newStatus}");
        $this->flash('success', 'Student status updated to ' . ucfirst($newStatus) . '.');
        $this->redirect("{$baseUrl}/student/{$id}");
    }

    protected function processStudentDelete(string $baseUrl): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect("{$baseUrl}/students");
            return;
        }
        $id = (int)$this->input('id');
        $student = $this->db->fetch(
            "SELECT s.*, u.id as uid, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$id]
        );
        if (!$student) {
            $this->flash('error', 'Student not found.');
            $this->redirect("{$baseUrl}/students");
            return;
        }

        $payments = $this->db->fetchAll("SELECT * FROM payments WHERE student_id = ?", [$id]);
        $payIds = array_column($payments, 'id');
        $paymentHistory = [];
        $receipts = [];
        if ($payIds) {
            $ph = implode(',', array_fill(0, count($payIds), '?'));
            $paymentHistory = $this->db->fetchAll("SELECT * FROM payment_history WHERE payment_id IN ({$ph})", $payIds);
            $receipts = $this->db->fetchAll("SELECT * FROM receipts WHERE payment_id IN ({$ph})", $payIds);
        }

        // Capture the room(s) this tenant occupies (approved reservations),
        // including their images and amenities, so a restore brings back the
        // complete room details and the room can be re-created if it was deleted.
        $reservations = $this->db->fetchAll("SELECT * FROM reservations WHERE student_id = ?", [$id]);
        $roomsData = [];
        $roomImages = [];
        $roomAmenities = [];
        foreach ($reservations as $resv) {
            if (($resv['status'] ?? '') !== 'approved' || empty($resv['room_id'])) {
                continue;
            }
            $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [(int)$resv['room_id']]);
            if (!$room) {
                continue;
            }
            $roomsData[$room['id']] = $room;
            $roomImages = array_merge($roomImages, $this->db->fetchAll("SELECT * FROM room_images WHERE room_id = ?", [(int)$room['id']]));
            $roomAmenities = array_merge($roomAmenities, $this->db->fetchAll("SELECT * FROM room_amenities WHERE room_id = ?", [(int)$room['id']]));
        }
        $roomsData = array_values($roomsData);

        $archived = $this->archiveRecord('student', $id,
            $this->db->fetch("SELECT * FROM students WHERE id = ?", [$id]),
            [
                'users'                => [$this->db->fetch("SELECT * FROM users WHERE id = ?", [$student['uid']])],
                'guardians'            => $this->db->fetchAll("SELECT * FROM guardians WHERE student_id = ?", [$id]),
                'password_history'     => $this->db->fetchAll("SELECT * FROM password_history WHERE user_id = ?", [$student['uid']]),
                'reservations'         => $reservations,
                'payments'             => $payments,
                'payment_history'      => $paymentHistory,
                'receipts'             => $receipts,
                'complaints'           => $this->db->fetchAll("SELECT * FROM complaints WHERE student_id = ?", [$id]),
                'maintenance_requests' => $this->db->fetchAll("SELECT * FROM maintenance_requests WHERE student_id = ?", [$id]),
                'feedback'             => $this->db->fetchAll("SELECT * FROM feedback WHERE student_id = ?", [$id]),
                'refund_requests'      => $this->db->fetchAll("SELECT * FROM refund_requests WHERE student_id = ?", [$id]),
                'rooms'                => $roomsData,
                'room_images'          => $roomImages,
                'room_amenities'       => $roomAmenities,
            ]
        );
        if (!$archived) {
            $this->flash('error', 'Could not archive this student. Deletion cancelled.');
            $this->redirect("{$baseUrl}/students");
            return;
        }

        $this->permanentDeleteStudent($student, ['id' => $student['uid'], 'email' => $student['email']], true);

        $this->logActivity('delete_student', "Student {$student['first_name']} {$student['last_name']} ({$student['email']}) permanently deleted");
        $notified = $this->notifyRecordDeleted('student', 'Student ' . trim($student['first_name'] . ' ' . $student['last_name']));
        $this->flash('success', $this->recordDeletedFlash('Student ' . trim($student['first_name'] . ' ' . $student['last_name']), $notified) . ' The record was moved to Archive & Recovery.');
        $this->redirect("{$baseUrl}/students");
    }

    // ─── Remove Tenant (shared by AdminController & ManagerController) ───

    protected function processRemoveTenant(string $baseUrl): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect("{$baseUrl}/tenants");
            return;
        }
        $studentId = (int)$this->input('id');
        $student = $this->db->fetch(
            "SELECT s.*, u.id as uid, u.email, u.status as user_status FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$studentId]
        );
        if (!$student) {
            $this->flash('error', 'Student not found.');
            $this->redirect("{$baseUrl}/tenants");
            return;
        }

        $reservation = $this->db->fetch(
            "SELECT * FROM reservations WHERE student_id = ? AND status = 'approved' ORDER BY approved_at DESC LIMIT 1",
            [$studentId]
        );
        if (!$reservation) {
            $this->flash('error', 'This student has no active reservation to remove.');
            $this->redirect("{$baseUrl}/tenants");
            return;
        }

        $this->db->update('reservations', [
            'status' => 'cancelled',
            'cancelled_at' => serverDateTime(),
            'admin_notes' => 'Removed from tenant by ' . ($_SESSION['role'] ?? 'admin') . ' on ' . serverDateTime(),
        ], "id = ?", [$reservation['id']]);

        $this->updateRoomOccupancy((int)$reservation['room_id']);

        $roomInfo = $this->db->fetch("SELECT room_number, room_name FROM rooms WHERE id = ?", [(int)$reservation['room_id']]);
        $roomLabel = $roomInfo ? ($roomInfo['room_number'] . ' - ' . $roomInfo['room_name']) : 'their assigned room';

        $this->db->insert('notifications', [
            'user_id' => $student['uid'],
            'title' => 'Tenant Status Removed',
            'message' => "You have been removed as a tenant. Your reservation ({$reservation['reservation_code']}) for {$roomLabel} has been cancelled. Please contact the administration for more details.",
            'type' => 'system',
            'reference_id' => $reservation['id'],
            'reference_type' => 'reservation',
        ]);

        $notified = $this->notifyTenantRemoved($student, $roomLabel, (string)$reservation['reservation_code']);

        $this->logActivity('remove_tenant', "Removed tenant {$student['first_name']} {$student['last_name']} ({$student['email']}) — reservation {$reservation['reservation_code']} cancelled");
        $this->flash('success', 'Tenant ' . e($student['first_name'] . ' ' . $student['last_name']) . ' has been removed. Their reservation has been cancelled and the room has been freed.' . ($notified ? ' Email notification sent to ' . $student['email'] . '.' : ' We could not send the email notification right now.'));
        $this->redirect("{$baseUrl}/tenants");
    }

    // ─── Walk-In Registration (shared by AdminController & ManagerController) ───

    protected function generateStudentIdNumber(): string {
        $year = serverDate('Y');
        $last = $this->db->fetch("SELECT student_id_number FROM students WHERE student_id_number LIKE ? ORDER BY id DESC LIMIT 1", ["STU-{$year}-%"]);
        if ($last && preg_match('/STU-\d{4}-(\d+)$/', $last['student_id_number'], $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        return sprintf('STU-%s-%03d', $year, $next);
    }

    protected function generateUniqueUsername(string $email): string {
        $base = strtolower(preg_replace('/[^a-z0-9_.]/', '', strstr($email, '@', true) ?: $email));
        $base = $base !== '' ? $base : 'tenant';
        $base = substr($base, 0, 18);
        $username = $base;
        $i = 1;
        while ($this->db->fetch("SELECT id FROM users WHERE username = ?", [$username])) {
            $suffix = (string)$i;
            $username = substr($base, 0, 18 - strlen($suffix)) . $suffix;
            $i++;
        }
        return $username;
    }

    protected function generateTemporaryPassword(): string {
        return bin2hex(random_bytes(6));
    }

    /**
     * Detects whether a PDOException was caused by a duplicate email/username
     * on the users table (MySQL error 1062). Returns 'email', 'username', or null.
     * This is the last line of defense against race conditions where two
     * submissions pass the SELECT check before either INSERT commits.
     */
    protected function getUserDuplicateField(\PDOException $e): ?string {
        $sqlState = $e->getCode();
        $driverCode = $e->errorInfo[1] ?? null;
        if ($sqlState !== '23000' && (int)$driverCode !== 1062) {
            return null;
        }
        $message = strtolower(($e->getMessage() ?? '') . ' ' . (string)($e->errorInfo[2] ?? ''));
        if (str_contains($message, 'uk_users_email') || str_contains($message, "key 'email'")) {
            return 'email';
        }
        if (str_contains($message, 'uk_users_username') || str_contains($message, "key 'username'")) {
            return 'username';
        }
        return null;
    }

    /**
     * Exact user-facing messages for duplicate account credentials.
     */
    protected function duplicateUserMessage(string $field): string {
        return $field === 'username'
            ? 'This username is already registered.'
            : 'This Gmail is already registered.';
    }

    /**
     * Server-side uniqueness validation for registration inputs.
     * Always runs both checks so the user sees every problem at once.
     *
     * @return array<string> Duplicate-related error messages (empty when unique).
     */
    protected function checkDuplicateUserCredentials(string $email, string $username): array {
        $errors = [];
        if ($email !== '' && isValidGmailEmail($email)) {
            if ($this->db->fetch("SELECT id FROM users WHERE email = ?", [$email])) {
                $errors[] = $this->duplicateUserMessage('email');
            }
        }
        if ($username !== '') {
            if ($this->db->fetch("SELECT id FROM users WHERE username = ?", [$username])) {
                $errors[] = $this->duplicateUserMessage('username');
            }
        }
        return $errors;
    }

    /**
     * Creates the bills for an approved reservation: the first monthly rent,
     * plus the advance payment for the tenant's very first reservation.
     * Also applies any payment credits from previously deleted reservations.
     */
    protected function createApprovedReservationBilling(int $studentUserId, array $reservation, array $room): void {
        $student = $this->db->fetch("SELECT id FROM students WHERE user_id = ?", [$studentUserId]);
        if (!$student) return;
        $studentId = (int)$student['id'];

        $monthlyRent = (float)($room['monthly_rent'] ?? 0);
        if ($monthlyRent <= 0) return;

        $duration = (int)($reservation['expected_duration'] ?? 1);
        $moveInDate = $reservation['move_in_date'] ?: serverDate('Y-m-d');

        $taxRateRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'tax_rate'");
        $taxRate = $taxRateRow ? (float)$taxRateRow['setting_value'] : 0;

        $totalAmount = round($monthlyRent * $duration, 2);
        if ($taxRate > 0) {
            $totalAmount = round($totalAmount + ($totalAmount * $taxRate / 100), 2);
        }

        $finalDueDate = $moveInDate;
        $billingPeriod = substr($moveInDate, 0, 7);

        $existing = $this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND reservation_id = ? AND payment_type = 'full_payment'",
            [$studentId, $reservation['id']]
        );
        if ($existing) return;

        $credits = $this->db->fetchAll(
            "SELECT * FROM student_reservation_credits WHERE student_id = ? ORDER BY created_at ASC",
            [$studentId]
        );

        $remainingCredits = [];
        $totalCreditAmount = 0.0;
        foreach ($credits as $credit) {
            $remainingCredits[$credit['payment_type']] = (float)$credit['total_amount'];
            $totalCreditAmount += (float)$credit['total_amount'];
        }

        $applyCredit = function (string $paymentType, float $amount) use (&$remainingCredits) {
            if (isset($remainingCredits[$paymentType]) && $remainingCredits[$paymentType] > 0) {
                $creditAmount = min($remainingCredits[$paymentType], $amount);
                $remainingCredits[$paymentType] -= $creditAmount;
                return $creditAmount;
            }
            return 0.0;
        };

        $fullPaymentCredited = $applyCredit('full_payment', $totalAmount);
        $advanceCredited = $applyCredit('advance_payment', $totalAmount);
        $monthlyCredited = $applyCredit('monthly_rent', $totalAmount);
        $reservationFeeCredited = $applyCredit('reservation_fee', $totalAmount);

        $totalCredited = $fullPaymentCredited + $advanceCredited + $monthlyCredited + $reservationFeeCredited;
        $amountPaid = $totalCredited > 0 ? $totalCredited : 0;
        $status = $totalCredited > 0 ? 'paid' : 'pending';
        $paymentMethod = $totalCredited > 0 ? 'credit' : null;
        $paidAt = $totalCredited > 0 ? serverDateTime() : null;
        $referenceNumber = $totalCredited > 0 ? 'AUTO-CREDIT-' . $reservation['reservation_code'] : null;

        $paymentId = $this->db->insert('payments', [
            'payment_code' => generateCode('PAY'),
            'student_id' => $studentId,
            'reservation_id' => $reservation['id'],
            'payment_type' => 'full_payment',
            'amount' => $totalAmount,
            'late_fee' => 0,
            'amount_paid' => $amountPaid,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'reference_number' => $referenceNumber,
            'due_date' => $finalDueDate,
            'billing_period' => $billingPeriod,
            'penalty_applied' => 0,
            'paid_at' => $paidAt,
            'notes' => 'Full payment for reservation ' . $reservation['reservation_code'] . ' (' . $duration . ' months × ' . formatCurrency($monthlyRent) . ')' . ($taxRate > 0 ? ' + ' . $taxRate . '% tax' : '') . ($totalCredited > 0 ? ' — AUTO-PAID from previous reservation credits: ' . formatCurrency($totalCredited) : ''),
        ]);

        $this->db->insert('payment_history', [
            'payment_id' => $paymentId,
            'action' => $totalCredited > 0 ? 'auto_paid_from_credit' : 'auto_created',
            'old_status' => null,
            'new_status' => $status,
            'notes' => ($totalCredited > 0 ? 'Full payment auto-paid from deleted reservation credits: ' . formatCurrency($totalCredited) : 'Full payment (Duration × Monthly Rent) auto-created on reservation approval'),
            'performed_by' => 0,
        ]);

        if ($totalCredited > 0) {
            $usedCredits = [];
            foreach ($credits as $credit) {
                $creditType = $credit['payment_type'];
                $creditAmount = (float)$credit['total_amount'];
                if ($creditAmount <= 0) continue;
                
                $useAmount = 0;
                if ($creditType === 'full_payment' && $fullPaymentCredited > 0) {
                    $useAmount = min($fullPaymentCredited, $creditAmount);
                    $fullPaymentCredited -= $useAmount;
                } elseif ($creditType === 'advance_payment' && $advanceCredited > 0) {
                    $useAmount = min($advanceCredited, $creditAmount);
                    $advanceCredited -= $useAmount;
                } elseif ($creditType === 'monthly_rent' && $monthlyCredited > 0) {
                    $useAmount = min($monthlyCredited, $creditAmount);
                    $monthlyCredited -= $useAmount;
                } elseif ($creditType === 'reservation_fee' && $reservationFeeCredited > 0) {
                    $useAmount = min($reservationFeeCredited, $creditAmount);
                    $reservationFeeCredited -= $useAmount;
                }
                
                if ($useAmount > 0) {
                    $usedCredits[] = $credit['id'];
                    $newRemaining = $creditAmount - $useAmount;
                    if ($newRemaining <= 0.001) {
                        $this->db->delete('student_reservation_credits', "id = ?", [$credit['id']]);
                    } else {
                        $this->db->update('student_reservation_credits', [
                            'total_amount' => round($newRemaining, 2),
                            'amount' => round($newRemaining - (float)$credit['late_fee'], 2),
                        ], "id = ?", [$credit['id']]);
                    }
                }
            }

            $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$studentId]);
            if ($student) {
                $this->db->insert('notifications', [
                    'user_id' => $student['user_id'],
                    'title' => 'Payment Credits Applied',
                    'message' => "Your new reservation {$reservation['reservation_code']} has been approved and automatically paid using " . formatCurrency($totalCredited) . " in credits from your previous deleted reservation(s).",
                    'type' => 'payment',
                    'reference_id' => $paymentId,
                    'reference_type' => 'payment',
                ]);
            }
        }
    }

    /**
     * Repopulates the walk-in form after a failed POST so no typed value is lost.
     * File inputs are excluded: browsers clear them on every navigation.
     *
     * @return array<string,mixed>
     */
    protected function walkInOldInput(): array {
        $t = fn(string $key, string $default = ''): string => trim((string)$this->input($key, $default));
        return [
            'email' => strtolower($t('email')), 'username' => strtolower($t('username')),
            'verification_code' => $t('verification_code'), 'password' => (string)$this->input('password', ''),
            'first_name' => $t('first_name'), 'middle_name' => $t('middle_name'), 'last_name' => $t('last_name'), 'suffix' => $t('suffix'),
            'gender' => $t('gender'), 'date_of_birth' => $t('date_of_birth'), 'civil_status' => $t('civil_status'), 'nationality' => $t('nationality', 'Filipino'),
            'school_university' => $t('school_university'), 'course_program' => $t('course_program'),
            'course_program_other' => $t('course_program_other'), 'year_level' => $t('year_level'),
            'phone' => $t('phone'), 'house_unit' => $t('house_unit'), 'street' => $t('street'), 'barangay' => $t('barangay'),
            'municipality_city' => $t('municipality_city'), 'province' => $t('province', 'Cebu'), 'zip_code' => $t('zip_code'),
            'room_id' => (int)$this->input('room_id', 0), 'move_in_date' => $t('move_in_date'), 'move_in_time' => $t('move_in_time'), 'expected_duration' => $t('expected_duration', '1'),
            'collect_payment' => (string)$this->input('collect_payment', '') === '1' ? '1' : '',
            'payment_method' => $t('payment_method'),
            'payment_received' => $t('payment_received'),
            'payment_reference' => $t('payment_reference'),
            'payment_notes' => trim((string)$this->input('payment_notes', '')),
            'guardian_first_name' => $t('guardian_first_name'), 'guardian_middle_name' => $t('guardian_middle_name'),
            'guardian_last_name' => $t('guardian_last_name'), 'guardian_relationship' => $t('guardian_relationship'),
            'guardian_mobile' => $t('guardian_mobile'), 'guardian_alt_contact' => $t('guardian_alt_contact'),
            'guardian_email' => strtolower($t('guardian_email')), 'guardian_house_unit' => $t('guardian_house_unit'),
            'guardian_street' => $t('guardian_street'), 'guardian_barangay' => $t('guardian_barangay'),
            'guardian_municipality_city' => $t('guardian_municipality_city'), 'guardian_province' => $t('guardian_province', 'Cebu'),
            'guardian_zip_code' => $t('guardian_zip_code'),
        ];
    }

    protected function processWalkInRegistration(string $baseUrl): void {
        $isAdmin = $baseUrl === '/admin';
        $studentsUrl = $baseUrl . '/students';
        $view = $isAdmin ? 'admin.walk_in_register' : 'manager.walk_in_register';
        $layout = $isAdmin ? 'admin' : 'manager';

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect($studentsUrl);
                return;
            }

            $t = fn(string $key, string $default = ''): string => trim((string)$this->input($key, $default));

            if ($t('send_code') === '1') {
                $isAjax = $this->input('ajax_send_code') === '1';
                $email = strtolower($t('email'));
                $firstName = $t('first_name');
                $errors = [];
                if (empty($email)) $errors[] = 'Email address is required. Enter it first, then click Send Verification Code.';
                elseif (!isValidGmailEmail($email)) $errors[] = gmailEmailError('Email address', $email);
                else {
                    $dup = $this->checkDuplicateUserCredentials($email, '');
                    if (!empty($dup)) $errors[] = 'That Gmail address is already registered.';
                }
                if (empty($errors)) {
                    $sent = $this->generateWalkInCode($email, $firstName !== '' ? $firstName : 'tenant');
                    if ($isAjax) {
                        if ($sent !== null) {
                            $this->json([
                                'ok'    => true,
                                'email' => $email,
                            ]);
                        }
                        $this->json([
                            'ok'     => false,
                            'errors' => ['We could not send the verification code to ' . $email . ' right now. Please check the email address and click "Send Verification Code" again.'],
                        ]);
                    }
                    $viewData = [
                        'pageTitle' => 'Walk-In Registration Form',
                        'errors' => [],
                        'old' => $this->walkInOldInput(),
                        'availableRooms' => $this->getAvailableRoomsForWalkIn(),
                        'generatedStudentId' => $this->generateStudentIdNumber(),
                        'baseUrl' => $baseUrl,
                        'flashMessages' => $this->getFlashMessages(),
                        'codeSent' => $sent !== null ? $email : '',
                        'codeError' => $sent !== null ? '' : 'We could not send the verification code to ' . $email . ' right now. Please check the email address and click "Send Verification Code" again.',
                    ];
                    $this->view($view, $viewData, $layout);
                    return;
                }
                if ($isAjax) {
                    $this->json(['ok' => false, 'errors' => $errors]);
                }
                $this->view($view, [
                    'pageTitle' => 'Walk-In Registration Form',
                    'errors' => $errors,
                    'old' => $this->walkInOldInput(),
                    'availableRooms' => $this->getAvailableRoomsForWalkIn(),
                    'generatedStudentId' => $this->generateStudentIdNumber(),
                    'baseUrl' => $baseUrl,
                    'flashMessages' => $this->getFlashMessages(),
                ], $layout);
                return;
            }

            // --- Collect inputs ---
            $email              = strtolower($t('email'));
            $username           = strtolower($t('username'));
            $password           = (string)$this->input('password', '');
            $firstName          = $t('first_name');
            $middleName         = $t('middle_name');
            $lastName           = $t('last_name');
            $suffix             = $t('suffix');
            $gender             = $t('gender');
            $dateOfBirth        = $t('date_of_birth');
            $civilStatus        = $t('civil_status');
            $nationality        = $t('nationality', 'Filipino');
            $schoolUniversity   = $t('school_university');
            $courseProgram      = $t('course_program');
            // Free-text used only when course_program = "Other" (see validation).
            $courseProgramOther = preg_replace('/\s+/', ' ', strip_tags((string)$this->input('course_program_other', '')));
            $courseProgramOther = $courseProgramOther === null ? '' : trim($courseProgramOther);
            $yearLevel          = $t('year_level');
            $phone              = normalizeMobileNumber($t('phone'));
            $houseUnit          = $t('house_unit');
            $street             = $t('street');
            $barangay           = $t('barangay');
            $municipalityCity   = $t('municipality_city');
            $province           = $t('province', 'Cebu');
            $zipCode            = $t('zip_code');

            $roomId             = (int)$this->input('room_id', 0);
            $moveInDate         = $t('move_in_date');
            $moveInTime         = $t('move_in_time');
            if ($moveInTime !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $moveInTime)) $moveInTime = '';
            $duration           = (int)$this->input('expected_duration', 1);
            if ($duration < 1 || $duration > 12) $duration = 1;

            $collectPayment     = (string)$this->input('collect_payment', '') === '1';
            $paymentMethod      = $t('payment_method');
            $paymentReceived    = $this->validateWholeNumber(str_replace(',', '', (string)$this->input('payment_received', '0')), 0);
            if ($paymentReceived === null) $paymentReceived = 0;
            $paymentReference   = $t('payment_reference');
            $paymentNotes       = trim((string)$this->input('payment_notes', ''));

            $guardianFirstName  = $t('guardian_first_name');
            $guardianMiddleName = $t('guardian_middle_name');
            $guardianLastName   = $t('guardian_last_name');
            $guardianRelationship = $t('guardian_relationship');
            $guardianMobile     = normalizeMobileNumber($t('guardian_mobile'));
            $guardianAltContact = normalizeMobileNumber($t('guardian_alt_contact'));
            $guardianEmail      = strtolower($t('guardian_email'));
            $guardianHouseUnit  = $t('guardian_house_unit');
            $guardianStreet     = $t('guardian_street');
            $guardianBarangay   = $t('guardian_barangay');
            $guardianMunicipality = $t('guardian_municipality_city');
            $guardianProvince   = $t('guardian_province', 'Cebu');
            $guardianZipCode    = $t('guardian_zip_code');

            $old = [
                'email' => $email, 'username' => $username, 'verification_code' => $t('verification_code'),
                'first_name' => $firstName, 'middle_name' => $middleName, 'last_name' => $lastName, 'suffix' => $suffix,
                'gender' => $gender, 'date_of_birth' => $dateOfBirth, 'civil_status' => $civilStatus, 'nationality' => $nationality,
                'school_university' => $schoolUniversity, 'course_program' => $courseProgram, 'year_level' => $yearLevel,
                'phone' => $t('phone'), 'house_unit' => $houseUnit, 'street' => $street, 'barangay' => $barangay,
                'municipality_city' => $municipalityCity, 'province' => $province, 'zip_code' => $zipCode,
                'room_id' => $roomId, 'move_in_date' => $moveInDate, 'move_in_time' => $moveInTime, 'expected_duration' => $duration,
                'collect_payment' => $collectPayment ? '1' : '',
                'payment_method' => $paymentMethod,
                'payment_received' => $paymentReceived > 0 ? $paymentReceived : '',
                'payment_reference' => $paymentReference,
                'payment_notes' => $paymentNotes,
                'guardian_first_name' => $guardianFirstName, 'guardian_middle_name' => $guardianMiddleName,
                'guardian_last_name' => $guardianLastName, 'guardian_relationship' => $guardianRelationship,
                'guardian_mobile' => $t('guardian_mobile'), 'guardian_alt_contact' => $t('guardian_alt_contact'),
                'guardian_email' => $guardianEmail, 'guardian_house_unit' => $guardianHouseUnit,
                'guardian_street' => $guardianStreet, 'guardian_barangay' => $guardianBarangay,
                'guardian_municipality_city' => $guardianMunicipality, 'guardian_province' => $guardianProvince,
                'guardian_zip_code' => $guardianZipCode,
            ];

            // --- Validation ---
            $errors = [];
            if (empty($firstName)) $errors[] = 'First name is required.';
            if (empty($lastName)) $errors[] = 'Last name is required.';
            if (empty($gender)) $errors[] = 'Gender is required.';
            if (empty($dateOfBirth)) $errors[] = 'Date of birth is required.';
            if (empty($civilStatus)) $errors[] = 'Civil status is required.';
            if (empty($nationality)) $errors[] = 'Nationality is required.';
            if (empty($schoolUniversity)) $errors[] = 'School/College is required.';
            $validCourses = ['BSIT', 'BSHM', 'BSED', 'BSBA', 'BSCE', 'BSCRIM'];
            if (empty($courseProgram)) $errors[] = 'Course/Program is required.';
            elseif ($courseProgram === 'Other') {
                // "Other (specify)" stores whatever the staff typed, so it shows
                // up as-is in the student lists instead of the word "Other".
                if ($courseProgramOther === '') $errors[] = 'Specify your course/program in the text box under the Course/Program list.';
                elseif (mb_strlen($courseProgramOther) > 100) $errors[] = 'Specify your course/program (100 characters or less).';
                else $courseProgram = $courseProgramOther;
            }
            elseif (!in_array($courseProgram, $validCourses)) $errors[] = 'Invalid course/program selected.';
            if (empty($yearLevel)) $errors[] = 'Year level is required.';
            if (empty($email)) $errors[] = gmailEmailError('Email address', $email);
            elseif (!isValidGmailEmail($email)) $errors[] = gmailEmailError('Email address', $email);
            if ($t('verification_code') === '') {
                $errors[] = 'Verification code is required. Click "Send Verification Code" to have a code emailed to this address.';
            } elseif (!preg_match('/^\d{6}$/', $t('verification_code'))) {
                $errors[] = 'Verification code must be exactly 6 digits.';
            }
            if (empty($errors) && !$this->verifyWalkInCode($email, $t('verification_code'))) {
                $errors[] = 'The verification code is invalid or has expired. Please click "Send Verification Code" to receive a new one.';
            }
            if ($phone === null) $errors[] = 'Mobile number is invalid. Please enter a valid 11-digit Philippine mobile number starting with 09.';
            elseif ($phone === '') $errors[] = 'Mobile number is required.';
            if (empty($barangay)) $errors[] = 'Barangay is required.';
            if (empty($municipalityCity)) $errors[] = 'Municipality/City is required.';
            if (empty($province)) $errors[] = 'Province is required.';
            if (empty($zipCode)) $errors[] = 'ZIP code is required.';
            if (empty($roomId)) $errors[] = 'Please select a room.';
            if (empty($moveInDate)) $errors[] = 'Move-in date is required.';
            elseif (strtotime($moveInDate) < strtotime(serverDate())) $errors[] = 'Move-in date cannot be in the past.';
            if ($username !== '' && strlen($username) < 4) $errors[] = 'Username must be at least 4 characters.';
            if ($password === '') {
                $errors[] = 'Password is required. Type your own password or click Generate Password.';
            } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
                $errors[] = 'Password must be at least 8 characters and include an uppercase letter, lowercase letter, number, and special character.';
            }
            if ($collectPayment) {
                if (!in_array($paymentMethod, ['cash', 'gcash'])) $errors[] = 'Payment method is required.';
                if ($paymentReceived <= 0) $errors[] = 'Amount received must be greater than 0.';
                if ($paymentMethod === 'gcash' && $paymentReference === '') $errors[] = 'Reference / OR Number is required for GCash payments.';
            }

            if (empty($guardianFirstName)) $errors[] = 'Guardian first name is required.';
            if (empty($guardianLastName)) $errors[] = 'Guardian last name is required.';
            if (empty($guardianRelationship)) $errors[] = 'Guardian relationship is required.';
            if ($guardianMobile === null) $errors[] = 'Guardian mobile number is invalid. Please enter a valid 11-digit Philippine mobile number starting with 09.';
            elseif ($guardianMobile === '') $errors[] = 'Guardian mobile number is required.';
            if ($guardianAltContact === null) $errors[] = 'Guardian alternative contact is invalid. Please enter a valid 11-digit Philippine mobile number starting with 09.';
            if (!empty($guardianEmail) && !isValidGmailEmail($guardianEmail)) $errors[] = gmailEmailError('Guardian email', $guardianEmail);
            if (empty($guardianBarangay)) $errors[] = 'Guardian barangay is required.';
            if (empty($guardianMunicipality)) $errors[] = 'Guardian municipality/city is required.';
            if (empty($guardianProvince)) $errors[] = 'Guardian province is required.';
            if (empty($guardianZipCode)) $errors[] = 'Guardian ZIP code is required.';

            $validIdFile = $_FILES['valid_id'] ?? null;
            if (!$validIdFile || (int)$validIdFile['error'] !== UPLOAD_ERR_OK || (int)$validIdFile['size'] <= 0) {
                $errors[] = 'Valid ID upload is required (JPG, PNG, PDF, max 5MB).';
            } else {
                $validIdExt = strtolower(pathinfo((string)$validIdFile['name'], PATHINFO_EXTENSION));
                if (!in_array($validIdExt, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
                    $errors[] = 'Valid ID must be a JPG, PNG, or PDF file.';
                } elseif ((int)$validIdFile['size'] > 5242880) {
                    $errors[] = 'Valid ID must be 5MB or smaller.';
                }
            }

            // Uniqueness checks (always run so both duplicates are reported at once)
            $errors = array_merge($errors, $this->checkDuplicateUserCredentials($email, $username));

            if (empty($errors)) {
                $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ? AND current_occupancy < max_capacity AND status != 'under_maintenance'", [$roomId]);
                if (!$room) $errors[] = 'The selected room is no longer available.';
            }

            if (!empty($errors)) {
                $this->view($view, [
                    'pageTitle' => 'Walk-In Registration Form',
                    'errors' => $errors,
                    'old' => $old,
                    'availableRooms' => $this->getAvailableRoomsForWalkIn(),
                    'generatedStudentId' => $this->generateStudentIdNumber(),
                    'baseUrl' => $baseUrl,
                    'flashMessages' => $this->getFlashMessages(),
                ], $layout);
                return;
            }

            // --- File uploads ---
            $profilePicture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $profilePicture = $this->uploadFile($_FILES['profile_picture'], 'profiles', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
            }
            $schoolIdPath = null;
            if (isset($_FILES['school_id_upload']) && $_FILES['school_id_upload']['error'] === UPLOAD_ERR_OK) {
                $schoolIdPath = $this->uploadFile($_FILES['school_id_upload'], 'school_ids', ['jpg', 'jpeg', 'png', 'pdf'], 5242880);
            }
            $validIdPath = null;
            if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
                $validIdPath = $this->uploadFile($_FILES['valid_id'], 'reservations', ['jpg', 'jpeg', 'png', 'pdf'], 5242880);
            }
            if ($validIdPath === null) {
                $this->view($view, [
                    'pageTitle' => 'Walk-In Registration Form',
                    'errors' => ['Valid ID upload could not be saved (JPG, PNG, PDF, max 5MB). Please re-select the file and submit again.'],
                    'old' => $old,
                    'availableRooms' => $this->getAvailableRoomsForWalkIn(),
                    'generatedStudentId' => $this->generateStudentIdNumber(),
                    'baseUrl' => $baseUrl,
                    'flashMessages' => $this->getFlashMessages(),
                ], $layout);
                return;
            }

            // --- Account credentials ---
            // Username is optional (auto-generated when blank). Password is
            // required by the validation above, so the branch that generates a
            // temporary password is only a safety net, never the normal path.
            if ($username === '') {
                $username = $this->generateUniqueUsername($email);
            }
            $generatedPassword = false;
            if ($password === '') {
                $password = $this->generateTemporaryPassword();
                $generatedPassword = true;
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $fullAddress = trim(implode(', ', array_filter([$houseUnit, $street, $barangay, $municipalityCity, $province, $zipCode])));

            // --- Create user + student atomically (unique constraints are the final safeguard) ---
            $pdo = $this->db->getConnection();

            try {
                $pdo->beginTransaction();

                $userId = $this->db->insert('users', [
                    'email' => $email,
                    'username' => $username,
                    'password' => $passwordHash,
                    'role' => 'student',
                    'status' => 'active',
                    'email_verified' => 1,
                    'email_verified_at' => serverDateTime(),
                    'password_changed_at' => serverDateTime(),
                ]);
                $this->savePasswordHistory($userId, $passwordHash);

                $studentIdNumber = $this->generateStudentIdNumber();
                $studentId = $this->db->insert('students', [
                    'user_id' => $userId,
                    'student_id_number' => $studentIdNumber,
                    'first_name' => $firstName,
                    'middle_name' => $middleName ?: null,
                    'last_name' => $lastName,
                    'suffix' => $suffix ?: null,
                    'phone' => $phone,
                    'address' => $fullAddress ?: null,
                    'house_unit' => $houseUnit ?: null,
                    'street' => $street ?: null,
                    'barangay' => $barangay ?: null,
                    'municipality_city' => $municipalityCity ?: null,
                    'province' => $province ?: null,
                    'zip_code' => $zipCode ?: null,
                    'date_of_birth' => $dateOfBirth ?: null,
                    'gender' => $gender,
                    'civil_status' => $civilStatus,
                    'nationality' => $nationality,
                    'school_university' => $schoolUniversity,
                    'course_program' => $courseProgram,
                    'year_level' => $yearLevel,
                    'profile_picture' => $profilePicture,
                    'school_id_path' => $schoolIdPath,
                ]);

                // --- Guardian (validated as required above, so always persisted) ---
                $this->db->insert('guardians', [
                    'student_id' => $studentId,
                    'first_name' => $guardianFirstName,
                    'middle_name' => $guardianMiddleName ?: null,
                    'last_name' => $guardianLastName,
                    'relationship' => $guardianRelationship,
                    'mobile_number' => $guardianMobile,
                    'alternative_contact' => $guardianAltContact ?: null,
                    'email' => $guardianEmail ?: null,
                    'house_unit' => $guardianHouseUnit ?: null,
                    'street' => $guardianStreet ?: null,
                    'barangay' => $guardianBarangay ?: null,
                    'municipality_city' => $guardianMunicipality ?: null,
                    'province' => $guardianProvince ?: null,
                    'zip_code' => $guardianZipCode ?: null,
                ]);

                $pdo->commit();
            } catch (\PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $dupField = $this->getUserDuplicateField($e);
                if ($dupField !== null) {
                    // Lost the race against a duplicate submission — reject safely.
                    $this->view($view, [
                        'pageTitle' => 'Walk-In Registration Form',
                        'errors' => [$this->duplicateUserMessage($dupField)],
                        'old' => $old,
                        'availableRooms' => $this->getAvailableRoomsForWalkIn(),
                        'generatedStudentId' => $this->generateStudentIdNumber(),
                        'baseUrl' => $baseUrl,
                        'flashMessages' => $this->getFlashMessages(),
                    ], $layout);
                    return;
                }
                throw $e;
            }

            // --- Create approved reservation ---
            $reservationCode = generateCode('RES');
            $reservationId = $this->db->insert('reservations', [
                'reservation_code' => $reservationCode,
                'student_id' => $studentId,
                'room_id' => $roomId,
                'move_in_date' => $moveInDate,
                'move_in_time' => $moveInTime ? $moveInTime . ':00' : null,
                'expected_duration' => $duration,
                'rent_amount' => (float)($room['monthly_rent'] ?? 0),
                'status' => 'approved',
                'valid_id_path' => $validIdPath,
                'approved_at' => serverDateTime(),
                'moved_in_at' => $moveInDate . ' ' . ($moveInTime ? $moveInTime . ':00' : '12:00:00'),
            ]);

            $reservation = [
                'id'                => $reservationId,
                'reservation_code'  => $reservationCode,
                'student_id'        => $studentId,
                'room_id'           => $roomId,
                'move_in_date'      => $moveInDate,
                'expected_duration' => $duration,
            ];
            $this->updateRoomOccupancy($roomId);
            $this->createApprovedReservationBilling($userId, $reservation, $room);

            $walkInPaymentResult = null;
            if ($collectPayment && $paymentReceived > 0) {
                $walkInPaymentResult = $this->applyWalkInPayment($studentId, $reservationId, $userId, $room, $paymentMethod, $paymentReceived, $paymentReference, $paymentNotes);
            }

            $this->db->insert('notifications', [
                'user_id' => $userId,
                'title' => 'Walk-In Registration Approved',
                'message' => "You have been registered as a walk-in tenant. Room {$room['room_number']} ({$room['room_name']}) is assigned to you.",
                'type' => 'reservation',
                'reference_id' => $reservationId,
                'reference_type' => 'reservation',
            ]);

            $this->logActivity('walk_in_register', "Walk-in tenant registered: {$firstName} {$lastName} ({$email}) — Room {$room['room_number']}");

            // The verification code is single-use: one successful registration burns it.
            if (session_status() === PHP_SESSION_NONE) session_start();
            unset($_SESSION['walkin_verify'][$email]);

            $successMsg = "Walk-in tenant registered successfully. Student ID: {$studentIdNumber}.";
            if ($walkInPaymentResult && $walkInPaymentResult['allocated'] > 0) {
                $successMsg .= " Walk-in payment of " . formatCurrency($walkInPaymentResult['allocated']) . " collected via " . strtoupper($paymentMethod) . " (receipts generated).";
            }
            if ($generatedPassword) {
                $successMsg .= " Login credentials — Email: {$email}, Username: {$username}, Temporary password: {$password}. The tenant signs in at /login with their Gmail address and this password (please share it with them).";
            } else {
                $successMsg .= " The tenant signs in at /login with Email: {$email} and the password set above.";
            }
            $this->flash('success', $successMsg);
            $this->redirect($studentsUrl);
            return;
        }

        $this->view($view, [
            'pageTitle' => 'Walk-In Registration Form',
            'errors' => [],
            'old' => [],
            'availableRooms' => $this->getAvailableRoomsForWalkIn(),
            'generatedStudentId' => $this->generateStudentIdNumber(),
            'baseUrl' => $baseUrl,
            'flashMessages' => $this->getFlashMessages(),
        ], $layout);
    }

    private function getAvailableRoomsForWalkIn(): array {
        $rooms = $this->db->fetchAll(
            "SELECT r.*, (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image, (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count FROM rooms r WHERE r.current_occupancy < r.max_capacity AND r.status NOT IN ('under_maintenance') ORDER BY r.room_number"
        );
        foreach ($rooms as &$room) {
            $room['images'] = $this->db->fetchAll(
                "SELECT image_path, alt_text, is_primary FROM room_images WHERE room_id = ? ORDER BY is_primary DESC, sort_order, id",
                [(int)$room['id']]
            );
        }
        return $rooms;
    }

    /**
     * Marks the auto-created advance payment + first month rent billings as
     * paid/partially paid using cash collected over the counter at walk-in
     * registration. Generates receipts, payment history, and a notification.
     */
    protected function applyWalkInPayment(int $studentId, int $reservationId, int $studentUserId, array $room, string $method, float $received, string $reference, string $notes): array {
        $billing = $this->db->fetchAll(
            "SELECT * FROM payments
             WHERE student_id = ? AND reservation_id = ? AND payment_type IN ('advance_payment', 'monthly_rent', 'full_payment')
             ORDER BY FIELD(payment_type, 'advance_payment', 'monthly_rent', 'full_payment')",
            [$studentId, $reservationId]
        );
        return $this->allocateWalkInPayment($billing, $method, $received, $reference, $notes, $studentUserId, "Room {$room['room_number']}", 'during walk-in registration');
    }

    /**
     * Allocates a received over-the-counter amount across the given unpaid
     * billings (in order). Marks each paid or partially paid, generates
     * receipts + payment history, and notifies the tenant.
     */
    protected function allocateWalkInPayment(array $billings, string $method, float $received, string $reference, string $notes, int $studentUserId, string $roomLabel, string $contextNote): array {
        $remaining = $received;
        $allocated = 0.0;
        foreach ($billings as $b) {
            if ($remaining <= 0) break;
            if (in_array((string)($b['status'] ?? ''), ['paid', 'cancelled', 'refunded'], true)) continue;
            $alreadyPaid = (float)($b['amount_paid'] ?? 0);
            $due = (float)$b['amount'] + (float)$b['late_fee'] - $alreadyPaid;
            if ($due <= 0) continue;
            $alloc = min($remaining, $due);
            $remaining -= $alloc;
            $allocated += $alloc;
            $fullyPaid = ($alreadyPaid + $alloc) >= ((float)$b['amount'] + (float)$b['late_fee']);
            $newStatus = $fullyPaid ? 'paid' : 'partially_paid';

            $this->db->update('payments', [
                'status' => $newStatus,
                'amount_paid' => $alreadyPaid + $alloc,
                'payment_method' => $method,
                'reference_number' => $reference ?: null,
                'notes' => $notes ?: null,
                'paid_at' => serverDateTime(),
                'verified_by' => $_SESSION['user_id'] ?? null,
                'verified_at' => serverDateTime(),
            ], "id = ?", [(int)$b['id']]);

            $this->db->insert('payment_history', [
                'payment_id' => (int)$b['id'],
                'action' => 'walk_in_payment',
                'old_status' => $b['status'],
                'new_status' => $newStatus,
                'notes' => 'Collected ' . $contextNote . ($reference ? " (Ref: {$reference})" : ''),
                'performed_by' => $_SESSION['user_id'] ?? null,
            ]);

            $this->db->insert('receipts', [
                'receipt_number' => generateCode('REC'),
                'payment_id' => (int)$b['id'],
                'issued_date' => serverDate(),
                'subtotal' => $b['amount'],
                'discount' => 0,
                'total' => $alloc,
            ]);
        }

        if ($allocated > 0) {
            $this->db->insert('notifications', [
                'user_id' => $studentUserId,
                'title' => 'Walk-In Payment Received',
                'message' => "Payment received over the counter: " . formatCurrency($allocated) . " via " . strtoupper($method) . " for {$roomLabel}.",
                'type' => 'payment',
            ]);
            $this->logActivity('walk_in_payment', "Walk-in payment of " . formatCurrency($allocated) . " received via " . strtoupper($method) . " for {$roomLabel} " . $contextNote . ".");
        }

        return ['allocated' => $allocated, 'received' => $received, 'reference' => $reference];
    }

    /**
     * Allocates a student-submitted payment across their unpaid bills.
     * Similar to walk-in payment but keeps status as 'pending' for admin verification.
     * Generates receipts and payment history, notifies admin.
     */
    protected function allocateStudentPayment(array $billings, string $method, float $received, string $reference, string $notes, ?string $proofPath, int $studentId, array $reservation): array {
        $remaining = $received;
        $allocated = 0.0;
        $studentUser = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$studentId]);
        $studentUserId = $studentUser ? (int)$studentUser['user_id'] : 0;
        $roomLabel = "Room {$reservation['room_number']} ({$reservation['room_name']})";

        foreach ($billings as $b) {
            if ($remaining <= 0) break;
            $due = (float)$b['amount'] + (float)$b['late_fee'] - (float)($b['amount_paid'] ?? 0);
            if ($due <= 0) continue;
            $alloc = min($remaining, $due);
            $remaining -= $alloc;
            $allocated += $alloc;
            $fullyPaid = $alloc >= $due;
            $newStatus = 'pending'; // Student submissions are pending until admin verifies
            $newPaid = ((float)($b['amount_paid'] ?? 0)) + $alloc;

            $this->db->update('payments', [
                'status' => $newStatus,
                'amount_paid' => $newPaid,
                'payment_method' => $method,
                'reference_number' => $reference ?: null,
                'notes' => $notes ?: null,
                'proof_of_payment' => $proofPath,
            ], "id = ?", [(int)$b['id']]);

            $this->db->insert('payment_history', [
                'payment_id' => (int)$b['id'],
                'action' => 'student_submission',
                'old_status' => $b['status'],
                'new_status' => $newStatus,
                'notes' => 'Submitted by student' . ($reference ? " (Ref: {$reference})" : '') . ' for ' . $roomLabel,
                'performed_by' => $studentUserId,
            ]);

            // Generate receipt for the allocated amount
            $this->db->insert('receipts', [
                'receipt_number' => generateCode('REC'),
                'payment_id' => (int)$b['id'],
                'issued_date' => serverDate(),
                'subtotal' => $b['amount'],
                'discount' => 0,
                'total' => $alloc,
            ]);
        }

        if ($allocated > 0 && $studentUserId) {
            $this->db->insert('notifications', [
                'user_id' => $studentUserId,
                'title' => 'Payment Submitted',
                'message' => "Your payment of " . formatCurrency($allocated) . " via " . strtoupper($method) . " for {$roomLabel} has been submitted and is pending verification.",
                'type' => 'payment',
            ]);
            $this->logActivity('student_payment_submission', "Student payment of " . formatCurrency($allocated) . " submitted via " . strtoupper($method) . " for {$roomLabel}.");
        }

        return ['allocated' => $allocated, 'received' => $received, 'reference' => $reference];
    }

    /**
     * Auto-issue (or update) a receipt for a payment that has been approved/paid.
     */
    protected function issueReceiptForPayment(int $paymentId): void {
        $payment = $this->db->fetch("SELECT * FROM payments WHERE id = ?", [$paymentId]);
        if (!$payment) return;
        if (!in_array($payment['status'], ['paid', 'partially_paid'], true)) return;

        $totalDue = (float)$payment['amount'] + (float)$payment['late_fee'];
        $receiptTotal = $payment['status'] === 'paid'
            ? $totalDue
            : ((float)$payment['amount_paid'] > 0 ? (float)$payment['amount_paid'] : (float)$payment['amount']);

        $existingReceipt = $this->db->fetch("SELECT id FROM receipts WHERE payment_id = ?", [$paymentId]);
        if (!$existingReceipt) {
            $this->db->insert('receipts', [
                'receipt_number' => generateCode('REC'),
                'payment_id' => $paymentId,
                'issued_date' => serverDate(),
                'subtotal' => $payment['amount'],
                'discount' => 0,
                'total' => $receiptTotal,
            ]);
        } else {
            $this->db->update('receipts', ['total' => $receiptTotal], "payment_id = ?", [$paymentId]);
        }
    }

    /**
     * Walk-in payment form for an EXISTING tenant: shows their outstanding
     * bills and records an over-the-counter payment against the selected ones.
     */
    protected function processWalkInPaymentForStudent(int $studentId, string $baseUrl): void {
        $isAdmin = $baseUrl === '/admin';
        $studentsUrl = $baseUrl . '/students';
        $view = $isAdmin ? 'admin.walk_in_payment' : 'manager.walk_in_payment';
        $layout = $isAdmin ? 'admin' : 'manager';

        $student = $this->getStudentById($studentId);
        if (!$student) {
            $this->flash('error', 'Tenant not found.');
            $this->redirect($studentsUrl);
            return;
        }

        $room = $this->db->fetch(
            "SELECT rm.room_number, rm.room_name, rm.monthly_rent, rm.advance_payment FROM reservations r JOIN rooms rm ON r.room_id = rm.id
             WHERE r.student_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 1",
            [$studentId]
        );
        $roomLabel = $room ? "Room {$room['room_number']} ({$room['room_name']})" : ($student['first_name'] . ' ' . $student['last_name']);
        $roomRent = $room ? (float)$room['monthly_rent'] : 0.0;
        $roomAdvance = $room ? (float)$room['advance_payment'] : 0.0;

        $outstanding = $this->db->fetchAll(
            "SELECT * FROM payments
             WHERE student_id = ? AND status IN ('pending','upcoming','due_today','partially_paid','overdue')
             ORDER BY (due_date IS NULL) ASC, due_date ASC, id ASC",
            [$studentId]
        );

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect($studentsUrl);
                return;
            }

            $t = fn(string $key, string $default = ''): string => trim((string)$this->input($key, $default));
            $paymentMethod = $t('payment_method');
            $reference = $t('payment_reference');
            $notes = trim((string)$this->input('payment_notes', ''));
            $selectedIds = array_map('intval', (array)$this->input('payment_ids', []));

            $errors = [];
            if (!in_array($paymentMethod, ['cash', 'gcash'])) $errors[] = 'Payment method is required.';
            if (empty($selectedIds)) $errors[] = 'Select at least one bill to pay.';

            $selectedBills = [];
            if (empty($errors)) {
                foreach ($outstanding as $bill) {
                    if (in_array((int)$bill['id'], $selectedIds, true)) {
                        $selectedBills[] = $bill;
                    }
                }
                if (empty($selectedBills)) $errors[] = 'Selected bill(s) are no longer outstanding.';
            }

            $received = 0;
            foreach ($selectedBills as $bill) {
                $received += (float)$bill['amount'] + (float)$bill['late_fee'];
            }

            if (!empty($errors)) {
                $this->view($view, [
                    'pageTitle' => 'Walk-In Payment',
                    'student' => $student,
                    'roomLabel' => $roomLabel,
                    'roomRent' => $roomRent,
                    'roomAdvance' => $roomAdvance,
                    'bills' => $outstanding,
                    'errors' => $errors,
                    'old' => [
                        'payment_method' => $paymentMethod,
                        'payment_reference' => $reference,
                        'payment_notes' => $notes,
                        'payment_ids' => $selectedIds,
                    ],
                    'baseUrl' => $baseUrl,
                    'flashMessages' => $this->getFlashMessages(),
                ], $layout);
                return;
            }

            $result = $this->allocateWalkInPayment($selectedBills, $paymentMethod, (float)$received, $reference, $notes, (int)$student['user_id'], $roomLabel, 'over the counter');

            $confirmed = $this->notifyTenantWalkInPayment($student, $roomLabel, (float)$result['allocated'], $paymentMethod);

            $this->flash('success', 'Walk-in payment of ' . formatCurrency($result['allocated']) . ' recorded via ' . strtoupper($paymentMethod) . ' for ' . $student['first_name'] . ' ' . $student['last_name'] . '.' . ($confirmed ? ' Confirmation emailed to ' . $student['email'] . '.' : ' We could not send the confirmation email right now.'));
            $this->redirect($studentsUrl);
            return;
        }

        $presetMethod = strtolower(trim((string)$this->input('method', 'cash')));
        if (!in_array($presetMethod, ['cash', 'gcash'])) $presetMethod = 'cash';

        $this->view($view, [
            'pageTitle' => 'Walk-In Payment',
            'student' => $student,
            'roomLabel' => $roomLabel,
            'roomRent' => $roomRent,
            'roomAdvance' => $roomAdvance,
            'bills' => $outstanding,
            'errors' => [],
            'old' => ['payment_method' => $presetMethod, 'payment_reference' => '', 'payment_notes' => '', 'payment_ids' => []],
            'baseUrl' => $baseUrl,
            'flashMessages' => $this->getFlashMessages(),
        ], $layout);
    }

    /**
     * Dedicated Walk-In Payment page for ALL tenants: search/select any tenant,
     * pay their outstanding bills and/or record a custom payment (electric bill,
     * water bill, other). Also lists recent walk-in payments.
     */
    protected function processWalkInPaymentsPage(string $baseUrl): void {
        $isAdmin = $baseUrl === '/admin';
        $view = $isAdmin ? 'admin.walk_in_payments' : 'manager.walk_in_payments';
        $layout = $isAdmin ? 'admin' : 'manager';

        $allStudents = $this->db->fetchAll(
            "SELECT s.id, s.first_name, s.last_name, s.student_id_number, u.status, u.email,
                    rm.room_number, rm.room_name, rm.monthly_rent, rm.advance_payment
             FROM students s
             JOIN users u ON s.user_id = u.id
             JOIN reservations r ON r.student_id = s.id AND r.status = 'approved'
             JOIN rooms rm ON r.room_id = rm.id
             ORDER BY s.first_name ASC, s.last_name ASC"
        );

        $studentId = (int)$this->input('student_id', 0);
        $selectedStudent = null;
        $bills = [];
        $roomLabel = '';
        $roomRent = 0.0;
        $roomAdvance = 0.0;
        $reservation = null;
        $tenantDuration = 0;
        $tenantMoveInDate = '';
        $tenantLastDueDate = '';
        $tenantMinMoveOutDate = '';
        if ($studentId) {
            $selectedStudent = $this->getStudentById($studentId);
            if ($selectedStudent) {
                $bills = $this->db->fetchAll(
                    "SELECT * FROM payments
                     WHERE student_id = ? AND status IN ('pending','upcoming','due_today','partially_paid','overdue')
                     ORDER BY (due_date IS NULL) ASC, due_date ASC, id ASC",
                    [$studentId]
                );
                $reservation = $this->db->fetch(
                    "SELECT r.id, r.expected_duration, r.move_in_date, rm.room_number, rm.room_name, rm.monthly_rent, rm.advance_payment
                     FROM reservations r JOIN rooms rm ON r.room_id = rm.id
                     WHERE r.student_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 1",
                    [$studentId]
                );
                $roomLabel = $reservation ? "Room {$reservation['room_number']} ({$reservation['room_name']})" : ($selectedStudent['first_name'] . ' ' . $selectedStudent['last_name']);
                $roomRent = $reservation ? (float)$reservation['monthly_rent'] : 0.0;
                $roomAdvance = $reservation ? (float)$reservation['advance_payment'] : 0.0;
                $tenantDuration = $reservation ? (int)($reservation['expected_duration'] ?? 1) : 0;
                $tenantMoveInDate = $reservation ? ($reservation['move_in_date'] ?? '') : '';
                $lastPaid = $this->db->fetch(
                    "SELECT due_date FROM payments WHERE student_id = ? AND due_date IS NOT NULL ORDER BY due_date DESC LIMIT 1",
                    [$studentId]
                );
                $tenantLastDueDate = $lastPaid ? ($lastPaid['due_date'] ?? '') : '';
                // Calculate minimum move-out date (next due date)
                $tenantMinMoveOutDate = '';
                if ($tenantLastDueDate) {
                    $dt = new DateTime($tenantLastDueDate);
                    $dt->modify('first day of next month');
                    $tenantMinMoveOutDate = $dt->format('Y-m-d');
                } elseif ($tenantMoveInDate) {
                    $dt = new DateTime($tenantMoveInDate);
                    $dt->modify('first day of next month');
                    $tenantMinMoveOutDate = $dt->format('Y-m-d');
                }
            }
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect($baseUrl . '/students/walk-in-payment');
                return;
            }

            $t = fn(string $key, string $default = ''): string => trim((string)$this->input($key, $default));
            $paymentMethod = $t('payment_method');
            $reference = $t('payment_reference');
            $notes = trim((string)$this->input('payment_notes', ''));
            $selectedIds = array_map('intval', (array)$this->input('payment_ids', []));
            $customType = $t('custom_type');
            $customAmount = $this->validateWholeNumber(str_replace(',', '', (string)$this->input('custom_amount', '')), 1);
            $customDuration = max(1, (int)$this->input('custom_duration', 1));
            $customDueDate = $t('custom_due_date');

            $errors = [];
            if (!in_array($paymentMethod, ['cash', 'gcash'])) $errors[] = 'Payment method is required.';
            if (!$selectedStudent) $errors[] = 'Select a tenant to record the payment for.';
            if (empty($selectedIds) && $customAmount === null) $errors[] = 'Select a bill to pay or enter a custom payment amount.';
            if ($customAmount !== null && !in_array($customType, ['monthly_rent', 'advance_payment'])) {
                $errors[] = 'Invalid custom payment type.';
            }
            if ($customDuration < 1 || $customDuration > 24) $errors[] = 'Duration must be between 1 and 24 months.';
            if ($paymentMethod === 'gcash' && empty($reference)) $errors[] = 'Reference / OR Number is required for GCash payments.';

            $selectedBills = [];
            if (!empty($selectedIds) && $selectedStudent) {
                foreach ($bills as $bill) {
                    if (in_array((int)$bill['id'], $selectedIds, true)) {
                        $selectedBills[] = $bill;
                    }
                }
                if (empty($selectedBills) && $customAmount === null) $errors[] = 'Selected bill(s) are no longer outstanding.';
            }

            $received = (float)($customAmount ?? 0);
            foreach ($selectedBills as $bill) {
                $received += (float)$bill['amount'] + (float)$bill['late_fee'];
            }

            if (!empty($errors)) {
                $this->view($view, [
                    'pageTitle' => 'Walk-In Payment',
                    'allStudents' => $allStudents,
                    'studentId' => $studentId,
                    'student' => $selectedStudent,
                    'roomLabel' => $roomLabel,
                    'roomRent' => $roomRent,
                    'roomAdvance' => $roomAdvance,
                    'tenantDuration' => $tenantDuration,
                    'tenantMoveInDate' => $tenantMoveInDate,
                    'tenantLastDueDate' => $tenantLastDueDate,
                    'tenantMinMoveOutDate' => $tenantMinMoveOutDate,
                    'bills' => $bills,
                    'walkInPayments' => $this->getWalkInPaymentLog(),
                    'errors' => $errors,
                    'old' => [
                        'payment_method' => $paymentMethod,
                        'payment_reference' => $reference,
                        'payment_notes' => $notes,
                        'payment_ids' => $selectedIds,
                        'custom_type' => $customType,
                        'custom_amount' => $customAmount ?? '',
                        'custom_duration' => $customDuration,
                        'custom_due_date' => $customDueDate,
                    ],
                    'baseUrl' => $baseUrl,
                    'flashMessages' => $this->getFlashMessages(),
                ], $layout);
                return;
            }

            $allocated = 0.0;
            $remaining = (float)$received;

            if ($customAmount !== null && $customAmount > 0) {
                $alloc = min($remaining, (float)$customAmount);
                $remaining -= $alloc;
                $allocated += $alloc;
                $fullyPaid = $alloc >= $customAmount;
                $newStatus = $fullyPaid ? 'paid' : 'partially_paid';

                $durationNote = $customDuration > 1 ? " ({$customDuration} months)" : '';
                $paymentId = $this->db->insert('payments', [
                    'payment_code' => generateCode('PAY'),
                    'student_id' => (int)$selectedStudent['id'],
                    'reservation_id' => $reservation['id'] ?? null,
                    'payment_type' => $customType,
                    'amount' => (float)$customAmount,
                    'late_fee' => 0,
                    'amount_paid' => $alloc,
                    'payment_method' => $paymentMethod,
                    'status' => $newStatus,
                    'reference_number' => $reference ?: null,
                    'due_date' => $customDueDate ?: null,
                    'notes' => ($notes ? $notes . ' — ' : '') . $customDuration . '-month' . ($customDuration > 1 ? 's' : '') . ' payment' . $durationNote,
                    'paid_at' => serverDateTime(),
                    'verified_by' => $_SESSION['user_id'] ?? null,
                    'verified_at' => serverDateTime(),
                ]);

                $this->db->insert('payment_history', [
                    'payment_id' => $paymentId,
                    'action' => 'walk_in_payment',
                    'old_status' => null,
                    'new_status' => $newStatus,
                    'notes' => 'Custom walk-in payment created over the counter' . ($reference ? " (Ref: {$reference})" : ''),
                    'performed_by' => $_SESSION['user_id'] ?? null,
                ]);

                $this->db->insert('receipts', [
                    'receipt_number' => generateCode('REC'),
                    'payment_id' => $paymentId,
                    'issued_date' => serverDate(),
                    'subtotal' => (float)$customAmount,
                    'discount' => 0,
                    'total' => $fullyPaid ? (float)$customAmount : $alloc,
                ]);

                $this->db->insert('notifications', [
                    'user_id' => (int)$selectedStudent['user_id'],
                    'title' => 'Walk-In Payment Received',
                    'message' => "Payment received over the counter: " . formatCurrency($alloc) . " via " . strtoupper($paymentMethod) . " for {$roomLabel}.",
                    'type' => 'payment',
                ]);
            }

            if ($remaining > 0 && !empty($selectedBills)) {
                $result = $this->allocateWalkInPayment($selectedBills, $paymentMethod, $remaining, $reference, $notes, (int)$selectedStudent['user_id'], $roomLabel, 'over the counter');
                $allocated += $result['allocated'];
            }

            $this->logActivity('walk_in_payment', "Walk-in payment of " . formatCurrency($allocated) . " received via " . strtoupper($paymentMethod) . " for " . $selectedStudent['first_name'] . ' ' . $selectedStudent['last_name'] . '.');

            $confirmed = $this->notifyTenantWalkInPayment($selectedStudent, $roomLabel, $allocated, $paymentMethod);

            $this->flash('success', 'Walk-in payment of ' . formatCurrency($allocated) . ' recorded via ' . strtoupper($paymentMethod) . ' for ' . $selectedStudent['first_name'] . ' ' . $selectedStudent['last_name'] . '.' . ($confirmed ? ' Confirmation emailed to ' . $selectedStudent['email'] . '.' : ' We could not send the confirmation email right now.'));
            $this->redirect($baseUrl . '/students/walk-in-payment');
            return;
        }

        $this->view($view, [
            'pageTitle' => 'Walk-In Payment',
            'allStudents' => $allStudents,
            'studentId' => $studentId,
            'student' => $selectedStudent,
            'roomLabel' => $roomLabel,
            'roomRent' => $roomRent,
            'roomAdvance' => $roomAdvance,
            'tenantDuration' => $tenantDuration,
            'tenantMoveInDate' => $tenantMoveInDate,
            'tenantLastDueDate' => $tenantLastDueDate,
            'tenantMinMoveOutDate' => $tenantMinMoveOutDate,
            'bills' => $bills,
            'walkInPayments' => $this->getWalkInPaymentLog(),
            'errors' => [],
            'old' => ['payment_method' => 'cash', 'payment_reference' => '', 'payment_notes' => '', 'payment_ids' => [], 'custom_type' => 'monthly_rent', 'custom_amount' => ''],
            'baseUrl' => $baseUrl,
            'flashMessages' => $this->getFlashMessages(),
        ], $layout);
    }

    private function getWalkInPaymentLog(): array {
        return $this->db->fetchAll(
            "SELECT ph.id, ph.payment_id, ph.action, ph.new_status, ph.created_at,
                    p.payment_code, p.payment_type, p.amount, p.amount_paid, p.payment_method,
                    s.first_name, s.last_name, s.student_id_number,
                    COALESCE(rm.room_name, (SELECT arn.room_name FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_name,
                    COALESCE(rm.room_number, (SELECT arn.room_number FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_number
             FROM payment_history ph
             JOIN payments p ON ph.payment_id = p.id
             JOIN students s ON p.student_id = s.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE ph.action = 'walk_in_payment'
             ORDER BY ph.id DESC LIMIT 20"
        );
    }

    /**
     * Delete a walk-in payment (its payment row, history, receipts, proof and
     * payment notifications) and redirect back to the Walk-In Payment page.
     */
    protected function processWalkInPaymentDelete(string $baseUrl): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect($baseUrl . '/students/walk-in-payment');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect($baseUrl . '/students/walk-in-payment');
            return;
        }
        $id = (int)$this->input('id', 0);
        $payment = $this->db->fetch("SELECT * FROM payments WHERE id = ?", [$id]);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect($baseUrl . '/students/walk-in-payment');
            return;
        }

        $isAdmin = $baseUrl === '/admin';
        $actorLabel = $isAdmin ? 'administrator' : 'manager';
        $student = $this->db->fetch("SELECT user_id, first_name, last_name FROM students WHERE id = ?", [$payment['student_id']]);
        $studentName = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Unknown Student';
        $paymentCode = $payment['payment_code'];
        $paymentAmount = formatCurrency($payment['amount']);
        $paymentType = ucwords(str_replace('_', ' ', $payment['payment_type']));

        $this->db->delete('notifications', "reference_id = ? AND reference_type = 'payment'", [$id]);

        if ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Walk-In Payment Deleted',
                'message' => "Your walk-in payment ({$paymentCode}) of {$paymentAmount} has been deleted by the {$actorLabel}.",
                'type' => 'payment',
                'reference_id' => null,
                'reference_type' => null,
            ]);
        }

        $this->archiveRecord('payment', $id, $payment, [
            'payment_history' => $this->db->fetchAll("SELECT * FROM payment_history WHERE payment_id = ?", [$id]),
            'receipts'        => $this->db->fetchAll("SELECT * FROM receipts WHERE payment_id = ?", [$id]),
        ]);
        $this->db->delete('payment_history', "payment_id = ?", [$id]);
        $this->db->delete('receipts', "payment_id = ?", [$id]);
        $this->db->delete('payments', "id = ?", [$id]);
        $this->logActivity('delete_walk_in_payment', "Walk-in payment {$paymentCode} ({$paymentType}, {$paymentAmount}) deleted by {$actorLabel} â€” student: {$studentName}");
        $notified = $this->notifyRecordDeleted('payment', 'Walk-in payment ' . $paymentCode);
        $this->flash('success', $this->recordDeletedFlash('Walk-in payment ' . $paymentCode, $notified));
        $this->redirect($baseUrl . '/students/walk-in-payment');
    }

    /**
     * Walk-In Student Payment – lets admin/manager pick a registered student
     * who has NO approved reservation, assign a room, collect payment, and
     * automatically promote them to tenant (approved reservation created).
     */
    protected function processWalkInStudentPayment(string $baseUrl): void
    {
        $isAdmin   = $baseUrl === '/admin';
        $view      = $isAdmin ? 'admin.walk_in_student_payments' : 'manager.walk_in_student_payments';
        $layout    = $isAdmin ? 'admin' : 'manager';
        $pageUrl   = $baseUrl . '/students/walk-in-student-payment';

        $nonTenantStudents = $this->db->fetchAll(
            "SELECT s.id, s.first_name, s.last_name, s.student_id_number, u.email, u.status
             FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE s.id NOT IN (
                 SELECT r.student_id FROM reservations r WHERE r.status = 'approved'
             )
             ORDER BY s.first_name ASC, s.last_name ASC"
        );

        $availableRooms = $this->getAvailableRoomsForWalkIn();

        $studentId   = (int)$this->input('student_id', 0);
        $selectedStudent = null;
        $roomRent    = 0.0;
        $roomAdvance = 0.0;
        $roomLabel   = '';
        if ($studentId > 0) {
            foreach ($nonTenantStudents as $s) {
                if ((int)$s['id'] === $studentId) { $selectedStudent = $s; break; }
            }
            if (!$selectedStudent) {
                $this->flash('error', 'Student not found or already a tenant.');
                $this->redirect($pageUrl);
                return;
            }
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect($pageUrl);
                return;
            }

            $t = fn(string $k, string $d = ''): string => trim((string)$this->input($k, $d));
            $roomId        = (int)$this->input('room_id', 0);
            $paymentMethod = $t('payment_method');
            $reference     = $t('payment_reference');
            $notes         = trim((string)$this->input('payment_notes', ''));
            $paymentReceived = (float)$this->input('payment_received', 0);
            $moveInDate    = $t('move_in_date') ?: serverDate();
            $duration      = max(1, min(3, (int)$this->input('duration', 1)));

            $errors = [];
            if (!$selectedStudent)                        $errors[] = 'Select a student.';
            if ($roomId < 1)                              $errors[] = 'Select a room.';
            if (!in_array($paymentMethod, ['cash','gcash'])) $errors[] = 'Payment method is required.';
            if ($paymentReceived < 0)                     $errors[] = 'Payment amount is invalid.';
            if ($paymentMethod === 'gcash' && $paymentReceived > 0 && empty($reference)) $errors[] = 'Reference / OR Number is required for GCash payments.';
            $maxMoveIn = date('Y-m-d', strtotime('+3 months'));
            if ($moveInDate < serverDate() || $moveInDate > $maxMoveIn) $errors[] = 'Move-in date must be within the next 3 months.';

            $room = null;
            if ($roomId > 0) {
                $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$roomId]);
                if (!$room) { $errors[] = 'Selected room not found.'; }
                else {
                    if ($room['status'] === 'under_maintenance') $errors[] = 'Room is under maintenance.';
                    if ((int)$room['current_occupancy'] >= (int)$room['max_capacity']) $errors[] = 'Room is already full.';
                }
            }

            $roomRent    = $room ? (float)$room['monthly_rent'] : 0.0;
            $roomAdvance = $room ? (float)$room['advance_payment'] : 0.0;
            $roomLabel   = $room ? "Room {$room['room_number']} ({$room['room_name']})" : '';

            if (!empty($errors)) {
                $this->view($view, [
                    'pageTitle'          => 'Walk-In Student Payment',
                    'nonTenantStudents'  => $nonTenantStudents,
                    'availableRooms'     => $availableRooms,
                    'studentId'          => $studentId,
                    'selectedStudent'    => $selectedStudent,
                    'roomRent'           => $roomRent,
                    'roomAdvance'        => $roomAdvance,
                    'roomLabel'          => $roomLabel,
                    'errors'             => $errors,
                    'old'                => [
                        'room_id'          => $roomId,
                        'payment_method'   => $paymentMethod,
                        'payment_reference'=> $reference,
                        'payment_notes'    => $notes,
                        'payment_received' => $paymentReceived,
                        'move_in_date'     => $moveInDate,
                        'duration'         => $duration,
                    ],
                    'baseUrl'            => $baseUrl,
                    'flashMessages'      => $this->getFlashMessages(),
                ], $layout);
                return;
            }

            $reservationCode = generateCode('RES');
            $reservationId = $this->db->insert('reservations', [
                'reservation_code' => $reservationCode,
                'student_id'       => $selectedStudent['id'],
                'room_id'          => $roomId,
                'move_in_date'     => $moveInDate,
                'expected_duration'=> $duration,
                'rent_amount'      => $roomRent,
                'status'           => 'approved',
                'approved_at'      => serverDateTime(),
                'moved_in_at'      => $moveInDate . ' 12:00:00',
            ]);

            $reservation = [
                'id'                => $reservationId,
                'reservation_code'  => $reservationCode,
                'student_id'        => $selectedStudent['id'],
                'room_id'           => $roomId,
                'move_in_date'      => $moveInDate,
                'expected_duration' => $duration,
            ];

            $studentUserId = 0;
            $su = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$selectedStudent['id']]);
            if ($su) $studentUserId = (int)$su['user_id'];

            $this->updateRoomOccupancy($roomId);
            $this->createApprovedReservationBilling($studentUserId, $reservation, $room);

            $allocResult = null;
            if ($paymentReceived > 0) {
                $allocResult = $this->applyWalkInPayment(
                    (int)$selectedStudent['id'], $reservationId, $studentUserId,
                    $room, $paymentMethod, $paymentReceived, $reference, $notes
                );
            }

            $this->db->insert('notifications', [
                'user_id'       => $studentUserId,
                'title'         => 'Walk-In Registration Approved',
                'message'       => "You have been registered as a walk-in tenant. Room {$room['room_number']} ({$room['room_name']}) is assigned to you.",
                'type'          => 'reservation',
                'reference_id'  => $reservationId,
                'reference_type'=> 'reservation',
            ]);

            $notified = $this->notifyWalkInRegistration(
                $selectedStudent,
                $room,
                $paymentReceived,
                $paymentMethod,
                $reservationCode,
                $moveInDate
            );

            $this->logActivity('walk_in_student_payment', "Walk-in student payment: {$selectedStudent['first_name']} {$selectedStudent['last_name']} registered as tenant (Room {$room['room_number']}) with payment of " . formatCurrency($paymentReceived) . '.');

            $msg = "{$selectedStudent['first_name']} {$selectedStudent['last_name']} is now a tenant. Room {$room['room_number']} assigned.";
            if ($allocResult) {
                $msg .= " Payment of " . formatCurrency($allocResult['allocated']) . " recorded.";
            }
            if ($notified > 0) {
                $msg .= " Confirmation emailed to {$notified} recipient" . ($notified !== 1 ? 's' : '') . '.';
            }
            $this->flash('success', $msg);
            $this->redirect($baseUrl . '/tenants');
            return;
        }

        $this->view($view, [
            'pageTitle'          => 'Walk-In Student Payment',
            'nonTenantStudents'  => $nonTenantStudents,
            'availableRooms'     => $availableRooms,
            'studentId'          => $studentId,
            'selectedStudent'    => $selectedStudent,
            'roomRent'           => $roomRent,
            'roomAdvance'        => $roomAdvance,
            'roomLabel'          => $roomLabel,
            'errors'             => [],
            'old'                => [],
            'baseUrl'            => $baseUrl,
            'flashMessages'      => $this->getFlashMessages(),
        ], $layout);
    }

    protected function paginate(string $table, string $where = '1', array $params = [], int $perPage = 10, int $page = 1): array {
        $total = $this->db->count($table, $where, $params);
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }
}
