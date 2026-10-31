<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("
    SELECT r.role_name, rp.operation_role_id, p.permission_key 
    FROM hc_role_permissions rp
    JOIN hc_roles r ON r.id = rp.operation_role_id
    JOIN hc_permissions p ON p.id = rp.permission_id
    WHERE p.permission_key IN ('all_client_list', 'region_wise_client', 'only_assigned_client')
");
while ($row = $r->fetch_assoc()) {
    echo "Role " . $row['operation_role_id'] . " (" . $row['role_name'] . "): " . $row['permission_key'] . "\n";
}
