<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $testimonials = $testimonials ?? []; ?>

<section class="page-header py-5" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Testimonials</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold">Tenant Testimonials</h1>
        <p class="lead mb-0 opacity-90">Hear what our tenants have to say about living with us.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (!empty($testimonials)): ?>
            <div class="row g-4">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:12px;">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:55px;height:55px;background:linear-gradient(135deg,#2563eb,#1e40af);color:#fff;flex-shrink:0;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0"><?= e($testimonial['student_name'] ?? $testimonial['name'] ?? 'Tenant') ?></h6>
                                    <small class="text-muted"><?= e($testimonial['course'] ?? 'Tenant Resident') ?></small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <?php $rating = (int)($testimonial['rating'] ?? 5); ?>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color:<?= $i <= $rating ? '#f59e0b' : '#e2e8f0' ?>;"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0" style="line-height:1.7;"><?= e($testimonial['comment'] ?? $testimonial['content'] ?? '') ?></p>
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted"><i class="fas fa-clock me-1"></i> <?= timeAgo($testimonial['created_at'] ?? '') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                <h4 class="fw-bold">No Testimonials Yet</h4>
                <p class="text-muted">Be the first to share your experience living with us!</p>
                <a href="<?= url('/register') ?>" class="btn btn-primary mt-2">Register Now</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-5" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;">
    <div class="container py-4 text-center">
        <h2 class="fw-bold mb-3">Ready to Experience It Yourself?</h2>
        <p class="mb-4 opacity-90">Join our community of happy tenants and find your perfect room.</p>
        <a href="<?= url('/rooms') ?>" class="btn btn-accent btn-lg px-5 py-3 fw-semibold">
            <i class="fas fa-door-open me-2"></i> Browse Rooms
        </a>
    </div>
</section>
