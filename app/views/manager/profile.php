<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Profile</h1>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-5">
                <?php if (!empty($manager['profile_picture'])): ?>
                    <img src="<?= UPLOAD_URL . $manager['profile_picture'] ?>" alt="Profile" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover">
                <?php else: ?>
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px">
                        <i class="fas fa-user fa-3x text-primary"></i>
                    </div>
                <?php endif; ?>
                <h5 class="mb-1"><?= e($manager['first_name'] . ' ' . $manager['last_name']) ?></h5>
                <p class="text-muted mb-0">Manager</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/manager/profile') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?= e($manager['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="<?= e($manager['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= e($manager['phone'] ?? '') ?>" inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Please enter a valid 11-digit Philippine mobile number starting with 09.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= e($manager['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
