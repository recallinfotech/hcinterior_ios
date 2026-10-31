<?php
$filePath = 'crm.hcinterior.in/application/controllers/mobileapi/Client.php';
$content = file_get_contents($filePath);

// Pattern 1: in mark_attendance clockout
$orig1 = "'latitude'        => !empty(\$lat) ? \$lat : null,\r\n                         'longitude'       => !empty(\$long) ? \$long : null,\r\n                         'location'        => !empty(\$location) ? \$location : null";
$orig1_lf = "'latitude'        => !empty(\$lat) ? \$lat : null,\n                         'longitude'       => !empty(\$long) ? \$long : null,\n                         'location'        => !empty(\$location) ? \$location : null";

$repl1 = "'clockout_latitude'  => !empty(\$lat) ? \$lat : null,\r\n                         'clockout_longitude' => !empty(\$long) ? \$long : null,\r\n                         'clockout_location'  => !empty(\$location) ? \$location : null";
$repl1_lf = "'clockout_latitude'  => !empty(\$lat) ? \$lat : null,\n                         'clockout_longitude' => !empty(\$long) ? \$long : null,\n                         'clockout_location'  => !empty(\$location) ? \$location : null";

if (strpos($content, $orig1) !== false) {
    $content = str_replace($orig1, $repl1, $content);
    echo "Replaced pattern 1 (CRLF)\n";
} elseif (strpos($content, $orig1_lf) !== false) {
    $content = str_replace($orig1_lf, $repl1_lf, $content);
    echo "Replaced pattern 1 (LF)\n";
} else {
    echo "Pattern 1 not found\n";
}

// Pattern 2: in mark_face_attendance clockout
$orig2 = "'latitude'        => \$lat,\r\n                         'longitude'       => \$lng,\r\n                         'location'        => \$address";
$orig2_lf = "'latitude'        => \$lat,\n                         'longitude'       => \$lng,\n                         'location'        => \$address";

$repl2 = "'clockout_latitude'  => \$lat,\r\n                         'clockout_longitude' => \$lng,\r\n                         'clockout_location'  => \$address";
$repl2_lf = "'clockout_latitude'  => \$lat,\n                         'clockout_longitude' => \$lng,\n                         'clockout_location'  => \$address";

if (strpos($content, $orig2) !== false) {
    $content = str_replace($orig2, $repl2, $content);
    echo "Replaced pattern 2 (CRLF)\n";
} elseif (strpos($content, $orig2_lf) !== false) {
    $content = str_replace($orig2_lf, $repl2_lf, $content);
    echo "Replaced pattern 2 (LF)\n";
} else {
    echo "Pattern 2 not found\n";
}

file_put_contents($filePath, $content);
echo "Done updating Client.php\n";
