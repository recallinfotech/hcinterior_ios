<?php
// Simulate browser session login as user 92 and call admin/client/clientList
session_start();

// We can login through cURL to http://localhost/hc_interior_live/crm.hcinterior.in/login
$cookie_file = __DIR__ . '/cookie.txt';
if (file_exists($cookie_file)) unlink($cookie_file);

// 1. GET login page
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$res = curl_exec($ch);
curl_close($ch);

// 2. POST login credentials
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'user_name' => 'Shreyashi_Kishor_HC3042',
    'password'  => 'HC@12345#'
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
curl_close($ch);

// 3. Call admin/client/clientList
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/client/clientList');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'draw'   => 1,
    'start'  => 0,
    'length' => 25
]));
$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
$json = json_decode($res, true);
if (!$json) {
    echo "Raw response (first 1000 chars):\n" . substr($res, 0, 1000) . "\n";
} else {
    echo "recordsTotal: " . $json['recordsTotal'] . "\n";
    echo "recordsFiltered: " . $json['recordsFiltered'] . "\n";
    echo "data count: " . count($json['data']) . "\n";
    foreach ($json['data'] as $idx => $row) {
        // Find client ID in row
        echo "Row $idx: " . strip_tags($row[1] ?? '') . " | " . strip_tags($row[2] ?? '') . "\n";
    }
}
