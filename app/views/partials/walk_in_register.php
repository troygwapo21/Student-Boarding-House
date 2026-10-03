<?php $old = $old ?? []; $errors = $errors ?? []; $baseUrl = $baseUrl ?? '/admin'; ?>
<?php
function walkInFieldError(string $field, array $errors): ?string {
    static $map = [
        'first_name'           => ['^first name is required'],
        'last_name'            => ['^last name is required'],
        'gender'               => ['gender is required'],
        'date_of_birth'        => ['date of birth is required'],
        'civil_status'         => ['civil status is required'],
        'nationality'          => ['nationality is required'],
        'school_university'    => ['school/college is required'],
        'course_program'       => ['course/program is required', 'invalid course'],
        'course_program_other' => ['specify your course'],
        'year_level'           => ['year level is required'],
        'email'                => ['email address is required', 'email address "', 'gmail is already registered', 'gmail address is already registered'],
        'phone'                => ['^mobile number is required', '^mobile number is invalid'],
        'province'             => ['^province is required'],
        'municipality_city'    => ['^municipality/city is required'],
        'barangay'             => ['^barangay is required'],
        'zip_code'             => ['^zip code is required'],
        'room_id'              => ['select a room', 'room is no longer available'],
        'move_in_date'         => ['move-in date'],
        'valid_id'             => ['valid id upload is required', 'valid id must be', 'valid id upload could not be saved'],
        'expected_duration'    => [],
        'payment_method'       => ['payment method is required'],
        'payment_received'     => ['amount received'],
        'payment_reference'    => ['^reference / or number is required'],
        'username'             => ['username must be', 'username is already registered'],
        'password'             => ['password must be', 'password is required'],
        'verification_code'    => ['^verification code', '^the verification code'],
        'guardian_first_name'  => ['guardian first name is required'],
        'guardian_last_name'   => ['guardian last name is required'],
        'guardian_relationship'=> ['guardian relationship is required'],
        'guardian_mobile'      => ['guardian mobile number is required', 'guardian mobile number is invalid'],
        'guardian_alt_contact' => ['alternative contact'],
        'guardian_email'       => ['guardian email'],
        'guardian_municipality_city' => ['guardian municipality/city is required'],
        'guardian_province'    => ['^guardian province is required'],
        'guardian_barangay'    => ['guardian barangay is required'],
        'guardian_zip_code'    => ['guardian zip code is required'],
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
function fe(string $f, array $e): string { $msg = walkInFieldError($f, $e); return $msg ? '<div class="field-error"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($msg) . '</div>' : ''; }
function hasErr(string $f, array $e): string { return walkInFieldError($f, $e) ? ' is-invalid' : ''; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>Walk-In Registration Form</h1>
    <a href="<?= url($baseUrl . '/students') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Students</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger message-autodismiss py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
    <i class="fas fa-exclamation-triangle me-1"></i>Please check the following:
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if (!empty($codeSent)): ?>
<div class="alert alert-success message-autodismiss py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
    <i class="fas fa-check-circle me-1"></i>A 6-digit verification code was sent to <strong><?= e($codeSent) ?></strong>. It expires in 5 minutes.
</div>
<?php endif; ?>

<?php if (!empty($codeError)): ?>
<div class="alert alert-danger message-autodismiss py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
    <i class="fas fa-exclamation-circle me-1"></i><?= e($codeError) ?>
</div>
<?php endif; ?>

<div class="card border-0 shadow-lg mb-4 walkin-card" style="border-radius:16px;overflow:hidden;">
    <div class="walkin-header">
        <div class="d-flex align-items-center">
            <div class="walkin-header-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <h4 class="mb-0 text-white fw-bold">Walk-In Registration</h4>
                <span class="text-white-50" style="font-size:.85rem;">Create a tenant account on the spot</span>
            </div>
        </div>
    </div>
    <div class="card-body" style="background:#fbfcfd;">
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;border-left:4px solid #0ea5e9;">
            <i class="fas fa-info-circle me-1"></i>
            Register a tenant who walks in without an online reservation. An account, student record, and <strong>approved</strong> room reservation are created automatically.
        </div>

        <form action="<?= url($baseUrl . '/students/walk-in') ?>" method="POST" enctype="multipart/form-data" id="walkInForm" novalidate>
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
                        <input type="text" name="date_of_birth" class="form-control<?= hasErr('date_of_birth',$errors) ?> flatpickr-dob" required value="<?= e($old['date_of_birth'] ?? '') ?>" placeholder="Select date" data-max="<?= date('Y-m-d') ?>">
                        <?= fe('date_of_birth',$errors) ?>
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
                            <?php
                            $nationalities = ['Filipino', 'American', 'Australian', 'British', 'Canadian', 'Other'];
                            $selNat = $old['nationality'] ?? 'Filipino';
                            foreach ($nationalities as $nat):
                            ?>
                                <option value="<?= e($nat) ?>" <?= $selNat === $nat ? 'selected' : '' ?>><?= e($nat) ?></option>
                            <?php endforeach; ?>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Student ID Number</label>
                        <input type="text" name="student_id_number" class="form-control" value="<?= e($generatedStudentId ?? '') ?>" readonly style="background:#f1f5f9;">
                        <div class="form-text">Auto-generated.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">School/College <span class="text-danger">*</span></label>
                        <select name="school_university" class="form-select<?= hasErr('school_university',$errors) ?>" required>
                            <option value="">-- Select School/College --</option>
                            <?php
                            $schools = [
                                'Madridejos Community College'                            => 'Madridejos Community College (MCC)',
                                'Salazar Colleges of Science and Institute of Technology' => 'Salazar Colleges of Science and Institute of Technology (SCSIT)',
                                'Cebu North Plains College'                               => 'Cebu North Plains College (CNPC)',
                                'Cebu Technological University'                           => 'Cebu Technological University (CTU)',
                                'Other'                                                   => 'Other (specify)',
                            ];
                            $selSchool = $old['school_university'] ?? '';
                            foreach ($schools as $val=>$label):
                            ?>
                                <option value="<?= e($val) ?>" <?= $selSchool===$val ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= fe('school_university',$errors) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Course/Program <span class="text-danger">*</span></label>
                        <select name="course_program" id="walkInCourseProgram" class="form-select<?= hasErr('course_program',$errors) ?>" required>
                            <option value="">-- Select Course Program --</option>
                            <?php
                            $courses = [
                                'BSIT'  => 'BSIT',
                                'BSHM'  => 'BSHM',
                                'BSED'  => 'BSED',
                                'BSBA'  => 'BSBA',
                                'BSCE'  => 'BSCE',
                                'BSCRIM'=> 'BSCRIM',
                                'Other' => 'Other (specify)',
                            ];
                            $labels = [
                                'BSIT'  => 'Bachelor of Science in Information Technology (BSIT)',
                                'BSHM'  => 'Bachelor of Science in Hospitality Management (BSHM)',
                                'BSED'  => 'Bachelor of Secondary Education (BSED)',
                                'BSBA'  => 'Bachelor of Science in Business Administration (BSBA)',
                                'BSCE'  => 'Bachelor of Science in Civil Engineering (BSCE)',
                                'BSCRIM'=> 'Bachelor of Science in Criminology (BSCRIM)',
                                'Other' => 'Other (specify)',
                            ];
                            foreach ($courses as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= ($old['course_program'] ?? '') === $k ? 'selected' : '' ?>><?= $labels[$k] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= fe('course_program',$errors) ?>
                        <div class="mt-2<?= ($old['course_program'] ?? '') === 'Other' ? '' : ' d-none' ?>" id="courseOtherWrap">
                            <input type="text" name="course_program_other" id="courseProgramOther"
                                   class="form-control<?= hasErr('course_program_other',$errors) ?>"
                                   maxlength="100" placeholder="Specify your course/program"
                                   value="<?= e($old['course_program_other'] ?? '') ?>">
                            <div class="form-text">e.g. BS Accounting, AB English, TVL-ICT</div>
                            <?= fe('course_program_other',$errors) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                        <select name="year_level" class="form-select<?= hasErr('year_level',$errors) ?>" required>
                            <option value="">-- Select Year Level --</option>
                            <?php foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $yl): ?>
                            <option value="<?= $yl ?>" <?= ($old['year_level'] ?? '') === $yl ? 'selected' : '' ?>><?= $yl ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= fe('year_level',$errors) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">School ID Upload</label>
                        <input type="file" name="school_id_upload" class="form-control" accept="image/*,.pdf">
                        <div class="form-text">Upload a photo/PDF of the school ID (optional).</div>
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
                        <div class="mt-2">
                            <button type="submit" name="send_code" value="1" id="sendCodeBtn" formnovalidate class="btn btn-sm btn-outline-primary"><i class="fas fa-paper-plane me-1"></i>Send Verification Code</button>
                        </div>
                        <div class="alert d-none mt-2 py-2 px-3 mb-0" id="sendCodeStatus" role="status" aria-live="polite" style="font-size:.85rem;border-radius:8px;"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Verification Code <span class="text-danger">*</span></label>
                        <input type="text" name="verification_code" class="form-control<?= hasErr('verification_code',$errors) ?>" maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit code" required value="<?= e($old['verification_code'] ?? '') ?>">
                        <?= fe('verification_code',$errors) ?>
                        <div class="field-error d-none" id="codeRequiredError"><i class="fas fa-exclamation-circle me-1"></i>Enter the 6-digit verification code sent to your Gmail address.</div>
                        <div class="form-text">A code is required to proceed. Click "Send Verification Code" to have one emailed to this address.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control<?= hasErr('phone',$errors) ?>" placeholder="09123456789" required inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Please enter a valid 11-digit Philippine mobile number starting with 09." value="<?= e($old['phone'] ?? '') ?>">
                        <?= fe('phone',$errors) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Street</label>
                        <input type="text" name="street" class="form-control" placeholder="Street" value="<?= e($old['street'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                        <select name="province" class="form-select<?= hasErr('province',$errors) ?>" required>
                            <option value="Cebu" <?= ($old['province'] ?? 'Cebu') === 'Cebu' ? 'selected' : '' ?>>Cebu</option>
                        </select>
                        <?= fe('province',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Municipality <span class="text-danger">*</span></label>
                        <select name="municipality_city" id="studentMunicipality" class="form-select<?= hasErr('municipality_city',$errors) ?>" required>
                            <option value="">-- Select Municipality --</option>
                            <option value="Bantayan" <?= ($old['municipality_city'] ?? '') === 'Bantayan' ? 'selected' : '' ?>>Bantayan</option>
                            <option value="Madridejos" <?= ($old['municipality_city'] ?? '') === 'Madridejos' ? 'selected' : '' ?>>Madridejos</option>
                            <option value="Santa Fe" <?= ($old['municipality_city'] ?? '') === 'Santa Fe' ? 'selected' : '' ?>>Santa Fe</option>
                        </select>
                        <?= fe('municipality_city',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                        <select name="barangay" id="studentBarangay" class="form-select<?= hasErr('barangay',$errors) ?>" required disabled>
                            <option value="">-- Select Barangay --</option>
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

            <!-- ============ SECTION 4: ROOM ASSIGNMENT ============ -->
            <div class="reg-section">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Room <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <select name="room_id" id="roomSelect" class="form-select<?= hasErr('room_id',$errors) ?>" required style="flex:1;">
                                <option value="">-- Select Room --</option>
                                <?php foreach ($availableRooms as $room): ?>
                                <?php $slotsLeft = (int)$room['max_capacity'] - (int)$room['current_occupancy']; ?>
                                <?php $images = $room['images'] ?? []; ?>
                                <?php $imagesJson = json_encode(array_values($images), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
                                <option value="<?= (int)$room['id'] ?>" <?= ($old['room_id'] ?? 0) == $room['id'] ? 'selected' : '' ?>
                                    data-advance="<?= (float)$room['advance_payment'] ?>"
                                    data-rent="<?= (float)$room['monthly_rent'] ?>"
                                    data-name="<?= e($room['room_number'] . ' â€” ' . $room['room_name']) ?>"
                                    data-capacity="<?= (int)$room['max_capacity'] ?>"
                                    data-occupancy="<?= (int)$room['current_occupancy'] ?>"
                                    data-type="<?= e($room['room_type']) ?>"
                                    data-status="<?= e($room['status']) ?>"
                                    data-images='<?= $imagesJson ?>'
                                    data-room-number="<?= e($room['room_number']) ?>"
                                    data-room-name="<?= e($room['room_name']) ?>"
                                    data-floor="<?= e($room['floor'] ?? '') ?>"
                                    data-size-sqm="<?= e($room['size_sqm'] ?? '') ?>"
                                    data-has-aircon="<?= (int)($room['has_aircon'] ?? 0) ?>"
                                    data-has-bathroom="<?= (int)($room['has_bathroom'] ?? 0) ?>"
                                    data-has-balcony="<?= (int)($room['has_balcony'] ?? 0) ?>"
                                    data-furniture="<?= e($room['furniture'] ?? '') ?>"
                                    data-description="<?= e($room['description'] ?? '') ?>"
                                    data-house-rules="<?= e($room['house_rules'] ?? '') ?>"
                                    data-primary-image="<?= e($room['primary_image'] ?? '') ?>">
                                    Room <?= e($room['room_number']) ?> - <?= e($room['room_name']) ?>
                                    (<?= formatCurrency((float)$room['monthly_rent']) ?>/mo, <?= $slotsLeft > 0 ? $slotsLeft . ' slot' . ($slotsLeft > 1 ? 's' : '') . ' left' : 'Full' ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                        </div>
                        <?= fe('room_id',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Move-in Date <span class="text-danger">*</span></label>
                        <input type="text" name="move_in_date" class="form-control<?= hasErr('move_in_date',$errors) ?> flatpickr-movein" required value="<?= e($old['move_in_date'] ?? '') ?>" placeholder="Select date" data-min="<?= date('Y-m-d') ?>" data-max="<?= date('Y-m-d', strtotime('+3 months')) ?>">
                        <?= fe('move_in_date',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Duration (months) <span class="text-danger">*</span></label>
                        <input type="number" name="expected_duration" id="durationMonths" class="form-control<?= hasErr('expected_duration',$errors) ?>" min="1" max="12" required value="<?= e($old['expected_duration'] ?? 1) ?>">
                        <div class="form-text">1â€“12 months, whole numbers only.</div>
                        <?= fe('expected_duration',$errors) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Valid ID Upload <span class="text-danger">*</span></label>
                        <input type="file" name="valid_id" class="form-control<?= hasErr('valid_id',$errors) ?>" accept="image/*,.pdf" required>
                        <div class="form-text">Upload a photo/PDF of a valid ID (required).</div>
                        <?= fe('valid_id',$errors) ?>
                    </div>
                </div>
                <div class="row g-3 mt-2" id="roomInfoRow" style="display:none;">
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3">
                            <div class="row text-center mb-2">
                                <div class="col-12 col-md-3">
                                    <div class="small text-muted">Room Type</div>
                                    <div class="fw-bold" id="displayType">â€”</div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="small text-muted">Status</div>
                                    <div id="displayStatus">â€”</div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="small text-muted">Capacity</div>
                                    <div class="fw-bold" id="displayCapacity">â€”</div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="small text-muted">Photos</div>
                                    <div class="fw-bold" id="displayImageCount">â€”</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div id="roomCarousel" class="carousel slide" data-bs-ride="false" style="display:none;">
                                        <div class="carousel-inner rounded" id="roomCarouselInner"></div>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev" style="display:none;">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next" style="display:none;">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                        <div class="carousel-indicators" id="roomCarouselIndicators"></div>
                                    </div>
                                    <div id="roomNoImage" class="text-center text-muted py-4" style="display:none;">
                                        <i class="fas fa-image fa-2x mb-2 d-block"></i>
                                        <small>No images available</small>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap mt-2" id="roomThumbnails" style="display:none;"></div>
                                </div>
                            </div>
                            <hr class="my-1">
                            <div class="row text-center">
                                <div class="col-12 col-md-4">
                                    <div class="small text-muted">Monthly Rent</div>
                                    <div class="fw-bold fs-5" id="displayRent">â€”</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="small text-muted">Advance Payment</div>
                                    <div class="fw-bold fs-5" id="displayAdvance">â€”</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="small text-muted">Total Due (Rent Ã— Duration + Advance)</div>
                                    <div class="fw-bold fs-5 text-success" id="displayTotal">â€”</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ SECTION 5: WALK-IN PAYMENT ============ -->
            <div class="reg-section" id="walk-in-payment">
                <h6 class="reg-section-title"><i class="fas fa-money-bill-wave me-2 text-success"></i>Walk-In Payment <span class="text-muted fw-normal small">(collect advance + first month rent over the counter)</span></h6>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="collect_payment" id="collectPayment" value="1" <?= ($old['collect_payment'] ?? '') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="collectPayment">
                        <strong>Collect payment during registration</strong>
                        <span class="text-muted d-block small">Mark the advance payment and the rent bill (monthly rent Ã— lease duration) as paid now. A receipt is generated for each bill.</span>
                    </label>
                </div>

                <div id="paymentFields" class="row g-3 <?= ($old['collect_payment'] ?? '') === '1' ? '' : 'd-none' ?>">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <h6 class="small fw-bold text-uppercase text-muted mb-2">Bill Summary</h6>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Advance Payment</span>
                                <strong class="advance-amt"><?= formatCurrency(0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Rent (lease period)</span>
                                <strong class="rent-amt"><?= formatCurrency(0) ?></strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total Due</span>
                                <strong class="total-amt"><?= formatCurrency(0) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select<?= hasErr('payment_method',$errors) ?>" id="paymentMethod">
                            <option value="cash" <?= ($old['payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="gcash" <?= ($old['payment_method'] ?? '') === 'gcash' ? 'selected' : '' ?>>GCash</option>
                        </select>
                        <?= fe('payment_method',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Amount Received <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= e(getCurrencySymbol()) ?></span>
                            <input type="number" step="1" min="0" name="payment_received" id="paymentReceived" class="form-control<?= hasErr('payment_received',$errors) ?>" placeholder="0" value="<?= e($old['payment_received'] ?? '') ?>" readonly style="background:#f1f5f9;">
                        </div>
                        <?= fe('payment_received',$errors) ?>
                        <div class="form-text">Auto-calculated from room rate and lease duration.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference / OR Number <span class="text-danger d-none" id="refRequired">*</span></label>
                        <input type="text" name="payment_reference" id="paymentReference" class="form-control<?= hasErr('payment_reference',$errors) ?>" placeholder="Official receipt or GCash reference no." value="<?= e($old['payment_reference'] ?? '') ?>">
                        <?= fe('payment_reference',$errors) ?>
                        <div class="form-text d-none" id="refHint">Required for GCash payments.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Notes</label>
                        <input type="text" name="payment_notes" class="form-control" placeholder="Optional notes" value="<?= e($old['payment_notes'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- ============ SECTION 6: ACCOUNT INFORMATION ============ -->
            <div class="reg-section">
                <h6 class="reg-section-title"><i class="fas fa-key me-2"></i>Account Information <span class="text-muted fw-normal small">(username optional â€” password required: type it or click Generate)</span></h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control<?= hasErr('username',$errors) ?>" placeholder="Auto-generated if left blank" minlength="4" maxlength="50" autocomplete="off" value="<?= e($old['username'] ?? '') ?>">
                        <div class="form-text">4â€“50 characters. Leave blank to auto-generate.</div>
                        <?= fe('username',$errors) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="pw-field-wrap">
                            <div class="pw-input-group">
                                <input type="password" name="password" id="walkInPassword" class="form-control<?= hasErr('password',$errors) ?>" placeholder="Type a password or click Generate" minlength="8" data-pw-validate autocomplete="new-password" value="<?= e($old['password'] ?? '') ?>">
                                <label class="pw-show-cb"><input type="checkbox"> Show</label>
                            </div>
                            <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="genPasswordBtn"><i class="fas fa-dice me-1"></i>Generate Password</button>
                        </div>
                        <div class="form-text">Required. Type your own password or click Generate Password (8+ chars, upper/lower case, number and symbol).</div>
                        <?= fe('password',$errors) ?>
                        <div class="field-error d-none" id="pwRequiredError"><i class="fas fa-exclamation-circle me-1"></i>Password is required. Type your own password or click Generate Password.</div>
                    </div>
                </div>
            </div>

            <!-- ============ SECTION 7: GUARDIAN INFORMATION (OPTIONAL) ============ -->
            <div class="reg-section">
                <h6 class="reg-section-title"><i class="fas fa-shield-alt me-2"></i>Guardian Information <span class="text-muted fw-normal small">(required)</span></h6>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Alternative Contact</label>
                        <input type="tel" name="guardian_alt_contact" class="form-control<?= hasErr('guardian_alt_contact',$errors) ?>" placeholder="Alt. phone (optional)" inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Alternative Contact: Please enter a valid 11-digit Philippine mobile number starting with 09." value="<?= e($old['guardian_alt_contact'] ?? '') ?>">
                        <?= fe('guardian_alt_contact',$errors) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="guardian_email" class="form-control<?= hasErr('guardian_email',$errors) ?>" placeholder="Guardian email (optional, e.g. example@gmail.com)" pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Guardian Email: Please enter a valid Gmail address ending with @gmail.com." value="<?= e($old['guardian_email'] ?? '') ?>">
                        <?= fe('guardian_email',$errors) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Street</label>
                        <input type="text" name="guardian_street" class="form-control" placeholder="Street" value="<?= e($old['guardian_street'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Province</label>
                        <select name="guardian_province" class="form-select<?= hasErr('guardian_province',$errors) ?>">
                            <option value="Cebu" <?= ($old['guardian_province'] ?? 'Cebu') === 'Cebu' ? 'selected' : '' ?>>Cebu</option>
                        </select>
                        <?= fe('guardian_province',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Municipality <span class="text-danger">*</span></label>
                        <select name="guardian_municipality_city" id="guardianMunicipality" class="form-select<?= hasErr('guardian_municipality_city',$errors) ?>" required>
                            <option value="">-- Select Municipality --</option>
                            <option value="Bantayan" <?= ($old['guardian_municipality_city'] ?? '') === 'Bantayan' ? 'selected' : '' ?>>Bantayan</option>
                            <option value="Madridejos" <?= ($old['guardian_municipality_city'] ?? '') === 'Madridejos' ? 'selected' : '' ?>>Madridejos</option>
                            <option value="Santa Fe" <?= ($old['guardian_municipality_city'] ?? '') === 'Santa Fe' ? 'selected' : '' ?>>Santa Fe</option>
                        </select>
                        <?= fe('guardian_municipality_city',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                        <select name="guardian_barangay" id="guardianBarangay" class="form-select<?= hasErr('guardian_barangay',$errors) ?>" disabled required>
                            <option value="">-- Select Barangay --</option>
                        </select>
                        <?= fe('guardian_barangay',$errors) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">ZIP Code <span class="text-danger">*</span></label>
                        <input type="text" name="guardian_zip_code" id="guardianZipCode" class="form-control<?= hasErr('guardian_zip_code',$errors) ?>" placeholder="ZIP Code" maxlength="4" pattern="[0-9]{4}" required value="<?= e($old['guardian_zip_code'] ?? '') ?>" readonly style="background:#f1f5f9;">
                        <?= fe('guardian_zip_code',$errors) ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-user-check me-1"></i>Register Walk-In Tenant
                </button>
                <a href="<?= url($baseUrl . '/students') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
                <h5 class="modal-title fw-bold" style="font-size: 1.05rem;" id="roomd-title">Room Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="position-relative">
                    <img id="roomd-image" src="" alt="" class="d-none w-100 mb-2" style="height:220px;object-fit:cover;border-radius:10px;cursor:zoom-in;">
                    <div class="d-flex gap-2 flex-wrap mb-3" id="roomd-thumbs" style="display:none;"></div>
                </div>
                <div class="row g-2">
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room Number</div>
                            <div class="fw-semibold small mt-1" id="roomd-number">â€”</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Room Type</div>
                            <div class="fw-semibold small mt-1 text-capitalize" id="roomd-type">â€”</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Monthly Rent</div>
                            <div class="fw-semibold small mt-1" style="color:#059669;" id="roomd-rent">â€”</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Advance Payment</div>
                            <div class="fw-semibold small mt-1" id="roomd-advance">â€”</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Floor</div>
                            <div class="fw-semibold small mt-1" id="roomd-floor">â€”</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;height:100%;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Size</div>
                            <div class="fw-semibold small mt-1" id="roomd-size">â€”</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Availability</div>
                            <div class="mt-1" id="roomd-avail">â€”</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Amenities</div>
                            <div class="mt-1 d-flex flex-wrap gap-1" id="roomd-amenities"></div>
                        </div>
                    </div>
                    <div class="col-12" id="roomd-furn-wrap" style="display:none;">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Furniture</div>
                            <div class="small mt-1" id="roomd-furniture">â€”</div>
                        </div>
                    </div>
                    <div class="col-12" id="roomd-desc-wrap" style="display:none;">
                        <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                            <div class="small text-muted text-uppercase" style="font-size:10px;">Description</div>
                            <div class="small mt-1" style="line-height:1.6;" id="roomd-description">â€”</div>
                        </div>
                    </div>
                    <div class="col-12" id="roomd-rules-wrap" style="display:none;">
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:10px 12px;">
                            <div class="small text-uppercase" style="font-size:10px;color:#92400e;letter-spacing:.5px;">House Rules</div>
                            <div class="small mt-1" style="color:#78350f;line-height:1.6;" id="roomd-rules">â€”</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 12px 20px;">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal for Room Details Modal -->
<div id="roomDetailsLightbox" class="position-fixed top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,0.95);z-index:10050;display:none;align-items:center;justify-content:center;">
    <button type="button" onclick="closeRoomDetailsLightbox()" class="position-absolute top-0 end-0 m-3 btn btn-sm" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:40px;height:40px;border-radius:50%;font-size:1.2rem;z-index:10060;">
        <i class="fas fa-times"></i>
    </button>
    <span id="roomDetailsLightboxCounter" class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 fs-6" style="z-index:10060;"></span>
    <button type="button" onclick="roomDetailsLightboxPrev()" class="position-absolute start-0 top-50 translate-middle-y ms-3 btn" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:48px;height:48px;border-radius:50%;font-size:1.2rem;z-index:10060;">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button type="button" onclick="roomDetailsLightboxNext()" class="position-absolute end-0 top-50 translate-middle-y me-3 btn" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:48px;height:48px;border-radius:50%;font-size:1.2rem;z-index:10060;">
        <i class="fas fa-chevron-right"></i>
    </button>
    <img id="roomDetailsLightboxImage" src="" alt="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;">
</div>

<!-- Lightbox Modal for Room Info Section -->
<div id="roomInfoLightbox" class="position-fixed top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,0.95);z-index:10050;display:none;align-items:center;justify-content:center;">
    <button type="button" onclick="closeRoomInfoLightbox()" class="position-absolute top-0 end-0 m-3 btn btn-sm" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:40px;height:40px;border-radius:50%;font-size:1.2rem;z-index:10060;">
        <i class="fas fa-times"></i>
    </button>
    <span id="roomInfoLightboxCounter" class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 fs-6" style="z-index:10060;"></span>
    <button type="button" onclick="roomInfoLightboxPrev()" class="position-absolute start-0 top-50 translate-middle-y ms-3 btn" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:48px;height:48px;border-radius:50%;font-size:1.2rem;z-index:10060;">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button type="button" onclick="roomInfoLightboxNext()" class="position-absolute end-0 top-50 translate-middle-y me-3 btn" style="color:#fff;background:rgba(255,255,255,0.15);border:none;width:48px;height:48px;border-radius:50%;font-size:1.2rem;z-index:10060;">
        <i class="fas fa-chevron-right"></i>
    </button>
    <img id="roomInfoLightboxImage" src="" alt="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;">
</div>

<style>
.walkin-card{box-shadow:0 10px 40px rgba(15,23,42,.12)!important;}
.walkin-header{background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 45%,#2d5a1e 100%);padding:1.5rem 1.75rem;color:#fff;}
.walkin-header-icon{width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-right:1rem;color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.18);}
.reg-section{background:linear-gradient(180deg,#ffffff 0%,#f7f9fc 100%);border:1px solid #e6ebf2;border-radius:14px;padding:1.35rem 1.5rem;margin-bottom:1.25rem;box-shadow:0 1px 3px rgba(15,23,42,.05);}
.reg-section-title{font-weight:700;color:#0f172a;margin-bottom:1rem;font-size:.95rem;border-bottom:2px solid #e6ebf2;padding-bottom:.6rem;}
.reg-section-title i{color:var(--primary,#8fa61b);}
.reg-section .form-label{font-size:.85rem;color:#475569;margin-bottom:.25rem;font-weight:500;}
.reg-section .form-control,.reg-section .form-select{border-radius:9px;font-size:.9rem;border-color:#dbe2ea;background:#fff;}
.reg-section .form-control:focus,.reg-section .form-select:focus{border-color:#8fa61b;box-shadow:0 0 0 .18rem rgba(143,166,27,.18);}
.field-error{color:#dc2626;font-size:.78rem;margin-top:.25rem;font-weight:500;}
.form-control.is-invalid,.form-select.is-invalid{border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.15);}
</style>

<script>
const barangays = {
    'Bantayan': ['Atop-atop','Baigad','Banale','Binaobao','Botigues','Doong','Guiwanon','Hilutungan','Kabac','Kampingganon','Lipayran','Mojon','Patao','Putian','Sungko','Suba','Sulangan','Tamiao','Ticad','Ticad Reclamation'],
    'Madridejos': ['Kaongkod','Maalat','Mancilang','Pili','Poblacion','Talangnan','Tugas'],
    'Santa Fe': ['Balidbid','Hagdan','Hilantagaan','Kinatarcan','Langub','Maricaban','Okoy','Poblacion','Pooc','Talisay']
};

const zipCodes = {
    'Bantayan': '6052',
    'Madridejos': '6053',
    'Santa Fe': '6047'
};

function walkInEsc(value) {
    return String(value).replace(/[&<>"']/g, function(ch) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
}

const WALKIN_CSRF_NAME = <?= json_encode(CSRF_TOKEN_NAME) ?>;
const WALKIN_CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
const WALKIN_FORM_ACTION = <?= json_encode(url($baseUrl . '/students/walk-in')) ?>;

function setupCascading(muniId, brgyId, zipId, oldBrgy) {
    const muni = document.getElementById(muniId);
    const brgy = document.getElementById(brgyId);
    const zip = document.getElementById(zipId);
    if (!muni || !brgy) return;

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
        // Auto-fill ZIP code
        if (zip) {
            zip.value = zipCodes[this.value] || '';
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

    // Verification Code must be a 6-digit code before the form may register.
    // Declared here (not inside the Send-button handler) so the submit guard
    // below and the spinner handler further down share the same variable.
    var sendCodeRequested = false;
    var codeForm = document.getElementById('walkInForm');
    var codeInput = document.querySelector('input[name="verification_code"]');
    var codeRequired = document.getElementById('codeRequiredError');

    // Sends the verification code over AJAX so the form is never re-rendered.
    // A full page reload would clear any Valid ID file the staff already picked.
    function sendWalkInCodeAjax() {
        var statusBox = document.getElementById('sendCodeStatus');
        var sendBtn = document.getElementById('sendCodeBtn');
        var emailEl = codeForm ? codeForm.querySelector('input[name="email"]') : null;
        var nameEl = codeForm ? codeForm.querySelector('input[name="first_name"]') : null;
        if (!codeForm) return;

        function showStatus(kind, html) {
            if (!statusBox) return;
            statusBox.className = 'alert py-2 px-3 mb-0 mt-2 ' + kind;
            statusBox.innerHTML = html;
        }

        var body = new URLSearchParams();
        body.set(WALKIN_CSRF_NAME, WALKIN_CSRF_TOKEN);
        body.set('send_code', '1');
        body.set('ajax_send_code', '1');
        body.set('email', emailEl ? (emailEl.value || '').trim() : '');
        body.set('first_name', nameEl ? (nameEl.value || '').trim() : '');

        var originalHtml = sendBtn ? sendBtn.innerHTML : '';
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending code...';
        }
        showStatus('alert-info', '<i class="fas fa-spinner fa-spin me-1"></i>Sending the verification code...');

        fetch(codeForm.action || WALKIN_FORM_ACTION, {
            method: 'POST',
            body: body,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.ok) {
                var html = '<i class="fas fa-check-circle me-1"></i>A 6-digit verification code was sent to <strong>' + walkInEsc(String(data.email || '')) + '</strong>. It expires in 5 minutes.'
                    + '<br><span style="font-size:.8rem">Resending within 45 seconds returns the same code.</span>';
                showStatus('alert-success', html);
                var vc = codeForm.querySelector('input[name="verification_code"]');
                if (vc) { vc.classList.remove('is-invalid'); vc.focus(); }
            } else {
                var errs = (data && data.errors) ? data.errors : ['Could not send the verification code. Please try again.'];
                showStatus('alert-danger', '<i class="fas fa-circle-exclamation me-1"></i>' + errs.map(walkInEsc).join('<br>'));
            }
        })
        .catch(function() {
            showStatus('alert-danger', '<i class="fas fa-circle-exclamation me-1"></i>Could not reach the server. Check your connection and try again.');
        })
        .finally(function() {
            if (sendBtn) { sendBtn.disabled = false; sendBtn.innerHTML = originalHtml; }
            sendCodeRequested = false;
        });
    }

    if (codeForm && codeInput) {
        codeForm.addEventListener('submit', function(e) {
            var viaSendButton = (e.submitter && e.submitter.name === 'send_code') || sendCodeRequested;
            if (viaSendButton) {
                e.preventDefault();
                e.stopImmediatePropagation();
                sendWalkInCodeAjax();
                return;
            }
            // "Other (specify)" must be filled in before the form registers.
            if (courseSel && courseSel.value === 'Other' && courseOtherInput && !(courseOtherInput.value || '').trim()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                courseOtherInput.classList.add('is-invalid');
                courseOtherInput.focus();
                return;
            }
            // Password is required: type it yourself or click Generate Password.
            if (pwField && !(pwField.value || '').trim()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                pwField.classList.add('is-invalid');
                if (pwRequired) pwRequired.classList.remove('d-none');
                pwField.focus();
                return;
            }
            if (pwField && pwRequired) pwRequired.classList.add('d-none');
            if (/^\d{6}$/.test((codeInput.value || '').trim())) {
                codeInput.classList.remove('is-invalid');
                if (codeRequired) codeRequired.classList.add('d-none');
                return;
            }
            e.preventDefault();
            e.stopImmediatePropagation();
            codeInput.classList.add('is-invalid');
            if (codeRequired) codeRequired.classList.remove('d-none');
            codeInput.focus();
        });
        codeInput.addEventListener('input', function() {
            if (/^\d{6}$/.test((this.value || '').trim())) {
                this.classList.remove('is-invalid');
                if (codeRequired) codeRequired.classList.add('d-none');
            }
        });
    }

    // ---- Course/Program: show the free-text box only for "Other (specify)" ----
    var courseSel = document.getElementById('walkInCourseProgram');
    var courseOtherWrap = document.getElementById('courseOtherWrap');
    var courseOtherInput = document.getElementById('courseProgramOther');
    function toggleCourseOther() {
        var isOther = !!courseSel && courseSel.value === 'Other';
        if (courseOtherWrap) courseOtherWrap.classList.toggle('d-none', !isOther);
        if (courseOtherInput) {
            courseOtherInput.required = isOther;
            if (!isOther) {
                courseOtherInput.classList.remove('is-invalid');
                courseOtherInput.setCustomValidity('');
            }
        }
    }
    if (courseSel) {
        courseSel.addEventListener('change', toggleCourseOther);
        toggleCourseOther();
    }
    if (courseOtherInput) {
        courseOtherInput.addEventListener('input', function() {
            if ((this.value || '').trim() !== '') {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });
    }

    // ---- Password: required (typed or generated) ----
    var pwField = document.getElementById('walkInPassword');
    var pwRequired = document.getElementById('pwRequiredError');
    if (pwField) {
        pwField.addEventListener('input', function() {
            if ((this.value || '').trim() !== '') {
                this.classList.remove('is-invalid');
                if (pwRequired) pwRequired.classList.add('d-none');
            }
        });
    }

    // ---- Generate Password ----
    const genPasswordBtn = document.getElementById('genPasswordBtn');
    if (genPasswordBtn) {
        genPasswordBtn.addEventListener('click', function() {
            const sets = [
                'ABCDEFGHJKLMNPQRSTUVWXYZ',
                'abcdefghjkmnpqrstuvwxyz',
                '23456789',
                '!@#$%^&*()_+-=',
            ];
            const pick = (s) => s[Math.floor(Math.random() * s.length)];
            let pw = sets.map(pick).join('');
            const all = sets.join('');
            const extras = 8 + Math.floor(Math.random() * 5);
            for (let i = 0; i < extras; i++) pw += pick(all);
            pw = pw.split('').sort(() => Math.random() - 0.5).join('');
            const input = document.querySelector('input[name="password"]');
            if (input) {
                input.value = pw;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    // ---- Walk-In Payment ----
    const collectCb = document.getElementById('collectPayment');
    const payFields = document.getElementById('paymentFields');
    const roomSelect = document.querySelector('select[name="room_id"]');
    const durInput = document.querySelector('input[name="expected_duration"]');
    const receivedInput = document.getElementById('paymentReceived');
    const advEl = document.querySelector('.advance-amt');
    const rentEl = document.querySelector('.rent-amt');
    const totalEl = document.querySelector('.total-amt');
    const infoRow = document.getElementById('roomInfoRow');
    const displayType = document.getElementById('displayType');
    const displayStatus = document.getElementById('displayStatus');
    const displayCapacity = document.getElementById('displayCapacity');
    const displayImageCount = document.getElementById('displayImageCount');
    const roomCarousel = document.getElementById('roomCarousel');
    const roomCarouselInner = document.getElementById('roomCarouselInner');
    const roomCarouselIndicators = document.getElementById('roomCarouselIndicators');
    const roomNoImage = document.getElementById('roomNoImage');
    const carouselPrev = roomCarousel ? roomCarousel.querySelector('.carousel-control-prev') : null;
    const carouselNext = roomCarousel ? roomCarousel.querySelector('.carousel-control-next') : null;
    const displayRent = document.getElementById('displayRent');
    const displayAdvance = document.getElementById('displayAdvance');
    const displayTotal = document.getElementById('displayTotal');
    const currencySymbol = <?= json_encode(getCurrencySymbol()) ?>;
    let lastTotal = 0;

    function fmt(n) {
        return currencySymbol + Number(n).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function buildCarousel(images) {
        if (!roomCarousel || !roomCarouselInner || !roomCarouselIndicators || !roomNoImage) return;
        var thumbsContainer = document.getElementById('roomThumbnails');
        if (!images || images.length === 0) {
            roomCarousel.style.display = 'none';
            roomNoImage.style.display = 'block';
            if (thumbsContainer) thumbsContainer.style.display = 'none';
            return;
        }
        roomCarouselInner.innerHTML = '';
        roomCarouselIndicators.innerHTML = '';
        if (thumbsContainer) thumbsContainer.innerHTML = '';
        images.forEach(function(img, index) {
            var indicator = document.createElement('button');
            indicator.type = 'button';
            indicator.setAttribute('data-bs-target', '#roomCarousel');
            indicator.setAttribute('data-bs-slide-to', index);
            indicator.setAttribute('aria-label', 'Slide ' + (index + 1));
            if (index === 0) {
                indicator.className = 'active';
                indicator.setAttribute('aria-current', 'true');
            }
            roomCarouselIndicators.appendChild(indicator);

            var item = document.createElement('div');
            item.className = 'carousel-item' + (index === 0 ? ' active' : '');
            var imageUrl = '<?= UPLOAD_URL ?>' + img.image_path;
            var altText = img.alt_text || 'Room image ' + (index + 1);
            item.innerHTML = '<img src="' + imageUrl + '" class="d-block w-100 rounded" alt="' + altText + '" style="max-height:300px;object-fit:cover;cursor:zoom-in;">';
            var carouselImg = item.querySelector('img');
            if (carouselImg) {
                carouselImg.onclick = function() {
                    openRoomInfoLightbox(images, index);
                };
            }
            roomCarouselInner.appendChild(item);

            if (thumbsContainer) {
                var thumb = document.createElement('img');
                thumb.src = imageUrl;
                thumb.alt = altText;
                thumb.style.cssText = 'width:60px;height:45px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid ' + (index === 0 ? '#2563eb' : '#e2e8f0') + ';transition:border-color 0.2s;';
                thumb.dataset.index = index;
                thumb.onclick = function() {
                    var carousel = bootstrap.Carousel.getInstance(roomCarousel) || new bootstrap.Carousel(roomCarousel);
                    carousel.to(index);
                };
                thumb.onmouseover = function() { this.style.borderColor = '#93c5fd'; };
                thumb.onmouseout = function() { this.style.borderColor = parseInt(this.dataset.index, 10) === getActiveIndex() ? '#2563eb' : '#e2e8f0'; };
                thumbsContainer.appendChild(thumb);
            }
        });
        roomCarousel.style.display = 'block';
        roomNoImage.style.display = 'none';
        if (thumbsContainer) thumbsContainer.style.display = 'flex';
        if (carouselPrev) carouselPrev.style.display = images.length > 1 ? 'block' : 'none';
        if (carouselNext) carouselNext.style.display = images.length > 1 ? 'block' : 'none';

        function getActiveIndex() {
            var activeItem = roomCarouselInner.querySelector('.carousel-item.active');
            if (!activeItem) return 0;
            return Array.from(roomCarouselInner.children).indexOf(activeItem);
        }

        if (roomCarousel) {
            roomCarousel.addEventListener('slid.bs.carousel', function(e) {
                var idx = e.to;
                if (thumbsContainer) {
                    thumbsContainer.querySelectorAll('img').forEach(function(thumb, i) {
                        thumb.style.borderColor = i === idx ? '#2563eb' : '#e2e8f0';
                    });
                }
            });
        }
    }

    function selectedRoomTotals() {
        const opt = roomSelect.options[roomSelect.selectedIndex];
        if (!opt) return {adv: 0, rent: 0};
        const dur = Math.max(1, parseInt(durInput ? durInput.value : '1', 10) || 1);
        const rent = parseFloat(opt.dataset.rent || 0) || 0;
        return {adv: rent, rent: rent * dur};
    }

    function updateSummary() {
        const t = selectedRoomTotals();
        lastTotal = Math.round(t.adv + t.rent);
        advEl.textContent = fmt(t.adv);
        rentEl.textContent = fmt(t.rent);
        totalEl.textContent = fmt(lastTotal);
        if (collectCb && collectCb.checked) {
            const cur = parseInt(receivedInput.value, 10) || 0;
            if (cur === 0) receivedInput.value = lastTotal;
        }
    }

    function updateRoomInfo() {
        if (!roomSelect || !infoRow) return;
        const opt = roomSelect.options[roomSelect.selectedIndex];
        if (!opt || opt.value === '') { infoRow.style.display = 'none'; return; }
        const rent = parseFloat(opt.dataset.rent) || 0;
        const advance = parseFloat(opt.dataset.advance) || 0;
        const months = parseInt(durInput.value, 10) || 1;
        const totalRent = rent * months;
        const totalDue = totalRent + advance;
        const maxCap = parseInt(opt.dataset.capacity) || 0;
        const occ = parseInt(opt.dataset.occupancy) || 0;
        const slotsLeft = maxCap - occ;
        const typeName = opt.dataset.type || '';
        const status = opt.dataset.status || '';
        let images = [];
        try {
            images = JSON.parse(opt.dataset.images || '[]');
        } catch (e) {
            images = [];
        }
        const imageCount = images.length;

        const statusLabels = {available: 'Available', reserved: 'Reserved', occupied: 'Occupied', under_maintenance: 'Under Maintenance'};
        const statusBadges = {available: 'success', reserved: 'warning', occupied: 'danger', under_maintenance: 'secondary'};
        const sLabel = statusLabels[status] || status;
        const sBadge = statusBadges[status] || 'secondary';

        if (displayType) displayType.textContent = typeName.charAt(0).toUpperCase() + typeName.slice(1);
        if (displayStatus) displayStatus.innerHTML = '<span class="badge bg-' + sBadge + ' bg-opacity-10 text-' + sBadge + '">' + sLabel + '</span>';
        if (displayCapacity) displayCapacity.textContent = occ + ' / ' + maxCap + ' beds (' + slotsLeft + ' slot' + (slotsLeft !== 1 ? 's' : '') + ' left)';
        if (displayImageCount) displayImageCount.textContent = imageCount + ' photo' + (imageCount !== 1 ? 's' : '');
        if (displayRent) displayRent.textContent = fmt(rent);
        if (displayAdvance) displayAdvance.textContent = fmt(advance);
        if (displayTotal) displayTotal.textContent = fmt(totalDue);

        buildCarousel(images);

        infoRow.style.display = '';
    }

    function enforceWholeNumber() {
        if (receivedInput.value === '') return;
        const num = parseInt(receivedInput.value, 10);
        if (isNaN(num) || num < 0) { receivedInput.value = ''; return; }
        receivedInput.value = String(Math.trunc(num));
    }

    if (collectCb) {
        collectCb.addEventListener('change', function() {
            payFields.classList.toggle('d-none', !this.checked);
            if (this.checked) updateSummary();
        });
    }
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            updateSummary();
            updateRoomInfo();
            var viewBtn = document.getElementById('viewRoomDetailsBtn');
            if (viewBtn) {
                viewBtn.disabled = this.value === '';
                if (this.value !== '') {
                    viewBtn.title = 'View room details';
                } else {
                    viewBtn.title = 'Select a room to view details';
                }
            }
        });
        if (roomSelect.value === '' && roomSelect.options.length > 1) {
            roomSelect.selectedIndex = 1;
        }
    }
    if (durInput) durInput.addEventListener('input', function() {
        updateSummary();
        updateRoomInfo();
    });
    if (receivedInput) {
        receivedInput.addEventListener('blur', enforceWholeNumber);
        receivedInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^\d]/g, '');
        });
    }

    updateSummary();
    updateRoomInfo();

    // Initialize view button state
    var viewBtnInit = document.getElementById('viewRoomDetailsBtn');
    var roomSelectInit = document.getElementById('roomSelect');
    if (viewBtnInit && roomSelectInit) {
        viewBtnInit.disabled = roomSelectInit.value === '';
        if (roomSelectInit.value !== '') {
            viewBtnInit.title = 'View room details';
        }
    }

    // Initialize Flatpickr for Move-in Date
    var fpMoveIn = null;
    function initWalkInRegisterMoveInFlatpickr() {
        var input = document.querySelector('input[name="move_in_date"].flatpickr-movein');
        if (!input || typeof flatpickr === 'undefined') return;
        if (fpMoveIn) fpMoveIn.destroy();
        var minDate = input.dataset.min;
        var maxDate = input.dataset.max;
        fpMoveIn = flatpickr(input, {
            dateFormat: 'Y-m-d',
            minDate: minDate,
            maxDate: maxDate,
            allowInput: true,
            clickOpens: true
        });
    }
    initWalkInRegisterMoveInFlatpickr();

    // Initialize Flatpickr for Date of Birth
    var fpDob = null;
    function initWalkInRegisterDobFlatpickr() {
        var input = document.querySelector('input[name="date_of_birth"].flatpickr-dob');
        if (!input || typeof flatpickr === 'undefined') return;
        if (fpDob) fpDob.destroy();
        var maxDate = input.dataset.max;
        fpDob = flatpickr(input, {
            dateFormat: 'Y-m-d',
            maxDate: maxDate,
            allowInput: true,
            clickOpens: true
        });
    }
    initWalkInRegisterDobFlatpickr();

    var payMethod = document.getElementById('paymentMethod');
    var refInput = document.getElementById('paymentReference');
    var refReq = document.getElementById('refRequired');
    var refHint = document.getElementById('refHint');
    function toggleRefRequired() {
        var isGcash = payMethod && payMethod.value === 'gcash';
        if (refInput) refInput.required = isGcash;
        if (refReq) refReq.classList.toggle('d-none', !isGcash);
        if (refHint) refHint.classList.toggle('d-none', !isGcash);
    }
    if (payMethod) payMethod.addEventListener('change', toggleRefRequired);
    toggleRefRequired();

    // Room Info Lightbox Functions (global scope for carousel image clicks)
    var roomInfoLightboxImages = [];
    var roomInfoLightboxIndex = 0;

    window.openRoomInfoLightbox = function(images, startIndex) {
        roomInfoLightboxImages = images.map(function(p) { return '<?= UPLOAD_URL ?>' + p.image_path; });
        roomInfoLightboxIndex = startIndex || 0;
        updateRoomInfoLightbox();
        var modal = document.getElementById('roomInfoLightbox');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };

    function updateRoomInfoLightbox() {
        var lbImg = document.getElementById('roomInfoLightboxImage');
        var lbCounter = document.getElementById('roomInfoLightboxCounter');
        if (lbImg) lbImg.src = roomInfoLightboxImages[roomInfoLightboxIndex];
        if (lbCounter) lbCounter.textContent = (roomInfoLightboxIndex + 1) + ' / ' + roomInfoLightboxImages.length;
    }

    window.closeRoomInfoLightbox = function() {
        var modal = document.getElementById('roomInfoLightbox');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    window.roomInfoLightboxNext = function() {
        roomInfoLightboxIndex = (roomInfoLightboxIndex + 1) % roomInfoLightboxImages.length;
        updateRoomInfoLightbox();
    };

    window.roomInfoLightboxPrev = function() {
        roomInfoLightboxIndex = (roomInfoLightboxIndex - 1 + roomInfoLightboxImages.length) % roomInfoLightboxImages.length;
        updateRoomInfoLightbox();
    };

    // View Room Details Modal
    var viewRoomBtn = document.getElementById('viewRoomDetailsBtn');
    if (viewRoomBtn) {
        viewRoomBtn.addEventListener('click', function() {
            var roomSelect = document.getElementById('roomSelect');
            if (!roomSelect || !roomSelect.value) return;
            var opt = roomSelect.options[roomSelect.selectedIndex];
            if (!opt) return;

            var uploadBaseUrl = '<?= UPLOAD_URL ?>';
            var fmtType = function(type) {
                var labels = {bedspacer: 'Bedspace', single: 'Single', studio: 'Studio'};
                return labels[type] || type;
            };
            var fmtMoney = function(val) {
                var symbol = '<?= e(getCurrencySymbol()) ?>';
                return symbol + Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            };

            document.getElementById('roomd-title').textContent = opt.dataset.roomName || 'Room Details';

            var img = document.getElementById('roomd-image');
            var thumbs = document.getElementById('roomd-thumbs');
            var lightboxImages = [];
            var lightboxIndex = 0;

            var images = [];
            try { images = JSON.parse(opt.dataset.images || '[]'); } catch (e) { images = []; }

            if (images.length > 0) {
                lightboxImages = images.map(function(p) { return uploadBaseUrl + p.image_path; });
                img.src = lightboxImages[0];
                img.classList.remove('d-none');

                if (images.length > 1) {
                    thumbs.innerHTML = '';
                    images.forEach(function(p, i) {
                        var t = document.createElement('img');
                        t.src = uploadBaseUrl + p.image_path;
                        t.alt = '';
                        if (i === 0) t.className = 'active';
                        t.style.cssText = 'width:60px;height:45px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid ' + (i === 0 ? '#2563eb' : '#e2e8f0') + ';transition:border-color 0.2s;';
                        t.dataset.index = i;
                        t.onclick = function() {
                            lightboxIndex = parseInt(this.dataset.index, 10);
                            updateRoomDetailsLightbox();
                            openRoomDetailsLightbox();
                        };
                        t.onmouseover = function() { this.style.borderColor = '#93c5fd'; };
                        t.onmouseout = function() { this.style.borderColor = parseInt(this.dataset.index, 10) === lightboxIndex ? '#2563eb' : '#e2e8f0'; };
                        thumbs.appendChild(t);
                    });
                    thumbs.style.display = 'flex';
                } else {
                    thumbs.innerHTML = '';
                    thumbs.style.display = 'none';
                }
            } else {
                img.src = '';
                img.classList.add('d-none');
                thumbs.innerHTML = '';
                thumbs.style.display = 'none';
            }

            img.onclick = function() {
                if (lightboxImages.length > 0) {
                    lightboxIndex = 0;
                    updateRoomDetailsLightbox();
                    openRoomDetailsLightbox();
                }
            };

            function updateRoomDetailsLightbox() {
                var lbImg = document.getElementById('roomDetailsLightboxImage');
                var lbCounter = document.getElementById('roomDetailsLightboxCounter');
                if (lbImg) lbImg.src = lightboxImages[lightboxIndex];
                if (lbCounter) lbCounter.textContent = (lightboxIndex + 1) + ' / ' + lightboxImages.length;
            }

            function openRoomDetailsLightbox() {
                var modal = document.getElementById('roomDetailsLightbox');
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    updateRoomDetailsLightbox();
                }
            }

            window.closeRoomDetailsLightbox = function() {
                var modal = document.getElementById('roomDetailsLightbox');
                if (modal) modal.style.display = 'none';
                document.body.style.overflow = '';
            };

            window.roomDetailsLightboxNext = function() {
                lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
                updateRoomDetailsLightbox();
            };

            window.roomDetailsLightboxPrev = function() {
                lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
                updateRoomDetailsLightbox();
            };

            var modalEl = document.getElementById('roomDetailsModal');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function() {
                    closeRoomDetailsLightbox();
                });
            }

            document.getElementById('roomd-number').textContent = opt.dataset.roomNumber || 'â€”';
            document.getElementById('roomd-type').textContent = fmtType(opt.dataset.type);
            document.getElementById('roomd-rent').textContent = fmtMoney(opt.dataset.rent);
            document.getElementById('roomd-advance').textContent = fmtMoney(opt.dataset.advance);
            document.getElementById('roomd-floor').textContent = opt.dataset.floor ? 'Floor ' + opt.dataset.floor : 'â€”';
            document.getElementById('roomd-size').textContent = opt.dataset.sizeSqm ? opt.dataset.sizeSqm + ' mÂ²' : 'â€”';

            var cap = Math.max(1, parseInt(opt.dataset.capacity, 10) || 1);
            var occ = parseInt(opt.dataset.occupancy, 10) || 0;
            var avail = Math.max(0, cap - occ);
            var availEl = document.getElementById('roomd-avail');
            if (avail > 0) {
                availEl.innerHTML = '<span class="badge bg-success">' + avail + ' slot' + (avail !== 1 ? 's' : '') + ' left</span> <span class="text-muted small ms-1">(' + occ + '/' + cap + ' occupied)</span>';
            } else {
                availEl.innerHTML = '<span class="badge bg-danger">Full</span>';
            }

            var am = document.getElementById('roomd-amenities');
            var amen = '';
            if (parseInt(opt.dataset.hasAircon, 10)) amen += '<span class="badge bg-light text-dark" style="font-size:.7rem;"><i class="fas fa-snowflake me-1"></i>Aircon</span>';
            if (parseInt(opt.dataset.hasBathroom, 10)) amen += '<span class="badge bg-light text-dark" style="font-size:.7rem;"><i class="fas fa-bath me-1"></i>Bathroom</span>';
            if (parseInt(opt.dataset.hasBalcony, 10)) amen += '<span class="badge bg-light text-dark" style="font-size:.7rem;"><i class="fas fa-building me-1"></i>Balcony</span>';
            am.innerHTML = amen || '<span class="text-muted small">Standard room</span>';

            var furniture = opt.dataset.furniture || '';
            document.getElementById('roomd-furniture').textContent = furniture;
            document.getElementById('roomd-furn-wrap').style.display = furniture ? 'block' : 'none';

            var desc = opt.dataset.description || '';
            document.getElementById('roomd-description').textContent = desc;
            document.getElementById('roomd-desc-wrap').style.display = desc ? 'block' : 'none';

            var rules = opt.dataset.houseRules || '';
            document.getElementById('roomd-rules').textContent = rules;
            document.getElementById('roomd-rules-wrap').style.display = rules ? 'block' : 'none';

            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('roomDetailsModal'));
            modal.show();
        });
    }
    // Send Verification Code: show progress while the email is delivered
    var sendCodeBtn = document.getElementById('sendCodeBtn');
    if (sendCodeBtn) {
        var sendOriginalHtml = sendCodeBtn.innerHTML;
        sendCodeRequested = false;
        sendCodeBtn.addEventListener('click', function() { sendCodeRequested = true; });
        window.addEventListener('pageshow', function() {
            sendCodeRequested = false;
            sendCodeBtn.disabled = false;
            sendCodeBtn.innerHTML = sendOriginalHtml;
        });
    }
});

// Global keydown handler for lightboxes (added once)
document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('roomDetailsLightbox');
    if (lb && lb.style.display === 'flex') {
        if (e.key === 'Escape') closeRoomDetailsLightbox();
        if (e.key === 'ArrowRight') roomDetailsLightboxNext();
        if (e.key === 'ArrowLeft') roomDetailsLightboxPrev();
    }
    var rib = document.getElementById('roomInfoLightbox');
    if (rib && rib.style.display === 'flex') {
        if (e.key === 'Escape') closeRoomInfoLightbox();
        if (e.key === 'ArrowRight') roomInfoLightboxNext();
        if (e.key === 'ArrowLeft') roomInfoLightboxPrev();
    }
});
</script>
