<?php
// Get an active session from tbl_sessions if any
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $mysqli->query("SELECT * FROM tbl_sessions ORDER BY timestamp DESC LIMIT 1");
$cookie_header = '';
if ($row = $res->fetch_assoc()) {
    $sess_id = $row['id'];
    $cookie_header = "Cookie: ci_session=" . $sess_id;
}

$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/attendance/attendance_report');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
if ($cookie_header) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$cookie_header]);
}
$html = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $http_code . "\n";
echo "Response Length: " . strlen($html) . "\n";

// Check for key elements in HTML
echo "Has 'Monthly Attendance Summary': " . (strpos($html, 'Monthly Attendance Summary') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Office Branch': " . (strpos($html, 'Office Branch') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'Edit Attendance :': " . (strpos($html, 'Edit Attendance :') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'attendance_report_table': " . (strpos($html, 'attendance_report_table') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'ABSENT': " . (strpos($html, 'ABSENT') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'HALF DAY': " . (strpos($html, 'HALF DAY') !== false ? 'YES' : 'NO') . "\n";
echo "Has 'PRESENT': " . (strpos($html, 'PRESENT') !== false ? 'YES' : 'NO') . "\n";

if (strpos($html, 'A PHP Error was encountered') !== false || strpos($html, 'An uncaught Exception was encountered') !== false) {
    echo "PHP Error found in response!\n";
    // extract error
    preg_match_all('/<h4>A PHP Error was encountered<\/h4>.*?<\/div>/s', $html, $matches);
    foreach($matches[0] as $m) echo strip_tags($m) . "\n";
} else {
    echo "NO PHP ERRORS DETECTED!\n";
}
