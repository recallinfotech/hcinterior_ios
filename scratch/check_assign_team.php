<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query('SELECT * FROM tbl_client_assign_team WHERE user_id = 92');
while ($row = $r->fetch_assoc()) {
    print_r($row);
}
