<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Settings</h1>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/manager/settings') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="pw-field-wrap">
                            <div class="pw-input-group">
                                <input type="password" name="current_password" class="form-control" required>
                                <label class="pw-show-cb"><input type="checkbox"> Show</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="pw-field-wrap">
                            <div class="pw-input-group">
                                <input type="password" name="new_password" class="form-control" minlength="8" required data-pw-validate>
                                <label class="pw-show-cb"><input type="checkbox"> Show</label>
                            </div>
                            <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="pw-field-wrap">
                            <div class="pw-input-group">
                                <input type="password" name="password_confirmation" class="form-control" required>
                                <label class="pw-show-cb"><input type="checkbox"> Show</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Password</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Update Email</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/manager/settings') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_email">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= e($_SESSION['user_email'] ?? '') ?>" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Email</button>
                </form>
            </div>
        </div>
    </div>
</div>
