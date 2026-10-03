<?php
/**
 * Monthly Billing Service (Real-Time, Asia/Manila)
 *
 * Fully automatic rent billing for approved tenants. Every billing cycle is
 * anchored to the tenant's MOVE-IN DATE and RENTAL DURATION — never to the
 * current date, the approval date, or the payment submission date.
 *
 *  - Move-in date & time and the rental duration are stored on the reservation
 *    at approval/registration; the monthly rent is frozen on the reservation.
 *  - Cycle 0 starts on the move-in date. Each subsequent cycle starts at
 *    move-in date + (cycle index × duration) calendar months. The due date of
 *    every cycle bill equals that cycle's start date.
 *        Duration 1 → due on the move-in day each month
 *        Duration 2 → due every 2 months on the move-in day
 *        Duration 3 → due every 3 months on the move-in day
 *  - Each bill covers one cycle: amount = monthly rent × duration (+ tax).
 *  - The current cycle's bill is auto-generated as soon as its cycle start
 *    arrives (and again if it was deleted). Existing bills are repaired to the
 *    correct due date / amount while unpaid.
 *  - Payment status is updated in real time against the Philippine date:
 *      pending      → before the 3-day window
 *      upcoming     → 3 days before the due date
 *      due_today    → on the exact due date
 *      overdue      → starting 12:00 AM on the day after the due date
 *      paid         → after successful payment
 *  - A late payment penalty is applied immediately when a bill becomes
 *    overdue (no grace period) and is recorded on the billing statement.
 *  - Notifications are sent to the Tenant, Manager, and Administrator:
 *      3 days before the due date, on the due date, and daily while overdue.
 */

require_once __DIR__ . '/../Database.php';

class MonthlyBillingService {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Process billing for all active tenants.
     */
    public function run(): void {
        $today = serverDate('Y-m-d');

        $tenants = $this->db->fetchAll(
            "SELECT s.id AS student_id, s.first_name, s.last_name, s.user_id,
                    r.id AS reservation_id, r.room_id, r.moved_in_at, r.move_in_date,
                    r.move_in_time, r.expected_duration, r.rent_amount,
                    rm.monthly_rent, rm.room_number, rm.room_name
             FROM reservations r
             JOIN students s ON r.student_id = s.id
             JOIN rooms rm ON r.room_id = rm.id
             JOIN users u ON s.user_id = u.id
             WHERE r.status = 'approved' AND u.status = 'active'"
        );

        foreach ($tenants as $tenant) {
            $this->processTenant($tenant, $today);
        }
    }

    /**
     * Ensure the tenant's current billing-cycle bill exists with the correct
     * due date and amount, then update its payment status.
     */
    private function processTenant(array $tenant, string $today): void {
        $anchor = $this->getAnchorDate($tenant);
        if (!$anchor) return;

        $duration = $this->getDuration($tenant);
        if ($today < $anchor) {
            return; // move-in date has not arrived yet — no billing before then
        }

        $cycleStart = $this->currentCycleStart($anchor, $duration, $today);
        $period = substr($cycleStart, 0, 7);
        $expectedAmount = $this->computeAmount($this->getMonthlyRent($tenant), $duration);

        // Terminal records (refunded from a previous stay, cancelled bills)
        // must never block or stand in for the current cycle's bill.
        $bill = $this->db->fetch(
            "SELECT * FROM payments WHERE student_id = ? AND payment_type = 'monthly_rent' AND billing_period = ? AND status NOT IN ('refunded', 'cancelled') ORDER BY id DESC LIMIT 1",
            [$tenant['student_id'], $period]
        );

        if (!$bill) {
            $billId = $this->createCycleBill($tenant, $cycleStart, $period, $expectedAmount);
            if (!$billId) return;
            $bill = $this->db->fetch("SELECT * FROM payments WHERE id = ?", [$billId]);
            if (!$bill) return;
        } else {
            $bill = $this->repairBill($bill, $cycleStart, $expectedAmount);
        }

        $this->processBillStatus($tenant, $bill, $today);
    }

    /**
     * Fix an existing bill's due date/amount when they drift from the
     * move-in-anchored cycle (paid/cancelled bills are left untouched).
     */
    private function repairBill(array $bill, string $cycleStart, float $expectedAmount): array {
        if (in_array($bill['status'], ['paid', 'cancelled', 'refunded'], true)) {
            return $bill;
        }
        $updates = [];
        if ((string)$bill['due_date'] !== $cycleStart) {
            $updates['due_date'] = $cycleStart;
        }
        if (abs((float)$bill['amount'] - $expectedAmount) > 0.001) {
            $updates['amount'] = $expectedAmount;
        }
        if ($updates) {
            $this->db->update('payments', $updates, "id = ?", [(int)$bill['id']]);
        }
        return array_merge($bill, $updates);
    }

    /**
     * Update a single bill's payment status against the Philippine date.
     * The late penalty is continuously reconciled with the current
     * Billing/Financial system settings (late_fee + grace_period_days):
     * applied/updated when a bill becomes overdue, removed when it is not.
     */
    private function processBillStatus(array $tenant, array $bill, string $today): void {
        $totalDue = (float)$bill['amount'] + (float)$bill['late_fee'];
        $amountPaid = (float)$bill['amount_paid'];
        $dueDate = (string)$bill['due_date'];
        $currentStatus = $bill['status'];

        if ($currentStatus === 'paid') {
            return; // already settled (verified by staff, or a legacy record)
        }

        $isVerified = !empty($bill['verified_by']) || !empty($bill['verified_at']);
        $fullyCovered = $amountPaid >= $totalDue && $totalDue > 0;

        if ($fullyCovered && $isVerified) {
            $this->markPaid($tenant, $bill, $currentStatus, $totalDue);
            return;
        }

        // Full payment submitted but staff have not verified it yet: hold the
        // bill in its submitted state — no reminders or penalties until then.
        if ($fullyCovered) {
            return;
        }

        // Grace period: a bill only becomes overdue (and gets penalized)
        // after due_date + grace_period_days has fully passed.
        $graceDays = max(0, (int)$this->getSetting('grace_period_days', 0));
        $overdueOn = (new DateTime($dueDate))->modify("+{$graceDays} days")->format('Y-m-d');

        if ($dueDate < $today && $today > $overdueOn) {
            $this->markOverdue($tenant, $bill);
            return;
        }

        // Not overdue: make sure no penalty remains (settings may have changed).
        $this->clearPenalty($bill);

        if ($dueDate > $today) {
            $daysUntil = $this->daysBetween($today, $dueDate);
            if ($daysUntil <= 3 && $currentStatus !== 'upcoming' && $currentStatus !== 'due_today') {
                $this->db->update('payments', ['status' => 'upcoming'], "id = ?", [$bill['id']]);
                $this->notifyUpcoming($tenant, $bill, $daysUntil);
            }
            return;
        }

        if ($dueDate === $today && $currentStatus !== 'due_today') {
            $this->db->update('payments', ['status' => 'due_today'], "id = ?", [$bill['id']]);
            $this->notifyDueToday($tenant, $bill);
            return;
        }

        // Inside the grace window: not overdue yet.
        if ($currentStatus === 'overdue') {
            $this->db->update('payments', ['status' => 'pending'], "id = ?", [$bill['id']]);
        }
    }

    /**
     * Remove an auto-applied late penalty from an unpaid bill that is no
     * longer overdue (e.g. grace_period_days was increased or the due date
     * moved). Paid/cancelled bills are historical records and stay untouched.
     */
    private function clearPenalty(array $bill): void {
        if ((int)$bill['penalty_applied'] === 0 && (float)$bill['late_fee'] <= 0.001) {
            return;
        }
        $this->db->update(
            'payments',
            ['late_fee' => 0, 'penalty_applied' => 0],
            "id = ? AND status NOT IN ('paid', 'cancelled', 'refunded')",
            [(int)$bill['id']]
        );
    }

    /**
     * Mark a fully paid bill as paid and notify.
     */
    private function markPaid(array $tenant, array $bill, string $currentStatus, float $totalDue): void {
        if ($currentStatus === 'paid') return;

        $this->db->update('payments', [
            'status' => 'paid',
            'paid_at' => serverDateTime(),
        ], "id = ?", [$bill['id']]);

        $houseName = $tenant['room_name'] . ' - ' . $tenant['room_number'];
        $this->notify(
            (int)$tenant['user_id'],
            (int)$bill['id'],
            'Payment Received',
            "Payment received successfully. Thank you for paying your rent of " . formatCurrency($totalDue) . ".",
            "Tenant {$tenant['first_name']} {$tenant['last_name']} has successfully completed the payment of " . formatCurrency($totalDue) . ".",
            "Payment completed by {$tenant['first_name']} {$tenant['last_name']} ({$houseName})."
        );
        $this->logActivity('payment_paid', "Rent paid by {$tenant['first_name']} {$tenant['last_name']} — {$bill['billing_period']}");
    }

    /**
     * Mark a past-due bill as overdue and reconcile its late penalty with the
     * current late_fee setting: the stored fee always equals the configured
     * value (updated in place when the setting changes, never double-charged),
     * then send a daily overdue notification.
     */
    private function markOverdue(array $tenant, array $bill): void {
        $lateFee = round((float)$this->getSetting('late_fee', 100.00), 2);
        $currentFee = round((float)$bill['late_fee'], 2);
        $feeChanged = abs($currentFee - $lateFee) > 0.001;

        // Idempotent reconcile: safe under concurrent runs, can never double-charge.
        $updates = [
            'status'          => 'overdue',
            'penalty_applied' => 1,
        ];
        if ($feeChanged) {
            $updates['late_fee'] = $lateFee;
        }
        $this->db->update('payments', $updates, "id = ? AND status NOT IN ('paid', 'cancelled', 'refunded')", [(int)$bill['id']]);

        if ($this->notifiedToday((int)$tenant['user_id'], (int)$bill['id'], 'Payment Overdue')) {
            return;
        }

        $daysPastDue = $this->daysBetween((string)$bill['due_date'], serverDate());
        $houseName = $tenant['room_name'] . ' - ' . $tenant['room_number'];
        $appliedFee = $feeChanged ? $lateFee : $currentFee;
        $totalDue = (float)$bill['amount'] + $appliedFee;

        if ($feeChanged) {
            $wasApplied = (int)$bill['penalty_applied'] === 1;
            if ($lateFee > 0) {
                $penaltyNote = $wasApplied
                    ? " Your late payment penalty has been updated to " . formatCurrency($lateFee) . ". Your updated balance is " . formatCurrency($totalDue) . "."
                    : " A " . formatCurrency($lateFee) . " late payment penalty has been applied. Your updated balance is " . formatCurrency($totalDue) . ".";
            } else {
                $penaltyNote = " The late payment penalty has been removed per updated billing rules. Your updated balance is " . formatCurrency($totalDue) . ".";
            }
        } else {
            $penaltyNote = $appliedFee > 0
                ? " A " . formatCurrency($appliedFee) . " late payment penalty has been applied. Your updated balance is " . formatCurrency($totalDue) . "."
                : "";
        }

        $this->notify(
            (int)$tenant['user_id'],
            (int)$bill['id'],
            'Payment Overdue',
            "Your rent of " . formatCurrency($totalDue) . " is {$daysPastDue} day" . ($daysPastDue > 1 ? 's' : '') . " overdue." . $penaltyNote . " Please pay as soon as possible.",
            "Tenant {$tenant['first_name']} {$tenant['last_name']} has an overdue payment of " . formatCurrency($totalDue) . "." . ($appliedFee > 0 ? " Late penalty: " . formatCurrency($appliedFee) . "." : ""),
            "Overdue payment detected for {$tenant['first_name']} {$tenant['last_name']} at {$houseName}." . ($appliedFee > 0 ? " Updated balance: " . formatCurrency($totalDue) . "." : "")
        );
        $this->logActivity('payment_overdue', "Payment overdue for {$tenant['first_name']} {$tenant['last_name']} — {$bill['billing_period']}" . ($appliedFee > 0 ? " — " . formatCurrency($appliedFee) . " penalty applied" : ""));
    }

    /**
     * Create a bill for a billing cycle that starts on $cycleStart.
     * The due date equals the cycle start date (move-in date + k × duration).
     */
    private function createCycleBill(array $tenant, string $cycleStart, string $period, float $amount): ?int {
        $billId = $this->db->insert('payments', [
            'payment_code' => generateCode('PAY'),
            'student_id' => $tenant['student_id'],
            'reservation_id' => $tenant['reservation_id'],
            'payment_type' => 'monthly_rent',
            'amount' => $amount,
            'late_fee' => 0,
            'amount_paid' => 0,
            'billing_period' => $period,
            'penalty_applied' => 0,
            'payment_method' => null,
            'status' => 'pending',
            'due_date' => $cycleStart,
        ]);

        $houseName = $tenant['room_name'] . ' - ' . $tenant['room_number'];
        $this->notify(
            (int)$tenant['user_id'],
            $billId,
            'New Bill Generated',
            "A new bill of " . formatCurrency($amount) . " for cycle {$period} has been generated. Due on " . formatDate($cycleStart) . ".",
            "New bill of " . formatCurrency($amount) . " generated for Tenant {$tenant['first_name']} {$tenant['last_name']} ({$houseName}). Due on " . formatDate($cycleStart) . ".",
            "New bill of " . formatCurrency($amount) . " generated for {$tenant['first_name']} {$tenant['last_name']} at {$houseName}. Due on " . formatDate($cycleStart) . ".",
            $billId
        );
        $this->logActivity('generate_monthly_bill', "Bill generated for {$tenant['first_name']} {$tenant['last_name']} — {$period} — " . formatCurrency($amount));

        return $billId;
    }

    /**
     * The tenant's move-in date (the anchor for every billing cycle).
     */
    private function getAnchorDate(array $tenant): ?string {
        if (!empty($tenant['move_in_date'])) {
            return $tenant['move_in_date'];
        }
        if (!empty($tenant['moved_in_at'])) {
            return substr((string)$tenant['moved_in_at'], 0, 10);
        }
        return null;
    }

    /**
     * Rental duration in months (clamped to 1-12).
     */
    private function getDuration(array $tenant): int {
        $duration = (int)($tenant['expected_duration'] ?? 1);
        if ($duration < 1) $duration = 1;
        if ($duration > 12) $duration = 12;
        return $duration;
    }

    /**
     * Monthly rent from the reservation (frozen at move-in) or the room.
     */
    private function getMonthlyRent(array $tenant): float {
        $rent = (float)($tenant['rent_amount'] ?? 0);
        if ($rent <= 0) $rent = (float)($tenant['monthly_rent'] ?? 0);
        return max(0, $rent);
    }

    /**
     * Cycle amount = monthly rent × duration, plus tax when configured.
     */
    private function computeAmount(float $monthlyRent, int $duration): float {
        $total = $monthlyRent * $duration;
        $taxRate = (float)$this->getSetting('tax_rate', 0);
        if ($taxRate > 0) {
            $total = round($total + ($total * $taxRate / 100), 2);
        }
        return round($total, 2);
    }

    /**
     * Start date of the billing cycle that is current for $today, based on the
     * move-in date and the rental duration: move-in + (k × duration) months.
     */
    public function currentCycleStart(string $moveInDate, int $duration, string $today): string {
        $a = new DateTime($moveInDate, new DateTimeZone('Asia/Manila'));
        $t = new DateTime($today, new DateTimeZone('Asia/Manila'));

        $ay = (int)$a->format('Y'); $am = (int)$a->format('n'); $ad = (int)$a->format('j');
        $ty = (int)$t->format('Y'); $tm = (int)$t->format('n'); $td = (int)$t->format('j');

        $months = ($ty - $ay) * 12 + ($tm - $am);
        if ($td < $ad) $months--; // the move-in day has not arrived yet this month

        if ($months < 0) $months = 0;
        $cycleIndex = intdiv($months, $duration);

        return $this->addMonths($moveInDate, $cycleIndex * $duration);
    }

    /**
     * Add calendar months to a date, clamping to the last day of the month
     * (e.g. Jan 31 + 1 month = Feb 28/29).
     */
    public function addMonths(string $date, int $months): string {
        $dt = new DateTime($date, new DateTimeZone('Asia/Manila'));
        $y = (int)$dt->format('Y');
        $m = (int)$dt->format('n');
        $d = (int)$dt->format('j');

        $total = $y * 12 + ($m - 1) + $months;
        $ny = intdiv($total, 12);
        $nm = $total % 12 + 1;

        $daysInMonth = (int)(new DateTime(sprintf('%04d-%02d-01', $ny, $nm), new DateTimeZone('Asia/Manila')))->format('t');
        $nd = min($d, $daysInMonth);

        return sprintf('%04d-%02d-%02d', $ny, $nm, $nd);
    }

    /**
     * Days from $start to $end (positive when $end is after $start).
     */
    private function daysBetween(string $start, string $end): int {
        $startDt = new DateTime($start, new DateTimeZone('Asia/Manila'));
        $endDt = new DateTime($end, new DateTimeZone('Asia/Manila'));
        return (int)$endDt->diff($startDt)->days;
    }

    private function getSetting(string $key, $default) {
        $row = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Whether a same-titled payment notification was already sent today.
     */
    private function notifiedToday(int $userId, int $paymentId, string $title): bool {
        return (bool)$this->db->fetch(
            "SELECT id FROM notifications WHERE user_id = ? AND reference_id = ? AND reference_type = 'payment' AND title = ? AND DATE(created_at) = CURDATE()",
            [$userId, $paymentId, $title]
        );
    }

    /**
     * Notify the tenant, all managers, and all admins.
     */
    private function notify(int $studentUserId, int $paymentId, string $title, string $tenantMessage, string $managerMessage, string $adminMessage): void {
        $payload = [
            'type' => 'payment',
            'reference_id' => $paymentId,
            'reference_type' => 'payment',
        ];
        $this->db->insert('notifications', array_merge($payload, [
            'user_id' => $studentUserId,
            'title' => $title,
            'message' => $tenantMessage,
        ]));
        $managers = $this->db->fetchAll("SELECT user_id FROM managers");
        foreach ($managers as $m) {
            $this->db->insert('notifications', array_merge($payload, [
                'user_id' => (int)$m['user_id'],
                'title' => $title,
                'message' => $managerMessage,
            ]));
        }
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role = 'super_admin'");
        foreach ($admins as $a) {
            $this->db->insert('notifications', array_merge($payload, [
                'user_id' => (int)$a['id'],
                'title' => $title,
                'message' => $adminMessage,
            ]));
        }
    }

    private function notifyUpcoming(array $tenant, array $bill, int $daysUntil): void {
        $totalDue = (float)$bill['amount'] + (float)$bill['late_fee'];
        $lateFee = $this->getSetting('late_fee', 100.00);
        $houseName = $tenant['room_name'] . ' - ' . $tenant['room_number'];
        $this->notify(
            (int)$tenant['user_id'],
            (int)$bill['id'],
            'Upcoming Payment Reminder',
            "Your rent of " . formatCurrency($totalDue) . " is due in {$daysUntil} day" . ($daysUntil > 1 ? 's' : '') . " on " . formatDate($bill['due_date']) . ". Please pay on or before the due date to avoid a " . formatCurrency((float)$lateFee) . " late payment penalty.",
            "Upcoming Payment: Tenant {$tenant['first_name']} {$tenant['last_name']} has a payment of " . formatCurrency($totalDue) . " due in {$daysUntil} day" . ($daysUntil > 1 ? 's' : '') . ".",
            "Upcoming Payment: Tenant {$tenant['first_name']} {$tenant['last_name']} at {$houseName} has a payment of " . formatCurrency($totalDue) . " due in {$daysUntil} day" . ($daysUntil > 1 ? 's' : '') . ".",
            (int)$bill['id']
        );
        $this->logActivity('payment_reminder', "3-day reminder for {$tenant['first_name']} {$tenant['last_name']} — {$bill['billing_period']}");
    }

    private function notifyDueToday(array $tenant, array $bill): void {
        $totalDue = (float)$bill['amount'] + (float)$bill['late_fee'];
        $lateFee = $this->getSetting('late_fee', 100.00);
        $houseName = $tenant['room_name'] . ' - ' . $tenant['room_number'];
        $this->notify(
            (int)$tenant['user_id'],
            (int)$bill['id'],
            'Payment Due Today',
            "Your rent of " . formatCurrency($totalDue) . " is due today. Please complete your payment before the end of the day to avoid a " . formatCurrency((float)$lateFee) . " late payment penalty.",
            "Tenant {$tenant['first_name']} {$tenant['last_name']} has a payment of " . formatCurrency($totalDue) . " due today.",
            "Tenant {$tenant['first_name']} {$tenant['last_name']} at {$houseName} has a payment of " . formatCurrency($totalDue) . " due today.",
            (int)$bill['id']
        );
        $this->logActivity('payment_due_today', "Payment due today for {$tenant['first_name']} {$tenant['last_name']} — {$bill['billing_period']}");
    }

    private function logActivity(string $action, string $description = ''): void {
        $this->db->insert('activity_logs', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    }
}
