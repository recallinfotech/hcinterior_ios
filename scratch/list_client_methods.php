<?php
$content = file_get_contents('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/controllers/admin/Client.php');
preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)/i', $content, $m);
foreach ($m[1] as $func) {
    echo $func . "\n";
}
