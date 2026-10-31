<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("SELECT designations_id, designations FROM tbl_designations WHERE designations_id IN (45, 39, 76, 71, 72, 77, 128, 150, 48)");
while ($row = $r->fetch_assoc()) {
    echo $row['designations_id'] . " => " . $row['designations'] . "\n";
}
