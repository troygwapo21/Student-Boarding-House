<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Maintenance Request Details</h4>
    <a href="<?= url('/student/maintenance') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><?= e($request['title']) ?></h5>
                    <p class="text-muted small mb-0">Code: <strong><?= e($request['request_code']) ?></strong></p>
                </div>
                <div class="d-flex gap-2">
                    <?= statusBadge($request['priority']) ?>
                    <?= statusBadge($request['status']) ?>
                </div>
            </div>

            <hr>

            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase mb-1">Category</h6>
                    <p class="fw-semibold mb-0"><span class="text-capitalize"><?= e(str_replace('_', ' ', $request['category'])) ?></span></p>
                </div>
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase mb-1">Room</h6>
                    <p class="fw-semibold mb-0"><?= !empty($request['room_name']) ? e($request['room_name'] . ' - ' . $request['room_number']) : 'N/A' ?></p>
                </div>
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase mb-1">Date Submitted</h6>
                    <p class="fw-semibold mb-0"><?= formatDateTime($request['created_at']) ?></p>
                </div>
            </div>

            <h6 class="fw-bold mb-2">Description</h6>
            <div class="p-3 mb-4" style="background: #f8fafc; border-radius: 8px; line-height: 1.7;">
                <?= nl2br(e($request['description'] ?? '')) ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Admin Response -->
        <div class="content-card mb-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-reply me-2"></i>Admin Response</h6>
            <?php if (!empty($request['admin_response'])): ?>
            <div class="p-3" style="background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                <p class="mb-0" style="line-height: 1.6;"><?= nl2br(e($request['admin_response'])) ?></p>
            </div>
            <?php else: ?>
            <div class="text-center py-3 text-muted">
                <i class="fas fa-clock d-block mb-2"></i>
                <small>Awaiting admin response</small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Timeline -->
        <div class="content-card">
            <h6 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>Status Timeline</h6>
            <div class="timeline">
                <div class="timeline-item mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #4f46e5; margin-top: 5px; min-width: 10px;"></div>
                        <div>
                            <p class="fw-semibold mb-0 small">Request Created</p>
                            <small class="text-muted"><?= formatDateTime($request['created_at']) ?></small>
                        </div>
                    </div>
                </div>
                <?php if ($request['status'] === 'in_progress'): ?>
                <div class="timeline-item mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; margin-top: 5px; min-width: 10px;"></div>
                        <div>
                            <p class="fw-semibold mb-0 small">In Progress</p>
                            <small class="text-muted">Admin is working on it</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($request['status'] === 'resolved' || $request['status'] === 'closed'): ?>
                <div class="timeline-item mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e; margin-top: 5px; min-width: 10px;"></div>
                        <div>
                            <p class="fw-semibold mb-0 small">Resolved</p>
                            <small class="text-muted"><?= !empty($request['resolved_at']) ? formatDateTime($request['resolved_at']) : '' ?></small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
