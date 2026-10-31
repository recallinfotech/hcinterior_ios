<?php
$output = shell_exec('git show HEAD:crm.hcinterior.in/application/controllers/admin/Client.php');
$lines = explode("\n", $output);
for ($i = 440; $i < 475; $i++) {
    echo ($i+1) . ': ' . ($lines[$i] ?? '') . "\n";
}
