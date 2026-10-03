-- ============================================================
-- MIGRATION: About & Contact Page Content Settings
-- Makes the remaining hardcoded About page labels and the whole
-- Contact page (headings, form labels, placeholders, subject
-- options, button, info-card labels) editable from System Settings.
--
-- Adds a new 'contact' group and extends the 'about' group.
-- Uses INSERT IGNORE so existing rows are never overwritten and
-- re-running is safe. Does NOT query INFORMATION_SCHEMA.
-- ============================================================

-- ------------------------------------------------------------
-- ABOUT GROUP  (extend: badges, headings, checklist, stat labels,
--               team labels — appended after existing about_* rows)
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('about_story_badge',   'Our Story',                 'text', 'about', 65, 'About page: Our Story section badge label.'),
('about_mission_title', 'Our Mission',               'text', 'about', 66, 'About page: Mission card heading.'),
('about_vision_title',  'Our Vision',                'text', 'about', 67, 'About page: Vision card heading.'),
('about_check_1',       'Safe & Secure',             'text', 'about', 68, 'About page: checklist item 1.'),
('about_check_2',       'Affordable Rates',          'text', 'about', 69, 'About page: checklist item 2.'),
('about_check_3',       'Modern Amenities',          'text', 'about', 70, 'About page: checklist item 3.'),
('about_check_4',       'Friendly Community',        'text', 'about', 71, 'About page: checklist item 4.'),
('about_stat_1',        'Total Rooms',               'text', 'about', 72, 'About page: stats band — label for total rooms count.'),
('about_stat_2',        'Available Rooms',           'text', 'about', 73, 'About page: stats band — label for available rooms count.'),
('about_stat_3',        'Tenant Record',             'text', 'about', 74, 'About page: stats band — label for tenant count.'),
('about_stat_4',        'Amenities',                 'text', 'about', 75, 'About page: stats band — label for amenities count.'),
('about_team_badge',    'Our Team',                  'text', 'about', 76, 'About page: Our Team section badge label.'),
('about_team_role',     'Manager',                   'text', 'about', 77, 'About page: role label shown under each team member.'),
('about_team_contact_btn', 'Contact / Visit',        'text', 'about', 78, 'About page: team member contact button label.'),
('about_team_contact_hint', 'Contact here for more details!', 'text', 'about', 79, 'About page: hint shown when a team member has no link.'),
('about_values_badge',  'Our Values',                'text', 'about', 80, 'About page: Our Values section badge label.');

-- ------------------------------------------------------------
-- CONTACT GROUP  (new)
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('contact_page_title',   'Contact Us',              'text',     'contact', 1,  'Contact page: main heading (breadcrumb uses the same label).'),
('contact_form_title',   'Send Us a Message',       'text',     'contact', 2,  'Contact page: form card heading.'),
('contact_info_title',   'Contact Information',     'text',     'contact', 3,  'Contact page: contact information card heading.'),
('contact_label_address','Address',                 'text',     'contact', 4,  'Contact page: address field label.'),
('contact_label_phone',  'Phone',                   'text',     'contact', 5,  'Contact page: phone field label.'),
('contact_label_email',  'Email',                   'text',     'contact', 6,  'Contact page: email field label.'),
('contact_label_hours',  'Working Hours',           'text',     'contact', 7,  'Contact page: working hours field label.'),
('contact_name_label',   'Full Name',               'text',     'contact', 8,  'Contact form: full name label.'),
('contact_name_placeholder', 'Your full name',      'text',     'contact', 9,  'Contact form: full name placeholder.'),
('contact_email_label',  'Email Address',           'text',     'contact', 10, 'Contact form: email label.'),
('contact_email_placeholder', 'you@example.com',    'text',     'contact', 11, 'Contact form: email placeholder.'),
('contact_phone_label',  'Phone Number',            'text',     'contact', 12, 'Contact form: phone label.'),
('contact_phone_placeholder', '09XXXXXXXXX',        'text',     'contact', 13, 'Contact form: phone placeholder.'),
('contact_subject_label','Subject',                 'text',     'contact', 14, 'Contact form: subject label.'),
('contact_subject_select','Select a subject',       'text',     'contact', 15, 'Contact form: first (placeholder) subject option.'),
('contact_subject_options','Room Inquiry; Reservation; Payment; Maintenance; General Inquiry; Feedback', 'textarea', 'contact', 16, 'Contact form: subject options, one per line or separated by semicolons.'),
('contact_message_label','Message',                 'text',     'contact', 17, 'Contact form: message label.'),
('contact_message_placeholder','Write your message here...', 'text', 'contact', 18, 'Contact form: message placeholder.'),
('contact_button_label','Send Message',             'text',     'contact', 19, 'Contact form: submit button label.');

SELECT 'About & Contact settings migration complete!' AS result;
