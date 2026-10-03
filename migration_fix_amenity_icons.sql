-- Migration: Fix amenity icons to include 'fas' prefix for Font Awesome 6
-- Updates any existing icons that are missing the 'fas ' prefix

UPDATE `amenities` SET `icon` = CONCAT('fas ', `icon`)
WHERE `icon` IS NOT NULL AND `icon` != '' AND `icon` NOT LIKE 'fas %' AND `icon` NOT LIKE 'far %' AND `icon` NOT LIKE 'fab %';

-- Fix any amenities with empty/null icons based on name
UPDATE `amenities` SET `icon` = 'fas fa-wifi' WHERE `name` LIKE '%Wi-Fi%' OR `name` LIKE '%Wifi%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-snowflake' WHERE `name` LIKE '%Air Condition%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-desktop' WHERE `name` LIKE '%Study Desk%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-door-closed' WHERE `name` LIKE '%Wardrobe%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-bath' WHERE `name` LIKE '%Bathroom%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-temperature-high' WHERE `name` LIKE '%Hot Water%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-shirt' WHERE `name` LIKE '%Laundry%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-utensils' WHERE `name` LIKE '%Kitchen%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-couch' WHERE `name` LIKE '%Common Area%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-shield-halved' WHERE `name` LIKE '%Security%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-video' WHERE `name` LIKE '%CCTV%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-square-parking' WHERE `name` LIKE '%Parking%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-droplet' WHERE `name` LIKE '%Water%' AND `name` LIKE '%Dispenser%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-bolt' WHERE `name` LIKE '%Generator%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-broom' WHERE `name` LIKE '%Cleaning%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-bed' WHERE `name` LIKE '%Bed%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-plug' WHERE `name` LIKE '%Electrical%' AND (`icon` IS NULL OR `icon` = '');
UPDATE `amenities` SET `icon` = 'fas fa-person-shelter' WHERE `name` LIKE '%Balcony%' AND (`icon` IS NULL OR `icon` = '');
