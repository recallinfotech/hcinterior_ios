<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("
    SELECT rp.*, p.permission_key, p.permission_name 
    FROM hc_role_permissions rp 
    JOIN hc_permissions p ON p.id = rp.permission_id 
    WHERE rp.operation_role_id = 4
");
while ($row = $r->fetch_assoc()) {
    echo $row['permission_key'] . " (" . $row['permission_name'] . ")\n";
}
