<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("SELECT * FROM tbl_menu WHERE parent = 89 OR menu_id = 89 ORDER BY sort ASC");
echo json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
