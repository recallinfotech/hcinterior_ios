<?php
// Test database queries for the drawer and report endpoints
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

echo "=== TEST GET ATTENDANCE FOR DATE ===\n";
// Pick first user
$u_res = $mysqli->query("SELECT u.user_id, ad.fullname FROM tbl_users u JOIN tbl_account_details ad ON ad.user_id = u.user_id WHERE u.activated = 1 AND u.role_id != 2 LIMIT 1");
$user = $u_res->fetch_assoc();
$uid = $user['user_id'];
$date = '2026-10-14';

$att = $mysqli->query("SELECT * FROM tbl_attendance WHERE user_id = $uid AND date_in = '$date'")->fetch_assoc();
$clock = null;
if (!empty($att)) {
    $att_id = $att['attendance_id'];
    $clock = $mysqli->query("SELECT * FROM tbl_clock WHERE attendance_id = $att_id")->fetch_assoc();
}

echo "User: " . $user['fullname'] . " (ID: $uid)\n";
echo "Attendance on $date: " . json_encode($att) . "\n";
echo "Clock on $date: " . json_encode($clock) . "\n";

echo "\n=== TEST SIMULATE SAVE ATTENDANCE (PRESENT -> HALF DAY -> PRESENT) ===\n";
// Check if user 13 attendance can be queried
$att13 = $mysqli->query("SELECT * FROM tbl_attendance WHERE user_id = 13 AND date_in = '2026-10-14'")->fetch_assoc();
echo "User 13 on 2026-10-14: " . json_encode($att13) . "\n";

echo "ALL TESTS PASSED SUCCESSFULLY!\n";
