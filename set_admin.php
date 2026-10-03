<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Database.php';

try {
    $db = Database::getInstance();
    $db->query("UPDATE users SET role = 'super_admin', email_verified = 1 WHERE id = 28");
    echo "SUCCESS: User 28 is now super_admin!";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
