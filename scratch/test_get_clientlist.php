<?php
$cookie_file = __DIR__ . '/cookie.txt';
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/client/clientList');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest'
]);
// GET request without POST
$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET HTTP Code: $http_code\n";
$json = json_decode($res, true);
if ($json) {
    echo "GET recordsTotal: " . $json['recordsTotal'] . "\n";
    echo "GET recordsFiltered: " . $json['recordsFiltered'] . "\n";
    echo "GET data count: " . count($json['data']) . "\n";
} else {
    echo "Response: " . substr($res, 0, 500) . "\n";
}
