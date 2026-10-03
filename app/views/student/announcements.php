<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Announcements</h4>
</div>

<?php if (!empty($announcements)): ?>
<div class="row g-4">
    <?php foreach ($announcements as $ann): ?>
    <div class="col-md-6 col-xl-4">
        <div class="content-card h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex gap-1">
                    <?= statusBadge($ann['type'] ?? 'general') ?>
                    <?= statusBadge($ann['priority'] ?? 'low') ?>
                </div>
                <small class="text-muted"><?= timeAgo($ann['published_at'] ?? $ann['created_at']) ?></small>
            </div>
            <h6 class="fw-bold mb-2"><?= e($ann['title']) ?></h6>
            <p class="text-muted small flex-grow-1" style="font-size: 13px;"><?= e(truncate($ann['content'] ?? '', 120)) ?></p>
            <a href="<?= url('/student/announcement/' . $ann['id']) ?>" class="btn btn-outline-primary btn-sm mt-2">
                <i class="fas fa-arrow-right me-1"></i> Read More
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="content-card text-center py-5">
    <i class="fas fa-bullhorn fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">No Announcements</h5>
    <p class="text-muted">There are no announcements at this time.</p>
</div>
<?php endif; ?>
