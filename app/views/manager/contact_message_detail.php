<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Contact Message Details</h1>
    <a href="<?= url('/manager/contact-messages') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Message: <?= e($message['subject']) ?></h6>
                    <?= statusBadge($message['status']) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <small class="text-muted">From</small>
                        <div class="fw-bold"><?= e($message['name']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Email</small>
                        <div class="fw-bold"><?= e($message['email']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Phone</small>
                        <div class="fw-bold"><?= e($message['phone'] ?: 'N/A') ?></div>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Subject</small>
                    <div class="fw-bold"><span class="badge bg-light text-dark"><?= e($message['subject']) ?></span></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Message</small>
                    <div class="mt-1 p-3 bg-light rounded" style="line-height:1.8;"><?= nl2br(e($message['message'])) ?></div>
                </div>
                <?php if (!empty($message['admin_response'])): ?>
                    <div class="p-3 rounded" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                        <small class="text-muted fw-bold"><i class="fas fa-reply me-1"></i>Management Response</small>
                        <div class="mt-1" style="line-height:1.8;"><?= nl2br(e($message['admin_response'])) ?></div>
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
                <div class="mb-2"><small class="text-muted">Status:</small><div><?= statusBadge($message['status']) ?></div></div>
                <div class="mb-2"><small class="text-muted">Received:</small><div class="fw-bold"><?= formatDateTime($message['created_at']) ?></div></div>
                <div class="mb-2"><small class="text-muted">Last Updated:</small><div class="fw-bold"><?= formatDateTime($message['updated_at']) ?></div></div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-reply me-2"></i>Respond</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/manager/contact-message/update/' . $message['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="new" <?= $message['status'] === 'new' ? 'selected' : '' ?>>New</option>
                            <option value="read" <?= $message['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                            <option value="replied" <?= $message['status'] === 'replied' ? 'selected' : '' ?>>Replied</option>
                            <option value="archived" <?= $message['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Response</label>
                        <textarea name="admin_response" class="form-control" rows="5" placeholder="Enter your response..."><?= e($message['admin_response'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" data-confirm="Update this message?"><i class="fas fa-save me-2"></i>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
