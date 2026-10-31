<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $conn->query("SHOW TABLES LIKE '%client%'");
echo "Client tables:\n";
while ($r = $res->fetch_array()) {
    echo "- " . $r[0] . "\n";
}
$res = $conn->query("DESCRIBE tbl_invoices");
if ($res) {
    echo "\ntbl_invoices:\n";
    while ($r = $res->fetch_assoc()) echo $r['Field'] . " (" . $r['Type'] . ")\n";
}
