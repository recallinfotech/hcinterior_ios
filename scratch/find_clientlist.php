<?php
$f = file('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/controllers/admin/Client.php');
foreach ($f as $i => $line) {
    if (stripos($line, 'clientList') !== false) {
        echo ($i + 1) . ': ' . $line;
    }
}
