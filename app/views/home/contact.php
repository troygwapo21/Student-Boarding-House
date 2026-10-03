<?php require_once __DIR__ . '/../../Helpers/helpers.php'; ?>
<?php $settings = $settings ?? []; ?>

<section class="page-header py-0" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;padding:6rem 0 3rem;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item active text-white">Contact</li>
            </ol>
        </nav>
        <h1 class="display-5 fw-bold"><?= e(getSettingValue('contact_page_title', 'Contact Us')) ?></h1>
       
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius:12px;">
                    <div class="card-body p-5">
                        <h3 class="fw-bold mb-4"><?= e(getSettingValue('contact_form_title', 'Send Us a Message')) ?></h3>
                        <form action="<?= url('/contact') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><?= e(getSettingValue('contact_name_label', 'Full Name')) ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-lg" placeholder="<?= e(getSettingValue('contact_name_placeholder', 'Your full name')) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><?= e(getSettingValue('contact_email_label', 'Email Address')) ?> <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control form-control-lg" placeholder="<?= e(getSettingValue('contact_email_placeholder', 'example@gmail.com')) ?>" required pattern="[A-Za-z0-9._%+-]+@gmail\.com" title="Email Address: Please enter a valid Gmail address ending with @gmail.com.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><?= e(getSettingValue('contact_phone_label', 'Phone Number')) ?></label>
                                    <input type="tel" name="phone" class="form-control form-control-lg" placeholder="<?= e(getSettingValue('contact_phone_placeholder', '09*********')) ?>" inputmode="tel" maxlength="11" pattern="09[0-9]{9}" title="Please enter a valid 11-digit Philippine mobile number starting with 09.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><?= e(getSettingValue('contact_subject_label', 'Subject')) ?> <span class="text-danger">*</span></label>
                                    <?php
                                    $subjectOptions = getSettingValue('contact_subject_options', 'Room Inquiry; Reservation; Payment; Maintenance; General Inquiry; Feedback');
                                    $subjectOptions = preg_split('/[;\n]+/', $subjectOptions);
                                    $subjectOptions = array_values(array_filter(array_map('trim', $subjectOptions)));
                                    ?>
                                    <select name="subject" class="form-select form-select-lg" required>
                                        <option value=""><?= e(getSettingValue('contact_subject_select', 'Select a subject')) ?></option>
                                        <?php foreach ($subjectOptions as $subjectOption): ?>
                                        <option value="<?= e($subjectOption) ?>"><?= e($subjectOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium"><?= e(getSettingValue('contact_message_label', 'Message')) ?> <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control" rows="6" placeholder="<?= e(getSettingValue('contact_message_placeholder', 'Write your message here...')) ?>" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-semibold">
                                        <i class="fas fa-paper-plane me-2"></i> <?= e(getSettingValue('contact_button_label', 'Send Message')) ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><?= e(getSettingValue('contact_info_title', 'Contact Information')) ?></h5>
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:50px;height:50px;background:linear-gradient(135deg,#2563eb15,#2563eb05);color:#2563eb;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= e(getSettingValue('contact_label_address', 'Address')) ?></h6>
                                <p class="text-muted mb-0 small"><?= e($settings['site_address'] ?? 'Pili Madredijos, Cebu') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:50px;height:50px;background:linear-gradient(135deg,#22c55e15,#22c55e05);color:#22c55e;">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= e(getSettingValue('contact_label_phone', 'Phone')) ?></h6>
                                <p class="text-muted mb-0 small"><?= e($settings['site_phone'] ?? '0945-495-5140') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:50px;height:50px;background:linear-gradient(135deg,#f59e0b15,#f59e0b05);color:#f59e0b;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= e(getSettingValue('contact_label_email', 'Email')) ?></h6>
                                <p class="text-muted mb-0 small"><?= e($settings['site_email'] ?? 'warlitovelliganio@gmail.com') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:50px;height:50px;background:linear-gradient(135deg,#8b5cf615,#8b5cf605);color:#8b5cf6;">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= e(getSettingValue('contact_label_hours', 'Working Hours')) ?></h6>
                                <p class="text-muted mb-0 small">Mon - Fri: <?= e(getSettingValue('home_hours_weekday', '8:00 AM - 8:00 PM')) ?></p>
                                <p class="text-muted mb-0 small">Saturday: <?= e(getSettingValue('home_hours_saturday', '8:00 AM - 6:00 PM')) ?></p>
                                <p class="text-muted mb-0 small">Sunday: <?= e(getSettingValue('home_hours_sunday', 'Closed')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

