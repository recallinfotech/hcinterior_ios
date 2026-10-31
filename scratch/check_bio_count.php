<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $conn->query("SELECT COUNT(*) as cnt FROM tbl_user_biometrics");
$row = $res->fetch_assoc();
echo "Total rows in tbl_user_biometrics: " . $row['cnt'] . "\n";

$users = $conn->query("SELECT user_id, username, role_id FROM tbl_users LIMIT 10");
while ($u = $users->fetch_assoc()) {
    echo "User: {$u['user_id']} | Username: {$u['username']} | Role: {$u['role_id']}\n";
}

