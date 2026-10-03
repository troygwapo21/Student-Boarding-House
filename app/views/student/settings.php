<div class="page-header">
    <h4>Settings</h4>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Update Email -->
        <div class="content-card mb-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-envelope me-2"></i>Update Email</h6>
            <form method="POST" action="<?= url('/student/settings') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_email">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?= e($_SESSION['user_email'] ?? '') ?>" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Update Email</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="content-card">
            <h6 class="fw-bold mb-3"><i class="fas fa-key me-2"></i>Password</h6>
            <p class="text-muted small">Keep your account secure by using a strong password.</p>
            <a href="<?= url('/student/change-password') ?>" class="btn btn-outline-primary">
                <i class="fas fa-key me-1"></i> Change Password
            </a>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="content-card border-danger">
            <h6 class="fw-bold text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
            <p class="text-muted small" style="font-size: 13px;">Once you delete your account, there is no going back. This will permanently delete your profile, reservations, and all associated data.</p>
            <form method="POST" action="<?= url('/student/settings') ?>" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_account">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="fas fa-trash me-1"></i> Delete My Account
                </button>
            </form>
        </div>
    </div>
</div>
