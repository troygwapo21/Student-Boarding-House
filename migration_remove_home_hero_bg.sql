-- ============================================================
-- MIGRATION: Remove Home Page Hero Background Picture
-- The public home page hero no longer displays a background image
-- (it now always uses the brand gradient). This clears any stored
-- home_hero_bg value so the System Settings field is empty too.
-- Safe to re-run.
-- ============================================================

UPDATE `system_settings` SET `setting_value` = '' WHERE `setting_key` = 'home_hero_bg';

SELECT 'Home hero background image cleared!' AS result;