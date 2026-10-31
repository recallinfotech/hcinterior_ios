<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

echo "=== LEAVE TYPES in tbl_leave_application ===\n";
$res = $mysqli->query("SELECT DISTINCT leave_type, COUNT(*) as cnt FROM tbl_leave_application GROUP BY leave_type");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== ATTENDANCE STATUSES in tbl_attendance ===\n";
$res = $mysqli->query("SELECT DISTINCT attendance_status, COUNT(*) as cnt FROM tbl_attendance GROUP BY attendance_status");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== ALL COLUMNS OF tbl_attendance ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM tbl_attendance");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";

echo "=== ALL COLUMNS OF tbl_leave_application ===\n";
$res = $mysqli->query("SHOW COLUMNS FROM tbl_leave_application");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";
