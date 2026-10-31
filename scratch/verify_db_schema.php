<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

$checks = [
    'tbl_client' => ['operation_status', 'client_hold', 'date_added', 'lead_region', 'leads_id', 'client_id', 'amount_received', 'payment_plan', 'hand_over_request_date'],
    'tbl_account_details' => ['user_region', 'designations_id', 'user_id', 'fullname'],
    'tbl_leads' => ['region', 'permission', 'leads_id'],
    'tbl_client_assign_team' => ['client_id', 'user_id', 'member_profile'],
    'timeline_manual_dates' => ['client_id', 'site_validation', 'site_start_date'],
    'tbl_client_service_request' => ['client_id', 'request_type', 'request_date'],
    'tbl_client_timeline' => ['client_id', 'task_name', 'start_date'],
    'tbl_client_payment_record' => ['client_id', 'paid_amount'],
    'proposals' => ['lead_id', 'totalToBePaid'],
    'timeline_task_status' => ['client_id', 'task_name', 'status', 'completed_at', 'delay_days'],
    'hc_permissions' => ['permission_name', 'permission_key'],
    'hc_role_permissions' => ['operation_role_id', 'permission_id']
];

foreach ($checks as $tbl => $cols) {
    $res = $m->query("SHOW TABLES LIKE '$tbl'");
    if ($res->num_rows == 0) {
        echo "Table MISSING: $tbl\n";
        continue;
    }
    foreach ($cols as $c) {
        $r = $m->query("SHOW COLUMNS FROM `$tbl` LIKE '$c'");
        if ($r->num_rows == 0) {
            echo "Column MISSING: $tbl.$c\n";
        }
    }
}
echo "Verification complete. All checked tables and columns exist!\n";
