-- ============================================================
-- MIGRATION: Add 'refund' to notifications.type ENUM
-- The refund decision flow (AdminController/ManagerController)
-- inserts notifications with type='refund', but the ENUM does not
-- include it. Under strict MySQL this insert throws an exception
-- and the refund approve/reject action fails with a 500.
--
-- Plain ALTER TABLE — no information_schema (InfinityFree safe).
-- Safe to run multiple times; if the ENUM already contains 'refund'
-- MySQL simply applies an equivalent definition without error.
-- ============================================================

ALTER TABLE `notifications`
    MODIFY COLUMN `type` ENUM('reservation','payment','announcement','maintenance','complaint','system','refund') NOT NULL DEFAULT 'system';

SELECT 'Notifications type ENUM updated to include refund.' AS result;
