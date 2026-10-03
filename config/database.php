<?php

$serverHost = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
$isLiveHost = (strpos($serverHost, 'gleeze.com') !== false || strpos($serverHost, 'agilahost') !== false);

if ($isLiveHost) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'despierisXcN_HAHA');
    define('DB_USER', 'despierisXcN_HAHA');
    define('DB_PASS', '8 Ball Pool');
    define('SITE_URL', 'https://fab-cafe.gleeze.com');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'student_boarding_house');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('SITE_URL', 'http://localhost/HAHA');
}

define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

define('SITE_NAME', 'Alondes Cda Dorm');
define('SITE_EMAIL', 'alondescdadorm@gmail.com');

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 7200);

date_default_timezone_set('Asia/Manila');

define('RECAPTCHA_SITE_KEY', '6Ld5trUtAAAAALYyWaknnHc3_fMiTDHKpVEmvTro');
define('RECAPTCHA_SECRET_KEY', '6Ld5trUtAAAAAJTdJuTzWEj1neOoBArfRB9gLu35');
define('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify');