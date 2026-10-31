<?php
define('BASEPATH', 1);
define('ENVIRONMENT', 'production');

header('Content-Type: text/plain; charset=utf-8');

include(__DIR__ . '/application/config/database.php');
$conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}

$username = $_GET['username'] ?? 'Shreyashi_Kishor_HC3042';
$u = $conn->query("SELECT * FROM tbl_users WHERE username = '" . $conn->real_escape_string($username) . "'")->fetch_assoc();

if (!$u) {
    die("User '$username' not found in tbl_users!\n");
}

echo "====================================================\n";
echo "   LIVE USER & PERMISSION DIAGNOSTIC\n";
echo "====================================================\n\n";

$user_id = (int)$u['user_id'];
$role_id = (int)$u['role_id'];
$op_role_id = !empty($u['operation_role_id']) ? (int)$u['operation_role_id'] : null;

echo "1. USER INFO:\n";
echo "   - Username:          " . $u['username'] . "\n";
echo "   - User ID:           $user_id\n";
echo "   - Role ID:           $role_id " . ($role_id == 1 ? "==> [CRITICAL: role_id 1 is ADMIN! Skips all filters!]" : "(Staff)") . "\n";
echo "   - Operation Role ID: " . ($op_role_id ?? 'NULL') . "\n";

$acc = $conn->query("SELECT * FROM tbl_account_details WHERE user_id = $user_id")->fetch_assoc();
$desig_id = (int)($acc['designations_id'] ?? 0);
echo "   - Fullname:          " . ($acc['fullname'] ?? '-') . "\n";
echo "   - Designation ID:    $desig_id\n";

$bypass_list = [45, 39, 76, 71, 72, 77, 128, 150];
if (in_array($desig_id, $bypass_list, true)) {
    echo "   ==> [ALERT: Designation ID $desig_id is in bypass list [45,39,76,71,72,77,128,150]!]\n";
}

echo "\n2. OPERATION ROLE & PERMISSIONS:\n";
$has_all_client_perm = false;
$has_only_assigned_perm = false;
$has_region_client_perm = false;

if ($op_role_id) {
    $r_row = $conn->query("SELECT role_name FROM hc_roles WHERE id = $op_role_id")->fetch_assoc();
    echo "   - Role Name: " . ($r_row['role_name'] ?? 'Unknown') . " (ID: $op_role_id)\n";
    echo "   - Active Permissions in hc_role_permissions:\n";
    $p = $conn->query("
        SELECT p.permission_key, p.permission_name 
        FROM hc_role_permissions rp 
        JOIN hc_permissions p ON p.id = rp.permission_id 
        WHERE rp.operation_role_id = $op_role_id
    ");
    while ($row = $p->fetch_assoc()) {
        echo "     * " . $row['permission_key'] . " (" . $row['permission_name'] . ")\n";
        if ($row['permission_key'] == 'all_client_list') $has_all_client_perm = true;
        if ($row['permission_key'] == 'only_assigned_client') $has_only_assigned_perm = true;
        if ($row['permission_key'] == 'region_wise_client') $has_region_client_perm = true;
    }
} else {
    echo "   [ALERT: operation_role_id is NULL or 0 in tbl_users!]\n";
}

echo "\n   Key Permissions Summary:\n";
echo "   - all_client_list:    " . ($has_all_client_perm ? "[TRUE - THIS SHOWS ALL CLIENTS!]" : "FALSE") . "\n";
echo "   - only_assigned_client: " . ($has_only_assigned_perm ? "TRUE" : "FALSE") . "\n";
echo "   - region_wise_client: " . ($has_region_client_perm ? "TRUE" : "FALSE") . "\n";

echo "\n3. ASSIGNED CLIENTS IN tbl_client_assign_team:\n";
$ac = $conn->query("SELECT client_id, member_profile FROM tbl_client_assign_team WHERE user_id = $user_id");
$assigned_cids = [];
while ($row = $ac->fetch_assoc()) {
    $assigned_cids[] = (int)$row['client_id'];
    echo "   - Client ID: " . $row['client_id'] . " (" . $row['member_profile'] . ")\n";
}
$assigned_cids = array_values(array_unique(array_filter($assigned_cids)));
echo "   Total unique assigned clients: " . count($assigned_cids) . " [" . implode(', ', $assigned_cids) . "]\n";

echo "\n4. DECISION BRANCH IN Client.php:\n";
if ($role_id == 1 || $has_all_client_perm) {
    echo "   ==> BRANCH 1 (All Clients): User sees ALL clients without any filter!\n";
    echo "       REASON: role_id = 1 or all_client_list is checked in hc_role_permissions.\n";
} elseif ($has_region_client_perm) {
    echo "   ==> BRANCH 2 (Region Wise): User sees only their region clients.\n";
} elseif ($has_only_assigned_perm || !in_array($desig_id, $bypass_list, true)) {
    echo "   ==> BRANCH 3 (Only Assigned): Strictly restricted to assigned clients: [" . implode(', ', $assigned_cids) . "]\n";
} else {
    echo "   ==> BRANCH 4 (Legacy Bypass): User sees ALL clients!\n";
}

echo "\n5. LIVE Client.php CODE INSPECTION:\n";
$client_file = __DIR__ . '/application/controllers/admin/Client.php';
if (!file_exists($client_file)) {
    echo "   [ERROR: application/controllers/admin/Client.php not found!]\n";
} else {
    $c_code = file_get_contents($client_file);
    echo "   - File size: " . strlen($c_code) . " bytes\n";
    echo "   - Last modified: " . date('Y-m-d H:i:s', filemtime($client_file)) . "\n";
    $has_base_custom = (strpos($c_code, '$base_custom_where') !== false);
    $has_where_raw = (strpos($c_code, 'where_raw') !== false);
    echo "   - Has '$base_custom_where': " . ($has_base_custom ? "YES (NEW CODE DEPLOYED)" : "NO (OLD CODE RUNNING ON LIVE!)") . "\n";
    echo "   - Has 'where_raw':          " . ($has_where_raw ? "YES (NEW CODE DEPLOYED)" : "NO (OLD CODE RUNNING ON LIVE!)") . "\n";
    if (!$has_base_custom || !$has_where_raw) {
        echo "\n   ===> CRITICAL ROOT CAUSE FOUND:\n";
        echo "        Live server is running the OLD Client.php file!\n";
        echo "        In the old file, assigned clients were NOT put into \$custom_where,\n";
        echo "        so DataTables wiped the filter and displayed all 600+ clients!\n";
        echo "        Admin filter worked only because dropdown filter injected into \$custom_where.\n";
        echo "        ACTION: Upload local Client.php to live server!\n";
    } else {
        echo "\n   ===> New Client.php code is present.\n";
    }
}

echo "\n6. LIVE GEOFENCE & MOBILE API INSPECTION:\n";
$geo_res = $conn->query("SELECT config_key, value FROM tbl_config WHERE config_key IN ('office_location','office_lat','office_long','allowed_radius','allow_geo_clock_in')");
$geo_vals = [];
while ($gr = $geo_res->fetch_assoc()) {
    $geo_vals[$gr['config_key']] = $gr['value'];
}
echo "   - office_location:    " . ($geo_vals['office_location'] ?? 'NOT SET (default: HC Interior Head Office)') . "\n";
echo "   - office_lat:         " . ($geo_vals['office_lat'] ?? 'NOT SET (default: 28.535517)') . "\n";
echo "   - office_long:        " . ($geo_vals['office_long'] ?? 'NOT SET (default: 77.391029)') . "\n";
echo "   - allowed_radius:     " . ($geo_vals['allowed_radius'] ?? 'NOT SET (default: 250m)') . "\n";
echo "   - allow_geo_clock_in: " . ($geo_vals['allow_geo_clock_in'] ?? 'NOT SET') . "\n";

$mob_client_file = __DIR__ . '/application/controllers/mobileapi/Client.php';
if (!file_exists($mob_client_file)) {
    echo "   [ERROR: application/controllers/mobileapi/Client.php not found!]\n";
} else {
    $m_code = file_get_contents($mob_client_file);
    $has_dyn_geo = (strpos($m_code, "config_item('office_lat')") !== false);
    echo "   - mobileapi/Client.php uses dynamic config_item('office_lat'): " . ($has_dyn_geo ? "YES (DYNAMIC)" : "NO (HARDCODED NOIDA 28.535517, 77.391029!)") . "\n";
    if (!$has_dyn_geo) {
        echo "     ===> REASON FOR 166 KM: Live mobileapi/Client.php is STILL HARDCODED to Noida (28.535517, 77.391029)!\n";
        echo "          Even if you updated lat/long in tbl_config, the mobile app receives Noida coordinates from the old file!\n";
        echo "          ACTION: Upload application/controllers/mobileapi/Client.php to live server!\n";
    }
}

echo "\n====================================================\n";
