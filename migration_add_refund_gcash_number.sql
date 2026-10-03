-- Add gcash_number to refund_requests: the GCash mobile number where the
-- refund will be sent. Required for new requests (enforced in the app);
-- nullable so existing rows remain valid.

SET @dbname = DATABASE();

SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'refund_requests'
      AND COLUMN_NAME = 'gcash_number'
);

SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE `refund_requests` ADD COLUMN `gcash_number` VARCHAR(20) DEFAULT NULL AFTER `reason`',
    'SELECT "gcash_number column already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
