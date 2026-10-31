<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("SELECT * FROM tbl_menu WHERE menu_id = 97");
echo "tbl_menu:\n" . json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT) . "\n";

$res2 = $mysqli->query("SELECT count(*) as cnt FROM tbl_user_role WHERE menu_id = 97");
echo "tbl_user_role count:\n" . json_encode($res2->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT) . "\n";
