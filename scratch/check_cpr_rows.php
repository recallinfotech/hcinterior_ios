<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $conn->query("SELECT * FROM tbl_client_payment_record LIMIT 10");
echo "Rows in tbl_client_payment_record: " . $res->num_rows . "\n";
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
