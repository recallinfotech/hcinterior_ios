<?php
$lines = file('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/views/admin/client/details/on_site_purchase_request_new.php');
for ($i = 708; $i <= 712; $i++) {
    echo $i . ': [' . bin2hex($lines[$i-1]) . '] ' . $lines[$i-1];
}
