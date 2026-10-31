-- ==========================================================
-- Migration: Create tbl_client_invoices Table & Operation Permissions
-- Purpose: Store client invoices (Amount & File uploads) + Permissions
-- ==========================================================

-- 1. Table for Client Invoices
CREATE TABLE IF NOT EXISTS `tbl_client_invoices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `invoice_file` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ci_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Operation Permissions for Invoices
INSERT INTO `hc_permissions` (`permission_name`, `permission_key`) 
SELECT 'Client Invoice View', 'client_invoice_view'
WHERE NOT EXISTS (SELECT 1 FROM `hc_permissions` WHERE `permission_key` = 'client_invoice_view');

INSERT INTO `hc_permissions` (`permission_name`, `permission_key`) 
SELECT 'Client Invoice Upload', 'client_invoice_upload'
WHERE NOT EXISTS (SELECT 1 FROM `hc_permissions` WHERE `permission_key` = 'client_invoice_upload');
