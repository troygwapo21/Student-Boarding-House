<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fas fa-globe me-2"></i>System Settings</h1>
    <small class="text-muted">Manage your website content</small>
</div>

<div class="alert alert-info d-flex align-items-start mb-4" style="border-left:4px solid #0ea5e9;">
    <i class="fas fa-circle-info fa-lg me-3 mt-1"></i>
    <div>
        <strong class="d-block">Website Management Console</strong>
        <span class="small">
            Use this panel to update your dormitory's public-facing content — branding, contact details,
            social links, and the About and Contact pages. All changes are applied immediately after
            saving, so what you enter here is exactly what your visitors will see.
        </span>
    </div>
</div>

<?php
// Website groups only — technical (billing / security / email) settings are
// managed elsewhere and are intentionally not shown here.
$groupMeta = [
    'social'   => ['icon' => 'fa-share-nodes',    'color' => '#06b6d4', 'label' => 'Social Media'],
    'about'    => ['icon' => 'fa-info-circle',    'color' => '#ec4899', 'label' => 'About Page'],
    'contact'  => ['icon' => 'fa-address-book',   'color' => '#8b5cf6', 'label' => 'Contact Page'],
];

$visibleGroups = array_keys($groupMeta);

// Friendly, human-readable labels for every editable website setting.
$labels = [
    'site_name'                    => 'Site Name',
    'site_tagline'                 => 'Site Tagline',
    'site_logo'                    => 'Site Logo',

    'facebook_url'                 => 'Facebook URL',
    'twitter_url'                  => 'Twitter / X URL',
    'instagram_url'                => 'Instagram URL',

    'home_hero_badge'              => 'Hero Badge',
    'home_hero_title_1'            => 'Hero Title (Line 1)',
    'home_hero_title_2'            => 'Hero Title (Line 2)',
    'home_hero_title_accent'       => 'Hero Accent Word',
    'home_hero_subtitle'           => 'Hero Subtitle',
    'home_hero_bg'                 => 'Hero Background Image',
    'home_rooms_badge'             => 'Featured Rooms Badge',
    'home_rooms_title'             => 'Featured Rooms Title',
    'home_rooms_subtitle'          => 'Featured Rooms Subtitle',
    'home_amenities_badge'         => 'Amenities Badge',
    'home_amenities_title'         => 'Amenities Title',
    'home_amenities_subtitle'      => 'Amenities Subtitle',
    'home_why_badge'               => 'Why Us Badge',
    'home_why_title'               => 'Why Us Title',
    'home_why_subtitle'            => 'Why Us Subtitle',
    'home_how_badge'               => 'How It Works Badge',
    'home_how_title'               => 'How It Works Title',
    'home_how_subtitle'            => 'How It Works Subtitle',
    'home_testimonials_badge'      => 'Testimonials Badge',
    'home_testimonials_title'      => 'Testimonials Title',
    'home_testimonials_subtitle'   => 'Testimonials Subtitle',
    'home_announcements_badge'     => 'Announcements Badge',
    'home_announcements_title'     => 'Announcements Title',
    'home_announcements_subtitle'  => 'Announcements Subtitle',
    'home_why_1_title'             => 'Why Card 1 — Title',
    'home_why_1_desc'              => 'Why Card 1 — Description',
    'home_why_2_title'             => 'Why Card 2 — Title',
    'home_why_2_desc'              => 'Why Card 2 — Description',
    'home_why_3_title'             => 'Why Card 3 — Title',
    'home_why_3_desc'              => 'Why Card 3 — Description',
    'home_why_4_title'             => 'Why Card 4 — Title',
    'home_why_4_desc'              => 'Why Card 4 — Description',
    'home_step_1_title'            => 'Step 1 — Title',
    'home_step_1_desc'             => 'Step 1 — Description',
    'home_step_2_title'            => 'Step 2 — Title',
    'home_step_2_desc'             => 'Step 2 — Description',
    'home_step_3_title'            => 'Step 3 — Title',
    'home_step_3_desc'             => 'Step 3 — Description',
    'home_cta_title'               => 'Call-to-action Title',
    'home_cta_text'                => 'Call-to-action Text',
    'home_cta_button'              => 'Call-to-action Button',
    'home_hours_weekday'           => 'Hours — Weekdays (Mon-Fri)',
    'home_hours_saturday'          => 'Hours — Saturday',
    'home_hours_sunday'            => 'Hours — Sunday',

    'about_title'                  => 'About Page Title',
    'about_subtitle'               => 'About Page Subtitle',
    'about_text'                   => 'Intro Text',
    'about_image'                  => 'About Image',
    'about_years_label'            => 'Years Badge (e.g. "12+ Years")',
    'about_years_sublabel'         => 'Years Badge Subtitle',
    'about_story_badge'            => 'Our Story — Badge',
    'about_story_title'            => 'Our Story — Title',
    'about_story_text'             => 'Our Story — Text (split with || for new lines)',
    'about_mission_title'          => 'Mission Heading',
    'about_mission'                => 'Mission Text',
    'about_vision_title'           => 'Vision Heading',
    'about_vision'                 => 'Vision Text',
    'about_check_1'                => 'Checklist Item 1',
    'about_check_2'                => 'Checklist Item 2',
    'about_check_3'                => 'Checklist Item 3',
    'about_check_4'                => 'Checklist Item 4',
    'about_stat_1'                 => 'Stats Label 1',
    'about_stat_2'                 => 'Stats Label 2',
    'about_stat_3'                 => 'Stats Label 3',
    'about_stat_4'                 => 'Stats Label 4',
    'about_team_badge'             => 'Our Team — Badge',
    'about_team_title'             => 'Our Team — Title',
    'about_team_subtitle'          => 'Our Team — Subtitle',
    'about_team_role'              => 'Team Role Label',
    'about_team_contact_btn'       => 'Team Contact Button',
    'about_team_contact_hint'      => 'Team Contact Hint',
    'about_values_badge'           => 'Our Values — Badge',
    'about_values_title'           => 'Our Values — Title',

    'contact_page_title'           => 'Contact Page Title',
    'site_address'                 => 'Address',
    'site_phone'                   => 'Phone',
    'site_email'                   => 'Email',
    'home_hours_weekday'           => 'Working Hours — Weekdays (Mon-Fri)',
    'home_hours_saturday'          => 'Working Hours — Saturday',
    'home_hours_sunday'            => 'Working Hours — Sunday',
    'contact_form_title'           => 'Contact Form Title',
    'contact_info_title'           => 'Contact Info Title',
    'contact_label_address'        => 'Address Label',
    'contact_label_phone'          => 'Phone Label',
    'contact_label_email'          => 'Email Label',
    'contact_label_hours'          => 'Working Hours Label',
    'contact_name_label'           => 'Name — Field Label',
    'contact_name_placeholder'     => 'Name — Placeholder',
    'contact_email_label'          => 'Email — Field Label',
    'contact_email_placeholder'    => 'Email — Placeholder',
    'contact_phone_label'          => 'Phone — Field Label',
    'contact_phone_placeholder'    => 'Phone — Placeholder',
    'contact_subject_label'        => 'Subject — Field Label',
    'contact_subject_select'       => 'Subject — Default Option',
    'contact_subject_options'      => 'Subject — Options (separate with ;)',
    'contact_message_label'        => 'Message — Field Label',
    'contact_message_placeholder'  => 'Message — Placeholder',
    'contact_button_label'         => 'Send Button Text',
];

// Short description shown under each group heading.
$groupHelp = [
    'general' => 'Your brand name and contact details shown site-wide (navbar, footer, contact page).',
    'social'  => 'Links to your social media pages, shown in the website footer.',
    'home'    => 'All the text and images on your public landing page.',
    'about'   => 'The content of your public About page, including Mission and Vision.',
    'contact' => 'Everything your visitors see on the public Contact page: page heading, contact information, working hours, and the form fields (labels, placeholders, subject options, and send button).',
];

// Rich, accurate help text per setting. Used instead of the short stored
// description so admins understand exactly what each field does.
$help = [
    'site_name'                => 'The name of your dormitory, shown in the navbar, page titles, and footer.',
    'site_tagline'             => 'A short slogan shown in the website footer under your name.',
    'site_email'               => 'The public contact email displayed on the Contact page and footer.',
    'site_phone'               => 'The public contact phone number displayed on the Contact page and footer.',
    'site_address'             => 'Your physical address, shown on the Contact page and in the footer.',
    'site_logo'                => 'Your logo image. Shown in the navbar and footer. Leave empty to use the text name.',

    'facebook_url'             => 'Full URL to your Facebook page. Shown as a link in the footer.',
    'twitter_url'              => 'Full URL to your Twitter / X profile. Shown in the footer.',
    'instagram_url'            => 'Full URL to your Instagram page. Shown in the footer.',

    'home_hero_badge'          => 'Small tag above the hero heading (e.g. "#Tenant Dormitory").',
    'home_hero_title_1'        => 'First line of the big hero heading.',
    'home_hero_title_2'        => 'Second line of the hero heading, before the highlighted word.',
    'home_hero_title_accent'   => 'The word highlighted in the brand green color inside the hero heading.',
    'home_hero_subtitle'       => 'The supporting paragraph under the hero heading.',
    'home_hero_bg'             => 'Optional background image for the hero. Leave empty to keep the default green gradient.',
    'home_rooms_badge'         => 'Small tag above the Featured Rooms heading.',
    'home_rooms_title'         => 'Heading of the Featured Rooms section.',
    'home_rooms_subtitle'      => 'Subtitle of the Featured Rooms section.',
    'home_amenities_badge'     => 'Small tag above the Amenities heading.',
    'home_amenities_title'     => 'Heading of the Amenities section.',
    'home_amenities_subtitle'  => 'Subtitle of the Amenities section.',
    'home_why_badge'           => 'Small tag above the Why Choose Us heading.',
    'home_why_title'           => 'Heading of the Why Choose Us section.',
    'home_why_subtitle'        => 'Subtitle of the Why Choose Us section.',
    'home_how_badge'           => 'Small tag above the How It Works heading.',
    'home_how_title'           => 'Heading of the How It Works section.',
    'home_how_subtitle'        => 'Subtitle of the How It Works section.',
    'home_testimonials_badge'  => 'Small tag above the Testimonials heading.',
    'home_testimonials_title'  => 'Heading of the Testimonials section.',
    'home_testimonials_subtitle' => 'Subtitle of the Testimonials section.',
    'home_announcements_badge' => 'Small tag above the Announcements heading.',
    'home_announcements_title' => 'Heading of the Announcements section.',
    'home_announcements_subtitle' => 'Subtitle of the Announcements section.',
    'home_why_1_title'         => 'Title of the first "Why Choose Us" card.',
    'home_why_1_desc'          => 'Description of the first "Why Choose Us" card.',
    'home_why_2_title'         => 'Title of the second "Why Choose Us" card.',
    'home_why_2_desc'          => 'Description of the second "Why Choose Us" card.',
    'home_why_3_title'         => 'Title of the third "Why Choose Us" card.',
    'home_why_3_desc'          => 'Description of the third "Why Choose Us" card.',
    'home_why_4_title'         => 'Title of the fourth "Why Choose Us" card.',
    'home_why_4_desc'          => 'Description of the fourth "Why Choose Us" card.',
    'home_step_1_title'        => 'Title of step 1 in "How It Works".',
    'home_step_1_desc'         => 'Description of step 1 in "How It Works".',
    'home_step_2_title'        => 'Title of step 2 in "How It Works".',
    'home_step_2_desc'         => 'Description of step 2 in "How It Works".',
    'home_step_3_title'        => 'Title of step 3 in "How It Works".',
    'home_step_3_desc'         => 'Description of step 3 in "How It Works".',
    'home_cta_title'           => 'Heading of the call-to-action banner near the bottom of the home page.',
    'home_cta_text'            => 'Text of the call-to-action banner.',
    'home_cta_button'          => 'Text on the button in the call-to-action banner.',
    'home_hours_weekday'       => 'Reception hours shown for weekdays (Mon-Fri) in the footer and contact page.',
    'home_hours_saturday'      => 'Reception hours shown for Saturday in the footer and contact page.',
    'home_hours_sunday'        => 'Reception hours shown for Sunday in the footer and contact page.',

    'about_title'              => 'Main heading at the top of the About page.',
    'about_subtitle'           => 'Subtitle under the main About page heading.',
    'about_text'               => 'A short introductory paragraph shown at the top of the About page.',
    'about_image'              => 'The main image shown on the About page.',
    'about_years_label'        => 'The big number/text in the yellow "years of service" badge.',
    'about_years_sublabel'     => 'The small text under the years badge (e.g. "of Service").',
    'about_story_badge'        => 'Small tag above the "Our Story" title.',
    'about_story_title'        => 'Heading of the "Our Story" section.',
    'about_story_text'         => 'The story text. Use <code>||</code> between two parts to start a new paragraph.',
    'about_mission_title'      => 'Heading of the Mission card.',
    'about_mission'            => 'The Mission statement text.',
    'about_vision_title'       => 'Heading of the Vision card.',
    'about_vision'             => 'The Vision statement text.',
    'about_check_1'            => 'First checkmark item under "Our Story".',
    'about_check_2'            => 'Second checkmark item under "Our Story".',
    'about_check_3'            => 'Third checkmark item under "Our Story".',
    'about_check_4'            => 'Fourth checkmark item under "Our Story".',
    'about_stat_1'             => 'Label under the first statistics count (rooms).',
    'about_stat_2'             => 'Label under the second statistics count (available rooms).',
    'about_stat_3'             => 'Label under the third statistics count (tenants).',
    'about_stat_4'             => 'Label under the fourth statistics count (amenities).',
    'about_team_badge'         => 'Small tag above the "Our Team" title.',
    'about_team_title'         => 'Heading of the "Our Team" section.',
    'about_team_subtitle'      => 'Subtitle of the "Our Team" section.',
    'about_team_role'          => 'Role label shown under each team member name (e.g. "Manager").',
    'about_team_contact_btn'   => 'Text on the team member contact button.',
    'about_team_contact_hint'  => 'Fallback text when a team member has no contact link.',
    'about_values_badge'       => 'Small tag above the "Our Values" title.',
    'about_values_title'       => 'Heading of the "Our Values" section.',

    'contact_page_title'       => 'Main heading at the top of the Contact page.',
    'contact_form_title'       => 'Heading of the message form card.',
    'contact_info_title'       => 'Heading of the "Contact Information" card.',
    'contact_label_address'    => 'Label above the address on the contact info card.',
    'contact_label_phone'      => 'Label above the phone number on the contact info card.',
    'contact_label_email'      => 'Label above the email on the contact info card.',
    'contact_label_hours'      => 'Label above the working hours on the contact info card.',
    'contact_name_label'       => 'Label of the "Full Name" field in the contact form.',
    'contact_name_placeholder' => 'Placeholder text of the full name field.',
    'contact_email_label'      => 'Label of the email field in the contact form.',
    'contact_email_placeholder'=> 'Placeholder text of the email field.',
    'contact_phone_label'      => 'Label of the phone field in the contact form.',
    'contact_phone_placeholder'=> 'Placeholder text of the phone field.',
    'contact_subject_label'    => 'Label of the subject field in the contact form.',
    'contact_subject_select'   => 'The first (blank/placeholder) option in the subject dropdown.',
    'contact_subject_options'  => 'The selectable subject options. Separate each one with a semicolon ( ; ).',
    'contact_message_label'    => 'Label of the message field in the contact form.',
    'contact_message_placeholder' => 'Placeholder text of the message field.',
    'contact_button_label'     => 'Text on the "Send" button in the contact form.',
];

$moneySettings = moneySettings();
$fileSettingKeys = ['site_logo', 'about_image', 'google_maps_image', 'home_hero_bg'];
?>

<?php if (!empty($settings)): ?>
    <?php
    // Site-wide contact details (address / phone / email) and working hours.
    // These live in the general/home groups but are only shown here under the
    // Contact group so the same field never appears in two places.
    $functionalContactKeys = ['site_address', 'site_phone', 'site_email', 'home_hours_weekday', 'home_hours_saturday', 'home_hours_sunday'];

    $groups = [];
    $byKey = [];
    foreach ($settings as $s) {
        $byKey[$s['setting_key']] = $s;
        $group = $s['setting_group'] ?? 'general';
        if (!in_array($group, $visibleGroups)) continue;
        // The functional contact fields are shown only under the Contact
        // group, so exclude them from General and Home to avoid duplication.
        if (in_array($s['setting_key'], $functionalContactKeys) && in_array($group, ['general', 'home'])) continue;
        $groups[$group][] = $s;
    }
    // Build the Contact group from every 'contact' group setting (page heading,
    // info-card labels, form labels, placeholders, subject options, button text)
    // plus the site-wide functional contact fields above.
    $contactKeys = array_unique(array_merge(
        $functionalContactKeys,
        array_column(array_filter($settings, function ($s) {
            return ($s['setting_group'] ?? '') === 'contact';
        }), 'setting_key')
    ));
    $groups['contact'] = array_values(array_filter($settings, function ($s) use ($contactKeys) {
        return in_array($s['setting_key'], $contactKeys);
    }));
    ksort($groups);
    ?>

    <form method="POST" action="<?= url('/admin/system-settings') ?>" enctype="multipart/form-data" id="settingsForm">
        <?= csrf_field() ?>

        <div class="accordion" id="settingsAccordion">
            <?php $first = true; ?>
            <?php foreach ($groups as $groupName => $groupSettings): ?>
                <?php $meta = $groupMeta[$groupName]; ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header p-0">
                        <button class="btn btn-light w-100 d-flex align-items-center gap-3 text-start p-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= e($groupName) ?>" aria-expanded="<?= $first ? 'true' : 'false' ?>">
                            <span class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:<?= $meta['color'] ?>18;color:<?= $meta['color'] ?>;">
                                <i class="fas <?= $meta['icon'] ?>"></i>
                            </span>
                            <span>
                                <span class="fw-bold d-block"><?= e($meta['label']) ?></span>
                                <small class="text-muted"><?= count($groupSettings) ?> setting<?= count($groupSettings) > 1 ? 's' : '' ?></small>
                            </span>
                            <i class="fas fa-chevron-down ms-auto text-muted"></i>
                        </button>
                    </div>
                    <div id="collapse-<?= e($groupName) ?>" class="collapse <?= $first ? 'show' : '' ?>" data-bs-parent="#settingsAccordion">
                        <div class="card-body">
                            <?php if (!empty($groupHelp[$groupName])): ?>
                                <p class="text-muted small mb-3"><i class="fas fa-circle-info me-1"></i><?= e($groupHelp[$groupName]) ?></p>
                            <?php endif; ?>
                            <div class="row g-3">
                                <?php foreach ($groupSettings as $s): ?>
                                    <?php $label = $labels[$s['setting_key']] ?? ucwords(str_replace(['_','-'], ' ', $s['setting_key'])); ?>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold"><?= e($label) ?></label>

                                        <?php if ($s['setting_type'] === 'email'): ?>
                                            <input type="email" name="<?= e($s['setting_key']) ?>" class="form-control" value="<?= e($s['setting_value'] ?? '') ?>">

                                        <?php elseif ($s['setting_type'] === 'textarea'): ?>
                                            <?php $taRows = in_array($s['setting_key'], ['about_story_text']) ? 4 : 3; ?>
                                            <textarea name="<?= e($s['setting_key']) ?>" class="form-control" rows="<?= $taRows ?>"><?= e($s['setting_value'] ?? '') ?></textarea>

                                        <?php elseif ($s['setting_type'] === 'number' || $s['setting_type'] === 'integer'): ?>
                                            <input type="number" name="<?= e($s['setting_key']) ?>" class="form-control" value="<?= e($s['setting_value'] ?? '') ?>">

                                        <?php elseif ($s['setting_type'] === 'boolean'): ?>
                                            <select name="<?= e($s['setting_key']) ?>" class="form-select">
                                                <option value="1" <?= ($s['setting_value'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                                                <option value="0" <?= ($s['setting_value'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                                            </select>

                                        <?php elseif ($s['setting_type'] === 'select'): ?>
                                            <select name="<?= e($s['setting_key']) ?>" class="form-select">
                                                <?php $options = json_decode($s['setting_options'] ?? '[]', true) ?? []; ?>
                                                <?php foreach ($options as $opt): ?>
                                                    <option value="<?= e($opt) ?>" <?= ($s['setting_value'] ?? '') === $opt ? 'selected' : '' ?>><?= e(ucfirst($opt)) ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                        <?php elseif ($s['setting_type'] === 'file' || in_array($s['setting_key'], $fileSettingKeys)): ?>
                                            <?php $fileKey = $s['setting_key']; ?>
                                            <?php $hasFile = !empty($s['setting_value']); ?>
                                            <div class="d-flex align-items-start gap-3 mb-2" id="current-file-<?= e($fileKey) ?>">
                                                <?php if ($hasFile): ?>
                                                    <img src="<?= UPLOAD_URL . $s['setting_value'] ?>" alt="" class="rounded border" style="max-height:90px;max-width:160px;object-fit:cover;">
                                                    <div>
                                                        <div class="small text-muted text-break" style="max-width:180px;"><?= e(basename($s['setting_value'])) ?></div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-setting-file" data-key="<?= e($fileKey) ?>"><i class="fas fa-trash me-1"></i>Remove</button>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-muted small">No image uploaded yet.</div>
                                                <?php endif; ?>
                                                <input type="hidden" name="remove_<?= e($fileKey) ?>" id="remove-<?= e($fileKey) ?>" value="0">
                                            </div>
                                            <input type="file" name="<?= e($fileKey) ?>" class="form-control setting-file-input" accept="image/*" data-key="<?= e($fileKey) ?>" data-preview="#preview-<?= e($fileKey) ?>">
                                            <div id="preview-<?= e($fileKey) ?>" class="mt-2"></div>

                                        <?php else: ?>
                                            <input type="text" name="<?= e($s['setting_key']) ?>" class="form-control" value="<?= e($s['setting_value'] ?? '') ?>">
                                        <?php endif; ?>

                                        <?php $fieldHelp = $help[$s['setting_key']] ?? ($s['description'] ?? ''); ?>
                                        <?php if (!empty($fieldHelp)): ?>
                                            <div class="form-text small"><?= $fieldHelp ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $first = false; ?>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-save me-2"></i>Save All Settings
            </button>
        </div>
    </form>

<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-cog fa-3x text-muted mb-3 d-block"></i>
            <h5>No settings found</h5>
            <p class="text-muted">The system_settings table may need to be seeded.</p>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.setting-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            var key = this.getAttribute('data-key');
            var preview = document.querySelector(this.getAttribute('data-preview'));
            var removeField = document.getElementById('remove-' + key);
            var currentWrap = document.getElementById('current-file-' + key);
            if (removeField) removeField.value = '0';
            if (currentWrap) currentWrap.style.opacity = '1';
            if (preview) preview.innerHTML = '';
            if (this.files && this.files[0]) {
                var f = this.files[0];
                if (!f.type.match(/^image\//)) { alert('Please select an image file.'); this.value = ''; return; }
                if (f.size > 2 * 1024 * 1024) { alert('File is too large. Maximum size is 2MB.'); this.value = ''; return; }
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) preview.innerHTML = '<img src="' + e.target.result + '" class="rounded border" style="max-height:90px;max-width:160px;object-fit:cover;">';
                };
                reader.readAsDataURL(f);
            }
        });
    });

    document.querySelectorAll('.remove-setting-file').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var key = this.getAttribute('data-key');
            var removeField = document.getElementById('remove-' + key);
            var fileInput = document.querySelector('.setting-file-input[data-key="' + key + '"]');
            var currentWrap = document.getElementById('current-file-' + key);
            if (removeField) removeField.value = '1';
            if (fileInput) fileInput.value = '';
            var preview = document.querySelector(fileInput ? fileInput.getAttribute('data-preview') : '');
            if (preview) preview.innerHTML = '';
            if (currentWrap) currentWrap.style.opacity = '0.35';
        });
    });

    var form = document.getElementById('settingsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var valid = true;
            document.querySelectorAll('input[type="number"]').forEach(function(input) {
                var val = input.value.trim();
                if (val !== '' && !/^\d+$/.test(val)) {
                    alert('Please enter a whole number for "' + (input.name || 'value') + '".');
                    input.focus();
                    valid = false;
                }
            });
            if (!valid) e.preventDefault();
        });
    }
});
</script>
