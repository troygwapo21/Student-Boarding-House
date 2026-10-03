<?php
/**
 * Helper Functions
 */

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return SITE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return SITE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header('Location: ' . SITE_URL . $url);
        exit;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $_SESSION[CSRF_TOKEN_NAME] . '">';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
}

if (!function_exists('flash')) {
    function flash(string $type): ?string {
        $msg = $_SESSION["flash_{$type}"] ?? null;
        unset($_SESSION["flash_{$type}"]);
        return $msg;
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount): string {
        if ($amount == (int)$amount) {
            return '₱' . number_format((int)$amount);
        }
        return '₱' . number_format($amount, 2);
    }
}

if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol(): string {
        return '₱';
    }
}

if (!function_exists('getSiteName')) {
    function getSiteName(): string {
        return getSettingValue('site_name', defined('SITE_NAME') ? SITE_NAME : 'Student Boarding House');
    }
}

if (!function_exists('getSettingValue')) {
    function getSettingValue(string $key, $default = '') {
        static $cache = [];
        if (array_key_exists($key, $cache)) return $cache[$key];
        try {
            $db = Database::getInstance();
            $row = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
            // Treat stored '0' as a real value (e.g. late_fee = 0 disables penalties).
            $raw = ($row && $row['setting_value'] !== null && $row['setting_value'] !== '')
                ? $row['setting_value']
                : $default;
            // Collapse compound HTML-entity encoding from legacy saves
            // (e.g. tenant&#039;s -> tenant&amp;#039;s -> ...) back to plain text.
            $cache[$key] = decodeNestedEntities((string)$raw);
        } catch (\Exception $e) {
            $cache[$key] = $default;
        }
        return $cache[$key];
    }
}

if (!function_exists('decodeNestedEntities')) {
    /**
     * Decode possibly-repeatedly HTML-encoded entities down to plain text.
     * Guards against compound &#039; / &amp;#039; / &amp;amp;#039; ... values.
     */
    function decodeNestedEntities($value): string {
        $decoded = (string)$value;
        for ($i = 0; $i < 10; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES, 'UTF-8');
            if ($next === $decoded) break;
            $decoded = $next;
        }
        return $decoded;
    }
}

if (!function_exists('getSiteLogo')) {
    function getSiteLogo(string $size = '32'): string {
        $logo = getSettingValue('site_logo', '');
        if (!empty($logo)) {
            return UPLOAD_URL . $logo;
        }
        return SITE_URL . '/public/images/logo.jpg';
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $date, string $format = 'M d, Y'): string {
        if (empty($date)) return '';
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime(?string $datetime, string $format = 'M d, Y h:i A'): string {
        if (empty($datetime)) return '';
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('addCalendarMonths')) {
    function addCalendarMonths(?string $date, int $months): ?string {
        if (empty($date)) return null;
        $d = new DateTime($date, new DateTimeZone('Asia/Manila'));
        $day = (int)$d->format('d');
        $target = clone $d;
        $target->setDate((int)$d->format('Y'), (int)$d->format('n') + $months, 1);
        $lastDay = (int)$target->format('t');
        $target->setDate((int)$target->format('Y'), (int)$target->format('n'), min($day, $lastDay));
        $target->setTime(0, 0, 0);
        return $target->format('Y-m-d');
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(?string $datetime): string {
        if (empty($datetime)) return '';
        $now = serverNow();
        $ago = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
        $diff = $now->diff($ago);
        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    }
}

if (!function_exists('generateCode')) {
    function generateCode(string $prefix, int $length = 6): string {
        $number = str_pad(mt_rand(1, 999999), $length, '0', STR_PAD_LEFT);
        return $prefix . '-' . serverDate('Y') . '-' . $number;
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string {
        $classes = [
            'active' => 'bg-success', 'inactive' => 'bg-secondary', 'pending' => 'bg-warning text-dark',
            'approved' => 'bg-success', 'rejected' => 'bg-danger', 'cancelled' => 'bg-secondary',
            'paid' => 'bg-success', 'overdue' => 'bg-danger', 'refunded' => 'bg-info',
            'available' => 'bg-success', 'occupied' => 'bg-primary', 'under_maintenance' => 'bg-warning text-dark',
            'reserved' => 'bg-info', 'open' => 'bg-warning text-dark', 'under_review' => 'bg-info',
            'resolved' => 'bg-success', 'closed' => 'bg-secondary',
            'in_progress' => 'bg-info', 'new' => 'bg-primary', 'read' => 'bg-secondary',
            'replied' => 'bg-success', 'archived' => 'bg-secondary',
            'suspended' => 'bg-danger', 'locked' => 'bg-dark',
            'low' => 'bg-info', 'medium' => 'bg-warning text-dark', 'high' => 'bg-danger',
            'critical' => 'bg-dark', 'urgent' => 'bg-danger',
            'general' => 'bg-secondary', 'important' => 'bg-warning text-dark',
            'maintenance' => 'bg-info', 'event' => 'bg-primary',
            'upcoming' => 'bg-primary', 'due_today' => 'bg-warning text-dark', 'partially_paid' => 'bg-warning text-dark',
        ];
        $class = $classes[$status] ?? 'bg-secondary';
        $label = ucwords(str_replace('_', ' ', $status));
        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
}

if (!function_exists('paymentStatusCell')) {
    function paymentStatusCell(array $payment): string {
        $amountPaid = (float)($payment['amount_paid'] ?? 0);
        $status = (string)($payment['status'] ?? '');
        $terminal = ['paid', 'partially_paid', 'refunded', 'cancelled'];

        if ($amountPaid <= 0 && !in_array($status, $terminal, true)) {
            return '<span class="text-muted">Not Submitted</span>';
        }

        $verified = !empty($payment['verified_at']) || !empty($payment['verified_by']);
        $isCredit = ($payment['payment_method'] ?? '') === 'credit';
        if ($amountPaid > 0 && !$verified && !$isCredit && !in_array($status, $terminal, true)) {
            return '<span class="badge bg-warning text-dark">Pending</span>';
        }

        return statusBadge($status);
    }
}

if (!function_exists('refundTypeLabel')) {
    function refundTypeLabel(string $type): string {
        if ($type === 'advance') return 'Advance';
        if ($type === 'all') return 'All Payments';
        return 'Monthly';
    }
}

if (!function_exists('refundTypeBadge')) {
    function refundTypeBadge(string $type): string {
        if ($type === 'advance') $cls = 'info';
        elseif ($type === 'all') $cls = 'success';
        else $cls = 'primary';
        return '<span class="badge bg-' . $cls . ' bg-opacity-10 text-' . $cls . '">' . e(refundTypeLabel($type)) . '</span>';
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100): string {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}

if (!defined('GMAIL_EMAIL_ERROR')) {
    define('GMAIL_EMAIL_ERROR', 'Please enter a valid Gmail address ending with @gmail.com.');
}

if (!function_exists('isValidGmailEmail')) {
    /**
     * Validate an email address: must be a syntactically valid email
     * ending exactly with "@gmail.com" (e.g. example@gmail.com).
     * Rejects gmail.con, gmail.co, yahoo.com, hotmail.com, etc.
     * Returns false for empty input.
     */
    function isValidGmailEmail(?string $value): bool {
        $email = strtolower(trim((string)$value));
        if ($email === '') return false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        return (bool)preg_match('/^[a-z0-9](?:[a-z0-9._%+-]*[a-z0-9])?@gmail\.com$/', $email);
    }
}

if (!function_exists('gmailEmailError')) {
    /**
     * Build a field-specific Gmail validation error that identifies the
     * exact field and echoes back the wrong input, e.g.:
     * Email address "example@gmail.con" is not a valid Gmail address.
     * Please enter a valid Gmail address ending with @gmail.com.
     * The returned string is safe to output (value is escaped).
     */
    function gmailEmailError(string $fieldLabel = 'Email', ?string $value = null): string {
        $label = trim($fieldLabel) !== '' ? trim($fieldLabel) : 'Email';
        $input = trim((string)$value);
        if ($input === '') {
            return "{$label} is required. Please enter a valid Gmail address ending with @gmail.com.";
        }
        $safe = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return "{$label} \"{$safe}\" is not a valid Gmail address. Please enter a valid Gmail address ending with @gmail.com.";
    }
}

if (!function_exists('normalizeMobileNumber')) {
    /**
     * Validate a PH mobile number: exactly 11 numeric digits starting with "09".
     * Any non-numeric character (letters, spaces, +, -, etc.) makes it invalid.
     * Returns '' for empty input, the valid number, or null when invalid.
     */
    function normalizeMobileNumber(?string $value): ?string {
        $raw = trim((string)$value);
        if ($raw === '') return '';
        if (!preg_match('/^\d{11}$/', $raw)) return null;
        return str_starts_with($raw, '09') ? $raw : null;
    }
}

if (!function_exists('locationBarangays')) {
    /**
     * Canonical service-area municipalities and their barangays.
     * Single source of truth: rendered into the registration form dropdowns
     * (via json_encode) and used for server-side whitelist validation so a
     * direct POST cannot inject values the form never offered.
     */
    function locationBarangays(): array {
        return [
            'Bantayan'   => ['Atop-atop','Baigad','Banale','Binaobao','Botigues','Doong','Guiwanon','Hilutungan','Kabac','Kampingganon','Lipayran','Mojon','Patao','Putian','Sungko','Suba','Sulangan','Tamiao','Ticad','Ticad Reclamation'],
            'Madridejos' => ['Kaongkod','Maalat','Bunakan', 'Kangwayan', 'San Agustin', 'Tabagak', 'Tarong', 'Kodia', 'Malbago','Mancilang','Pili','Poblacion','Talangnan','Tugas'],
            'Santa Fe'   => ['Balidbid','Hagdan','Hilantagaan','Kinatarcan','Langub','Maricaban','Okoy','Poblacion','Pooc','Talisay'],
        ];
    }
}

if (!function_exists('isValidServiceLocation')) {
    /**
     * Validate a province/municipality/barangay combination against the
     * canonical service area. The barangay must belong to the municipality.
     */
    function isValidServiceLocation(string $province, string $municipality, string $barangay): bool {
        if ($province !== 'Cebu') return false;
        $map = locationBarangays();
        if (!isset($map[$municipality])) return false;
        return in_array($barangay, $map[$municipality], true);
    }
}

if (!function_exists('validateMoney')) {
    function validateMoney($value, bool $required = false, float $min = 0, ?float $max = null): ?float {
        $raw = trim((string)$value);
        if ($raw === '' || $raw === null) {
            if ($required) return null;
            return 0.0;
        }
        $raw = preg_replace('/[^0-9]/', '', $raw);
        if ($raw === '') return ($required) ? null : 0.0;
        $num = filter_var($raw, FILTER_VALIDATE_INT);
        if ($num === false || $num < 0) return null;
        if ((float)$num < $min) return null;
        if ($max !== null && (float)$num > $max) return null;
        return (float)$num;
    }
}

if (!function_exists('moneySettings')) {
    function moneySettings(): array {
        return ['late_fee', 'monthly_rent', 'advance_payment'];
    }
}

if (!function_exists('serverNow')) {
    function serverNow(): DateTime {
        return new DateTime('now', new DateTimeZone('Asia/Manila'));
    }
}

if (!function_exists('serverDate')) {
    function serverDate(string $format = 'Y-m-d'): string {
        return $format === 'Y-m-d'
            ? serverNow()->format('Y-m-d')
            : serverNow()->format($format);
    }
}

if (!function_exists('serverDateTime')) {
    function serverDateTime(): string {
        return serverNow()->format('Y-m-d H:i:s');
    }
}

if (!function_exists('serverTimestamp')) {
    function serverTimestamp(): int {
        return time();
    }
}
                