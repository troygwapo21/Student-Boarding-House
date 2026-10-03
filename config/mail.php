<?php
/**
 * Secure server-side SMTP configuration.
 *
 * ---------------------------------------------------------------------------
 * SECURITY: This file is executed by PHP only and is never rendered to the
 * browser. Do NOT paste these values into HTML, JavaScript, or any public
 * file, and never commit the App Password to version control.
 * ---------------------------------------------------------------------------
 *
 * Steps to enable Gmail sending:
 *   1. Generate a Gmail App Password for alondescdadorm@gmail.com at
 *      https://myaccount.google.com/apppasswords (requires 2-Step
 *      Verification on the account).
 *   2. Replace MAIL_PASSWORD below with that 16-character App Password.
 *      Do NOT use the account's regular login password.
 */

define('MAIL_HOST', 'smtp.gmail.com');
// Port 587 + STARTTLS works on InfinityFree hosting (ports 465/25 are blocked
// there) and on XAMPP/local. MailService auto-falls-back to 465/SSL if 587 fails.
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls'); // PHPMailer::ENCRYPTION_STARTTLS
define('MAIL_AUTH', true);
define('MAIL_USERNAME', 'tvillaruel39@gmail.com');
define('MAIL_PASSWORD', 'dihj bqpr zgtt newd');
define('MAIL_FROM_EMAIL', 'tvillaruel39@gmail.com');
define('MAIL_FROM_NAME', 'ALONDES CDA DORM');