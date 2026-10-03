<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Database.php';

header('Content-Type: text/plain');

$email = 'tvillaruel39@gmail.com';
$password = '8 Ball Pool';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = Database::getInstance();

    // Check if user exists
    $existing = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);

    // Check if email_verified column exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'email_verified'");
    $hasEmailVerifiedCol = !empty($cols);

    if ($existing) {
        if ($hasEmailVerifiedCol) {
            $db->query(
                "UPDATE users SET password = ?, role = 'super_admin', status = 'active', email_verified = 1, email_verified_at = NOW(), login_attempts = 0, locked_until = NULL WHERE email = ?",
                [$hash, $email]
            );
        } else {
            $db->query(
                "UPDATE users SET password = ?, role = 'super_admin', status = 'active', email_verified_at = NOW(), login_attempts = 0, locked_until = NULL WHERE email = ?",
                [$hash, $email]
            );
        }
        echo "SUCCESS: Existing user updated to super_admin.\n";
    } else {
        if ($hasEmailVerifiedCol) {
            $db->query(
                "INSERT INTO users (email, username, password, role, status, email_verified, email_verified_at, login_attempts, created_at, updated_at) 
                 VALUES (?, 'tvillaruel39', ?, 'super_admin', 'active', 1, NOW(), 0, NOW(), NOW())",
                [$email, $hash]
            );
        } else {
            $db->query(
                "INSERT INTO users (email, username, password, role, status, email_verified_at, login_attempts, created_at, updated_at) 
                 VALUES (?, 'tvillaruel39', ?, 'super_admin', 'active', NOW(), 0, NOW(), NOW())",
                [$email, $hash]
            );
        }
        echo "SUCCESS: Super admin user created.\n";
    }

    $user = $db->fetch("SELECT id, email, username, role, status FROM users WHERE email = ?", [$email]);
    print_r($user);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
