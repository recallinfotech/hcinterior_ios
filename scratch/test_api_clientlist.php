<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$token = bin2hex(random_bytes(32));
$m->query("UPDATE tbl_users SET api_token = '$token' WHERE user_id = 92");
echo "Token: $token\n";

// Now test calling mobileapi/Client/clientList
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/mobileapi/Client/clientList');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['limit' => 20]));
$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
$data = json_decode($res, true);
echo "Status: " . ($data['status'] ?? 'unknown') . "\n";
echo "Total Records: " . ($data['total_records'] ?? 'none') . "\n";
if (!empty($data['data'])) {
    echo "Clients returned: " . count($data['data']) . "\n";
    foreach ($data['data'] as $c) {
        echo "  - Client ID: " . $c['client_id'] . ", Name: " . $c['name'] . "\n";
    }
} else {
    echo "Response: " . substr($res, 0, 500) . "\n";
}
