<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $announcements = $announcements ?? []; ?>

<section class="page-header py-4" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:5rem 0 2.5rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Announcements</li>
            </ol>
        </nav>
        <h1 class="display-6 fw-bold mb-0">Announcements</h1>
        <p class="text-white-50 mb-0">Stay informed with the latest news and updates from the boarding house.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (!empty($announcements)): ?>
            <div class="row g-4">
                <?php foreach ($announcements as $announcement): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:12px;transition:transform .2s;">
                            <a href="<?= url('/announcement/' . $announcement['id']) ?>" class="text-decoration-none">
                                <div class="card-body d-flex flex-column" style="min-height:220px;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex gap-1 flex-wrap">
                                            <?= statusBadge($announcement['type'] ?? 'general') ?>
                                            <?= statusBadge($announcement['priority'] ?? 'low') ?>
                                        </div>
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i><?= timeAgo($announcement['published_at'] ?? $announcement['created_at']) ?></small>
                                    </div>
                                    <h5 class="fw-bold mb-2 text-dark"><?= e($announcement['title']) ?></h5>
                                    <p class="text-muted small flex-grow-1 mb-3" style="font-size:13px;"><?= e(truncate($announcement['content'] ?? '', 120)) ?></p>
                                    <div class="d-flex justify-content-between align-items-center text-muted small">
                                        <span><i class="fas fa-calendar me-1"></i><?= formatDate($announcement['published_at'] ?? $announcement['created_at']) ?></span>
                                        <span class="text-primary fw-semibold">Read More <i class="fas fa-arrow-right ms-1"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No Announcements Yet</h5>
                <p class="text-muted">There are no announcements at this time. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>