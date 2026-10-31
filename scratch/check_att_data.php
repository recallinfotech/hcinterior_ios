<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

echo "=== ATTENDANCE DATES RANGE ===\n";
$res = $mysqli->query("SELECT MIN(date_in) as min_date, MAX(date_in) as max_date, COUNT(*) as total_rows FROM tbl_attendance");
echo json_encode($res->fetch_assoc()) . "\n";

echo "=== ATTENDANCE STATUS DISTRIBUTION ===\n";
$res = $mysqli->query("SELECT attendance_status, COUNT(*) as cnt FROM tbl_attendance GROUP BY attendance_status");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== RECENT ATTENDANCE ROWS ===\n";
$res = $mysqli->query("SELECT a.*, c.clockin_time, c.clockout_time FROM tbl_attendance a LEFT JOIN tbl_clock c ON c.attendance_id = a.attendance_id ORDER BY a.attendance_id DESC LIMIT 15");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";
