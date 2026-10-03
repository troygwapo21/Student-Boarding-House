<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $featuredRooms = $featuredRooms ?? []; ?>
<?php $testimonials = $testimonials ?? []; ?>
<?php $announcements = $announcements ?? []; ?>
<?php $faqs = $faqs ?? []; ?>
<?php $totalRooms = $totalRooms ?? 0; ?>
<?php $availableRooms = $availableRooms ?? 0; ?>
<?php $totalTenants = $totalTenants ?? 0; ?>

<!-- ===================== HERO SECTION ===================== -->
<section class="hero-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);min-height:92vh;display:flex;align-items:center;position:relative;overflow:hidden;padding-top:0;">
    <!-- Animated Background Shapes -->
    <div class="hero-bg-shapes" aria-hidden="true">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
        <div class="hero-shape hero-shape-4"></div>
    </div>
    <!-- Grid Pattern Overlay -->
    <div class="hero-grid-overlay" aria-hidden="true"></div>
    
    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-badge animate-hero" style="animation-delay:0.05s;">
                    <i class="fas fa-star"></i>
                    <span><?= e(getSettingValue('home_hero_badge', '#Tenant Dormitory')) ?></span>
                </div>
                <h1 class="hero-title animate-hero" style="animation-delay:0.15s;">
                    <?= e(getSettingValue('home_hero_title_1', 'Your Home')) ?><br>
                    <?= e(getSettingValue('home_hero_title_2', 'Away From')) ?> <span class="hero-title-accent"><?= e(getSettingValue('home_hero_title_accent', 'Home')) ?></span>
                </h1>
                <p class="hero-subtitle animate-hero" style="animation-delay:0.25s;">
                    <?= e(getSettingValue('home_hero_subtitle', 'Find the perfect boarding house room that suits your needs and budget. Comfortable, safe, and affordable accommodations for tenants.')) ?>
                </p>
                <div class="hero-actions animate-hero" style="animation-delay:0.35s;">
                    <a href="<?= url('/rooms') ?>" class="hero-btn hero-btn-primary">
                        <i class="fas fa-search"></i>
                        <span>Visit Rooms</span>
                    </a>
                    <a href="<?= url('/about') ?>" class="hero-btn hero-btn-ghost">
                        <i class="fas fa-play"></i>
                        <span>Learn More</span>
                    </a>
                </div>
                <div class="hero-stats animate-hero" style="animation-delay:0.2s;">
                    <div class="hero-stat-item" style="animation:heroReveal 0.8s cubic-bezier(0.4,0,0.2,1) forwards; opacity:0; animation-delay:0.45s;">
                        <span class="hero-stat-number" data-count="<?= $totalRooms ?>">0</span>
                        <span class="hero-stat-label">Total Rooms</span>
                    </div>
                    <div class="hero-stat-divider" style="opacity:0;animation:heroReveal 0.8s cubic-bezier(0.4,0,0.2,1) forwards; animation-delay:0.55s;"></div>
                    <div class="hero-stat-item" style="animation:heroReveal 0.8s cubic-bezier(0.4,0,0.2,1) forwards; opacity:0; animation-delay:0.55s;">
                        <span class="hero-stat-number" data-count="<?= $availableRooms ?>">0</span>
                        <span class="hero-stat-label">Available</span>
                    </div>
                    <div class="hero-stat-divider" style="opacity:0;animation:heroReveal 0.8s cubic-bezier(0.4,0,0.2,1) forwards; animation-delay:0.3s;"></div>
                    <div class="hero-stat-item" style="animation:heroReveal 0.8s cubic-bezier(0.4,0,0.2,1) forwards; opacity:0; animation-delay:0.3s;">
                        <span class="hero-stat-number" data-count="<?= $totalTenants ?>">0</span>
                        <span class="hero-stat-label">Tenant Record</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- Bottom Wave -->
    <div class="hero-bottom-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H0Z" fill="#f8fafc"/>
        </svg>
    </div>
</section>




<!-- ===================== FEATURED ROOMS ===================== -->
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-star"></i> <?= e(getSettingValue('home_rooms_badge', 'Featured')) ?></span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_rooms_title', 'Featured Rooms')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_rooms_subtitle', 'Handpicked rooms selected for their comfort, amenities, and great value.')) ?></p>
        </div>
        <div class="row g-2">
            <?php if (!empty($featuredRooms)): ?>
                <?php foreach ($featuredRooms as $index => $room): ?>
                    <?php
                    $typeLabels = ['bedspacer' => 'Bedspace', 'single' => 'Single', 'studio' => 'Studio'];
                    $rt = $room['room_type'] ?? 'bedspacer';
                    $fCap = max(1, (int)($room['max_capacity'] ?? 1));
                    $fOcc = max((int)($room['live_occupancy'] ?? 0), (int)($room['approved_count'] ?? 0));
                    $fAvail = max(0, $fCap - $fOcc);
                    $fPct = min(100, round(($fOcc / $fCap) * 100));
                    $fBarColor = $fPct <= 50 ? '#22c55e' : ($fPct <= 80 ? '#f59e0b' : ($fPct < 100 ? '#f97316' : '#ef4444'));
                    ?>
                    <div class="col-lg-2 col-md-6 animate-on-scroll" style="transition-delay:<?= $index * 0.1 ?>s;">
                        <div class="premium-room-card" style="cursor:pointer;" onclick="window.location='<?= url('/room/' . $room['id']) ?>'">
                            <div class="premium-room-image">
                                <?php if (!empty($room['primary_image'])): ?>
                                    <img src="<?= UPLOAD_URL . $room['primary_image'] ?>" alt="<?= e($room['room_name']) ?>" style="transition:transform 0.5s cubic-bezier(0.4,0,0.2,1);">
                                <?php else: ?>
                                    <div class="premium-room-placeholder">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1" style="z-index:3;">
                                    <span class="badge" style="background:rgba(0,0,0,0.75);color:#fff;font-size:0.68rem;backdrop-filter:blur(4px);"><?= $typeLabels[$rt] ?? ucfirst($rt) ?></span>
                                    <?php if (!empty($room['size_sqm'])): ?>
                                    <span class="badge" style="background:rgba(0,0,0,0.75);color:#fff;font-size:0.68rem;backdrop-filter:blur(4px);"><i class="fas fa-ruler-combined me-1"></i><?= e($room['size_sqm']) ?>m&sup2;</span>
                                    <?php endif; ?>
                                </div>

                                <div class="position-absolute top-0 end-0 m-2" style="z-index:3;">
                                    <span class="badge" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:0.72rem;box-shadow:0 2px 8px rgba(245,158,11,0.4);">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                </div>

                                <div class="position-absolute bottom-0 start-0 m-2 d-flex gap-1" style="z-index:3;">
                                    <?php if (($room['image_count'] ?? 0) > 0): ?>
                                    <span class="badge" style="background:rgba(0,0,0,0.7);color:#fff;font-size:0.65rem;backdrop-filter:blur(4px);">
                                        <i class="fas fa-camera me-1"></i><?= $room['image_count'] ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($room['has_aircon'])): ?>
                                    <span class="badge" style="background:rgba(0,0,0,0.7);color:#fff;font-size:0.65rem;backdrop-filter:blur(4px);">
                                        <i class="fas fa-snowflake"></i>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (!empty($room['has_bathroom'])): ?>
                                    <span class="badge" style="background:rgba(0,0,0,0.7);color:#fff;font-size:0.65rem;backdrop-filter:blur(4px);">
                                        <i class="fas fa-bath"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <div class="position-absolute bottom-0 end-0 m-2" style="z-index:3;">
                                    <?php if (($room['status'] ?? '') === 'under_maintenance'): ?>
                                    <span class="badge bg-warning text-dark" style="font-size:0.68rem;">
                                        <i class="fas fa-wrench me-1"></i>Maintenance
                                    </span>
                                    <?php elseif ($fAvail > 0): ?>
                                    <span class="badge" style="background:#22c55e;color:#fff;font-size:0.68rem;box-shadow:0 2px 8px rgba(34,197,94,0.4);">
                                        <?= $fAvail ?> slot<?= $fAvail !== 1 ? 's' : '' ?> left
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-danger" style="font-size:0.68rem;">
                                        Fully Occupied
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <div class="premium-room-overlay">
                                    <a href="<?= url('/room/' . $room['id']) ?>" class="premium-room-view-btn" onclick="event.stopPropagation()">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>

                            <div class="premium-room-body">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h5 class="premium-room-name mb-0" style="font-size:1.05rem;"><?= e($room['room_name']) ?></h5>
                                    <div class="premium-room-price mb-0">
                                        <span class="price-amount" style="font-size:1.1rem;"><?= formatCurrency($room['monthly_rent']) ?></span>
                                        <span class="price-period">/mo</span>
                                    </div>
                                </div>

                                <p class="premium-room-number"><i class="fas fa-door-open"></i> Room <?= e($room['room_number']) ?></p>

                                <?php if (!empty($room['description'])): ?>
                                <p class="text-muted small mb-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5;font-size:0.82rem;"><?= e($room['description']) ?></p>
                                <?php endif; ?>

                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php if (!empty($room['has_aircon'])): ?><span class="badge" style="background:#eff6ff;color:#2563eb;font-size:0.68rem;font-weight:500;"><i class="fas fa-snowflake me-1"></i>AC</span><?php endif; ?>
                                    <?php if (!empty($room['has_bathroom'])): ?><span class="badge" style="background:#ecfeff;color:#0891b2;font-size:0.68rem;font-weight:500;"><i class="fas fa-bath me-1"></i>Bath</span><?php endif; ?>
                                    <?php if (!empty($room['has_balcony'])): ?><span class="badge" style="background:#f5f3ff;color:#7c3aed;font-size:0.68rem;font-weight:500;"><i class="fas fa-building me-1"></i>Balcony</span><?php endif; ?>
                                    <?php if (!empty($room['floor'])): ?><span class="badge" style="background:#f0fdf4;color:#16a34a;font-size:0.68rem;font-weight:500;"><i class="fas fa-layer-group me-1"></i>F<?= e($room['floor']) ?></span><?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-muted"><i class="fas fa-users me-1"></i><?= $fOcc ?>/<?= $fCap ?> boarders</span>
                                    <span class="small fw-semibold" style="color:<?= $fBarColor ?>;"><?= $fAvail ?> left</span>
                                </div>
                                <div class="progress mb-0" style="height:4px;border-radius:2px;">
                                    <div class="progress-bar" style="width:<?= $fPct ?>%;background:<?= $fBarColor ?>;border-radius:2px;"></div>
                                </div>

                                <a href="<?= url('/room/' . $room['id']) ?>" class="premium-room-btn mt-3" onclick="event.stopPropagation()">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 animate-on-scroll">
                    <div class="premium-empty-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5 class="mt-3 text-muted">No Featured Rooms Yet</h5>
                    <p class="text-muted">Check back soon — we're curating the best rooms for you.</p>
                    <a href="<?= url('/rooms') ?>" class="premium-cta-btn mt-2">
                        Browse All Rooms <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($featuredRooms)): ?>
        <div class="text-center mt-5 animate-on-scroll">
            <a href="<?= url('/rooms') ?>" class="premium-cta-btn">
                View All Rooms <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== AMENITIES ===================== -->
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-concierge-bell"></i> <?= e(getSettingValue('home_amenities_badge', 'What We Offer')) ?></span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_amenities_title', 'Our Amenities')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_amenities_subtitle', 'Everything you need for a comfortable tenant life, all in one place.')) ?></p>
        </div>
        <div class="row g-2">
            <?php
            $amenityColors = ['#2563eb', '#06b6d4', '#22c55e', '#f59e0b', '#8b5cf6', '#ec4899', '#ef4444', '#14b8a6', '#6366f1', '#0ea5e9', '#f97316', '#10b981'];
            $amenities = $amenities ?? [];
            foreach ($amenities as $index => $amenity):
                $color = $amenityColors[$index % count($amenityColors)];
            ?>
                <div class="col-lg-4 col-md-6 animate-on-scroll" style="transition-delay:<?= $index * 0.08 ?>s;">
                    <div class="premium-amenity-card">
                        <div class="premium-amenity-icon" style="background:<?= $color ?>15;color:<?= $color ?>;">
                            <i class="<?= e($amenity['icon'] ?? 'fas fa-check') ?>"></i>
                        </div>
                        <h5 class="premium-amenity-title"><?= e($amenity['name']) ?></h5>
                        <p class="premium-amenity-desc"><?= e($amenity['description'] ?? 'Available for all residents.') ?></p>
                        <div class="premium-amenity-line" style="background:<?= $color ?>;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== WHY CHOOSE US ===================== -->
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-award"></i> <?= e(getSettingValue('home_why_badge', 'Why Us')) ?></span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_why_title', 'Why Choose Us')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_why_subtitle', 'We go above and beyond to provide the best boarding house experience for tenants.')) ?></p>
        </div>
        <div class="row g-2">
            <?php
            $whyItems = [
                ['icon' => 'fas fa-money-bill-wave', 'title' => getSettingValue('home_why_1_title', 'Affordable Rates'), 'desc' => getSettingValue('home_why_1_desc', 'Competitive pricing that fits within a tenant\'s budget without compromising quality.'), 'color' => '#2563eb'],
                ['icon' => 'fas fa-location-dot', 'title' => getSettingValue('home_why_2_title', 'Prime Location'), 'desc' => getSettingValue('home_why_2_desc', 'Strategically located near universities, schools, public transport, and commercial areas.'), 'color' => '#10b981'],
                ['icon' => 'fas fa-headset', 'title' => getSettingValue('home_why_3_title', '24/7 Support'), 'desc' => getSettingValue('home_why_3_desc', 'Our team is always available to assist you with any concerns or requests.'), 'color' => '#f59e0b'],
                ['icon' => 'fas fa-broom', 'title' => getSettingValue('home_why_4_title', 'Clean Environment'), 'desc' => getSettingValue('home_why_4_desc', 'Regular cleaning and maintenance to ensure a hygienic living space for all residents.'), 'color' => '#8b5cf6'],
            ];
            foreach ($whyItems as $index => $item): ?>
                <div class="col-lg-3 col-md-6 animate-on-scroll" style="transition-delay:<?= $index * 0.1 ?>s;">
                    <div class="premium-why-card">
                        <div class="premium-why-icon" style="color:<?= $item['color'] ?>;">
                            <i class="<?= $item['icon'] ?>"></i>
                        </div>
                        <div class="premium-why-line" style="background:<?= $item['color'] ?>;"></div>
                        <h5 class="premium-why-title"><?= $item['title'] ?></h5>
                        <p class="premium-why-desc"><?= $item['desc'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== HOW IT WORKS ===================== -->
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-route"></i> <?= e(getSettingValue('home_how_badge', 'Simple Process')) ?></span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_how_title', 'How It Works')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_how_subtitle', 'Getting your perfect room is easy with our simple 3-step process.')) ?></p>
        </div>
        <div class="row g-2 align-items-start">
            <?php
            $steps = [
                ['num' => '01', 'icon' => 'fas fa-search', 'title' => getSettingValue('home_step_1_title', 'Browse'), 'desc' => getSettingValue('home_step_1_desc', 'Explore our available rooms and find the one that matches your preferences and budget.'), 'color' => '#2563eb'],
                ['num' => '02', 'icon' => 'fas fa-calendar-check', 'title' => getSettingValue('home_step_2_title', 'Reserve'), 'desc' => getSettingValue('home_step_2_desc', 'Submit your reservation request and complete the payment to secure your room.'), 'color' => '#f59e0b'],
                ['num' => '03', 'icon' => 'fas fa-house-user', 'title' => getSettingValue('home_step_3_title', 'Move In'), 'desc' => getSettingValue('home_step_3_desc', 'Get your keys and settle into your new home. Welcome to your boarding house family!'), 'color' => '#10b981'],
            ];
            foreach ($steps as $index => $step): ?>
                <div class="col-lg-4 text-center animate-on-scroll" style="transition-delay:<?= $index * 0.15 ?>s;">
                    <div class="premium-step-card">
                        <div class="premium-step-number" style="background:<?= $step['color'] ?>15;color:<?= $step['color'] ?>;">
                            <?= $step['num'] ?>
                        </div>
                        <div class="premium-step-icon" style="background:linear-gradient(135deg,<?= $step['color'] ?>,<?= $step['color'] ?>cc);color:#fff;">
                            <i class="<?= $step['icon'] ?>"></i>
                        </div>
                        <?php if ($index < 2): ?>
                            <div class="premium-step-connector d-none d-lg-block"></div>
                        <?php endif; ?>
                        <h5 class="premium-step-title"><?= $step['title'] ?></h5>
                        <p class="premium-step-desc"><?= $step['desc'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<?php if (!empty($testimonials)): ?>
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-quote-left"></i> <?= e(getSettingValue('home_testimonials_badge', 'Testimonials')) ?></span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_testimonials_title', 'What Tenants Say')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_testimonials_subtitle', 'Hear from our happy residents about their experience living with us.')) ?></p>
        </div>
        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="premium-testimonial-card">
                                    <div class="premium-testimonial-quote">
                                        <i class="fas fa-quote-left"></i>
                                    </div>
                                    <div class="premium-testimonial-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="premium-testimonial-text">
                                        "<?= e($testimonial['comment'] ?? $testimonial['content'] ?? '') ?>"
                                    </p>
                                    <div class="premium-testimonial-author">
                                        <div class="premium-testimonial-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="premium-testimonial-name"><?= e($testimonial['student_name'] ?? $testimonial['name'] ?? 'Tenant') ?></h6>
                                            <span class="premium-testimonial-role"><?= e($testimonial['course'] ?? 'Tenant Resident') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($testimonials) > 1): ?>
                <div class="testimonial-controls">
                    <button class="testimonial-btn" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="testimonial-indicators">
                        <?php foreach ($testimonials as $index => $testimonial): ?>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <button class="testimonial-btn" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== ANNOUNCEMENTS ===================== -->
<?php if (!empty($announcements)): ?>
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-bullhorn"></i> <?= e(getSettingValue('home_announcements_badge', 'Updates')) ?></span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_announcements_title', 'Latest Announcements')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_announcements_subtitle', 'Stay informed with the latest news and updates from the boarding house.')) ?></p>
        </div>
        <div class="row g-2">
            <?php foreach ($announcements as $index => $announcement): ?>
                <div class="col-lg-4 col-md-6 animate-on-scroll" style="transition-delay:<?= $index * 0.1 ?>s;">
                    <a href="<?= url('/announcement/' . $announcement['id']) ?>" class="text-decoration-none d-block h-100">
                        <div class="premium-announcement-card h-100">
                            <div class="premium-announcement-top">
                                <?= statusBadge($announcement['priority'] ?? 'general') ?>
                                <span class="premium-announcement-time"><i class="fas fa-clock"></i> <?= timeAgo($announcement['published_at'] ?? $announcement['created_at']) ?></span>
                            </div>
                            <h5 class="premium-announcement-title"><?= e($announcement['title']) ?></h5>
                            <p class="premium-announcement-text"><?= e(truncate($announcement['content'], 120)) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="premium-announcement-date">
                                    <i class="fas fa-calendar"></i> <?= formatDate($announcement['published_at'] ?? $announcement['created_at']) ?>
                                </span>
                                <span style="color:#8fa61b;font-weight:600;font-size:0.82rem;">View Details <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= url('/announcements') ?>" class="premium-cta-btn-white d-inline-flex">
                <i class="fas fa-bullhorn"></i>View All Announcements
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== FAQS ===================== -->
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="container">
        <div class="premium-section-header animate-on-scroll">
            <span class="premium-section-badge"><i class="fas fa-question-circle"></i> Frequently Asked Questions</span>
            <h2 class="premium-section-title"><?= e(getSettingValue('home_faqs_title', 'Common Questions')) ?></h2>
            <p class="premium-section-subtitle"><?= e(getSettingValue('home_faqs_subtitle', 'Quick answers to the questions we hear most often from our tenants.')) ?></p>
        </div>
        <?php if (!empty($faqs)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="row g-3">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="col-md-6">
                            <div class="h-100" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:0.9rem 1.1rem;">
                                <div class="d-flex align-items-start gap-2" style="color:#fff;">
                                    <i class="fas fa-question-circle" style="color:#8fa61b;margin-top:0.2rem;"></i>
                                    <span class="fw-semibold" style="font-size:0.9rem;line-height:1.5;"><?= e($faq['question']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4 animate-on-scroll">
                    <a href="<?= url('/faqs') ?>" class="premium-cta-btn">
                        View All FAQs <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center mt-4 animate-on-scroll">
            <a href="<?= url('/faqs') ?>" class="premium-cta-btn">
                View All FAQs <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== CTA SECTION ===================== -->
<section class="premium-cta-section" style="background:linear-gradient(135deg,#0f172a 0%,#1a3a1f 30%,#2d5a1e 60%,#8fa61b 100%);">
    <div class="premium-cta-bg" aria-hidden="true">
        <div class="premium-cta-shape premium-cta-shape-1"></div>
        <div class="premium-cta-shape premium-cta-shape-2"></div>
    </div>
    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="premium-cta-title"><?= e(getSettingValue('home_cta_title', 'Ready to Find Your Perfect Room?')) ?></h2>
                <p class="premium-cta-text"><?= e(getSettingValue('home_cta_text', 'Browse our available rooms and secure your spot today. Your ideal tenant accommodation is just a click away.')) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= url('/rooms') ?>" class="premium-cta-btn-white">
                    <i class="fas fa-door-open"></i> <?= e(getSettingValue('home_cta_button', 'Browse Rooms')) ?>
                </a>
            </div>
        </div>
    </div>
</section>

