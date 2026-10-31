-- ==========================================================
-- Migration: Add GST Fields to tbl_client & tbl_leads
-- Purpose: Support GST Yes/No, GSTIN Number, and GST PDF Upload
-- ==========================================================

-- 1. Add GST columns to tbl_client
ALTER TABLE `tbl_client` 
ADD COLUMN IF NOT EXISTS `is_gst` ENUM('No','Yes') NOT NULL DEFAULT 'No' AFTER `pan_card_file`,
ADD COLUMN IF NOT EXISTS `gst_number` VARCHAR(50) NULL DEFAULT NULL AFTER `is_gst`,
ADD COLUMN IF NOT EXISTS `gst_file` TEXT NULL DEFAULT NULL AFTER `gst_number`;

-- 2. Add GST columns to tbl_leads
ALTER TABLE `tbl_leads` 
ADD COLUMN IF NOT EXISTS `is_gst` ENUM('No','Yes') NOT NULL DEFAULT 'No',
ADD COLUMN IF NOT EXISTS `gst_number` VARCHAR(50) NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `gst_file` TEXT NULL DEFAULT NULL;
