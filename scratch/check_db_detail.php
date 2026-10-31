<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
echo "=== tbl_office_locations ===\n";
$r = $m->query("SELECT * FROM tbl_office_locations");
while($row = $r->fetch_assoc()) print_r($row);

echo "=== tbl_users for Abhishek or 87 ===\n";
$r2 = $m->query("SELECT user_id, username, office_location_id, activated FROM tbl_users WHERE user_id = 87 OR username LIKE '%abhishek%'");
while($row = $r2->fetch_assoc()) print_r($row);

echo "=== tbl_config office keys ===\n";
$r3 = $m->query("SELECT * FROM tbl_config WHERE config_key LIKE '%office%'");
while($row = $r3->fetch_assoc()) print_r($row);
