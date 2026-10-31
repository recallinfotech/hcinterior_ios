<?php
/**
 * Standalone Debug Script for Live Server
 * Place this in CRM root (crm.hcinterior.in/debug_client_visibility.php)
 * Access via browser: https://crm.hcinterior.in/debug_client_visibility.php?username=Shreyashi_Kishor_HC3042
 */

$username = $_GET['username'] ?? 'Shreyashi_Kishor_HC3042';

// DB Connection (reads from CodeIgniter database.php if present, or configure manually)
$db_file = __DIR__ . '/application/config/database.php';
$hostname = 'localhost';
$dbuser   = 'root';
$dbpass   = '';
$dbname   = 'hcinterior_git';

if (file_exists($db_file)) {
    include($db_file);
    if (isset($db['default'])) {
        $hostname = $db['default']['hostname'];
        $dbuser   = $db['default']['username'];
        $dbpass   = $db['default']['password'];
        $dbname   = $db['default']['database'];
    }
}

$conn = new mysqli($hostname, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

header('Content-Type: text/plain; charset=utf-8');
echo "====================================================\n";
echo "   CLIENT VISIBILITY DIAGNOSTIC FOR: $username\n";
echo "====================================================\n\n";

// 1. User Record
$u_res = $conn->query("SELECT * FROM tbl_users WHERE username = '" . $conn->real_escape_string($username) . "'");
$user = $u_res->fetch_assoc();

if (!$user) {
    die("ERROR: User '$username' not found in tbl_users!\n");
}

$user_id           = (int)$user['user_id'];
$role_id           = (int)$user['role_id'];
$operation_role_id = !empty($user['operation_role_id']) ? (int)$user['operation_role_id'] : null;

echo "1. USER INFO (tbl_users):\n";
echo "   - user_id:           $user_id\n";
echo "   - role_id:           $role_id " . ($role_id == 1 ? "==> [CRITICAL: role_id 1 is ADMIN! Shows ALL clients]" : "(Staff/Non-admin)") . "\n";
echo "   - operation_role_id: " . ($operation_role_id ?? 'NULL') . "\n";
echo "   - activated:         " . $user['activated'] . "\n";
echo "   - banned:            " . $user['banned'] . "\n\n";

// 2. Account Details
$acc_res = $conn->query("SELECT user_id, fullname, designations_id, user_region FROM tbl_account_details WHERE user_id = $user_id");
$acc = $acc_res->fetch_assoc();
$designations_id = (int)($acc['designations_id'] ?? 0);
$user_region     = $acc['user_region'] ?? '';

echo "2. ACCOUNT DETAILS (tbl_account_details):\n";
echo "   - fullname:        " . ($acc['fullname'] ?? '-') . "\n";
echo "   - designations_id: $designations_id\n";
echo "   - user_region:     $user_region\n";

$all_client_designations = [45, 39, 76, 71, 72, 77, 128, 150];
if (in_array($designations_id, $all_client_designations, true)) {
    echo "   ==> [WARNING: designations_id $designations_id is in legacy all_client_designations bypass list!]\n";
} else {
    echo "   ==> [OK: designations_id $designations_id is NOT in legacy all_client_designations bypass list]\n";
}
echo "\n";

// 3. Permissions Check (hc_role_permissions)
echo "3. OPERATION ROLE PERMISSIONS:\n";
if ($operation_role_id) {
    $r_name_res = $conn->query("SELECT role_name FROM hc_roles WHERE id = $operation_role_id");
    $r_name = ($r_name_res && $r = $r_name_res->fetch_assoc()) ? $r['role_name'] : 'Unknown';
    echo "   - Role Name: $r_name (ID: $operation_role_id)\n";

    $p_res = $conn->query("
        SELECT p.permission_key, p.permission_name 
        FROM hc_role_permissions rp
        JOIN hc_permissions p ON p.id = rp.permission_id
        WHERE rp.operation_role_id = $operation_role_id
    ");

    $active_perms = [];
    while ($p = $p_res->fetch_assoc()) {
        $active_perms[] = $p['permission_key'];
        echo "     * " . $p['permission_key'] . " (" . $p['permission_name'] . ")\n";
    }

    $has_all_client_perm    = in_array('all_client_list', $active_perms, true);
    $has_region_client_perm = in_array('region_wise_client', $active_perms, true);
    $has_only_assigned_perm = in_array('only_assigned_client', $active_perms, true);

    echo "\n   Summary of Client Visibility Permissions:\n";
    echo "     - all_client_list:    " . ($has_all_client_perm ? "[TRUE - OVERRIDES ALL AND SHOWS ALL CLIENTS!]" : "FALSE") . "\n";
    echo "     - region_wise_client: " . ($has_region_client_perm ? "TRUE" : "FALSE") . "\n";
    echo "     - only_assigned_client: " . ($has_only_assigned_perm ? "TRUE" : "FALSE") . "\n";
} else {
    echo "   [WARNING: operation_role_id is NULL or 0 in tbl_users!]\n";
    $has_all_client_perm    = false;
    $has_region_client_perm = false;
    $has_only_assigned_perm = false;
}
echo "\n";

// 4. Assigned Clients in tbl_client_assign_team
echo "4. ASSIGNED CLIENTS (tbl_client_assign_team):\n";
$assign_res = $conn->query("SELECT client_id, member_profile FROM tbl_client_assign_team WHERE user_id = $user_id");
$assigned_cids = [];
while ($a = $assign_res->fetch_assoc()) {
    $assigned_cids[] = (int)$a['client_id'];
    echo "   - Client ID: " . $a['client_id'] . " | Profile: " . $a['member_profile'] . "\n";
}
$assigned_cids = array_values(array_unique(array_filter($assigned_cids)));
echo "   Total unique assigned clients: " . count($assigned_cids) . " [" . implode(', ', $assigned_cids) . "]\n\n";

// 5. Permitted Leads
$perm_leads_res = $conn->query("SELECT leads_id FROM tbl_leads WHERE permission LIKE '%\"$user_id\"%'");
$perm_leads_ids = [];
if ($perm_leads_res) {
    while ($l = $perm_leads_res->fetch_assoc()) {
        $perm_leads_ids[] = (int)$l['leads_id'];
    }
}
echo "5. PERMITTED LEADS: " . count($perm_leads_ids) . "\n\n";

// 6. Branch Decision in Client.php clientList()
echo "6. BRANCH DECISION IN Client.php clientList():\n";
if ($role_id == 1 || $has_all_client_perm) {
    echo "   ==> BRANCH 1: Admin or all_client_list\n";
    echo "       RESULT: NO FILTER APPLIED -> USER WILL SEE ALL 574+ CLIENTS!\n";
    echo "       FIX: If user is designer, ensure role_id != 1 and all_client_list is unchecked in Operation Permissions!\n";
} elseif ($has_region_client_perm) {
    echo "   ==> BRANCH 2: region_wise_client\n";
    echo "       RESULT: Filtered by region '$user_region'\n";
} elseif ($has_only_assigned_perm || !in_array($designations_id, $all_client_designations, true)) {
    echo "   ==> BRANCH 3: only_assigned_client (or designation restriction)\n";
    echo "       RESULT: Strictly filtered to assigned clients: [" . implode(', ', $assigned_cids) . "]\n";
    echo "       Total clients that should be returned: " . count($assigned_cids) . "\n";
} else {
    echo "   ==> BRANCH 4: Legacy bypass list\n";
    echo "       RESULT: NO FILTER APPLIED -> USER WILL SEE ALL CLIENTS!\n";
}

echo "\n====================================================\n";
