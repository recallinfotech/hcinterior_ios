<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== USER 87 IN tbl_users ===\n";
$u = $mysqli->query("SELECT user_id, username, office_location_id FROM tbl_users WHERE user_id = 87")->fetch_assoc();
print_r($u);

echo "\n=== USER 87 IN tbl_account_details ===\n";
$ad = $mysqli->query("SELECT user_id, fullname, office_location_id FROM tbl_account_details WHERE user_id = 87")->fetch_assoc();
print_r($ad);

echo "=== ALL tbl_office_locations IN LOCAL DB ===\n";
$res = $mysqli->query("SELECT * FROM tbl_office_locations");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}

echo "\n=== USERS STATS ===\n";
$stat = $mysqli->query("SELECT COUNT(*) as total, SUM(CASE WHEN office_location_id IS NOT NULL AND office_location_id > 0 THEN 1 ELSE 0 END) as with_office FROM tbl_users")->fetch_assoc();
print_r($stat);

echo "\n=== USERS WITHOUT OFFICE (first 5) ===\n";
$no_off = $mysqli->query("SELECT u.user_id, u.username, u.office_location_id, ad.office_location_id as ad_office FROM tbl_users u LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id WHERE (u.office_location_id IS NULL OR u.office_location_id = 0) AND (ad.office_location_id IS NULL OR ad.office_location_id = 0) LIMIT 5");
while ($row = $no_off->fetch_assoc()) {
    print_r($row);
}
