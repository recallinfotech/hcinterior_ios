<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $m->query("SHOW COLUMNS FROM `proposals`");
echo "--- proposals columns ---\n";
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
