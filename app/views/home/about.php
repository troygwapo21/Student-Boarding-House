<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $settings = $settings ?? []; ?>
<?php $teamMembers = $teamMembers ?? []; ?>
<?php $aboutValues = $aboutValues ?? []; ?>
<?php $totalRooms = $totalRooms ?? 0; ?>
<?php $availableRooms = $availableRooms ?? 0; ?>
<?php $totalTenants = $totalTenants ?? 0; ?>
<?php $totalAmenities = $totalAmenities ?? 0; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">About Us</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold"><?= e($settings['about_title'] ?? 'About Us') ?></h1>
        <p class="lead mb-0 text-white-50"><?= e($settings['about_subtitle'] ?? '') ?></p>
    </div>
</section>

<?php if (!empty($settings['about_text'])): ?>
<section class="section-padding pb-0">
    <div class="container text-center">
        <p class="lead text-muted mx-auto" style="max-width:800px;"><?= e($settings['about_text']) ?></p>
    </div>
</section>
<?php endif; ?>

<section class="section-padding bg-darkwhite">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="rounded-4 overflow-hidden shadow-lg" style="aspect-ratio:4/3;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);">
                        <?php $aboutImg = !empty($settings['about_image']) ? UPLOAD_URL . $settings['about_image'] : (SITE_URL . '/logo,map,ect/Abouthome.jpg'); ?>
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <img src="<?= e($aboutImg) ?>" alt="About <?= e(getSiteName()) ?>" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    </div>
                    <div class="position-absolute" style="bottom:-20px;right:-20px;background:#f59e0b;color:#1e293b;padding:1.5rem 2rem;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.15);">
                        <span class="fw-bold fs-4"><?= e($settings['about_years_label'] ?? '') ?></span>
                        <span class="d-block small"><?= e($settings['about_years_sublabel'] ?? 'of Service') ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3"><?= e(getSettingValue('about_story_badge', 'Our Story')) ?></span>
                <h2 class="section-title fw-bold mb-4"><?= e($settings['about_story_title'] ?? 'Welcome to ' . getSiteName()) ?></h2>
                <?php
                $storyText = $settings['about_story_text'] ?? '';
                $paragraphs = explode('||', $storyText);
                foreach ($paragraphs as $para):
                ?>
                <p class="text-muted mb-4"><?= e(trim($para)) ?></p>
                <?php endforeach; ?>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-primary me-2"></i>
                            <span class="fw-medium"><?= e(getSettingValue('about_check_1', 'Safe & Secure')) ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-primary me-2"></i>
                            <span class="fw-medium"><?= e(getSettingValue('about_check_2', 'Affordable Rates')) ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-primary me-2"></i>
                            <span class="fw-medium"><?= e(getSettingValue('about_check_3', 'Modern Amenities')) ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-primary me-2"></i>
                            <span class="fw-medium"><?= e(getSettingValue('about_check_4', 'Friendly Community')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-darkwhite">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-5 h-100" style="border-radius:16px;border-left:4px solid #2563eb;">
                    <div class="mb-3">
                        <i class="fas fa-bullseye fa-2x" style="color:#2563eb;"></i>
                    </div>
                    <h3 class="mb-3" style="font-size:1.25rem;font-weight:600;letter-spacing:-0.02em;"><?= e(getSettingValue('about_mission_title', 'Our Mission')) ?></h3>
                    <p class="mb-0" style="font-size:1rem;font-weight:400;line-height:1.8;color:#475569;"><?= e($settings['about_mission'] ?? '') ?></p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-5 h-100" style="border-radius:16px;border-left:4px solid #f59e0b;">
                    <div class="mb-3">
                        <i class="fas fa-eye fa-2x" style="color:#f59e0b;"></i>
                    </div>
                    <h3 class="mb-3" style="font-size:1.25rem;font-weight:600;letter-spacing:-0.02em;"><?= e(getSettingValue('about_vision_title', 'Our Vision')) ?></h3>
                    <p class="mb-0" style="font-size:1rem;font-weight:400;line-height:1.8;color:#475569;"><?= e($settings['about_vision'] ?? '') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-lg-3 col-md-6">
                <div class="p-3">
                    <div style="font-size:2.5rem;font-weight:800;"><?= $totalRooms ?></div>
                    <div style="opacity:.8;font-size:.95rem;"><?= e(getSettingValue('about_stat_1', 'Total Rooms')) ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="p-3">
                    <div style="font-size:2.5rem;font-weight:800;"><?= $availableRooms ?></div>
                    <div style="opacity:.8;font-size:.95rem;"><?= e(getSettingValue('about_stat_2', 'Available Rooms')) ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="p-3">
                    <div style="font-size:2.5rem;font-weight:800;"><?= $totalTenants ?></div>
                    <div style="opacity:.8;font-size:.95rem;"><?= e(getSettingValue('about_stat_3', 'Tenant Record')) ?></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="p-3">
                    <div style="font-size:2.5rem;font-weight:800;"><?= $totalAmenities ?></div>
                    <div style="opacity:.8;font-size:.95rem;"><?= e(getSettingValue('about_stat_4', 'Amenities')) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($teamMembers)): ?>
<section class="section-padding bg-darkwhite">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3"><?= e(getSettingValue('about_team_badge', 'Our Team')) ?></span>
            <h2 class="section-title fw-bold"><?= e($settings['about_team_title'] ?? 'Meet Our Team') ?></h2>
            <p class="text-muted mx-auto" style="max-width:600px;"><?= e($settings['about_team_subtitle'] ?? 'The dedicated people behind ' . getSiteName() . '.') ?></p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $colors = ['#2563eb', '#f59e0b', '#22c55e', '#8b5cf6', '#ef4444', '#06b6d4'];
            foreach ($teamMembers as $index => $member):
                $color = $colors[$index % count($colors)];
            ?>
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius:12px;">
                    <div class="mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width:120px;height:100px;background:linear-gradient(135deg,<?= $color ?>20,<?= $color ?>05);">
                        <?php if (!empty($member['profile_picture'])): ?>
                            <img src="<?= UPLOAD_URL . $member['profile_picture'] ?>" alt="<?= e($member['first_name'] . ' ' . $member['last_name']) ?>" class="rounded-circle" style="width:100px;height:120px;object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user-tie fa-3x" style="color:<?= $color ?>;"></i>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></h5>
                    <p class="small mb-2" style="color:<?= $color ?>;"><i class="fas fa-user-tie me-1"></i><?= e(getSettingValue('about_team_role', 'Manager')) ?></p>
                    <?php if (!empty($member['phone'])): ?>
                        <p class="text-muted small mb-1"><i class="fas fa-phone me-1"></i><?= e($member['phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($member['email'])): ?>
                        <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?= e($member['email']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($member['link'])): ?>
                        <a href="<?= e($member['link']) ?>" class="btn btn-sm btn-outline-primary mt-auto" target="_blank"><i class="fas fa-external-link-alt me-1"></i><?= e(getSettingValue('about_team_contact_btn', 'Contact / Visit')) ?></a>
                    <?php else: ?>
                        <p class="text-muted small mb-0 mt-auto"><?= e(getSettingValue('about_team_contact_hint', 'Contact here for more details!')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($aboutValues)): ?>
<section class="section-padding bg-darkwhite">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3"><?= e(getSettingValue('about_values_badge', 'Our Values')) ?></span>
            <h2 class="section-title fw-bold"><?= e($settings['about_values_title'] ?? 'What We Stand For') ?></h2>
        </div>
        <div class="row g-4">
            <?php foreach ($aboutValues as $value): ?>
            <div class="col-lg-3 col-md-6">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:80px;height:80px;background:linear-gradient(135deg,<?= e($value['color']) ?>,<?= e($value['color']) ?>cc);color:#fff;">
                            <i class="<?= e($value['icon']) ?> fa-lg"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold"><?= e($value['title']) ?></h5>
                    <p class="text-muted mb-0"><?= e($value['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php $aboutFaqs = $aboutFaqs ?? []; ?>
<?php if (!empty($aboutFaqs)): ?>
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3"><i class="fas fa-question-circle me-1"></i> Frequently Asked Questions</span>
            <h2 class="section-title fw-bold"><?= e(getSettingValue('home_faqs_title', 'Common Questions')) ?></h2>
            <p class="text-muted mx-auto" style="max-width:640px;"><?= e(getSettingValue('home_faqs_subtitle', 'Quick answers to the questions we hear most often from our tenants.')) ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm p-5" style="border-radius:16px;">
                    <div class="row g-3">
                        <?php foreach ($aboutFaqs as $faq): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2" style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:12px;padding:0.9rem 1.1rem;">
                                    <i class="fas fa-question-circle text-primary mt-0.5" style="margin-top:0.2rem;"></i>
                                    <span class="fw-semibold" style="font-size:0.9rem;line-height:1.5;color:#1e293b;"><?= e($faq['question']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="<?= url('/faqs') ?>" class="btn btn-primary px-4 py-2 fw-medium">
                            <i class="fas fa-question-circle me-2"></i> View All FAQs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
