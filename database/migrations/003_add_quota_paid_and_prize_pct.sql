-- Migration 003: quota_paid on pool_members + prize distribution on pool_rules
-- Run only once on existing databases (fresh installs use 001_create_tables.sql)

ALTER TABLE pool_members
    ADD COLUMN quota_paid TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE pool_rules
    ADD COLUMN prize_pct_1st TINYINT UNSIGNED NOT NULL DEFAULT 60,
    ADD COLUMN prize_pct_2nd TINYINT UNSIGNED NOT NULL DEFAULT 30,
    ADD COLUMN prize_pct_3rd TINYINT UNSIGNED NOT NULL DEFAULT 10;
