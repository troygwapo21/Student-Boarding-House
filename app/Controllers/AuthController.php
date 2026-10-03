<?php

class AuthController extends Controller {

    private int $maxAttempts = 3;
    private int $lockoutDuration = 500;

    private RecaptchaService $recaptcha;

    private const RESET_TOKEN_TTL_MINUTES = 60;
    private const RESET_REQUEST_COOLDOWN_MINUTES = 2;

    private const OTP_TTL_SECONDS = 300;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 3;
    private const OTP_MAX_CODES_PER_HOUR = 6;

    public function __construct() {
        parent::__construct();
        $this->recaptcha = new RecaptchaService();
        $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('max_login_attempts','lockout_duration')");
        foreach ($settings as $s) {
            if ($s['setting_key'] === 'max_login_attempts') $this->maxAttempts = (int)$s['setting_value'];
            if ($s['setting_key'] === 'lockout_duration') $this->lockoutDuration = (int)$s['setting_value'];
        }
    }

    public function login(): void {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/login');
                return;
            }

            $recaptchaToken = $this->input('g-recaptcha-response', '');
            $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (empty($recaptchaToken)) {
                $this->flash('error', 'Please complete the reCAPTCHA verification.');
                $this->redirect('/login');
                return;
            }

            $valid = $this->recaptcha->verify($recaptchaToken, $remoteIp);

            if (!$valid) {
                $this->flash('error', 'reCAPTCHA verification failed. Please try again.');
                $this->redirect('/login');
                return;
            }

            $email = strtolower(trim($this->input('email', '')));
            $password = $this->input('password', '');

            if (empty($email) || empty($password)) {
                $this->flash('error', 'Please enter both email and password.');
                $this->redirect('/login');
                return;
            }

            if (!isValidGmailEmail($email)) {
                $this->flash('error', gmailEmailError('Email address', $email));
                $this->redirect('/login');
                return;
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $user = $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);

            if (!$user) {
                $this->db->insert('login_attempts', [
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'success' => 0,
                ]);
                $this->flash('error', 'Invalid email or password.');
                $this->redirect('/login');
                return;
            }

            if ($user['status'] === 'suspended') {
                $this->flash('error', 'Your account has been suspended. Please contact support.');
                $this->redirect('/login');
                return;
            }

            if ($user['status'] === 'locked') {
                $this->flash('error', 'Your account is locked. Please try again later or contact support.');
                $this->redirect('/login');
                return;
            }

            if ($user['locked_until'] && strtotime($user['locked_until']) > serverTimestamp()) {
                $remaining = (int)ceil(strtotime($user['locked_until']) - serverTimestamp());
                $_SESSION['login_lock_message'] = 'Too many failed attempts. Your account is locked.';
                $_SESSION['login_lock_seconds'] = $remaining;
                $this->flash('error', "Too many failed attempts. Your account is locked. Try again in " . $this->formatLockDuration($remaining) . ".");
                $this->redirect('/login');
                return;
            }

            if (!password_verify($password, $user['password'])) {
                $this->db->insert('login_attempts', [
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'success' => 0,
                ]);

                $attempts = (int)$user['login_attempts'] + 1;

                if ($attempts >= $this->maxAttempts) {
                    $this->db->update('users', [
                        'login_attempts' => 0,
                        'locked_until' => serverNow()->modify("+{$this->lockoutDuration} seconds")->format('Y-m-d H:i:s'),
                    ], "id = ?", [$user['id']]);
                    $_SESSION['login_lock_message'] = 'Too many failed attempts. Your account is locked.';
                    $_SESSION['login_lock_seconds'] = $this->lockoutDuration;
                    $this->flash('error', "Too many failed attempts. Your account is locked for " . $this->formatLockDuration($this->lockoutDuration) . ". Try again later.");
                } else {
                    $this->db->update('users', [
                        'login_attempts' => $attempts,
                    ], "id = ?", [$user['id']]);
                    $remaining = $this->maxAttempts - $attempts;
                    $this->flash('error', "Invalid email or password. {$remaining} attempt" . ($remaining !== 1 ? 's' : '') . " remaining.");
                }
                $this->redirect('/login');
                return;
            }

            if ((int)($user['email_verified'] ?? 0) === 0) {
                $_SESSION['verify_user_id'] = (int)$user['id'];
                // A code used to be assumed "already sent" here — nothing was
                // mailed, so the account never received anything to type in.
                try {
                    if ($this->sendEmailVerificationCode((int)$user['id'], $user['email'])) {
                        $this->flash('info', 'Please verify your email before logging in. Enter the 6-digit code we just sent to your email.');
                    } else {
                        $this->flash('warning', 'Please verify your email before logging in. We could not send the code right now — click Resend Code on the next page.');
                    }
                } catch (\Throwable $e) {
                    error_log('login: could not send verification code for user ' . (int)$user['id'] . ' - ' . $e->getMessage());
                    $this->flash('warning', 'Please verify your email before logging in. Click Resend Code on the next page to receive your code.');
                }
                $this->redirect('/verify-email');
                return;
            }

            if ($user['status'] === 'inactive') {
                $this->flash('error', 'Your account is inactive. Please verify your email or contact support.');
                $this->redirect('/login');
                return;
            }

            $_SESSION['login_verify_user_id'] = (int)$user['id'];

            if ($this->sendLoginVerificationCode((int)$user['id'], $user['email'])) {
                $this->flash('info', 'Enter the 6-digit code we sent to your email to complete your login.');
            } else {
                $this->flash('warning', 'We could not send a verification code right now. Please click Resend Code to try again.');
            }
            $this->redirect('/verify-login');
            return;
        }

        $lockSeconds = (int)($_SESSION['login_lock_seconds'] ?? 0);
        $lockMessage = (string)($_SESSION['login_lock_message'] ?? '');
        unset($_SESSION['login_lock_seconds'], $_SESSION['login_lock_message']);

        $data = [
            'pageTitle' => 'Login',
            'flashMessages' => $this->getFlashMessages(),
            'lockSeconds' => max(0, $lockSeconds),
            'lockMessage' => $lockMessage,
            'siteKey' => $this->recaptcha->getSiteKey(),
        ];
        $this->view('auth.login', $data, 'public');
    }

    private function formatLockDuration(int $seconds): string {
        $mins = (int)floor($seconds / 60);
        $secs = $seconds % 60;
        $parts = [];
        if ($mins > 0) $parts[] = $mins . ' minute' . ($mins !== 1 ? 's' : '');
        if ($secs > 0 || $mins === 0) $parts[] = $secs . ' second' . ($secs !== 1 ? 's' : '');
        return implode(' ', $parts);
    }

    private function generateStudentId(): string {
        $year = serverDate('Y');
        $last = $this->db->fetch("SELECT student_id_number FROM students WHERE student_id_number LIKE ? ORDER BY id DESC LIMIT 1", ["STU-{$year}-%"]);
        if ($last && preg_match('/STU-\d{4}-(\d+)$/', $last['student_id_number'], $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        return sprintf('STU-%s-%03d', $year, $next);
    }

    public function register(): void {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/register');
                return;
            }

            // --- Collect all inputs ---
            $email              = strtolower(trim($this->input('email', '')));
            $username           = strtolower(trim($this->input('username', '')));
            $password           = $this->input('password', '');
            $passwordConfirm    = $this->input('password_confirmation', '');
            $firstName          = $this->sanitize($this->input('first_name', ''));
            $middleName         = $this->sanitize($this->input('middle_name', ''));
            $lastName           = $this->sanitize($this->input('last_name', ''));
            $suffix             = $this->sanitize($this->input('suffix', ''));
            $gender             = $this->input('gender', '');
            $dateOfBirth        = $this->input('date_of_birth', '');
            $civilStatus        = $this->input('civil_status', '');
            $nationality        = $this->sanitize($this->input('nationality', 'Filipino'));
            $studentIdNumber    = $this->generateStudentId();
            $schoolUniversity   = $this->sanitize($this->input('school_university', ''));
            $courseProgram       = $this->input('course_program', '');
            $yearLevel           = $this->input('year_level', '');
            $phone               = normalizeMobileNumber($this->input('phone', ''));
            $houseUnit           = $this->sanitize($this->input('house_unit', ''));
            $street              = $this->sanitize($this->input('street', ''));
            $barangay            = $this->sanitize($this->input('barangay', ''));
            $municipalityCity    = $this->sanitize($this->input('municipality_city', ''));
            $province            = $this->sanitize($this->input('province', ''));
            $zipCode             = $this->sanitize($this->input('zip_code', ''));

            // Guardian fields
            $guardianFirstName      = $this->sanitize($this->input('guardian_first_name', ''));
            $guardianMiddleName     = $this->sanitize($this->input('guardian_middle_name', ''));
            $guardianLastName       = $this->sanitize($this->input('guardian_last_name', ''));
            $guardianRelationship   = $this->sanitize($this->input('guardian_relationship', ''));
            $guardianMobile         = normalizeMobileNumber($this->input('guardian_mobile', ''));
            $guardianAltContact     = normalizeMobileNumber($this->input('guardian_alt_contact', ''));
            $guardianEmail          = strtolower(trim($this->input('guardian_email', '')));
            $guardianHouseUnit      = $this->sanitize($this->input('guardian_house_unit', ''));
            $guardianStreet         = $this->sanitize($this->input('guardian_street', ''));
            $guardianBarangay       = $this->sanitize($this->input('guardian_barangay', ''));
            $guardianMunicipality   = $this->sanitize($this->input('guardian_municipality_city', ''));
            $guardianProvince       = $this->sanitize($this->input('guardian_province', ''));
            $guardianZipCode        = $this->sanitize($this->input('guardian_zip_code', ''));

            // Build old input array for repopulation (never include passwords)
            $old = [
                'first_name'                 => $firstName,
                'middle_name'                => $middleName,
                'last_name'                  => $lastName,
                'suffix'                     => $suffix,
                'gender'                     => $gender,
                'date_of_birth'              => $dateOfBirth,
                'civil_status'               => $civilStatus,
                'nationality'                => $nationality,
                'student_id_number'          => $studentIdNumber,
                'school_university'          => $schoolUniversity,
                'course_program'             => $courseProgram,
                'year_level'                 => $yearLevel,
                'email'                      => $email,
                'phone'                      => $this->input('phone', ''),
                'house_unit'                 => $houseUnit,
                'street'                     => $street,
                'barangay'                   => $barangay,
                'municipality_city'          => $municipalityCity,
                'province'                   => $province,
                'zip_code'                   => $zipCode,
                'username'                   => $username,
                'guardian_first_name'        => $guardianFirstName,
                'guardian_middle_name'       => $guardianMiddleName,
                'guardian_last_name'         => $guardianLastName,
                'guardian_relationship'      => $guardianRelationship,
                'guardian_mobile'            => $this->input('guardian_mobile', ''),
                'guardian_alt_contact'       => $this->input('guardian_alt_contact', ''),
                'guardian_email'             => $guardianEmail,
                'guardian_house_unit'        => $guardianHouseUnit,
                'guardian_street'            => $guardianStreet,
                'guardian_barangay'          => $guardianBarangay,
                'guardian_municipality_city' => $guardianMunicipality,
                'guardian_province'          => $guardianProvince,
                'guardian_zip_code'          => $guardianZipCode,
            ];

            // --- Validation ---
            $errors = [];

            // Personal
            if (empty($firstName))    $errors[] = 'First name is required.';
            if (empty($lastName))     $errors[] = 'Last name is required.';
            if (empty($gender))       $errors[] = 'Gender is required.';
            if (empty($dateOfBirth))  $errors[] = 'Date of birth is required.';
            else {
                $dob = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
                if (!$dob || $dob->format('Y-m-d') !== $dateOfBirth) {
                    $errors[] = 'Date of birth is invalid.';
                } else {
                    $age = (int)$dob->diff(serverNow())->y;
                    if ($age < 18) {
                        $errors[] = 'You must be at least 18 years old to register.';
                    } elseif ($age > 100) {
                        $errors[] = 'You must be 100 years old or below to register.';
                    }
                }
            }
            if (empty($civilStatus))  $errors[] = 'Civil status is required.';
            if (empty($nationality))  $errors[] = 'Nationality is required.';

            // Student
            if (empty($schoolUniversity)) $errors[] = 'School/College is required.';
            $validCourses = ['BSIT', 'BSHM', 'BSED', 'BSBA', 'BSCE', 'BSCRIM'];
            if (empty($courseProgram))    $errors[] = 'Course/Program is required.';
            elseif (!in_array($courseProgram, $validCourses)) $errors[] = 'Invalid course/program selected.';
            if (empty($yearLevel))        $errors[] = 'Year level is required.';

            // Contact
            // NOTE: normalizeMobileNumber() returns '' for empty input and null for
            // invalid input — the null check MUST come first or invalid numbers
            // would be misreported as "required" (empty(null) === true).
            if (mb_strlen($email) > 254) {
                $errors[] = 'Email address must be 254 characters or fewer.';
            } elseif (!isValidGmailEmail($email)) {
                $errors[] = gmailEmailError('Email address', $email);
            }
            if ($phone === null) {
                $errors[] = 'Mobile number is invalid. Please enter a valid 11-digit Philippine mobile number starting with 09.';
            } elseif ($phone === '') {
                $errors[] = 'Mobile number is required.';
            }
            if ($zipCode === '') {
                $errors[] = 'ZIP code is required.';
            } elseif (!preg_match('/^\d{4}$/', $zipCode)) {
                $errors[] = 'ZIP code must be exactly 4 digits.';
            }
            if (mb_strlen($street) > 255) {
                $errors[] = 'Street must not exceed 255 characters.';
            }
            // Location whitelist: the form only offers these; reject anything
            // else injected via direct requests.
            if ($province === '') {
                $errors[] = 'Province is required.';
            } elseif ($province !== 'Cebu') {
                $errors[] = 'Please select a valid province.';
            }
            if ($municipalityCity === '') {
                $errors[] = 'Municipality/City is required.';
            } elseif (!isset(locationBarangays()[$municipalityCity])) {
                $errors[] = 'Please select a valid municipality/city.';
            }
            if ($barangay === '') {
                $errors[] = 'Barangay is required.';
            } elseif ($province === 'Cebu' && isset(locationBarangays()[$municipalityCity]) && !isValidServiceLocation($province, $municipalityCity, $barangay)) {
                // Only meaningful once province+municipality are themselves valid;
                // otherwise the location errors above already block submission.
                $errors[] = 'Please select a valid barangay for the selected municipality.';
            }

            // Account
            if (empty($username)) $errors[] = 'Username is required.';
            elseif (strlen($username) < 4) $errors[] = 'Username must be at least 4 characters.';
            if (empty($password)) $errors[] = 'Password is required.';
            elseif (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
            elseif (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter.';
            elseif (!preg_match('/[a-z]/', $password)) $errors[] = 'Password must contain at least one lowercase letter.';
            elseif (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number.';
            elseif (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Password must contain at least one special character.';
            if ($password !== $passwordConfirm) $errors[] = 'Passwords do not match.';

            // Guardian
            if (empty($guardianFirstName))    $errors[] = 'Guardian first name is required.';
            if (empty($guardianLastName))     $errors[] = 'Guardian last name is required.';
            if (empty($guardianRelationship)) $errors[] = 'Guardian relationship is required.';
            if (empty($guardianMobile))       $errors[] = 'Guardian mobile number is required.';
            elseif ($guardianMobile === null) $errors[] = 'Guardian mobile number is invalid. Please enter a valid 11-digit Philippine mobile number starting with 09.';
            if ($guardianAltContact === null) $errors[] = 'Guardian alternative contact is invalid. Please enter a valid 11-digit Philippine mobile number starting with 09.';
            if (!empty($guardianEmail) && !isValidGmailEmail($guardianEmail)) $errors[] = gmailEmailError('Guardian email', $guardianEmail);
            if (empty($guardianBarangay))     $errors[] = 'Guardian barangay is required.';
            if (empty($guardianMunicipality)) $errors[] = 'Guardian municipality/city is required.';
            if (empty($guardianProvince))     $errors[] = 'Guardian province is required.';
            if (empty($guardianZipCode))      $errors[] = 'Guardian ZIP code is required.';

            // Terms
            $termsAccepted = $this->input('terms', '');
            if (empty($termsAccepted)) $errors[] = 'You must agree to the Terms & Conditions and Privacy Policy.';

            // Uniqueness checks (always run so both duplicates are reported at once)
            $errors = array_merge($errors, $this->checkDuplicateUserCredentials($email, $username));

            // If errors, re-render form with data preserved
            if (!empty($errors)) {
                $data = [
                    'pageTitle'     => 'Register',
                    'errors'        => $errors,
                    'old'           => $old,
                    'generatedStudentId' => $studentIdNumber,
                    'flashMessages' => $this->getFlashMessages(),
                ];
                $this->view('auth.register', $data, 'public');
                return;
            }

            // --- File uploads ---
            $profilePicture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $profilePicture = $this->uploadFile($_FILES['profile_picture'], 'profiles', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
            }
            $schoolIdPath = null;
            if (isset($_FILES['school_id_upload']) && $_FILES['school_id_upload']['error'] === UPLOAD_ERR_OK) {
                $schoolIdPath = $this->uploadFile($_FILES['school_id_upload'], 'school_ids', ['jpg', 'jpeg', 'png', 'pdf'], 5242880);
            }

            // --- Compute full address ---
            $fullAddress = trim(implode(', ', array_filter([$houseUnit, $street, $barangay, $municipalityCity, $province, $zipCode])));

            // --- Insert user, student, guardian atomically ---
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $pdo = $this->db->getConnection();

            try {
                $pdo->beginTransaction();

                $userId = $this->db->insert('users', [
                    'email'             => $email,
                    'username'          => $username,
                    'password'          => $hashedPassword,
                    'role'              => 'student',
                    'status'            => 'active',
                    'email_verified'    => 0,
                    'email_verified_at' => null,
                    'password_changed_at' => serverDateTime(),
                ]);

                $this->savePasswordHistory($userId, $hashedPassword);

                // --- Insert student ---
                $studentId = $this->db->insert('students', [
                    'user_id'              => $userId,
                    'student_id_number'    => $studentIdNumber,
                    'first_name'           => $firstName,
                    'middle_name'          => $middleName ?: null,
                    'last_name'            => $lastName,
                    'suffix'               => $suffix ?: null,
                    'phone'                => $phone,
                    'address'              => $fullAddress ?: null,
                    'house_unit'           => $houseUnit ?: null,
                    'street'               => $street ?: null,
                    'barangay'             => $barangay ?: null,
                    'municipality_city'    => $municipalityCity ?: null,
                    'province'             => $province ?: null,
                    'zip_code'             => $zipCode ?: null,
                    'date_of_birth'        => $dateOfBirth ?: null,
                    'gender'               => $gender,
                    'civil_status'         => $civilStatus,
                    'nationality'          => $nationality,
                    'school_university'    => $schoolUniversity,
                    'course_program'       => $courseProgram,
                    'year_level'           => $yearLevel,
                    'profile_picture'      => $profilePicture,
                    'school_id_path'       => $schoolIdPath,
                ]);

                // --- Insert guardian ---
                $this->db->insert('guardians', [
                    'student_id'            => $studentId,
                    'first_name'            => $guardianFirstName,
                    'middle_name'           => $guardianMiddleName ?: null,
                    'last_name'             => $guardianLastName,
                    'relationship'          => $guardianRelationship,
                    'mobile_number'         => $guardianMobile,
                    'alternative_contact'   => $guardianAltContact ?: null,
                    'email'                 => $guardianEmail ?: null,
                    'house_unit'            => $guardianHouseUnit ?: null,
                    'street'                => $guardianStreet ?: null,
                    'barangay'              => $guardianBarangay ?: null,
                    'municipality_city'     => $guardianMunicipality ?: null,
                    'province'              => $guardianProvince ?: null,
                    'zip_code'              => $guardianZipCode ?: null,
                ]);

                $pdo->commit();
            } catch (\PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $dupField = $this->getUserDuplicateField($e);
                if ($dupField !== null) {
                    // Lost the race against a duplicate submission — reject safely.
                    $data = [
                        'pageTitle'     => 'Register',
                        'errors'        => [$this->duplicateUserMessage($dupField)],
                        'old'           => $old,
                        'generatedStudentId' => $studentIdNumber,
                        'flashMessages' => $this->getFlashMessages(),
                    ];
                    $this->view('auth.register', $data, 'public');
                    return;
                }
                throw $e;
            }

            $this->logActivity('register', 'New student registered: ' . $email);

            $_SESSION['verify_user_id'] = (int)$userId;

            $sent = $this->sendEmailVerificationCode($userId, $email);
            if ($sent) {
                $this->flash('success', 'Your account has been created! We sent a 6-digit verification code to your email. Please verify your email to continue.');
            } else {
                $this->flash('warning', 'Your account has been created, but we could not send the verification email right now. Please click Resend Code to try again.');
            }
            $this->redirect('/verify-email');
            return;
        }

        $data = [
            'pageTitle' => 'Register',
            'errors'    => [],
            'old'       => [],
            'generatedStudentId' => $this->generateStudentId(),
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('auth.register', $data, 'public');
    }

    /**
     * Create a fresh 6-digit OTP for the user, invalidate every previous
     * unused one, and persist it with an OTP_TTL_SECONDS expiry. Returns the code,
     * or null if the row could not be saved.
     */
    private function persistOtp(int $userId): ?string {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $this->db->update('email_verification_codes', ['used' => 1], "user_id = ? AND used = 0", [$userId]);

            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $this->db->insert('email_verification_codes', [
                'user_id'    => $userId,
                'code'       => $code,
                'attempts'   => 0,
                'expires_at' => serverNow()->modify('+' . self::OTP_TTL_SECONDS . ' seconds')->format('Y-m-d H:i:s'),
                'created_at' => serverDateTime(),
                'used'       => 0,
            ]);

            $pdo->commit();
            return $code;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('persistOtp: failed to persist OTP for user ' . $userId . ' — ' . $e->getMessage());
            return null;
        }
    }

    private function mailCode(string $email, string $subject, string $body): bool {
        require_once __DIR__ . '/../Services/MailService.php';
        try {
            $mailer = new MailService();
            return $mailer->send($email, $subject, $body, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (\Throwable $e) {
            error_log('mailCode: email send failed for ' . $email . ' — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a fresh 6-digit OTP for the user, invalidate every previous
     * one, persist it with an OTP_TTL_SECONDS expiry, and email it right away.
     */
    private function sendEmailVerificationCode(int $userId, string $email): bool {
        $code = $this->persistOtp($userId);
        if ($code === null) {
            return false;
        }

        $subject = 'Verify Your Email Address';
        $body    = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Verify Your Email Address</h2>'
            . '<p>Your verification code is: <strong style="font-size:28px;letter-spacing:4px;color:#8fa61b;">' . $code . '</strong></p>'
            . '<p>This code is 6 digits and expires after 5 minutes. It can only be used once and must not be shared with anyone.</p>'
            . '<p style="color:#64748b;font-size:13px;">If you did not create this account, you can safely ignore this email.</p>'
            . '</div>';

        return $this->mailCode($email, $subject, $body);
    }

    private function sendLoginVerificationCode(int $userId, string $email): bool {
        $code = $this->persistOtp($userId);
        if ($code === null) {
            return false;
        }

        $subject = 'Your Login Verification Code';
        $body    = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Your Login Verification Code</h2>'
            . '<p>Your login verification code is: <strong style="font-size:28px;letter-spacing:4px;color:#8fa61b;">' . $code . '</strong></p>'
            . '<p>Enter this code to complete your sign-in. It is 6 digits, expires after 5 minutes, can only be used once, and must not be shared with anyone.</p>'
            . '<p>You have up to 3 attempts to enter this code. After 3 incorrect attempts you will be returned to the login page.</p>'
            . '<p style="color:#64748b;font-size:13px;">If you did not try to sign in to your account, please ignore this email.</p>'
            . '</div>';

        return $this->mailCode($email, $subject, $body);
    }

    private function sendPasswordChangeVerificationCode(int $userId, string $email): bool {
        $code = $this->persistOtp($userId);
        if ($code === null) {
            return false;
        }

        $subject = 'Your Password Change Verification Code';
        $body    = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Your Password Change Verification Code</h2>'
            . '<p>Your password change verification code is: <strong style="font-size:28px;letter-spacing:4px;color:#8fa61b;">' . $code . '</strong></p>'
            . '<p>Enter this code to confirm your password change. It is 6 digits, expires after 5 minutes, can only be used once, and must not be shared with anyone.</p>'
            . '<p>You have up to 3 attempts to enter this code. After 3 incorrect attempts you will be returned to the password change page.</p>'
            . '<p style="color:#64748b;font-size:13px;">If you did not request a password change, please ignore this email.</p>'
            . '</div>';

        return $this->mailCode($email, $subject, $body);
    }

    public function sendPasswordChangeCode(int $userId, string $email): bool {
        return $this->sendPasswordChangeVerificationCode($userId, $email);
    }

    private function sendPasswordChangedNotification(string $email): void {
        $subject = 'Your Password Has Been Changed';
        $body    = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px;">'
            . '<h2 style="color:#0f172a;margin:0 0 16px;">Password Changed Successfully</h2>'
            . '<p>Your password has been changed successfully. If you made this change, no further action is needed.</p>'
            . '<p>If you did not change your password, please contact support immediately.</p>'
            . '<p style="color:#64748b;font-size:13px;">Security reminder: never share your password with anyone.</p>'
            . '</div>';

        if (!$this->mailCode($email, $subject, $body)) {
            error_log('sendPasswordChangedNotification: failed to send notification to ' . $email);
        }
    }

    private function pendingVerificationUser(): ?array {
        if (empty($_SESSION['verify_user_id'])) {
            return null;
        }
        $user = $this->db->fetch("SELECT id, email, email_verified FROM users WHERE id = ?", [(int)$_SESSION['verify_user_id']]);
        if (!$user) {
            unset($_SESSION['verify_user_id']);
            return null;
        }
        return $user;
    }

    private function pendingLoginUser(): ?array {
        if (empty($_SESSION['login_verify_user_id'])) {
            return null;
        }
        $user = $this->db->fetch("SELECT id, email, role FROM users WHERE id = ?", [(int)$_SESSION['login_verify_user_id']]);
        if (!$user) {
            unset($_SESSION['login_verify_user_id']);
            return null;
        }
        return $user;
    }

    public function verifyEmail(): void {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        $user = $this->pendingVerificationUser();
        if (!$user) {
            $this->flash('warning', 'No pending email verification found. Please login to continue.');
            $this->redirect('/login');
            return;
        }

        if ((int)$user['email_verified'] === 1) {
            unset($_SESSION['verify_user_id']);
            $this->flash('info', 'Your email is already verified. Please login.');
            $this->redirect('/login');
            return;
        }

        // Opening this page never sent anything: the flash shown on redirect
        // promised "the code we sent" while no email went out, and resend is a
        // separate button. On GET, issue a code whenever none is still usable,
        // so landing here always delivers one (TTL doubles as the rate limit).
        if (!$this->isPost()) {
            try {
                $pending = $this->db->fetch(
                    "SELECT expires_at, created_at FROM email_verification_codes
                      WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
                    [(int)$user['id']]
                );
                $hasLiveCode = $pending && strtotime($pending['expires_at']) > serverTimestamp();
                $withinCooldown = $pending
                    && (serverTimestamp() - strtotime($pending['created_at'])) < self::OTP_RESEND_COOLDOWN_SECONDS;
                if (!$hasLiveCode && !$withinCooldown && !$this->sendEmailVerificationCode((int)$user['id'], $user['email'])) {
                    $this->flash('warning', 'We could not send the verification code right now. Please click Resend Code to try again.');
                }
            } catch (\Throwable $e) {
                // Never let the auto-send break the page — the Resend button below still works.
                error_log('verifyEmail: could not auto-send code for user ' . (int)$user['id'] . ' - ' . $e->getMessage());
            }
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/verify-email');
                return;
            }

            $code = trim($this->input('code', ''));
            if (!preg_match('/^\d{6}$/', $code)) {
                $this->flash('error', 'Invalid verification code. Please try again.');
                $this->redirect('/verify-email');
                return;
            }

            $user   = $this->pendingVerificationUser();
            $otp    = $this->db->fetch(
                "SELECT * FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
                [(int)$user['id']]
            );

            if (!$otp) {
                $this->flash('error', 'Verification code not found. Please request a new code.');
                $this->redirect('/verify-email');
                return;
            }

            if (strtotime($otp['expires_at']) < serverTimestamp()) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                $this->flash('error', 'Verification code expired. Please request a new code.');
                $this->redirect('/verify-email');
                return;
            }

            if ((int)$otp['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                $this->flash('error', 'Too many incorrect attempts. Please request a new code.');
                $this->redirect('/verify-email');
                return;
            }

            if (hash_equals($otp['code'], $code)) {
                $pdo = $this->db->getConnection();
                $pdo->beginTransaction();
                try {
                    $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                    $this->db->update('users', [
                        'email_verified'    => 1,
                        'email_verified_at' => serverDateTime(),
                    ], "id = ?", [(int)$user['id']]);
                    $pdo->commit();
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $this->flash('error', 'Something went wrong while verifying your email. Please try again.');
                    $this->redirect('/verify-email');
                    return;
                }

                unset($_SESSION['verify_user_id']);

                $_SESSION['email_captcha_verified_email'] = $user['email'];
                $_SESSION['email_captcha_attempts'] = 0;

                $this->logActivity('email_verified', 'Email verified: ' . $user['email']);

                $this->flash('success', 'Email verified successfully! Please solve the security check to finish.');
                $this->redirect('/verify-email-captcha');
                return;
            }

            $newAttempts = (int)$otp['attempts'] + 1;
            if ($newAttempts >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1, 'attempts' => $newAttempts], "id = ?", [$otp['id']]);
                $this->flash('error', 'Too many incorrect attempts. Please request a new code.');
            } else {
                $this->db->update('email_verification_codes', ['attempts' => $newAttempts], "id = ?", [$otp['id']]);
                $this->flash('error', 'Invalid verification code. Please try again.');
            }
            $this->redirect('/verify-email');
            return;
        }

        $latest = $this->db->fetch(
            "SELECT expires_at FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
            [(int)$user['id']]
        );

        $remainingSeconds = 0;
        if ($latest && strtotime($latest['expires_at']) > serverTimestamp()) {
            $remainingSeconds = strtotime($latest['expires_at']) - serverTimestamp();
        }

        // Resend cooldown: codes are issued every 60 seconds max, so the
        // earliest a new code may be requested is (created_at + 60s).
        $resendCooldown = 0;
        $created = $latest ? (strtotime($latest['expires_at']) - self::OTP_TTL_SECONDS) : 0;
        $resendAvailableAt = $created + self::OTP_RESEND_COOLDOWN_SECONDS;
        if ($resendAvailableAt > serverTimestamp()) {
            $resendCooldown = $resendAvailableAt - serverTimestamp();
        }

        $data = [
            'pageTitle'        => 'Verify Your Email',
            'email'            => $user['email'],
            'remainingSeconds' => max(0, $remainingSeconds),
            'resendCooldown'   => $resendCooldown,
            'flashMessages'    => $this->getFlashMessages(),
        ];
        $this->view('auth.verify_email', $data, 'public');
    }

    public function emailCaptcha(): void {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        if (empty($_SESSION['email_captcha_verified_email'])) {
            $this->flash('warning', 'Please verify your email first.');
            $this->redirect('/verify-email');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/verify-email-captcha');
                return;
            }

            $recaptchaToken = $this->input('g-recaptcha-response', '');
            $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (empty($recaptchaToken)) {
                $this->flash('error', 'Please complete the reCAPTCHA verification.');
                $this->view('auth.captcha_email', [
                    'pageTitle'     => 'Security Check',
                    'siteKey'       => $this->recaptcha->getSiteKey(),
                    'flashMessages' => $this->getFlashMessages(),
                ], 'public');
                return;
            }

            $valid = $this->recaptcha->verify($recaptchaToken, $remoteIp);

            if (!$valid) {
                $this->flash('error', 'reCAPTCHA verification failed. Please try again.');
                $this->view('auth.captcha_email', [
                    'pageTitle'     => 'Security Check',
                    'siteKey'       => $this->recaptcha->getSiteKey(),
                    'flashMessages' => $this->getFlashMessages(),
                ], 'public');
                return;
            }

            unset(
                $_SESSION['email_captcha_verified_email'],
                $_SESSION['email_captcha_attempts']
            );

            $this->flash('success', 'Your account is fully verified. Please login to continue.');
            $this->redirect('/login');
            return;
        }

        $this->view('auth.captcha_email', [
            'pageTitle'     => 'Security Check',
            'siteKey'       => $this->recaptcha->getSiteKey(),
            'flashMessages' => $this->getFlashMessages(),
        ], 'public');
    }

    public function verifyLogin(): void {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        $user = $this->pendingLoginUser();
        if (!$user) {
            $this->flash('warning', 'No pending login verification found. Please login to continue.');
            $this->redirect('/login');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/verify-login');
                return;
            }

            $code = trim($this->input('code', ''));
            if (!preg_match('/^\d{6}$/', $code)) {
                $this->flash('error', 'Invalid verification code. Please try again.');
                $this->redirect('/verify-login');
                return;
            }

            $user = $this->pendingLoginUser();
            $otp  = $this->db->fetch(
                "SELECT * FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
                [(int)$user['id']]
            );

            if (!$otp) {
                $this->flash('error', 'Verification code not found. Please request a new code.');
                $this->redirect('/verify-login');
                return;
            }

            if (strtotime($otp['expires_at']) < serverTimestamp()) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                $this->flash('error', 'Verification code expired. Please request a new code.');
                $this->redirect('/verify-login');
                return;
            }

            if ((int)$otp['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                unset($_SESSION['login_verify_user_id']);
                $this->flash('error', 'Too many incorrect attempts. Returning to the login page.');
                $this->redirect('/login');
                return;
            }

            if (hash_equals($otp['code'], $code)) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);

                unset($_SESSION['login_verify_user_id']);

                $_SESSION['captcha_login_user_id'] = (int)$user['id'];
                $_SESSION['captcha_login_email'] = $user['email'];
                $_SESSION['captcha_login_role'] = $user['role'];
                $_SESSION['captcha_login_attempts'] = 0;

                $this->flash('success', 'Code verified! Please solve the security check to continue.');
                $this->redirect('/verify-login-captcha');
                return;
            }

            $newAttempts = (int)$otp['attempts'] + 1;
            if ($newAttempts >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1, 'attempts' => $newAttempts], "id = ?", [$otp['id']]);
                unset($_SESSION['login_verify_user_id']);
                $this->flash('error', 'Too many incorrect attempts. Returning to the login page.');
                $this->redirect('/login');
                return;
            } else {
                $this->db->update('email_verification_codes', ['attempts' => $newAttempts], "id = ?", [$otp['id']]);
                $this->flash('error', 'Invalid verification code. Please try again.');
            }
            $this->redirect('/verify-login');
            return;
        }

        $latest = $this->db->fetch(
            "SELECT expires_at, attempts FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
            [(int)$user['id']]
        );

        $remainingSeconds = 0;
        if ($latest && strtotime($latest['expires_at']) > serverTimestamp()) {
            $remainingSeconds = strtotime($latest['expires_at']) - serverTimestamp();
        }

        $remainingAttempts = self::OTP_MAX_ATTEMPTS - (int)($latest['attempts'] ?? 0);
        if ($remainingAttempts < 0) {
            $remainingAttempts = 0;
        }

        $resendCooldown = 0;
        $created = $latest ? (strtotime($latest['expires_at']) - self::OTP_TTL_SECONDS) : 0;
        $resendAvailableAt = $created + self::OTP_RESEND_COOLDOWN_SECONDS;
        if ($resendAvailableAt > serverTimestamp()) {
            $resendCooldown = $resendAvailableAt - serverTimestamp();
        }

        $data = [
            'pageTitle'        => 'Login Verification',
            'email'            => $user['email'],
            'remainingSeconds' => max(0, $remainingSeconds),
            'resendCooldown'   => $resendCooldown,
            'remainingAttempts'=> $remainingAttempts,
            'flashMessages'    => $this->getFlashMessages(),
        ];
        $this->view('auth.verify_login', $data, 'public');
    }

    private function pendingCaptchaUser(): ?array {
        if (!isset($_SESSION['captcha_login_user_id'])) {
            return null;
        }
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [(int)$_SESSION['captcha_login_user_id']]);
    }

    public function captchaLogin(): void {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        $user = $this->pendingCaptchaUser();
        if (!$user) {
            $this->flash('warning', 'No pending login verification found. Please login to continue.');
            $this->redirect('/login');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/verify-login-captcha');
                return;
            }

            $recaptchaToken = $this->input('g-recaptcha-response', '');
            $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (empty($recaptchaToken)) {
                $this->flash('error', 'Please complete the reCAPTCHA verification.');
                $this->view('auth.captcha_login', [
                    'pageTitle'     => 'Security Check',
                    'siteKey'       => $this->recaptcha->getSiteKey(),
                    'flashMessages' => $this->getFlashMessages(),
                ], 'public');
                return;
            }

            $valid = $this->recaptcha->verify($recaptchaToken, $remoteIp);

            if (!$valid) {
                $this->flash('error', 'reCAPTCHA verification failed. Please try again.');
                $this->view('auth.captcha_login', [
                    'pageTitle'     => 'Security Check',
                    'siteKey'       => $this->recaptcha->getSiteKey(),
                    'flashMessages' => $this->getFlashMessages(),
                ], 'public');
                return;
            }

            unset(
                $_SESSION['captcha_login_attempts']
            );

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->db->insert('login_attempts', [
                'email' => $user['email'],
                'ip_address' => $ipAddress,
                'success' => 1,
            ]);

            $this->db->update('users', [
                'login_attempts' => 0,
                'locked_until' => null,
            ], "id = ?", [(int)$user['id']]);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            session_regenerate_id(true);

            unset(
                $_SESSION['captcha_login_user_id'],
                $_SESSION['captcha_login_email'],
                $_SESSION['captcha_login_role']
            );

            $this->logActivity('login', 'User logged in successfully');

            $this->flash('success', 'Welcome back!');
            $this->redirectBasedOnRole();
            return;
        }

        $this->view('auth.captcha_login', [
            'pageTitle'     => 'Security Check',
            'siteKey'       => $this->recaptcha->getSiteKey(),
            'flashMessages' => $this->getFlashMessages(),
        ], 'public');
    }

    public function resendLoginCode(): void {
        if (!$this->isPost()) {
            $this->redirect('/verify-login');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token. Please try again.');
            $this->redirect('/verify-login');
            return;
        }

        $user = $this->pendingLoginUser();
        if (!$user) {
            $this->flash('error', 'No pending login verification found.');
            $this->redirect('/login');
            return;
        }

        $latest = $this->db->fetch(
            "SELECT created_at FROM email_verification_codes WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            [(int)$user['id']]
        );

        if ($latest && (serverTimestamp() - strtotime($latest['created_at'])) < self::OTP_RESEND_COOLDOWN_SECONDS) {
            $remaining = self::OTP_RESEND_COOLDOWN_SECONDS - (serverTimestamp() - strtotime($latest['created_at']));
            $this->flash('error', 'Please wait ' . $remaining . ' second' . ($remaining !== 1 ? 's' : '') . ' before requesting a new code.');
            $this->redirect('/verify-login');
            return;
        }

        $recentCount = $this->db->count(
            'email_verification_codes',
            "user_id = ? AND created_at > ?",
            [(int)$user['id'], serverNow()->modify('-60 minutes')->format('Y-m-d H:i:s')]
        );
        if ($recentCount >= self::OTP_MAX_CODES_PER_HOUR) {
            $this->flash('error', 'Too many resend requests. Please try again in an hour.');
            $this->redirect('/verify-login');
            return;
        }

        if ($this->sendLoginVerificationCode((int)$user['id'], $user['email'])) {
            $this->flash('success', 'A new verification code has been sent to your email.');
        } else {
            $this->flash('error', 'We could not send a new verification code right now. Please try again later.');
        }
        $this->redirect('/verify-login');
    }

    public function resendVerification(): void {
        if (!$this->isPost()) {
            $this->redirect('/verify-email');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token. Please try again.');
            $this->redirect('/verify-email');
            return;
        }

        $user = $this->pendingVerificationUser();
        if (!$user || (int)$user['email_verified'] === 1) {
            unset($_SESSION['verify_user_id']);
            $this->flash('error', 'No pending email verification found.');
            $this->redirect('/login');
            return;
        }

        $latest = $this->db->fetch(
            "SELECT created_at FROM email_verification_codes WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            [(int)$user['id']]
        );

        if ($latest && (serverTimestamp() - strtotime($latest['created_at'])) < self::OTP_RESEND_COOLDOWN_SECONDS) {
            $remaining = self::OTP_RESEND_COOLDOWN_SECONDS - (serverTimestamp() - strtotime($latest['created_at']));
            $this->flash('error', 'Please wait ' . $remaining . ' second' . ($remaining !== 1 ? 's' : '') . ' before requesting a new code.');
            $this->redirect('/verify-email');
            return;
        }

        $recentCount = $this->db->count(
            'email_verification_codes',
            "user_id = ? AND created_at > ?",
            [(int)$user['id'], serverNow()->modify('-60 minutes')->format('Y-m-d H:i:s')]
        );
        if ($recentCount >= self::OTP_MAX_CODES_PER_HOUR) {
            $this->flash('error', 'Too many resend requests. Please try again in an hour.');
            $this->redirect('/verify-email');
            return;
        }

        if ($this->sendEmailVerificationCode((int)$user['id'], $user['email'])) {
            $this->flash('success', 'A new verification code has been sent to your email.');
        } else {
            $this->flash('error', 'We could not send a new verification code right now. Please try again later.');
        }
        $this->redirect('/verify-email');
    }

    private function pendingPasswordChange(): ?array {
        if (empty($_SESSION['pending_pw_change']) || !is_array($_SESSION['pending_pw_change'])) {
            return null;
        }
        return $_SESSION['pending_pw_change'];
    }

    private function passwordChangeFallbackUrl(): string {
        $context = (string)($_SESSION['pending_pw_change']['context'] ?? '');
        switch ($context) {
            case 'admin':
                return '/admin/settings';
            case 'manager':
                return '/manager/settings';
            case 'student':
                return '/student/change-password';
            default:
                return '/forgot-password';
        }
    }

    public function verifyPasswordChange(): void {
        $pending = $this->pendingPasswordChange();
        $mode = (string)($pending['mode'] ?? 'forgot');

        if ($mode === 'settings') {
            if (!$this->isLoggedIn()) {
                $this->flash('warning', 'You must be logged in to change your password.');
                $this->redirect('/login');
                return;
            }
        } elseif ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        if (!$pending || !isset($pending['user_id'], $pending['email'], $pending['password_hash'])) {
            $this->flash('warning', 'No pending password change found. Please submit the form again.');
            $this->redirect($this->passwordChangeFallbackUrl());
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/verify-password-change');
                return;
            }

            $code = trim($this->input('code', ''));
            if (!preg_match('/^\d{6}$/', $code)) {
                $this->flash('error', 'Invalid verification code. Please try again.');
                $this->redirect('/verify-password-change');
                return;
            }

            $pending = $this->pendingPasswordChange();

            $user = $this->db->fetch("SELECT id, email FROM users WHERE id = ?", [(int)$pending['user_id']]);
            if (!$user) {
                unset($_SESSION['pending_pw_change']);
                $this->flash('error', 'Account not found. Please submit the form again.');
                $this->redirect($this->passwordChangeFallbackUrl());
                return;
            }

            $otp = $this->db->fetch(
                "SELECT * FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
                [(int)$user['id']]
            );

            if (!$otp) {
                $this->flash('error', 'Verification code not found. Please request a new code.');
                $this->redirect('/verify-password-change');
                return;
            }

            if (strtotime($otp['expires_at']) < serverTimestamp()) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                $this->flash('error', 'Verification code expired. Please request a new code.');
                $this->redirect('/verify-password-change');
                return;
            }

            if ((int)$otp['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);
                unset(
                    $_SESSION['pending_pw_change'],
                    $_SESSION['pending_pw_captcha_ready'],
                    $_SESSION['pending_pw_captcha_attempts']
                );
                $this->flash('error', 'Too many incorrect attempts. Returning to the password change page.');
                $this->redirect($this->passwordChangeFallbackUrl());
                return;
            }

            if (hash_equals($otp['code'], $code)) {
                $this->db->update('email_verification_codes', ['used' => 1], "id = ?", [$otp['id']]);

                $_SESSION['pending_pw_captcha_ready'] = true;
                $_SESSION['pending_pw_captcha_attempts'] = 0;

                $this->flash('success', 'Code verified! Please solve the security check to confirm your password change.');
                $this->redirect('/verify-password-change-captcha');
                return;
            }

            $newAttempts = (int)$otp['attempts'] + 1;
            if ($newAttempts >= self::OTP_MAX_ATTEMPTS) {
                $this->db->update('email_verification_codes', ['used' => 1, 'attempts' => $newAttempts], "id = ?", [$otp['id']]);
                unset(
                    $_SESSION['pending_pw_change'],
                    $_SESSION['pending_pw_captcha_ready'],
                    $_SESSION['pending_pw_captcha_attempts']
                );
                $this->flash('error', 'Too many incorrect attempts. Returning to the password change page.');
                $this->redirect($this->passwordChangeFallbackUrl());
                return;
            } else {
                $this->db->update('email_verification_codes', ['attempts' => $newAttempts], "id = ?", [$otp['id']]);
                $this->flash('error', 'Invalid verification code. Please try again.');
            }
            $this->redirect('/verify-password-change');
            return;
        }

        $latest = $this->db->fetch(
            "SELECT expires_at, attempts FROM email_verification_codes WHERE user_id = ? AND used = 0 ORDER BY id DESC LIMIT 1",
            [(int)$pending['user_id']]
        );

        $remainingSeconds = 0;
        if ($latest && strtotime($latest['expires_at']) > serverTimestamp()) {
            $remainingSeconds = strtotime($latest['expires_at']) - serverTimestamp();
        }

        $remainingAttempts = self::OTP_MAX_ATTEMPTS - (int)($latest['attempts'] ?? 0);
        if ($remainingAttempts < 0) {
            $remainingAttempts = 0;
        }

        $resendCooldown = 0;
        $created = $latest ? (strtotime($latest['expires_at']) - self::OTP_TTL_SECONDS) : 0;
        $resendAvailableAt = $created + self::OTP_RESEND_COOLDOWN_SECONDS;
        if ($resendAvailableAt > serverTimestamp()) {
            $resendCooldown = $resendAvailableAt - serverTimestamp();
        }

        $data = [
            'pageTitle'        => 'Password Change Verification',
            'email'            => $pending['email'],
            'remainingSeconds' => max(0, $remainingSeconds),
            'resendCooldown'   => $resendCooldown,
            'remainingAttempts'=> $remainingAttempts,
            'flashMessages'    => $this->getFlashMessages(),
        ];
        $this->view('auth.verify_password_change', $data, 'public');
    }

    public function passwordChangeCaptcha(): void {
        $pending = $this->pendingPasswordChange();
        $mode = (string)($pending['mode'] ?? 'forgot');

        if ($mode === 'settings') {
            if (!$this->isLoggedIn()) {
                $this->flash('warning', 'You must be logged in to change your password.');
                $this->redirect('/login');
                return;
            }
        } elseif ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }

        if (!$pending || !isset($pending['user_id'], $pending['email'], $pending['password_hash'])) {
            $this->flash('warning', 'No pending password change found. Please submit the form again.');
            $this->redirect($this->passwordChangeFallbackUrl());
            return;
        }

        if (empty($_SESSION['pending_pw_captcha_ready'])) {
            $this->flash('warning', 'Please verify your email code first.');
            $this->redirect('/verify-password-change');
            return;
        }

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/verify-password-change-captcha');
                return;
            }

            $recaptchaToken = $this->input('g-recaptcha-response', '');
            $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (empty($recaptchaToken)) {
                $this->flash('error', 'Please complete the reCAPTCHA verification.');
                $this->view('auth.captcha_password_change', [
                    'pageTitle'     => 'Security Check',
                    'siteKey'       => $this->recaptcha->getSiteKey(),
                    'mode'          => $mode,
                    'flashMessages' => $this->getFlashMessages(),
                ], 'public');
                return;
            }

            $valid = $this->recaptcha->verify($recaptchaToken, $remoteIp);

            if (!$valid) {
                $this->flash('error', 'reCAPTCHA verification failed. Please try again.');
                $this->view('auth.captcha_password_change', [
                    'pageTitle'     => 'Security Check',
                    'siteKey'       => $this->recaptcha->getSiteKey(),
                    'mode'          => $mode,
                    'flashMessages' => $this->getFlashMessages(),
                ], 'public');
                return;
            }

            unset(
                $_SESSION['pending_pw_captcha_ready'],
                $_SESSION['pending_pw_captcha_attempts']
            );

            $user = $this->db->fetch("SELECT id, email FROM users WHERE id = ?", [(int)$pending['user_id']]);
            if (!$user) {
                unset($_SESSION['pending_pw_change']);
                $this->flash('error', 'Account not found. Please submit the form again.');
                $this->redirect($this->passwordChangeFallbackUrl());
                return;
            }

            $this->db->update('users', [
                'password' => $pending['password_hash'],
                'password_changed_at' => serverDateTime(),
                'locked_until' => null,
                'login_attempts' => 0,
            ], "id = ?", [(int)$user['id']]);

            $this->savePasswordHistory((int)$user['id'], $pending['password_hash']);

            $this->db->update('password_resets', ['used' => 1], "email = ?", [$user['email']]);

            $this->logActivity(
                $mode === 'settings' ? 'change_password' : 'reset_password',
                ($mode === 'settings' ? 'Password changed via settings for: ' : 'Password changed via forgot password for: ') . $user['email']
            );

            $redirectTarget = $mode === 'settings' ? $this->passwordChangeFallbackUrl() : '/login';

            unset($_SESSION['pending_pw_change']);

            $this->sendPasswordChangedNotification($user['email']);

            if ($mode === 'settings') {
                $this->flash('success', 'Password changed successfully.');
                $this->redirect($redirectTarget);
            } else {
                $this->flash('success', 'Your password has been changed. Please login with your new password.');
                $this->redirect('/login');
            }
            return;
        }

        $this->view('auth.captcha_password_change', [
            'pageTitle'     => 'Security Check',
            'siteKey'       => $this->recaptcha->getSiteKey(),
            'mode'          => $mode,
            'flashMessages' => $this->getFlashMessages(),
        ], 'public');
    }

    public function resendPasswordChangeCode(): void {
        if (!$this->isPost()) {
            $this->redirect('/verify-password-change');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid CSRF token. Please try again.');
            $this->redirect('/verify-password-change');
            return;
        }

        $pending = $this->pendingPasswordChange();
        if (!$pending) {
            $this->flash('error', 'No pending password change found.');
            $this->redirect($this->passwordChangeFallbackUrl());
            return;
        }

        $latest = $this->db->fetch(
            "SELECT created_at FROM email_verification_codes WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            [(int)$pending['user_id']]
        );

        if ($latest && (serverTimestamp() - strtotime($latest['created_at'])) < self::OTP_RESEND_COOLDOWN_SECONDS) {
            $remaining = self::OTP_RESEND_COOLDOWN_SECONDS - (serverTimestamp() - strtotime($latest['created_at']));
            $this->flash('error', 'Please wait ' . $remaining . ' second' . ($remaining !== 1 ? 's' : '') . ' before requesting a new code.');
            $this->redirect('/verify-password-change');
            return;
        }

        $recentCount = $this->db->count(
            'email_verification_codes',
            "user_id = ? AND created_at > ?",
            [(int)$pending['user_id'], serverNow()->modify('-60 minutes')->format('Y-m-d H:i:s')]
        );
        if ($recentCount >= self::OTP_MAX_CODES_PER_HOUR) {
            $this->flash('error', 'Too many resend requests. Please try again in an hour.');
            $this->redirect('/verify-password-change');
            return;
        }

        if ($this->sendPasswordChangeVerificationCode((int)$pending['user_id'], $pending['email'])) {
            $this->flash('success', 'A new verification code has been sent to your email.');
        } else {
            $this->flash('error', 'We could not send a new verification code right now. Please try again later.');
        }
        $this->redirect('/verify-password-change');
    }

    public function logout(): void {
        $this->logActivity('logout', 'User logged out');
        session_destroy();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        $this->redirect('/login');
    }

    public function forgotPassword(): void {
        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/forgot-password');
                return;
            }

            $recaptchaToken = $this->input('g-recaptcha-response', '');
            $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (empty($recaptchaToken)) {
                $this->flash('error', 'Please complete the reCAPTCHA verification.');
                $this->redirect('/forgot-password');
                return;
            }

            $valid = $this->recaptcha->verify($recaptchaToken, $remoteIp);

            if (!$valid) {
                $this->flash('error', 'reCAPTCHA verification failed. Please try again.');
                $this->redirect('/forgot-password');
                return;
            }

            $email = strtolower(trim($this->input('email', '')));
            $currentPassword = (string)$this->input('current_password', '');
            $password = (string)$this->input('password', '');
            $passwordConfirm = (string)$this->input('password_confirmation', '');

            if ($email === '' || !isValidGmailEmail($email)) {
                $this->flash('error', gmailEmailError('Registered email address', $email));
                $this->redirect('/forgot-password');
                return;
            }

            if ($currentPassword === '') {
                $this->flash('error', 'Current password is required.');
                $this->redirect('/forgot-password');
                return;
            }

            $user = $this->db->fetch("SELECT id, email, password FROM users WHERE email = ?", [$email]);

            if (!$user) {
                $this->flash('error', 'This email is not registered. Please check the email you entered.');
                $this->redirect('/forgot-password');
                return;
            }

            if (!password_verify($currentPassword, $user['password'])) {
                $this->flash('error', 'The current password you entered is incorrect.');
                $this->redirect('/forgot-password');
                return;
            }

            $pwErrors = [];
            if ($password === '') $pwErrors[] = 'Password is required.';
            else {
                if (strlen($password) < 8) $pwErrors[] = 'at least 8 characters';
                if (strlen($password) > 128) $pwErrors[] = 'no more than 128 characters';
                if (!preg_match('/[A-Z]/', $password)) $pwErrors[] = 'one uppercase letter';
                if (!preg_match('/[a-z]/', $password)) $pwErrors[] = 'one lowercase letter';
                if (!preg_match('/[0-9]/', $password)) $pwErrors[] = 'one number';
                if (!preg_match('/[^A-Za-z0-9]/', $password)) $pwErrors[] = 'one special character';
                if ($password !== $passwordConfirm) $pwErrors[] = 'Passwords do not match.';
            }

            if (!empty($pwErrors)) {
                $this->flash('error', 'Password must contain ' . implode(', ', $pwErrors) . '.');
                $this->redirect('/forgot-password');
                return;
            }

            if ($this->isPasswordReused((int)$user['id'], $password)) {
                $this->flash('error', 'You cannot reuse a previous password. Please choose a new one.');
                $this->redirect('/forgot-password');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $_SESSION['pending_pw_change'] = [
                'user_id'       => (int)$user['id'],
                'email'         => $user['email'],
                'password_hash' => $hashedPassword,
                'mode'          => 'forgot',
            ];

            if ($this->sendPasswordChangeVerificationCode((int)$user['id'], $user['email'])) {
                $this->flash('info', 'Your password will be changed once you enter the 6-digit code we sent to your email.');
            } else {
                $this->flash('warning', 'We could not send a verification code right now. Please click Resend Code to try again.');
            }
            $this->redirect('/verify-password-change');
            return;
        }

        $data = [
            'pageTitle' => 'Forgot Password',
            'flashMessages' => $this->getFlashMessages(),
            'siteKey' => $this->recaptcha->getSiteKey(),
        ];
        $this->view('auth.forgot_password', $data, 'public');
    }

    public function resetPassword(): void {
        $tokenFromInput = trim($this->input('token', ''));
        $tokenFromSession = $_SESSION['reset_token'] ?? '';

        $token = '';
        $tokenSource = '';

        if ($this->isGet()) {
            if ($tokenFromInput === '' || !preg_match('/^[a-f0-9]{64}$/', $tokenFromInput)) {
                $this->flash('error', 'Invalid reset token. Please request a new reset link.');
                $this->redirect('/login');
                return;
            }

            $reset = $this->db->fetch(
                "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()",
                [$tokenFromInput]
            );

            if (!$reset) {
                $this->flash('error', 'Invalid or expired reset token. Please request a new reset link.');
                $this->redirect('/login');
                return;
            }

            $_SESSION['reset_token'] = $tokenFromInput;
            $_SESSION['reset_token_email'] = $reset['email'];
            $token = $tokenFromInput;
            $tokenSource = 'input';
        } else {
            if ($tokenFromSession === '' || !preg_match('/^[a-f0-9]{64}$/', $tokenFromSession)) {
                $this->flash('error', 'Invalid or expired reset session. Please request a new reset link.');
                unset($_SESSION['reset_token'], $_SESSION['reset_token_email']);
                $this->redirect('/login');
                return;
            }

            $reset = $this->db->fetch(
                "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()",
                [$tokenFromSession]
            );

            if (!$reset) {
                $this->flash('error', 'Invalid or expired reset token. Please request a new reset link.');
                unset($_SESSION['reset_token'], $_SESSION['reset_token_email']);
                $this->redirect('/login');
                return;
            }

            $token = $tokenFromSession;
            $tokenSource = 'session';
        }

        $user = $this->db->fetch("SELECT id, email FROM users WHERE email = ?", [$reset['email']]);

        if ($this->isPost()) {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid CSRF token. Please try again.');
                $this->redirect('/reset-password');
                return;
            }

            $password = (string)$this->input('password', '');
            $passwordConfirm = (string)$this->input('password_confirmation', '');

            $pwErrors = [];
            if ($password === '') $pwErrors[] = 'Password is required.';
            else {
                if (strlen($password) < 8) $pwErrors[] = 'at least 8 characters';
                if (strlen($password) > 128) $pwErrors[] = 'no more than 128 characters';
                if (!preg_match('/[A-Z]/', $password)) $pwErrors[] = 'one uppercase letter';
                if (!preg_match('/[a-z]/', $password)) $pwErrors[] = 'one lowercase letter';
                if (!preg_match('/[0-9]/', $password)) $pwErrors[] = 'one number';
                if (!preg_match('/[^A-Za-z0-9]/', $password)) $pwErrors[] = 'one special character';
                if ($password !== $passwordConfirm) $pwErrors[] = 'Passwords do not match.';
            }

            if (!empty($pwErrors)) {
                $this->flash('error', 'Password must contain ' . implode(', ', $pwErrors) . '.');
                $this->redirect('/reset-password');
                return;
            }

            if ($user && $this->isPasswordReused((int)$user['id'], $password)) {
                $this->flash('error', 'You cannot reuse a previous password. Please choose a new one.');
                $this->redirect('/reset-password');
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $this->db->update('users', [
                'password' => $hashedPassword,
                'password_changed_at' => serverDateTime(),
                'locked_until' => null,
                'login_attempts' => 0,
            ], "email = ?", [$reset['email']]);

            if ($user) {
                $this->savePasswordHistory((int)$user['id'], $hashedPassword);
            }

            $this->db->update('password_resets', ['used' => 1], "email = ?", [$reset['email']]);

            $this->logActivity('reset_password', 'Password reset completed for: ' . $reset['email']);

            $this->sendPasswordChangedNotification($reset['email']);

            unset($_SESSION['reset_token'], $_SESSION['reset_token_email']);

            $this->flash('success', 'Password has been reset. Please login with your new password.');
            $this->redirect('/login');
            return;
        }

        $data = [
            'pageTitle' => 'Reset Password',
            'flashMessages' => $this->getFlashMessages(),
        ];
        $this->view('auth.reset_password', $data, 'public');
    }

    private function redirectBasedOnRole(): void {
        $role = $_SESSION['user_role'] ?? '';
        switch ($role) {
            case 'super_admin':
                $this->redirect('/admin/dashboard');
                break;
            case 'manager':
                $this->redirect('/manager/dashboard');
                break;
            default:
                $this->redirect('/student/dashboard');
                break;
        }
    }
}
