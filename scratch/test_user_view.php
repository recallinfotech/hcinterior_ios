<?php
// Simulate user 92 environment
$user_id = 92;
$user_type = 3;
$designation_id = 48;

$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

// 1. Check check_permission('all_client_list', 92)
$r1 = $m->query("SELECT rp.id FROM hc_role_permissions rp JOIN hc_permissions p ON p.id = rp.permission_id JOIN tbl_users u ON u.operation_role_id = rp.operation_role_id WHERE u.user_id = 92 AND p.permission_key = 'all_client_list'");
$has_all_client_perm = ($r1->num_rows > 0);

$r2 = $m->query("SELECT rp.id FROM hc_role_permissions rp JOIN hc_permissions p ON p.id = rp.permission_id JOIN tbl_users u ON u.operation_role_id = rp.operation_role_id WHERE u.user_id = 92 AND p.permission_key = 'region_wise_client'");
$has_region_client_perm = ($r2->num_rows > 0);

$r3 = $m->query("SELECT rp.id FROM hc_role_permissions rp JOIN hc_permissions p ON p.id = rp.permission_id JOIN tbl_users u ON u.operation_role_id = rp.operation_role_id WHERE u.user_id = 92 AND p.permission_key = 'only_assigned_client'");
$has_only_assigned_perm = ($r3->num_rows > 0);

echo "has_all_client_perm: " . ($has_all_client_perm ? 'true' : 'false') . "\n";
echo "has_region_client_perm: " . ($has_region_client_perm ? 'true' : 'false') . "\n";
echo "has_only_assigned_perm: " . ($has_only_assigned_perm ? 'true' : 'false') . "\n";

// Now check main_content.php logic (Dashboard)
$is_admin = ($user_type == 1);
$all_client_designations = [45, 39, 76, 71, 72, 77, 128, 150];

$can_view_all_clients = (
    $is_admin ||
    $has_all_client_perm ||
    (!$has_region_client_perm && !$has_only_assigned_perm && in_array((int)$designation_id, $all_client_designations, true))
);
echo "Dashboard can_view_all_clients: " . ($can_view_all_clients ? 'true' : 'false') . "\n";

// In main_content.php, if not can_view_all_clients:
$assign_query = $m->query("SELECT client_id FROM tbl_client_assign_team WHERE user_id = 92");
$assigned_cids = [];
while ($row = $assign_query->fetch_assoc()) {
    $assigned_cids[] = $row['client_id'];
}
echo "Assigned cids: " . implode(',', $assigned_cids) . "\n";

// Now check client_list in Client.php:
echo "--- Client.php client_list logic ---\n";
echo "Branch taken: ";
if ($user_type == 1 || $has_all_client_perm) {
    echo "1. Admin or all_client_list (ALL CLIENTS!)\n";
} elseif ($has_region_client_perm) {
    echo "2. Region wise\n";
} elseif ($has_only_assigned_perm || !in_array($designation_id, [45, 39, 76, 71, 72, 77, 128, 150])) {
    echo "3. Only assigned\n";
} else {
    echo "4. Legacy bypass (ALL CLIENTS!)\n";
}
