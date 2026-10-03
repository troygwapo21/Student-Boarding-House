<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $announcement = $announcement ?? []; ?>
<?php $recentAnnouncements = $recentAnnouncements ?? []; ?>

<section class="page-header py-4" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:5rem 0 2.5rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/announcements') ?>" class="text-white-50">Announcements</a></li>
                <li class="breadcrumb-item active text-white"><?= e($announcement['title'] ?? 'Announcement') ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex gap-2 flex-wrap">
                                <?= statusBadge($announcement['type'] ?? 'general') ?>
                                <?= statusBadge($announcement['priority'] ?? 'low') ?>
                            </div>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i><?= formatDate($announcement['published_at'] ?? $announcement['created_at'] ?? '', 'M d, Y h:i A') ?></small>
                        </div>
                        <h1 class="fw-bold mb-4"><?= e($announcement['title']) ?></h1>
                        <div style="line-height:1.9;color:#334155;">
                            <?= nl2br(e($announcement['content'] ?? '')) ?>
                        </div>
                        <?php if (!empty($announcement['attachment_path'])): ?>
                            <div class="mt-4 p-3" style="background:#f8fafc;border-radius:10px;">
                                <h6 class="fw-bold small"><i class="fas fa-paperclip me-1"></i> Attachment</h6>
                                <a href="<?= UPLOAD_URL . $announcement['attachment_path'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i> Download Attachment
                                </a>
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between text-muted small flex-wrap gap-2">
                            <span><i class="fas fa-user me-1"></i> Posted by Administration</span>
                            <span><i class="fas fa-calendar me-1"></i><?= formatDate($announcement['published_at'] ?? $announcement['created_at'] ?? '') ?></span>
                        </div>
                        <a href="<?= url('/announcements') ?>" class="btn btn-outline-secondary mt-4">
                            <i class="fas fa-arrow-left me-2"></i>Back to Announcements
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fas fa-bullhorn me-2 text-primary"></i>Recent Announcements</h5>
                        <?php if (!empty($recentAnnouncements)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentAnnouncements as $ra): ?>
                                    <a href="<?= url('/announcement/' . $ra['id']) ?>" class="list-group-item list-group-item-action px-0">
                                        <div class="fw-semibold small text-dark"><?= e($ra['title']) ?></div>
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i><?= timeAgo($ra['published_at'] ?? $ra['created_at']) ?></small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No other announcements.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>