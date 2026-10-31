<?php
define('BASEPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/system/');
define('APPPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/');
define('VIEWPATH', APPPATH . 'views/');
define('ENVIRONMENT', 'development');

require_once BASEPATH . 'core/Common.php';

// Let's inspect the query directly
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

$user_id = 92;
$assign_team = $m->query("SELECT client_id FROM tbl_client_assign_team WHERE user_id = $user_id");
$assigned_client_ids = [];
while ($row = $assign_team->fetch_assoc()) {
    $assigned_client_ids[] = $row['client_id'];
}

$perm_leads = $m->query("SELECT leads_id FROM tbl_leads WHERE permission LIKE '%\"$user_id\"%'");
$permission_lead_ids = [];
while ($row = $perm_leads->fetch_assoc()) {
    $permission_lead_ids[] = $row['leads_id'];
}

print_r([
    'assigned_client_ids' => $assigned_client_ids,
    'permission_lead_ids' => $permission_lead_ids
]);
