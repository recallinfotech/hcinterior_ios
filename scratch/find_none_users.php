<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query('SELECT u.user_id, u.username, ad.app_access FROM tbl_users u LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id WHERE ad.app_access = "none" OR ad.app_access IS NULL OR ad.app_access = "" LIMIT 5');
while($row = $r->fetch_assoc()) {
    print_r($row);
}
