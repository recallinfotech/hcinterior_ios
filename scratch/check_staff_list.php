<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

$res = $mysqli->query("SELECT COUNT(*) as total_users FROM tbl_users WHERE activated = 1 AND role_id != 2");
echo "Active staff count: " . $res->fetch_assoc()['total_users'] . "\n";

$res = $mysqli->query("SELECT u.user_id, ad.fullname, ad.phone, ad.mobile, ad.emp_code, ad.employment_id, d.designations, ol.location_name
FROM tbl_users u
LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id
LEFT JOIN tbl_designations d ON d.designations_id = ad.designations_id
LEFT JOIN tbl_office_locations ol ON ol.id = COALESCE(ad.office_location_id, ad.office_id)
WHERE u.activated = 1 AND u.role_id != 2
ORDER BY ad.fullname ASC
LIMIT 15");
while($r = $res->fetch_assoc()) echo json_encode($r) . "\n";
