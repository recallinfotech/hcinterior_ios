<?php
$cookie_file = __DIR__ . '/cookie.txt';
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$res = curl_exec($ch);
curl_close($ch);

preg_match_all('/<table[^>]*>.*?<\/table>/is', $res, $matches);
echo strip_tags($matches[0][0]) . "\n";
