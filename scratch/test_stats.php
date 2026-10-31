<?php
$_SERVER['HTTP_HOST'] = 'localhost';
define('FCPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/');
define('BASEPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/system/');
define('APPPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/');
define('VIEWPATH', APPPATH . 'views/');
define('MODULES_PATH', APPPATH . 'modules/');
define('ENVIRONMENT', 'development');

require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'database/DB.php';

$db = DB('default', TRUE);

$user_id = 92;
$user_type = 3;
$designation_id = 48;

$has_all_client_perm = false;
$has_region_client_perm = false;
$has_only_assigned_perm = true;

$assigned_client_ids = [];
$res = $db->select('client_id')->where('user_id', $user_id)->get('tbl_client_assign_team')->result();
foreach ($res as $row) {
    $assigned_client_ids[] = (int)$row->client_id;
}
$assigned_client_ids = array_unique($assigned_client_ids);

$perm_leads = $db->select('leads_id')->like('permission', '"' . $user_id . '"')->get('tbl_leads')->result_array();
$permission_lead_ids = !empty($perm_leads) ? array_column($perm_leads, 'leads_id') : [];

$db->select("
    COUNT(*) as total_clients,
    SUM(CASE WHEN (operation_status IS NULL OR operation_status = '' OR operation_status = 'Unhold' OR operation_status = 'Active') AND (client_hold IS NULL OR client_hold = 0) THEN 1 ELSE 0 END) as active_clients,
    SUM(CASE WHEN (operation_status = 'Hold' OR client_hold = 1) THEN 1 ELSE 0 END) as hold_clients,
    SUM(CASE WHEN (operation_status IN ('Completed', 'Closed')) THEN 1 ELSE 0 END) as closed_clients,
    SUM(CASE WHEN DATE(date_added) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_clients,
    SUM(CASE WHEN YEAR(date_added) = YEAR(CURDATE()) AND MONTH(date_added) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as month_clients,
    SUM(CASE WHEN DATE(date_added) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as last_30_days_clients
");
$db->from('tbl_client');

$db->group_start();
$has_cond = false;
if (!empty($assigned_client_ids)) {
    $db->where_in('tbl_client.client_id', $assigned_client_ids);
    $has_cond = true;
}
if (!empty($permission_lead_ids)) {
    if ($has_cond) {
        $db->or_where_in('tbl_client.leads_id', $permission_lead_ids);
    } else {
        $db->where_in('tbl_client.leads_id', $permission_lead_ids);
        $has_cond = true;
    }
}
if (!$has_cond) {
    $db->where('tbl_client.client_id', 0);
}
$db->group_end();

echo "SQL: " . $db->get_compiled_select(null, false) . "\n";
$row = $db->get()->row_array();
print_r($row);
