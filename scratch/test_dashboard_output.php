<?php
$cookie_file = __DIR__ . '/cookie.txt';
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$res = curl_exec($ch);
curl_close($ch);

// Search for any client IDs or names in dashboard html
echo "Dashboard length: " . strlen($res) . "\n";
if (strpos($res, 'Manish') !== false) echo "Found Manish in dashboard\n";
if (strpos($res, 'Vikas') !== false) echo "Found Vikas in dashboard\n";
if (strpos($res, 'Client Records') !== false) echo "Found Client Records in dashboard\n";
