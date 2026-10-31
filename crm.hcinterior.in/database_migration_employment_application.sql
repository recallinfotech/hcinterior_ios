-- ==============================================================
-- DATABASE MIGRATION SCRIPT: Employment Application Form & KYC
-- Company: High Creation Interior Projects Private Limited
-- ==============================================================

-- STEP 1: Create Table `tbl_employee_application_form`
CREATE TABLE IF NOT EXISTS `tbl_employee_application_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `father_mob_no` varchar(50) DEFAULT NULL,
  `fresher_experience` varchar(50) DEFAULT 'Fresher',
  `total_experience_years` varchar(50) DEFAULT '',
  `last_salary` varchar(100) DEFAULT '',
  `present_salary` varchar(100) DEFAULT '',
  `aadhaar_no` varchar(50) DEFAULT '',
  `pan_no` varchar(50) DEFAULT '',
  `date_of_marriage` date DEFAULT NULL,
  `spouse_name` varchar(150) DEFAULT '',
  `blood_group` varchar(20) DEFAULT '',
  `height_weight` varchar(50) DEFAULT '',
  `present_address` text DEFAULT NULL,
  `present_contact_no` varchar(50) DEFAULT '',
  `permanent_address` text DEFAULT NULL,
  `permanent_contact_no` varchar(50) DEFAULT '',
  `relative_friend_in_company` text DEFAULT NULL,
  `previously_applied` text DEFAULT NULL,
  `photograph` varchar(255) DEFAULT '',
  `aadhaar_card_doc` varchar(255) DEFAULT '',
  `pan_card_doc` varchar(255) DEFAULT '',
  `signature_doc` varchar(255) DEFAULT '',
  `educational_qualifications` longtext DEFAULT NULL,
  `professional_references` longtext DEFAULT NULL,
  `family_details` longtext DEFAULT NULL,
  `work_experience` longtext DEFAULT NULL,
  `declaration_date` date DEFAULT NULL,
  `declaration_place` varchar(150) DEFAULT '',
  `status` enum('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `rejection_reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_app` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- STEP 2: Add `father_mob_no` into `tbl_account_details`
-- (Ignore error if column already exists)
ALTER TABLE `tbl_account_details` 
ADD COLUMN `father_mob_no` VARCHAR(50) NULL DEFAULT NULL AFTER `father_name`;

-- STEP 3: Setup User Menu (24) Dropdown with Submenus
-- 3.1 Make menu 24 a parent dropdown
UPDATE `tbl_menu` SET `link` = '#' WHERE `menu_id` = 24;

-- 3.2 Add 'all_users' submenu under parent = 24 (if not exists)
SET @next_all_users_id = (SELECT COALESCE(MAX(`menu_id`), 0) + 1 FROM `tbl_menu`);

INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `status`)
SELECT 
  @next_all_users_id, 
  'all_users', 
  'admin/user/user_list', 
  'fa fa-circle-o', 
  24, 
  1, 
  1
WHERE NOT EXISTS (
  SELECT 1 FROM `tbl_menu` WHERE `link` = 'admin/user/user_list' AND `parent` = 24
);

-- 3.3 Add 'employment_applications' submenu under parent = 24 (if not exists)
SET @next_app_id = (SELECT COALESCE(MAX(`menu_id`), 0) + 1 FROM `tbl_menu`);

INSERT INTO `tbl_menu` (`menu_id`, `label`, `link`, `icon`, `parent`, `sort`, `status`)
SELECT 
  @next_app_id, 
  'employment_applications', 
  'admin/employment_applications', 
  'fa fa-id-card-o', 
  24, 
  2, 
  1
WHERE NOT EXISTS (
  SELECT 1 FROM `tbl_menu` WHERE `link` = 'admin/employment_applications'
);

-- If already existed, ensure parent is 24
UPDATE `tbl_menu` 
SET `parent` = 24, `sort` = 2 
WHERE `link` = 'admin/employment_applications';

-- STEP 4: Restrict Permissions ONLY to Designations that have User (Menu 24) access
DELETE ur FROM `tbl_user_role` ur
JOIN `tbl_menu` m ON m.`menu_id` = ur.`menu_id`
WHERE m.`link` IN ('admin/employment_applications', 'admin/user/user_list') AND m.`parent` = 24;

INSERT IGNORE INTO `tbl_user_role` (`designations_id`, `menu_id`, `view`, `created`, `edited`, `deleted`)
SELECT 
  ur.`designations_id`,
  m.`menu_id`,
  m.`menu_id`,
  m.`menu_id`,
  m.`menu_id`,
  m.`menu_id`
FROM `tbl_user_role` ur
CROSS JOIN `tbl_menu` m
WHERE ur.`menu_id` = 24 
  AND m.`parent` = 24 
  AND m.`link` IN ('admin/employment_applications', 'admin/user/user_list');
