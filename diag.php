<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Database.php';

$db = Database::getInstance();

echo "--- RECENT OTP CODES ---\n";
$otps = $db->fetchAll("SELECT * FROM email_verification_codes ORDER BY id DESC LIMIT 5");
print_r($otps);

echo "\n--- RECENT ACTIVITY LOGS ---\n";
$logs = $db->fetchAll("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 5");
print_r($logs);

echo "\n--- RECENT USERS ---\n";
$users = $db->fetchAll("SELECT id, email, username, role, status, email_verified, login_attempts, locked_until FROM users ORDER BY id DESC LIMIT 5");
print_r($users);
