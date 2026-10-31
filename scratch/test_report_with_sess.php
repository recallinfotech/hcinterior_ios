<?php
// Find admin credentials
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $mysqli->query("SELECT u.username, u.email FROM tbl_users u WHERE u.role_id = 1 LIMIT 1");
$admin = $res->fetch_assoc();
echo "Admin user: " . json_encode($admin) . "\n";

// Login via curl
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'user_name' => $admin['username'],
    'password' => 'admin' // test default or let's check
]);
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
$res_login = curl_exec($ch);

// Or let's see how session is created in tbl_sessions
$sess_res = $mysqli->query("SELECT * FROM tbl_sessions WHERE data LIKE '%user_id%' ORDER BY timestamp DESC LIMIT 1");
if ($s = $sess_res->fetch_assoc()) {
    echo "Found active session: " . $s['id'] . "\n";
    $ch2 = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/attendance/attendance_report');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Cookie: ci_session=" . $s['id']]);
    $html = curl_exec($ch2);
    echo "Response Length with session: " . strlen($html) . "\n";
    echo "Has 'Monthly Attendance Summary': " . (strpos($html, 'Monthly Attendance Summary') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'Office Branch': " . (strpos($html, 'Office Branch') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'Edit Attendance :': " . (strpos($html, 'Edit Attendance :') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'attendance_report_table': " . (strpos($html, 'attendance_report_table') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'ABSENT': " . (strpos($html, 'ABSENT') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'HALF DAY': " . (strpos($html, 'HALF DAY') !== false ? 'YES' : 'NO') . "\n";
    echo "Has 'PRESENT': " . (strpos($html, 'PRESENT') !== false ? 'YES' : 'NO') . "\n";
    if (strpos($html, 'A PHP Error was encountered') !== false) {
        echo "PHP Error encountered!\n";
        preg_match_all('/<h4>A PHP Error was encountered<\/h4>.*?<\/div>/s', $html, $matches);
        foreach($matches[0] as $m) echo strip_tags($m) . "\n";
    }
}
