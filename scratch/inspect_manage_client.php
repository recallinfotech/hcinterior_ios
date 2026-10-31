<?php
$cookie_file = __DIR__ . '/cookie.txt';
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/client/manage_client');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$res = curl_exec($ch);
curl_close($ch);

echo "manage_client length: " . strlen($res) . "\n";
// Check if any client names are hardcoded in the HTML
if (strpos($res, 'Manish') !== false) echo "Found Manish in HTML\n";
if (strpos($res, 'Vikas') !== false) echo "Found Vikas in HTML\n";

// Check if KPI cards are visible
if (strpos($res, 'kpi-stat-card') !== false) {
    echo "KPI cards found in HTML\n";
} else {
    echo "KPI cards NOT in HTML (hidden as expected)\n";
}

// Check what Datatables URL is used
preg_match('/list\s*=\s*([^;]+);/', $res, $m_list);
echo "list variable in HTML: " . ($m_list[1] ?? 'not found') . "\n";
