-- ============================================================
-- MIGRATION: Finalize "Affordable Rates" Why-Choose-Us card
-- Fixes the compound HTML-entity corruption on home_why_1_desc
-- (each save re-encoded the apostrophe: &#039; -> &amp;#039; -> ...).
--
-- Resets the stored values to clean, plain text. Re-running is safe.
-- ============================================================

UPDATE `system_settings`
SET `setting_value` = 'Competitive pricing that fits within a tenant''s budget without compromising quality.'
WHERE `setting_key` = 'home_why_1_desc';

UPDATE `system_settings`
SET `setting_value` = 'Affordable Rates'
WHERE `setting_key` = 'home_why_1_title';

SELECT 'Why-card 1 settings finalized!' AS result;