<?php
$content = file_get_contents('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/views/admin/payroll/make_payment.php');
$start = strpos($content, '<script type="text/javascript">');
$end = strpos($content, '</script>', $start);
$js = substr($content, $start + 31, $end - ($start + 31));
file_put_contents('c:/wamp64/www/hc_interior_live/scratch/test_make_payment.js', $js);
echo "Extracted JS length: " . strlen($js) . "\n";
