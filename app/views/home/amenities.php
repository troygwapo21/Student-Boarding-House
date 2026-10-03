<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $amenities = $amenities ?? []; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Amenities</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold">Our Amenities</h1>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (!empty($amenities)): ?>
            <div class="row g-4">
                <?php
                $colors = ['#2563eb', '#06b6d4', '#22c55e', '#f59e0b', '#8b5cf6', '#ec4899', '#ef4444', '#14b8a6', '#6366f1', '#0ea5e9', '#f97316', '#10b981'];
                ?>
                <?php foreach ($amenities as $index => $amenity): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:12px;transition:transform 0.3s;">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:60px;height:60px;background:<?= $colors[$index % count($colors)] ?>15;color:<?= $colors[$index % count($colors)] ?>;">
                                        <i class="<?= e($amenity['icon'] ?? 'fas fa-check') ?> fa-lg"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-2"><?= e($amenity['name']) ?></h5>
                                    <p class="text-muted mb-0"><?= e($amenity['description'] ?? 'Available for all residents.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-concierge-bell fa-4x text-muted mb-3"></i>
                <h4 class="fw-bold">Amenities Coming Soon</h4>
                <p class="text-muted">We're constantly improving our facilities. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;">
    <div class="container py-4 text-center">
        <h2 class="fw-bold mb-3">Want to See Our Rooms?</h2>
        <p class="lead mb-0 text-white-50 fw-bold">Browse our available rooms and find the perfect fit for your needs.</p>
        <a href="<?= url('/rooms') ?>" class="btn btn-accent btn-lg px-5 py-3 fw-semibold">
            <i class="fas fa-door-open me-2"></i> Browse Rooms
        </a>
    </div>
</section>
