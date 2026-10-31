<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

echo "=== CHECK USERS & OFFICE LOCATIONS ===\n";
$res = $mysqli->query("SELECT u.user_id, ad.fullname, ad.phone, ad.mobile, ad.emp_code, ad.employment_id, ad.office_location_id, ad.office_id, d.designations, ol.location_name 
FROM tbl_users u
LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id
LEFT JOIN tbl_designations d ON d.designations_id = ad.designations_id
LEFT JOIN tbl_office_locations ol ON ol.id = COALESCE(ad.office_location_id, ad.office_id)
WHERE u.activated = 1 AND u.role_id != 2
LIMIT 10");
while($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== CHECK tbl_attendance SAMPLE ===\n";
$res = $mysqli->query("SELECT * FROM tbl_attendance ORDER BY attendance_id DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== CHECK tbl_clock SAMPLE ===\n";
$res = $mysqli->query("SELECT * FROM tbl_clock ORDER BY clock_id DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== CHECK tbl_attendance_status VALUES ===\n";
$res = $mysqli->query("SELECT DISTINCT attendance_status, count(*) as cnt FROM tbl_attendance GROUP BY attendance_status");
while($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== CHECK tbl_leave_application SAMPLE ===\n";
$res = $mysqli->query("SELECT * FROM tbl_leave_application ORDER BY leave_application_id DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== CHECK tbl_working_days / HOLIDAYS ===\n";
$res = $mysqli->query("SHOW TABLES LIKE '%holiday%'");
while($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
$res = $mysqli->query("SHOW TABLES LIKE '%working%'");
while($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
