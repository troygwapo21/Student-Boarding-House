<?php

class StudentController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole('student');
    }

    private function getStudent(): ?array {
        return $this->db->fetch("SELECT * FROM students WHERE user_id = ?", [$_SESSION['user_id']]);
    }

    private function isTenant(int $studentId): bool {
        return (bool)$this->db->fetch(
            "SELECT id FROM reservations WHERE student_id = ? AND status = 'approved' LIMIT 1",
            [$studentId]
        );
    }

    private function notifyAdminsNewReservation(array $student, array $room, string $code, string $moveInDate, string $moveInTime, int $duration, string $studentEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $roomLabel   = trim(($room['room_number'] ?? '') . ($room['room_name'] ?? ''));

        $subject = 'New Reservation Submitted';
        $body    = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">New Reservation Submitted</h2>'
            . '<p>A student has submitted a new reservation request. Please review and approve it.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . $studentName . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Email</td><td style="padding:6px 8px;">' . $studentEmail . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;font-weight:600;">' . $roomLabel . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Reservation Code</td><td style="padding:6px 8px;">' . $code . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Move-in Date</td><td style="padding:6px 8px;">' . $moveInDate . ($moveInTime !== '' ? ' at ' . $moveInTime : '') . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Expected Duration</td><td style="padding:6px 8px;">' . $duration . ' month' . ($duration !== 1 ? 's' : '') . '</td></tr>'
            . '</table>'
            . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/admin/reservations" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Review Reservations</a></p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewReservation: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyAdminsReservationChange(string $action, array $student, array $reservation, string $roomLabel, string $studentEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return;

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $code = (string)($reservation['reservation_code'] ?? '');
        $isCancel = ($action === 'cancel');

        $subject = ($isCancel ? 'Reservation Cancelled' : 'Reservation Deleted') . ' by Student';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">' . ($isCancel ? 'Reservation Cancelled' : 'Reservation Deleted') . '</h2>'
            . '<p>A student has ' . ($isCancel ? 'cancelled' : 'deleted') . ' their reservation. Please review the details below.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Email</td><td style="padding:6px 8px;">' . e($studentEmail) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;font-weight:600;">' . e($roomLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Reservation Code</td><td style="padding:6px 8px;">' . e($code) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsReservationChange: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyAdminsRefundRequest(array $student, string $code, string $refundType, float $amount, string $reason, string $gcashNumber): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return;

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $typeLabels = ['monthly' => 'Monthly Rent', 'advance' => 'Advance Payment', 'all' => 'All Payments'];
        $typeLabel = $typeLabels[$refundType] ?? ucfirst($refundType);

        $subject = 'New Refund Request Submitted - ' . $typeLabel;
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">New Refund Request</h2>'
            . '<p>A tenant has submitted a new refund request. Please review it.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Refund Code</td><td style="padding:6px 8px;font-weight:600;">' . e($code) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Refund Type</td><td style="padding:6px 8px;"><strong>' . e($typeLabel) . '</strong></td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">GCash Number</td><td style="padding:6px 8px;">' . e($gcashNumber) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Reason</td><td style="padding:6px 8px;">' . e($reason) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsRefundRequest: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyAdminsRefundCancelled(array $student, array $refund): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return;

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $typeLabels = ['monthly' => 'Monthly Rent', 'advance' => 'Advance Payment', 'all' => 'All Payments'];
        $typeLabel = $typeLabels[$refund['refund_type'] ?? ''] ?? ucfirst((string)($refund['refund_type'] ?? ''));

        $subject = 'Refund Request Cancelled by Student';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Refund Request Cancelled</h2>'
            . '<p>Your tenant has cancelled their refund request.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Refund Code</td><td style="padding:6px 8px;font-weight:600;">' . e((string)($refund['refund_code'] ?? '')) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Refund Type</td><td style="padding:6px 8px;"><strong>' . e($typeLabel) . '</strong></td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency((float)($refund['amount'] ?? 0)) . '</td></tr>'
            . '</table>'
            . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsRefundCancelled: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyAdminsNewMaintenanceRequest(array $student, string $code, string $title, string $description, string $category, string $priority, string $roomLabel): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $account = $this->db->fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $studentEmail = strtolower(trim((string)($account['email'] ?? '')));
        $categoryLabels = ['plumbing' => 'Plumbing', 'electrical' => 'Electrical', 'furniture' => 'Furniture', 'appliance' => 'Appliance', 'structural' => 'Structural', 'other' => 'Other'];
        $priorityLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
        $categoryLabel = $categoryLabels[$category] ?? ucfirst($category);
        $priorityLabel = $priorityLabels[$priority] ?? ucfirst($priority);

        // Notify the student that their maintenance request was submitted successfully
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Maintenance Request Submitted Successfully - ' . $code;
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Maintenance Request Submitted Successfully</h2>'
                . '<p>Hi ' . e($studentName) . ',</p>'
                . '<p>Your maintenance request has been submitted successfully. Our team will review it and address the issue as soon as possible. You can track its status anytime in your portal.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Request Code</td><td style="padding:6px 8px;font-weight:600;">' . e($code) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Title</td><td style="padding:6px 8px;">' . e($title) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Category</td><td style="padding:6px 8px;">' . e($categoryLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Priority</td><td style="padding:6px 8px;"><strong>' . e($priorityLabel) . '</strong></td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;">' . e($roomLabel) . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/student/maintenance" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View My Maintenance Requests</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewMaintenanceRequest: failed to notify student ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify admins and managers that a new maintenance request was submitted
        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return;

        $subject = 'New Maintenance Request - ' . $code;
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">New Maintenance Request</h2>'
            . '<p>A tenant has submitted a new maintenance request. Please review and take action.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:140px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Student Email</td><td style="padding:6px 8px;">' . e($studentEmail) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Request Code</td><td style="padding:6px 8px;font-weight:600;">' . e($code) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Title</td><td style="padding:6px 8px;">' . e($title) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Category</td><td style="padding:6px 8px;">' . e($categoryLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Priority</td><td style="padding:6px 8px;"><strong>' . e($priorityLabel) . '</strong></td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Room</td><td style="padding:6px 8px;">' . e($roomLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Description</td><td style="padding:6px 8px;">' . nl2br(e($description)) . '</td></tr>'
            . '</table>'
            . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/admin/maintenance" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View Maintenance Requests</a></p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewMaintenanceRequest: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyAdminsNewComplaint(array $student, string $code, string $subject, string $description, string $category, string $severity): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $account = $this->db->fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $studentEmail = strtolower(trim((string)($account['email'] ?? '')));
        $categoryLabels = ['noise' => 'Noise', 'cleanliness' => 'Cleanliness', 'security' => 'Security', 'roommate' => 'Roommate', 'management' => 'Management', 'other' => 'Other'];
        $severityLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];
        $categoryLabel = $categoryLabels[$category] ?? ucfirst($category);
        $severityLabel = $severityLabels[$severity] ?? ucfirst($severity);

        // Notify the student that their complaint was submitted successfully
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Complaint Submitted Successfully - ' . $code;
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Complaint Submitted Successfully</h2>'
                . '<p>Hi ' . e($studentName) . ',</p>'
                . '<p>Your complaint has been submitted successfully. Our team will review it and respond as soon as possible. You can track its status anytime in your portal.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Complaint Code</td><td style="padding:6px 8px;font-weight:600;">' . e($code) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Subject</td><td style="padding:6px 8px;">' . e($subject) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Category</td><td style="padding:6px 8px;">' . e($categoryLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Severity</td><td style="padding:6px 8px;"><strong>' . e($severityLabel) . '</strong></td></tr>'
                . '</table>'
                . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/student/complaints" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View My Complaints</a></p>'
                . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewComplaint: failed to notify student ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify admins and managers that a new complaint was submitted
        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return;

        $subject = 'New Complaint - ' . $code;
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">New Complaint</h2>'
            . '<p>A tenant has submitted a new complaint. Please review and take action.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Student Email</td><td style="padding:6px 8px;">' . e($studentEmail) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Complaint Code</td><td style="padding:6px 8px;font-weight:600;">' . e($code) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Subject</td><td style="padding:6px 8px;">' . e($subject) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Category</td><td style="padding:6px 8px;">' . e($categoryLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Severity</td><td style="padding:6px 8px;"><strong>' . e($severityLabel) . '</strong></td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Description</td><td style="padding:6px 8px;">' . nl2br(e($description)) . '</td></tr>'
            . '</table>'
            . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/admin/complaints" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View Complaints</a></p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewComplaint: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyAdminsNewFeedback(array $student, string $subject, string $message, string $category, int $rating, string $studentEmail): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $categoryLabels = ['suggestion' => 'Suggestion', 'compliment' => 'Compliment', 'complaint' => 'Complaint', 'inquiry' => 'Inquiry', 'other' => 'Other'];
        $categoryLabel = $categoryLabels[$category] ?? ucfirst($category);
        $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

        // Notify the student that their feedback was submitted successfully
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Feedback Submitted Successfully';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Feedback Submitted Successfully</h2>'
                . '<p>Hi ' . e($studentName) . ',</p>'
                . '<p>Thank you for your feedback! It has been submitted successfully and our team will review it. We truly appreciate your input in helping us improve Alondes Dorm.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Subject</td><td style="padding:6px 8px;">' . e($subject) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Category</td><td style="padding:6px 8px;">' . e($categoryLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Rating</td><td style="padding:6px 8px;">' . $stars . '</td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewFeedback: failed to notify student ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify admins and managers that new feedback was submitted
        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (empty($admins)) return;

        $subject = 'New Feedback Submitted';
        $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">New Feedback Submitted</h2>'
            . '<p>A tenant has submitted new feedback. Please review it.</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
            . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Student Email</td><td style="padding:6px 8px;">' . e($studentEmail) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Subject</td><td style="padding:6px 8px;">' . e($subject) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Category</td><td style="padding:6px 8px;">' . e($categoryLabel) . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;">Rating</td><td style="padding:6px 8px;">' . $stars . '</td></tr>'
            . '<tr><td style="padding:6px 8px;color:#475569;vertical-align:top;">Message</td><td style="padding:6px 8px;">' . nl2br(e($message)) . '</td></tr>'
            . '</table>'
            . '<p style="margin-top:16px;"><a href="https://localhost/student_boarding_house/admin/feedback" style="display:inline-block;padding:10px 18px;background:#8fa61b;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">View Feedback</a></p>'
            . '<p style="color:#64748b;font-size:13px;">This is an automated notification from Alondes Dorm.</p>'
            . '</div>';

        foreach ($admins as $admin) {
            try {
                $mailer = new MailService();
                $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyAdminsNewFeedback: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
            }
        }
    }

    private function notifyPaymentSubmission(array $student, float $amount, string $paymentType, string $paymentRef): void {
        require_once __DIR__ . '/../Services/MailService.php';

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $account = $this->db->fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $studentEmail = strtolower(trim((string)($account['email'] ?? '')));

        $typeLabels = ['advance_payment' => 'Advance Payment', 'monthly_rent' => 'Monthly Rent', 'reservation_fee' => 'Reservation Fee'];
        $typeLabel = $typeLabels[$paymentType] ?? str_replace('_', ' ', $paymentType);

        // Notify the student that their submission was successful
        if (filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = 'Payment Submission Received';
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#8fa61b;margin:0 0 16px;">Payment Submission Received</h2>'
                . '<p>Hi ' . e($studentName) . ',</p>'
                . '<p>Your payment submission on Alondes Dorm has been received successfully. It is now pending verification and will be marked as paid once approved.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Reference</td><td style="padding:6px 8px;font-weight:600;">' . e($paymentRef) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Payment Type</td><td style="padding:6px 8px;">' . e($typeLabel) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            try {
                $mailer = new MailService();
                $mailer->send($studentEmail, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            } catch (\Throwable $e) {
                error_log('notifyPaymentSubmission: failed to notify student ' . $studentEmail . ' — ' . $e->getMessage());
            }
        }

        // Notify admins that a new payment was submitted
        $admins = $this->db->fetchAll(
            "SELECT email FROM users WHERE role IN ('super_admin','manager') AND status = 'active' AND email_verified = 1 AND email IS NOT NULL AND TRIM(email) != ''"
        );
        if (!empty($admins)) {
            $subject = 'New Payment Submission - ' . $typeLabel;
            $body = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:24px;">'
                . '<h2 style="color:#0f172a;margin:0 0 16px;">New Payment Submission</h2>'
                . '<p>A tenant has submitted a payment to Alondes Dorm. Please verify it.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . '<tr><td style="padding:6px 8px;color:#475569;width:150px;">Student</td><td style="padding:6px 8px;font-weight:600;">' . e($studentName) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Reference</td><td style="padding:6px 8px;">' . e($paymentRef) . '</td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Payment Type</td><td style="padding:6px 8px;"><strong>' . e($typeLabel) . '</strong></td></tr>'
                . '<tr><td style="padding:6px 8px;color:#475569;">Amount</td><td style="padding:6px 8px;font-weight:600;">' . formatCurrency($amount) . '</td></tr>'
                . '</table>'
                . '<p style="color:#64748b;font-size:13px;margin-top:16px;">This is an automated notification from Alondes Dorm.</p>'
                . '</div>';
            foreach ($admins as $admin) {
                try {
                    $mailer = new MailService();
                    $mailer->send($admin['email'], $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                } catch (\Throwable $e) {
                    error_log('notifyPaymentSubmission: failed to notify ' . $admin['email'] . ' — ' . $e->getMessage());
                }
            }
        }
    }

    public function dashboard(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $this->processMonthlyPayments();

        $sid = $student['id'];

        $activeReservation = $this->db->fetch(
            "SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent,
                    (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.room_id AND ri.is_primary = 1 LIMIT 1) as primary_image
             FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.student_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 1",
            [$sid]
        );

        $availableRooms = $this->db->fetchAll(
            "SELECT r.*,
                    (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image,
                    (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count
             FROM rooms r WHERE r.current_occupancy < r.max_capacity AND r.status NOT IN ('under_maintenance') ORDER BY r.room_number"
        );

        $pendingPayments = $this->db->count('payments', "student_id = ? AND status = 'pending'", [$sid]);
        $totalPaid = (float)($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount,0)),0) as total FROM payments WHERE student_id = ? AND status IN ('paid','partially_paid','refunded') AND amount_paid > 0", [$sid])['total'] ?? 0);
        $overduePayments = $this->db->count('payments', "student_id = ? AND status = 'overdue'", [$sid]);
        $totalPayments = $this->db->count('payments', "student_id = ? AND status = 'paid'", [$sid]);
        $openMaintenance = $this->db->count('maintenance_requests', "student_id = ? AND status IN ('pending','in_progress')", [$sid]);
        $openComplaints = $this->db->count('complaints', "student_id = ? AND status IN ('open','under_review')", [$sid]);
        $totalReceipts = $this->db->count('receipts', "payment_id IN (SELECT id FROM payments WHERE student_id = ?)", [$sid]);

        $recentPayments = $this->db->fetchAll(
            "SELECT p.*, r.reservation_code, rm.room_name, rm.room_number FROM payments p LEFT JOIN reservations r ON p.reservation_id = r.id LEFT JOIN rooms rm ON r.room_id = rm.id WHERE p.student_id = ? ORDER BY p.created_at DESC LIMIT 5",
            [$sid]
        );

        $recentMaintenance = $this->db->fetchAll(
            "SELECT * FROM maintenance_requests WHERE student_id = ? ORDER BY created_at DESC LIMIT 3",
            [$sid]
        );

        $recentComplaints = $this->db->fetchAll(
            "SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 3",
            [$sid]
        );

        $recentAnnouncements = $this->db->fetchAll(
            "SELECT * FROM announcements WHERE is_published = 1 ORDER BY created_at DESC LIMIT 5"
        );

        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
            [$_SESSION['user_id']]
        );

        $activityLogs = $this->db->fetchAll(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
            [$_SESSION['user_id']]
        );

        // Monthly payment trend (last 6 months)
        $monthlyTrend = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month_key, DATE_FORMAT(created_at, '%b') as month_label, SUM(amount) as total, status FROM payments WHERE student_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month_key, month_label, status ORDER BY month_key ASC",
            [$sid]
        );

        // Pending payment details for upcoming reminders
        $upcomingPayments = $this->db->fetchAll(
            "SELECT p.*, rm.room_number FROM payments p LEFT JOIN reservations r ON p.reservation_id = r.id LEFT JOIN rooms rm ON r.room_id = rm.id WHERE p.student_id = ? AND p.status = 'pending' AND p.due_date IS NOT NULL ORDER BY p.due_date ASC LIMIT 5",
            [$sid]
        );

        $latestReceipts = $this->db->fetchAll(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.late_fee, p.amount_paid,
                    p.payment_method, p.paid_at, p.billing_period, rm.room_name, rm.room_number
             FROM receipts rc
             JOIN payments p ON rc.payment_id = p.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             JOIN (
                 SELECT p2.payment_type, MAX(rc2.id) AS max_id
                 FROM receipts rc2 JOIN payments p2 ON rc2.payment_id = p2.id
                 WHERE p2.student_id = ?
                 GROUP BY p2.payment_type
             ) latest ON latest.max_id = rc.id
             ORDER BY rc.issued_date DESC, rc.id DESC",
            [$sid]
        );

        $data = [
            'pageTitle' => 'Dashboard',
            'student' => $student,
            'activeReservation' => $activeReservation,
            'isTenant' => $this->isTenant($sid),
            'availableRooms' => $availableRooms,
            'pendingPayments' => $pendingPayments,
            'totalPayments' => $totalPayments,
            'totalPaid' => $totalPaid,
            'overduePayments' => $overduePayments,
            'openMaintenance' => $openMaintenance,
            'openComplaints' => $openComplaints,
            'totalReceipts' => $totalReceipts,
            'recentPayments' => $recentPayments,
            'recentMaintenance' => $recentMaintenance,
            'recentComplaints' => $recentComplaints,
            'recentAnnouncements' => $recentAnnouncements,
            'notifications' => $notifications,
            'unreadNotifications' => $this->db->count('notifications', "user_id = ? AND is_read = 0", [$_SESSION['user_id']]),
            'activityLogs' => $activityLogs,
            'monthlyTrend' => $monthlyTrend,
            'upcomingPayments' => $upcomingPayments,
            'latestReceipts' => $latestReceipts,
            'guardian' => $this->db->fetch(
                "SELECT * FROM guardians WHERE student_id = ? ORDER BY id ASC LIMIT 1",
                [$sid]
            ),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.dashboard', $data, 'student');
    }

    public function profile(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/profile');
                return;
            }

            $firstName  = $this->sanitize($this->input('first_name', ''));
            $middleName = $this->sanitize($this->input('middle_name', ''));
            $lastName   = $this->sanitize($this->input('last_name', ''));
            $suffix     = $this->sanitize($this->input('suffix', ''));

            if (empty($firstName) || empty($lastName)) {
                $this->flash('error', 'First and last name are required.');
                $this->redirect('/student/profile');
                return;
            }

            $phone  = normalizeMobileNumber($this->input('phone', ''));
            if ($phone === null) {
                $this->flash('error', 'Please enter a valid 11-digit Philippine mobile number starting with 09.');
                $this->redirect('/student/profile');
                return;
            }
            $dob    = $this->input('date_of_birth', '');
            $gender = $this->input('gender', '');
            $civilStatus  = $this->sanitize($this->input('civil_status', ''));
            $nationality  = $this->sanitize($this->input('nationality', ''));

            $validCivil = ['single','married','widowed','separated','divorced'];
            if (!empty($civilStatus) && !in_array($civilStatus, $validCivil)) $civilStatus = '';
            $validGenders = ['male','female','other'];
            if (!empty($gender) && !in_array($gender, $validGenders)) $gender = '';

            $houseUnit        = $this->sanitize($this->input('house_unit', ''));
            $street           = $this->sanitize($this->input('street', ''));
            $barangay         = $this->sanitize($this->input('barangay', ''));
            $municipalityCity = $this->sanitize($this->input('municipality_city', ''));
            $province         = $this->sanitize($this->input('province', ''));
            $zipCode          = $this->sanitize($this->input('zip_code', ''));
            $fullAddress = trim(implode(', ', array_filter([$houseUnit, $street, $barangay, $municipalityCity, $province, $zipCode])));

            $studentIdNumber = $this->sanitize($this->input('student_id_number', ''));
            $schoolUniversity = $this->sanitize($this->input('school_university', ''));
            $courseProgram    = $this->sanitize($this->input('course_program', ''));
            $yearLevel        = $this->sanitize($this->input('year_level', ''));

            $validCourses = ['BSIT', 'BSHM', 'BSED', 'BSBA', 'BSCE', 'BSCRIM'];
            if (!empty($courseProgram) && !in_array($courseProgram, $validCourses)) $courseProgram = '';
            $validYears = ['1st Year','2nd Year','3rd Year','4th Year','5th Year'];
            if (!empty($yearLevel) && !in_array($yearLevel, $validYears)) $yearLevel = '';

            $data = [
                'first_name'            => $firstName,
                'middle_name'           => $middleName,
                'last_name'             => $lastName,
                'suffix'                => $suffix,
                'phone'                 => $phone,
                'date_of_birth'         => $dob ?: null,
                'gender'                => $gender,
                'civil_status'          => $civilStatus,
                'nationality'           => $nationality,
                'house_unit'            => $houseUnit,
                'street'                => $street,
                'barangay'              => $barangay,
                'municipality_city'     => $municipalityCity,
                'province'              => $province,
                'zip_code'              => $zipCode,
                'address'               => $fullAddress,
                'student_id_number'     => $studentIdNumber,
                'school_university'     => $schoolUniversity,
                'course_program'        => $courseProgram,
                'year_level'            => $yearLevel,
            ];

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->uploadFile($_FILES['profile_picture'], 'profiles', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
                if ($uploaded) $data['profile_picture'] = $uploaded;
            }

            if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->uploadFile($_FILES['valid_id'], 'valid_ids', ['jpg', 'jpeg', 'png', 'pdf'], 5242880);
                if ($uploaded) $data['valid_id_path'] = $uploaded;
            }

            if (isset($_FILES['school_id_upload']) && $_FILES['school_id_upload']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $this->uploadFile($_FILES['school_id_upload'], 'school_ids', ['jpg', 'jpeg', 'png', 'pdf'], 5242880);
                if ($uploaded) $data['school_id_path'] = $uploaded;
            }

            $this->db->update('students', $data, "id = ?", [$student['id']]);
            $this->logActivity('update_profile', 'Student profile updated');
            $accountEmail = $this->db->fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']])['email'] ?? '';
            $notified = $this->notifyProfileUpdate($accountEmail, $firstName);
            $this->flash('success', 'Profile updated successfully.' . ($notified ? ' Email notification sent to ' . $accountEmail . '.' : ' We could not send the email notification right now.'));
            $this->redirect('/student/profile');
            return;
        }

        $data = [
            'pageTitle' => 'My Profile',
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.profile', $data, 'student');
    }

    public function reservations(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/reservations');
                return;
            }

            $roomId = (int)$this->input('room_id');
            $moveInDate = $this->input('move_in_date', '');
            $moveInTime = $this->input('move_in_time', '');
            if ($moveInTime !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $moveInTime)) $moveInTime = '';
            $duration = (int)$this->input('expected_duration', 1);

            if ($duration < 1 || $duration > 12) $duration = 1;

            $room = $this->db->fetch("SELECT * FROM rooms WHERE id = ? AND current_occupancy < max_capacity AND status != 'under_maintenance'", [$roomId]);
            if (!$room) {
                $this->flash('error', 'This room has reached its maximum capacity and is no longer available for reservation.');
                $this->redirect('/student/reservations');
                return;
            }

            // Gender-based room filtering validation
            $studentGender = $student['gender'] ?? '';
            $requiredRoomName = $studentGender === 'male' ? 'Boys Only' : ($studentGender === 'female' ? 'Girls Only' : '');
            if ($requiredRoomName && $room['room_name'] !== $requiredRoomName) {
                $this->flash('error', 'You can only reserve rooms designated for your gender (' . $requiredRoomName . ').');
                $this->redirect('/student/reservations');
                return;
            }

            if (empty($moveInDate) || strtotime($moveInDate) < strtotime(serverDate())) {
                $this->flash('error', 'Please select a valid move-in date.');
                $this->redirect('/student/reservations');
                return;
            }
            if (strtotime($moveInDate) > strtotime(serverNow()->modify('+3 months')->format('Y-m-d'))) {
                $this->flash('error', 'Move-in date cannot be more than 3 months from today.');
                $this->redirect('/student/reservations');
                return;
            }

            $existing = $this->db->fetch(
                "SELECT id FROM reservations WHERE student_id = ? AND status IN ('pending','approved')",
                [$student['id']]
            );
            if ($existing) {
                $this->flash('error', 'You already have an active reservation.');
                $this->redirect('/student/reservations');
                return;
            }

            $validIdPath = null;
            if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
                $validIdPath = $this->uploadFile($_FILES['valid_id'], 'reservations', ['jpg', 'jpeg', 'png', 'pdf'], 5242880);
            }
            if ($validIdPath === null) {
                $this->flash('error', 'Please upload a valid ID (government-issued ID, JPG/PNG/PDF, max 5MB).');
                $this->redirect('/student/reservations');
                return;
            }

            $code = generateCode('RES');
            $this->db->insert('reservations', [
                'reservation_code' => $code,
                'student_id' => $student['id'],
                'room_id' => $roomId,
                'move_in_date' => $moveInDate,
                'move_in_time' => $moveInTime ? $moveInTime . ':00' : null,
                'expected_duration' => $duration,
                'valid_id_path' => $validIdPath,
            ]);

            $this->logActivity('create_reservation', "Reservation {$code} created for room {$room['room_number']}");
            $this->notifyAdminsNewReservation(
                $student,
                $room,
                $code,
                $moveInDate,
                $moveInTime,
                $duration,
                (string)($_SESSION['user_email'] ?? '')
            );
            $this->flash('success', 'Reservation submitted successfully. Please wait for approval.');
            $this->redirect('/student/reservations');
            return;
        }

        $reservations = $this->db->fetchAll(
            "SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent, rm.room_type, rm.advance_payment,
                    rm.floor, rm.size_sqm, rm.has_bathroom, rm.has_balcony, rm.has_aircon,
                    rm.description, rm.house_rules, rm.furniture,
                    (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.room_id AND ri.is_primary = 1 LIMIT 1) as primary_image
             FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.student_id = ? ORDER BY r.created_at DESC",
            [$student['id']]
        );

        $studentGender = $student['gender'] ?? '';
        $genderRoomName = $studentGender === 'male' ? 'Boys Only' : ($studentGender === 'female' ? 'Girls Only' : '');

        $availableRoomsQuery = "
            SELECT r.*,
                    (SELECT ri.image_path FROM room_images ri WHERE ri.room_id = r.id AND ri.is_primary = 1 LIMIT 1) as primary_image,
                    (SELECT COUNT(*) FROM room_images ri WHERE ri.room_id = r.id) as image_count
            FROM rooms r
            WHERE r.current_occupancy < r.max_capacity
            AND r.status NOT IN ('under_maintenance')
        ";

        if ($genderRoomName) {
            $availableRoomsQuery .= " AND r.room_name = ?";
            $availableRooms = $this->db->fetchAll($availableRoomsQuery . " ORDER BY r.room_number", [$genderRoomName]);
        } else {
            $availableRooms = $this->db->fetchAll($availableRoomsQuery . " ORDER BY r.room_number");
        }

        $allRoomImages = $this->db->fetchAll(
            "SELECT room_id, image_path FROM room_images ORDER BY room_id, is_primary DESC, sort_order ASC"
        );
        $roomImagesMap = [];
        foreach ($allRoomImages as $ri) {
            $roomImagesMap[(int)$ri['room_id']][] = $ri['image_path'];
        }
        foreach ($availableRooms as &$room) {
            $room['images'] = $roomImagesMap[(int)$room['id']] ?? [];
            if (empty($room['images']) && !empty($room['primary_image'])) {
                $room['images'] = [$room['primary_image']];
            }
        }
        unset($room);
        foreach ($reservations as &$res) {
            $res['images'] = $roomImagesMap[(int)$res['room_id']] ?? [];
            if (empty($res['images']) && !empty($res['primary_image'])) {
                $res['images'] = [$res['primary_image']];
            }
        }
        unset($res);

        $hasActiveReservation = (bool)$this->db->fetch(
            "SELECT id FROM reservations WHERE student_id = ? AND status IN ('pending','approved') LIMIT 1",
            [$student['id']]
        );

        $data = [
            'pageTitle' => 'My Reservations',
            'reservations' => $reservations,
            'availableRooms' => $availableRooms,
            'roomImagesMap' => $roomImagesMap,
            'hasActiveReservation' => $hasActiveReservation,
            'openReserve' => $this->input('open', '') === 'reserve',
            'selectedRoomId' => (int)$this->input('room', 0),
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.reservations', $data, 'student');
    }

    public function cancelReservation(): void {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/student/reservations');
            return;
        }

        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $id = (int)$this->input('id');
        $reservation = $this->db->fetch(
            "SELECT * FROM reservations WHERE id = ? AND student_id = ? AND status = 'pending'",
            [$id, $student['id']]
        );

        if (!$reservation) {
            $this->flash('error', 'Reservation not found or cannot be cancelled.');
            $this->redirect('/student/reservations');
            return;
        }

        $this->db->update('reservations', [
            'status' => 'cancelled',
            'cancelled_at' => serverDateTime(),
        ], "id = ?", [$id]);

        if ($reservation['status'] === 'approved') {
            $this->updateRoomOccupancy($reservation['room_id']);
        }

        $room = $this->db->fetch("SELECT room_number, room_name FROM rooms WHERE id = ?", [(int)$reservation['room_id']]);
        $roomLabel = $room ? trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? '')) : 'Room';
        $accountEmail = $this->db->fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']])['email'] ?? '';
        $this->notifyAdminsReservationChange('cancel', $student, $reservation, $roomLabel, $accountEmail);

        $this->logActivity('cancel_reservation', "Reservation {$reservation['reservation_code']} cancelled");
        $this->flash('success', 'Reservation cancelled successfully. Admin notified by email.');
        $this->redirect('/student/reservations');
    }

    public function deleteReservation(): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect('/student/reservations');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/student/reservations');
            return;
        }

        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $id = (int)$this->input('id', 0);
        $reservation = $this->db->fetch(
            "SELECT * FROM reservations WHERE id = ? AND student_id = ?",
            [$id, $student['id']]
        );
        if (!$reservation) {
            $this->flash('error', 'Reservation not found.');
            $this->redirect('/student/reservations');
            return;
        }

        if ($reservation['status'] === 'approved') {
            $this->flash('error', 'Approved reservations cannot be deleted.');
            $this->redirect('/student/reservations');
            return;
        }

        if ($reservation['status'] === 'approved') {
            $this->updateRoomOccupancy($reservation['room_id']);
        }

        $room = $this->db->fetch("SELECT room_number, room_name FROM rooms WHERE id = ?", [(int)$reservation['room_id']]);
        $roomLabel = $room ? trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? '')) : 'Room';
        $accountEmail = $this->db->fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']])['email'] ?? '';
        $this->notifyAdminsReservationChange('delete', $student, $reservation, $roomLabel, $accountEmail);

        if (!empty($reservation['valid_id_path']) && file_exists(UPLOAD_PATH . $reservation['valid_id_path'])) {
            @unlink(UPLOAD_PATH . $reservation['valid_id_path']);
        }

        $this->db->delete('notifications', "reference_id = ? AND reference_type = 'reservation'", [$id]);
        $this->db->delete('reservations', "id = ?", [$id]);

        $this->logActivity('delete_reservation', "Reservation {$reservation['reservation_code']} deleted by student");
        $this->flash('success', "Reservation {$reservation['reservation_code']} deleted successfully. Admin notified by email.");
        $this->redirect('/student/reservations');
    }

    public function payments(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }
        $this->processMonthlyPayments();

        $activeReservation = $this->db->fetch(
            "SELECT r.*, rm.monthly_rent, rm.room_number, rm.room_name FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.student_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 1",
            [$student['id']]
        );

        // Full payment history: summary cards must always reflect ALL
        // payments regardless of the selected month in the droplist.
        $allPayments = $this->db->fetchAll(
            "SELECT p.*, r.reservation_code, rm.room_name, rm.room_number FROM payments p LEFT JOIN reservations r ON p.reservation_id = r.id LEFT JOIN rooms rm ON r.room_id = rm.id WHERE p.student_id = ? ORDER BY p.created_at DESC",
            [$student['id']]
        );

        // Monthly rent / full payment bills with no submission yet are shown
        // as "Not Submitted" (see paymentStatusCell()) instead of being hidden.
        $allPayments = $this->dedupeMonthlyRentPayments($allPayments);

        // Month grouping key: due date when set, otherwise created date so
        // payments without a due date are never lost from list or filter.
        $monthKey = function (array $p): string {
            $base = !empty($p['due_date']) ? substr((string)$p['due_date'], 0, 10) : substr((string)$p['created_at'], 0, 10);
            return substr($base, 0, 7);
        };

        $month = $this->input('month', '');
        $isValidMonth = (bool)preg_match('/^\d{4}-\d{2}$/', $month);

        $validTypes = ['advance_payment', 'monthly_rent', 'full_payment'];
        $type = $this->input('type', '');
        $isValidType = in_array($type, $validTypes, true);

        if ($isValidMonth || $isValidType) {
            $payments = array_values(array_filter($allPayments, function ($p) use ($monthKey, $month, $isValidMonth, $type, $isValidType) {
                if ($isValidMonth && $monthKey($p) !== $month) return false;
                if ($isValidType && (string)$p['payment_type'] !== $type) return false;
                return true;
            }));
        } else {
            $payments = $allPayments;
        }

        // Droplist options: every month in which the student has a payment.
        $monthOptions = [];
        foreach ($allPayments as $p) {
            $ym = $monthKey($p);
            if (preg_match('/^\d{4}-\d{2}$/', $ym)) $monthOptions[$ym] = true;
        }
        krsort($monthOptions);

        // Type droplist options: only types the student actually has.
        $typeOptions = [];
        foreach ($allPayments as $p) {
            $t = (string)$p['payment_type'];
            if ($t !== '') $typeOptions[$t] = true;
        }
        ksort($typeOptions);

        $data = [
            'pageTitle' => 'My Payments',
            'payments' => $payments,
            'summaryPayments' => $allPayments,
            'student' => $student,
            'activeReservation' => $activeReservation,
            'month' => $isValidMonth ? $month : '',
            'monthOptions' => $monthOptions,
            'type' => $isValidType ? $type : '',
            'typeOptions' => $typeOptions,
            'billingLateFee' => (float) getSettingValue('late_fee', 100),
            'billingGraceDays' => max(0, (int) getSettingValue('grace_period_days', 0)),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.payments', $data, 'student');
    }

    public function paymentCreate(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }
        $this->processMonthlyPayments();

        $activeReservation = $this->db->fetch(
            "SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent, rm.advance_payment FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.student_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 1",
            [$student['id']]
        );

        // Get last due date for custom payment calculation
        $lastPaid = $this->db->fetch(
            "SELECT due_date FROM payments WHERE student_id = ? AND due_date IS NOT NULL ORDER BY due_date DESC LIMIT 1",
            [$student['id']]
        );
        $lastDueDate = $lastPaid ? ($lastPaid['due_date'] ?? '') : '';

        // Get outstanding bills for display (when no target payment)
        $outstandingBills = [];
        if ($activeReservation) {
            $outstandingBills = $this->db->fetchAll(
                "SELECT * FROM payments
                 WHERE student_id = ? AND status IN ('pending','upcoming','due_today','partially_paid','overdue')
                 ORDER BY (due_date IS NULL) ASC, due_date ASC, id ASC",
                [$student['id']]
            );
        }

        // Monthly Rent is blocked once a submission already exists
        // (awaiting approval or fully paid with nothing outstanding).
        $monthlySubmittedRow = $this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND amount_paid > 0 AND status IN ('pending','partially_paid') LIMIT 1",
            [$student['id']]
        );
        $hasOutstandingMonthly = (bool)$this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') LIMIT 1",
            [$student['id']]
        );
        $hasPaidMonthly = (bool)$this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status = 'paid' LIMIT 1",
            [$student['id']]
        );
        $monthlyAlreadySubmitted = (bool)$monthlySubmittedRow || ($hasPaidMonthly && !$hasOutstandingMonthly);

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/payment/create');
                return;
            }

            $t = fn(string $key, string $default = ''): string => trim((string)$this->input($key, $default));
            $paymentMethod = $t('payment_method');
            $reference = $t('payment_reference');
            $notes = trim((string)$this->input('payment_notes', ''));
            $selectedIds = array_map('intval', (array)$this->input('payment_ids', []));
            // Also support payment_id (singular) from "Pay Now" button on existing bills
            $singlePaymentId = (int)$this->input('payment_id', 0);
            if ($singlePaymentId > 0 && !in_array($singlePaymentId, $selectedIds, true)) {
                $selectedIds[] = $singlePaymentId;
            }
            $customType = $t('custom_type');
            $customAmount = $this->validateWholeNumber(str_replace(',', '', (string)$this->input('custom_amount', '')), 1);
            $customDuration = max(1, (int)$this->input('duration_months', $this->input('custom_duration', 1)));
            $customDueDate = $t('custom_due_date');

            $errors = [];
            if (!in_array($paymentMethod, ['cash', 'gcash'])) $errors[] = 'Payment method is required.';
            if (!$activeReservation) $errors[] = 'No active reservation found.';
            if (empty($selectedIds) && $customAmount === null) $errors[] = 'Select a bill to pay or enter a custom payment amount.';
            if ($customAmount !== null && !in_array($customType, ['monthly_rent', 'advance_payment'])) {
                $errors[] = 'Invalid custom payment type.';
            }
            if ($customDuration < 1 || $customDuration > 12) $errors[] = 'Duration must be between 1 and 12 months.';
            if ($paymentMethod === 'gcash' && empty($reference)) $errors[] = 'Reference / OR Number is required for GCash payments.';

            $proofPath = null;
            if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
                $proofPath = $this->uploadFile($_FILES['proof_of_payment'], 'payments', ['jpg', 'jpeg', 'png', 'gif', 'pdf'], 5242880);
            }
            if ($paymentMethod === 'gcash' && $proofPath === null) {
                $errors[] = 'Please upload a proof of payment for GCash transactions.';
            }

            $selectedBills = [];
            if (!empty($selectedIds) && $activeReservation) {
                foreach ($outstandingBills as $bill) {
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

            // Payment Type: Monthly Rent may not be used to create a new payment
            // once a Monthly Rent submission already exists (pending approval or paid).
            if ($customAmount !== null && $customAmount > 0 && empty($selectedBills)
                && $customType === 'monthly_rent' && $monthlyAlreadySubmitted) {
                $errors[] = 'You already submitted a Monthly Rent payment. Please wait for it to be verified before submitting another.';
            }

            if (!empty($errors)) {
                $pendingAdvance = $this->db->fetch(
                    "SELECT id, amount, due_date FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') ORDER BY created_at DESC LIMIT 1",
                    [$student['id']]
                );
                $currentMonthlyBill = $this->db->fetch(
                    "SELECT id, amount, late_fee, amount_paid, due_date, status, billing_period FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') ORDER BY due_date ASC, id ASC LIMIT 1",
                    [$student['id']]
                );
                $monthlyAlreadyPaid = (bool)$this->db->fetch(
                    "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status = 'paid' LIMIT 1",
                    [$student['id']]
                ) && !$currentMonthlyBill;
                $advanceAlreadyPaid = (bool)$this->db->fetch(
                    "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status = 'paid' LIMIT 1",
                    [$student['id']]
                ) && !$this->db->fetch(
                    "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') LIMIT 1",
                    [$student['id']]
                );

                $targetPayment = null;
                $targetId = (int)($_GET['id'] ?? 0);
                if ($targetId > 0) {
                    $targetPayment = $this->db->fetch(
                        "SELECT id, amount, late_fee, amount_paid, due_date, status, billing_period, payment_type FROM payments WHERE id = ? AND student_id = ? AND status != 'paid' AND status != 'cancelled'",
                        [$targetId, $student['id']]
                    );
                    if ($targetPayment) {
                        if ($targetPayment['payment_type'] === 'monthly_rent') {
                            $currentMonthlyBill = $targetPayment;
                        } elseif ($targetPayment['payment_type'] === 'advance_payment') {
                            $pendingAdvance = $targetPayment;
                        }
                    }
                }

                $outstandingBills = [];

                $defaultCustomType = $targetPayment ? $targetPayment['payment_type'] : ($customType ?: 'monthly_rent');
                if ($monthlyAlreadySubmitted && $defaultCustomType === 'monthly_rent' && empty($targetPayment)) {
                    $defaultCustomType = 'advance_payment';
                }

                $data = [
                    'pageTitle' => 'Submit Payment',
                    'student' => $student,
                    'activeReservation' => $activeReservation,
                    'pendingAdvance' => $pendingAdvance,
                    'currentMonthlyBill' => $currentMonthlyBill,
                    'monthlyAlreadyPaid' => $monthlyAlreadyPaid,
                    'monthlyAlreadySubmitted' => $monthlyAlreadySubmitted,
                    'advanceAlreadyPaid' => $advanceAlreadyPaid,
                    'targetPayment' => $targetPayment,
                    'outstandingBills' => $outstandingBills,
                    'lastDueDate' => $lastDueDate,
                    'errors' => $errors,
                    'old' => [
                        'payment_method' => $paymentMethod,
                        'payment_reference' => $reference,
                        'payment_notes' => $notes,
                        'payment_ids' => $selectedIds,
                        'custom_type' => $defaultCustomType,
                        'custom_amount' => $customAmount ?? '',
                        'custom_duration' => $customDuration,
                        'custom_due_date' => $customDueDate,
                    ],
                    'flashMessages' => $this->getFlashMessages(),
                ];
                $this->view('student.payment_create', $data, 'student');
                return;
            }

            $allocated = 0.0;
            $remaining = (float)$received;
            $roomLabel = $activeReservation ? "Room {$activeReservation['room_number']} ({$activeReservation['room_name']})" : ($student['first_name'] . ' ' . $student['last_name']);
            $studentUser = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$student['id']]);
            $studentUserId = $studentUser ? (int)$studentUser['user_id'] : 0;

            // Calculate due date based on reservation
            $moveInDate = $activeReservation ? ($activeReservation['move_in_date'] ?: serverDate('Y-m-d')) : serverDate('Y-m-d');
            $duration = $activeReservation ? (int)($activeReservation['expected_duration'] ?? 1) : 1;

            // Handle custom payment (new bill creation) - ONLY if not paying existing bills
            if ($customAmount !== null && $customAmount > 0 && empty($selectedBills)) {
                $alloc = min($remaining, (float)$customAmount);
                $remaining -= $alloc;
                $allocated += $alloc;

                // Calculate total bill amount and due date based on payment type
                $totalAmount = (float)$customAmount;
                $billDueDate = $customDueDate ?: null;
                $billingPeriod = null;

                if ($customType === 'monthly_rent' && $activeReservation) {
                    $monthlyRent = (float)($activeReservation['monthly_rent'] ?? 0);
                    if ($monthlyRent <= 0) $monthlyRent = (float)$activeReservation['monthly_rent'];
                    $taxRateRow = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'tax_rate'");
                    $taxRate = $taxRateRow ? (float)$taxRateRow['setting_value'] : 0;
                    $totalAmount = round($monthlyRent * $duration, 2);
                    if ($taxRate > 0) {
                        $totalAmount = round($totalAmount + ($totalAmount * $taxRate / 100), 2);
                    }
                    $billDueDate = $moveInDate;
                    $billingPeriod = substr($moveInDate, 0, 7);
                } elseif ($customType === 'advance_payment' && $activeReservation) {
                    $rentAmount = (float)($activeReservation['monthly_rent'] ?? 0);
                    $advancePayment = (float)($activeReservation['advance_payment'] ?? 0);
                    $advBase = $rentAmount > 0 ? $rentAmount : $advancePayment;
                    $advDuration = max(1, min(12, $customDuration));
                    $totalAmount = $advBase > 0 ? round($advBase * $advDuration, 2) : (float)$customAmount;
                    // Due date = anchor (move-in / pending advance due / today) + selected duration
                    $pendingAdvRow = $this->db->fetch(
                        "SELECT due_date FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') ORDER BY created_at DESC LIMIT 1",
                        [$student['id']]
                    );
                    $advAnchor = $moveInDate
                        ?: (!empty($pendingAdvRow['due_date']) ? substr((string)$pendingAdvRow['due_date'], 0, 10) : '')
                        ?: serverDate();
                    $billDueDate = addCalendarMonths($advAnchor, $advDuration) ?: $billDueDate;
                }

                $durationNote = $customDuration > 1 ? " ({$customDuration} months)" : '';
                $paymentId = $this->db->insert('payments', [
                    'payment_code' => generateCode('PAY'),
                    'student_id' => $student['id'],
                    'reservation_id' => $activeReservation ? $activeReservation['id'] : null,
                    'payment_type' => $customType,
                    'amount' => $totalAmount,
                    'late_fee' => 0,
                    'amount_paid' => $alloc,
                    'payment_method' => $paymentMethod,
                    'status' => 'pending', // Student submissions are pending until admin verifies
                    'reference_number' => $reference ?: null,
                    'due_date' => $billDueDate,
                    'billing_period' => $billingPeriod,
                    'penalty_applied' => 0,
                    'notes' => ($notes ? $notes . ' — ' : '') . $customType . $durationNote . ' payment submitted by student',
                ]);

                $this->db->insert('payment_history', [
                    'payment_id' => $paymentId,
                    'action' => 'student_submission',
                    'old_status' => null,
                    'new_status' => 'pending',
                    'notes' => 'Custom ' . $customType . ' payment submitted by student' . ($reference ? " (Ref: {$reference})" : '') . ' for ' . $roomLabel,
                    'performed_by' => $studentUserId,
                ]);

                $this->db->insert('receipts', [
                    'receipt_number' => generateCode('REC'),
                    'payment_id' => $paymentId,
                    'issued_date' => serverDate(),
                    'subtotal' => $totalAmount,
                    'discount' => 0,
                    'total' => $alloc,
                ]);

                if ($studentUserId) {
                    $this->db->insert('notifications', [
                        'user_id' => $studentUserId,
                        'title' => 'Payment Submitted',
                        'message' => "Your custom payment of " . formatCurrency($alloc) . " via " . strtoupper($paymentMethod) . " for {$roomLabel} has been submitted and is pending verification.",
                        'type' => 'payment',
                    ]);
                }
            }

            // Handle selected existing bills
            if ($remaining > 0 && !empty($selectedBills)) {
                $result = $this->allocateStudentPayment($selectedBills, $paymentMethod, $remaining, $reference, $notes, $proofPath, $student['id'], $activeReservation);
                $allocated += $result['allocated'];
            }

            $this->logActivity('student_payment_submission', "Student payment of " . formatCurrency($allocated) . " submitted via " . strtoupper($paymentMethod) . " for {$roomLabel}.");

            $confirmed = false;
            if ($studentUserId) {
                $this->db->insert('notifications', [
                    'user_id' => $studentUserId,
                    'title' => 'Payment Submitted',
                    'message' => "Your payment of " . formatCurrency($allocated) . " via " . strtoupper($paymentMethod) . " for {$roomLabel} has been submitted and is pending verification.",
                    'type' => 'payment',
                ]);
                $confirmed = true;
            }

            $this->flash('success', 'Payment of ' . formatCurrency($allocated) . ' submitted via ' . strtoupper($paymentMethod) . '.' . ($confirmed ? ' Confirmation sent.' : ''));
            $this->redirect('/student/payments');
            return;
        }

        // GET request - load data for view
        // Bills are no longer auto-created. Fetch only existing payments (from previous submissions).
        $pendingAdvance = $this->db->fetch(
            "SELECT id, amount, due_date FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') ORDER BY created_at DESC LIMIT 1",
            [$student['id']]
        );
        $currentMonthlyBill = $this->db->fetch(
            "SELECT id, amount, late_fee, amount_paid, due_date, status, billing_period FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') ORDER BY due_date ASC, id ASC LIMIT 1",
            [$student['id']]
        );
        $monthlyAlreadyPaid = (bool)$this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status = 'paid' LIMIT 1",
            [$student['id']]
        ) && !$currentMonthlyBill;
        $advanceAlreadyPaid = (bool)$this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status = 'paid' LIMIT 1",
            [$student['id']]
        ) && !$this->db->fetch(
            "SELECT id FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status IN ('pending','upcoming','due_today','partially_paid','overdue') LIMIT 1",
            [$student['id']]
        );

        // No outstanding bills to display (bills created on submission)
        $outstandingBills = [];

        $targetPayment = null;
        $targetId = (int)($_GET['id'] ?? 0);
        if ($targetId > 0) {
            $targetPayment = $this->db->fetch(
                "SELECT id, amount, late_fee, amount_paid, due_date, status, billing_period, payment_type FROM payments WHERE id = ? AND student_id = ? AND status != 'paid' AND status != 'cancelled'",
                [$targetId, $student['id']]
            );
            if ($targetPayment) {
                if ($targetPayment['payment_type'] === 'monthly_rent') {
                    $currentMonthlyBill = $targetPayment;
                } elseif ($targetPayment['payment_type'] === 'advance_payment') {
                    $pendingAdvance = $targetPayment;
                }
            }
        }

        $defaultCustomType = $targetPayment ? $targetPayment['payment_type'] : 'monthly_rent';
        if ($monthlyAlreadySubmitted && $defaultCustomType === 'monthly_rent' && empty($targetPayment)) {
            $defaultCustomType = 'advance_payment';
        }
        $data = [
            'pageTitle' => 'Submit Payment',
            'student' => $student,
            'activeReservation' => $activeReservation,
            'pendingAdvance' => $pendingAdvance,
            'currentMonthlyBill' => $currentMonthlyBill,
            'monthlyAlreadyPaid' => $monthlyAlreadyPaid,
            'monthlyAlreadySubmitted' => $monthlyAlreadySubmitted,
            'advanceAlreadyPaid' => $advanceAlreadyPaid,
            'targetPayment' => $targetPayment,
            'outstandingBills' => $outstandingBills,
            'lastDueDate' => $lastDueDate,
            'errors' => [],
            'old' => ['payment_method' => 'cash', 'payment_reference' => '', 'payment_notes' => '', 'payment_ids' => [], 'custom_type' => $defaultCustomType, 'custom_amount' => '', 'custom_duration' => '1', 'custom_due_date' => ''],
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.payment_create', $data, 'student');
    }

    public function paymentDetail(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $id = (int)$this->input('id');
        $payment = $this->db->fetch(
            "SELECT p.*, r.reservation_code, r.move_in_date, r.expected_duration, rm.room_name, rm.room_number FROM payments p LEFT JOIN reservations r ON p.reservation_id = r.id LEFT JOIN rooms rm ON r.room_id = rm.id WHERE p.id = ? AND p.student_id = ?",
            [$id, $student['id']]
        );

        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('/student/payments');
            return;
        }

        $data = [
            'pageTitle' => 'Payment Details',
            'payment' => $payment,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.payment_detail', $data, 'student');
    }

    public function paymentDelete(): void {
        if (!$this->isPost()) {
            $this->flash('error', 'Invalid request method.');
            $this->redirect('/student/payments');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/student/payments');
            return;
        }

        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $id = (int)$this->input('id', 0);
        $payment = $this->db->fetch("SELECT * FROM payments WHERE id = ? AND student_id = ?", [$id, $student['id']]);
        if (!$payment) {
            $this->flash('error', 'Payment not found.');
            $this->redirect('/student/payments');
            return;
        }

        // Tenants can never delete Monthly Rent or Advance Payment records.
        if (in_array($payment['payment_type'], ['monthly_rent', 'advance_payment'], true)) {
            $this->flash('error', ucwords(str_replace('_', ' ', $payment['payment_type'])) . ' payments cannot be deleted.');
            $this->redirect('/student/payments');
            return;
        }

        if (in_array($payment['status'], ['paid', 'due_today', 'overdue'], true)) {
            $this->flash('error', 'Payments with status "' . ucwords(str_replace('_', ' ', $payment['status'])) . '" cannot be deleted.');
            $this->redirect('/student/payments');
            return;
        }

        $paymentCode = $payment['payment_code'];
        $paymentAmount = formatCurrency($payment['amount']);
        $paymentType = ucwords(str_replace('_', ' ', $payment['payment_type']));
        $studentName = $student['first_name'] . ' ' . $student['last_name'];

        if (!empty($payment['proof_of_payment']) && file_exists(UPLOAD_PATH . $payment['proof_of_payment'])) {
            @unlink(UPLOAD_PATH . $payment['proof_of_payment']);
        }

        $this->db->delete('notifications', "reference_id = ? AND reference_type = 'payment'", [$id]);

        $staff = $this->db->fetchAll("SELECT u.id FROM users u WHERE u.role IN ('manager','super_admin') AND u.status = 'active'");
        foreach ($staff as $member) {
            $this->db->insert('notifications', [
                'user_id' => $member['id'],
                'title' => 'Payment Deleted by Student',
                'message' => "Payment {$paymentCode} ({$paymentType}, {$paymentAmount}) for {$studentName} was deleted by the student.",
                'type' => 'payment',
                'reference_id' => null,
                'reference_type' => null,
            ]);
        }

        $this->db->delete('payment_history', "payment_id = ?", [$id]);
        $this->db->delete('receipts', "payment_id = ?", [$id]);
        $this->db->delete('payments', "id = ?", [$id]);
        $this->logActivity('delete_payment', "Payment {$paymentCode} ({$paymentType}, {$paymentAmount}) deleted by student â€” student: {$studentName}");
        $this->flash('success', "Payment {$paymentCode} deleted successfully.");
        $this->redirect('/student/payments');
    }

    public function receipts(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $validTypes = ['reservation_fee', 'advance_payment', 'monthly_rent', 'electric_bill', 'water_bill', 'other'];
        $type = trim((string)$this->input('type', ''));
        $isValidType = $type !== '' && in_array($type, $validTypes, true);
        $month = $this->input('month', '');
        $isValidMonth = (bool)preg_match('/^\d{4}-\d{2}$/', $month);
        $where = "p.student_id = ?";
        $params = [$student['id']];
        if ($isValidType) {
            $where .= " AND p.payment_type = ?";
            $params[] = $type;
        }
        if ($isValidMonth) {
            $where .= " AND DATE_FORMAT(rc.issued_date, '%Y-%m') = ?";
            $params[] = $month;
        }

        $receipts = $this->db->fetchAll(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.payment_method, p.paid_at, p.status as payment_status FROM receipts rc JOIN payments p ON rc.payment_id = p.id WHERE $where ORDER BY rc.issued_date DESC, rc.id DESC",
            $params
        );

        $monthRows = $this->db->fetchAll(
            "SELECT DISTINCT DATE_FORMAT(rc.issued_date, '%Y-%m') as m FROM receipts rc JOIN payments p ON rc.payment_id = p.id WHERE p.student_id = ? AND rc.issued_date IS NOT NULL ORDER BY m DESC",
            [$student['id']]
        );
        $monthOptions = [];
        if ($isValidMonth) {
            $monthOptions[$month] = true;
        }
        foreach ($monthRows as $row) {
            $monthOptions[$row['m']] = true;
        }
        krsort($monthOptions);

        // Type droplist: only payment types the student actually has receipts for.
        $distinctTypes = $this->db->fetchAll(
            "SELECT DISTINCT p.payment_type FROM receipts rc JOIN payments p ON rc.payment_id = p.id WHERE p.student_id = ? ORDER BY p.payment_type ASC",
            [$student['id']]
        );
        $paymentTypes = [];
        foreach ($distinctTypes as $dt) {
            $key = (string)($dt['payment_type'] ?? '');
            if ($key !== '') $paymentTypes[$key] = ucwords(str_replace('_', ' ', $key));
        }
        ksort($paymentTypes);

        $months = [];
        foreach ($receipts as $receipt) {
            $ym = substr((string)$receipt['issued_date'], 0, 7);
            if ($ym === '') continue;
            if (!isset($months[$ym])) {
                $months[$ym] = [
                    'key' => $ym,
                    'label' => date('F Y', strtotime($ym . '-01')),
                    'receipts' => [],
                    'total' => 0.0,
                    'count' => 0,
                ];
            }
            $months[$ym]['receipts'][] = $receipt;
            $months[$ym]['total'] += (float)($receipt['total'] ?? $receipt['amount'] ?? 0);
            $months[$ym]['count']++;
        }

        $data = [
            'pageTitle' => 'My Receipts',
            'receipts' => $receipts,
            'months' => $months,
            'paymentTypes' => $paymentTypes,
            'activeType' => $isValidType ? $type : '',
            'month' => $isValidMonth ? $month : '',
            'monthOptions' => $monthOptions,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.receipts', $data, 'student');
    }

    public function monthlyReceipt(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $month = (string)$this->input('month', '');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->flash('error', 'Invalid month.');
            $this->redirect('/student/receipts');
            return;
        }

        $receipts = $this->db->fetchAll(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.late_fee, p.amount_paid,
                    p.payment_method, p.paid_at, p.billing_period, p.reservation_id,
                    rm.room_name, rm.room_number
             FROM receipts rc
             JOIN payments p ON rc.payment_id = p.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE p.student_id = ? AND DATE_FORMAT(rc.issued_date, '%Y-%m') = ?
             ORDER BY rc.issued_date ASC, rc.id ASC",
            [$student['id'], $month]
        );

        if (empty($receipts)) {
            $this->flash('error', 'No receipts found for that month.');
            $this->redirect('/student/receipts');
            return;
        }

        $monthTotal = 0.0;
        foreach ($receipts as $receipt) {
            $monthTotal += (float)($receipt['total'] ?? $receipt['amount'] ?? 0);
        }

        $data = [
            'pageTitle' => 'Monthly Statement - ' . date('F Y', strtotime($month . '-01')),
            'receipts' => $receipts,
            'month' => $month,
            'monthLabel' => date('F Y', strtotime($month . '-01')),
            'monthTotal' => $monthTotal,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.receipt_monthly', $data, 'student');
    }

    public function rentReceipt(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $period = (string)$this->input('period', '');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            $this->flash('error', 'Invalid billing period.');
            $this->redirect('/student/receipts');
            return;
        }

        $payments = $this->db->fetchAll(
            "SELECT p.*, rc.id as receipt_id, rc.receipt_number, rc.issued_date as receipt_issued_date,
                    rm.room_name, rm.room_number, r.move_in_date, r.expected_duration, r.rent_amount
             FROM payments p
             LEFT JOIN receipts rc ON rc.payment_id = p.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE p.student_id = ? AND p.payment_type = 'monthly_rent' AND p.billing_period = ?
             ORDER BY p.id ASC",
            [$student['id'], $period]
        );

        if (empty($payments)) {
            $this->flash('error', 'No monthly rent found for that billing period.');
            $this->redirect('/student/receipts');
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
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.receipt_rent', $data, 'student');
    }

    public function receiptDetail(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $id = (int)$this->input('id');
        $receipt = $this->db->fetch(
            "SELECT rc.*, p.payment_code, p.payment_type, p.amount, p.status as payment_status,
                    p.payment_method, p.paid_at, p.notes, p.late_fee, p.amount_paid,
                    p.billing_period, p.due_date, p.reference_number, p.reservation_id,
                    s.first_name, s.last_name, s.student_id_number,
                    s.phone, s.school_university, u.email as student_email,
                    rm.room_name, rm.room_number,
                    r.move_in_date, r.expected_duration, r.rent_amount
             FROM receipts rc
             JOIN payments p ON rc.payment_id = p.id
             JOIN students s ON p.student_id = s.id
             JOIN users u ON s.user_id = u.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE rc.id = ? AND p.student_id = ?",
            [$id, $student['id']]
        );

        if (!$receipt) {
            $this->flash('error', 'Receipt not found.');
            $this->redirect('/student/receipts');
            return;
        }

        $data = [
            'pageTitle' => 'Receipt Details',
            'receipt' => $receipt,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.receipt_detail', $data, 'student');
    }

    public function announcements(): void {
        $student = $this->getStudent();
        $announcements = $this->db->fetchAll(
            "SELECT * FROM announcements WHERE is_published = 1 ORDER BY created_at DESC"
        );

        $data = [
            'pageTitle' => 'Announcements',
            'announcements' => $announcements,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.announcements', $data, 'student');
    }

    public function announcementDetail(): void {
        $student = $this->getStudent();
        $id = (int)$this->input('id');
        $announcement = $this->db->fetch("SELECT * FROM announcements WHERE id = ? AND is_published = 1", [$id]);

        if (!$announcement) {
            $this->flash('error', 'Announcement not found.');
            $this->redirect('/student/announcements');
            return;
        }

        $data = [
            'pageTitle' => $announcement['title'],
            'announcement' => $announcement,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.announcement_detail', $data, 'student');
    }

    public function announcementsRead(): void {
        if ($this->isPost() && $this->validateCsrf()) {
            $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND type = 'announcement' AND is_read = 0", [$_SESSION['user_id']]);
        }
        $this->redirect('/student/announcements');
    }

    public function announcementsClear(): void {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/student/announcements');
            return;
        }
        $this->db->delete('notifications', "user_id = ? AND type = 'announcement'", [$_SESSION['user_id']]);
        $this->flash('success', 'All announcement notifications cleared.');
        $this->redirect('/student/announcements');
    }

    public function maintenance(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $requests = $this->db->fetchAll(
            "SELECT mr.*, rm.room_name, rm.room_number FROM maintenance_requests mr LEFT JOIN rooms rm ON mr.room_id = rm.id WHERE mr.student_id = ? ORDER BY mr.created_at DESC",
            [$student['id']]
        );

        $data = [
            'pageTitle' => 'Maintenance Requests',
            'requests' => $requests,
            'student' => $student,
            'isTenant' => $this->isTenant((int)$student['id']),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.maintenance', $data, 'student');
    }

    public function maintenanceCreate(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $isTenant = $this->isTenant((int)$student['id']);
        if (!$isTenant) {
            $this->flash('error', 'Only tenants with an active reservation can submit maintenance requests.');
            $this->redirect('/student/maintenance');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/maintenance/create');
                return;
            }

            $title = $this->sanitize($this->input('title', ''));
            $description = $this->sanitize($this->input('description', ''));
            $category = $this->input('category', 'other');
            $priority = $this->input('priority', 'medium');
            $roomId = (int)$this->input('room_id');

            if (empty($title) || empty($description)) {
                $this->flash('error', 'Title and description are required.');
                $this->redirect('/student/maintenance/create');
                return;
            }

            $validCategories = ['plumbing', 'electrical', 'furniture', 'appliance', 'structural', 'other'];
            if (!in_array($category, $validCategories)) $category = 'other';
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($priority, $validPriorities)) $priority = 'medium';

            $code = generateCode('MNT');
            $this->db->insert('maintenance_requests', [
                'request_code' => $code,
                'student_id' => $student['id'],
                'room_id' => $roomId ?: null,
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'priority' => $priority,
            ]);

            $roomLabel = '';
            if ($roomId) {
                $room = $this->db->fetch("SELECT room_number, room_name FROM rooms WHERE id = ?", [$roomId]);
                if ($room) $roomLabel = trim(($room['room_number'] ?? '') . ' ' . ($room['room_name'] ?? ''));
            }

            $this->logActivity('create_maintenance', "Maintenance request {$code} created");
            $this->notifyAdminsNewMaintenanceRequest($student, $code, $title, $description, $category, $priority, $roomLabel);
            $this->flash('success', 'Maintenance request submitted successfully.');
            $this->redirect('/student/maintenance');
            return;
        }

        $activeReservation = $this->db->fetch(
            "SELECT r.*, rm.id as rid, rm.room_name, rm.room_number FROM reservations r JOIN rooms rm ON r.room_id = rm.id WHERE r.student_id = ? AND r.status = 'approved'",
            [$student['id']]
        );

        $data = [
            'pageTitle' => 'Submit Maintenance Request',
            'student' => $student,
            'activeReservation' => $activeReservation,
            'isTenant' => true,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.maintenance_create', $data, 'student');
    }

    public function maintenanceDetail(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }
        $id = (int)$this->input('id');
        $request = $this->db->fetch(
            "SELECT mr.*, rm.room_name, rm.room_number FROM maintenance_requests mr LEFT JOIN rooms rm ON mr.room_id = rm.id WHERE mr.id = ? AND mr.student_id = ?",
            [$id, $student['id']]
        );

        if (!$request) {
            $this->flash('error', 'Maintenance request not found.');
            $this->redirect('/student/maintenance');
            return;
        }

        $data = [
            'pageTitle' => 'Maintenance Request Details',
            'request' => $request,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.maintenance_detail', $data, 'student');
    }

    public function complaints(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $complaints = $this->db->fetchAll(
            "SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC",
            [$student['id']]
        );

        $data = [
            'pageTitle' => 'My Complaints',
            'complaints' => $complaints,
            'student' => $student,
            'isTenant' => $this->isTenant((int)$student['id']),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.complaints', $data, 'student');
    }

    public function complaintsCreate(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $isTenant = $this->isTenant((int)$student['id']);
        if (!$isTenant) {
            $this->flash('error', 'Only tenants with an active reservation can submit complaints.');
            $this->redirect('/student/complaints');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/complaints/create');
                return;
            }

            $subject = $this->sanitize($this->input('subject', ''));
            $description = $this->sanitize($this->input('description', ''));
            $category = $this->input('category', 'other');
            $severity = $this->input('severity', 'medium');

            if (empty($subject) || empty($description)) {
                $this->flash('error', 'Subject and description are required.');
                $this->redirect('/student/complaints/create');
                return;
            }

            $validCategories = ['noise', 'cleanliness', 'security', 'roommate', 'management', 'other'];
            if (!in_array($category, $validCategories)) $category = 'other';
            $validSeverities = ['low', 'medium', 'high'];
            if (!in_array($severity, $validSeverities)) $severity = 'medium';

            $code = generateCode('CMP');
            $this->db->insert('complaints', [
                'complaint_code' => $code,
                'student_id' => $student['id'],
                'subject' => $subject,
                'description' => $description,
                'category' => $category,
                'severity' => $severity,
            ]);

            $this->logActivity('create_complaint', "Complaint {$code} created");
            $this->notifyAdminsNewComplaint($student, $code, $subject, $description, $category, $severity);
            $this->flash('success', 'Your complaint submitted successfully!');
            $this->redirect('/student/complaints');
            return;
        }

        $data = [
            'pageTitle' => 'Submit Complaint',
            'student' => $student,
            'isTenant' => true,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.complaints_create', $data, 'student');
    }

    public function complaintDetail(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }
        $id = (int)$this->input('id');
        $complaint = $this->db->fetch(
            "SELECT * FROM complaints WHERE id = ? AND student_id = ?",
            [$id, $student['id']]
        );

        if (!$complaint) {
            $this->flash('error', 'Complaint not found.');
            $this->redirect('/student/complaints');
            return;
        }

        $data = [
            'pageTitle' => 'Complaint Details',
            'complaint' => $complaint,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.complaint_detail', $data, 'student');
    }

    public function refunds(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $refunds = $this->db->fetchAll(
            "SELECT rf.*, r.reservation_code, rm.room_name, rm.room_number
             FROM refund_requests rf
             LEFT JOIN reservations r ON rf.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE rf.student_id = ?
             ORDER BY rf.created_at DESC",
            [$student['id']]
        );

        $data = [
            'pageTitle' => 'My Refund Requests',
            'refunds' => $refunds,
            'student' => $student,
            'isTenant' => $this->isTenant((int)$student['id']),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.refunds', $data, 'student');
    }

    public function refundRequestCreate(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $isTenant = $this->isTenant((int)$student['id']);
        if (!$isTenant) {
            $this->flash('error', 'Only tenants with an active reservation can submit refund requests.');
            $this->redirect('/student/refund-requests');
            return;
        }

        $activeReservation = $this->db->fetch(
            "SELECT r.*, rm.room_name, rm.room_number, rm.monthly_rent
             FROM reservations r JOIN rooms rm ON r.room_id = rm.id
             WHERE r.student_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC LIMIT 1",
            [$student['id']]
        );

        // One month's rent (reference rate)
        $monthlyRent = 0.0;
        if ($activeReservation) {
            $monthlyRent = (float)($activeReservation['rent_amount'] ?? 0);
            if ($monthlyRent <= 0) $monthlyRent = (float)($activeReservation['monthly_rent'] ?? 0);
        }
        if ($monthlyRent <= 0) {
            $monthlyRent = (float)($this->db->fetch(
                "SELECT COALESCE(MAX(amount),0) as amt FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status = 'paid'",
                [$student['id']]
            )['amt'] ?? 0);
        }

        // Amounts actually paid, net of any portion already refunded.
        // Only 'paid'/'refunded' payments are counted so that the refundable
        // total stays consistent with applyRefundDeduction().
        $monthlyPaidNet = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount,0)),0) as total
             FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND status IN ('paid','refunded')",
            [$student['id']]
        )['total'] ?? 0);

        $advancePaidNet = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount,0)),0) as total
             FROM payments WHERE student_id = ? AND payment_type = 'advance_payment' AND status IN ('paid','refunded')",
            [$student['id']]
        )['total'] ?? 0);

        // Amounts already tied up in pending requests.
        $pendingMonthly = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(amount),0) as total FROM refund_requests WHERE student_id = ? AND status = 'pending' AND refund_type = 'monthly'",
            [$student['id']]
        )['total'] ?? 0);
        $pendingAdvance = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(amount),0) as total FROM refund_requests WHERE student_id = ? AND status = 'pending' AND refund_type = 'advance'",
            [$student['id']]
        )['total'] ?? 0);
        $pendingAll = (float)($this->db->fetch(
            "SELECT COALESCE(SUM(amount),0) as total FROM refund_requests WHERE student_id = ? AND status = 'pending' AND refund_type = 'all'",
            [$student['id']]
        )['total'] ?? 0);

        // Maximum refundable per type. Monthly rent refund is only allowed
        // when the tenant has paid more than 2 months; minus 1 month penalty.
        // 'All' requires the same eligibility — it bundles monthly + advance.
        $monthsPaid = $monthlyRent > 0 ? (int)floor($monthlyPaidNet / $monthlyRent) : 0;
        $monthlyAvailable = ($monthsPaid > 2)
            ? round(max(0, $monthlyPaidNet - $monthlyRent - $pendingMonthly - $pendingAll))
            : 0;
        $advanceAvailable = round(max(0, $advancePaidNet - $pendingAdvance - $pendingAll));
        $allAvailable = ($monthsPaid > 2) ? round($monthlyAvailable + $advanceAvailable) : 0;

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/refund-request/create');
                return;
            }

            $refundType = $this->input('refund_type', 'monthly');
            if (!in_array($refundType, ['monthly', 'advance', 'all'])) {
                $this->flash('error', 'Invalid refund type.');
                $this->redirect('/student/refund-request/create');
                return;
            }

            $typeLabels = ['monthly' => 'monthly', 'advance' => 'advance', 'all' => 'all payments'];
            $typeLabel = $typeLabels[$refundType];
            if ($refundType === 'advance') $maxRefund = $advanceAvailable;
            elseif ($refundType === 'all') $maxRefund = $allAvailable;
            else $maxRefund = $monthlyAvailable;

            $amount = $this->input('amount', '');
            $reason = $this->sanitize($this->input('reason', ''));
            $amountVal = is_numeric($amount) ? (float)$amount : -1.0;

            if ($maxRefund <= 0) {
                $this->flash('error', 'You have no eligible ' . $typeLabel . ' refund amount left to request.');
                $this->redirect('/student/refund-request/create');
                return;
            }
            if ($amountVal <= 0 || round($amountVal) != $amountVal) {
                $this->flash('error', 'Please enter a valid whole number refund amount.');
                $this->redirect('/student/refund-request/create');
                return;
            }
            if ($amountVal > $maxRefund) {
                $this->flash('error', 'Refund amount cannot exceed the maximum ' . $typeLabel . ' refundable amount of ' . formatCurrency($maxRefund) . '.');
                $this->redirect('/student/refund-request/create');
                return;
            }
            if (empty($reason)) {
                $this->flash('error', 'Please provide a reason for the refund.');
                $this->redirect('/student/refund-request/create');
                return;
            }

            // GCash number is required: where the refund will be sent.
            $gcashNumber = normalizeMobileNumber($this->input('gcash_number', ''));
            if ($gcashNumber === '' || $gcashNumber === null) {
                $this->flash('error', 'Please enter a valid 11-digit Philippine mobile number starting with 09.');
                $this->redirect('/student/refund-request/create');
                return;
            }

            $existingPending = $this->db->fetch(
                "SELECT id FROM refund_requests WHERE student_id = ? AND status = 'pending' LIMIT 1",
                [$student['id']]
            );
            if ($existingPending) {
                $this->flash('error', 'You already have a pending refund request. Wait for it to be reviewed, or cancel it first.');
                $this->redirect('/student/refund-request/create');
                return;
            }

            $code = generateCode('RFD');
            $this->db->insert('refund_requests', [
                'refund_code' => $code,
                'student_id' => $student['id'],
                'reservation_id' => $activeReservation['id'] ?? null,
                'refund_type' => $refundType,
                'amount' => $amountVal,
                'reason' => $reason,
                'gcash_number' => $gcashNumber,
            ]);

            $this->notifyAdminsRefundRequest($student, $code, $refundType, $amountVal, $reason, $gcashNumber);

            $this->logActivity('create_refund_request', "Refund request {$code} created");
            $this->flash('success', 'Refund request submitted successfully. Admin notified by email. Refund Type: ' . $typeLabel . '.');
            $this->redirect('/student/refund-requests');
            return;
        }

        $data = [
            'pageTitle' => 'Submit Refund Request',
            'student' => $student,
            'activeReservation' => $activeReservation,
            'monthlyRent' => round($monthlyRent, 2),
            'monthlyPaidNet' => round($monthlyPaidNet, 2),
            'monthsPaid' => $monthsPaid,
            'advanceTotal' => round($advancePaidNet, 2),
            'monthlyAvailable' => $monthlyAvailable,
            'advanceAvailable' => $advanceAvailable,
            'allAvailable' => $allAvailable,
            'pendingMonthly' => round($pendingMonthly, 2),
            'pendingAdvance' => round($pendingAdvance, 2),
            'pendingAll' => round($pendingAll, 2),
            'isTenant' => true,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.refund_request_create', $data, 'student');
    }

    public function refundRequestCancel(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/student/refund-requests');
            return;
        }

        $id = (int)$this->input('id');
        $refund = $this->db->fetch(
            "SELECT * FROM refund_requests WHERE id = ? AND student_id = ?",
            [$id, $student['id']]
        );
        if (!$refund || $refund['status'] !== 'pending') {
            $this->flash('error', 'Only pending refund requests can be cancelled.');
            $this->redirect('/student/refund-requests');
            return;
        }

        $this->db->update('refund_requests', ['status' => 'cancelled', 'admin_notes' => 'Cancelled by student.'], "id = ?", [$id]);
        $this->notifyAdminsRefundCancelled($student, $refund);
        $this->logActivity('cancel_refund_request', "Refund request {$refund['refund_code']} cancelled by student");
        $this->flash('success', 'Refund request cancelled. Admin notified by email.');
        $this->redirect('/student/refund-requests');
    }

    public function notifications(): void {
        $student = $this->getStudent();
        $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
            [$_SESSION['user_id']]
        );

        $data = [
            'pageTitle' => 'Notifications',
            'notifications' => $notifications,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.notifications', $data, 'student');
    }

    public function notificationsRead(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/notifications');
                return;
            }

            $notificationId = (int)$this->input('notification_id');
            if ($notificationId) {
                $this->db->update('notifications', ['is_read' => 1], "id = ? AND user_id = ?", [$notificationId, $_SESSION['user_id']]);
            } else {
                $this->db->update('notifications', ['is_read' => 1], "user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
            }
        }
        $this->redirect('/student/notifications');
    }

    public function notificationsClear(): void {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/student/notifications');
            return;
        }
        $this->db->delete('notifications', "user_id = ?", [$_SESSION['user_id']]);
        $this->flash('success', 'All notifications cleared.');
        $this->redirect('/student/notifications');
    }

    public function feedback(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        $feedback = $this->db->fetchAll(
            "SELECT * FROM feedback WHERE student_id = ? ORDER BY created_at DESC",
            [$student['id']]
        );

        $data = [
            'pageTitle' => 'My Feedback',
            'feedback' => $feedback,
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.feedback', $data, 'student');
    }

    public function feedbackCreate(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/feedback/create');
                return;
            }

            $subject = $this->sanitize($this->input('subject', ''));
            $message = $this->sanitize($this->input('message', ''));
            $category = $this->input('category', 'other');
            $rating = (int)$this->input('rating', 5);

            if (empty($subject) || empty($message)) {
                $this->flash('error', 'Subject and message are required.');
                $this->redirect('/student/feedback/create');
                return;
            }

            $validCategories = ['suggestion', 'compliment', 'complaint', 'inquiry', 'other'];
            if (!in_array($category, $validCategories)) $category = 'other';
            if ($rating < 1 || $rating > 5) $rating = 5;

            $this->db->insert('feedback', [
                'student_id' => $student['id'],
                'name' => $student['first_name'] . ' ' . $student['last_name'],
                'email' => $_SESSION['user_email'],
                'subject' => $subject,
                'message' => $message,
                'rating' => $rating,
                'category' => $category,
            ]);

            $this->logActivity('create_feedback', 'Feedback submitted');
            $this->notifyAdminsNewFeedback($student, $subject, $message, $category, $rating, $_SESSION['user_email'] ?? '');
            $this->flash('success', 'Your feedback submitted successfully!');
            $this->redirect('/student/feedback');
            return;
        }

        $data = [
            'pageTitle' => 'Submit Feedback',
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.feedback_create', $data, 'student');
    }

    public function settings(): void {
        $student = $this->getStudent();
        if (!$student) {
            $this->flash('error', 'Student profile not found.');
            $this->redirect('/login');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/settings');
                return;
            }

            $action = $this->input('action', '');

            if ($action === 'update_email') {
                $newEmail = strtolower(trim($this->input('email', '')));
                if (!isValidGmailEmail($newEmail)) {
                    $this->flash('error', gmailEmailError('New email address', $newEmail));
                    $this->redirect('/student/settings');
                    return;
                }
                $existing = $this->db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$newEmail, $_SESSION['user_id']]);
                if ($existing) {
                    $this->flash('error', 'Email is already in use.');
                    $this->redirect('/student/settings');
                    return;
                }
                $this->db->update('users', ['email' => $newEmail], "id = ?", [$_SESSION['user_id']]);
                $_SESSION['user_email'] = $newEmail;
                $this->flash('success', 'Email updated successfully.');
            } elseif ($action === 'delete_account') {
                $studentRec = $this->db->fetch(
                    "SELECT s.id AS student_id, s.profile_picture, s.valid_id_path, s.school_id_path, u.email
                     FROM students s JOIN users u ON u.id = s.user_id
                     WHERE s.user_id = ?",
                    [$_SESSION['user_id']]
                );
                if ($studentRec) {
                    $this->deleteStudentAccount(
                        (int)$studentRec['student_id'],
                        (int)$_SESSION['user_id'],
                        (string)$studentRec['email'],
                        array_filter([$studentRec['profile_picture'], $studentRec['valid_id_path'], $studentRec['school_id_path']])
                    );
                } else {
                    $this->db->delete('users', "id = ?", [$_SESSION['user_id']]);
                }
                session_destroy();
                $this->flash('success', 'Account deleted.');
                $this->redirect('/');
                return;
            }

            $this->redirect('/student/settings');
            return;
        }

        $data = [
            'pageTitle' => 'Settings',
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.settings', $data, 'student');
    }

    public function changePassword(): void {
        $student = $this->getStudent();
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token.');
                $this->redirect('/student/change-password');
                return;
            }

            $currentPassword = $this->input('current_password', '');
            $newPassword = $this->input('new_password', '');
            $confirmPassword = $this->input('password_confirmation', '');

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->flash('error', 'All fields are required.');
                $this->redirect('/student/change-password');
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
                $this->redirect('/student/change-password');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->flash('error', 'Passwords do not match.');
                $this->redirect('/student/change-password');
                return;
            }

            $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $this->flash('error', 'Current password is incorrect.');
                $this->redirect('/student/change-password');
                return;
            }

            if ($this->isPasswordReused((int)$_SESSION['user_id'], $newPassword)) {
                $this->flash('error', 'You cannot reuse a previous password. Please choose a new one.');
                $this->redirect('/student/change-password');
                return;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $pending = [
                'user_id'       => (int)$_SESSION['user_id'],
                'email'         => (string)$_SESSION['user_email'],
                'password_hash' => $hashedPassword,
                'mode'          => 'settings',
                'context'       => 'student',
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

        $data = [
            'pageTitle' => 'Change Password',
            'student' => $student,
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('student.change_password', $data, 'student');
    }

    public function markModuleReadApi(): void {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
        $module = $this->input('module', '');
        $validModules = ['payments','announcements','maintenance','complaints','feedback','notifications'];
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
        $student = $this->db->fetch("SELECT id FROM students WHERE user_id = ?", [$userId]);
        $sid = $student['id'] ?? 0;
        $role = $_SESSION['user_role'] ?? '';
        $sidebar = $this->getSidebarCountsForRole($role);
        $stats = [
            'sidebar' => $sidebar,
            'pendingPayments' => $sid ? $this->db->count('payments', "student_id = ? AND status = 'pending'", [$sid]) : 0,
            'totalPaid' => (float)($sid ? ($this->db->fetch("SELECT COALESCE(SUM(amount_paid - COALESCE(refunded_amount,0)),0) as t FROM payments WHERE student_id = ? AND status IN ('paid','partially_paid','refunded') AND amount_paid > 0", [$sid])['t'] ?? 0) : 0),
            'openMaintenance' => $sid ? $this->db->count('maintenance_requests', "student_id = ? AND status IN ('pending','in_progress')", [$sid]) : 0,
            'activeReservation' => $sid ? $this->db->count('reservations', "student_id = ? AND status = 'approved'", [$sid]) : 0,
        ];
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }
}
