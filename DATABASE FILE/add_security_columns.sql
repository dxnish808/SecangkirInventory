-- Add security columns to users table for login attempt tracking
-- Run this script to add the missing columns

ALTER TABLE `users` 
ADD COLUMN `failed_attempts` INT(11) DEFAULT 0 AFTER `last_login`,
ADD COLUMN `locked_until` DATETIME NULL DEFAULT NULL AFTER `failed_attempts`;

-- Update existing users to have 0 failed attempts
UPDATE `users` SET `failed_attempts` = 0 WHERE `failed_attempts` IS NULL; 