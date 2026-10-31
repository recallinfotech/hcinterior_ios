<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("SELECT t.id, t.client_id, t.user_id, t.member_profile, a.fullname, u.email 
                FROM tbl_client_assign_team t
                LEFT JOIN tbl_account_details a ON a.user_id = t.user_id
                LEFT JOIN tbl_users u ON u.user_id = t.user_id
                WHERE t.member_profile LIKE '%Designer%'
                LIMIT 5");
print_r($r->fetch_all(MYSQLI_ASSOC));
