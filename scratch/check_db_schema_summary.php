<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

echo "=== tbl_clock columns related to logout / geo ===\n";
$r = $m->query("SHOW COLUMNS FROM tbl_clock LIKE '%clock%'");
while($row = $r->fetch_assoc()) echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";

echo "\n=== tbl_users columns related to office & logout ===\n";
$r = $m->query("SHOW COLUMNS FROM tbl_users WHERE Field LIKE '%office%' OR Field LIKE '%logout%' OR Field LIKE '%manual%'");
while($row = $r->fetch_assoc()) echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";

echo "\n=== tbl_account_details columns related to office ===\n";
$r = $m->query("SHOW COLUMNS FROM tbl_account_details WHERE Field LIKE '%office%'");
while($row = $r->fetch_assoc()) echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";

echo "\n=== tbl_office_locations structure ===\n";
$r = $m->query("SHOW COLUMNS FROM tbl_office_locations");
while($row = $r->fetch_assoc()) echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
