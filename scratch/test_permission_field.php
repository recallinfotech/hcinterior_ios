<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("SHOW COLUMNS FROM tbl_client LIKE 'permission'");
echo "tbl_client permission column exists? " . ($r->num_rows > 0 ? "YES" : "NO") . "\n";
if ($r->num_rows > 0) {
    print_r($r->fetch_assoc());
    $r2 = $m->query("SELECT permission, COUNT(*) as cnt FROM tbl_client GROUP BY permission");
    while ($row = $r2->fetch_assoc()) {
        echo "permission value: [" . $row['permission'] . "] => count: " . $row['cnt'] . "\n";
    }
}
