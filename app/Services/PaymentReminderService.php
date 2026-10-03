<?php
/**
 * Payment Reminder Service
 *
 * Handles reminders for NON-monthly_rent payments only (reservation_fee, advance_payment, other).
 * Monthly rent billing/overdue/reminders are handled by MonthlyBillingService.
 */

class PaymentReminderService {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Run all payment reminder checks for non-monthly_rent payments.
     */
    public function run(): void {
        $this->revertNotOverduePenalties();
        $this->sendUpcomingReminders();
        $this->sendDueTodayReminders();
        $this->sendOverdueReminders();
    }

    /**
     * Remove auto-applied late penalties from unpaid non-monthly payments that
     * are no longer overdue (e.g. grace_period_days was increased in System
     * Settings or the due date moved). Keeps penalties in sync with the
     * current Billing/Financial settings.
     */
    private function revertNotOverduePenalties(): void {
        $graceDays = max(0, (int)$this->getSetting('grace_period_days', 0));
        $cutoff = serverNow()->modify("-{$graceDays} days")->format('Y-m-d');
        $this->db->update(
            'payments',
            [
                'late_fee'        => 0,
                'penalty_applied' => 0,
                'status'          => 'pending',
            ],
            "payment_type != 'monthly_rent' AND penalty_applied = 1 AND status NOT IN ('paid', 'cancelled', 'refunded') AND due_date >= ?",
            [$cutoff]
        );
    }

    private function getNonMonthlyPayments(string $whereExtra, array $paramsExtra): array {
        return $this->db->fetchAll(
            "SELECT p.*, s.user_id, s.first_name, rm.room_number
             FROM payments p
             JOIN students s ON p.student_id = s.id
             LEFT JOIN reservations r ON p.reservation_id = r.id
             LEFT JOIN rooms rm ON r.room_id = rm.id
             WHERE p.payment_type != 'monthly_rent'
               AND p.status NOT IN ('paid', 'cancelled')
               AND p.due_date IS NOT NULL
               {$whereExtra}",
            $paramsExtra
        );
    }

    private function hasNotification(int $userId, int $paymentId, string $title): bool {
        $existing = $this->db->fetch(
            "SELECT id FROM notifications WHERE user_id = ? AND reference_id = ? AND reference_type = 'payment' AND title = ? AND DATE(created_at) = CURDATE()",
            [$userId, $paymentId, $title]
        );
        return (bool)$existing;
    }

    private function getSetting(string $key, $default) {
        $row = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
        return $row ? $row['setting_value'] : $default;
    }

    private function sendUpcomingReminders(): void {
        $now = serverNow();
        $today = $now->format('Y-m-d');
        $threeDaysLater = (clone $now)->modify('+3 days')->format('Y-m-d');
        $payments = $this->getNonMonthlyPayments(
            "AND p.due_date BETWEEN ? AND ?",
            [$today, $threeDaysLater]
        );

        foreach ($payments as $payment) {
            if ($this->hasNotification($payment['user_id'], $payment['id'], 'Payment Due Soon')) continue;

            $typeLabel = ucwords(str_replace('_', ' ', $payment['payment_type']));
            $roomLabel = !empty($payment['room_number']) ? " for Room {$payment['room_number']}" : '';
            $daysUntil = (int)((new DateTime($payment['due_date'], new DateTimeZone('Asia/Manila')))->diff($now)->days);

            $this->db->insert('notifications', [
                'user_id' => $payment['user_id'],
                'title' => 'Payment Due Soon',
                'message' => "Your {$typeLabel}{$roomLabel} of " . formatCurrency((float)$payment['amount']) .
                    " is due in {$daysUntil} day(s) on " . formatDate($payment['due_date']) . ". Please pay on time to avoid late fees.",
                'type' => 'payment',
                'reference_id' => $payment['id'],
                'reference_type' => 'payment',
            ]);
        }
    }

    private function sendDueTodayReminders(): void {
        $today = serverDate();
        $payments = $this->getNonMonthlyPayments(
            "AND p.due_date = ?",
            [$today]
        );

        foreach ($payments as $payment) {
            if ($this->hasNotification($payment['user_id'], $payment['id'], 'Payment Due Today')) continue;

            $typeLabel = ucwords(str_replace('_', ' ', $payment['payment_type']));
            $roomLabel = !empty($payment['room_number']) ? " for Room {$payment['room_number']}" : '';

            $this->db->insert('notifications', [
                'user_id' => $payment['user_id'],
                'title' => 'Payment Due Today',
                'message' => "Your {$typeLabel}{$roomLabel} of " . formatCurrency((float)$payment['amount']) .
                    " is due today. Please submit your payment as soon as possible.",
                'type' => 'payment',
                'reference_id' => $payment['id'],
                'reference_type' => 'payment',
            ]);
        }
    }

    private function sendOverdueReminders(): void {
        $now = serverNow();
        // Respect the grace period: payments only become overdue (and get
        // penalized) after due_date + grace_period_days has fully passed.
        $graceDays = max(0, (int)$this->getSetting('grace_period_days', 0));
        $cutoff = (clone $now)->modify("-{$graceDays} days")->format('Y-m-d');
        $payments = $this->getNonMonthlyPayments(
            "AND p.due_date < ?",
            [$cutoff]
        );

        foreach ($payments as $payment) {
            // Reconcile the late penalty with the current late_fee setting:
            // the stored fee always equals the configured value (updated in
            // place when the setting changes, never double-charged),
            // consistent with MonthlyBillingService for monthly rent bills.
            // This runs BEFORE the daily-notification guard so Billing/Financial
            // setting changes apply to existing bills immediately.
            $lateFee = round((float)$this->getSetting('late_fee', 100.00), 2);
            $currentFee = round((float)$payment['late_fee'], 2);
            $feeChanged = abs($currentFee - $lateFee) > 0.001;

            $update = [
                'status'          => 'overdue',
                'penalty_applied' => 1,
            ];
            if ($feeChanged) {
                $update['late_fee'] = $lateFee;
            }
            $this->db->update('payments', $update, "id = ? AND status NOT IN ('paid', 'cancelled', 'refunded')", [$payment['id']]);

            if ($this->hasNotification($payment['user_id'], $payment['id'], 'Payment Overdue')) continue;

            $typeLabel = ucwords(str_replace('_', ' ', $payment['payment_type']));
            $roomLabel = !empty($payment['room_number']) ? " for Room {$payment['room_number']}" : '';
            $daysOverdue = (int)($now->diff(new DateTime($payment['due_date'], new DateTimeZone('Asia/Manila')))->days);

            $penaltyApplied = $feeChanged || (int)$payment['penalty_applied'] === 0;
            $appliedFee = $feeChanged ? $lateFee : $currentFee;
            $totalDue = (float)$payment['amount'] + $appliedFee;

            if ($feeChanged) {
                $wasApplied = (int)$payment['penalty_applied'] === 1;
                if ($lateFee > 0) {
                    $penaltyNote = $wasApplied
                        ? " Your late payment penalty has been updated to " . formatCurrency($lateFee) . ". Your updated balance is " . formatCurrency($totalDue) . "."
                        : " A " . formatCurrency($lateFee) . " late payment penalty has been applied. Your updated balance is " . formatCurrency($totalDue) . ".";
                } else {
                    $penaltyNote = " The late payment penalty has been removed per updated billing rules.";
                }
            } else {
                $penaltyNote = $penaltyApplied && $appliedFee > 0
                    ? " A " . formatCurrency($appliedFee) . " late payment penalty has been applied. Your updated balance is " . formatCurrency($totalDue) . "."
                    : "";
            }

            $this->db->insert('notifications', [
                'user_id' => $payment['user_id'],
                'title' => 'Payment Overdue',
                'message' => "Your {$typeLabel}{$roomLabel} of " . formatCurrency($totalDue) .
                    " was due on " . formatDate($payment['due_date']) .
                    " and is now {$daysOverdue} day" . ($daysOverdue > 1 ? 's' : '') . " overdue." . $penaltyNote . " Please pay immediately.",
                'type' => 'payment',
                'reference_id' => $payment['id'],
                'reference_type' => 'payment',
            ]);
        }
    }
}
