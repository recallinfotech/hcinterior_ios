<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

$res = $conn->query("SHOW TABLES LIKE '%payment%'");
echo "Payment tables:\n";
while ($r = $res->fetch_array()) {
    echo "- " . $r[0] . "\n";
}

$res = $conn->query("SHOW TABLES LIKE '%invoice%'");
echo "\nInvoice tables:\n";
while ($r = $res->fetch_array()) {
    echo "- " . $r[0] . "\n";
}

$res = $conn->query("DESCRIBE tbl_client_payment_record");
if ($res) {
    echo "\ntbl_client_payment_record schema:\n";
    while ($r = $res->fetch_assoc()) {
        echo $r['Field'] . ' (' . $r['Type'] . ")\n";
    }
}
