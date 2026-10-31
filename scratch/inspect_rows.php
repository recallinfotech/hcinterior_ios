<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

echo "=== Non-empty lat/long in tbl_clock ===\n";
$q = $conn->query("SELECT clock_id, attendance_id, clockin_time, clockout_time, latitude, longitude, location FROM tbl_clock WHERE (latitude IS NOT NULL AND latitude != '') OR (longitude IS NOT NULL AND longitude != '') LIMIT 10");
$count = 0;
while ($r = $q->fetch_assoc()) {
    print_r($r);
    $count++;
}
echo "Total with lat/long: $count\n";

echo "=== Total count with lat/long in tbl_clock ===\n";
$q2 = $conn->query("SELECT count(*) as total FROM tbl_clock WHERE (latitude IS NOT NULL AND latitude != '') OR (longitude IS NOT NULL AND longitude != '')");
$r2 = $q2->fetch_assoc();
echo "Total: " . $r2['total'] . "\n";
