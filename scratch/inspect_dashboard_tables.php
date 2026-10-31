<?php
$cookie_file = __DIR__ . '/cookie.txt';
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/admin/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$res = curl_exec($ch);
curl_close($ch);

// Find tables in dashboard
preg_match_all('/<table[^>]*>.*?<\/table>/is', $res, $matches);
echo "Total tables in dashboard: " . count($matches[0]) . "\n";
foreach ($matches[0] as $i => $tbl) {
    if (preg_match('/<table[^>]*class=["\']([^"\']*)["\']/', $tbl, $m_class)) {
        echo "Table $i class: " . $m_class[1] . "\n";
    }
    // Check how many rows
    $rows = preg_match_all('/<tr/i', $tbl, $m_rows);
    echo "  Rows: $rows\n";
    if (strpos($tbl, 'Manish') !== false) {
        echo "  *** Contains Manish! ***\n";
        // print first 500 chars of table
        echo substr(strip_tags($tbl), 0, 300) . "\n---\n";
    }
}
