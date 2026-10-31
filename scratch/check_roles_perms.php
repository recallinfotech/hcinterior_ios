<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);
$res = $conn->query("SELECT id, role_name FROM hc_roles");
echo "Total roles: " . $res->num_rows . "\n";
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . ': ' . $row['role_name'] . "\n";
}
