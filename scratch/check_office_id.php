<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$u = $m->query("SELECT user_id, username, office_location_id, office_id FROM tbl_users WHERE user_id = 87")->fetch_assoc();
print_r($u);

$ad = $m->query("SELECT user_id, fullname, office_location_id, office_id FROM tbl_account_details WHERE user_id = 87")->fetch_assoc();
print_r($ad);
