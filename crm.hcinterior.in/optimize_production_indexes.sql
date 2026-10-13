-- =====================================================================
-- PERFORMANCE OPTIMIZATION INDEXES FOR HCINTERIOR CRM (LIVE SERVER)
-- Run this SQL in phpMyAdmin or MySQL CLI on the Live server
-- =====================================================================

-- 1. tbl_leads indexes
ALTER TABLE `tbl_leads` ADD INDEX `idx_conv_created` (`converted_client_id`, `created_time`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_lead_status` (`lead_status_id`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_lead_source` (`lead_source_id`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_phone` (`phone`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_budget` (`budget_from`, `budget_to`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_assign_date` (`lead_assign_date`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_sub_status` (`sub_status`);
ALTER TABLE `tbl_leads` ADD INDEX `idx_is_duplicate` (`is_duplicate`);

-- 2. tbl_status_log indexes
ALTER TABLE `tbl_status_log` ADD INDEX `idx_status_lead_date` (`status`, `lead_id`, `createdate`);
ALTER TABLE `tbl_status_log` ADD INDEX `idx_lead_id` (`lead_id`);
ALTER TABLE `tbl_status_log` ADD INDEX `idx_update_by_date` (`update_by`, `createdate`);

-- 3. tbl_task_comment indexes
ALTER TABLE `tbl_task_comment` ADD INDEX `idx_mod_field_date` (`module`, `module_field_id`, `comment_datetime`);
ALTER TABLE `tbl_task_comment` ADD INDEX `idx_mod_field_id` (`module`, `module_field_id`, `task_comment_id`);

-- 4. tbl_notifications indexes
ALTER TABLE `tbl_notifications` ADD INDEX `idx_user_read` (`to_user_id`, `read`);
ALTER TABLE `tbl_notifications` ADD INDEX `idx_user_read_inline` (`to_user_id`, `read_inline`);

-- 5. tbl_account_details indexes
ALTER TABLE `tbl_account_details` ADD INDEX `idx_user_id` (`user_id`);
ALTER TABLE `tbl_account_details` ADD INDEX `idx_desig_id` (`designations_id`);

-- 6. tbl_activities indexes
ALTER TABLE `tbl_activities` ADD INDEX `idx_mod_field` (`module`, `module_field_id`);
ALTER TABLE `tbl_activities` ADD INDEX `idx_user_date` (`user`, `activity_date`);

-- 7. tbl_client indexes
ALTER TABLE `tbl_client` ADD INDEX `idx_client_leads_id` (`leads_id`);
ALTER TABLE `tbl_client` ADD INDEX `idx_client_primary_contact` (`primary_contact`);
ALTER TABLE `tbl_client` ADD INDEX `idx_client_customer_group_id` (`customer_group_id`);
ALTER TABLE `tbl_client` ADD INDEX `idx_client_operation_status` (`operation_status`);
ALTER TABLE `tbl_client` ADD INDEX `idx_client_date_added` (`date_added`);
ALTER TABLE `tbl_client` ADD INDEX `idx_client_hand_over` (`hand_over_request_date`);
ALTER TABLE `tbl_client` ADD INDEX `idx_client_go_ahead` (`go_ahead`);

-- 8. tbl_client_assign_team indexes
ALTER TABLE `tbl_client_assign_team` ADD INDEX `idx_client_assign_client_user` (`client_id`, `user_id`);
ALTER TABLE `tbl_client_assign_team` ADD INDEX `idx_client_assign_user` (`user_id`);
ALTER TABLE `tbl_client_assign_team` ADD INDEX `idx_client_assign_profile` (`member_profile`);

-- 9. proposals & floor categories indexes
ALTER TABLE `proposals` ADD INDEX `idx_proposals_lead_id` (`lead_id`);
ALTER TABLE `proposal_floor_categories` ADD INDEX `idx_pfc_proposal_id` (`proposal_id`);

-- 10. tbl_client_payment_record & timeline indexes
ALTER TABLE `tbl_client_payment_record` ADD INDEX `idx_cpr_client_id` (`client_id`);
ALTER TABLE `timeline_manual_dates` ADD INDEX `idx_tmd_client_id` (`client_id`);

-- 11. tbl_client_service_request indexes
ALTER TABLE `tbl_client_service_request` ADD INDEX `idx_csr_client_req` (`client_id`, `request_type`);
ALTER TABLE `tbl_client_service_request` ADD INDEX `idx_csr_req_date` (`request_type`, `request_date`);

-- 12. tbl_client_escalation, 2d design & validation files
ALTER TABLE `tbl_client_escalation` ADD INDEX `idx_ce_client_id` (`client_id`);
ALTER TABLE `tbl_client_escalation` ADD INDEX `idx_ce_status` (`status`);
ALTER TABLE `tbl_client_two_d_design` ADD INDEX `idx_ctdd_client_design` (`client_id`, `design_type`, `is_final`, `status`);
ALTER TABLE `tbl_client_validation_files` ADD INDEX `idx_cvf_client_id` (`client_id`);
