<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

$query = "SELECT 
    u.user_id,
    ad.fullname,
    ad.phone,
    ad.mobile,
    ad.emp_code,
    ad.employment_id,
    ad.avatar,
    d.designations,
    dep.departments_id,
    dep.deptname,
    ad.office_location_id as office_id,
    COALESCE(ol.location_name, 'HC Interior Head Office, Noida') as office_branch
FROM tbl_users u
INNER JOIN tbl_account_details ad ON ad.user_id = u.user_id
LEFT JOIN tbl_designations d ON d.designations_id = ad.designations_id
LEFT JOIN tbl_departments dep ON dep.departments_id = d.departments_id
LEFT JOIN tbl_office_locations ol ON ol.id = ad.office_location_id
WHERE u.activated = 1 AND u.role_id != 2
ORDER BY ad.fullname ASC LIMIT 5";

$res = $mysqli->query($query);
if (!$res) {
    echo "SQL ERROR: " . $mysqli->error . "\n";
} else {
    echo "QUERY SUCCESS! Row count: " . $res->num_rows . "\n";
    while($row = $res->fetch_assoc()) {
        echo json_encode($row) . "\n";
    }
}
