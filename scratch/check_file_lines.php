<?php
$content = file_get_contents('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/views/admin/client/details/on_site_purchase_request_new.php');
$lines = explode("\n", $content);
for ($i = 705; $i <= 715; $i++) {
    echo $i . ': [' . bin2hex($lines[$i-1]) . '] ' . $lines[$i-1] . "\n";
}
