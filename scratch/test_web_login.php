<?php
// Test login verification
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

// In config.php, what is encryption_key?
$config_file = file_get_contents('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/config/config.php');
preg_match('/config_item\(\'encryption_key\'\)|encryption_key\'\]\s*=\s*\'(.*)\'/', $config_file, $matches);
$encryption_key = $matches[1] ?? '';
echo "Encryption Key: $encryption_key\n";

$username = 'Shreyashi_Kishor_HC3042';
$password = 'HC@12345#';
$hashed = hash('sha512', $password . $encryption_key);

$r = $m->query("SELECT * FROM tbl_users WHERE username = '$username'");
$u = $r->fetch_assoc();
echo "User in DB:\n";
echo "  user_id: " . $u['user_id'] . "\n";
echo "  role_id: " . $u['role_id'] . "\n";
echo "  operation_role_id: " . $u['operation_role_id'] . "\n";
echo "  activated: " . $u['activated'] . "\n";
echo "  banned: " . $u['banned'] . "\n";
echo "  password matches: " . ($u['password'] === $hashed ? "YES" : "NO") . "\n";

$r2 = $m->query("SELECT * FROM tbl_account_details WHERE user_id = " . $u['user_id']);
$acc = $r2->fetch_assoc();
echo "Account details:\n";
echo "  fullname: " . $acc['fullname'] . "\n";
echo "  designations_id: " . $acc['designations_id'] . "\n";
echo "  company: " . $acc['company'] . "\n";
echo "  user_region: " . $acc['user_region'] . "\n";
