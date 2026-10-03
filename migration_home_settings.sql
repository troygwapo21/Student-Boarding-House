-- ============================================================
-- MIGRATION: Home Page Content Settings
-- Adds a 'home' grouping of system_settings so the admin can edit
-- the public home page (hero, section headings, why-us items,
-- how-it-works steps, CTA, working hours) from System Settings.
--
-- Uses INSERT IGNORE so existing rows are never overwritten and
-- re-running this file is safe. Does NOT query INFORMATION_SCHEMA
-- (InfinityFree phpMyAdmin cannot read it).
-- ============================================================

-- ------------------------------------------------------------
-- HERO
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('home_hero_badge',      '#Tenant Dormitory', 'text',     'home', 1,  'Hero section: small badge / tag line.'),
('home_hero_title_1',    'Your Home',          'text',     'home', 2,  'Hero heading line 1.'),
('home_hero_title_2',    'Away From',          'text',     'home', 3,  'Hero heading line 2 (before the highlighted word).'),
('home_hero_title_accent','Home',              'text',     'home', 4,  'Hero heading: highlighted word shown in the brand color.'),
('home_hero_subtitle',   'Find the perfect boarding house room that suits your needs and budget. Comfortable, safe, and affordable accommodations for tenants.', 'textarea', 'home', 5, 'Hero section: supporting paragraph.'),
('home_hero_bg',         '',                   'file',     'home', 6,  'Optional hero background image (leave empty to use the default green gradient).');

-- ------------------------------------------------------------
-- SECTION HEADERS  (badge / title / subtitle)
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('home_rooms_badge',      'Featured',                       'text',     'home', 10, 'Featured Rooms section: badge label.'),
('home_rooms_title',      'Featured Rooms',                 'text',     'home', 11, 'Featured Rooms section: heading.'),
('home_rooms_subtitle',   'Handpicked rooms selected for their comfort, amenities, and great value.', 'textarea', 'home', 12, 'Featured Rooms section: subtitle.'),
('home_amenities_badge',  'What We Offer',                  'text',     'home', 13, 'Amenities section: badge label.'),
('home_amenities_title',  'Our Amenities',                  'text',     'home', 14, 'Amenities section: heading.'),
('home_amenities_subtitle','Everything you need for a comfortable tenant life, all in one place.', 'textarea', 'home', 15, 'Amenities section: subtitle.'),
('home_why_badge',        'Why Us',                         'text',     'home', 16, 'Why Choose Us section: badge label.'),
('home_why_title',        'Why Choose Us',                  'text',     'home', 17, 'Why Choose Us section: heading.'),
('home_why_subtitle',     'We go above and beyond to provide the best boarding house experience for tenants.', 'textarea', 'home', 18, 'Why Choose Us section: subtitle.'),
('home_how_badge',        'Simple Process',                 'text',     'home', 19, 'How It Works section: badge label.'),
('home_how_title',        'How It Works',                   'text',     'home', 20, 'How It Works section: heading.'),
('home_how_subtitle',     'Getting your perfect room is easy with our simple 3-step process.', 'textarea', 'home', 21, 'How It Works section: subtitle.'),
('home_testimonials_badge','Testimonials',                  'text',     'home', 22, 'Testimonials section: badge label.'),
('home_testimonials_title','What Tenants Say',              'text',     'home', 23, 'Testimonials section: heading.'),
('home_testimonials_subtitle','Hear from our happy residents about their experience living with us.', 'textarea', 'home', 24, 'Testimonials section: subtitle.'),
('home_announcements_badge','Updates',                      'text',     'home', 25, 'Announcements section: badge label.'),
('home_announcements_title','Latest Announcements',         'text',     'home', 26, 'Announcements section: heading.'),
('home_announcements_subtitle','Stay informed with the latest news and updates from the boarding house.', 'textarea', 'home', 27, 'Announcements section: subtitle.');

-- ------------------------------------------------------------
-- WHY CHOOSE US ITEMS  (4 cards: title / description)
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('home_why_1_title', 'Affordable Rates',   'text', 'home', 30, 'Why Choose Us: card 1 title.'),
('home_why_1_desc',  'Competitive pricing that fits within a tenant''s budget without compromising quality.', 'textarea', 'home', 31, 'Why Choose Us: card 1 description.'),
('home_why_2_title', 'Prime Location',     'text', 'home', 32, 'Why Choose Us: card 2 title.'),
('home_why_2_desc',  'Strategically located near universities, schools, public transport, and commercial areas.', 'textarea', 'home', 33, 'Why Choose Us: card 2 description.'),
('home_why_3_title', '24/7 Support',       'text', 'home', 34, 'Why Choose Us: card 3 title.'),
('home_why_3_desc',  'Our team is always available to assist you with any concerns or requests.', 'textarea', 'home', 35, 'Why Choose Us: card 3 description.'),
('home_why_4_title', 'Clean Environment',  'text', 'home', 36, 'Why Choose Us: card 4 title.'),
('home_why_4_desc',  'Regular cleaning and maintenance to ensure a hygienic living space for all residents.', 'textarea', 'home', 37, 'Why Choose Us: card 4 description.');

-- ------------------------------------------------------------
-- HOW IT WORKS STEPS  (3 steps: title / description)
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('home_step_1_title', 'Browse', 'text', 'home', 40, 'How It Works: step 1 title.'),
('home_step_1_desc',  'Explore our available rooms and find the one that matches your preferences and budget.', 'textarea', 'home', 41, 'How It Works: step 1 description.'),
('home_step_2_title', 'Reserve', 'text', 'home', 42, 'How It Works: step 2 title.'),
('home_step_2_desc',  'Submit your reservation request and complete the payment to secure your room.', 'textarea', 'home', 43, 'How It Works: step 2 description.'),
('home_step_3_title', 'Move In', 'text', 'home', 44, 'How It Works: step 3 title.'),
('home_step_3_desc',  'Get your keys and settle into your new home. Welcome to your boarding house family!', 'textarea', 'home', 45, 'How It Works: step 3 description.');

-- ------------------------------------------------------------
-- CTA SECTION
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('home_cta_title',  'Ready to Find Your Perfect Room?', 'text',     'home', 50, 'CTA section: heading.'),
('home_cta_text',   'Browse our available rooms and secure your spot today. Your ideal tenant accommodation is just a click away.', 'textarea', 'home', 51, 'CTA section: supporting text.'),
('home_cta_button', 'Browse Rooms',                      'text',     'home', 52, 'CTA section: button label.');

-- ------------------------------------------------------------
-- WORKING HOURS  (footer / contact)
-- ------------------------------------------------------------
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `description`)
VALUES
('home_hours_weekday',  '8:00 AM - 8:00 PM', 'text', 'home', 53, 'Working hours shown in the footer (weekdays).'),
('home_hours_saturday', '8:00 AM - 6:00 PM', 'text', 'home', 54, 'Working hours shown in the footer (Saturday).'),
('home_hours_sunday',   'Closed',             'text', 'home', 55, 'Working hours shown in the footer (Sunday).');

SELECT 'Home settings migration complete!' AS result;
