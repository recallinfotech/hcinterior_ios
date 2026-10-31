<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

$today = date('Y-m-d');
$r = $m->query("SELECT u.user_id, u.username, u.last_login, u.online_time, ad.fullname, ad.employment_id, att.clocking_status, clk.clockin_time, clk.clockout_time 
FROM tbl_users u 
LEFT JOIN tbl_account_details ad ON u.user_id = ad.user_id 
LEFT JOIN tbl_attendance att ON (att.user_id = u.user_id AND att.date_in = '$today') 
LEFT JOIN tbl_clock clk ON clk.attendance_id = att.attendance_id 
WHERE u.role_id != 2 AND u.activated = 1 
LIMIT 10");

while($row = $r->fetch_assoc()) {
    $is_logged_in = ((int)$row['clocking_status'] === 1);
    $status = $is_logged_in ? 'logged_in' : (!empty($row['clockout_time']) ? 'clocked_out' : 'not_logged_in');
    echo "{$row['fullname']} ({$row['employment_id']}) -> Status: $status | In: {$row['clockin_time']} | Out: {$row['clockout_time']}\n";
}


