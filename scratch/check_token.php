<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$token = 'ae2259e2b8488ad8c0d22c837f3e26e8a0a18d4d90d040895d41ea888a6c9026';
$r = $m->query("SELECT user_id, username, office_location_id, api_token FROM tbl_users WHERE api_token = '$token'");
if ($row = $r->fetch_assoc()) {
    print_r($row);
} else {
    echo "Token not found in local DB\n";
    $r2 = $m->query("SELECT user_id, username, office_location_id, api_token FROM tbl_users WHERE user_id = 87");
    print_r($r2->fetch_assoc());
}
