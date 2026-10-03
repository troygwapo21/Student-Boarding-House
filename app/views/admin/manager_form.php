<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= isset($manager) ? 'Edit Manager' : 'Create Manager' ?></h1>
    <a href="<?= url('/admin/manage-managers') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Managers</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Account Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url(isset($manager) ? '/admin/manager/edit/' . $manager['id'] : '/admin/manager/create') ?>" enctype="multipart/form-data" id="managerForm">
                    <?= csrf_field() ?>
                    <?php if (!empty($manager)): ?>
                        <input type="hidden" name="id" value="<?= $manager['id'] ?>">
                    <?php endif; ?>
                    <div class="row g-3">
                        <?php if (!isset($manager)): ?>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?= e($manager['email'] ?? '') ?>" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Manager Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="pw-field-wrap">
                                    <div class="pw-input-group">
                                        <input type="password" name="password" class="form-control" minlength="8" required data-pw-validate>
                                        <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                    </div>
                                    <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                                </div>
                                <small class="text-muted">Min 8 chars, uppercase, lowercase, number & special character.</small>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?= e($manager['email'] ?? '') ?>" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Manager Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <div class="pw-field-wrap">
                                    <div class="pw-input-group">
                                        <input type="password" name="password" class="form-control" minlength="8">
                                        <label class="pw-show-cb"><input type="checkbox"> Show</label>
                                    </div>
                                    <div class="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <h6 class="text-muted mb-0 mt-2"><i class="fas fa-id-card me-2"></i>Personal Information</h6>
                            <hr class="mt-2 mb-3">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?= e($manager['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="<?= e($manager['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone" class="form-control" value="<?= e($manager['phone'] ?? '') ?>" placeholder="09123456789" inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Please enter a valid 11-digit Philippine mobile number starting with 09.">
                            </div>
                            <small class="text-muted">Please enter a valid 11-digit Philippine mobile number starting with 09</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <?php $isCurrentUser = isset($manager) && ((int)$manager['user_id'] === (int)($_SESSION['user_id'] ?? 0)); ?>
                            <select name="status" class="form-select" <?= $isCurrentUser ? 'disabled' : '' ?>>
                                <option value="active" <?= ($manager['user_status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($manager['user_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="suspended" <?= ($manager['user_status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                            <?php if ($isCurrentUser): ?>
                                <input type="hidden" name="status" value="<?= e($manager['user_status'] ?? 'active') ?>">
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>You cannot change your own status.</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/jpeg,image/png,image/gif" id="profilePictureInput">
                            <small class="text-muted">JPG, PNG or GIF. Max 2MB.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Enter full address..."><?= e($manager['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website / Social Link</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input type="url" name="link" class="form-control" value="<?= e($manager['link'] ?? '') ?>" placeholder="https://example.com">
                            </div>
                            <small class="text-muted">Full URL including https://</small>
                        </div>
                        <?php if (isset($manager)): ?>
                        <div class="col-12 mt-3">
                            <h6 class="text-muted mb-0 mt-2"><i class="fas fa-shield-alt me-2"></i>Update Verification</h6>
                            <hr class="mt-2 mb-3">
                            <p class="small text-muted mb-2">A 6-digit verification code is required to save this update. The code will be emailed to <strong><?= e($manager['email']) ?></strong>. Click <strong>Send Code</strong>, enter it here, then click <strong>Update Manager</strong>.</p>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <div class="input-group" style="max-width:340px;">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="text" name="verification_code" id="verificationCode" class="form-control" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="off">
                                </div>
                                <button type="button" id="sendManagerCodeBtn" class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane me-1"></i>Send Code</button>
                            </div>
                            <div id="managerCodeStatus" class="small mt-2"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i><?= isset($manager) ? 'Update' : 'Create' ?> Manager</button>
                        <a href="<?= url('/admin/manage-managers') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-camera me-2 text-primary"></i>Profile Photo</h6>
            </div>
            <div class="card-body text-center">
                <div id="imagePreview">
                    <?php if (!empty($manager['profile_picture'])): ?>
                        <img src="<?= UPLOAD_URL . $manager['profile_picture'] ?>" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover" alt="Profile" id="currentPhoto">
                    <?php else: ?>
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:120px;height:120px" id="placeholderPhoto">
                            <i class="fas fa-user text-secondary fa-3x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="newImagePreview" class="d-none">
                    <img src="" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover" alt="New Photo" id="newPhoto">
                    <div><small class="text-success"><i class="fas fa-check me-1"></i>New photo selected</small></div>
                </div>
            </div>
        </div>

        <?php if (isset($manager)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">Role</td>
                        <td class="fw-semibold">Manager</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><?= statusBadge($manager['user_status'] ?? 'active') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Joined</td>
                        <td><?= formatDate($manager['created_at'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Website</td>
                        <td>
                            <?php if (!empty($manager['link'])): ?>
                                <a href="<?= e($manager['link']) ?>" class="text-primary small" target="_blank"><i class="fas fa-external-link-alt me-1"></i><?= e(parse_url($manager['link'], PHP_URL_HOST) ?? $manager['link']) ?></a>
                            <?php else: ?>
                                <span class="small text-muted">None</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">User ID</td>
                        <td class="small">#<?= e($manager['user_id']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var fileInput = document.getElementById('profilePictureInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('File is too large. Maximum size is 2MB.');
                fileInput.value = '';
                return;
            }
            if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
                alert('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                fileInput.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(ev) {
                var preview = document.getElementById('newImagePreview');
                var newPhoto = document.getElementById('newPhoto');
                newPhoto.src = ev.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('sendManagerCodeBtn');
    var statusEl = document.getElementById('managerCodeStatus');
    if (!btn || !statusEl) return;

    var sendUrl = <?= isset($manager) ? json_encode(url('/admin/manager/send-update-code/' . (int)$manager['id'])) : '""' ?>;
    var managerId = <?= isset($manager) ? (int)$manager['id'] : 0 ?>;
    var managerEmail = <?= isset($manager) ? json_encode($manager['email'] ?? '') : '""' ?>;
    var token = <?= json_encode(csrf_token()) ?>;

    function setStatus(message, type) {
        var icon = type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-circle' : 'info-circle');
        var color = type === 'success' ? 'success' : (type === 'danger' ? 'danger' : 'muted');
        statusEl.innerHTML = '<span class="text-' + color + '"><i class="fas fa-' + icon + ' me-1"></i>' + message + '</span>';
    }

    function startCooldown() {
        var remaining = 60;
        btn.innerHTML = 'Resend in <span id="codeCooldown">' + remaining + 's</span>';
        var timer = setInterval(function() {
            remaining--;
            var el = document.getElementById('codeCooldown');
            if (el) el.textContent = remaining + 's';
            if (remaining <= 0) {
                clearInterval(timer);
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Code';
                btn.disabled = false;
            }
        }, 1000);
    }

    btn.addEventListener('click', function() {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
        setStatus('Sending verification code to ' + managerEmail + '...');
        fetch(sendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
            body: 'id=' + encodeURIComponent(managerId)
        })
        .then(function(r) {
            return r.json().then(function(d) { return { ok: r.ok, data: d }; });
        })
        .then(function(res) {
            var d = res.data || {};
            if (res.ok && d.success) {
                setStatus(d.message, 'success');
                var codeInput = document.getElementById('verificationCode');
                if (codeInput) { codeInput.disabled = false; codeInput.focus(); }
            } else {
                setStatus(d.message || 'Failed to send the code. Please try again.', 'danger');
            }
            startCooldown();
        })
        .catch(function() {
            setStatus('Something went wrong while sending the code. Please try again.', 'danger');
            startCooldown();
        });
    });
});
</script>
