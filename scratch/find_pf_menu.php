<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("SELECT * FROM tbl_menu WHERE link LIKE '%provident%' OR label LIKE '%provident%'");
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}
echo json_encode($rows, JSON_PRETTY_PRINT);
