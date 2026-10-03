<div class="page-header d-flex justify-content-between align-items-center">
    <h4>My Profile</h4>
    <a href="<?= url('/student/change-password') ?>" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-key me-1"></i> Change Password
    </a>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= e($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= e($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<form method="POST" action="<?= url('/student/profile') ?>" enctype="multipart/form-data" id="profileForm">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Profile Picture & IDs -->
        <div class="col-lg-4">
            <div class="content-card text-center mb-4">
                <div class="mb-3">
                    <?php if (!empty($student['profile_picture'])): ?>
                        <img src="<?= UPLOAD_URL . $student['profile_picture'] ?>" alt="Profile" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    <?php else: ?>
                        <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 120px; height: 120px; background: #e0e7ff; color: #4f46e5; font-size: 40px; font-weight: 700;">
                            <?= strtoupper(substr($student['first_name'] ?? 'S', 0, 1) . substr($student['last_name'] ?? 'T', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold">
                    <?= e($student['first_name'] ?? '') ?>
                    <?= e($student['middle_name'] ?? '') ?>
                    <?= e($student['last_name'] ?? '') ?>
                    <?= !empty($student['suffix']) ? e($student['suffix']) : '' ?>
                </h5>
                <p class="text-muted small mb-3"><?= e($_SESSION['user_email'] ?? '') ?></p>

                <div class="text-start">
                    <label class="form-label fw-semibold small">Profile Picture</label>
                    <input type="file" class="form-control form-control-sm" name="profile_picture" accept="image/*">
                    <small class="text-muted">Max 2MB. JPG, PNG, GIF</small>
                </div>
            </div>

            <div class="content-card mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-id-card me-2"></i>Valid ID</h6>
                <?php if (!empty($student['valid_id_path'])): ?>
                    <div class="mb-3">
                        <a href="<?= UPLOAD_URL . $student['valid_id_path'] ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i> View Uploaded ID
                        </a>
                    </div>
                <?php endif; ?>
                <label class="form-label fw-semibold small">Upload Valid ID</label>
                <input type="file" class="form-control form-control-sm" name="valid_id" accept="image/*,.pdf">
                <small class="text-muted">Max 5MB. JPG, PNG, PDF</small>
            </div>

            <div class="content-card">
                <h6 class="fw-bold mb-3"><i class="fas fa-graduation-cap me-2"></i>School ID</h6>
                <?php if (!empty($student['school_id_path'])): ?>
                    <div class="mb-3">
                        <a href="<?= UPLOAD_URL . $student['school_id_path'] ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i> View School ID
                        </a>
                    </div>
                <?php endif; ?>
                <label class="form-label fw-semibold small">Upload School ID</label>
                <input type="file" class="form-control form-control-sm" name="school_id_upload" accept="image/*,.pdf">
                <small class="text-muted">Max 5MB. JPG, PNG, PDF</small>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="content-card mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-user me-2"></i>Personal Information</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="first_name" value="<?= e($student['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Middle Name</label>
                        <input type="text" class="form-control" name="middle_name" value="<?= e($student['middle_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="last_name" value="<?= e($student['last_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Suffix</label>
                        <select name="suffix" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach(['Jr.','Sr.','II','III','IV','V'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($student['suffix'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" name="gender" required>
                            <option value="">-- Select --</option>
                            <option value="male" <?= ($student['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($student['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date_of_birth" id="profileDob" value="<?= e($student['date_of_birth'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Age</label>
                        <input type="text" class="form-control" id="profileAge" readonly placeholder="Auto-computed" style="background:#f1f5f9;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Civil Status <span class="text-danger">*</span></label>
                        <select name="civil_status" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php foreach(['single'=>'Single','married'=>'Married','widowed'=>'Widowed','separated'=>'Separated','divorced'=>'Divorced'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($student['civil_status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nationality <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nationality" value="<?= e($student['nationality'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="phone" value="<?= e($student['phone'] ?? '') ?>" required inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Please enter a valid 11-digit Philippine mobile number starting with 09.">
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="content-card mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">House/Unit No.</label>
                        <input type="text" class="form-control" name="house_unit" value="<?= e($student['house_unit'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Street</label>
                        <input type="text" class="form-control" name="street" value="<?= e($student['street'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Barangay <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="barangay" value="<?= e($student['barangay'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Municipality/City <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="municipality_city" value="<?= e($student['municipality_city'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="province" value="<?= e($student['province'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">ZIP Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="zip_code" value="<?= e($student['zip_code'] ?? '') ?>" maxlength="4" pattern="[0-9]{4}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Full Address (combined)</label>
                        <input type="text" class="form-control" id="fullAddressPreview" readonly style="background:#f1f5f9;" placeholder="Auto-composed">
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="content-card mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Student ID Number</label>
                        <input type="text" class="form-control" name="student_id_number" value="<?= e($student['student_id_number'] ?? '') ?>" placeholder="e.g. 2026-001 (or N/A)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">School/College <span class="text-danger">*</span></label>
                        <?php
                        $schools = [
                            'Madridejos Community College'                            => 'Madridejos Community College (MCC)',
                            'Salazar Colleges of Science and Institute of Technology' => 'Salazar Colleges of Science and Institute of Technology (SCSIT)',
                            'Cebu North Plains College'                               => 'Cebu North Plains College (CNPC)',
                            'Cebu Technological University'                           => 'Cebu Technological University (CTU)',
                        ];
                        $selSchool = $student['school_university'] ?? '';
                        $isKnown   = $selSchool === '' || isset($schools[$selSchool]);
                        ?>
                        <select class="form-select" name="school_university" required>
                            <option value="">-- Select School/College --</option>
                            <?php foreach ($schools as $val=>$label): ?>
                                <option value="<?= e($val) ?>" <?= $selSchool===$val ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                            <?php if (!$isKnown): ?>
                                <option value="<?= e($selSchool) ?>" selected><?= e($selSchool) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Course/Program <span class="text-danger">*</span></label>
                        <select class="form-select" name="course_program" required>
                            <option value="">-- Select Course Program --</option>
                            <?php
                            $courses = ['BSIT'=>'BSIT','BSHM'=>'BSHM','BSED'=>'BSED','BSBA'=>'BSBA','BSCE'=>'BSCE','BSCRIM'=>'BSCRIM'];
                            $labels = ['BSIT'=>'Bachelor of Science in Information Technology (BSIT)','BSHM'=>'Bachelor of Science in Hospitality Management (BSHM)','BSED'=>'Bachelor of Secondary Education (BSED)','BSBA'=>'Bachelor of Science in Business Administration (BSBA)','BSCE'=>'Bachelor of Science in Civil Engineering (BSCE)','BSCRIM'=>'Bachelor of Science in Criminology (BSCRIM)'];
                            foreach ($courses as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= ($student['course_program'] ?? '') === $k ? 'selected' : '' ?>><?= $labels[$k] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Year Level <span class="text-danger">*</span></label>
                        <select class="form-select" name="year_level" required>
                            <option value="">-- Select Year Level --</option>
                            <?php foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $yl): ?>
                            <option value="<?= $yl ?>" <?= ($student['year_level'] ?? '') === $yl ? 'selected' : '' ?>><?= $yl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('/student/profile') ?>" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Age auto-compute from DOB
var dobInput = document.getElementById('profileDob');
    var ageInput = document.getElementById('profileAge');

    if (dobInput && typeof flatpickr !== 'undefined') {
        flatpickr(dobInput, {
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            allowInput: true
        });
    }

    function computeAge() {
        if (!dobInput.value) { ageInput.value = ''; return; }
        var parts = dobInput.value.split('-');
        var birth = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        var today = new Date();
        var age = today.getFullYear() - birth.getFullYear();
        var m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        ageInput.value = age >= 0 ? age : '';
    }
    if (dobInput.value) computeAge();
    dobInput.addEventListener('change', computeAge);

    // Full address preview
    var fields = ['house_unit','street','barangay','municipality_city','province','zip_code'];
    var preview = document.getElementById('fullAddressPreview');
    function updatePreview() {
        var parts = [];
        fields.forEach(function(f) {
            var el = document.querySelector('[name="' + f + '"]');
            if (el && el.value.trim()) parts.push(el.value.trim());
        });
        preview.value = parts.join(', ');
    }
    fields.forEach(function(f) {
        var el = document.querySelector('[name="' + f + '"]');
        if (el) el.addEventListener('input', updatePreview);
    });
    updatePreview();
});
</script>
