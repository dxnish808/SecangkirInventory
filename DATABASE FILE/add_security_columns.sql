-- Add security columns to users table for login attempt tracking
-- Run this script to add the missing columns

ALTER TABLE `users` 
ADD COLUMN `failed_attempts` INT(11) DEFAULT 0 AFTER `last_login`,
ADD COLUMN `locked_until` DATETIME NULL DEFAULT NULL AFTER `failed_attempts`;

-- Update existing users to have 0 failed attempts
UPDATE `users` SET `failed_attempts` = 0 WHERE `failed_attempts` IS NULL;

-- Security enhancements for fake order verification protection
-- Add dual verification system

-- Add columns to restock table for dual verification
ALTER TABLE `restock` 
ADD COLUMN `verified_by_1` int(11) DEFAULT NULL,
ADD COLUMN `verified_by_2` int(11) DEFAULT NULL,
ADD COLUMN `verification_1_date` datetime DEFAULT NULL,
ADD COLUMN `verification_2_date` datetime DEFAULT NULL,
ADD COLUMN `verification_status` enum('pending','partial','completed','rejected') DEFAULT 'pending',
ADD COLUMN `rejection_reason` text DEFAULT NULL;

-- Create audit trail table for restock verifications
CREATE TABLE `restock_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restock_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_values` text,
  `new_values` text,
  `ip_address` varchar(45),
  `user_agent` text,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `restock_id` (`restock_id`),
  KEY `user_id` (`user_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create verification attempts table for anomaly detection
CREATE TABLE `verification_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `restock_id` int(11) NOT NULL,
  `attempt_type` enum('verify','reject') NOT NULL,
  `ip_address` varchar(45),
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `restock_id` (`restock_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create anomaly detection alerts table
CREATE TABLE `security_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_type` varchar(50) NOT NULL,
  `user_id` int(11),
  `restock_id` int(11),
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('new','investigating','resolved','false_positive') DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_type` (`alert_type`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create verification rules table for configurable thresholds
CREATE TABLE `verification_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(100) NOT NULL,
  `rule_type` varchar(50) NOT NULL,
  `threshold_value` decimal(10,2),
  `threshold_count` int(11),
  `time_window_minutes` int(11),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_name` (`rule_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default verification rules
INSERT INTO `verification_rules` (`rule_name`, `rule_type`, `threshold_value`, `threshold_count`, `time_window_minutes`, `is_active`) VALUES
('high_value_restock', 'value_threshold', 1000.00, NULL, NULL, 1),
('bulk_quantity_restock', 'quantity_threshold', NULL, 100, NULL, 1),
('rapid_verification_attempts', 'frequency_check', NULL, 5, 60, 1),
('unusual_time_verification', 'time_pattern', NULL, NULL, NULL, 1),
('same_user_dual_verification', 'user_conflict', NULL, NULL, NULL, 1);

-- Add foreign key constraints
ALTER TABLE `restock` 
ADD CONSTRAINT `fk_restock_verified_by_1` FOREIGN KEY (`verified_by_1`) REFERENCES `users` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_restock_verified_by_2` FOREIGN KEY (`verified_by_2`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `restock_audit_log`
ADD CONSTRAINT `fk_audit_restock` FOREIGN KEY (`restock_id`) REFERENCES `restock` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `verification_attempts`
ADD CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_attempts_restock` FOREIGN KEY (`restock_id`) REFERENCES `restock` (`id`) ON DELETE CASCADE;

ALTER TABLE `security_alerts`
ADD CONSTRAINT `fk_alerts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_alerts_restock` FOREIGN KEY (`restock_id`) REFERENCES `restock` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_alerts_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL; 