<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

echo "=== attendance_logs ===\n";
$res = $conn->query("DESCRIBE attendance_logs");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo $r['Field'] . ' (' . $r['Type'] . ")\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
