<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("SHOW COLUMNS FROM tbl_office_locations");
echo "tbl_office_locations cols: " . json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT) . "\n";

$res2 = $mysqli->query("SELECT * FROM tbl_office_locations LIMIT 5");
echo "tbl_office_locations sample: " . json_encode($res2->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT) . "\n";

$res3 = $mysqli->query("SHOW COLUMNS FROM tbl_account_details LIKE '%office%'");
echo "tbl_account_details office cols: " . json_encode($res3->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT) . "\n";

$res4 = $mysqli->query("SHOW COLUMNS FROM tbl_users LIKE '%office%'");
echo "tbl_users office cols: " . json_encode($res4->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT) . "\n";

$res5 = $mysqli->query("SHOW COLUMNS FROM tbl_account_details");
echo "all tbl_account_details cols: " . json_encode(array_column($res5->fetch_all(MYSQLI_ASSOC), 'Field'), JSON_PRETTY_PRINT) . "\n";
