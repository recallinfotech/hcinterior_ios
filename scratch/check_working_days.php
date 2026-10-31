<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

echo "=== WORKING DAYS ===\n";
$res = $mysqli->query("SELECT w.*, d.day FROM tbl_working_days w LEFT JOIN tbl_days d ON d.day_id = w.day_id");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== HOLIDAYS ===\n";
$h = $mysqli->query("SELECT * FROM tbl_holiday LIMIT 10");
while($r = $h->fetch_assoc()) echo json_encode($r) . "\n";
