<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Database.php';

$db = Database::getInstance();

echo "<pre>Current users:\n";
$users = $db->fetchAll("SELECT id, email, role, status FROM users ORDER BY id");
foreach ($users as $u) {
    printf("  ID:%d | %s | %s | %s\n", $u['id'], $u['email'], $u['role'], $u['status']);
}

$password = password_hash('password123', PASSWORD_DEFAULT);

$db->query("UPDATE users SET email = 'alondecdadorm@gmail.com', password = ?, role = 'super_admin', status = 'active', locked_until = NULL WHERE id = 1", [$password]);
$db->query("UPDATE users SET email = 'manager1234@gmail.com', password = ?, role = 'manager', status = 'active', locked_until = NULL WHERE id = 2", [$password]);

echo "\n\nAfter update:\n";
$users = $db->fetchAll("SELECT id, email, role, status FROM users ORDER BY id");
foreach ($users as $u) {
    printf("  ID:%d | %s | %s | %s\n", $u['id'], $u['email'], $u['role'], $u['status']);
}

$test = $db->fetch("SELECT email, role FROM users WHERE email = 'alondes1234@gmail.com'");
if ($test) {
    echo "\n SUCCESS! Login with:\n";
    echo "   Super Admin: alondescdadorm@gmail.com / password123\n";
    echo "   Manager:     manager1234@gmail.com / password123\n";
    
} else {
    echo "\n FAILED\n";
}
echo "</pre>";
