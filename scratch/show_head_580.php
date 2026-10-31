<?php
$output = shell_exec('git show HEAD:crm.hcinterior.in/application/controllers/admin/Client.php');
$lines = explode("\n", $output);
for ($i = 570; $i < 600; $i++) {
    echo ($i+1) . ': ' . ($lines[$i] ?? '') . "\n";
}
