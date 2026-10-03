<?php

class HomeController extends Controller {

    public function index(): void {
        $this->reconcileRoomStatuses();
        $data = [
            'pageTitle' => 'Home',
            'featuredRooms' => $this->db->fetchAll(
                "SELECT r.*,
                    (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') as live_occupancy,
                    (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'pending') as pending_count,
                    (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image, 
                    (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count
                 FROM rooms r WHERE r.is_featured = 1 ORDER BY r.created_at DESC LIMIT 6"
            ),
            'testimonials' => $this->db->fetchAll(
                "SELECT * FROM testimonials WHERE status = 'active' AND is_featured = 1 ORDER BY created_at DESC LIMIT 5"
            ),
            'announcements' => $this->db->fetchAll(
                "SELECT * FROM announcements WHERE is_published = 1 ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3"
            ),
            'faqs' => $this->db->fetchAll(
                "SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6"
            ),
            'totalRooms' => $this->db->count('rooms'),
            'availableRooms' => (int)$this->db->fetch(
                "SELECT COUNT(*) as c FROM rooms r
                 WHERE r.status != 'under_maintenance' AND r.status != 'reserved'
                   AND (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') < r.max_capacity"
            )['c'],
            'totalTenants' => (int)($this->db->fetch(
                "SELECT COUNT(DISTINCT s.id) as count FROM reservations r JOIN students s ON r.student_id = s.id WHERE r.status = 'approved'"
            )['count'] ?? 0),
            'amenities' => $this->db->fetchAll(
                "SELECT * FROM amenities WHERE status = 'active' ORDER BY name ASC LIMIT 6"
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.index', $data, 'public');
    }

    public function about(): void {
        $settings = $this->getSettings();
        if (empty($settings['about_years_label'])) {
            $settings['about_years_label'] = $this->calculateYearsOfService();
        }
        $data = [
            'pageTitle' => 'About Us',
            'settings' => $settings,
            'totalRooms' => $this->db->count('rooms'),
            'availableRooms' => (int)$this->db->fetch(
                "SELECT COUNT(*) as c FROM rooms r
                 WHERE r.status != 'under_maintenance' AND r.status != 'reserved'
                   AND (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') < r.max_capacity"
            )['c'],
            'totalTenants' => (int)($this->db->fetch(
                "SELECT COUNT(DISTINCT s.id) as count FROM reservations r JOIN students s ON r.student_id = s.id WHERE r.status = 'approved'"
            )['count'] ?? 0),
            'totalAmenities' => $this->db->count('amenities', "status = 'active'"),
            'teamMembers' => $this->db->fetchAll(
                "SELECT m.first_name, m.last_name, m.phone, m.profile_picture, m.link, u.email
                 FROM managers m JOIN users u ON m.user_id = u.id
                 WHERE u.status = 'active' ORDER BY m.first_name ASC"
            ),
            'aboutValues' => $this->db->fetchAll(
                "SELECT * FROM about_values WHERE status = 'active' ORDER BY sort_order ASC"
            ),
            'aboutFaqs' => $this->db->fetchAll(
                "SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6"
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.about', $data, 'public');
    }

    public function rooms(): void {
        $this->reconcileRoomStatuses();
        $search = $this->input('search', '');
        $minPrice = validateMoney($this->input('min_price', ''), false, 0);
        $maxPrice = validateMoney($this->input('max_price', ''), false, 0);
        $roomType = $this->input('room_type', '');
        $status = $this->input('status', '');
        $sort = $this->input('sort', 'newest');
        $page = (int)($this->input('page', 1));
        $perPage = 12;

        $validTypes = ['bedspacer', 'single', 'studio'];
        if ($roomType && !in_array($roomType, $validTypes)) $roomType = '';
        $validStatuses = ['available', 'occupied', 'reserved', 'under_maintenance'];
        if ($status && !in_array($status, $validStatuses)) $status = '';
        $validSorts = ['newest', 'price_low', 'price_high', 'name_asc', 'name_desc', 'popular'];
        if (!in_array($sort, $validSorts)) $sort = 'newest';

        $where = "1";
        $params = [];

        if ($search) {
            $where .= " AND (r.room_name LIKE ? OR r.description LIKE ? OR r.room_number LIKE ?)";
            $params = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
        }
        if ($minPrice !== null && $minPrice > 0) {
            $where .= " AND r.monthly_rent >= ?";
            $params[] = $minPrice;
        }
        if ($maxPrice !== null && $maxPrice > 0) {
            $where .= " AND r.monthly_rent <= ?";
            $params[] = $maxPrice;
        }
        if ($roomType) {
            $where .= " AND r.room_type = ?";
            $params[] = $roomType;
        }
        if ($status) {
            $where .= " AND r.status = ?";
            $params[] = $status;
        }

        $orderMap = [
            'newest'     => 'r.created_at DESC',
            'price_low'  => 'r.monthly_rent ASC',
            'price_high' => 'r.monthly_rent DESC',
            'name_asc'   => 'r.room_name ASC',
            'name_desc'  => 'r.room_name DESC',
            'popular'    => 'r.is_featured DESC, r.created_at DESC',
        ];
        $orderBy = $orderMap[$sort] ?? 'r.created_at DESC';

        $total = $this->db->fetch(
            "SELECT COUNT(*) as count FROM rooms r WHERE {$where}",
            $params
        )['count'];
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $rooms = $this->db->fetchAll(
            "SELECT r.*,
                (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') as live_occupancy,
                (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'pending') as pending_count,
                (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image,
                (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count
             FROM rooms r WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $data = [
            'pageTitle' => 'Rooms',
            'rooms' => $rooms,
            'search' => $search,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'roomType' => $roomType,
            'status' => $status,
            'sort' => $sort,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $totalPages,
            ],
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.rooms', $data, 'public');
    }

    public function roomDetail(): void {
        $this->reconcileRoomStatuses();
        $id = (int)$this->input('id');
        $room = $this->db->fetch(
            "SELECT r.*,
                (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') as live_occupancy,
                (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'pending') as pending_count,
                (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image,
                (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count
             FROM rooms r WHERE r.id = ?",
            [$id]
        );

        if (!$room) {
            $this->redirect('/404');
            return;
        }

        $images = $this->db->fetchAll(
            "SELECT * FROM room_images WHERE room_id = ? ORDER BY sort_order ASC",
            [$id]
        );

        $amenities = $this->db->fetchAll(
            "SELECT a.* FROM amenities a INNER JOIN room_amenities ra ON a.id = ra.amenity_id WHERE ra.room_id = ? AND a.status = 'active'",
            [$id]
        );

        $similarRooms = $this->db->fetchAll(
            "SELECT r.*,
                (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'approved') as live_occupancy,
                (SELECT COUNT(*) FROM reservations res WHERE res.room_id = r.id AND res.status = 'pending') as pending_count,
                (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image,
                (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count
             FROM rooms r WHERE r.id != ? AND r.status = 'available' ORDER BY RAND() LIMIT 3",
            [$id]
        );

        $data = [
            'pageTitle' => $room['room_name'],
            'room' => $room,
            'images' => $images,
            'amenities' => $amenities,
            'similarRooms' => $similarRooms,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.room_detail', $data, 'public');
    }

    public function gallery(): void {
        $category = $this->input('category', '');

        $where = "status = 'active'";
        $params = [];
        if ($category) {
            $where .= " AND category = ?";
            $params[] = $category;
        }

        $data = [
            'pageTitle' => 'Gallery',
            'gallery' => $this->db->fetchAll(
                "SELECT * FROM gallery WHERE {$where} ORDER BY sort_order ASC",
                $params
            ),
            'categories' => $this->db->fetchAll(
                "SELECT DISTINCT category FROM gallery WHERE status = 'active' ORDER BY category"
            ),
            'selectedCategory' => $category,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.gallery', $data, 'public');
    }

    public function amenities(): void {
        $data = [
            'pageTitle' => 'Amenities',
            'amenities' => $this->db->fetchAll(
                "SELECT * FROM amenities WHERE status = 'active' ORDER BY name ASC"
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.amenities', $data, 'public');
    }

    public function testimonials(): void {
        $data = [
            'pageTitle' => 'Testimonials',
            'testimonials' => $this->db->fetchAll(
                "SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC"
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.testimonials', $data, 'public');
    }

    public function faqs(): void {
        $data = [
            'pageTitle' => 'Frequently Asked Questions',
            'faqs' => $this->db->fetchAll(
                "SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC"
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.faqs', $data, 'public');
    }

    public function contact(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/contact');
                return;
            }

            $name = $this->sanitize($this->input('name', ''));
            $email = strtolower(trim($this->input('email', '')));
            $phone = normalizeMobileNumber($this->input('phone', ''));
            $subject = $this->sanitize($this->input('subject', ''));
            $message = $this->sanitize($this->input('message', ''));

            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                $this->flash('error', 'Please fill in all required fields.');
                $this->redirect('/contact');
                return;
            }

            if (!isValidGmailEmail($email)) {
                $this->flash('error', gmailEmailError('Email address', $email));
                $this->redirect('/contact');
                return;
            }

            if ($phone === null) {
                $this->flash('error', 'Please enter a valid 11-digit Philippine mobile number starting with 09.');
                $this->redirect('/contact');
                return;
            }

            $this->db->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
            ]);

            $this->flash('success', 'Your message has been sent. We will get back to you soon!');
            $this->redirect('/contact');
            return;
        }

        $data = [
            'pageTitle' => 'Contact Us',
            'settings' => $this->getSettings(),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.contact', $data, 'public');
    }

    public function privacy(): void {
        $data = [
            'pageTitle' => 'Privacy Policy',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.privacy', $data, 'public');
    }

    public function terms(): void {
        $data = [
            'pageTitle' => 'Terms and Conditions',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.terms', $data, 'public');
    }

    public function announcements(): void {
        $announcements = $this->db->fetchAll(
            "SELECT * FROM announcements WHERE is_published = 1 ORDER BY COALESCE(published_at, created_at) DESC"
        );
        $data = [
            'pageTitle' => 'Announcements',
            'announcements' => $announcements,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.announcements', $data, 'public');
    }

    public function announcementDetail(): void {
        $id = (int)$this->input('id');
        $announcement = $this->db->fetch(
            "SELECT * FROM announcements WHERE id = ? AND is_published = 1",
            [$id]
        );
        if (!$announcement) {
            $this->redirect('/404');
            return;
        }
        $recent = $this->db->fetchAll(
            "SELECT * FROM announcements WHERE is_published = 1 AND id != ?
             ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3",
            [$id]
        );
        $data = [
            'pageTitle' => $announcement['title'],
            'announcement' => $announcement,
            'recentAnnouncements' => $recent,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('home.announcement_detail', $data, 'public');
    }

    public function notFound(): void {
        http_response_code(404);
        $data = [
            'pageTitle' => 'Page Not Found',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('errors.404', $data, 'public');
    }

    public function markModuleReadApi(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
        $this->requireCsrf();
        $module = $this->input('module', '');
        $validModules = ['rooms','reservations','students','payments','announcements','maintenance','complaints','feedback','contact_messages','notifications'];
        if (!in_array($module, $validModules)) { http_response_code(400); echo json_encode(['error' => 'Invalid module']); exit; }
        $this->markModuleRead($module);
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

        if ($role === 'student') {
            $student = $this->db->fetch("SELECT id FROM students WHERE user_id = ?", [$userId]);
            $sid = $student['id'] ?? 0;
            $stats = [
                'sidebar' => $sidebar,
                'pendingPayments' => $sid ? $this->db->count('payments', "student_id = ? AND status = 'pending'", [$sid]) : 0,
                'totalPaid' => (float)($sid ? ($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount,0)),0) as t FROM payments WHERE student_id = ? AND status IN ('paid','partially_paid','refunded') AND amount_paid > 0", [$sid])['t'] ?? 0) : 0),
                'openMaintenance' => $sid ? $this->db->count('maintenance_requests', "student_id = ? AND status IN ('pending','in_progress')", [$sid]) : 0,
                'activeReservation' => $sid ? $this->db->count('reservations', "student_id = ? AND status = 'approved'", [$sid]) : 0,
            ];
        } else {
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
        }
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }

    /**
     * Return the latest notifications for the logged-in user (unread first)
     * plus the current unread count. Used by the dashboard bell dropdown.
     */
    public function notificationsApi(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

        $perPage = max(1, min(20, (int)($this->input('limit', 8))));
        $notifications = $this->db->fetchAll(
            "SELECT id, type, title, message, reference_id, reference_type,
                    is_read, created_at
             FROM notifications WHERE user_id = ?
             ORDER BY is_read ASC, created_at DESC LIMIT ?",
            [$userId, $perPage]
        );
        $unread = $this->db->count('notifications', "user_id = ? AND is_read = 0", [$userId]);

        echo json_encode(['success' => true, 'unread' => $unread, 'notifications' => $notifications]);
        exit;
    }

    /**
     * Mark a single notification (or all if notification_id is 0) as read.
     * Expects notification_id (int) and csrf_token.
     */
    public function notificationsApiRead(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
        if (!$this->isPost()) { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }

        $raw = file_get_contents('php://input');
        $body = json_decode($raw ?: '', true);
        if (!is_array($body)) {
            $body = $_POST;
        }

        $token = $body['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token)) {
            http_response_code(403); echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']); exit;
        }

        $notificationId = (int)($body['notification_id'] ?? 0);
        if ($notificationId > 0) {
            $this->db->update('notifications', ['is_read' => 1], "id = ? AND user_id = ?", [$notificationId, $userId]);
        } else {
            $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND is_read = 0", [$userId]);
        }

        $unread = $this->db->count('notifications', "user_id = ? AND is_read = 0", [$userId]);
        echo json_encode(['success' => true, 'unread' => $unread]);
        exit;
    }

    private function getSettings(): array {
        $settings = [];
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    private function calculateYearsOfService(): string {
        $years = (int)serverDate('Y') - 2014;
        return $years . '+ Years';
    }
}
