<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Profile</h1>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-5">
                <?php if (!empty($admin['profile_picture'])): ?>
                    <img src="<?= UPLOAD_URL . $admin['profile_picture'] ?>" alt="Profile" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover">
                <?php else: ?>
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px">
                        <i class="fas fa-user-shield fa-3x text-danger"></i>
                    </div>
                <?php endif; ?>
                <h5 class="mb-1"><?= e($_SESSION['user_email'] ?? 'Admin') ?></h5>
                <p class="text-muted mb-2">Super Administrator</p>
                <span class="badge bg-danger">Super Admin</span>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/profile') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($admin['email'] ?? '') ?>" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="Super Admin" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="<?= e(ucfirst($admin['status'] ?? 'active')) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registered</label>
                            <input type="text" class="form-control" value="<?= formatDate($admin['created_at'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
