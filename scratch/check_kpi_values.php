<?php
$cookie_file = __DIR__ . '/cookie.txt';
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/client/manage_client');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$res = curl_exec($ch);
curl_close($ch);

preg_match('/<div class="client-kpi-grid">.*?<\/div>\s*<\/div>/s', $res, $m_grid);
if (!empty($m_grid[0])) {
    echo "KPI Grid content:\n" . strip_tags($m_grid[0]) . "\n";
} else {
    echo "No KPI grid matched\n";
}
