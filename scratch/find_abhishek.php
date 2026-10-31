<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $conn->query("SELECT u.user_id, u.username, a.fullname, a.employment_id FROM tbl_users u LEFT JOIN tbl_account_details a ON u.user_id = a.user_id WHERE a.fullname LIKE '%Abhishek%' OR u.username LIKE '%abhishek%'");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['user_id']} | Username: {$r['username']} | Name: {$r['fullname']} | EmpID: {$r['employment_id']}\n";
}

