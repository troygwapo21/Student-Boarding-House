<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php $old = $old ?? []; $errors = $errors ?? []; ?>
<?php
function fieldError(string $field, array $errors): ?string {
    $map = [
        'first_name'           => ['first name is required'],
        'middle_name'          => [],
        'last_name'            => ['last name is required'],
        'suffix'               => [],
        'gender'               => ['gender is required'],
        'date_of_birth'        => ['date of birth is required', 'at least 18', 'or below to register', 'date of birth is invalid'],
        'civil_status'         => ['civil status is required'],
        'nationality'          => ['nationality is required'],
        'profile_picture'      => [],
        'student_id_number'    => ['student id number is already registered'],
        'school_university'    => ['school/college is required'],
        'course_program'       => ['course/program is required', 'invalid course'],
        'year_level'           => ['year level is required'],
        'school_id_upload'     => [],
        'email'                => ['email address is required', 'email address "', 'characters or fewer', 'gmail is already registered'],
        'phone'                => ['mobile number is required', '^mobile number is invalid'],
        'house_unit'           => [],
        'street'               => ['street must not exceed'],
        'barangay'             => ['^barangay is required', 'select a valid barangay'],
        'municipality_city'    => ['^municipality/city is required', 'select a valid municipality/city'],
        'province'             => ['^province is required', 'select a valid province'],
        'zip_code'             => ['^zip code is required', '^zip code must be'],
        'username'             => ['username is required', 'at least 4 characters', 'username is already registered'],
        'password'             => ['password is required', 'at least 8 characters', 'uppercase', 'lowercase', 'one number', 'special character'],
        'password_confirmation'=> ['passwords do not match'],
        'guardian_first_name'  => ['guardian first name is required'],
        'guardian_middle_name' => [],
        'guardian_last_name'   => ['guardian last name is required'],
        'guardian_relationship'=> ['guardian relationship is required'],
        'guardian_mobile'      => ['guardian mobile number is required', 'guardian mobile number is invalid'],
        'guardian_alt_contact' => ['alternative contact'],
        'guardian_email'       => ['guardian email'],
        'guardian_house_unit'  => [],
        'guardian_street'      => [],
        'guardian_barangay'    => ['guardian barangay is required'],
        'guardian_municipality_city' => ['guardian municipality/city is required'],
        'guardian_province'    => ['guardian province is required'],
        'guardian_zip_code'    => ['guardian zip code is required'],
        'terms'                => ['terms & conditions', 'privacy policy'],
    ];
    foreach ($errors as $err) {
        $lc = strtolower($err);
        foreach (($map[$field] ?? []) as $kw) {
            if ($kw !== '' && $kw[0] === '^') {
                if (str_starts_with($lc, substr($kw, 1))) return $err;
            } elseif (str_contains($lc, $kw)) {
                return $err;
            }
        }
    }
    return null;
}
function fe(string $f, array $e): string { $msg = fieldError($f, $e); return $msg ? '<div class="field-error"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($msg) . '</div>' : ''; }
function hasErr(string $f, array $e): string { return fieldError($f, $e) ? ' is-invalid' : ''; }
?>

<section class="auth-section" style="min-height:calc(100vh - 72px);display:flex;align-items:flex-start;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);padding:2rem 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="auth-card">
                    <div class="row g-0">
                        <!-- Left Panel -->
                        <div class="col-lg-4 d-none d-lg-flex">
                            <div class="auth-visual">
                                <div class="auth-visual-shapes" aria-hidden="true">
                                    <div class="auth-shape auth-shape-1"></div>
                                    <div class="auth-shape auth-shape-2"></div>
                                    <div class="auth-shape auth-shape-3"></div>
                                </div>
                                <div class="auth-visual-content">
                                    <div class="auth-visual-icon"><i class="fas fa-user-plus"></i></div>
                                    <h2>Join <?= e(getSiteName()) ?></h2>
                                    <p>Create your account and find the perfect boarding house room today.</p>
                                    <div class="auth-visual-features">
                                        <div class="auth-feature"><i class="fas fa-check-circle"></i><span>Browse Available Rooms</span></div>
                                        <div class="auth-feature"><i class="fas fa-check-circle"></i><span>Easy Online Reservations</span></div>
                                        <div class="auth-feature"><i class="fas fa-check-circle"></i><span>Secure Payment Tracking</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel -->
                        <div class="col-lg-8">
                            <div class="auth-form-panel">
                                <div class="auth-form-header">
                                    <a href="<?= url('/') ?>" class="auth-form-brand">
                                        <span class="auth-form-brand-icon"><i class="fas fa-building"></i></span>
                                        <span><?= e(getSiteName()) ?></span>
                                    </a>
                                    <a href="<?= url('/') ?>" class="auth-close-btn" title="Close" aria-label="Close">&times;</a>
                                </div>

                                <div class="auth-form-body" style="padding:1.5rem 2rem;">
                                    <h3 class="auth-form-title">Create Account</h3>
                                    <p class="auth-form-subtitle">Register as a new student resident</p>

                                    <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger message-autodismiss py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Please check the blank.
                                    </div>
                                    <?php endif; ?>

                                    <form action="<?= url('/register') ?>" method="POST" enctype="multipart/form-data" id="regForm" novalidate>
                                        <?= csrf_field() ?>

                                        <!-- ============ SECTION 1: PERSONAL INFORMATION ============ -->
                                        <div class="reg-section">
                                            <h6 class="reg-section-title"><i class="fas fa-user me-2"></i>Personal Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="first_name" class="form-control<?= hasErr('first_name',$errors) ?>" placeholder="First name" required value="<?= e($old['first_name'] ?? '') ?>">
                                                    <?= fe('first_name',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Middle Name</label>
                                                    <input type="text" name="middle_name" class="form-control" placeholder="Middle name" value="<?= e($old['middle_name'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="last_name" class="form-control<?= hasErr('last_name',$errors) ?>" placeholder="Last name" required value="<?= e($old['last_name'] ?? '') ?>">
                                                    <?= fe('last_name',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Suffix</label>
                                                    <select name="suffix" class="form-select">
                                                        <option value="">-- None --</option>
                                                        <?php foreach(['Jr.','Sr.','II','III','IV','V'] as $s): ?>
                                                        <option value="<?= $s ?>" <?= ($old['suffix'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                                    <select name="gender" class="form-select<?= hasErr('gender',$errors) ?>" required>
                                                        <option value="">-- Select --</option>
                                                        <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                                        <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                                    </select>
                                                    <?= fe('gender',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                                    <?php $dobMin = serverNow()->modify('-100 years')->format('Y-m-d'); $dobMax = serverNow()->modify('-18 years')->format('Y-m-d'); ?>
                                                    <input type="date" name="date_of_birth" class="form-control<?= hasErr('date_of_birth',$errors) ?>" id="regDob" required min="<?= $dobMin ?>" max="<?= $dobMax ?>" value="<?= e($old['date_of_birth'] ?? '') ?>">
                                                    <div class="form-text">Ages 18 to 100 only.</div>
                                                    <?= fe('date_of_birth',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Age</label>
                                                    <input type="text" class="form-control" id="regAge" readonly placeholder="Auto-computed" style="background:#f1f5f9;">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
                                                    <select name="civil_status" class="form-select<?= hasErr('civil_status',$errors) ?>" required>
                                                        <option value="">-- Select --</option>
                                                        <?php foreach(['single'=>'Single','married'=>'Married','widowed'=>'Widowed','separated'=>'Separated','divorced'=>'Divorced'] as $v=>$l): ?>
                                                        <option value="<?= $v ?>" <?= ($old['civil_status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?= fe('civil_status',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Nationality <span class="text-danger">*</span></label>
                                                    <select name="nationality" class="form-select<?= hasErr('nationality',$errors) ?>" required>
                                                        <option value="">-- Select Nationality --</option>
                                                        <option value="Filipino" <?= ($old['nationality'] ?? 'Filipino') === 'Filipino' ? 'selected' : '' ?>>Filipino</option>
                                                        <option value="American" <?= ($old['nationality'] ?? '') === 'American' ? 'selected' : '' ?>>American</option>
                                                        <option value="Australian" <?= ($old['nationality'] ?? '') === 'Australian' ? 'selected' : '' ?>>Australian</option>
                                                        <option value="British" <?= ($old['nationality'] ?? '') === 'British' ? 'selected' : '' ?>>British</option>
                                                        <option value="Canadian" <?= ($old['nationality'] ?? '') === 'Canadian' ? 'selected' : '' ?>>Canadian</option>
                                                        <option value="Other" <?= ($old['nationality'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                                    </select>
                                                    <?= fe('nationality',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Profile Photo</label>
                                                    <input type="file" name="profile_picture" class="form-control" accept="image/*">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============ SECTION 2: STUDENT INFORMATION ============ -->
                                        <div class="reg-section">
                                            <h6 class="reg-section-title"><i class="fas fa-graduation-cap me-2"></i>Student Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Student ID Number</label>
                                                    <input type="text" name="student_id_number" class="form-control" value="<?= e($generatedStudentId ?? ($old['student_id_number'] ?? '')) ?>" readonly style="background:#f1f5f9;">
                                                    <div class="form-text">Auto-generated upon registration.</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">School/College <span class="text-danger">*</span></label>
                                                    <select name="school_university" class="form-select<?= hasErr('school_university',$errors) ?>" required>
                                                        <option value="">-- Select School/College --</option>
                                                        <?php
                                                        $schools = [
                                                            'Madridejos Community College'                            => 'Madridejos Community College (MCC)',
                                                            'Salazar Colleges of Science and Institute of Technology' => 'Salazar Colleges of Science and Institute of Technology (SCSIT)',
                                                            'Cebu North Plains College'                               => 'Cebu North Plains College (CNPC)',
                                                            'Cebu Technological University'                           => 'Cebu Technological University (CTU)',
                                                            'Other'                                                   => 'Other',
                                                        ];
                                                        $selSchool = $old['school_university'] ?? '';
                                                        foreach ($schools as $val=>$label):
                                                        ?>
                                                            <option value="<?= e($val) ?>" <?= $selSchool===$val ? 'selected' : '' ?>><?= e($label) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?= fe('school_university',$errors) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Course/Program <span class="text-danger">*</span></label>
                                                    <select name="course_program" class="form-select<?= hasErr('course_program',$errors) ?>" required>
                                                        <option value="">-- Select Course Program --</option>
                                                        <?php
                                                        $courses = ['BSIT'=>'BSIT','BSHM'=>'BSHM','BSED'=>'BSED','BSBA'=>'BSBA','BSCE'=>'BSCE','BSCRIM'=>'BSCRIM','Other'=>'Other'];
                                                        $labels = ['BSIT'=>'Bachelor of Science in Information Technology (BSIT)','BSHM'=>'Bachelor of Science in Hospitality Management (BSHM)','BSED'=>'Bachelor of Secondary Education (BSED)','BSBA'=>'Bachelor of Science in Business Administration (BSBA)','BSCE'=>'Bachelor of Science in Civil Engineering (BSCE)','BSCRIM'=>'Bachelor of Science in Criminology (BSCRIM)','Other'=>'Other'];
                                                        foreach ($courses as $k=>$v): ?>
                                                        <option value="<?= $k ?>" <?= ($old['course_program'] ?? '') === $k ? 'selected' : '' ?>><?= $labels[$k] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?= fe('course_program',$errors) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                                                    <select name="year_level" class="form-select<?= hasErr('year_level',$errors) ?>" required>
                                                        <option value="">-- Select Year Level --</option>
                                                        <?php foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $yl): ?>
                                                        <option value="<?= $yl ?>" <?= ($old['year_level'] ?? '') === $yl ? 'selected' : '' ?>><?= $yl ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?= fe('year_level',$errors) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">School ID Upload</label>
                                                    <input type="file" name="school_id_upload" class="form-control" accept="image/*,.pdf">
                                                    <div class="form-text">Upload a photo/PDF of your school ID (optional).</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============ SECTION 3: CONTACT INFORMATION ============ -->
                                        <div class="reg-section">
                                            <h6 class="reg-section-title"><i class="fas fa-address-card me-2"></i>Contact Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" name="email" class="form-control<?= hasErr('email',$errors) ?>" placeholder="example@gmail.com" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Email Address: Please enter a valid Gmail address ending with @gmail.com." value="<?= e($old['email'] ?? '') ?>">
                                                    <?= fe('email',$errors) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                                                    <input type="tel" name="phone" class="form-control<?= hasErr('phone',$errors) ?>" placeholder="09*********" required inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Please enter a valid 11-digit Philippine mobile number starting with 09." value="<?= e($old['phone'] ?? '') ?>">
                                                    <?= fe('phone',$errors) ?>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Complete Address <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Street</label>
                                                    <input type="text" name="street" class="form-control" placeholder="Street" maxlength="255" value="<?= e($old['street'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                                                    <select name="province" class="form-select<?= hasErr('province',$errors) ?>" required>
                                                        <option value="Cebu" selected>Cebu</option>
                                                    </select>
                                                    <?= fe('province',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Municipality <span class="text-danger">*</span></label>
                                                    <select name="municipality_city" id="studentMunicipality" class="form-select<?= hasErr('municipality_city',$errors) ?>" required>
                                                        <option value="">Select Municipality</option>
                                                        <option value="Bantayan" <?= ($old['municipality_city'] ?? '') === 'Bantayan' ? 'selected' : '' ?>>Bantayan</option>
                                                        <option value="Madridejos" <?= ($old['municipality_city'] ?? '') === 'Madridejos' ? 'selected' : '' ?>>Madridejos</option>
                                                        <option value="Santa Fe" <?= ($old['municipality_city'] ?? '') === 'Santa Fe' ? 'selected' : '' ?>>Santa Fe</option>
                                                    </select>
                                                    <?= fe('municipality_city',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                                                    <select name="barangay" id="studentBarangay" class="form-select<?= hasErr('barangay',$errors) ?>" required disabled>
                                                        <option value="">Select Barangay</option>
                                                    </select>
                                                    <?= fe('barangay',$errors) ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small">ZIP Code <span class="text-danger">*</span></label>
                                                    <input type="text" name="zip_code" id="studentZipCode" class="form-control<?= hasErr('zip_code',$errors) ?>" placeholder="ZIP Code" required maxlength="4" pattern="[0-9]{4}" value="<?= e($old['zip_code'] ?? '') ?>" readonly style="background:#f1f5f9;">
                                                    <?= fe('zip_code',$errors) ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============ SECTION 4: ACCOUNT INFORMATION ============ -->
                                        <div class="reg-section">
                                            <h6 class="reg-section-title"><i class="fas fa-key me-2"></i>Account Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                                    <input type="text" name="username" class="form-control<?= hasErr('username',$errors) ?>" placeholder="Choose a username" required minlength="4" maxlength="50" value="<?= e($old['username'] ?? '') ?>">
                                                    <?= fe('username',$errors) ?>
                                                </div>
                                                <div class="col-md-6"></div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                                    <div class="pw-field-wrap">
                                                        <div class="pw-input-group">
                                                            <input type="password" name="password" class="form-control<?= hasErr('password',$errors) ?>" placeholder="Create a strong password" required minlength="8" data-pw-validate autocomplete="new-password">
                                                            <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                                        </div>
                                                        <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                                                    </div>
                                                    <?= fe('password',$errors) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                                    <div class="pw-field-wrap">
                                                        <div class="pw-input-group">
                                                            <input type="password" name="password_confirmation" class="form-control<?= hasErr('password_confirmation',$errors) ?>" placeholder="Re-enter password" required data-pw-match="password" autocomplete="new-password">
                                                            <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                                        </div>
                                                    </div>
                                                    <?= fe('password_confirmation',$errors) ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============ SECTION 5: GUARDIAN INFORMATION ============ -->
                                        <div class="reg-section">
                                            <h6 class="reg-section-title"><i class="fas fa-shield-alt me-2"></i>Guardian Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Guardian First Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="guardian_first_name" class="form-control<?= hasErr('guardian_first_name',$errors) ?>" placeholder="First name" required value="<?= e($old['guardian_first_name'] ?? '') ?>">
                                                    <?= fe('guardian_first_name',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Guardian Middle Name</label>
                                                    <input type="text" name="guardian_middle_name" class="form-control" placeholder="Middle name" value="<?= e($old['guardian_middle_name'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Guardian Last Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="guardian_last_name" class="form-control<?= hasErr('guardian_last_name',$errors) ?>" placeholder="Last name" required value="<?= e($old['guardian_last_name'] ?? '') ?>">
                                                    <?= fe('guardian_last_name',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Relationship <span class="text-danger">*</span></label>
                                                    <select name="guardian_relationship" class="form-select<?= hasErr('guardian_relationship',$errors) ?>" required>
                                                        <option value="">-- Select --</option>
                                                        <?php foreach(['Father','Mother','Guardian','Sibling','Spouse','Uncle/Aunt','Grandparent','Other'] as $r): ?>
                                                        <option value="<?= $r ?>" <?= ($old['guardian_relationship'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?= fe('guardian_relationship',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                                                    <input type="tel" name="guardian_mobile" class="form-control<?= hasErr('guardian_mobile',$errors) ?>" placeholder="09123456789" required inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Guardian Mobile Number: Please enter a valid 11-digit Philippine mobile number starting with 09." value="<?= e($old['guardian_mobile'] ?? '') ?>">
                                                    <?= fe('guardian_mobile',$errors) ?>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Email Address</label>
                                                    <input type="email" name="guardian_email" class="form-control<?= hasErr('guardian_email',$errors) ?>" placeholder="Guardian email (optional, e.g. example@gmail.com)" pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Guardian Email: Please enter a valid Gmail address ending with @gmail.com." value="<?= e($old['guardian_email'] ?? '') ?>">
                                                    <?= fe('guardian_email',$errors) ?>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Guardian Address <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Street</label>
                                                    <input type="text" name="guardian_street" class="form-control" placeholder="Street" value="<?= e($old['guardian_street'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                                                    <select name="guardian_province" class="form-select<?= hasErr('guardian_province',$errors) ?>" required>
                                                        <option value="Cebu" selected>Cebu</option>
                                                    </select>
                                                    <?= fe('guardian_province',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Municipality <span class="text-danger">*</span></label>
                                                    <select name="guardian_municipality_city" id="guardianMunicipality" class="form-select<?= hasErr('guardian_municipality_city',$errors) ?>" required>
                                                        <option value="">Select Municipality</option>
                                                        <option value="Bantayan" <?= ($old['guardian_municipality_city'] ?? '') === 'Bantayan' ? 'selected' : '' ?>>Bantayan</option>
                                                        <option value="Madridejos" <?= ($old['guardian_municipality_city'] ?? '') === 'Madridejos' ? 'selected' : '' ?>>Madridejos</option>
                                                        <option value="Santa Fe" <?= ($old['guardian_municipality_city'] ?? '') === 'Santa Fe' ? 'selected' : '' ?>>Santa Fe</option>
                                                    </select>
                                                    <?= fe('guardian_municipality_city',$errors) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                                                    <select name="guardian_barangay" id="guardianBarangay" class="form-select<?= hasErr('guardian_barangay',$errors) ?>" required disabled>
                                                        <option value="">Select Barangay</option>
                                                    </select>
                                                    <?= fe('guardian_barangay',$errors) ?>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small">ZIP Code <span class="text-danger">*</span></label>
                                                    <input type="text" name="guardian_zip_code" id="guardianZipCode" class="form-control<?= hasErr('guardian_zip_code',$errors) ?>" placeholder="ZIP Code" required maxlength="4" pattern="[0-9]{4}" value="<?= e($old['guardian_zip_code'] ?? '') ?>" readonly style="background:#f1f5f9;">
                                                    <?= fe('guardian_zip_code',$errors) ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============ TERMS & SUBMIT ============ -->
                                        <div class="reg-section" style="padding-top:0;">
                                            <div class="form-check mb-3">
                                                <input type="checkbox" name="terms" class="form-check-input<?= hasErr('terms',$errors) ?>" id="terms" required style="border-radius:4px;">
                                                <label class="form-check-label small" for="terms" style="color:var(--gray-600);font-weight:500;">
                                                    I agree to the <a href="<?= url('/terms') ?>" class="text-primary" target="_blank">Terms & Conditions</a> and <a href="<?= url('/privacy') ?>" class="text-primary" target="_blank">Privacy Policy</a>
                                                </label>
                                                <?= fe('terms',$errors) ?>
                                            </div>
                                            <button type="submit" class="auth-submit-btn">
                                                <i class="fas fa-user-plus"></i>
                                                <span>Create Account</span>
                                            </button>
                                        </div>
                                    </form>

                                    <div class="auth-divider">
                                        <span>Already have an account?</span>
                                    </div>

                                    <a href="<?= url('/login') ?>" class="auth-alt-btn">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Sign In Instead</span>
                                    </a>
                                </div>

                                <div class="auth-form-footer">
                                    <a href="<?= url('/') ?>"><i class="fas fa-arrow-left"></i> Back to Home</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.reg-section{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;}
.reg-section-title{font-weight:700;color:#1e293b;margin-bottom:1rem;font-size:.95rem;border-bottom:2px solid #e2e8f0;padding-bottom:.5rem;}
.reg-section .form-label{font-size:.85rem;color:#475569;margin-bottom:.25rem;}
.reg-section .form-control,.reg-section .form-select{border-radius:8px;font-size:.9rem;}
.reg-section .pw-input-group .form-control{border-radius:16px;padding:.8rem 1rem;}
.reg-section .pw-show-cb{font-size:.78rem;}
.reg-section .pw-validation{padding:2px 0;font-size:.78rem;line-height:1.5;}
.reg-section .pw-validation i{width:14px;text-align:center;}
.reg-section .pw-strength-bar{height:4px;margin-top:.35rem;}
.reg-section .pw-strength-fill{height:100%;border-radius:2px;transition:width .3s ease,background .3s ease;}
.auth-form-body{overflow-y:auto;max-height:80vh;}
.auth-form-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;}
.auth-close-btn{width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:300;color:#64748b;background:none;border:none;border-radius:8px;cursor:pointer;transition:all .2s;line-height:1;text-decoration:none;}
.auth-close-btn:hover{color:#dc2626;background:#fef2f2;}
.field-error{color:#dc2626;font-size:.78rem;margin-top:.25rem;font-weight:500;}
.form-control.is-invalid,.form-select.is-invalid{border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.15);}
.alert-danger{background:#fef2f2;border-color:#fecaca;color:#991b1b;}
</style>

<script>
// Single source of truth: injected from helpers.php and validated server-side.
const barangays = <?= json_encode(locationBarangays()) ?>;
const zipCodes = {
    'Bantayan': '6052',
    'Madridejos': '6053',
    'Santa Fe': '6047'
};

function setupCascading(muniId, brgyId, zipId, oldBrgy) {
    const muni = document.getElementById(muniId);
    const brgy = document.getElementById(brgyId);
    const zip = document.getElementById(zipId);
    if (!muni || !brgy || !zip) return;

    muni.addEventListener('change', function() {
        brgy.innerHTML = '<option value="">-- Select Barangay --</option>';
        const list = barangays[this.value] || [];
        list.forEach(function(b) {
            const opt = document.createElement('option');
            opt.value = b;
            opt.textContent = b;
            brgy.appendChild(opt);
        });
        brgy.disabled = list.length === 0;
        if (oldBrgy && list.indexOf(oldBrgy) !== -1) {
            brgy.value = oldBrgy;
        }

        // Auto-fill zip code and make it read-only
        const selectedMuni = this.value;
        if (selectedMuni && zipCodes[selectedMuni]) {
            zip.value = zipCodes[selectedMuni];
            zip.readOnly = true;
            zip.style.backgroundColor = '#f1f5f9';
        } else {
            zip.value = '';
            zip.readOnly = false;
            zip.style.backgroundColor = '';
        }
    });

    if (muni.value && barangays[muni.value]) {
        muni.dispatchEvent(new Event('change'));
        if (oldBrgy) brgy.value = oldBrgy;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setupCascading('studentMunicipality', 'studentBarangay', 'studentZipCode', <?= json_encode($old['barangay'] ?? '') ?>);
    setupCascading('guardianMunicipality', 'guardianBarangay', 'guardianZipCode', <?= json_encode($old['guardian_barangay'] ?? '') ?>);

    // Pre-fill zip code if municipality already selected (e.g., after validation error)
    const studentMuni = document.getElementById('studentMunicipality');
    const studentZip = document.getElementById('studentZipCode');
    if (studentMuni && studentMuni.value && zipCodes[studentMuni.value]) {
        studentZip.value = zipCodes[studentMuni.value];
        studentZip.readOnly = true;
        studentZip.style.backgroundColor = '#f1f5f9';
    }

    const guardianMuni = document.getElementById('guardianMunicipality');
    const guardianZip = document.getElementById('guardianZipCode');
    if (guardianMuni && guardianMuni.value && zipCodes[guardianMuni.value]) {
        guardianZip.value = zipCodes[guardianMuni.value];
        guardianZip.readOnly = true;
        guardianZip.style.backgroundColor = '#f1f5f9';
    }
});
</script>
