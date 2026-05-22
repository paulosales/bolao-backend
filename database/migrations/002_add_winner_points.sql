-- Migration 002: add winner_points to pool_rules
-- Safe to run on existing databases (IF NOT EXISTS guard via COLUMN check)

USE bolao_copa;

SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'bolao_copa'
      AND TABLE_NAME   = 'pool_rules'
      AND COLUMN_NAME  = 'winner_points'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE pool_rules ADD COLUMN winner_points TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER goal_difference_points',
    'SELECT ''Column winner_points already exists, skipping.'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
