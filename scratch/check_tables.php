<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

$res = $m->query("SHOW COLUMNS FROM `tbl_invoices`");
echo "--- tbl_invoices columns ---\n";
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

$res = $m->query("SELECT * FROM tbl_client_payment_record ORDER BY id DESC LIMIT 5");
echo "--- tbl_client_payment_record sample ---\n";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
