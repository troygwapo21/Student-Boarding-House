<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Change Password</h4>
    <a href="<?= url('/student/settings') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Settings
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="content-card">
            <div class="text-center mb-4">
                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fas fa-lock fa-2x"></i>
                </div>
                <h5 class="fw-bold">Change Your Password</h5>
                <p class="text-muted small">Enter your current password and choose a new one.</p>
            </div>

            <form method="POST" action="<?= url('/student/change-password') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                    <div class="pw-field-wrap">
                        <div class="pw-input-group">
                            <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                            <label class="pw-show-cb"><input type="checkbox"> Show</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                    <div class="pw-field-wrap">
                        <div class="pw-input-group">
                            <input type="password" class="form-control" name="new_password" id="newPassword" required minlength="8" autocomplete="new-password" data-pw-validate>
                            <label class="pw-show-cb"><input type="checkbox"> Show</label>
                        </div>
                        <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                    <div class="pw-field-wrap">
                        <div class="pw-input-group">
                            <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            <label class="pw-show-cb"><input type="checkbox"> Show</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/student/settings') ?>" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
