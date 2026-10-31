<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $m->query("SHOW TABLES LIKE '%client_invoice%'");
echo "Matches for client_invoice:\n";
while ($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}

$res = $m->query("SHOW TABLES LIKE '%upload%'");
echo "Matches for upload:\n";
while ($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
