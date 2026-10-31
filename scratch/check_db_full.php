<?php
// Check all possible MySQL/MariaDB ports and configurations
$ports = [3306, 3307, 3308, 3309];
foreach ($ports as $port) {
    echo "=== Testing Port $port ===\n";
    $conn = @new mysqli('127.0.0.1', 'root', '', '', $port);
    if ($conn->connect_error) {
        echo "Failed: " . $conn->connect_error . "\n";
        continue;
    }
    echo "Connected successfully to port $port!\n";
    $res = $conn->query("SHOW DATABASES");
    if ($res) {
        while ($row = $res->fetch_row()) {
            echo "  Database: " . $row[0] . "\n";
        }
    }
    $conn->close();
}
