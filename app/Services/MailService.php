<?php
/**
 * Mail Service
 *
 * Sends email through PHPMailer using Gmail SMTP. All SMTP credentials come
 * from the secure server-side configuration in config/mail.php — they are
 * never rendered, exposed in JavaScript, or stored verbatim in HTML.
 *
 * Falls back to PHP mail() only if PHPMailer setup is missing or sending
 * throws, so existing features never break silently.
 */

require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../lib/phpmailer/Exception.php';
require_once __DIR__ . '/../../lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../lib/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

class MailService {

    /**
     * Send a multipart/alternative (HTML + plain text) email.
     * Deliberately has no database dependency: sending must keep working
     * even when the DB is slow or unavailable.
     */
    public function send(string $to, string $subject, string $htmlBody, string $fromEmail = '', string $fromName = ''): bool {
        $to        = $this->cleanHeaderValue(trim($to));
        $subject   = $this->cleanHeaderValue($subject);
        $fromEmail = $this->cleanHeaderValue($fromEmail !== '' ? $fromEmail : (defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : ''));
        $fromName  = $this->cleanHeaderValue($fromName !== '' ? $fromName : (defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : ''));
        $plainBody = $this->toPlainText($htmlBody);

        foreach ($this->connectionAttempts() as [$port, $encryption]) {
            try {
                if ($this->sendPhpMailer($to, $subject, $fromEmail, $fromName, $htmlBody, $plainBody, $port, $encryption)) {
                    return true;
                }
            } catch (\Throwable $e) {
                error_log('MailService: PHPMailer failed for ' . $to . ' on port ' . $port . ' (' . $encryption . ') — ' . $e->getMessage());
            }
        }

        return $this->sendMail($fromEmail, $fromName, $to, $subject, $htmlBody, $plainBody);
    }

    /**
     * Port/encryption pairs to try, in order.
     *
     * InfinityFree (and many shared hosts) block outbound port 465, so the
     * configured port is tried first and the alternate Gmail-supported
     * combination (587/STARTTLS) is used as an automatic fallback.
     *
     * @return array<int, array{0:int,1:string}>
     */
    private function connectionAttempts(): array {
        $port = defined('MAIL_PORT') ? (int)MAIL_PORT : 465;
        $enc  = defined('MAIL_ENCRYPTION') ? strtolower((string)MAIL_ENCRYPTION) : 'ssl';
        if (!in_array($enc, ['ssl', 'tls'], true)) $enc = 'ssl';

        $attempts = [$port === 587 ? [587, 'tls'] : [$port, $enc]];
        $attempts[] = $port === 465 ? [587, 'tls'] : [465, 'ssl'];

        return $attempts;
    }

    /**
     * Send through PHPMailer over SMTP (SMTPS 465 or STARTTLS 587).
     */
    private function sendPhpMailer(string $to, string $subject, string $fromEmail, string $fromName, string $htmlBody, string $plainBody, int $port, string $encryption): bool {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = defined('MAIL_HOST') && MAIL_HOST !== '' ? MAIL_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = MAIL_AUTH;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = $encryption === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $port;
        $mail->Timeout    = 20;
        $mail->SMTPKeepAlive = false;

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($fromEmail, $fromName);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody;

        return $mail->send();
    }

    private function sendMail(string $fromEmail, string $fromName, string $to, string $subject, string $htmlBody, string $plainBody): bool {
        $boundary = '----=_Part_' . md5(uniqid((string)mt_rand(), true));
        $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

        $message  = "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $plainBody . "\r\n";
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlBody . "\r\n";
        $message .= "--{$boundary}--\r\n";

        return @mail($to, $subject, $message, $headers);
    }

    private function cleanHeaderValue(string $value): string {
        return str_replace(["\r", "\n"], '', $value);
    }

    private function toPlainText(string $html): string {
        $text = preg_replace('#<(br\s*/?|/p|/div|/h[1-6]|/li|/tr)>#i', "\n", $html);
        $text = preg_replace('#<[^>]+>#', '', $text);
        $text = html_entity_decode($text ?? '', ENT_QUOTES, 'UTF-8');
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text ?? '');
    }
}