<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $siteKey = $siteKey ?? ''; ?>
<style>
    .pw-input-group{position:relative}
    .pw-input-group .form-control{display:block;width:100%;border-radius:16px;background:#f1f5f9;border:1.5px solid #dee2e6;padding:.8rem 3rem .8rem 1rem;font-family:inherit;font-size:.9rem;color:#212529;transition:all .2s ease;outline:none}
    .pw-input-group .form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12);background:#fff}
    .pw-input-group .form-control::placeholder{color:#94a3b8}
    .pw-field-wrap:has(.pw-show-cb input:checked) .pw-validation,
    .pw-field-wrap:has(.pw-show-cb input:checked) .pw-strength-bar{display:none!important}
    .pw-input-group.pw-visible .form-control{border-color:rgba(37,99,235,.4);background:#f8faff;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
    .pw-show-cb{display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:#64748b;cursor:pointer;user-select:none;-webkit-user-select:none;white-space:nowrap;padding:.25rem 0;transition:color .2s ease}
    .pw-show-cb:hover{color:#2563eb}
    .pw-show-cb input[type="checkbox"]{width:15px;height:15px;accent-color:#2563eb;cursor:pointer;margin:0}
    .pw-strength-bar{height:4px;border-radius:2px;background:#e2e8f0;margin-top:.45rem;overflow:hidden}
    .pw-strength-fill{height:100%;border-radius:2px;width:0%;transition:width .3s ease,background .3s ease}
</style>

<section class="d-flex align-items-center justify-content-center" style="min-height:80vh;background:linear-gradient(135deg,#f8fafc,#e2e8f0);">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg" style="border-radius:16px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <a href="<?= url('/') ?>" class="text-decoration-none">
                                <i class="fas fa-home fa-2x mb-2" style="color:#2563eb;"></i>
                            </a>
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:linear-gradient(135deg,#2563eb15,#2563eb05);color:#2563eb;">
                                <i class="fas fa-key fa-2x"></i>
                            </div>
                            <h3 class="fw-bold" style="color:#1e293b;">Forgot Password?</h3>
                            <p class="text-muted">Enter your registered email, current password, and set a new password.</p>
                        </div>

                        <form action="<?= url('/forgot-password') ?>" method="POST" id="forgotPasswordForm">
                            <?= csrf_field() ?>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Registered Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control form-control-lg border-start-0" placeholder="example@gmail.com" required autofocus pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Registered Email Address: Please enter a valid Gmail address ending with @gmail.com." value="<?= e((string)($_POST['email'] ?? '')) ?>">
                                </div>
                                <div class="text-danger small mt-1 d-none" id="emailError"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Current Password <span class="text-danger">*</span></label>
                                <div class="pw-field-wrap">
                                    <div class="pw-input-group">
                                        <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required id="currentPassword">
                                        <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">New Password <span class="text-danger">*</span></label>
                                <div class="pw-field-wrap">
                                    <div class="pw-input-group">
                                        <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8" id="password" data-pw-validate>
                                        <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                    </div>
                                    <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="pw-field-wrap">
                                    <div class="pw-input-group">
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password" required data-pw-match>
                                        <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 d-flex justify-content-center">
                                <div class="g-recaptcha" data-sitekey="<?= e($siteKey ?? '') ?>"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-semibold">
                                <i class="fas fa-unlock-alt me-2"></i> Change Password
                            </button>
                        </form>

                        <script>
                            (function () {
                                var form = document.getElementById('forgotPasswordForm');
                                var emailInput = form.querySelector('input[name="email"]');
                                var emailError = document.getElementById('emailError');
                                form.addEventListener('submit', function (e) {
                                    var value = emailInput.value.trim();
                                    var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                                    if (!valid) {
                                        e.preventDefault();
                                        emailError.textContent = 'Please enter a valid email address.';
                                        emailError.classList.remove('d-none');
                                        emailInput.focus();
                                    }
                                });
                                emailInput.addEventListener('input', function () {
                                    emailError.classList.add('d-none');
                                });
                            })();
                        </script>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="text-muted mb-0">Remember your password? <a href="<?= url('/login') ?>" class="text-primary fw-semibold text-decoration-none">Login here</a></p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= url('/') ?>" class="text-muted text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
