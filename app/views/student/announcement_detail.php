<div class="page-header d-flex justify-content-between align-items-center">
    <h4>Announcement</h4>
    <a href="<?= url('/student/announcements') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Announcements
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex gap-2">
                    <?= statusBadge($announcement['type'] ?? 'general') ?>
                    <?= statusBadge($announcement['priority'] ?? 'low') ?>
                </div>
                <small class="text-muted"><i class="fas fa-clock me-1"></i> <?= formatDate($announcement['published_at'] ?? $announcement['created_at'] ?? '', 'M d, Y h:i A') ?></small>
            </div>

            <h4 class="fw-bold mb-4"><?= e($announcement['title']) ?></h4>

            <div class="announcement-content" style="line-height: 1.8; color: #334155;">
                <?= nl2br(e($announcement['content'] ?? '')) ?>
            </div>

            <?php if (!empty($announcement['attachment_path'])): ?>
            <div class="mt-4 p-3" style="background: #f8fafc; border-radius: 10px;">
                <h6 class="fw-bold small"><i class="fas fa-paperclip me-1"></i> Attachment</h6>
                <a href="<?= UPLOAD_URL . $announcement['attachment_path'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download me-1"></i> Download Attachment
                </a>
            </div>
            <?php endif; ?>

            <hr>
            <div class="d-flex justify-content-between text-muted small">
                <span><i class="fas fa-user me-1"></i> Posted by Administration</span>
                <span><i class="fas fa-calendar me-1"></i> <?= formatDate($announcement['published_at'] ?? $announcement['created_at'] ?? '') ?></span>
            </div>
        </div>
    </div>
</div>
