<div class="page-header d-flex justify-content-between align-items-center">
    <h4>My Feedback</h4>
    <a href="<?= url('/student/feedback/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Submit Feedback
    </a>
</div>

<?php if (!empty($feedback)): ?>
<div class="row g-4">
    <?php foreach ($feedback as $fb): ?>
    <div class="col-md-6">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0"><?= e($fb['subject']) ?></h6>
                <?= statusBadge($fb['status'] ?? 'pending') ?>
            </div>
            <div class="mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= ($fb['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size: 14px;"></i>
                <?php endfor; ?>
            </div>
            <p class="text-muted small mb-2" style="font-size: 13px;"><?= e(truncate($fb['message'] ?? '', 150)) ?></p>
            <small class="text-muted"><i class="fas fa-tag me-1"></i> <?= e(ucwords(str_replace('_', ' ', $fb['category'] ?? 'other'))) ?> &bull; <?= timeAgo($fb['created_at']) ?></small>

            <?php if (!empty($fb['admin_response'])): ?>
            <div class="mt-3 p-3" style="background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                <small class="fw-bold text-success d-block mb-1"><i class="fas fa-reply me-1"></i> Admin Response</small>
                <small class="text-muted"><?= e(truncate($fb['admin_response'], 150)) ?></small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="content-card text-center py-5">
    <i class="fas fa-comment-dots fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">No Feedback Yet</h5>
    <p class="text-muted">Share your thoughts with us.</p>
    <a href="<?= url('/student/feedback/create') ?>" class="btn btn-primary btn-sm">Submit Feedback</a>
</div>
<?php endif; ?>
