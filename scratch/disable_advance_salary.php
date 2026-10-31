<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("UPDATE tbl_menu SET status = 0 WHERE menu_id = 98");
echo "Rows affected: " . $mysqli->affected_rows . "\n";

$check = $mysqli->query("SELECT menu_id, label, link, status FROM tbl_menu WHERE menu_id = 98");
echo json_encode($check->fetch_assoc(), JSON_PRETTY_PRINT);
