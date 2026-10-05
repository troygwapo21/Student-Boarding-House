<?php

class AdminController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole('super_admin');
    }

    private const OTP_TTL_SECONDS = 300;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;

    private function getAdmin(): ?array {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    public function dashboard(): void {
        $this->processMonthlyPayments();

        $totalRooms = $this->db->count('rooms');
        $availableRooms = $this->db->count('rooms', "status = 'available'");
        $occupiedRooms = $this->db->count('rooms', "status = 'occupied'");
        $reservedRooms = $this->db->count('rooms', "status = 'reserved'");
        $maintenanceRooms = $this->db->count('rooms', "status = 'under_maintenance'");
        $fullyOccupiedRooms = $this->db->count('rooms', "status = 'occupied' AND current_occupancy >= max_capacity");
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        $totalRevenue = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0")['total'] ?? 0);
        $thisMonthRevenue = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND MONTH(COALESCE(paid_at, created_at)) = MONTH(CURDATE()) AND YEAR(COALESCE(paid_at, created_at)) = YEAR(CURDATE())")['total'] ?? 0);
        $lastMonthRevenue = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND MONTH(COALESCE(paid_at, created_at)) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(COALESCE(paid_at, created_at)) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")['total'] ?? 0);
        $todayRevenue = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) AS total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND DATE(COALESCE(paid_at, created_at)) = CURDATE()")['total'] ?? 0);
        $thisWeekRevenue = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) AS total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND YEARWEEK(COALESCE(paid_at, created_at), 1) = YEARWEEK(CURDATE(), 1)")['total'] ?? 0);
        $lastWeekRevenue = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) AS total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND YEARWEEK(COALESCE(paid_at, created_at), 1) = YEARWEEK(CURDATE(), 1) - 1")['total'] ?? 0);
        $avgMonthlyRevenue = (float)($this->db->fetch("SELECT COALESCE(AVG(m.total), 0) AS avg_total FROM (SELECT DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') AS ym, SUM(amount_paid - COALESCE(refunded_amount,0)) AS total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 GROUP BY ym) m")['avg_total'] ?? 0);
        $bestMonth = $this->db->fetch("SELECT DATE_FORMAT(COALESCE(paid_at, created_at), '%M %Y') AS label, SUM(amount_paid - COALESCE(refunded_amount,0)) AS total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 GROUP BY DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') ORDER BY total DESC LIMIT 1");
        $analyticsPaymentRows = $this->db->fetchAll(
            "SELECT payment_method AS method, amount_paid, COALESCE(refunded_amount, 0) AS refunded_amount, status
             FROM payments
             WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0"
        );
        $analyticsChartPaymentRows = $this->db->fetchAll(
            "SELECT COALESCE(paid_at, created_at) AS paid_at, amount_paid, COALESCE(refunded_amount, 0) AS refunded_amount, status, payment_type
             FROM payments
             WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0
             AND COALESCE(paid_at, created_at) >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 11 MONTH)"
        );
        $analyticsReservationRows = $this->db->fetchAll(
            "SELECT created_at FROM reservations
             WHERE created_at >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 11 MONTH)"
        );
        $analyticsRoomRows = $this->db->fetchAll(
            "SELECT room_type, status, current_occupancy, max_capacity FROM rooms"
        );
        $analyticsStudentRows = $this->db->fetchAll(
            "SELECT gender, COALESCE(NULLIF(year_level,''), 'N/A') AS year_level FROM students"
        );
        $newStudentsThisMonth = $this->db->count('students', "created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
        $newReservationsThisMonth = $this->db->count('reservations', "created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
        $activeTenants = (int)($this->db->fetch("SELECT COUNT(DISTINCT student_id) AS c FROM reservations WHERE status = 'approved'")['c'] ?? 0);
        $walkinCollectionsThisMonth = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(p.amount_paid), 0) AS t FROM payment_history ph JOIN payments p ON ph.payment_id = p.id WHERE ph.action = 'walk_in_payment' AND ph.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        )['t'] ?? 0);

        $newMessages = $this->db->count('contact_messages', "status = 'new'");

        $data = [
            'pageTitle' => 'Admin Dashboard',
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'reservedRooms' => $reservedRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'fullyOccupiedRooms' => $fullyOccupiedRooms,
            'totalStudents' => $this->db->count('students'),
            'totalManagers' => $this->db->count('managers'),
            'pendingReservations' => $this->db->count('reservations', "status = 'pending'"),
            'totalRevenue' => $totalRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'todayRevenue' => $todayRevenue,
            'thisWeekRevenue' => $thisWeekRevenue,
            'lastWeekRevenue' => $lastWeekRevenue,
            'avgMonthlyRevenue' => $avgMonthlyRevenue,
            'bestMonth' => $bestMonth,
            'analyticsAsOfDate' => serverDate('Y-m-d'),
            'analyticsPaymentRows' => $analyticsPaymentRows,
            'analyticsChartPaymentRows' => $analyticsChartPaymentRows,
            'analyticsReservationRows' => $analyticsReservationRows,
            'analyticsRoomRows' => $analyticsRoomRows,
            'analyticsStudentRows' => $analyticsStudentRows,
            'newStudentsThisMonth' => $newStudentsThisMonth,
            'newReservationsThisMonth' => $newReservationsThisMonth,
            'activeTenants' => $activeTenants,
            'walkinCollectionsThisMonth' => $walkinCollectionsThisMonth,
            'pendingPayments' => $this->db->count('payments', "status = 'pending'"),
            'openMaintenance' => $this->db->count('maintenance_requests', "status IN ('pending','in_progress')"),
            'openComplaints' => $this->db->count('complaints', "status IN ('open','under_review')"),
            'unreadFeedback' => $this->db->count('feedback', "status = 'new'"),
            'newMessages' => $newMessages,
            'occupancyRate' => $occupancyRate,
            'roomStatus' => $this->db->fetchAll(
                "SELECT s.status, COUNT(r.id) as count FROM (SELECT 'available' AS status UNION SELECT 'occupied' UNION SELECT 'reserved' UNION SELECT 'under_maintenance') s LEFT JOIN rooms r ON r.status = s.status GROUP BY s.status"
            ),
            'recentActivity' => $this->db->fetchAll(
                "SELECT al.*, u.email FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 8"
            ),
            'recentReservations' => $this->db->fetchAll(
                "SELECT r.*, s.first_name, s.middle_name, s.last_name, s.suffix, rm.room_name, rm.room_number FROM reservations r JOIN students s ON r.student_id = s.id JOIN rooms rm ON r.room_id = rm.id ORDER BY r.created_at DESC LIMIT 5"
            ),
            'recentPayments' => $this->db->fetchAll(
                "SELECT p.*, s.first_name, s.middle_name, s.last_name, s.suffix FROM payments p JOIN students s ON p.student_id = s.id ORDER BY p.created_at DESC LIMIT 5"
            ),
            'notifications' => $this->db->fetchAll(
                "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
                [$_SESSION['user_id']]
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.dashboard', $data, 'admin');
    }

    public function notifications(): void {
        $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
            [$_SESSION['user_id']]
        );
        $this->view('admin.notifications', [
            'pageTitle' => 'Notifications',
            'notifications' => $notifications,
            'flashMessages' => $this->getFlashMessages(),
        ], 'admin');
    }

    public function notificationsRead(): void {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/notifications');
            return;
        }
        $notificationId = (int)$this->input('notification_id');
        if ($notificationId) {
            $this->db->update('notifications', ['is_read' => 1], "id = ? AND user_id = ?", [$notificationId, $_SESSION['user_id']]);
        } else {
            $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
        }
        $this->redirect('/admin/notifications');
    }

    public function notificationsClear(): void {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/notifications');
            return;
        }
        $this->db->delete('notifications', "user_id = ?", [$_SESSION['user_id']]);
        $this->logActivity('clear_notifications', 'Cleared all notifications');
        $this->flash('success', 'All notifications cleared.');
        $this->redirect('/admin/notifications');
    }

    public function rooms(): void {
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $roomType = $this->input('room_type', '');

        $where = "1";
        $params = [];
        if ($search) {
            $where .= " AND (r.room_name LIKE ? OR r.room_number LIKE ?)";
            $params = array_merge($params, ["%{$search}%", "%{$search}%"]);
        }
        if ($status) {
            $where .= " AND r.status = ?";
            $params[] = $status;
        }
        if ($roomType) {
            $where .= " AND r.room_type = ?";
            $params[] = $roomType;
        }

        $page = max(1, (int)$this->input('page', 1));
        $perPage = 15;
        $total = $this->db->fetch("SELECT COUNT(*) as c FROM rooms r WHERE {$where}", $params)['c'];
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $rooms = $this->db->fetchAll(
            "SELECT r.*, (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') as current_occupancy, (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image, (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count FROM rooms r WHERE {$where} ORDER BY r.room_number ASC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $data = [
            'pageTitle' => 'Manage Rooms',
            'rooms' => $rooms,
            'search' => $search,
            'status' => $status,
            'roomType' => $roomType,
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages],
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.rooms', $data, 'admin');
    }

    public function roomNextNumber(): void {
        header('Content-Type: application/json');
        $maxRoom = $this->db->fetch("SELECT MAX(CAST(room_number AS UNSIGNED)) as max_num FROM rooms WHERE room_number REGEXP '^[0-9]+$'");
        $nextNumber = ($maxRoom && $maxRoom['max_num'] !== null) ? (int)$maxRoom['max_num'] + 1 : 1;
        echo json_encode(['next_number' => (string)$nextNumber]);
    }

    public function roomCreate(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/room/create');
                return;
            }

            $roomNumber = $this->sanitize($this->input('room_number', ''));
            $roomName = $this->sanitize($this->input('room_name', ''));
            $monthlyRent = validateMoney($this->input('monthly_rent', ''), true, 0.01);
            $advancePayment = $monthlyRent;
            $maxCapacity = (int)$this->input('max_capacity', 1);
            $description = $this->sanitize($this->input('description', ''));
            $floor = (int)$this->input('floor', 0);
            $sizeSqm = (float)$this->input('size_sqm', 0);
            $roomType = $this->input('room_type', 'bedspacer');
            $validTypes = ['bedspacer', 'single', 'studio'];
            if (!in_array($roomType, $validTypes)) $roomType = 'bedspacer';

            if (empty($roomNumber) || empty($roomName) || $monthlyRent === null || $monthlyRent <= 0) {
                $this->flash('error', 'Room number, name, and a valid monthly rent (e.g., 100) are required.');
                $this->redirect('/admin/room/create');
                return;
            }

            $existing = $this->db->fetch("SELECT id FROM rooms WHERE room_number = ?", [$roomNumber]);
            if ($existing) {
                $this->flash('error', 'Room number already exists.');
                $this->redirect('/admin/room/create');
                return;
            }

            $roomId = $this->db->insert('rooms', [
                'room_number' => $roomNumber,
                'room_name' => $roomName,
                'monthly_rent' => $monthlyRent,
                'advance_payment' => $advancePayment,
                'max_capacity' => $maxCapacity,
                'description' => $description,
                'status' => 'available',
                'floor' => $floor ?: null,
                'size_sqm' => $sizeSqm ?: null,
                'has_bathroom' => $this->input('has_bathroom') ? 1 : 0,
                'has_balcony' => $this->input('has_balcony') ? 1 : 0,
                'has_aircon' => $this->input('has_aircon') ? 1 : 0,
                'house_rules' => $this->sanitize($this->input('house_rules', '')),
                'furniture' => $this->sanitize($this->input('furniture', '')),
                'room_type' => $roomType,
                'is_featured' => $this->input('is_featured') ? 1 : 0,
            ]);

            $amenityIds = $this->input('amenities') ?: [];
            if (!is_array($amenityIds)) $amenityIds = [$amenityIds];
            foreach ($amenityIds as $amenityId) {
                $this->db->insert('room_amenities', [
                    'room_id' => $roomId,
                    'amenity_id' => (int)$amenityId,
                ]);
            }

            if (!empty($_FILES['room_images']['name'][0])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $sortOrder = 0;
                $fileCount = count($_FILES['room_images']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $file = [
                        'name' => $_FILES['room_images']['name'][$i],
                        'type' => $_FILES['room_images']['type'][$i],
                        'tmp_name' => $_FILES['room_images']['tmp_name'][$i],
                        'error' => $_FILES['room_images']['error'][$i],
                        'size' => $_FILES['room_images']['size'][$i],
                    ];
                    if ($file['error'] !== UPLOAD_ERR_OK) continue;
                    $uploaded = $this->uploadFile($file, 'rooms', $allowed, 5242880);
                    if ($uploaded) {
                        $this->db->insert('room_images', [
                            'room_id' => $roomId,
                            'image_path' => $uploaded,
                            'is_primary' => $i === 0 ? 1 : 0,
                            'sort_order' => $sortOrder++,
                        ]);
                    }
                }
            }

            $this->updateRoomOccupancy($roomId);

            $this->logActivity('create_room', "Room {$roomNumber} created");

            $notified = $this->notifyStudentsNewRoom([
                'room_number' => $roomNumber,
                'room_name' => $roomName,
                'room_type' => $roomType,
                'monthly_rent' => $monthlyRent,
            ]);
            $successMsg = 'Room created successfully.';
            if ($notified > 0) {
                $successMsg .= ' New room notified to ' . $notified . ' registered student' . ($notified !== 1 ? 's' : '') . '.';
            }
            $this->flash('success', $successMsg);
            $this->redirect('/admin/rooms');
            return;
        }

        $defaultRentRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'monthly_rent'");
        $defaultAdvanceRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'advance_payment'");
        $data = [
            'pageTitle' => 'Create Room',
            'room' => [
                'monthly_rent' => $defaultRentRow ? (int)$defaultRentRow['setting_value'] : 0,
                'advance_payment' => $defaultAdvanceRow ? (int)$defaultAdvanceRow['setting_value'] : 0,
            ],
            'amenities' => $this->db->fetchAll("SELECT * FROM amenities WHERE status = 'active'"),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.room_form', $data, 'admin');
    }

    public function roomEdit(): void {
        $id = (int)$this->input('id');
        $room = $this->db->fetch(
            "SELECT r.*, (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') as current_occupancy FROM rooms r WHERE r.id = ?",
            [$id]
        );
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('/admin/rooms');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect("/admin/room/edit/{$id}");
                return;
            }

            $setPrimary = $this->input('set_primary');
            if ($setPrimary) {
                $setPrimaryId = (int)$setPrimary;
                $this->db->query("UPDATE room_images SET is_primary = 0 WHERE room_id = ?", [$id]);
                $this->db->query("UPDATE room_images SET is_primary = 1 WHERE id = ? AND room_id = ?", [$setPrimaryId, $id]);
                $this->logActivity('set_primary_image', "Primary image set to #{$setPrimaryId} for room #{$id}");
                $this->flash('success', 'Primary image updated.');
                $this->redirect("/admin/room/edit/{$id}");
                return;
            }

            $roomNumber = $this->sanitize($this->input('room_number', ''));
            $roomName = $this->sanitize($this->input('room_name', ''));
            $monthlyRent = validateMoney($this->input('monthly_rent', ''), true, 0.01);
            $advancePayment = $monthlyRent;
            $maxCapacity = (int)$this->input('max_capacity', 1);
            $status = $this->input('status', 'available');
            $description = $this->sanitize($this->input('description', ''));
            $floor = (int)$this->input('floor', 0);
            $sizeSqm = (float)$this->input('size_sqm', 0);
            $roomType = $this->input('room_type', 'bedspacer');
            $validTypes = ['bedspacer', 'single', 'studio'];
            if (!in_array($roomType, $validTypes)) $roomType = $room['room_type'];

            if (empty($roomNumber) || empty($roomName) || $monthlyRent === null || $monthlyRent <= 0) {
                $this->flash('error', 'Room number, name, and a valid monthly rent (e.g., 100) are required.');
                $this->redirect("/admin/room/edit/{$id}");
                return;
            }
            if ($maxCapacity < 1) {
                $this->flash('error', 'Max capacity must be at least 1.');
                $this->redirect("/admin/room/edit/{$id}");
                return;
            }
            $currentOccupancy = (int)$room['current_occupancy'];
            if ($maxCapacity < $currentOccupancy) {
                $this->flash('error', "Cannot set capacity below the current occupancy ({$currentOccupancy}). Remove boarders first.");
                $this->redirect("/admin/room/edit/{$id}");
                return;
            }

            $existing = $this->db->fetch("SELECT id FROM rooms WHERE room_number = ? AND id != ?", [$roomNumber, $id]);
            if ($existing) {
                $this->flash('error', 'Room number already exists.');
                $this->redirect("/admin/room/edit/{$id}");
                return;
            }

            $validStatuses = ['available', 'reserved', 'occupied', 'under_maintenance'];
            if (!in_array($status, $validStatuses)) $status = $room['status'];

            $this->db->update('rooms', [
                'room_number' => $roomNumber,
                'room_name' => $roomName,
                'monthly_rent' => $monthlyRent,
                'advance_payment' => $advancePayment,
                'max_capacity' => $maxCapacity,
                'description' => $description,
                'status' => $status,
                'floor' => $floor ?: null,
                'size_sqm' => $sizeSqm ?: null,
                'has_bathroom' => $this->input('has_bathroom') ? 1 : 0,
                'has_balcony' => $this->input('has_balcony') ? 1 : 0,
                'has_aircon' => $this->input('has_aircon') ? 1 : 0,
                'house_rules' => $this->sanitize($this->input('house_rules', '')),
                'furniture' => $this->sanitize($this->input('furniture', '')),
                'room_type' => $roomType,
                'is_featured' => $this->input('is_featured') ? 1 : 0,
            ], "id = ?", [$id]);

            $this->db->delete('room_amenities', "room_id = ?", [$id]);
            $amenityIds = $this->input('amenities') ?: [];
            if (!is_array($amenityIds)) $amenityIds = [$amenityIds];
            foreach ($amenityIds as $amenityId) {
                $this->db->insert('room_amenities', [
                    'room_id' => $id,
                    'amenity_id' => (int)$amenityId,
                ]);
            }

            $imagesAdded = 0;
            if (!empty($_FILES['room_images']['name'][0])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $maxSort = $this->db->fetch("SELECT COALESCE(MAX(sort_order), -1) as m FROM room_images WHERE room_id = ?", [$id])['m'];
                $existingCount = $this->db->fetch("SELECT COUNT(*) as c FROM room_images WHERE room_id = ?", [$id])['c'];
                $sortOrder = $maxSort + 1;
                $fileCount = count($_FILES['room_images']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $file = [
                        'name' => $_FILES['room_images']['name'][$i],
                        'type' => $_FILES['room_images']['type'][$i],
                        'tmp_name' => $_FILES['room_images']['tmp_name'][$i],
                        'error' => $_FILES['room_images']['error'][$i],
                        'size' => $_FILES['room_images']['size'][$i],
                    ];
                    if ($file['error'] !== UPLOAD_ERR_OK) continue;
                    $uploaded = $this->uploadFile($file, 'rooms', $allowed, 5242880);
                    if ($uploaded) {
                        $this->db->insert('room_images', [
                            'room_id' => $id,
                            'image_path' => $uploaded,
                            'is_primary' => $existingCount === 0 && $imagesAdded === 0 ? 1 : 0,
                            'sort_order' => $sortOrder++,
                        ]);
                        $imagesAdded++;
                        $existingCount++;
                    }
                }
            }

            $this->updateRoomOccupancy($id);

            $notified = $this->notifyRoomUpdate([
                'room_number' => $roomNumber,
                'room_name' => $roomName,
                'room_type' => $roomType,
                'monthly_rent' => $monthlyRent,
                'max_capacity' => $maxCapacity,
                'status' => $status,
            ], $room);

            $this->logActivity('update_room', "Room {$roomNumber} updated");
            $successMsg = 'Room updated successfully.';
            if ($imagesAdded > 0) {
                $successMsg .= " {$imagesAdded} image(s) added.";
            }
            if ($notified > 0) {
                $successMsg .= ' Update notified to ' . $notified . ' recipient' . ($notified !== 1 ? 's' : '') . '.';
            }
            $this->flash('success', $successMsg);
            $this->redirect("/admin/room/edit/{$id}");
            return;
        }

        $roomAmenities = $this->db->fetchAll("SELECT amenity_id FROM room_amenities WHERE room_id = ?", [$id]);
        $selectedAmenities = array_column($roomAmenities, 'amenity_id');
        $images = $this->db->fetchAll("SELECT * FROM room_images WHERE room_id = ? ORDER BY sort_order", [$id]);

        $data = [
            'pageTitle' => 'Edit Room',
            'room' => $room,
            'amenities' => $this->db->fetchAll("SELECT * FROM amenities WHERE status = 'active'"),
            'selectedAmenities' => $selectedAmenities,
            'images' => $images,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.room_form', $data, 'admin');
    }

    public function roomDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/rooms');
            return;
        }
        $id = (int)$this->input('id');
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('/admin/rooms');
            return;
        }

        $activeReservations = $this->db->fetchAll(
            "SELECT * FROM reservations WHERE room_id = ? AND status IN ('pending','approved')",
            [$id]
        );
        $archived = $this->archiveRecord('room', $id, $room, [
            'room_images'   => $this->db->fetchAll("SELECT * FROM room_images WHERE room_id = ?", [$id]),
            'room_amenities'=> $this->db->fetchAll("SELECT * FROM room_amenities WHERE room_id = ?", [$id]),
            'reservations'  => $activeReservations,
        ]);
        if (!$archived) {
            $this->flash('error', 'Could not archive this room. Deletion cancelled.');
            $this->redirect('/admin/rooms');
            return;
        }

        if (!empty($activeReservations)) {
            $this->db->delete('reservations', "room_id = ? AND status IN ('pending','approved')", [$id]);
        }

        $this->db->delete('room_amenities', "room_id = ?", [$id]);
        $this->db->delete('room_images', "room_id = ?", [$id]);
        $this->db->delete('rooms', "id = ?", [$id]);
        $this->logActivity('delete_room', "Room {$room['room_number']} deleted (active reservations archived with the room)");
        $roomLabel = trim((string)($room['room_number'] ?? '') . ' ' . (string)($room['room_name'] ?? ''));
        $roomLabel = $roomLabel !== '' ? $roomLabel : 'Dorm Room';
        $notified = $this->notifyRecordDeleted('room', 'Room ' . $roomLabel);
        $this->flash('success', $this->recordDeletedFlash('Room ' . $roomLabel, $notified) . ' Active tenants and capacity will be restored with this room from Archive & Recovery.');
        $this->redirect('/admin/rooms');
    }

    public function roomStatusUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            return;
        }
        $id = (int)$this->input('id');
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);
        if (!$room) {
            $this->json(['success' => false, 'message' => 'Room not found.'], 404);
            return;
        }
        $status = $this->input('status', '');
        $validStatuses = ['available', 'reserved', 'occupied', 'under_maintenance'];
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Invalid status value.'], 400);
            return;
        }
        $currentOccupancy = (int)$this->db->fetch(
            "SELECT COUNT(*) as c FROM reservations WHERE room_id = ? AND status = 'approved'",
            [$id]
        )['c'];
        if ($currentOccupancy > 0 && in_array($status, ['available', 'reserved'])) {
            $this->json(['success' => false, 'message' => "Cannot mark this room as " . str_replace('_', ' ', $status) . " â€” it currently has {$currentOccupancy} boarder(s)."], 400);
            return;
        }
        $this->db->update('rooms', ['status' => $status], "id = ?", [$id]);
        $this->updateRoomOccupancy($id);
        $finalStatus = $this->db->fetch("SELECT status FROM rooms WHERE id = ?", [$id])['status'];
        $label = ucfirst(str_replace('_', ' ', $finalStatus));
        $message = ($finalStatus === $status)
            ? "Room status updated to {$label}."
            : "Status adjusted to {$label} to match the room's actual occupancy.";
        $this->logActivity('update_room_status', "Room {$room['room_number']} status changed to {$finalStatus}");
        $this->json(['success' => true, 'status' => $finalStatus, 'message' => $message]);
    }

    public function roomCapacityUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            return;
        }
        $id = (int)$this->input('id');
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);
        if (!$room) {
            $this->json(['success' => false, 'message' => 'Room not found.'], 404);
            return;
        }
        $maxCapacity = (int)$this->input('max_capacity', 0);
        if ($maxCapacity < 1) {
            $this->json(['success' => false, 'message' => 'Capacity must be at least 1.'], 400);
            return;
        }
        $currentOccupancy = (int)$this->db->fetch(
            "SELECT COUNT(*) as c FROM reservations WHERE room_id = ? AND status = 'approved'",
            [$id]
        )['c'];
        if ($maxCapacity < $currentOccupancy) {
            $this->json(['success' => false, 'message' => "Cannot set below current occupancy ({$currentOccupancy}). Remove boarders first."], 400);
            return;
        }
        $this->db->update('rooms', ['max_capacity' => $maxCapacity], "id = ?", [$id]);
        $this->updateRoomOccupancy($id);
        $final = $this->db->fetch("SELECT current_occupancy, status FROM rooms WHERE id = ?", [$id]);
        $this->logActivity('update_room_capacity', "Room {$room['room_number']} capacity changed from {$room['max_capacity']} to {$maxCapacity}");
        $this->json([
            'success' => true,
            'status' => $final['status'],
            'occupancy' => (int)$final['current_occupancy'],
            'message' => "Room capacity updated to {$maxCapacity}."
        ]);
    }

    public function roomNumberUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            return;
        }
        $id = (int)$this->input('id');
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);
        if (!$room) {
            $this->json(['success' => false, 'message' => 'Room not found.'], 404);
            return;
        }
        $roomNumber = $this->sanitize($this->input('room_number', ''));
        if (trim($roomNumber) === '') {
            $this->json(['success' => false, 'message' => 'Room number is required.'], 400);
            return;
        }
        $existing = $this->db->fetch("SELECT id FROM rooms WHERE room_number = ? AND id != ?", [$roomNumber, $id]);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Room number already exists.'], 400);
            return;
        }
        $this->db->update('rooms', ['room_number' => $roomNumber], "id = ?", [$id]);
        $this->logActivity('update_room_number', "Room #{$id} room number changed from {$room['room_number']} to {$roomNumber}");
        $this->json(['success' => true, 'room_number' => $roomNumber, 'message' => 'Room number updated.']);
    }

    public function roomRentUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            return;
        }
        $id = (int)$this->input('id');
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);
        if (!$room) {
            $this->json(['success' => false, 'message' => 'Room not found.'], 404);
            return;
        }
        $monthlyRent = validateMoney($this->input('monthly_rent', ''), true, 0.01);
        if ($monthlyRent === null) {
            $this->json(['success' => false, 'message' => 'Please enter a valid monthly rent (e.g., 100).'], 400);
            return;
        }
        $this->db->update('rooms', ['monthly_rent' => $monthlyRent, 'advance_payment' => $monthlyRent], "id = ?", [$id]);
        $this->logActivity('update_room_rent', "Room {$room['room_number']} monthly rent changed from " . formatCurrency((float)$room['monthly_rent']) . " to " . formatCurrency($monthlyRent));
        $this->json(['success' => true, 'monthly_rent' => $monthlyRent, 'message' => 'Monthly rent updated.']);
    }

    public function roomInlineUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            return;
        }
        $id = (int)$this->input('id');
        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);
        if (!$room) {
            $this->json(['success' => false, 'message' => 'Room not found.'], 404);
            return;
        }

        $update = [];
        $currentOccupancy = (int)$this->db->fetch(
            "SELECT COUNT(*) as c FROM reservations WHERE room_id = ? AND status = 'approved'",
            [$id]
        )['c'];

        $roomNumber = $this->sanitize(trim($this->input('room_number', '')));
        if ($roomNumber !== '' && $roomNumber !== (string)$room['room_number']) {
            $existing = $this->db->fetch("SELECT id FROM rooms WHERE room_number = ? AND id != ?", [$roomNumber, $id]);
            if ($existing) {
                $this->json(['success' => false, 'message' => 'Room number already exists.'], 400);
                return;
            }
            $update['room_number'] = $roomNumber;
        }

        $rentInput = $this->input('monthly_rent', '');
        $monthlyRent = $rentInput !== '' ? validateMoney($rentInput, true, 0.01) : null;
        if ($rentInput !== '' && $monthlyRent === null) {
            $this->json(['success' => false, 'message' => 'Please enter a valid monthly rent (e.g., 100).'], 400);
            return;
        }
        if ($monthlyRent !== null && (float)$monthlyRent !== (float)$room['monthly_rent']) {
            $update['monthly_rent'] = $monthlyRent;
            $update['advance_payment'] = $monthlyRent;
        }

        $capacityInput = $this->input('max_capacity', '');
        if ($capacityInput !== '') {
            $newCap = (int)$capacityInput;
            if ($newCap < 1) {
                $this->json(['success' => false, 'message' => 'Capacity must be at least 1.'], 400);
                return;
            }
            if ($newCap < $currentOccupancy) {
                $this->json(['success' => false, 'message' => "Cannot set below current occupancy ({$currentOccupancy}). Remove boarders first."], 400);
                return;
            }
            if ($newCap !== (int)$room['max_capacity']) {
                $update['max_capacity'] = $newCap;
            }
        }

        $status = $this->input('status', '');
        $validStatuses = ['available', 'reserved', 'occupied', 'under_maintenance'];
        if ($status !== '' && in_array($status, $validStatuses) && $status !== (string)$room['status']) {
            if ($currentOccupancy > 0 && in_array($status, ['available', 'reserved'])) {
                $this->json(['success' => false, 'message' => "Cannot mark this room as " . str_replace('_', ' ', $status) . " — it currently has {$currentOccupancy} boarder(s)."], 400);
                return;
            }
            $update['status'] = $status;
        }

        if (empty($update)) {
            $this->json(['success' => true, 'updated' => false, 'message' => 'No changes were made.']);
            return;
        }

        $oldRoom = $room;
        $this->db->update('rooms', $update, "id = ?", [$id]);
        $this->updateRoomOccupancy($id);
        $finalRoom = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$id]);

        $changed = array_map(
            fn($k) => str_replace('_', ' ', $k),
            array_keys($update)
        );
        $this->logActivity('update_room', "Room #{$id} updated (" . implode(', ', $changed) . ")");

        $notified = $this->notifyRoomUpdate($finalRoom, $oldRoom);

        $this->json([
            'success' => true,
            'updated' => true,
            'message' => 'Room updated.' . ($notified > 0 ? " Notified {$notified} recipient(s) by email." : ''),
        ]);
    }

    public function roomImageDelete(): void {
        $id = (int)$this->input('id');
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/rooms');
            return;
        }

        $image = $this->db->fetch("SELECT * FROM room_images WHERE id = ?", [$id]);
        if (!$image) {
            $this->flash('error', 'Image not found.');
            $this->redirect('/admin/rooms');
            return;
        }

        $roomId = $image['room_id'];
        $filePath = UPLOAD_PATH . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $wasPrimary = $image['is_primary'];
        $this->db->delete('room_images', "id = ?", [$id]);

        if ($wasPrimary) {
            $next = $this->db->fetch("SELECT id FROM room_images WHERE room_id = ? ORDER BY sort_order ASC LIMIT 1", [$roomId]);
            if ($next) {
                $this->db->update('room_images', ['is_primary' => 1], "id = ?", [$next['id']]);
            }
        }

        $this->logActivity('delete_room_image', "Room image #{$id} deleted");
        $notified = $this->notifyRecordDeleted('room', 'Room image');
        $this->flash('success', $this->recordDeletedFlash('the room image', $notified));
        $this->redirect("/admin/room/edit/{$roomId}");
    }

    public function roomImageReplace(): void {
        $id = (int)$this->input('id');
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/rooms');
            return;
        }

        $image = $this->db->fetch("SELECT * FROM room_images WHERE id = ?", [$id]);
        if (!$image) {
            $this->flash('error', 'Image not found.');
            $this->redirect('/admin/rooms');
            return;
        }

        $roomId = $image['room_id'];

        if (!isset($_FILES['replace_image']) || $_FILES['replace_image']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select a valid image to replace with.');
            $this->redirect("/admin/room/edit/{$roomId}");
            return;
        }

        $uploaded = $this->uploadFile($_FILES['replace_image'], 'rooms', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 5242880);
        if (!$uploaded) {
            $this->flash('error', 'Failed to upload image. Allowed: JPG, PNG, GIF, WebP. Max 5MB.');
            $this->redirect("/admin/room/edit/{$roomId}");
            return;
        }

        $oldPath = UPLOAD_PATH . $image['image_path'];
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }

        $this->db->update('room_images', ['image_path' => $uploaded], "id = ?", [$id]);

        $this->logActivity('replace_room_image', "Room image #{$id} replaced");
        $this->flash('success', 'Image replaced successfully.');
        $this->redirect("/admin/room/edit/{$roomId}");
    }

    public function roomImageAdd(): void {
        $afterId = (int)$this->input('id');
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/rooms');
            return;
        }

        $afterImage = $this->db->fetch("SELECT * FROM room_images WHERE id = ?", [$afterId]);
        if (!$afterImage) {
            $this->flash('error', 'Image not found.');
            $this->redirect('/admin/rooms');
            return;
        }

        $roomId = $afterImage['room_id'];

        if (!isset($_FILES['add_image']) || $_FILES['add_image']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select an image to add.');
            $this->redirect("/admin/room/edit/{$roomId}");
            return;
        }

        $uploaded = $this->uploadFile($_FILES['add_image'], 'rooms', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 5242880);
        if (!$uploaded) {
            $this->flash('error', 'Failed to upload image. Allowed: JPG, PNG, GIF, WebP. Max 5MB.');
            $this->redirect("/admin/room/edit/{$roomId}");
            return;
        }

        $nextImage = $this->db->fetch(
            "SELECT id, sort_order FROM room_images WHERE room_id = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1",
            [$roomId, $afterImage['sort_order']]
        );

        if ($nextImage) {
            $newOrder = ($afterImage['sort_order'] + $nextImage['sort_order']) / 2;
        } else {
            $newOrder = $afterImage['sort_order'] + 1;
        }

        $existingCount = $this->db->fetch("SELECT COUNT(*) as c FROM room_images WHERE room_id = ?", [$roomId])['c'];

        $this->db->insert('room_images', [
            'room_id' => $roomId,
            'image_path' => $uploaded,
            'is_primary' => $existingCount === 0 ? 1 : 0,
            'sort_order' => $newOrder,
        ]);

        $this->logActivity('add_room_image', "Image added after #{$afterId} to room #{$roomId}");
        $this->flash('success', 'Image added successfully.');
        $this->redirect("/admin/room/edit/{$roomId}");
    }

    public function roomImagesAdd(): void {
        $roomId = (int)$this->input('id');
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/rooms');
            return;
        }

        $room = $this->db->fetch("SELECT id FROM rooms WHERE id = ?", [$roomId]);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('/admin/rooms');
            return;
        }

        if (empty($_FILES['room_images']) || empty($_FILES['room_images']['name'][0])) {
            $this->flash('error', 'Please select at least one image to add.');
            $this->redirect("/admin/room/edit/{$roomId}");
            return;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSort = $this->db->fetch("SELECT COALESCE(MAX(sort_order), -1) as m FROM room_images WHERE room_id = ?", [$roomId])['m'];
        $existingCount = $this->db->fetch("SELECT COUNT(*) as c FROM room_images WHERE room_id = ?", [$roomId])['c'];
        $sortOrder = $maxSort + 1;
        $added = 0;
        $fileCount = count($_FILES['room_images']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $_FILES['room_images']['name'][$i],
                'type' => $_FILES['room_images']['type'][$i],
                'tmp_name' => $_FILES['room_images']['tmp_name'][$i],
                'error' => $_FILES['room_images']['error'][$i],
                'size' => $_FILES['room_images']['size'][$i],
            ];
            if ($file['error'] !== UPLOAD_ERR_OK) continue;

            $uploaded = $this->uploadFile($file, 'rooms', $allowed, 5242880);
                if ($uploaded) {
                    $this->db->insert('room_images', [
                        'room_id' => $roomId,
                        'image_path' => $uploaded,
                        'is_primary' => $existingCount === 0 && $added === 0 ? 1 : 0,
                        'sort_order' => $sortOrder++,
                    ]);
                    $added++;
                }
        }

        $this->logActivity('add_room_images', "Added {$added} image(s) to room #{$roomId}");
        $this->flash($added > 0 ? 'success' : 'error', $added > 0 ? "{$added} image(s) added successfully." : 'Failed to add images. Please check file type and size (JPG, PNG, GIF, WebP, max 5MB).');
        $this->redirect("/admin/room/edit/{$roomId}");
    }

    public function reservations(): void {
        $status = $this->input('status', '');
        $where = "1";
        $params = [];
        if ($status) {
            $where .= " AND r.status = ?";
            $params[] = $status;
        }

        $reservations = $this->db->fetchAll(
            "SELECT r.*, s.first_name, s.last_name, s.phone as student_phone, s.student_id_number, u.email as student_email, rm.room_name, rm.room_number, rm.monthly_rent, rm.advance_payment FROM reservations r JOIN students s ON r.student_id = s.id JOIN users u ON s.user_id = u.id JOIN rooms rm ON r.room_id = rm.id WHERE {$where} ORDER BY r.created_at DESC",
            $params
        );

        // Calculate final due date for each approved reservation based on payments
        foreach ($reservations as &$r) {
            $r['final_due_date'] = null;
            $r['move_out_month'] = null;
            $r['next_due_date'] = null;
            $r['months_from_advance'] = 0;
            $r['months_from_monthly'] = 0;
            $r['total_months_paid'] = 0;
            
            if ($r['status'] === 'approved' && !empty($r['move_in_date'])) {
                $studentId = $r['student_id'];
                $monthlyRent = (float)($r['monthly_rent'] ?? 0);
                $advancePayment = (float)($r['advance_payment'] ?? 0);
                $moveInDate = new DateTime($r['move_in_date']);
                $expectedDuration = (int)($r['expected_duration'] ?? 0);
                
                // Get all payments for this student for this reservation
                $payments = $this->db->fetchAll(
                    "SELECT * FROM payments WHERE student_id = ? AND reservation_id = ? AND payment_type IN ('monthly_rent', 'advance_payment') AND status IN ('paid', 'partially_paid') ORDER BY paid_at ASC",
                    [$studentId, $r['id']]
                );
                
                $totalMonthlyPaid = 0;
                $totalAdvancePaid = 0;
                
                foreach ($payments as $p) {
                    $amountPaid = (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
                    if ($p['payment_type'] === 'advance_payment') {
                        $totalAdvancePaid += $amountPaid;
                    }
                    if ($p['payment_type'] === 'monthly_rent') {
                        $totalMonthlyPaid += $amountPaid;
                    }
                }
                
                // Calculate months covered by advance payment
                $monthsFromAdvance = $monthlyRent > 0 ? floor($totalAdvancePaid / $monthlyRent) : 0;
                
                // Calculate months covered by monthly rent payments
                $monthsFromMonthly = $monthlyRent > 0 ? floor($totalMonthlyPaid / $monthlyRent) : 0;
                
                // Total months fully paid
                $totalMonthsPaid = $monthsFromAdvance + $monthsFromMonthly;
                
                // NEXT DUE DATE: When next monthly payment is due
                $nextDue = clone $moveInDate;
                $nextDue->modify('first day of next month');
                $nextDue->modify("+{$totalMonthsPaid} months");
                
                $today = new DateTime();
                $today->setTime(0, 0, 0);
                
                if ($nextDue <= $today) {
                    $nextDue->modify('first day of next month');
                }
                
                $r['next_due_date'] = $nextDue->format('Y-m-d');
                
                // FINAL DUE DATE (Lease End): Contractual end of tenancy
                if ($expectedDuration > 0) {
                    $finalDue = clone $moveInDate;
                    $finalDue->modify("+{$expectedDuration} months");
                    $r['final_due_date'] = $finalDue->format('Y-m-d');
                    $r['move_out_month'] = $finalDue->format('F Y');
                } else {
                    $r['final_due_date'] = $nextDue->format('Y-m-d');
                    $r['move_out_month'] = $nextDue->format('F Y');
                }
                
                $r['months_from_advance'] = $monthsFromAdvance;
                $r['months_from_monthly'] = $monthsFromMonthly;
                $r['total_months_paid'] = $totalMonthsPaid;
            }
        }
        unset($r);

        $data = [
            'pageTitle' => 'Manage Reservations',
            'reservations' => $reservations,
            'status' => $status,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.reservations', $data, 'admin');
    }

    public function reservationApprove(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/reservations');
            return;
        }
        $id = (int)$this->input('id');
        $reservation = $this->db->fetch("SELECT * FROM reservations WHERE id = ? AND status = 'pending'", [$id]);
        if (!$reservation) {
            $this->flash('error', 'Reservation not found or already processed.');
            $this->redirect('/admin/reservations');
            return;
        }

        $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ?", [$reservation['room_id']]);
        if ($room && $room['status'] === 'under_maintenance') {
            $this->flash('error', 'Cannot approve: room is under maintenance.');
            $this->redirect('/admin/reservations');
            return;
        }
        if ($room && $room['current_occupancy'] >= $room['max_capacity']) {
            $this->flash('error', 'Cannot approve: This room has reached its maximum capacity and is no longer available for reservation.');
            $this->redirect('/admin/reservations');
            return;
        }

        $this->db->update('reservations', [
            'status' => 'approved',
            'approved_at' => serverDateTime(),
            'moved_in_at' => !empty($reservation['move_in_date'])
                ? $reservation['move_in_date'] . ' ' . (!empty($reservation['move_in_time']) ? $reservation['move_in_time'] : '12:00:00')
                : serverDateTime(),
            'rent_amount' => $room ? (float)$room['monthly_rent'] : null,
        ], "id = ?", [$id]);

        $this->updateRoomOccupancy($reservation['room_id']);

        $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$reservation['student_id']]);
        $notified = false;
        if ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Reservation Approved',
                'message' => "Your reservation for Room {$room['room_number']} ({$room['room_name']}) has been approved.",
                'type' => 'reservation',
                'reference_id' => $id,
                'reference_type' => 'reservation',
            ]);

            $this->createApprovedReservationBilling((int)$student['user_id'], $reservation, $room);

            $studentInfo = $this->db->fetch(
                "SELECT s.first_name, s.last_name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
                [$reservation['student_id']]
            );
            if ($studentInfo) {
                $roomLabel = $room ? trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? '')) : 'the room';
                $studentName = trim((($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? '')));
                $notified = $this->notifyReservationApproved((string)$studentInfo['email'], $studentName, $roomLabel, (string)$reservation['reservation_code']);
            }
        }

        $this->logActivity('approve_reservation', "Reservation {$reservation['reservation_code']} approved");
        $this->flash('success', 'Reservation approved.' . ($notified ? ' Email sent to the student: your reservation is approved!' : ' We could not send the email notification.'));
        $this->redirect('/admin/reservations');
    }

    public function reservationReject(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/reservations');
            return;
        }
        $id = (int)$this->input('id');
        $reservation = $this->db->fetch("SELECT * FROM reservations WHERE id = ? AND status = 'pending'", [$id]);
        if (!$reservation) {
            $this->flash('error', 'Reservation not found or already processed.');
            $this->redirect('/admin/reservations');
            return;
        }

        $reason = $this->sanitize($this->input('rejection_reason', ''));

        $this->db->update('reservations', [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => serverDateTime(),
        ], "id = ?", [$id]);

        $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$reservation['student_id']]);
        if ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Reservation Rejected',
                'message' => "Your reservation has been rejected." . ($reason ? " Reason: {$reason}" : ''),
                'type' => 'reservation',
                'reference_id' => $id,
                'reference_type' => 'reservation',
            ]);
        }

        $this->logActivity('reject_reservation', "Reservation {$reservation['reservation_code']} rejected");
        $this->flash('success', 'Reservation rejected.');
        $this->redirect('/admin/reservations');
    }

    public function reservationDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/reservations');
            return;
        }
        $id = (int)$this->input('id');
        $reservation = $this->db->fetch("SELECT * FROM reservations WHERE id = ?", [$id]);
        if (!$reservation) {
            $this->flash('error', 'Reservation not found.');
            $this->redirect('/admin/reservations');
            return;
        }

        if ($reservation['status'] === 'approved') {
            $this->updateRoomOccupancy($reservation['room_id']);
            
            $paidPayments = $this->db->fetchAll(
                "SELECT * FROM payments WHERE reservation_id = ? AND status = 'paid' AND payment_type IN ('reservation_fee', 'advance_payment', 'monthly_rent', 'full_payment')",
                [$id]
            );
            
            foreach ($paidPayments as $payment) {
                $this->db->insert('student_reservation_credits', [
                    'student_id' => $reservation['student_id'],
                    'original_reservation_id' => $id,
                    'original_reservation_code' => $reservation['reservation_code'],
                    'payment_type' => $payment['payment_type'],
                    'amount' => (float)$payment['amount'],
                    'late_fee' => (float)($payment['late_fee'] ?? 0),
                    'total_amount' => (float)$payment['amount'] + (float)($payment['late_fee'] ?? 0),
                    'notes' => 'Credit from deleted reservation ' . $reservation['reservation_code'] . ' (payment ' . $payment['payment_code'] . ')',
                ]);
            }
            
            if (!empty($paidPayments)) {
                $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$reservation['student_id']]);
                if ($student) {
                    $totalCredited = array_sum(array_map(fn($p) => (float)$p['amount'] + (float)($p['late_fee'] ?? 0), $paidPayments));
                    $this->db->insert('notifications', [
                        'user_id' => $student['user_id'],
                        'title' => 'Reservation Deleted - Payment Credits Saved',
                        'message' => "Your reservation {$reservation['reservation_code']} has been deleted by the administrator. Your paid amount of " . formatCurrency($totalCredited) . " has been saved as credit and will be applied to your next approved reservation.",
                        'type' => 'reservation',
                        'reference_id' => $id,
                        'reference_type' => 'reservation',
                    ]);
                }
            }
            
            $pendingPayments = $this->db->fetchAll(
                "SELECT * FROM payments WHERE reservation_id = ? AND status IN ('pending', 'upcoming', 'due_today', 'partially_paid', 'overdue')",
                [$id]
            );
            
            if (!empty($pendingPayments)) {
                foreach ($pendingPayments as $payment) {
                    $this->db->delete('payment_history', "payment_id = ?", [$payment['id']]);
                }
                $this->db->delete('payments', "reservation_id = ? AND status IN ('pending', 'upcoming', 'due_today', 'partially_paid', 'overdue')", [$id]);
            }
        }

        $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$reservation['student_id']]);
        if ($student && $reservation['status'] !== 'approved') {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Reservation Deleted',
                'message' => "Your reservation {$reservation['reservation_code']} has been deleted by the administrator.",
                'type' => 'reservation',
                'reference_id' => $id,
                'reference_type' => 'reservation',
            ]);
        }

        $archived = $this->archiveRecord('reservation', $id, $reservation);
        if (!$archived) {
            $this->flash('error', 'Could not archive this reservation. Deletion cancelled.');
            $this->redirect('/admin/reservations');
            return;
        }
        $this->db->delete('reservations', "id = ?", [$id]);
        $this->logActivity('delete_reservation', "Reservation {$reservation['reservation_code']} deleted");
        $notified = $this->notifyRecordDeleted('reservation', 'Reservation ' . $reservation['reservation_code']);
        $this->flash('success', $this->recordDeletedFlash('Reservation ' . $reservation['reservation_code'], $notified));
        $this->redirect('/admin/reservations');
    }

    public function students(): void {
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $result = $this->getStudents($search, $status);
        $this->view('admin.students', [
            'pageTitle' => 'Manage Students',
            'students' => $result['students'],
            'counts' => $result['counts'],
            'search' => $search,
            'status' => $status,
            'baseUrl' => '/admin',
            'flashMessages' => $this->getFlashMessages(),
        ], 'admin');
    }

    public function studentDetail(): void {
        $id = (int)$this->input('id');
        $student = $this->getStudentById($id);
        if (!$student) {
            $this->flash('error', 'Student not found.');
            $this->redirect('/admin/students');
            return;
        }
        if ($this->isPost()) {
            $this->handleStudentStatusUpdate($student, '/admin');
            return;
        }
        $detail = $this->getStudentDetailData($id);
        $this->view('admin.student_detail', array_merge([
            'pageTitle' => 'Student Details',
            'student' => $student,
            'baseUrl' => '/admin',
            'flashMessages' => $this->getFlashMessages(),
        ], $detail), 'admin');
    }

    public function tenants(): void {
        $search = $this->input('search', '');
        $selectedTenantId = (int)$this->input('tenant_id', 0);
        $result = $this->getTenants($search);
        
        $selectedTenant = null;
        $tenantPayments = [];
        $finalDueDate = null;
        $nextDueDate = null;
        $moveOutMonth = null;
        $monthsFromAdvance = 0;
        $monthsFromMonthly = 0;
        $totalMonthsPaid = 0;
        
        if ($selectedTenantId > 0) {
            $selectedTenant = $this->db->fetch(
                "SELECT s.*, u.email, u.status as user_status,
                        rm.room_number, rm.room_name, rm.monthly_rent, rm.advance_payment,
                        r.id as reservation_id, r.reservation_code, r.move_in_date, r.approved_at
                 FROM reservations r
                 JOIN students s ON r.student_id = s.id
                 JOIN users u ON s.user_id = u.id
                 LEFT JOIN rooms rm ON r.room_id = rm.id
                 WHERE r.id = ? AND r.status = 'approved'",
                [$selectedTenantId]
            );
            
            if ($selectedTenant) {
                $tenantPayments = $this->db->fetchAll(
                    "SELECT p.*, r.reservation_code, r.expected_duration, r.move_in_date,
                            COALESCE(rm.room_name, (SELECT arn.room_name FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_name,
                            COALESCE(rm.room_number, (SELECT arn.room_number FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = p.student_id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_number
                     FROM payments p
                     LEFT JOIN reservations r ON p.reservation_id = r.id
                     LEFT JOIN rooms rm ON r.room_id = rm.id
                     WHERE p.student_id = ?
                     ORDER BY p.created_at DESC",
                    [$selectedTenant['id']]
                );
                
                $advancePaid = 0;
                $monthlyPaid = 0;
                
                foreach ($tenantPayments as $p) {
                    $amountPaid = (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
                    if ($p['payment_type'] === 'advance_payment' && in_array($p['status'], ['paid', 'partially_paid'])) {
                        $advancePaid += $amountPaid;
                    }
                    if ($p['payment_type'] === 'monthly_rent' && in_array($p['status'], ['paid', 'partially_paid'])) {
                        $monthlyPaid += $amountPaid;
                    }
                }
                
                $monthlyRent = (float)($selectedTenant['monthly_rent'] ?? 0);
                $advancePayment = (float)($selectedTenant['advance_payment'] ?? 0);
                $moveInDate = $selectedTenant['move_in_date'] ? new DateTime($selectedTenant['move_in_date']) : null;
                $expectedDuration = (int)($selectedTenant['expected_duration'] ?? 0);
                
                if ($moveInDate && $monthlyRent > 0) {
                    // Calculate months covered by payments
                    $monthsFromAdvance = $monthlyRent > 0 ? floor($advancePaid / $monthlyRent) : 0;
                    $monthsFromMonthly = $monthlyRent > 0 ? floor($monthlyPaid / $monthlyRent) : 0;
                    $totalMonthsPaid = $monthsFromAdvance + $monthsFromMonthly;
                    
                    // Calculate lease end date based on expected duration
                    $leaseEndDate = clone $moveInDate;
                    if ($expectedDuration > 0) {
                        $leaseEndDate->modify("+{$expectedDuration} months");
                    } else {
                        $leaseEndDate->modify('+12 months'); // Default 12 months if not set
                    }
                    
                    // NEXT DUE DATE - Based on last monthly rent within expected duration
                    // Start from move-in date, go to first day of next month
                    $nextDue = clone $moveInDate;
                    $nextDue->modify('first day of next month');
                    
                    // Add months paid (advance + monthly) to get the next due date
                    $nextDue->modify("+{$totalMonthsPaid} months");
                    
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    
                    // If calculated next due date is in the past, move to next month
                    if ($nextDue <= $today) {
                        $nextDue->modify('first day of next month');
                    }
                    
                    // Cap next due date at lease end date (last monthly rent before lease ends)
                    $lastMonthlyDue = clone $leaseEndDate;
                    $lastMonthlyDue->modify('first day of this month');
                    $lastMonthlyDue->modify('-1 month'); // Last monthly rent is due at start of last month
                    
                    if ($nextDue > $lastMonthlyDue) {
                        $nextDue = $lastMonthlyDue;
                    }
                    
                    $nextDueDate = $nextDue->format('Y-m-d');
                    
                    // FINAL DUE DATE (Lease End)
                    $finalDueDate = $leaseEndDate->format('Y-m-d');
                    $moveOutMonth = $leaseEndDate->format('F Y');
                }
            }
        }
        
        $this->view('admin.tenants', [
            'pageTitle' => 'Manage Tenants',
            'tenants' => $result['tenants'],
            'totalTenants' => $result['total'],
            'search' => $search,
            'selectedTenantId' => $selectedTenantId,
            'selectedTenant' => $selectedTenant,
            'tenantPayments' => $tenantPayments,
            'finalDueDate' => $finalDueDate,
            'nextDueDate' => $nextDueDate,
            'moveOutMonth' => $moveOutMonth,
            'monthsFromAdvance' => $monthsFromAdvance,
            'monthsFromMonthly' => $monthsFromMonthly,
            'totalMonthsPaid' => $totalMonthsPaid,
            'baseUrl' => '/admin',
            'flashMessages' => $this->getFlashMessages(),
        ], 'admin');
    }

    public function removeTenant(): void {
        $this->processRemoveTenant('/admin');
    }

    public function studentDelete(): void {
        $this->processStudentDelete('/admin');
    }

    public function walkInRegister(): void {
        $this->processWalkInRegistration('/admin');
    }

    public function walkInPayment(): void {
        $this->processWalkInPaymentForStudent((int)$this->input('id', 0), '/admin');
    }

    public function walkInPayments(): void {
        $this->processWalkInPaymentsPage('/admin');
    }

    public function walkInPaymentDelete(): void {
        $this->processWalkInPaymentDelete('/admin');
    }

    public function walkInStudentPayment(): void {
        $this->processWalkInStudentPayment('/admin');
    }

public function payments(): void {
        $this->processMonthlyPayments();
        $validStatuses = ['pending', 'upcoming', 'due_today', 'partially_paid', 'paid', 'overdue', 'cancelled', 'refunded'];
        $validTypes = ['monthly_rent', 'advance_payment', 'reservation_fee', 'full_payment'];
        $status = $this->input('status', '');
        $type = $this->input('type', '');
        $isValidStatus = $status !== '' && in_array($status, $validStatuses, true);
        $isValidType = $type !== '' && in_array($type, $validTypes, true);
        $month = $this->input('month', '');
        $where = "1";
        $params = [];
        if ($isValidStatus) {
            if ($status === 'refunded') {
                $where .= " AND (p.status = 'refunded' OR COALESCE(p.refunded_amount, 0) > 0)";
            } else {
                $where .= " AND p.status = ?";
                $params[] = $status;
            }
        }
        if ($isValidType) {
            $where .= " AND p.payment_type = ?";
            $params[] = $type;
        }
        $isValidMonth = (bool)preg_match('/^\d{4}-\d{2}$/', $month);
        if ($isValidMonth) {
            $where .= " AND DATE_FORMAT(p.due_date, '%Y-%m') = ?";
            $params[] = $month;
        }

        $payments = $this->db->fetchAll(
            "SELECT p.*, s.first_name, s.last_name, s.student_id_number,
                    r.reservation_code, r.expected_duration, rm.room_name, rm.room_number, rm.monthly_rent,
                    (SELECT ar.room_name FROM reservations ar_res JOIN rooms ar ON ar_res.room_id = ar.id
                      WHERE ar_res.student_id = s.id AND ar_res.status = 'approved'
                      ORDER BY ar_res.created_at DESC LIMIT 1) AS alt_room_name,
                    (SELECT ar.room_number FROM reservations ar_res JOIN rooms ar ON ar_res.room_id = ar.id
                      WHERE ar_res.student_id = s.id AND ar_res.status = 'approved'
                      ORDER BY ar_res.created_at DESC LIMIT 1) AS alt_room_number
             FROM payments p
             JOIN students s ON p.student_id = s.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE {$where} ORDER BY p.created_at DESC",
            $params
        );

        // Remove duplicate Monthly Rent records (one per student per period).
        $payments = $this->dedupeMonthlyRentPayments($payments);

        $monthRows = $this->db->fetchAll(
            "SELECT DISTINCT DATE_FORMAT(p.due_date, '%Y-%m') as m FROM payments p WHERE p.due_date IS NOT NULL ORDER BY m DESC"
        );
        $monthOptions = [];
        if ($isValidMonth) {
            $monthOptions[$month] = true;
        }
        foreach ($monthRows as $row) {
            $monthOptions[$row['m']] = true;
        }
        krsort($monthOptions);

        $data = [
            'pageTitle' => 'Manage Payments',
            'payments' => $payments,
            'status' => $isValidStatus ? $status : '',
            'type' => $isValidType ? $type : '',
            'month' => $isValidMonth ? $month : '',
            'monthOptions' => $monthOptions,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.payments', $data, 'admin');
    }

    public function paymentVerify(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/payments');
            return;
        }
        $id = (int)$this->input('id');
        $payment = $this->db->fetch("SELECT * FROM payments WHERE id = ? AND status NOT IN ('paid','cancelled')", [$id]);
        if (!$payment) {
            $this->flash('error', 'Payment not found or already processed.');
            $this->redirect('/admin/payments');
            return;
        }

        $action = $this->input('action', 'paid');
        $newStatus = $action === 'paid' ? 'paid' : 'cancelled';

        $totalDue = (float)$payment['amount'] + (float)$payment['late_fee'];
        if ($newStatus === 'paid' && $payment['payment_type'] === 'monthly_rent') {
            $amountPaid = (float)$payment['amount_paid'];
            if ($amountPaid < $totalDue && $amountPaid > 0) {
                $newStatus = 'partially_paid';
            }
        }

        $this->db->update('payments', [
            'status' => $newStatus,
            'verified_by' => $_SESSION['user_id'],
            'verified_at' => serverDateTime(),
            'paid_at' => $newStatus === 'paid' ? serverDateTime() : null,
            'amount_paid' => $newStatus === 'paid' ? $totalDue : ($newStatus === 'partially_paid' ? $payment['amount_paid'] : $payment['amount_paid']),
        ], "id = ?", [$id]);

        $this->db->insert('payment_history', [
            'payment_id' => $id,
            'action' => 'verify',
            'old_status' => $payment['status'],
            'new_status' => $newStatus,
            'performed_by' => $_SESSION['user_id'],
        ]);

        $receiptTotal = $newStatus === 'paid' ? $totalDue : ($payment['amount_paid'] > 0 ? $payment['amount_paid'] : $payment['amount']);
        if ($newStatus === 'paid' || $newStatus === 'partially_paid') {
            $this->issueReceiptForPayment($id);
        }

        $student = $this->db->fetch("SELECT s.first_name, s.last_name, s.user_id, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$payment['student_id']]);
        $studentName = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Unknown';
        $houseName = $payment['payment_code'];
        if ($student && $newStatus !== 'cancelled') {
            $this->notifyPaymentEvent(
                (int)$student['user_id'],
                'Payment ' . ucfirst($newStatus),
                "Your payment {$payment['payment_code']} has been {$newStatus}. " . ($newStatus === 'paid' ? "Total: " . formatCurrency($receiptTotal) . "." : ($newStatus === 'partially_paid' ? "Remaining balance: " . formatCurrency($totalDue - (float)$payment['amount_paid']) . "." : "")),
                "Tenant {$studentName} payment {$payment['payment_code']} has been {$newStatus}.",
                "Payment {$payment['payment_code']} for {$studentName} has been {$newStatus}.",
                $id
            );
            if ($newStatus === 'paid') {
                $this->notifyPaymentApproved((string)$student['email'], $studentName, (string)$payment['payment_code'], (float)$receiptTotal);
            }
            // Notify staff (managers/admins) via email
            $this->notifyStaffPaymentVerified($payment, $studentName, $newStatus, (float)$receiptTotal);
        } elseif ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Payment Cancelled',
                'message' => "Your payment {$payment['payment_code']} has been cancelled.",
                'type' => 'payment',
                'reference_id' => $id,
                'reference_type' => 'payment',
            ]);
            // Notify staff (managers/admins) via email for cancellation
            $this->notifyStaffPaymentVerified($payment, $studentName, $newStatus, (float)$receiptTotal);
        }

        $this->logActivity('verify_payment', "Payment {$payment['payment_code']} {$newStatus}");
        $this->flash('success', $newStatus === 'paid' ? 'Payment approved! Email sent to the tenant: your payment is approved!' : 'Payment ' . $newStatus . '.');
        $this->redirect('/admin/payments');
    }

    public function paymentDelete(): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect('/admin/payments');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/payments');
            return;
        }
        $id = (int)$this->input('id');
        $payment = $this->db->fetch("SELECT * FROM payments WHERE id = ?", [$id]);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('/admin/payments');
            return;
        }

        $student = $this->db->fetch("SELECT user_id, first_name, last_name FROM students WHERE id = ?", [$payment['student_id']]);
        $studentName = $student ? $student['first_name'] . ' ' . $student['last_name'] : 'Unknown Student';
        $paymentCode = $payment['payment_code'];
        $paymentAmount = formatCurrency($payment['amount']);
        $paymentType = ucwords(str_replace('_', ' ', $payment['payment_type']));
        $adminName = $_SESSION['user_email'] ?? 'Admin';

        $this->db->delete('notifications', "reference_id = ? AND reference_type = 'payment'", [$id]);

        if ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Payment Deleted',
                'message' => "Your {$paymentType} payment ({$paymentCode}) of {$paymentAmount} has been deleted by the administrator.",
                'type' => 'payment',
                'reference_id' => null,
                'reference_type' => null,
            ]);
        }

        $managers = $this->db->fetchAll("SELECT u.id FROM users u WHERE u.role = 'manager' AND u.status = 'active'");
        foreach ($managers as $mgr) {
            $this->db->insert('notifications', [
                'user_id' => $mgr['id'],
                'title' => 'Payment Deleted by Admin',
                'message' => "Payment {$paymentCode} ({$paymentType}, {$paymentAmount}) for {$studentName} was deleted by {$adminName}.",
                'type' => 'payment',
                'reference_id' => null,
                'reference_type' => null,
            ]);
        }

        $archived = $this->archiveRecord('payment', $id, $payment, [
            'payment_history' => $this->db->fetchAll("SELECT * FROM payment_history WHERE payment_id = ?", [$id]),
            'receipts'        => $this->db->fetchAll("SELECT * FROM receipts WHERE payment_id = ?", [$id]),
        ]);
        if (!$archived) {
            $this->flash('error', 'Could not archive this payment. Deletion cancelled.');
            $this->redirect('/admin/payments');
            return;
        }
        $this->db->delete('payment_history', "payment_id = ?", [$id]);
        $this->db->delete('receipts', "payment_id = ?", [$id]);
        $this->db->delete('payments', "id = ?", [$id]);
        $this->logActivity('delete_payment', "Payment {$paymentCode} ({$paymentType}, {$paymentAmount}) deleted by admin â€” student: {$studentName}");
        $notified = $this->notifyRecordDeleted('payment', 'Payment ' . $paymentCode);
        $this->flash('success', $this->recordDeletedFlash('Payment ' . $paymentCode, $notified));
        $this->redirect('/admin/payments');
    }

    public function receipts(): void {
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $type = $this->input('type', '');
        $month = $this->input('month', '');
        $isValidMonth = (bool)preg_match('/^\d{4}-\d{2}$/', $month);

        $where = "1";
        $params = [];
        if ($search) {
            $where .= " AND (rc.receipt_number LIKE ? OR p.payment_code LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where .= " AND p.status = ?";
            $params[] = $status;
        }
        if ($type) {
            $where .= " AND p.payment_type = ?";
            $params[] = $type;
        }
        if ($isValidMonth) {
            $where .= " AND DATE_FORMAT(rc.issued_date, '%Y-%m') = ?";
            $params[] = $month;
        }

        $receipts = $this->db->fetchAll(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.status as payment_status,
                    p.payment_method, p.paid_at, p.late_fee, p.amount_paid, p.billing_period,
                    s.id as student_id, s.first_name, s.last_name, s.student_id_number,
                    COALESCE(rm.room_name, (SELECT arn.room_name FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = s.id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_name,
                    COALESCE(rm.room_number, (SELECT arn.room_number FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = s.id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_number
             FROM receipts rc
             JOIN payments p ON rc.payment_id = p.id
             JOIN students s ON p.student_id = s.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE {$where}
             ORDER BY rc.issued_date DESC",
            $params
        );

        $monthRows = $this->db->fetchAll(
            "SELECT DISTINCT DATE_FORMAT(rc.issued_date, '%Y-%m') as m FROM receipts rc WHERE rc.issued_date IS NOT NULL ORDER BY m DESC"
        );
        $monthOptions = [];
        if ($isValidMonth) {
            $monthOptions[$month] = true;
        }
        foreach ($monthRows as $row) {
            $monthOptions[$row['m']] = true;
        }
        krsort($monthOptions);

        $data = [
            'pageTitle' => 'Manage Receipts',
            'receipts' => $receipts,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'month' => $isValidMonth ? $month : '',
            'monthOptions' => $monthOptions,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.receipts', $data, 'admin');
    }

    public function receiptDelete(): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect('/admin/receipts');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/receipts');
            return;
        }
        $id = (int)$this->input('id');
        $receipt = $this->db->fetch(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, s.first_name, s.last_name
             FROM receipts rc
             JOIN payments p ON rc.payment_id = p.id
             JOIN students s ON p.student_id = s.id
             WHERE rc.id = ?",
            [$id]
        );
        if (!$receipt) {
            $this->flash('error', 'Receipt not found.');
            $this->redirect('/admin/receipts');
            return;
        }

        $archived = $this->archiveRecord('receipt', $id, $this->db->fetch("SELECT * FROM receipts WHERE id = ?", [$id]));
        if (!$archived) {
            $this->flash('error', 'Could not archive this receipt. Deletion cancelled.');
            $this->redirect('/admin/receipts');
            return;
        }
        $this->db->delete('receipts', "id = ?", [$id]);
        $this->logActivity('delete_receipt', "Receipt {$receipt['receipt_number']} ({$receipt['payment_code']}, " . formatCurrency($receipt['amount']) . ") deleted by admin â€” student: {$receipt['first_name']} {$receipt['last_name']}");
        $notified = $this->notifyRecordDeleted('receipt', 'Receipt ' . $receipt['receipt_number']);
        $this->flash('success', $this->recordDeletedFlash('Receipt ' . $receipt['receipt_number'], $notified));
        $this->redirect('/admin/receipts');
    }

    public function rentReceipt(): void {
        $period = (string)$this->input('period', '');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            $this->flash('error', 'Invalid billing period.');
            $this->redirect('/admin/receipts');
            return;
        }

        $studentId = (int)$this->input('student', 0);
        if ($studentId <= 0) {
            $this->flash('error', 'Please select a student to view the monthly rent receipt.');
            $this->redirect('/admin/receipts');
            return;
        }

        $student = $this->db->fetch(
            "SELECT s.*, u.email as student_email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$studentId]
        );
        if (!$student) {
            $this->flash('error', 'Student not found.');
            $this->redirect('/admin/receipts');
            return;
        }

        $payments = $this->db->fetchAll(
            "SELECT p.*, rc.id as receipt_id, rc.receipt_number, rc.issued_date as receipt_issued_date, rc.total as receipt_total,
                    rm.room_name, rm.room_number, r.move_in_date, r.expected_duration, r.rent_amount
             FROM payments p
             LEFT JOIN receipts rc ON rc.payment_id = p.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE p.student_id = ? AND p.payment_type = 'monthly_rent' AND p.billing_period = ?
             ORDER BY p.id ASC",
            [$studentId, $period]
        );

        if (empty($payments)) {
            $this->flash('error', 'No monthly rent found for that student and billing period.');
            $this->redirect('/admin/receipts');
            return;
        }

        $duration = (int)($payments[0]['expected_duration'] ?? 0);
        $monthlyRent = (float)($payments[0]['rent_amount'] ?? 0);
        if ($monthlyRent <= 0) {
            $monthlyRent = $duration > 0 ? round((float)($payments[0]['amount'] ?? 0) / $duration, 2) : (float)($payments[0]['amount'] ?? 0);
        }

        $rentDue = 0.0;
        $lateFee = 0.0;
        $amountPaid = 0.0;
        foreach ($payments as $payment) {
            $rentDue += (float)($payment['amount'] ?? 0);
            $lateFee += (float)($payment['late_fee'] ?? 0);
            $amountPaid += (float)($payment['amount_paid'] ?? 0);
        }
        $totalDue = $rentDue + $lateFee;
        $remaining = max(0, $totalDue - $amountPaid);

        $statuses = array_unique(array_map('strval', array_column($payments, 'status')));
        if (count($statuses) === 1) {
            $status = $statuses[0];
        } elseif (in_array('overdue', $statuses, true)) {
            $status = 'overdue';
        } else {
            $status = 'partially_paid';
        }

        $data = [
            'pageTitle' => 'Monthly Rent Receipt - ' . date('F Y', strtotime($period . '-01')),
            'student' => $student,
            'payments' => $payments,
            'period' => $period,
            'periodLabel' => date('F Y', strtotime($period . '-01')),
            'duration' => $duration,
            'monthlyRent' => $monthlyRent,
            'rentDue' => $rentDue,
            'lateFee' => $lateFee,
            'totalDue' => $totalDue,
            'amountPaid' => $amountPaid,
            'remaining' => $remaining,
            'status' => $status,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.receipt_rent', $data, 'admin');
    }

    public function receiptDetail(): void {
        $id = (int)$this->input('id');
        $receipt = $this->db->fetch(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.status as payment_status,
                    p.payment_method, p.paid_at, p.notes, p.late_fee, p.amount_paid,
                    p.billing_period, p.due_date, p.reference_number, p.reservation_id,
                    s.first_name, s.last_name, s.student_id_number, s.user_id,
                    s.phone, s.school_university, u.email as student_email,
                    COALESCE(rm.room_name, (SELECT arn.room_name FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = s.id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_name,
                    COALESCE(rm.room_number, (SELECT arn.room_number FROM reservations arr JOIN rooms arn ON arr.room_id = arn.id WHERE arr.student_id = s.id AND arr.status = 'approved' ORDER BY arr.created_at DESC LIMIT 1)) AS room_number,
                    r.move_in_date, r.expected_duration, r.rent_amount
             FROM receipts rc
             JOIN payments p ON rc.payment_id = p.id
             JOIN students s ON p.student_id = s.id
             JOIN users u ON s.user_id = u.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE rc.id = ?",
            [$id]
        );

        if (!$receipt) {
            $this->flash('error', 'Receipt not found.');
            $this->redirect('/admin/receipts');
            return;
        }

        $data = [
            'pageTitle' => 'Receipt Details',
            'receipt' => $receipt,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.receipt_detail', $data, 'admin');
    }

    public function announcements(): void {
        $announcements = $this->db->fetchAll(
            "SELECT a.*, u.email as creator_email FROM announcements a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC"
        );

        $data = [
            'pageTitle' => 'Manage Announcements',
            'announcements' => $announcements,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.announcements', $data, 'admin');
    }

    public function announcementCreate(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/announcement/create');
                return;
            }

            $title = $this->sanitize($this->input('title', ''));
            $content = $this->sanitize($this->input('content', ''));
            $type = $this->input('type', 'general');
            $priority = $this->input('priority', 'medium');
            $isPublished = $this->input('is_published') ? 1 : 0;

            if (empty($title) || empty($content)) {
                $this->flash('error', 'Title and content are required.');
                $this->redirect('/admin/announcement/create');
                return;
            }

            $validTypes = ['general', 'important', 'urgent', 'maintenance', 'event'];
            $validPriorities = ['low', 'medium', 'high', 'critical'];
            if (!in_array($type, $validTypes)) $type = 'general';
            if (!in_array($priority, $validPriorities)) $priority = 'medium';

            $this->db->insert('announcements', [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'priority' => $priority,
                'is_published' => $isPublished,
                'published_at' => $isPublished ? serverDateTime() : null,
                'created_by' => $_SESSION['user_id'],
            ]);

            if ($isPublished) {
                $students = $this->db->fetchAll("SELECT user_id FROM students");
                foreach ($students as $s) {
                    $this->db->insert('notifications', [
                        'user_id' => $s['user_id'],
                        'title' => 'New Announcement',
                        'message' => $title,
                        'type' => 'announcement',
                    ]);
                }

                $notified = $this->notifyAnnouncement([
                    'title' => $title,
                    'content' => $content,
                    'type' => $type,
                    'priority' => $priority,
                    'is_published' => 1,
                ]);
            }

            $this->logActivity('create_announcement', "Announcement: {$title}");
            $successMsg = 'Announcement created.';
            if (($notified ?? 0) > 0) {
                $successMsg .= ' Emailed to ' . $notified . ' recipient' . ($notified !== 1 ? 's' : '') . '.';
            }
            $this->flash('success', $successMsg);
            $this->redirect('/admin/announcements');
            return;
        }

        $data = [
            'pageTitle' => 'Create Announcement',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.announcement_form', $data, 'admin');
    }

    public function announcementEdit(): void {
        $id = (int)$this->input('id');
        $announcement = $this->db->fetch("SELECT * FROM announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            $this->flash('error', 'Announcement not found.');
            $this->redirect('/admin/announcements');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect("/admin/announcement/edit/{$id}");
                return;
            }

            $title = $this->sanitize($this->input('title', ''));
            $content = $this->sanitize($this->input('content', ''));
            $type = $this->input('type', 'general');
            $priority = $this->input('priority', 'medium');
            $isPublished = $this->input('is_published') ? 1 : 0;

            if (empty($title) || empty($content)) {
                $this->flash('error', 'Title and content are required.');
                $this->redirect("/admin/announcement/edit/{$id}");
                return;
            }

            $validTypes = ['general', 'important', 'urgent', 'maintenance', 'event'];
            $validPriorities = ['low', 'medium', 'high', 'critical'];
            if (!in_array($type, $validTypes)) $type = 'general';
            if (!in_array($priority, $validPriorities)) $priority = 'medium';

            $this->db->update('announcements', [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'priority' => $priority,
                'is_published' => $isPublished,
                'published_at' => $isPublished ? serverDateTime() : null,
            ], "id = ?", [$id]);

            $this->logActivity('update_announcement', "Announcement updated: {$title}");
            $this->flash('success', 'Announcement updated.');
            $this->redirect('/admin/announcements');
            return;
        }

        $data = [
            'pageTitle' => 'Edit Announcement',
            'announcement' => $announcement,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.announcement_form', $data, 'admin');
    }

    public function announcementDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/announcements');
            return;
        }
        $id = (int)$this->input('id');
        $announcement = $this->db->fetch("SELECT * FROM announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            $this->flash('error', 'Announcement not found.');
            $this->redirect('/admin/announcements');
            return;
        }
        $archived = $this->archiveRecord('announcement', $id, $announcement);
        if (!$archived) {
            $this->flash('error', 'Could not archive this announcement. Deletion cancelled.');
            $this->redirect('/admin/announcements');
            return;
        }
        $this->db->delete('announcements', "id = ?", [$id]);
        $this->logActivity('delete_announcement', "Announcement #{$id} deleted");
        $notified = $this->notifyRecordDeleted('announcement', 'Announcement "' . $announcement['title'] . '"');
        $this->flash('success', $this->recordDeletedFlash('Announcement "' . $announcement['title'] . '"', $notified));
        $this->redirect('/admin/announcements');
    }

    public function gallery(): void {
        $items = $this->db->fetchAll("SELECT * FROM gallery ORDER BY sort_order ASC");

        $data = [
            'pageTitle' => 'Manage Gallery',
            'gallery' => $items,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.gallery', $data, 'admin');
    }

    public function galleryCreate(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/gallery/create');
                return;
            }

            $title = $this->sanitize($this->input('title', ''));
            $description = $this->sanitize($this->input('description', ''));
            $category = $this->sanitize($this->input('category', 'general'));
            $validCategories = ['general', 'rooms', 'amenities', 'events', 'facilities'];
            if (!in_array($category, $validCategories)) $category = 'general';

            if (empty($title)) {
                $this->flash('error', 'Title is required.');
                $this->redirect('/admin/gallery/create');
                return;
            }

            if (!isset($_FILES['images']) || !is_array($_FILES['images']['name']) || empty($_FILES['images']['name'][0])) {
                $this->flash('error', 'Please select at least one image to upload.');
                $this->redirect('/admin/gallery/create');
                return;
            }

            $maxOrder = $this->db->fetch("SELECT MAX(sort_order) as max_order FROM gallery")['max_order'] ?? 0;
            $uploaded = 0;
            $skipped = 0;
            $count = count($_FILES['images']['name']);

            for ($i = 0; $i < $count; $i++) {
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i],
                ];

                if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
                    $skipped++;
                    continue;
                }

                $imagePath = $this->uploadFile($file, 'gallery', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], 5242880);

                if ($imagePath) {
                    $maxOrder++;
                    $this->db->insert('gallery', [
                        'title' => $title,
                        'description' => $description,
                        'image_path' => $imagePath,
                        'category' => $category,
                        'sort_order' => $maxOrder,
                    ]);
                    $uploaded++;
                } else {
                    $skipped++;
                }
            }

            $this->logActivity('create_gallery', "Gallery: {$uploaded} image(s) uploaded as '{$title}'");

            if ($uploaded > 0) {
                $notified = $this->notifyGalleryUpload($title, $uploaded);
            } else {
                $notified = 0;
            }

            if ($uploaded > 0 && $skipped > 0) {
                $this->flash('success', "{$uploaded} image(s) uploaded successfully. {$skipped} skipped (invalid or too large). Notified {$notified} student(s) by email.");
            } elseif ($uploaded > 0) {
                $this->flash('success', "{$uploaded} image(s) uploaded successfully. Notified {$notified} student(s) by email.");
            } else {
                $this->flash('error', 'No images could be uploaded. Please check file format and size (max 5MB).');
            }
            $this->redirect('/admin/gallery');
            return;
        }

        $data = [
            'pageTitle' => 'Add Gallery Image',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.gallery_form', $data, 'admin');
    }

    public function galleryDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/gallery');
            return;
        }
        $id = (int)$this->input('id');
        $item = $this->db->fetch("SELECT * FROM gallery WHERE id = ?", [$id]);
        if (!$item) {
            $this->flash('error', 'Gallery item not found.');
            $this->redirect('/admin/gallery');
            return;
        }
        $archived = $this->archiveRecord('gallery', $id, $item);
        if (!$archived) {
            $this->flash('error', 'Could not archive this gallery item. Deletion cancelled.');
            $this->redirect('/admin/gallery');
            return;
        }
        $this->db->delete('gallery', "id = ?", [$id]);
        $this->logActivity('delete_gallery', "Gallery item #{$id} deleted");
        $notified = $this->notifyRecordDeleted('gallery', 'Gallery image');
        $this->flash('success', $this->recordDeletedFlash('the gallery image', $notified));
        $this->redirect('/admin/gallery');
    }

    public function amenities(): void {
        $amenities = $this->db->fetchAll("SELECT * FROM amenities ORDER BY name ASC");

        $data = [
            'pageTitle' => 'Manage Amenities',
            'amenities' => $amenities,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.amenities', $data, 'admin');
    }

    public function amenityCreate(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/amenity/create');
                return;
            }

            $name = $this->sanitize($this->input('name', ''));
            $icon = $this->sanitize($this->input('icon', ''));
            $description = $this->sanitize($this->input('description', ''));
            $status = $this->input('status', 'active');

            if (empty($name)) {
                $this->flash('error', 'Name is required.');
                $this->redirect('/admin/amenity/create');
                return;
            }

            if (!in_array($status, ['active', 'inactive'])) $status = 'active';

            $this->db->insert('amenities', [
                'name' => $name,
                'icon' => $icon,
                'description' => $description,
                'status' => $status,
            ]);

            $this->logActivity('create_amenity', "Amenity: {$name}");
            $this->flash('success', 'Amenity created.');
            $this->redirect('/admin/amenities');
            return;
        }

        $data = [
            'pageTitle' => 'Create Amenity',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.amenity_form', $data, 'admin');
    }

    public function amenityEdit(): void {
        $id = (int)$this->input('id');
        $amenity = $this->db->fetch("SELECT * FROM amenities WHERE id = ?", [$id]);
        if (!$amenity) {
            $this->flash('error', 'Amenity not found.');
            $this->redirect('/admin/amenities');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect("/admin/amenity/edit/{$id}");
                return;
            }

            $name = $this->sanitize($this->input('name', ''));
            $icon = $this->sanitize($this->input('icon', ''));
            $description = $this->sanitize($this->input('description', ''));
            $status = $this->input('status', 'active');

            if (empty($name)) {
                $this->flash('error', 'Name is required.');
                $this->redirect("/admin/amenity/edit/{$id}");
                return;
            }

            if (!in_array($status, ['active', 'inactive'])) $status = 'active';

            $this->db->update('amenities', [
                'name' => $name,
                'icon' => $icon,
                'description' => $description,
                'status' => $status,
            ], "id = ?", [$id]);

            $this->logActivity('update_amenity', "Amenity updated: {$name}");
            $this->flash('success', 'Amenity updated.');
            $this->redirect('/admin/amenities');
            return;
        }

        $data = [
            'pageTitle' => 'Edit Amenity',
            'amenity' => $amenity,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.amenity_form', $data, 'admin');
    }

    public function amenityDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/amenities');
            return;
        }
        $id = (int)$this->input('id');
        $amenity = $this->db->fetch("SELECT * FROM amenities WHERE id = ?", [$id]);
        if (!$amenity) {
            $this->flash('error', 'Amenity not found.');
            $this->redirect('/admin/amenities');
            return;
        }
        $archived = $this->archiveRecord('amenity', $id, $amenity, [
            'room_amenities' => $this->db->fetchAll("SELECT * FROM room_amenities WHERE amenity_id = ?", [$id]),
        ]);
        if (!$archived) {
            $this->flash('error', 'Could not archive this amenity. Deletion cancelled.');
            $this->redirect('/admin/amenities');
            return;
        }
        $this->db->delete('room_amenities', "amenity_id = ?", [$id]);
        $this->db->delete('amenities', "id = ?", [$id]);
        $this->logActivity('delete_amenity', "Amenity #{$id} deleted");
        $notified = $this->notifyRecordDeleted('amenity', 'Amenity "' . $amenity['name'] . '"');
        $this->flash('success', $this->recordDeletedFlash('Amenity "' . $amenity['name'] . '"', $notified));
        $this->redirect('/admin/amenities');
    }

    public function maintenance(): void {
        $status = $this->input('status', '');
        $where = "1";
        $params = [];
        if ($status) {
            $where .= " AND mr.status = ?";
            $params[] = $status;
        }

        $requests = $this->db->fetchAll(
            "SELECT mr.*, s.first_name, s.last_name, rm.room_name, rm.room_number FROM maintenance_requests mr JOIN students s ON mr.student_id = s.id LEFT JOIN rooms rm ON mr.room_id = rm.id WHERE {$where} ORDER BY mr.created_at DESC",
            $params
        );

        $data = [
            'pageTitle' => 'Manage Maintenance',
            'requests' => $requests,
            'status' => $status,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.maintenance', $data, 'admin');
    }

    public function maintenanceDetail(): void {
        $id = (int)$this->input('id');
        $request = $this->db->fetch(
            "SELECT mr.*, s.first_name, s.last_name, s.phone as student_phone, rm.room_name, rm.room_number FROM maintenance_requests mr JOIN students s ON mr.student_id = s.id LEFT JOIN rooms rm ON mr.room_id = rm.id WHERE mr.id = ?",
            [$id]
        );

        if (!$request) {
            $this->flash('error', 'Request not found.');
            $this->redirect('/admin/maintenance');
            return;
        }

        $data = [
            'pageTitle' => 'Maintenance Detail',
            'request' => $request,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.maintenance_detail', $data, 'admin');
    }

    private function notifyMaintenanceUpdate(array $request, string $status, string $adminResponse, string $actorRole, string $actorEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $statusLabel = str_replace('_', ' ', $status);
        $statusLabels = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];
        $statusLabel = $statusLabels[$status] ?? ucfirst($statusLabel);

        $student = $this->db->fetch(
            "SELECT s.first_name, s.last_name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$request['student_id']]
        );
        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

        $roomLabel = '';
        if (!empty($request['room_id'])) {
            $room = $this->db->fetch("SELECT room_number, room_name FROM rooms WHERE id = ?", [$request['room_id']]);
            if ($room) $roomLabel = trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? ''));
        }

        // Notify the active actor (admin/manager) that the update was successful
        if (filter_var($actorEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Maintenance Request Updated Successfully';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#16a34a;margin:0 0 16px;">Maintenance Request Updated Successfully</h2>'
                . '<p>Hello,</p>'
                . '<p>You successfully updated maintenance request <strong>' . e((string)$request['request_code']) . '</strong>. The tenant has been notified by email.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Request Code</td><td style="padding:6px 8px;font-weight:600;">' . e((string)$request['request_code']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Title</td><td style="padding:6px 8px;">' . e((string)$request['title']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Tenant</td><td style="padding:6px 8px;">' . e($studentName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;"><strong>' . e($statusLabel) . '</strong></td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($actorEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyMaintenanceUpdate: failed to notify actor ' . $actorEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify the tenant that their maintenance request was updated
        $studentEmail = strtolower(trim((string)($student['email'] ?? '')));
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Your Maintenance Request Updated - ' . $request['request_code'];
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Maintenance Request Updated</h2>'
                . '<p>Hi ' . e($studentName) . ',</p>'
                . '<p>Good news! Your maintenance request <strong>' . e((string)$request['request_code']) . '</strong> has been updated by the ' . e($actorRole) . '.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Request Code</td><td style="padding:6px 8px;font-weight:600;">' . e((string)$request['request_code']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Title</td><td style="padding:6px 8px;">' . e((string)$request['title']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;">' . e($roomLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;"><strong>' . e($statusLabel) . '</strong></td></tr>'
                . ($adminResponse !== '' ? '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Response</td><td style="padding:6px 8px;">' . nl2br(e($adminResponse)) . '</td></tr>' : '')
                . '</table>'
                . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/student/maintenance" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View My Maintenance Requests</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyMaintenanceUpdate: failed to notify tenant ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }
    }

    public function maintenanceUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/maintenance');
            return;
        }
        $id = (int)$this->input('id');
        $request = $this->db->fetch("SELECT * FROM maintenance_requests WHERE id = ?", [$id]);
        if (!$request) {
            $this->flash('error', 'Request not found.');
            $this->redirect('/admin/maintenance');
            return;
        }

        $status = $this->input('status', $request['status']);
        $adminResponse = $this->sanitize($this->input('admin_response', ''));

        $validStatuses = ['pending', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) $status = $request['status'];

        $updateData = [
            'status' => $status,
            'admin_response' => $adminResponse,
        ];

        if ($status === 'resolved' && $request['status'] !== 'resolved') {
            $updateData['resolved_at'] = serverDateTime();
        }

        $this->db->update('maintenance_requests', $updateData, "id = ?", [$id]);

        $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$request['student_id']]);
        if ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Maintenance Update',
                'message' => "Your maintenance request {$request['request_code']} status: " . str_replace('_', ' ', $status) . ".",
                'type' => 'maintenance',
                'reference_id' => $id,
                'reference_type' => 'maintenance',
            ]);
        }

        $this->logActivity('update_maintenance', "Maintenance {$request['request_code']} updated to {$status}");
        $this->notifyMaintenanceUpdate($request, $status, $adminResponse, 'Admin', $_SESSION['user_email'] ?? '');
        $this->flash('success', 'Maintenance request updated.');
        $this->redirect('/admin/maintenance');
    }

    public function maintenanceDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/maintenance');
            return;
        }
        $id = (int)$this->input('id');
        $request = $this->db->fetch("SELECT * FROM maintenance_requests WHERE id = ?", [$id]);
        if (!$request) {
            $this->flash('error', 'Request not found.');
            $this->redirect('/admin/maintenance');
            return;
        }
        $archived = $this->archiveRecord('maintenance_request', $id, $request);
        if (!$archived) {
            $this->flash('error', 'Could not archive this maintenance request. Deletion cancelled.');
            $this->redirect('/admin/maintenance');
            return;
        }
        $this->db->delete('maintenance_requests', "id = ?", [$id]);
        $this->logActivity('delete_maintenance', "Maintenance {$request['request_code']} deleted");
        $notified = $this->notifyRecordDeleted('maintenance_request', 'Maintenance request ' . $request['request_code']);
        $this->flash('success', $this->recordDeletedFlash('Maintenance request ' . $request['request_code'], $notified));
        $this->redirect('/admin/maintenance');
    }

    public function complaints(): void {
        $status = $this->input('status', '');
        $where = "1";
        $params = [];
        if ($status) {
            $where .= " AND c.status = ?";
            $params[] = $status;
        }

        $complaints = $this->db->fetchAll(
            "SELECT c.*, s.first_name, s.last_name FROM complaints c JOIN students s ON c.student_id = s.id WHERE {$where} ORDER BY c.created_at DESC",
            $params
        );

        $data = [
            'pageTitle' => 'Manage Complaints',
            'complaints' => $complaints,
            'status' => $status,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.complaints', $data, 'admin');
    }

    public function complaintDetail(): void {
        $id = (int)$this->input('id');
        $complaint = $this->db->fetch(
            "SELECT c.*, s.first_name, s.last_name, s.phone as student_phone FROM complaints c JOIN students s ON c.student_id = s.id WHERE c.id = ?",
            [$id]
        );

        if (!$complaint) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('/admin/complaints');
            return;
        }

        $data = [
            'pageTitle' => 'Complaint Detail',
            'complaint' => $complaint,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.complaint_detail', $data, 'admin');
    }

    private function notifyComplaintUpdate(array $complaint, string $status, string $adminResponse, string $actorRole, string $actorEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $statusLabels = ['open' => 'Open', 'under_review' => 'Under Review', 'resolved' => 'Resolved', 'closed' => 'Closed'];
        $statusLabel = $statusLabels[$status] ?? str_replace('_', ' ', $status);

        $student = $this->db->fetch(
            "SELECT s.first_name, s.last_name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$complaint['student_id']]
        );
        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

        // Notify the active actor (admin/manager) that the update was successful
        if (filter_var($actorEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Complaint Updated Successfully';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#16a34a;margin:0 0 16px;">Complaint Updated Successfully</h2>'
                . '<p>Hello,</p>'
                . '<p>You successfully updated complaint <strong>' . e((string)$complaint['complaint_code']) . '</strong>. The tenant has been notified by email.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Complaint Code</td><td style="padding:6px 8px;font-weight:600;">' . e((string)$complaint['complaint_code']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Subject</td><td style="padding:6px 8px;">' . e((string)$complaint['subject']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Tenant</td><td style="padding:6px 8px;">' . e($studentName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;"><strong>' . e($statusLabel) . '</strong></td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($actorEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyComplaintUpdate: failed to notify actor ' . $actorEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify the tenant that their complaint was updated
        $studentEmail = strtolower(trim((string)($student['email'] ?? '')));
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Your Complaint Updated - ' . $complaint['complaint_code'];
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Complaint Updated</h2>'
                . '<p>Hi ' . e($studentName) . ',</p>'
                . '<p>Your complaint <strong>' . e((string)$complaint['complaint_code']) . '</strong> has been updated by the ' . e($actorRole) . '.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Complaint Code</td><td style="padding:6px 8px;font-weight:600;">' . e((string)$complaint['complaint_code']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Subject</td><td style="padding:6px 8px;">' . e((string)$complaint['subject']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Status</td><td style="padding:6px 8px;"><strong>' . e($statusLabel) . '</strong></td></tr>'
                . ($adminResponse !== '' ? '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Response</td><td style="padding:6px 8px;">' . nl2br(e($adminResponse)) . '</td></tr>' : '')
                . '</table>'
                . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/student/complaints" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View My Complaints</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyComplaintUpdate: failed to notify tenant ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }
    }

    public function complaintUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/complaints');
            return;
        }
        $id = (int)$this->input('id');
        $complaint = $this->db->fetch("SELECT * FROM complaints WHERE id = ?", [$id]);
        if (!$complaint) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('/admin/complaints');
            return;
        }

        $status = $this->input('status', $complaint['status']);
        $adminResponse = $this->sanitize($this->input('admin_response', ''));

        $validStatuses = ['open', 'under_review', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) $status = $complaint['status'];

        $updateData = [
            'status' => $status,
            'admin_response' => $adminResponse,
        ];

        if ($status === 'resolved' && $complaint['status'] !== 'resolved') {
            $updateData['resolved_at'] = serverDateTime();
        }

        $this->db->update('complaints', $updateData, "id = ?", [$id]);

        $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$complaint['student_id']]);
        if ($student) {
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Complaint Update',
                'message' => "Your complaint {$complaint['complaint_code']} status: " . str_replace('_', ' ', $status) . ".",
                'type' => 'complaint',
                'reference_id' => $id,
                'reference_type' => 'complaint',
            ]);
        }

        $this->logActivity('update_complaint', "Complaint {$complaint['complaint_code']} updated to {$status}");
        $this->notifyComplaintUpdate($complaint, $status, $adminResponse, 'Admin', $_SESSION['user_email'] ?? '');
        $this->flash('success', 'Complaint updated.');
        $this->redirect('/admin/complaints');
    }

    public function complaintDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/complaints');
            return;
        }
        $id = (int)$this->input('id');
        $complaint = $this->db->fetch("SELECT * FROM complaints WHERE id = ?", [$id]);
        if (!$complaint) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('/admin/complaints');
            return;
        }
        $archived = $this->archiveRecord('complaint', $id, $complaint);
        if (!$archived) {
            $this->flash('error', 'Could not archive this complaint. Deletion cancelled.');
            $this->redirect('/admin/complaints');
            return;
        }
        $this->db->delete('complaints', "id = ?", [$id]);
        $this->logActivity('delete_complaint', "Complaint {$complaint['complaint_code']} deleted");
        $notified = $this->notifyRecordDeleted('complaint', 'Complaint ' . $complaint['complaint_code']);
        $this->flash('success', $this->recordDeletedFlash('Complaint ' . $complaint['complaint_code'], $notified));
        $this->redirect('/admin/complaints');
    }

    public function refunds(): void {
        $status = $this->input('status', '');
        $where = "1";
        $params = [];
        if ($status) {
            $where .= " AND rf.status = ?";
            $params[] = $status;
        }

        $refunds = $this->db->fetchAll(
            "SELECT rf.*, s.first_name, s.last_name, s.student_id_number, r.reservation_code, rm.room_name, rm.room_number
             FROM refund_requests rf
             LEFT JOIN students s ON rf.student_id = s.id
             LEFT JOIN reservations r ON rf.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE {$where}
             ORDER BY CASE WHEN rf.status = 'pending' THEN 0 ELSE 1 END, rf.created_at DESC",
            $params
        );

        $data = [
            'pageTitle' => 'Refund Requests',
            'refunds' => $refunds,
            'status' => $status,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.refunds', $data, 'admin');
    }

    public function refundDetail(): void {
        $id = (int)$this->input('id');
        $refund = $this->db->fetch(
            "SELECT rf.*, s.first_name, s.last_name, s.student_id_number, u.email, s.phone as student_phone,
                    r.reservation_code, rm.room_name, rm.room_number
             FROM refund_requests rf
             LEFT JOIN students s ON rf.student_id = s.id
             LEFT JOIN users u ON s.user_id = u.id
             LEFT JOIN reservations r ON rf.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE rf.id = ?",
            [$id]
        );

        if (!$refund) {
            $this->flash('error', 'Refund request not found.');
            $this->redirect('/admin/refunds');
            return;
        }

        $refundType = $refund['refund_type'] ?? 'monthly';
        $refundPaymentTypes = $refundType === 'advance' ? ['advance_payment'] : ($refundType === 'all' ? ['monthly_rent', 'advance_payment'] : ['monthly_rent']);
        $refundPlaceholders = implode(',', array_fill(0, count($refundPaymentTypes), '?'));

        $deductedPayments = $this->db->fetchAll(
            "SELECT id, payment_code, payment_type, amount, amount_paid, status, notes
             FROM payments
             WHERE student_id = ? AND payment_type IN ({$refundPlaceholders}) AND status = 'refunded'
             ORDER BY updated_at DESC",
            array_merge([$refund['student_id']], $refundPaymentTypes)
        );

        $data = [
            'pageTitle' => 'Refund Request Detail',
            'refund' => $refund,
            'deductedPayments' => $deductedPayments,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.refund_detail', $data, 'admin');
    }

    public function refundUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/refunds');
            return;
        }
        $id = (int)$this->input('id');
        $refund = $this->db->fetch("SELECT * FROM refund_requests WHERE id = ?", [$id]);
        if (!$refund) {
            $this->flash('error', 'Refund request not found.');
            $this->redirect('/admin/refunds');
            return;
        }
        if ($refund['status'] !== 'pending') {
            $this->flash('error', 'This refund request has already been reviewed.');
            $this->redirect('/admin/refunds');
            return;
        }

        $status = $this->input('status', $refund['status']);
        $adminNotes = $this->sanitize($this->input('admin_notes', ''));

        $validStatuses = ['approved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            $this->flash('error', 'Invalid refund status.');
            $this->redirect('/admin/refunds');
            return;
        }

        $this->db->update('refund_requests', [
            'status' => $status,
            'admin_notes' => $adminNotes,
            'reviewed_by' => $_SESSION['user_id'] ?? null,
            'reviewed_at' => serverDateTime(),
        ], "id = ?", [$id]);

        $deductedInfo = null;
        $terminatedInfo = null;
        $cancelledBills = null;
        $deletedInfo = null;
        if ($status === 'approved') {
            $deductedInfo = $this->applyRefundDeduction($refund);
            if (($refund['refund_type'] ?? '') === 'all') {
                $terminatedInfo = $this->terminateTenantOnAllPaymentRefund($refund);
                $cancelledBills = $this->cancelUnpaidPaymentsAfterTermination((int)$refund['student_id'], (string)$refund['refund_code']);
                $deletedInfo = $this->deleteTenantRecordsAfterAllRefund((int)$refund['student_id']);
            }
        }

        $student = $this->db->fetch("SELECT s.user_id, s.first_name, s.last_name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$refund['student_id']]);
        if ($student) {
            $deductNote = '';
            if ($deductedInfo !== null && $deductedInfo['count'] > 0) {
                $deductNote = " " . $deductedInfo['count'] . " payment(s) totaling " . formatCurrency($deductedInfo['amount']) . " have been automatically deducted.";
            }
            $terminateNote = '';
            if ($terminatedInfo !== null && $terminatedInfo['reservations'] > 0) {
                $terminateNote = " Your reservation has been terminated and your slot in the room has been released. The room is now open for new reservations.";
                if ($cancelledBills !== null && $cancelledBills['count'] > 0) {
                    $terminateNote .= " " . $cancelledBills['count'] . " unpaid bill(s) totaling " . formatCurrency($cancelledBills['amount']) . " were automatically cancelled.";
                }
            }
            if ($deletedInfo !== null && ($deletedInfo['payments'] > 0 || $deletedInfo['reservations'] > 0)) {
                $terminateNote .= " Your payment, receipt, and reservation records have been cleared.";
            }
            $this->db->insert('notifications', [
                'user_id' => $student['user_id'],
                'title' => 'Refund Request ' . ucfirst($status),
                'message' => "Your refund request {$refund['refund_code']} has been " . $status . " for " . formatCurrency((float)$refund['amount']) . "." . $deductNote . $terminateNote . ($adminNotes !== '' ? " Note: {$adminNotes}" : ''),
                'type' => 'refund',
                'reference_id' => $id,
                'reference_type' => 'refund_request',
            ]);
            $this->notifyRefundDecision((string)$student['email'], trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')), (string)$refund['refund_code'], $status, (float)$refund['amount'], $adminNotes);
        }

        $logNote = '';
        if ($terminatedInfo !== null && $terminatedInfo['reservations'] > 0) {
            $logNote = "; terminated " . $terminatedInfo['reservations'] . " reservation(s) and updated occupancy for room(s) #" . implode(', #', $terminatedInfo['rooms']);
            if ($cancelledBills !== null && $cancelledBills['count'] > 0) {
                $logNote .= "; cancelled " . $cancelledBills['count'] . " unpaid bill(s) totaling " . formatCurrency($cancelledBills['amount']);
            }
        }
        if ($deletedInfo !== null) {
            $logNote .= "; cleared " . $deletedInfo['payments'] . " payment(s), " . $deletedInfo['receipts'] . " receipt(s), " . $deletedInfo['reservations'] . " reservation(s) (archived)";
        }
        $this->logActivity('update_refund_request', "Refund request {$refund['refund_code']} {$status}" . $logNote);
        $this->flash('success', 'Refund request ' . $status . '. Email notification sent to the tenant.');
        $this->redirect('/admin/refunds');
    }

    public function refundDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/refunds');
            return;
        }
        $id = (int)$this->input('id');
        $refund = $this->db->fetch("SELECT * FROM refund_requests WHERE id = ?", [$id]);
        if (!$refund) {
            $this->flash('error', 'Refund request not found.');
            $this->redirect('/admin/refunds');
            return;
        }
        $archived = $this->archiveRecord('refund_request', $id, $refund);
        if (!$archived) {
            $this->flash('error', 'Could not archive this refund request. Deletion cancelled.');
            $this->redirect('/admin/refunds');
            return;
        }
        $this->db->delete('refund_requests', "id = ?", [$id]);
        $this->logActivity('delete_refund_request', "Refund request {$refund['refund_code']} deleted");
        $notified = $this->notifyRecordDeleted('refund_request', 'Refund request ' . $refund['refund_code']);
        $this->flash('success', $this->recordDeletedFlash('Refund request ' . $refund['refund_code'], $notified));
        $this->redirect('/admin/refunds');
    }

    public function feedback(): void {
        $status = $this->input('status', '');
        $where = "1";
        $params = [];
        if ($status) {
            $where .= " AND f.status = ?";
            $params[] = $status;
        }

        $feedback = $this->db->fetchAll(
            "SELECT f.*, s.first_name, s.last_name FROM feedback f LEFT JOIN students s ON f.student_id = s.id WHERE {$where} ORDER BY f.created_at DESC",
            $params
        );

        $data = [
            'pageTitle' => 'Manage Feedback',
            'feedback' => $feedback,
            'status' => $status,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.feedback', $data, 'admin');
    }

    private function notifyFeedbackReply(array $fb, string $response, string $actorRole, string $actorEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $studentName = trim((string)($fb['name'] ?? ''));
        $studentEmail = strtolower(trim((string)($fb['email'] ?? '')));
        if ($studentName === '' && !empty($fb['student_id'])) {
            $student = $this->db->fetch(
                "SELECT s.first_name, s.last_name, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
                [$fb['student_id']]
            );
            if ($student) {
                $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
                $studentEmail = $studentEmail !== '' ? $studentEmail : strtolower(trim((string)($student['email'] ?? '')));
            }
        }

        // Notify the active actor (admin/manager) that the reply was sent
        if (filter_var($actorEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Feedback Reply Sent Successfully';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#16a34a;margin:0 0 16px;">Feedback Reply Sent Successfully</h2>'
                . '<p>Hello,</p>'
                . '<p>You successfully responded to the tenant\'s feedback. The tenant has been notified by email.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Feedback Subject</td><td style="padding:6px 8px;font-weight:600;">' . e((string)$fb['subject']) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Tenant</td><td style="padding:6px 8px;">' . e($studentName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Your Reply</td><td style="padding:6px 8px;">' . nl2br(e($response)) . '</td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($actorEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyFeedbackReply: failed to notify actor ' . $actorEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify the tenant that their feedback was responded to
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Your Feedback Has Been Responded To';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Feedback Response</h2>'
                . '<p>Hi ' . e($studentName !== '' ? $studentName : 'there') . ',</p>'
                . '<p>Good news! The management of Alondes Dorm has responded to your feedback.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;vertical-align:top;">Your Feedback</td><td style="padding:6px 8px;"><strong>' . e((string)$fb['subject']) . '</strong></td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Response</td><td style="padding:6px 8px;">' . nl2br(e($response)) . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/student/feedback" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View Feedback</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyFeedbackReply: failed to notify tenant ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }
    }

    public function feedbackReply(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/feedback');
            return;
        }
        $id = (int)$this->input('id');
        $fb = $this->db->fetch("SELECT * FROM feedback WHERE id = ?", [$id]);
        if (!$fb) {
            $this->flash('error', 'Feedback not found.');
            $this->redirect('/admin/feedback');
            return;
        }

        $response = $this->sanitize($this->input('admin_response', ''));
        if (empty($response)) {
            $this->flash('error', 'Response cannot be empty.');
            $this->redirect('/admin/feedback');
            return;
        }

        $this->db->update('feedback', [
            'status' => 'replied',
            'admin_response' => $response,
        ], "id = ?", [$id]);

        if ($fb['student_id']) {
            $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$fb['student_id']]);
            if ($student) {
                $this->db->insert('notifications', [
                    'user_id' => $student['user_id'],
                    'title' => 'Feedback Reply',
                    'message' => "Management has replied to your feedback: {$fb['subject']}",
                    'type' => 'system',
                ]);
            }
        }

        $this->logActivity('reply_feedback', "Replied to feedback #{$id}");
        $this->notifyFeedbackReply($fb, $response, 'Admin', $_SESSION['user_email'] ?? '');
        $this->flash('success', 'Response sent.');
        $this->redirect('/admin/feedback');
    }

    public function feedbackDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/feedback');
            return;
        }
        $id = (int)$this->input('id');
        $fb = $this->db->fetch("SELECT * FROM feedback WHERE id = ?", [$id]);
        if (!$fb) {
            $this->flash('error', 'Feedback not found.');
            $this->redirect('/admin/feedback');
            return;
        }

        if ($fb['student_id']) {
            $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$fb['student_id']]);
            if ($student) {
                $this->db->insert('notifications', [
                    'user_id' => $student['user_id'],
                    'title' => 'Feedback Deleted',
                    'message' => "Your feedback \"{$fb['subject']}\" has been deleted by the administrator.",
                    'type' => 'system',
                ]);
            }
        }

        $archived = $this->archiveRecord('feedback', $id, $fb);
        if (!$archived) {
            $this->flash('error', 'Could not archive this feedback. Deletion cancelled.');
            $this->redirect('/admin/feedback');
            return;
        }
        $this->db->delete('feedback', "id = ?", [$id]);
        $this->logActivity('delete_feedback', "Feedback \"{$fb['subject']}\" deleted");
        $notified = $this->notifyRecordDeleted('feedback', 'Feedback "' . $fb['subject'] . '"');
        $this->flash('success', $this->recordDeletedFlash('Feedback "' . $fb['subject'] . '"', $notified));
        $this->redirect('/admin/feedback');
    }

    public function contactMessages(): void {
        $status = $this->input('status', '');
        $subject = $this->input('subject', '');
        $where = "1";
        $params = [];
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        if ($subject) {
            $where .= " AND subject = ?";
            $params[] = $subject;
        }

        $messages = $this->db->fetchAll(
            "SELECT * FROM contact_messages WHERE {$where} ORDER BY created_at DESC",
            $params
        );

        $newCount = $this->db->fetch("SELECT COUNT(*) as c FROM contact_messages WHERE status = 'new'")['c'];

        $data = [
            'pageTitle' => 'Contact Messages',
            'messages' => $messages,
            'status' => $status,
            'subject' => $subject,
            'newCount' => $newCount,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.contact_messages', $data, 'admin');
    }

    public function contactMessageDetail(): void {
        $id = (int)$this->input('id');
        $message = $this->db->fetch("SELECT * FROM contact_messages WHERE id = ?", [$id]);

        if (!$message) {
            $this->flash('error', 'Message not found.');
            $this->redirect('/admin/contact-messages');
            return;
        }

        if ($message['status'] === 'new') {
            $this->db->update('contact_messages', ['status' => 'read'], "id = ?", [$id]);
        }

        $data = [
            'pageTitle' => 'Contact Message',
            'message' => $message,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.contact_message_detail', $data, 'admin');
    }

    public function contactMessageUpdate(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/contact-messages');
            return;
        }

        $id = (int)$this->input('id');
        $message = $this->db->fetch("SELECT * FROM contact_messages WHERE id = ?", [$id]);
        if (!$message) {
            $this->flash('error', 'Message not found.');
            $this->redirect('/admin/contact-messages');
            return;
        }

        $status = $this->input('status', $message['status']);
        $adminResponse = $this->sanitize($this->input('admin_response', ''));

        $validStatuses = ['new', 'read', 'replied', 'archived'];
        if (!in_array($status, $validStatuses)) $status = $message['status'];

        $updateData = ['status' => $status];
        if (!empty($adminResponse)) {
            $updateData['admin_response'] = $adminResponse;
            $updateData['status'] = 'replied';
        }

        $this->db->update('contact_messages', $updateData, "id = ?", [$id]);
        $this->logActivity('update_contact_message', "Contact message #{$id} updated to {$status}");

        $updatedMessage = $this->db->fetch("SELECT * FROM contact_messages WHERE id = ?", [$id]);
        $notified = $this->notifyContactMessageResponse($updatedMessage ?: $message);
        $this->flash('success', 'Message updated successfully.' . ($notified ? ' Email notification sent to ' . $message['email'] . '.' : ' We could not send the email notification right now.'));
        $this->redirect('/admin/contact-messages');
    }

    public function contactMessageDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/contact-messages');
            return;
        }

        $id = (int)$this->input('id');
        $message = $this->db->fetch("SELECT * FROM contact_messages WHERE id = ?", [$id]);
        if (!$message) {
            $this->flash('error', 'Message not found.');
            $this->redirect('/admin/contact-messages');
            return;
        }

        $archived = $this->archiveRecord('contact_message', $id, $message);
        if (!$archived) {
            $this->flash('error', 'Could not archive this message. Deletion cancelled.');
            $this->redirect('/admin/contact-messages');
            return;
        }
        $this->db->delete('contact_messages', "id = ?", [$id]);
        $this->logActivity('delete_contact_message', "Contact message from {$message['name']} deleted");
        $notified = $this->notifyRecordDeleted('contact_message', 'Message from ' . $message['name']);
        $this->flash('success', $this->recordDeletedFlash('Message from ' . $message['name'], $notified));
        $this->redirect('/admin/contact-messages');
    }

    public function reports(): void {
        $dateFrom = $this->input('date_from', serverNow()->format('Y-m-01'));
        $dateTo = $this->input('date_to', serverNow()->format('Y-m-t'));
        $t1 = $dateFrom . ' 00:00:00';
        $t2 = $dateTo . ' 23:59:59';

        $from = new DateTime($dateFrom);
        $to = new DateTime($dateTo);
        $span = $to->diff($from)->days + 1;
        $prevFrom = (clone $from)->modify("-{$span} days")->format('Y-m-d') . ' 00:00:00';
        $prevTo = (clone $from)->modify("-1 days")->format('Y-m-d') . ' 23:59:59';

        $totalRooms = $this->db->count('rooms');
        $occupiedRooms = $this->db->count('rooms', "status = 'occupied'");
        $availableRooms = $this->db->count('rooms', "status = 'available'");
        $reservedRooms = $this->db->count('rooms', "status = 'reserved'");
        $occupancyRate = $occupiedRooms / max(1, $totalRooms) * 100;

        $totalRevenue = (float)$this->db->fetch(
            "SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) BETWEEN ? AND ?",
            [$t1, $t2]
        )['total'];
        $previousRevenue = (float)$this->db->fetch(
            "SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) BETWEEN ? AND ?",
            [$prevFrom, $prevTo]
        )['total'];
        if ($previousRevenue > 0) {
            $revenueTrendPct = round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100);
        } elseif ($totalRevenue > 0) {
            $revenueTrendPct = 100;
        } else {
            $revenueTrendPct = 0;
        }

        $totalPayments = $this->db->count('payments', "status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) BETWEEN ? AND ?", [$t1, $t2]);
        $totalReservations = $this->db->count('reservations', "created_at BETWEEN ? AND ?", [$t1, $t2]);
        $newStudents = $this->db->count('students', "created_at BETWEEN ? AND ?", [$t1, $t2]);
        $pendingReservations = $this->db->count('reservations', "status = 'pending'");
        $pendingPayments = $this->db->count('payments', "status = 'pending'");

        $data = [
            'pageTitle' => 'Reports',
            'totalRooms' => $totalRooms,
            'occupiedRooms' => $occupiedRooms,
            'availableRooms' => $availableRooms,
            'reservedRooms' => $reservedRooms,
            'totalRevenue' => $totalRevenue,
            'previousRevenue' => $previousRevenue,
            'revenueTrendPct' => $revenueTrendPct,
            'totalPayments' => $totalPayments,
            'avgPayment' => $totalPayments > 0 ? $totalRevenue / $totalPayments : 0,
            'occupancyRate' => $occupancyRate,
            'totalReservations' => $totalReservations,
            'newStudents' => $newStudents,
            'pendingReservations' => $pendingReservations,
            'pendingPayments' => $pendingPayments,
            'roomStatus' => $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM rooms GROUP BY status"
            ),
            'reservationStatus' => $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM reservations GROUP BY status"
            ),
            'paymentByType' => $this->db->fetchAll(
                "SELECT payment_type, SUM(amount_paid - COALESCE(refunded_amount, 0)) as total, COUNT(*) as count FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) BETWEEN ? AND ? GROUP BY payment_type",
                [$t1, $t2]
            ),
            'paymentMethod' => $this->db->fetchAll(
                "SELECT payment_method, COUNT(*) as count, SUM(amount_paid - COALESCE(refunded_amount, 0)) as total FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) BETWEEN ? AND ? GROUP BY payment_method",
                [$t1, $t2]
            ),
            'monthlyPayments' => $this->db->fetchAll(
                "SELECT DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as month, SUM(amount_paid - COALESCE(refunded_amount, 0)) as total, COUNT(*) as count FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) BETWEEN ? AND ? GROUP BY DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') ORDER BY month",
                [$t1, $t2]
            ),
            'monthlyReservations' => $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total FROM reservations WHERE created_at BETWEEN ? AND ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month",
                [$t1, $t2]
            ),
            'topStudents' => $this->db->fetchAll(
                "SELECT s.first_name, s.middle_name, s.last_name, s.suffix, COUNT(p.id) as payments, SUM(p.amount_paid - COALESCE(p.refunded_amount, 0)) as total FROM payments p JOIN students s ON p.student_id = s.id WHERE p.status IN ('paid','partially_paid','refunded') AND p.amount_paid > 0 AND COALESCE(p.paid_at, p.created_at) BETWEEN ? AND ? GROUP BY s.id ORDER BY total DESC LIMIT 6",
                [$t1, $t2]
            ),
            'recentPayments' => $this->db->fetchAll(
                "SELECT p.*, s.first_name, s.middle_name, s.last_name, s.suffix FROM payments p JOIN students s ON p.student_id = s.id WHERE COALESCE(p.paid_at, p.created_at) BETWEEN ? AND ? ORDER BY COALESCE(p.paid_at, p.created_at) DESC LIMIT 10",
                [$t1, $t2]
            ),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.reports', $data, 'admin');
    }

    public function analytics(): void {
        $totalRevenue12 = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount, 0)), 0) as t FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
        )['t'] ?? 0);
        $totalReservations12 = $this->db->count('reservations', "created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)");
        $totalStudents12 = $this->db->count('students', "created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)");

        $totalRooms = $this->db->count('rooms');
        $occupiedRooms = $this->db->count('rooms', "status = 'occupied'");
        $occupancyRate = $totalRooms > 0 ? round($occupiedRooms / $totalRooms * 100) : 0;

        $walkinTotal12 = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(p.amount_paid), 0) AS t FROM payment_history ph JOIN payments p ON ph.payment_id = p.id WHERE ph.action = 'walk_in_payment' AND ph.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
        )['t'] ?? 0);
        $walkinThisMonth = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(p.amount_paid), 0) AS t FROM payment_history ph JOIN payments p ON ph.payment_id = p.id WHERE ph.action = 'walk_in_payment' AND ph.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        )['t'] ?? 0);
        $walkinCount12 = (int)($this->db->fetch(
            "SELECT COUNT(*) AS c FROM payment_history ph WHERE ph.action = 'walk_in_payment' AND ph.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
        )['c'] ?? 0);

        $data = [
            'pageTitle' => 'Analytics',
            'last12Months' => $totalRevenue12 > 0 && $totalReservations12 > 0,
            'monthlyRevenue' => $this->db->fetchAll(
                "SELECT DATE_FORMAT(COALESCE(paid_at, created_at), '%Y-%m') as m, SUM(amount_paid - COALESCE(refunded_amount, 0)) as t FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY m ORDER BY m"
            ),
            'monthlyReservations' => $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as t FROM reservations WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY m ORDER BY m"
            ),
            'monthlyStudents' => $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as t FROM students WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY m ORDER BY m"
            ),
            'weekdayPayments' => $this->db->fetchAll(
                "SELECT DAYOFWEEK(COALESCE(paid_at, created_at)) as wd, COUNT(*) as cnt, SUM(amount_paid - COALESCE(refunded_amount, 0)) as t FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY wd ORDER BY wd"
            ),
            'topRooms' => $this->db->fetchAll(
                "SELECT rm.room_number, rm.room_name, COUNT(p.id) as cnt, SUM(p.amount_paid - COALESCE(p.refunded_amount, 0)) as t FROM payments p LEFT JOIN reservations r ON p.reservation_id = r.id LEFT JOIN rooms rm ON r.room_id = rm.id WHERE p.status IN ('paid','partially_paid','refunded') AND p.amount_paid > 0 AND COALESCE(p.paid_at, p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY rm.id ORDER BY t DESC LIMIT 6"
            ),
            'paymentMethod' => $this->db->fetchAll(
                "SELECT payment_method, COUNT(*) as cnt, SUM(amount_paid - COALESCE(refunded_amount, 0)) as t FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0 AND COALESCE(paid_at, created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY payment_method"
            ),
            'reservationStatus' => $this->db->fetchAll(
                "SELECT status, COUNT(*) as c FROM reservations GROUP BY status"
            ),
            'roomStatus' => $this->db->fetchAll(
                "SELECT status, COUNT(*) as cnt FROM rooms GROUP BY status"
            ),
            'walkinMonthly' => $this->db->fetchAll(
                "SELECT DATE_FORMAT(ph.created_at, '%Y-%m') AS m, SUM(p.amount_paid) AS t FROM payment_history ph JOIN payments p ON ph.payment_id = p.id WHERE ph.action = 'walk_in_payment' AND ph.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY m ORDER BY m"
            ),
            'walkinByMethod' => $this->db->fetchAll(
                "SELECT p.payment_method, SUM(p.amount_paid) AS t FROM payment_history ph JOIN payments p ON ph.payment_id = p.id WHERE ph.action = 'walk_in_payment' AND ph.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY p.payment_method"
            ),
            'walkinRecent' => $this->db->fetchAll(
                "SELECT p.payment_code, p.payment_type, p.amount_paid, p.payment_method, ph.created_at, s.first_name, s.middle_name, s.last_name, s.suffix FROM payment_history ph JOIN payments p ON ph.payment_id = p.id JOIN students s ON p.student_id = s.id WHERE ph.action = 'walk_in_payment' ORDER BY ph.id DESC LIMIT 5"
            ),
            'totalRevenue12' => $totalRevenue12,
            'totalReservations12' => $totalReservations12,
            'totalStudents12' => $totalStudents12,
            'totalRooms' => $totalRooms,
            'occupiedRooms' => $occupiedRooms,
            'occupancyRate' => $occupancyRate,
            'walkinTotal12' => $walkinTotal12,
            'walkinThisMonth' => $walkinThisMonth,
            'walkinCount12' => $walkinCount12,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.analytics', $data, 'admin');
    }

    public function profile(): void {
        $admin = $this->getAdmin();

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/profile');
                return;
            }

            $email = strtolower(trim($this->input('email', '')));
            if (!isValidGmailEmail($email)) {
                $this->flash('error', gmailEmailError('Email address', $email));
                $this->redirect('/admin/profile');
                return;
            }

            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']]);
            if ($existing) {
                $this->flash('error', 'Email already in use.');
                $this->redirect('/admin/profile');
                return;
            }

            $this->db->update('users', ['email' => $email], "id = ?", [$_SESSION['user_id']]);
            $_SESSION['user_email'] = $email;
            $this->logActivity('update_profile', 'Admin profile updated');
            $firstName = $this->db->fetch("SELECT first_name FROM managers WHERE user_id = ?", [$_SESSION['user_id']])['first_name'] ?? '';
            $notified = $this->notifyProfileUpdate($email, (string)$firstName);
            $this->flash('success', 'Profile updated.' . ($notified ? ' Email notification sent to ' . $email . '.' : ' We could not send the email notification right now.'));
            $this->redirect('/admin/profile');
            return;
        }

        $data = [
            'pageTitle' => 'My Profile',
            'admin' => $admin,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.profile', $data, 'admin');
    }

    public function settings(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/settings');
                return;
            }

            $action = $this->input('action', '');

            if ($action === 'change_password') {
                $currentPassword = $this->input('current_password', '');
                $newPassword = $this->input('new_password', '');
                $confirmPassword = $this->input('password_confirmation', '');

                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $this->flash('error', 'All fields are required.');
                    $this->redirect('/admin/settings');
                    return;
                }

                $pwErrors = [];
                if (strlen($newPassword) < 8) $pwErrors[] = 'at least 8 characters';
                if (!preg_match('/[A-Z]/', $newPassword)) $pwErrors[] = 'one uppercase letter';
                if (!preg_match('/[a-z]/', $newPassword)) $pwErrors[] = 'one lowercase letter';
                if (!preg_match('/[0-9]/', $newPassword)) $pwErrors[] = 'one number';
                if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) $pwErrors[] = 'one special character';
                if (!empty($pwErrors)) {
                    $this->flash('error', 'New password must contain ' . implode(', ', $pwErrors) . '.');
                    $this->redirect('/admin/settings');
                    return;
                }

                if ($newPassword !== $confirmPassword) {
                    $this->flash('error', 'Passwords do not match.');
                    $this->redirect('/admin/settings');
                    return;
                }

                $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
                if (!$user || !password_verify($currentPassword, $user['password'])) {
                    $this->flash('error', 'Current password is incorrect.');
                    $this->redirect('/admin/settings');
                    return;
                }

                if ($this->isPasswordReused((int)$_SESSION['user_id'], $newPassword)) {
                    $this->flash('error', 'You cannot reuse a previous password. Please choose a new one.');
                    $this->redirect('/admin/settings');
                    return;
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $pending = [
                    'user_id'       => (int)$_SESSION['user_id'],
                    'email'         => (string)$_SESSION['user_email'],
                    'password_hash' => $hashedPassword,
                    'mode'          => 'settings',
                    'context'       => 'admin',
                ];
                $_SESSION['pending_pw_change'] = $pending;

                if ((new AuthController())->sendPasswordChangeCode((int)$_SESSION['user_id'], (string)$_SESSION['user_email'])) {
                    $this->flash('info', 'Enter the 6-digit code we emailed you to confirm the password change.');
                } else {
                    $this->flash('warning', 'We could not send a verification code right now. Please click Resend Code to try again.');
                }
                $this->redirect('/verify-password-change');
                return;
            }

            $this->redirect('/admin/settings');
            return;
        }

        $data = [
            'pageTitle' => 'Settings',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.settings', $data, 'admin');
    }

    public function manageManagers(): void {
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 15;

        $where = "1";
        $params = [];
        if ($search) {
            $where .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR u.email LIKE ? OR m.phone LIKE ?)";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        }
        if ($status && in_array($status, ['active', 'inactive', 'suspended'])) {
            $where .= " AND u.status = ?";
            $params[] = $status;
        }

        $total = $this->db->count('managers m JOIN users u ON m.user_id = u.id', $where, $params);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $managers = $this->db->fetchAll(
            "SELECT m.*, u.email, u.status as user_status, u.created_at as user_created FROM managers m JOIN users u ON m.user_id = u.id WHERE {$where} ORDER BY m.first_name ASC, m.last_name ASC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $stats = [
            'total' => $total,
            'active' => $this->db->count('managers m JOIN users u ON m.user_id = u.id', "1 AND u.status = 'active'"),
            'inactive' => $this->db->count('managers m JOIN users u ON m.user_id = u.id', "1 AND u.status = 'inactive'"),
            'suspended' => $this->db->count('managers m JOIN users u ON m.user_id = u.id', "1 AND u.status = 'suspended'"),
        ];

        $data = [
            'pageTitle' => 'Manage Managers',
            'managers' => $managers,
            'search' => $search,
            'status' => $status,
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages],
            'stats' => $stats,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.manage_managers', $data, 'admin');
    }

    private function notifyManagerCreated(string $managerName, string $managerEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        // Announcement to all registered accounts
        $allUsers = $this->db->fetchAll(
            "SELECT email FROM users WHERE status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (!empty($allUsers)) {
            $subject = 'We Have a New Manager!';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">We Have a New Manager!</h2>'
                . '<p>Hello,</p>'
                . '<p>We are pleased to announce that <strong>' . e($managerName) . '</strong> has joined the Alondes Dorm team as a new manager.</p>'
                . '<p>Please give them a warm welcome!</p>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            foreach ($allUsers as $user) {
                if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) continue;
                try {
                    $mailer = new MailService();
                    $mailer->send($user['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                } catch (\Throwable $e) {
                    error_log('notifyManagerCreated: failed to notify ' . $user['email'] . ' - ' . $e->getMessage());
                }
            }
        }

        // Success notice to admin accounts
        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role = 'super_admin' AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (!empty($admins)) {
            $subject = 'Success to Add Manager!';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#16a34a;margin:0 0 16px;">Success to Add Manager!</h2>'
                . '<p>Hello Admin,</p>'
                . '<p>You have successfully added a new manager to Alondes Dorm.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Manager</td><td style="padding:6px 8px;font-weight:600;">' . e($managerName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Email</td><td style="padding:6px 8px;">' . e($managerEmail) . '</td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            foreach ($admins as $admin) {
                if (!filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) continue;
                try {
                    $mailer = new MailService();
                    $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                } catch (\Throwable $e) {
                    error_log('notifyManagerCreated: failed to notify ' . $admin['email'] . ' - ' . $e->getMessage());
                }
            }
        }
    }

    public function managerCreate(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/manager/create');
                return;
            }

            $email = strtolower(trim($this->input('email', '')));
            $password = $this->input('password', '');
            $firstName = $this->sanitize($this->input('first_name', ''));
            $lastName = $this->sanitize($this->input('last_name', ''));
            $phone = normalizeMobileNumber($this->input('phone', ''));
            $address = $this->sanitize($this->input('address', ''));
            $link = $this->validateUrl($this->input('link', ''));

            if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
                $this->flash('error', 'Email, password, first name, and last name are required.');
                $this->redirect('/admin/manager/create');
                return;
            }

            if ($phone === null) {
                $this->flash('error', 'Please enter a valid 11-digit Philippine mobile number starting with 09.');
                $this->redirect('/admin/manager/create');
                return;
            }

            if (!isValidGmailEmail($email)) {
                $this->flash('error', gmailEmailError('Manager email address', $email));
                $this->redirect('/admin/manager/create');
                return;
            }

            $pwErrors = [];
            if (strlen($password) < 8) $pwErrors[] = 'at least 8 characters';
            if (!preg_match('/[A-Z]/', $password)) $pwErrors[] = 'one uppercase letter';
            if (!preg_match('/[a-z]/', $password)) $pwErrors[] = 'one lowercase letter';
            if (!preg_match('/[0-9]/', $password)) $pwErrors[] = 'one number';
            if (!preg_match('/[^A-Za-z0-9]/', $password)) $pwErrors[] = 'one special character';
            if (!empty($pwErrors)) {
                $this->flash('error', 'Password must contain ' . implode(', ', $pwErrors) . '.');
                $this->redirect('/admin/manager/create');
                return;
            }

            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                $this->flash('error', $this->duplicateUserMessage('email'));
                $this->redirect('/admin/manager/create');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $profilePicture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $profilePicture = $this->uploadFile($_FILES['profile_picture'], 'profiles', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
            }

            try {
                $userId = $this->db->insert('users', [
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role' => 'manager',
                    'status' => 'active',
                    'email_verified' => 1,
                    'email_verified_at' => serverDateTime(),
                    'password_changed_at' => serverDateTime(),
                ]);
            } catch (\PDOException $e) {
                if ($this->getUserDuplicateField($e) === 'email') {
                    // Lost a race against a concurrent submission — reject safely.
                    $this->flash('error', $this->duplicateUserMessage('email'));
                    $this->redirect('/admin/manager/create');
                    return;
                }
                throw $e;
            }

            $this->savePasswordHistory($userId, $hashedPassword);

            $this->db->insert('managers', [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'address' => $address,
                'profile_picture' => $profilePicture,
                'link' => $link,
            ]);

            $this->logActivity('create_manager', "Manager created: {$email}");
            $this->notifyManagerCreated($firstName . ' ' . $lastName, $email);
            $this->flash('success', 'Manager created successfully.');
            $this->redirect('/admin/manage-managers');
            return;
        }

        $data = [
            'pageTitle' => 'Create Manager',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.manager_form', $data, 'admin');
    }

    public function managerEdit(): void {
        $id = (int)$this->input('id');
        $manager = $this->db->fetch(
            "SELECT m.*, u.email, u.status as user_status FROM managers m JOIN users u ON m.user_id = u.id WHERE m.id = ?",
            [$id]
        );
        if (!$manager) {
            $this->flash('error', 'Manager not found.');
            $this->redirect('/admin/manage-managers');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            $firstName = $this->sanitize($this->input('first_name', ''));
            $lastName = $this->sanitize($this->input('last_name', ''));
            $phone = normalizeMobileNumber($this->input('phone', ''));
            $address = $this->sanitize($this->input('address', ''));
            $link = $this->validateUrl($this->input('link', ''));
            $status = $this->input('status', 'active');
            $email = strtolower(trim($this->input('email', '')));

            if (empty($firstName) || empty($lastName)) {
                $this->flash('error', 'First and last name are required.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            if ($phone === null) {
                $this->flash('error', 'Please enter a valid 11-digit Philippine mobile number starting with 09.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            if (!isValidGmailEmail($email)) {
                $this->flash('error', gmailEmailError('Manager email address', $email));
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            $existing = $this->db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $manager['user_id']]);
            if ($existing) {
                $this->flash('error', 'Email already in use by another account.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            $validStatuses = ['active', 'inactive', 'suspended'];
            if (!in_array($status, $validStatuses)) $status = $manager['user_status'];

            if ($status !== 'active' && (int)$manager['user_id'] === (int)($_SESSION['user_id'] ?? 0)) {
                $this->flash('error', 'You cannot deactivate or suspend your own account.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            if ($status !== 'active' && $manager['user_status'] === 'active') {
                $activeCount = $this->db->count('managers m JOIN users u ON m.user_id = u.id', "1 AND u.status = 'active'");
                if ($activeCount <= 1) {
                    $this->flash('error', 'Cannot deactivate the last active manager.');
                    $this->redirect("/admin/manager/edit/{$id}");
                    return;
                }
            }

            $code = trim($this->input('verification_code', ''));
            if (!preg_match('/^\d{6}$/', $code)) {
                $this->flash('error', 'Please enter the 6-digit verification code that was emailed to the manager.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            $otp = $this->db->fetch(
                "SELECT * FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
                [(int)$manager['user_id']]
            );
            if (!$otp) {
                $this->flash('error', 'No verification code found. Click "Send Code" to have one emailed to the manager.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            if (strtotime($otp['expires_at']) < serverTimestamp()) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                $this->flash('error', 'Verification code expired. Please request a new code.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            if ((int)$otp['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                $this->flash('error', 'Too many incorrect attempts. Please request a new code.');
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            if (!hash_equals($otp['code'], $code)) {
                $newAttempts = (int)$otp['attempts'] + 1;
                if ($newAttempts >= self::OTP_MAX_ATTEMPTS) {
                    $this->db->update('email_verification_codes', ['used' => 1, 'attempts' => $newAttempts], "id = ?", [$otp['id']]);
                    $this->flash('error', 'Too many incorrect attempts. Please request a new code.');
                } else {
                    $this->db->update('email_verification_codes', ['attempts' => $newAttempts], "id = ?", [$otp['id']]);
                    $this->flash('error', 'Invalid verification code. Please try again.');
                }
                $this->redirect("/admin/manager/edit/{$id}");
                return;
            }

            $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
            $this->logActivity('update_manager_code_verified', "Manager update code verified: {$email}");

            $managerData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'address' => $address,
                'link' => $link,
            ];

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->uploadFile($_FILES['profile_picture'], 'profiles', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
                if ($uploaded) {
                    if (!empty($manager['profile_picture'])) {
                        $oldPath = UPLOAD_PATH . 'profiles/' . basename($manager['profile_picture']);
                        if (file_exists($oldPath)) @unlink($oldPath);
                    }
                    $managerData['profile_picture'] = $uploaded;
                }
            }

            $this->db->update('managers', $managerData, "id = ?", [$id]);
            $this->db->update('users', [
                'email' => $email,
                'status' => $status,
            ], "id = ?", [$manager['user_id']]);

            $newPassword = $this->input('password', '');
            if (!empty($newPassword)) {
                $pwErrors = [];
                if (strlen($newPassword) < 8) $pwErrors[] = 'at least 8 characters';
                if (!preg_match('/[A-Z]/', $newPassword)) $pwErrors[] = 'one uppercase letter';
                if (!preg_match('/[a-z]/', $newPassword)) $pwErrors[] = 'one lowercase letter';
                if (!preg_match('/[0-9]/', $newPassword)) $pwErrors[] = 'one number';
                if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) $pwErrors[] = 'one special character';
                if (!empty($pwErrors)) {
                    $this->flash('error', 'Password must contain ' . implode(', ', $pwErrors) . '.');
                    $this->redirect("/admin/manager/edit/{$id}");
                    return;
                }
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->db->update('users', [
                    'password' => $hashedPassword,
                    'password_changed_at' => serverDateTime(),
                ], "id = ?", [$manager['user_id']]);
                $this->savePasswordHistory((int)$manager['user_id'], $hashedPassword);
            }

            $this->logActivity('update_manager', "Manager updated: {$email}");
            $this->flash('success', 'Manager updated successfully.');
            $this->redirect('/admin/manage-managers');
            return;
        }

        $data = [
            'pageTitle' => 'Edit Manager',
            'manager' => $manager,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.manager_form', $data, 'admin');
    }

    public function managerDelete(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/admin/manage-managers');
            return;
        }
        $id = (int)$this->input('id');
        $manager = $this->db->fetch("SELECT m.*, u.email, u.status as user_status FROM managers m JOIN users u ON m.user_id = u.id WHERE m.id = ?", [$id]);
        if (!$manager) {
            $this->flash('error', 'Manager not found.');
            $this->redirect('/admin/manage-managers');
            return;
        }

        if ($manager['user_status'] === 'active') {
            $activeCount = $this->db->count('managers m JOIN users u ON m.user_id = u.id', "1 AND u.status = 'active'");
            if ($activeCount <= 1) {
                $this->flash('error', 'Cannot delete the last active manager. Deactivate or suspend another manager first.');
                $this->redirect('/admin/manage-managers');
                return;
            }
        }

        $archived = $this->archiveRecord('manager', $id,
            $this->db->fetch("SELECT * FROM managers WHERE id = ?", [$id]),
            [
                'users'            => [$this->db->fetch("SELECT * FROM users WHERE id = ?", [$manager['user_id']])],
                'password_history' => $this->db->fetchAll("SELECT * FROM password_history WHERE user_id = ?", [$manager['user_id']]),
                'notifications'    => $this->db->fetchAll("SELECT * FROM notifications WHERE user_id = ?", [$manager['user_id']]),
                'user_module_reads'=> $this->db->fetchAll("SELECT * FROM user_module_reads WHERE user_id = ?", [$manager['user_id']]),
            ]
        );
        if (!$archived) {
            $this->flash('error', 'Could not archive this manager. Deletion cancelled.');
            $this->redirect('/admin/manage-managers');
            return;
        }

        $this->db->delete('managers', "id = ?", [$id]);
        $this->db->delete('users', "id = ?", [$manager['user_id']]);
        $this->logActivity('delete_manager', "Manager deleted: {$manager['first_name']} {$manager['last_name']} ({$manager['email']})");
        $managerName = trim($manager['first_name'] . ' ' . $manager['last_name']);
        $notified = $this->notifyRecordDeleted('manager', 'Manager ' . $managerName);
        $this->flash('success', $this->recordDeletedFlash('Manager ' . $managerName, $notified) . ' The record was moved to Archive & Recovery.');
        $this->redirect('/admin/manage-managers');
    }

    /**
     * Create a fresh 6-digit OTP for the manager, invalidating any previous
     * unused code, persisted with a 5-minute expiry. Returns the code or null.
     */
    private function persistManagerOtp(int $userId): ?string {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $this->db->update('email_verification_codes', ['used' => 1], "user_id = ? AND used = 0", [$userId]);

            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $this->db->insert('email_verification_codes', [
                'user_id'    => $userId,
                'code'       => $code,
                'attempts'   => 0,
                'expires_at' => serverNow()->modify('+' . self::OTP_TTL_SECONDS . ' seconds')->format('Y-m-d H:i:s'),
                'created_at' => serverDateTime(),
                'used'       => 0,
            ]);

            $pdo->commit();
            return $code;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('persistManagerOtp: failed to persist OTP for user ' . $userId . ' — ' . $e->getMessage());
            return null;
        }
    }

    private function sendManagerUpdateCode(int $userId, string $email, string $code): bool {
        require_once __DIR__ . '/../Services/MailService.php';
        try {
            $mailer   = new MailService();
            $subject  = 'Manager Account Update Confirmation';
            $body     = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 16px;">Manager Account Update Confirmation</h2>'
                . '<p>Your manager account profile is being updated by the administrator. To confirm this update, enter this verification code:</p>'
                . '<p style="font-size:28px;letter-spacing:4px;color:#8fa61b;font-weight:700;margin:18px 0;">' . $code . '</p>'
                . '<p>The code is 6 digits, expires after 5 minutes, can only be used once, and must not be shared with anyone.</p>'
                . '<p style="color:#64748b;font-size:13px;">If you did not request this change, please contact the system administrator immediately.</p>'
                . '</div>';
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('sendManagerUpdateCode: email send failed for ' . $email . ' — ' . $e->getMessage());
            return false;
        }
    }

    public function managerSendUpdateCode(): void {
        $this->requireCsrf();

        $id = (int)$this->input('id', 0);
        $manager = $this->db->fetch(
            "SELECT m.*, u.email FROM managers m JOIN users u ON m.user_id = u.id WHERE m.id = ?",
            [$id]
        );
        if (!$manager) {
            $this->json(['success' => false, 'message' => 'Manager not found.'], 404);
        }

        $latest = $this->db->fetch(
            "SELECT created_at FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
            [(int)$manager['user_id']]
        );
        if ($latest && (serverTimestamp() - strtotime($latest['created_at'])) < self::OTP_RESEND_COOLDOWN_SECONDS) {
            $remaining = self::OTP_RESEND_COOLDOWN_SECONDS - (serverTimestamp() - strtotime($latest['created_at']));
            $this->json(['success' => false, 'message' => 'Please wait ' . $remaining . ' seconds before requesting another code.'], 429);
        }

        $code = $this->persistManagerOtp((int)$manager['user_id']);
        if ($code === null) {
            $this->json(['success' => false, 'message' => 'Failed to generate the verification code. Please try again.'], 500);
        }

        if (!$this->sendManagerUpdateCode((int)$manager['user_id'], $manager['email'], $code)) {
            $this->json(['success' => false, 'message' => 'The verification code was generated but could not be emailed to ' . $manager['email'] . '. Please check the email address and try again.'], 500);
        }

        $this->json(['success' => true, 'message' => 'Verification code sent to ' . $manager['email'] . '. It expires in 5 minutes.']);
    }

    private function notifyAdminSettingsUpdate(): bool {
        require_once __DIR__ . '/../Services/MailService.php';

        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role = 'super_admin' AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return false;

        $subject = 'System Settings Successfully Updated';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#16a34a;margin:0 0 16px;">Successfully Updated!</h2>'
            . '<p>Hello Admin,</p>'
            . '<p>Your system settings on Alondes Dorm have been successfully updated. Billing rules have been applied to all unpaid payments.</p>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        $sent = 0;
        foreach ($admins as $admin) {
            if (!filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) continue;
            try {
                $mailer = new MailService();
                if ($mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                error_log('notifyAdminSettingsUpdate: failed to notify ' . $admin['email'] . ' - ' . $e->getMessage());
            }
        }
        return $sent > 0;
    }

    public function systemSettings(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/system-settings');
                return;
            }

            $moneySettings = moneySettings();
            $internalKeys = ['payment_reminder_last_run'];
            // Known file (upload) keys. Some rows may carry an empty/incorrect
            // setting_type (e.g. about_image), so we also match on the key itself
            // to guarantee image fields are handled as files both here and in the view.
            $fileSettingKeys = ['site_logo', 'about_image', 'google_maps_image', 'home_hero_bg'];

            // Per-key number validation rules: [min, max, error message].
            // Null min/max means no boundary on that side.
            $numberRules = [
                'monthly_rent'          => [1, null, 'Monthly rent must be a whole number greater than 0.'],
                'advance_payment'       => [0, null, 'Advance payment must be a valid whole number.'],
                'late_fee'              => [0, null, 'Late fee must be a valid whole number.'],
                'tax_rate'              => [0, 100, 'Tax rate must be a whole number between 0 and 100.'],
                'lockout_duration'      => [10, 86400, 'Lockout duration must be a whole number between 10 and 86400 seconds.'],
                'max_login_attempts'    => [1, 100, 'Max login attempts must be a whole number between 1 and 100.'],
                'reservation_expiry_days' => [1, 365, 'Reservation expiry days must be a whole number between 1 and 365.'],
                'grace_period_days'     => [0, 30, 'Grace period days must be a whole number between 0 and 30.'],
                'monthly_rent_due_day'  => [1, 28, 'Monthly rent due day must be a whole number between 1 and 28.'],
                'session_timeout'       => [5, 1440, 'Session timeout must be a whole number between 5 and 1440 minutes.'],
                'password_min_length'   => [8, 128, 'Minimum password length must be a whole number between 8 and 128.'],
                'password_expiry_days'  => [0, 365, 'Password expiry days must be 0 (disabled) or a whole number between 30 and 365.'],
                'smtp_port'             => [1, 65535, 'SMTP port must be a whole number between 1 and 65535.'],
            ];

            $settings = $this->db->fetchAll("SELECT id, setting_key, setting_type, setting_value FROM system_settings");
            foreach ($settings as $s) {
                $key = $s['setting_key'];

                if (in_array($key, $internalKeys)) continue;

                // Only update a setting when it was actually submitted by the form.
                // This lets the simplified website-only editor save without wiping
                // settings from hidden groups (billing / security / email) that are
                // intentionally not rendered on the page.
                if (!in_array($key, $fileSettingKeys)
                    && !isset($_POST[$key])
                    && !isset($_FILES[$key])) {
                    continue;
                }

                if ($s['setting_type'] === 'file' || in_array($key, $fileSettingKeys)) {
                    $remove = $this->input('remove_' . $key, '') === '1';
                    if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                        $uploaded = $this->uploadFile($_FILES[$key], 'settings', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'], 2097152);
                        if ($uploaded) {
                            $this->deleteUploadedFile($s['setting_value'] ?? null);
                            $this->db->update('system_settings', ['setting_value' => $uploaded], "id = ?", [$s['id']]);
                        }
                    } elseif ($remove && !empty($s['setting_value'])) {
                        $this->deleteUploadedFile($s['setting_value']);
                        $this->db->update('system_settings', ['setting_value' => ''], "id = ?", [$s['id']]);
                    }
                    continue;
                }

                if ($s['setting_type'] === 'boolean') {
                    $value = $this->input($key) ? '1' : '0';
                } elseif ($s['setting_type'] === 'number' || $s['setting_type'] === 'integer') {
                    $rawInput = trim((string)$this->input($key, '0'));

                    // Empty inputs on optional numeric fields fall back to 0 so the
                    // value is never stored as a blank string.
                    if ($rawInput === '') $rawInput = '0';

                    if (isset($numberRules[$key])) {
                        [$min, $max, $errMsg] = $numberRules[$key];
                        $intVal = $this->validateWholeNumber($rawInput, $min, $max);
                        // password_expiry_days is special: 0 = disabled, otherwise >= 30.
                        if ($intVal !== null && $key === 'password_expiry_days' && $intVal > 0 && $intVal < 30) {
                            $intVal = null;
                        }
                        if ($intVal === null) {
                            $this->flash('error', $errMsg);
                            $this->redirect('/admin/system-settings');
                            return;
                        }
                        $value = (string)$intVal;
                    } else {
                        $intVal = $this->validateWholeNumber($rawInput, null, null);
                        if ($intVal === null) {
                            $this->flash('error', ucfirst(str_replace('_', ' ', $key)) . ' must be a valid whole number.');
                            $this->redirect('/admin/system-settings');
                            return;
                        }
                        $value = (string)$intVal;
                    }
                } else {
                    $value = $this->sanitize($this->input($key, ''));
                }

                $this->db->update('system_settings', [
                    'setting_value' => $value,
                ], "id = ?", [$s['id']]);
            }

            $this->logActivity('update_settings', 'System settings updated');

            // Re-run billing immediately so Billing/Financial changes
            // (late fee, grace period) apply to existing unpaid bills at once.
            $this->processMonthlyPayments();

            $adminNotified = $this->notifyAdminSettingsUpdate();

            $this->flash('success', 'Successfully Updated!' . ($adminNotified ? ' An email notification has been sent to the administrators.' : ''));
            $this->redirect('/admin/system-settings');
            return;
        }

        $settings = $this->db->fetchAll("SELECT * FROM system_settings ORDER BY setting_group ASC, setting_order ASC, id ASC");

        $data = [
            'pageTitle' => 'System Settings',
            'settings' => $settings,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.system_settings', $data, 'admin');
    }

    public function activityLogs(): void {
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 25;

        $total = $this->db->count('activity_logs');
        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $logs = $this->db->fetchAll(
            "SELECT al.*, u.email FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );

        $data = [
            'pageTitle' => 'Activity Logs',
            'logs' => $logs,
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages],
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.activity_logs', $data, 'admin');
    }

    public function aboutPage(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/admin/about');
                return;
            }

            $section = $this->input('section', '');

            if ($section === 'general') {
                $textFields = ['about_title', 'about_subtitle', 'about_story_title', 'about_story_text', 'about_years_label', 'about_years_sublabel', 'about_mission', 'about_vision', 'about_team_title', 'about_team_subtitle', 'about_values_title', 'about_text'];
                foreach ($textFields as $key) {
                    $value = $this->input($key, '');
                    if ($value !== null) {
                        $existing = $this->db->fetch("SELECT id FROM system_settings WHERE setting_key = ?", [$key]);
                        if ($existing) {
                            $this->db->update('system_settings', ['setting_value' => $this->sanitize($value)], "setting_key = ?", [$key]);
                        } else {
                            $this->db->insert('system_settings', [
                                'setting_key' => $key,
                                'setting_value' => $this->sanitize($value),
                                'setting_type' => 'text',
                                'description' => 'About page setting',
                            ]);
                        }
                    }
                }

                if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = $this->uploadFile($_FILES['about_image'], 'about', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 5242880);
                    if ($uploaded) {
                        $existing = $this->db->fetch("SELECT id, setting_value FROM system_settings WHERE setting_key = 'about_image'");
                        if ($existing) {
                            $this->deleteUploadedFile($existing['setting_value'] ?? null);
                            $this->db->update('system_settings', [
                                'setting_value' => $uploaded,
                                'setting_type' => 'file',
                                'setting_group' => 'about',
                                'setting_order' => 63,
                            ], "id = ?", [$existing['id']]);
                        } else {
                            $this->db->insert('system_settings', [
                                'setting_key' => 'about_image',
                                'setting_value' => $uploaded,
                                'setting_type' => 'file',
                                'setting_group' => 'about',
                                'setting_order' => 63,
                                'description' => 'About page story image',
                            ]);
                        }
                    }
                } elseif ($this->input('remove_about_image', '0') === '1') {
                    $existing = $this->db->fetch("SELECT id, setting_value FROM system_settings WHERE setting_key = 'about_image'");
                    if ($existing) {
                        $this->deleteUploadedFile($existing['setting_value'] ?? null);
                        $this->db->update('system_settings', ['setting_value' => ''], "id = ?", [$existing['id']]);
                    }
                }

                $this->logActivity('update_about', 'About page general settings updated');
                $this->flash('success', 'About page settings saved.');
                $this->redirect('/admin/about');
                return;
            }

            if ($section === 'team_member_add') {
                $name = $this->sanitize($this->input('name', ''));
                $role = $this->sanitize($this->input('role', ''));
                $description = $this->sanitize($this->input('description', ''));
                $facebookUrl = $this->validateUrl($this->input('facebook_url', ''));
                $twitterUrl = $this->validateUrl($this->input('twitter_url', ''));
                $linkedinUrl = $this->validateUrl($this->input('linkedin_url', ''));

                if (empty($name) || empty($role)) {
                    $this->flash('error', 'Name and role are required.');
                    $this->redirect('/admin/about');
                    return;
                }

                $imagePath = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $this->uploadFile($_FILES['image'], 'team', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 2097152);
                }

                $maxOrder = $this->db->fetch("SELECT MAX(sort_order) as m FROM team_members")['m'] ?? 0;

                $this->db->insert('team_members', [
                    'name' => $name,
                    'role' => $role,
                    'description' => $description,
                    'image_path' => $imagePath,
                    'facebook_url' => $facebookUrl,
                    'twitter_url' => $twitterUrl,
                    'linkedin_url' => $linkedinUrl,
                    'sort_order' => $maxOrder + 1,
                ]);

                $this->logActivity('add_team_member', "Team member added: {$name}");
                $this->flash('success', 'Team member added.');
                $this->redirect('/admin/about');
                return;
            }

            if ($section === 'team_member_edit') {
                $id = (int)$this->input('member_id');
                $member = $this->db->fetch("SELECT * FROM team_members WHERE id = ?", [$id]);
                if (!$member) {
                    $this->flash('error', 'Team member not found.');
                    $this->redirect('/admin/about');
                    return;
                }

                $updateData = [
                    'name' => $this->sanitize($this->input('name', '')),
                    'role' => $this->sanitize($this->input('role', '')),
                    'description' => $this->sanitize($this->input('description', '')),
                    'facebook_url' => $this->validateUrl($this->input('facebook_url', '')),
                    'twitter_url' => $this->validateUrl($this->input('twitter_url', '')),
                    'linkedin_url' => $this->validateUrl($this->input('linkedin_url', '')),
                    'status' => $this->input('status', 'active'),
                ];

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = $this->uploadFile($_FILES['image'], 'team', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 2097152);
                    if ($uploaded) $updateData['image_path'] = $uploaded;
                }

                $this->db->update('team_members', $updateData, "id = ?", [$id]);
                $this->logActivity('update_team_member', "Team member updated: {$updateData['name']}");
                $this->flash('success', 'Team member updated.');
                $this->redirect('/admin/about');
                return;
            }

            if ($section === 'team_member_delete') {
                $id = (int)$this->input('member_id');
                $this->db->delete('team_members', "id = ?", [$id]);
                $this->logActivity('delete_team_member', "Team member #{$id} deleted");
                $this->flash('success', 'Team member removed.');
                $this->redirect('/admin/about');
                return;
            }

            if ($section === 'value_add') {
                $icon = $this->sanitize($this->input('icon', 'fas fa-star'));
                $title = $this->sanitize($this->input('title', ''));
                $description = $this->sanitize($this->input('description', ''));
                $color = $this->sanitize($this->input('color', '#2563eb'));

                if (empty($title)) {
                    $this->flash('error', 'Value title is required.');
                    $this->redirect('/admin/about');
                    return;
                }

                $maxOrder = $this->db->fetch("SELECT MAX(sort_order) as m FROM about_values")['m'] ?? 0;

                $this->db->insert('about_values', [
                    'icon' => $icon,
                    'title' => $title,
                    'description' => $description,
                    'color' => $color,
                    'sort_order' => $maxOrder + 1,
                ]);

                $this->logActivity('add_about_value', "About value added: {$title}");
                $this->flash('success', 'Value added.');
                $this->redirect('/admin/about');
                return;
            }

            if ($section === 'value_edit') {
                $id = (int)$this->input('value_id');
                $value = $this->db->fetch("SELECT * FROM about_values WHERE id = ?", [$id]);
                if (!$value) {
                    $this->flash('error', 'Value not found.');
                    $this->redirect('/admin/about');
                    return;
                }

                $this->db->update('about_values', [
                    'icon' => $this->sanitize($this->input('icon', '')),
                    'title' => $this->sanitize($this->input('title', '')),
                    'description' => $this->sanitize($this->input('description', '')),
                    'color' => $this->sanitize($this->input('color', '#2563eb')),
                    'status' => $this->input('status', 'active'),
                ], "id = ?", [$id]);

                $this->logActivity('update_about_value', "About value updated");
                $this->flash('success', 'Value updated.');
                $this->redirect('/admin/about');
                return;
            }

            if ($section === 'value_delete') {
                $id = (int)$this->input('value_id');
                $this->db->delete('about_values', "id = ?", [$id]);
                $this->logActivity('delete_about_value', "About value #{$id} deleted");
                $this->flash('success', 'Value removed.');
                $this->redirect('/admin/about');
                return;
            }
        }

        $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'about_%'");
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['setting_key']] = $s['setting_value'];
        }

        $data = [
            'pageTitle' => 'About Page',
            'aboutSettings' => $settingsMap,
            'teamMembers' => $this->db->fetchAll("SELECT * FROM team_members ORDER BY sort_order ASC"),
            'aboutValues' => $this->db->fetchAll("SELECT * FROM about_values ORDER BY sort_order ASC"),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('admin.about', $data, 'admin');
    }

    public function markModuleReadApi(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
        $module = $this->input('module', '');
        $validModules = ['rooms','reservations','students','payments','announcements','maintenance','complaints','feedback','contact_messages','notifications'];
        if (!in_array($module, $validModules)) { http_response_code(400); echo json_encode(['error' => 'Invalid module']); exit; }
        $this->markModuleRead($module);
        if ($module === 'notifications') {
            $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND is_read = 0", [$userId]);
        }
        $role = $_SESSION['user_role'] ?? '';
        $counts = $this->getSidebarCountsForRole($role);
        echo json_encode(['success' => true, 'counts' => $counts]);
        exit;
    }

    public function dashboardStatsApi(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
        $role = $_SESSION['user_role'] ?? '';
        $sidebar = $this->getSidebarCountsForRole($role);
        $stats = [
            'sidebar' => $sidebar,
            'totalRooms' => $this->db->count('rooms'),
            'availableRooms' => $this->db->count('rooms', "status = 'available'"),
            'occupiedRooms' => $this->db->count('rooms', "status = 'occupied'"),
            'totalStudents' => $this->db->count('students'),
            'totalRevenue' => (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount,0)),0) as t FROM payments WHERE status IN ('paid','partially_paid','refunded') AND amount_paid > 0")['t'] ?? 0),
            'pendingReservations' => $this->db->count('reservations', "status = 'pending'"),
            'openMaintenance' => $this->db->count('maintenance_requests', "status IN ('pending','in_progress')"),
            'openComplaints' => $this->db->count('complaints', "status IN ('open','under_review')"),
        ];
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

}
