<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("SELECT user_id, username, role_id FROM tbl_users WHERE role_id = 1 LIMIT 5");
while ($row = $r->fetch_assoc()) {
    print_r($row);
}
