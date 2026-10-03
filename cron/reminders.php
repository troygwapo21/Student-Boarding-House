<?php
/**
 * Cron endpoint for real-time monthly billing and payment due-date reminders.
 *
 * Runs the fully automatic MonthlyBillingService (due-date generation,
 * payment status transitions, late penalties, notifications) plus reminders
 * for non-monthly payments.
 *
 * Usage (Windows Task Scheduler or Linux cron):
 *   curl http://localhost/student_boarding_house/cron/reminders.php
 *
 * Or add to crontab:
 *   0 * * * * curl -s http://localhost/student_boarding_house/cron/reminders.php > /dev/null 2>&1
 */

define('BASE_PATH', __DIR__ . '/..');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Database.php';
require_once BASE_PATH . '/app/Helpers/helpers.php';
require_once BASE_PATH . '/app/Services/PaymentReminderService.php';
require_once BASE_PATH . '/app/Services/MonthlyBillingService.php';

header('Content-Type: application/json');

try {
    $monthly = new MonthlyBillingService();
    $monthly->run();

    $reminder = new PaymentReminderService();
    $reminder->run();
    echo json_encode(['status' => 'ok', 'message' => 'Monthly billing and payment reminders processed successfully.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
