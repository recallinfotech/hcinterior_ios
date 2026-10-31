<?php
// Try port 3306, 3307, 3308
foreach ([3306, 3307, 3308] as $port) {
    echo "--- Checking Port $port ---\n";
    $m = @new mysqli('127.0.0.1', 'root', '', '', $port);
    if ($m->connect_error) {
        echo "Error on port $port: " . $m->connect_error . "\n";
        continue;
    }
    echo "Connected to port $port! Databases:\n";
    $res = $m->query("SHOW DATABASES");
    while ($r = $res->fetch_row()) {
        echo " - " . $r[0] . "\n";
    }
}
