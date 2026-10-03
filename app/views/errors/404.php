<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>

<section class="d-flex align-items-center justify-content-center" style="min-height:80vh;background:#f8fafc;">
    <div class="container text-center py-5">
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:180px;height:180px;background:linear-gradient(135deg,#2563eb10,#2563eb05);">
                <span class="display-1 fw-bold" style="color:#2563eb;">404</span>
            </div>
        </div>
        <h1 class="display-5 fw-bold mb-3" style="color:#1e293b;">Page Not Found</h1>
        <p class="text-muted mb-4 mx-auto" style="max-width:500px;">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="<?= url('/') ?>" class="btn btn-primary btn-lg px-5 py-3 fw-semibold">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>
            <a href="<?= url('/rooms') ?>" class="btn btn-outline-primary btn-lg px-5 py-3 fw-semibold">
                <i class="fas fa-door-open me-2"></i> Browse Rooms
            </a>
        </div>
    </div>
</section>
