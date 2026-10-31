<?php
$filePath = 'c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/views/admin/client/details/on_site_purchase_request_new.php';
$lines = file($filePath);

// 1. Line 709 and 710 (1-indexed, so 708 and 709 in 0-indexed)
echo "Line 709 was: " . trim($lines[708]) . "\n";
echo "Line 710 was: " . trim($lines[709]) . "\n";

$lines[708] = "                                                  <i class=\"fa fa-camera text-primary\"></i> Photos (<span class=\"photo_count_badge\"><?= \$photo_count ?></span>)\r\n";
unset($lines[709]);

$content = implode('', $lines);

// 2. Remove table_photo_mandatory_badge blocks from JS
$pattern = '/\s*var badgeMandatory = parentRow\.find\(\'\.table_photo_mandatory_badge\'\);[\s\S]*?parentRow\.find\(\'\.photo_count_badge\'\)\.addClass\(\'text-danger font-bold\'\)\.removeClass\(\'text-success\'\);[\s\S]*?\}/';

$content = preg_replace($pattern, '', $content);

file_put_contents($filePath, $content);
echo "SUCCESS: Required badge completely removed from table row!\n";
