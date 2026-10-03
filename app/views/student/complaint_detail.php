<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Complaint Details</h4>
    <a href="<?= url('/student/complaints') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><?= e($complaint['subject']) ?></h5>
                    <p class="text-muted small mb-0">Code: <strong><?= e($complaint['complaint_code']) ?></strong></p>
                </div>
                <div class="d-flex gap-2">
                    <?= statusBadge($complaint['severity']) ?>
                    <?= statusBadge($complaint['status']) ?>
                </div>
            </div>

            <hr>

            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase mb-1">Category</h6>
                    <p class="fw-semibold mb-0"><span class="text-capitalize"><?= e(str_replace('_', ' ', $complaint['category'])) ?></span></p>
                </div>
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase mb-1">Date Submitted</h6>
                    <p class="fw-semibold mb-0"><?= formatDateTime($complaint['created_at']) ?></p>
                </div>
            </div>

            <h6 class="fw-bold mb-2">Description</h6>
            <div class="p-3 mb-4" style="background: #f8fafc; border-radius: 8px; line-height: 1.7;">
                <?= nl2br(e($complaint['description'] ?? '')) ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Admin Response -->
        <div class="content-card">
            <h6 class="fw-bold mb-3"><i class="fas fa-reply me-2"></i>Admin Response</h6>
            <?php if (!empty($complaint['admin_response'])): ?>
            <div class="p-3" style="background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                <p class="mb-0" style="line-height: 1.6;"><?= nl2br(e($complaint['admin_response'])) ?></p>
                <?php if (!empty($complaint['responded_at'])): ?>
                <small class="text-muted d-block mt-2"><i class="fas fa-clock me-1"></i> <?= formatDateTime($complaint['responded_at']) ?></small>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-3 text-muted">
                <i class="fas fa-clock d-block mb-2"></i>
                <small>Awaiting admin response</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
