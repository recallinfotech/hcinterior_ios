<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("SELECT * FROM hc_permissions ORDER BY id ASC");
while ($row = $r->fetch_assoc()) {
    echo $row['id'] . " | " . $row['permission_key'] . " | " . $row['permission_name'] . "\n";
}
