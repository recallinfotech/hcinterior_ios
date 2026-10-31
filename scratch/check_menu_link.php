<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $mysqli->query("SELECT * FROM tbl_menu WHERE link LIKE '%attendance_report%'");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";
