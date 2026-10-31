<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("
    SELECT rp.*, r.role_name, p.permission_key 
    FROM hc_role_permissions rp
    JOIN hc_roles r ON r.id = rp.operation_role_id
    JOIN hc_permissions p ON p.id = rp.permission_id
    WHERE p.permission_key IN ('client_dashboard_kpi', 'client_filters')
");
while ($row = $r->fetch_assoc()) {
    echo "Role " . $row['operation_role_id'] . " (" . $row['role_name'] . "): " . $row['permission_key'] . "\n";
}
