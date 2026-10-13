<?php
// Migration script for adding high performance indexes to hcinterior_crm
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_crm');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

function add_index_if_not_exists($mysqli, $table, $index_name, $columns) {
    // Check if index already exists
    $check = $mysqli->query("SHOW INDEX FROM `$table` WHERE Key_name = '$index_name'");
    if ($check && $check->num_rows > 0) {
        echo "[EXISTS] Index `$index_name` already exists on `$table`.\n";
        return;
    }
    
    $sql = "ALTER TABLE `$table` ADD INDEX `$index_name` ($columns)";
    $start = microtime(true);
    if ($mysqli->query($sql)) {
        $time = number_format((microtime(true) - $start) * 1000, 2);
        echo "[CREATED] Index `$index_name` on `$table` ($columns) added in {$time}ms.\n";
    } else {
        echo "[ERROR] Failed to add index `$index_name` on `$table`: " . $mysqli->error . "\n";
    }
}

echo "=== STARTING DATABASE INDEX OPTIMIZATION ===\n";

// 1. tbl_leads indexes
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_conv_created', '`converted_client_id`, `created_time`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_lead_status', '`lead_status_id`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_lead_source', '`lead_source_id`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_phone', '`phone`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_budget', '`budget_from`, `budget_to`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_assign_date', '`lead_assign_date`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_sub_status', '`sub_status`');
add_index_if_not_exists($mysqli, 'tbl_leads', 'idx_is_duplicate', '`is_duplicate`');

// 2. tbl_status_log indexes
add_index_if_not_exists($mysqli, 'tbl_status_log', 'idx_status_lead_date', '`status`, `lead_id`, `createdate`');
add_index_if_not_exists($mysqli, 'tbl_status_log', 'idx_lead_id', '`lead_id`');
add_index_if_not_exists($mysqli, 'tbl_status_log', 'idx_update_by_date', '`update_by`, `createdate`');

// 3. tbl_task_comment indexes
add_index_if_not_exists($mysqli, 'tbl_task_comment', 'idx_mod_field_date', '`module`, `module_field_id`, `comment_datetime`');
add_index_if_not_exists($mysqli, 'tbl_task_comment', 'idx_mod_field_id', '`module`, `module_field_id`, `task_comment_id`');

// 4. tbl_notifications indexes
add_index_if_not_exists($mysqli, 'tbl_notifications', 'idx_user_read', '`to_user_id`, `read`');
add_index_if_not_exists($mysqli, 'tbl_notifications', 'idx_user_read_inline', '`to_user_id`, `read_inline`');

// 5. tbl_account_details indexes
add_index_if_not_exists($mysqli, 'tbl_account_details', 'idx_user_id', '`user_id`');
add_index_if_not_exists($mysqli, 'tbl_account_details', 'idx_desig_id', '`designations_id`');

// 6. tbl_activities indexes
add_index_if_not_exists($mysqli, 'tbl_activities', 'idx_mod_field', '`module`, `module_field_id`');
add_index_if_not_exists($mysqli, 'tbl_activities', 'idx_user_date', '`user`, `activity_date`');

// 7. tbl_client indexes
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_leads_id', '`leads_id`');
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_primary_contact', '`primary_contact`');
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_customer_group_id', '`customer_group_id`');
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_operation_status', '`operation_status`');
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_date_added', '`date_added`');
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_hand_over', '`hand_over_request_date`');
add_index_if_not_exists($mysqli, 'tbl_client', 'idx_client_go_ahead', '`go_ahead`');

// 8. tbl_client_assign_team indexes
add_index_if_not_exists($mysqli, 'tbl_client_assign_team', 'idx_client_assign_client_user', '`client_id`, `user_id`');
add_index_if_not_exists($mysqli, 'tbl_client_assign_team', 'idx_client_assign_user', '`user_id`');
add_index_if_not_exists($mysqli, 'tbl_client_assign_team', 'idx_client_assign_profile', '`member_profile`');

// 9. proposals & floor categories indexes
add_index_if_not_exists($mysqli, 'proposals', 'idx_proposals_lead_id', '`lead_id`');
add_index_if_not_exists($mysqli, 'proposal_floor_categories', 'idx_pfc_proposal_id', '`proposal_id`');

// 10. tbl_client_payment_record & timeline indexes
add_index_if_not_exists($mysqli, 'tbl_client_payment_record', 'idx_cpr_client_id', '`client_id`');
add_index_if_not_exists($mysqli, 'timeline_manual_dates', 'idx_tmd_client_id', '`client_id`');

// 11. tbl_client_service_request indexes
add_index_if_not_exists($mysqli, 'tbl_client_service_request', 'idx_csr_client_req', '`client_id`, `request_type`');
add_index_if_not_exists($mysqli, 'tbl_client_service_request', 'idx_csr_req_date', '`request_type`, `request_date`');

// 12. tbl_client_escalation, 2d design & validation files
add_index_if_not_exists($mysqli, 'tbl_client_escalation', 'idx_ce_client_id', '`client_id`');
add_index_if_not_exists($mysqli, 'tbl_client_escalation', 'idx_ce_status', '`status`');
add_index_if_not_exists($mysqli, 'tbl_client_two_d_design', 'idx_ctdd_client_design', '`client_id`, `design_type`, `is_final`, `status`');
add_index_if_not_exists($mysqli, 'tbl_client_validation_files', 'idx_cvf_client_id', '`client_id`');

echo "=== DATABASE INDEX OPTIMIZATION COMPLETE ===\n";
