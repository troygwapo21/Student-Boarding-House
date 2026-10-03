<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Maintenance Request Details</h1>
    <a href="<?= url('/admin/maintenance') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Request: <?= e($request['request_code']) ?></h6>
                    <?= statusBadge($request['status']) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><small class="text-muted">Title</small><div class="fw-bold"><?= e($request['title']) ?></div></div>
                    <div class="col-md-3"><small class="text-muted">Category</small><div class="fw-bold"><?= e(ucwords(str_replace('_', ' ', $request['category'] ?? 'N/A'))) ?></div></div>
                    <div class="col-md-3"><small class="text-muted">Priority</small><div><?= statusBadge($request['priority'] ?? 'medium') ?></div></div>
                </div>
                <div class="mb-3"><small class="text-muted">Description</small><div class="mt-1"><?= nl2br(e($request['description'] ?? '')) ?></div></div>
                <?php if (!empty($request['admin_response'])): ?>
                    <div class="bg-light rounded p-3">
                        <small class="text-muted fw-bold">Admin Response</small>
                        <div class="mt-1"><?= nl2br(e($request['admin_response'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h6></div>
            <div class="card-body">
                <div class="mb-2"><small class="text-muted">Student:</small><div class="fw-bold"><?= e($request['first_name'] . ' ' . $request['last_name']) ?></div></div>
                <div class="mb-2"><small class="text-muted">Phone:</small><div class="fw-bold"><?= e($request['student_phone'] ?? 'N/A') ?></div></div>
                <div class="mb-2"><small class="text-muted">Room:</small><div class="fw-bold"><?= e($request['room_number'] ?? 'N/A') ?> - <?= e($request['room_name'] ?? '') ?></div></div>
                <div class="mb-2"><small class="text-muted">Submitted:</small><div class="fw-bold"><?= formatDateTime($request['created_at']) ?></div></div>
                <?php if (!empty($request['resolved_at'])): ?>
                    <div class="mb-2"><small class="text-muted">Resolved:</small><div class="fw-bold"><?= formatDateTime($request['resolved_at']) ?></div></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h6 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/maintenance/update/' . $request['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $request['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="resolved" <?= $request['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            <option value="closed" <?= $request['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Response</label>
                        <textarea name="admin_response" class="form-control" rows="4" placeholder="Enter response..."><?= e($request['admin_response'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" data-confirm="Update maintenance request status?"><i class="fas fa-save me-2"></i>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
