<?php
$lines = file('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/views/admin/client/details/on_site_purchase_request_new.php');
foreach($lines as $num => $line) {
    if(stripos($line, 'Site Photos') !== false || stripos($line, 'Upload Photos') !== false || stripos($line, 'Delivery Status') !== false || stripos($line, 'Save Status') !== false) {
        echo ($num+1) . ': ' . trim($line) . "\n";
    }
}
