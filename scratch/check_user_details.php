<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query('SELECT user_id, username, role_id, operation_role_id, activated, banned FROM tbl_users WHERE user_id = 92');
print_r($r->fetch_assoc());
$r2 = $m->query('SELECT user_id, fullname, designations_id, user_region FROM tbl_account_details WHERE user_id = 92');
print_r($r2->fetch_assoc());
