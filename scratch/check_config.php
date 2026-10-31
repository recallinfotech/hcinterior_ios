<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $m->query("SELECT config_key, value FROM tbl_config WHERE config_key LIKE '%office%' OR config_key LIKE '%radius%'");
while($row = $r->fetch_assoc()) {
    print_r($row);
}
