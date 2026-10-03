<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Complaint Details</h1>
    <a href="<?= url('/manager/complaints') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Complaint: <?= e($complaint['complaint_code']) ?></h6>
                    <?= statusBadge($complaint['status']) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted">Subject</small>
                        <div class="fw-bold"><?= e($complaint['subject']) ?></div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Category</small>
                        <div class="fw-bold"><?= e(ucwords(str_replace('_', ' ', $complaint['category'] ?? 'N/A'))) ?></div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Severity</small>
                        <div><?= statusBadge($complaint['severity'] ?? 'medium') ?></div>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Description</small>
                    <div class="mt-1"><?= nl2br(e($complaint['description'] ?? '')) ?></div>
                </div>
                <?php if (!empty($complaint['admin_response'])): ?>
                    <div class="bg-light rounded p-3">
                        <small class="text-muted fw-bold">Admin Response</small>
                        <div class="mt-1"><?= nl2br(e($complaint['admin_response'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-2"><small class="text-muted">Student:</small><div class="fw-bold"><?= e($complaint['first_name'] . ' ' . $complaint['last_name']) ?></div></div>
                <div class="mb-2"><small class="text-muted">Phone:</small><div class="fw-bold"><?= e($complaint['student_phone'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Submitted:</small><div class="fw-bold"><?= formatDateTime($complaint['created_at']) ?></div></div>
                <?php if (!empty($complaint['resolved_at'])): ?>
                    <div class="mb-2"><small class="text-muted">Resolved:</small><div class="fw-bold"><?= formatDateTime($complaint['resolved_at']) ?></div></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/manager/complaint/update/' . $complaint['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="open" <?= $complaint['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="under_review" <?= $complaint['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="resolved" <?= $complaint['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            <option value="closed" <?= $complaint['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Response</label>
                        <textarea name="admin_response" class="form-control" rows="4" placeholder="Enter response..."><?= e($complaint['admin_response'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
