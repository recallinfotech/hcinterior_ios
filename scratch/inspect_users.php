<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

echo "=== tbl_users ===\n";
$res = $conn->query("DESCRIBE tbl_users");
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . ' (' . $r['Type'] . ")\n";
}
