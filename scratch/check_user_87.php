<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

echo "=== User 87 in tbl_users ===\n";
$u = $conn->query("SELECT user_id, username, email, office_location_id, office_id FROM tbl_users WHERE user_id = 87")->fetch_assoc();
print_r($u);

echo "=== User 87 in tbl_account_details ===\n";
$ad = $conn->query("SELECT * FROM tbl_account_details WHERE user_id = 87")->fetch_assoc();
print_r($ad);

echo "=== tbl_office_locations ===\n";
$offices = $conn->query("SELECT id, location_name, latitude, longitude, allowed_radius FROM tbl_office_locations");
while ($o = $offices->fetch_assoc()) {
    print_r($o);
}
