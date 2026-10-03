<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:80px;height:80px;background:linear-gradient(135deg,#22c55e15,#22c55e05);color:#22c55e;">
                                <i class="fas fa-lock fa-2x"></i>
                            </div>
                            <h3 class="fw-bold" style="color:#1e293b;">Reset Password</h3>
                            <p class="text-muted">Enter your new password below.</p>
                        </div>

                        <form action="<?= url('/reset-password') ?>" method="POST">
                            <?= csrf_field() ?>
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
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-semibold">
                                <i class="fas fa-sync-alt me-2"></i> Reset Password
                            </button>
                        </form>

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
