<?php


define('DB_HOST', 'sql311.infinityfree.com');
define('DB_NAME', 'if0_42763980_student_boarding_house');
define('DB_USER', 'if0_42763980');
define('DB_PASS', '8DOhvchcMSrhIk');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

define('SITE_URL', 'https://alondescdadorm.great-site.net');
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