<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">About Page Editor</h4>
    <a href="<?= url('/about') ?>" class="btn btn-outline-primary btn-sm" target="_blank">
        <i class="fas fa-external-link-alt me-1"></i> View Live Page
    </a>
</div>

<ul class="nav nav-tabs mb-4" id="aboutTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">
            <i class="fas fa-cog me-1"></i> General Settings
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#team" type="button">
            <i class="fas fa-users me-1"></i> Team Members (<?= count($teamMembers) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#values" type="button">
            <i class="fas fa-heart me-1"></i> Values (<?= count($aboutValues) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- ======================== GENERAL SETTINGS ======================== -->
    <div class="tab-pane fade show active" id="general">
        <form method="POST" action="<?= url('/admin/about') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="section" value="general">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-heading me-2"></i>Page Header</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Page Title</label>
                                <input type="text" class="form-control" name="about_title" value="<?= e($aboutSettings['about_title'] ?? 'About Us') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Page Subtitle</label>
                                <input type="text" class="form-control" name="about_subtitle" value="<?= e($aboutSettings['about_subtitle'] ?? 'Learn more about ' . getSiteName() . '.') ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Badge Label</label>
                                    <input type="text" class="form-control" name="about_years_label" value="<?= e($aboutSettings['about_years_label'] ?? '5+ Years') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Badge Sub-label</label>
                                    <input type="text" class="form-control" name="about_years_sublabel" value="<?= e($aboutSettings['about_years_sublabel'] ?? 'of Service') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-image me-2"></i>Intro Content</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">About Intro Text</label>
                                <textarea class="form-control" name="about_text" rows="2" placeholder="Short intro text shown above the story section on the About page"><?= e($aboutSettings['about_text'] ?? '') ?></textarea>
                                <div class="form-text">Displayed as a centered paragraph below the page header on the About page.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Story Image</label>
                                <?php if (!empty($aboutSettings['about_image'])): ?>
                                    <div class="mb-2 position-relative d-inline-block" id="currentImageWrap">
                                        <img src="<?= UPLOAD_URL . $aboutSettings['about_image'] ?>" alt="Current about image" class="rounded border" id="currentAboutImage" style="max-height:200px;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top:6px;right:6px;" onclick="removeAboutImage()" title="Remove image"><i class="fas fa-times"></i></button>
                                    </div>
                                    <input type="hidden" name="remove_about_image" id="removeAboutImage" value="0">
                                    <div class="form-text" id="currentImageText">Current image. Upload a new one to replace, or click <i class="fas fa-times text-danger"></i> to remove.</div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="about_image" id="aboutImageInput" accept=".jpg,.jpeg,.png,.gif,.webp">
                                <div id="aboutImagePreview" class="mt-2"></div>
                                <div class="form-text">Main image displayed in the "Our Story" section. JPG, PNG, GIF, WebP. Max 5MB.</div>
                            </div>
                            <script>
                            (function(){
                                var input = document.getElementById('aboutImageInput');
                                if (!input) return;
                                input.addEventListener('change', function(){
                                    var preview = document.getElementById('aboutImagePreview');
                                    preview.innerHTML = '';
                                    if (this.files && this.files[0]) {
                                        var f = this.files[0];
                                        if (f.size > 5*1024*1024) { alert('File is too large. Max 5MB.'); this.value=''; return; }
                                        if (!['image/jpeg','image/png','image/gif','image/webp'].includes(f.type)) { alert('Invalid file type.'); this.value=''; return; }
                                        var reader = new FileReader();
                                        reader.onload = function(e){
                                            preview.innerHTML = '<img src="'+e.target.result+'" class="rounded border" style="max-height:200px;">';
                                        };
                                        reader.readAsDataURL(f);
                                    }
                                });
                            })();
                            function removeAboutImage(){
                                document.getElementById('removeAboutImage').value = '1';
                                document.getElementById('currentImageWrap').style.opacity = '0.3';
                                document.getElementById('currentImageText').innerHTML = '<em>Image will be removed on save.</em>';
                            }
                            </script>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-book-open me-2"></i>Our Story</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Story Title</label>
                                <input type="text" class="form-control" name="about_story_title" value="<?= e($aboutSettings['about_story_title'] ?? 'Welcome to ' . getSiteName()) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Story Text</label>
                                <small class="text-muted d-block mb-1">Use <code>||</code> to separate paragraphs.</small>
                                <textarea class="form-control" name="about_story_text" rows="5"><?= e($aboutSettings['about_story_text'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-bullseye me-2"></i>Mission & Vision</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mission Statement</label>
                                <textarea class="form-control" name="about_mission" rows="4"><?= e($aboutSettings['about_mission'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vision Statement</label>
                                <textarea class="form-control" name="about_vision" rows="4"><?= e($aboutSettings['about_vision'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2"></i>Team Section Header</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Section Title</label>
                                    <input type="text" class="form-control" name="about_team_title" value="<?= e($aboutSettings['about_team_title'] ?? 'Meet Our Team') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Section Subtitle</label>
                                    <input type="text" class="form-control" name="about_team_subtitle" value="<?= e($aboutSettings['about_team_subtitle'] ?? 'The dedicated people behind ' . getSiteName() . '.') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-heart me-2"></i>Values Section Header</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Section Title</label>
                                <input type="text" class="form-control" name="about_values_title" value="<?= e($aboutSettings['about_values_title'] ?? 'What We Stand For') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center">
                            <i class="fas fa-info-circle fa-2x text-primary mb-2"></i>
                            <h6 class="fw-bold">Tips</h6>
                            <ul class="text-muted small text-start mb-0">
                                <li>Use <code>||</code> to separate story paragraphs</li>
                                <li>Keep story text concise but meaningful</li>
                                <li>Mission/Vision should be 2-3 sentences each</li>
                                <li>Team members are managed in the Team tab</li>
                                <li>Company values are managed in the Values tab</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- ======================== TEAM MEMBERS ======================== -->
    <div class="tab-pane fade" id="team">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($teamMembers)): ?>
                    <?php foreach ($teamMembers as $member): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:64px;height:64px;background:#e0e7ff;">
                                <?php if (!empty($member['image_path'])): ?>
                                    <img src="<?= UPLOAD_URL . $member['image_path'] ?>" alt="" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-tie fa-lg text-primary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0"><?= e($member['name']) ?></h6>
                                <small class="text-primary"><?= e($member['role']) ?></small>
                                <p class="text-muted small mb-0 mt-1"><?= e(truncate($member['description'] ?? '', 80)) ?></p>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewMember<?= $member['id'] ?>" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editMember<?= $member['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteMember<?= $member['id'] ?>" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- View Member Modal -->
                    <div class="modal fade" id="viewMember<?= $member['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <h5 class="modal-title fw-bold" style="color:#fff;">
                                        <i class="fas fa-user-tie me-2" style="color:#60a5fa;"></i><?= e($member['name']) ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                                </div>
                                <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                                    <div class="text-center mb-4">
                                        <?php if (!empty($member['image_path'])): ?>
                                            <img src="<?= UPLOAD_URL . $member['image_path'] ?>" alt="<?= e($member['name']) ?>" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover;border:3px solid rgba(96,165,250,0.4);">
                                        <?php else: ?>
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:96px;height:96px;background:rgba(96,165,250,0.15);">
                                                <i class="fas fa-user-tie fa-2x" style="color:#60a5fa;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <h5 class="fw-bold mb-0" style="color:#fff;"><?= e($member['name']) ?></h5>
                                        <span class="badge" style="background:rgba(96,165,250,0.2);color:#60a5fa;"><?= e($member['role']) ?></span>
                                    </div>
                                    <div class="nasa-glass-box" style="border-radius:12px;padding:1.25rem;">
                                        <h6 style="color:rgba(255,255,255,0.7);"><i class="fas fa-info-circle me-1"></i>Description</h6>
                                        <p class="mb-0" style="color:rgba(255,255,255,0.85);"><?= e($member['description'] ?? 'No description provided.') ?></p>
                                    </div>
                                    <?php if (!empty($member['facebook_url']) || !empty($member['twitter_url']) || !empty($member['linkedin_url'])): ?>
                                        <div class="nasa-glass-box mt-3" style="border-radius:12px;padding:1rem;">
                                            <h6 style="color:rgba(255,255,255,0.7);"><i class="fas fa-share-alt me-1"></i>Social Links</h6>
                                            <div class="d-flex gap-2">
                                                <?php if (!empty($member['facebook_url'])): ?>
                                                    <a href="<?= e($member['facebook_url']) ?>" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                                        <i class="fab fa-facebook-f"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['twitter_url'])): ?>
                                                    <a href="<?= e($member['twitter_url']) ?>" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                                        <i class="fab fa-twitter"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['linkedin_url'])): ?>
                                                    <a href="<?= e($member['linkedin_url']) ?>" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                                        <i class="fab fa-linkedin-in"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-3">
                                        <span class="badge <?= $member['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:.75rem;">
                                            <?= ucfirst($member['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                                    <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Member Modal -->
                    <div class="modal fade" id="deleteMember<?= $member['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <h5 class="modal-title fw-bold" style="color:#fff;">
                                        <i class="fas fa-trash me-2 text-danger"></i>Remove Team Member
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                                </div>
                                <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                                    <div class="nasa-glass-box" style="background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);border-radius:12px;padding:1.25rem;">
                                        <p class="mb-0" style="color:rgba(255,255,255,0.85);">
                                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                            Are you sure you want to permanently remove team member
                                            <strong style="color:#fff;"><?= e($member['name']) ?></strong>?
                                        </p>
                                        <p class="mt-2 mb-0" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                                    <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST" action="<?= url('/admin/about') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="section" value="team_member_delete">
                                        <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Remove Member</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Member Modal -->
                    <div class="modal fade" id="editMember<?= $member['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="border-radius:12px;">
        <form method="POST" action="<?= url('/admin/about') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="team_member_edit">
                                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold">Edit Team Member</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" value="<?= e($member['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="role" value="<?= e($member['role']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" name="description" rows="3"><?= e($member['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Profile Image</label>
                                            <input type="file" class="form-control" name="image" accept="image/*">
                                            <small class="text-muted">Leave empty to keep current image.</small>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Facebook URL</label>
                                                <input type="url" class="form-control" name="facebook_url" value="<?= e($member['facebook_url'] ?? '') ?>" pattern="https?://.*" title="Please enter a valid URL starting with http:// or https://">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Twitter URL</label>
                                                <input type="url" class="form-control" name="twitter_url" value="<?= e($member['twitter_url'] ?? '') ?>" pattern="https?://.*" title="Please enter a valid URL starting with http:// or https://">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">LinkedIn URL</label>
                                                <input type="url" class="form-control" name="linkedin_url" value="<?= e($member['linkedin_url'] ?? '') ?>" pattern="https?://.*" title="Please enter a valid URL starting with http:// or https://">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">No team members yet. Add one below.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-plus me-1"></i> Add Team Member</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('/admin/about') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="section" value="team_member_add">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="role" placeholder="e.g. General Manager" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Brief description of their role..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Profile Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Facebook URL</label>
                                <input type="url" class="form-control" name="facebook_url" placeholder="https://facebook.com/..." pattern="https?://.*" title="Please enter a valid URL starting with http:// or https://">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Twitter URL</label>
                                <input type="url" class="form-control" name="twitter_url" placeholder="https://twitter.com/..." pattern="https?://.*" title="Please enter a valid URL starting with http:// or https://">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">LinkedIn URL</label>
                                <input type="url" class="form-control" name="linkedin_url" placeholder="https://linkedin.com/in/..." pattern="https?://.*" title="Please enter a valid URL starting with http:// or https://">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i> Add Team Member
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================== VALUES ======================== -->
    <div class="tab-pane fade" id="values">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($aboutValues)): ?>
                    <?php foreach ($aboutValues as $value): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;background:<?= e($value['color']) ?>20;color:<?= e($value['color']) ?>;">
                                <i class="<?= e($value['icon']) ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0"><?= e($value['title']) ?></h6>
                                <p class="text-muted small mb-0"><?= e(truncate($value['description'] ?? '', 80)) ?></p>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewValue<?= $value['id'] ?>" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editValue<?= $value['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteValue<?= $value['id'] ?>" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- View Value Modal -->
                    <div class="modal fade" id="viewValue<?= $value['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <h5 class="modal-title fw-bold" style="color:#fff;">
                                        <i class="<?= e($value['icon']) ?> me-2" style="color:<?= e($value['color']) ?>;"></i><?= e($value['title']) ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                                </div>
                                <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                                    <div class="text-center mb-4">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;background:<?= e($value['color']) ?>20;color:<?= e($value['color']) ?>;border:2px solid <?= e($value['color']) ?>40;">
                                            <i class="<?= e($value['icon']) ?>" style="font-size:1.8rem;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-0" style="color:#fff;"><?= e($value['title']) ?></h5>
                                    </div>
                                    <div class="nasa-glass-box" style="border-radius:12px;padding:1.25rem;">
                                        <h6 style="color:rgba(255,255,255,0.7);"><i class="fas fa-info-circle me-1"></i>Description</h6>
                                        <p class="mb-0" style="color:rgba(255,255,255,0.85);"><?= e($value['description'] ?? 'No description provided.') ?></p>
                                    </div>
                                    <div class="nasa-glass-box mt-3" style="border-radius:12px;padding:1rem;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small style="color:rgba(255,255,255,0.5);">Icon:</small>
                                                <span style="color:rgba(255,255,255,0.85);"><?= e($value['icon']) ?></span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <small style="color:rgba(255,255,255,0.5);">Color:</small>
                                                <span class="d-inline-block rounded" style="width:20px;height:20px;background:<?= e($value['color']) ?>;"></span>
                                                <span style="color:rgba(255,255,255,0.85);"><?= e($value['color']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <span class="badge <?= $value['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>" style="font-size:.75rem;">
                                            <?= ucfirst($value['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                                    <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Value Modal -->
                    <div class="modal fade" id="deleteValue<?= $value['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-bottom:1px solid rgba(255,255,255,0.08);">
                                    <h5 class="modal-title fw-bold" style="color:#fff;">
                                        <i class="fas fa-trash me-2 text-danger"></i>Remove Value
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter:brightness(0) invert(1);opacity:0.85;cursor:pointer;padding:.5rem;"></button>
                                </div>
                                <div class="modal-body" style="background:linear-gradient(135deg,#0b1628 0%,#0f2847 40%,#132e52 70%,#1a3a5c 100%);padding:1.5rem;">
                                    <div class="nasa-glass-box" style="background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);border-radius:12px;padding:1.25rem;">
                                        <p class="mb-0" style="color:rgba(255,255,255,0.85);">
                                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                            Are you sure you want to permanently remove the value
                                            <strong style="color:#fff;"><?= e($value['title']) ?></strong>?
                                        </p>
                                        <p class="mt-2 mb-0" style="color:rgba(255,255,255,0.5);font-size:0.85rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer" style="background:linear-gradient(135deg,#0b1628 0%,#132e52 100%);border-top:1px solid rgba(255,255,255,0.08);">
                                    <button type="button" class="btn" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST" action="<?= url('/admin/about') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="section" value="value_delete">
                                        <input type="hidden" name="value_id" value="<?= $value['id'] ?>">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Remove Value</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Value Modal -->
                    <div class="modal fade" id="editValue<?= $value['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="border-radius:12px;">
                                <form method="POST" action="<?= url('/admin/about') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="value_edit">
                                    <input type="hidden" name="value_id" value="<?= $value['id'] ?>">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold">Edit Value</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" value="<?= e($value['title']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea class="form-control" name="description" rows="3"><?= e($value['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label fw-semibold">Icon Class</label>
                                                <input type="text" class="form-control" name="icon" value="<?= e($value['icon']) ?>" placeholder="fas fa-heart">
                                                <small class="text-muted">Font Awesome class (e.g. fas fa-heart)</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Color</label>
                                                <input type="color" class="form-control form-control-color" name="color" value="<?= e($value['color']) ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="active" <?= $value['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $value['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">No values yet. Add one below.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-plus me-1"></i> Add Value</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('/admin/about') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="section" value="value_add">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="e.g. Integrity" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Brief description..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Icon Class</label>
                                <input type="text" class="form-control" name="icon" value="fas fa-star" placeholder="fas fa-heart">
                                <small class="text-muted">Font Awesome class (e.g. fas fa-heart)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Color</label>
                                <input type="color" class="form-control form-control-color" name="color" value="#2563eb">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i> Add Value
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
