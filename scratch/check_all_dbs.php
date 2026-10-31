<?php
$dbs = ['hcinterior_git', 'hcinterior', 'hcinterior_2025', 'hcinterior_crm', 'hcinterior_latest', 'crm', 'crm_backup'];
foreach ($dbs as $db) {
    $m = @new mysqli('localhost', 'root', '', $db);
    if ($m->connect_error) continue;
    
    // check user 87
    $res = $m->query("SELECT user_id, username, office_location_id FROM tbl_users WHERE user_id = 87");
    if ($res && $row = $res->fetch_assoc()) {
        echo "DB: $db -> User 87: office_location_id = " . var_export($row['office_location_id'], true) . "\n";
    }
    // check offices
    $res2 = $m->query("SELECT id, location_name, is_default FROM tbl_office_locations");
    if ($res2) {
        echo "   Offices in $db:\n";
        while ($o = $res2->fetch_assoc()) {
            echo "     id {$o['id']}: {$o['location_name']} (default: {$o['is_default']})\n";
        }
    }
}
