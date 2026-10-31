<?php
define('BASEPATH', 'true');
define('ENVIRONMENT', 'development');
require __DIR__ . '/../crm.hcinterior.in/application/config/database.php';
$db_conf = $db['default'];
$m = new mysqli($db_conf['hostname'], $db_conf['username'], $db_conf['password'], $db_conf['database']);

echo "=== CHECKING RECENT COLUMNS & TABLES ===" . PHP_EOL;

$checks = [
    ['tbl_salary_payment', 'payment_status'],
    ['tbl_account_details', 'father_mob_no'],
    ['tbl_account_details', 'office_location_id'],
    ['tbl_users', 'office_location_id'],
    ['tbl_client_two_d_design', 'loose_furniture_file'],
];

foreach ($checks as $item) {
    $table = $item[0];
    $col = $item[1];
    $c = $m->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    echo "$table.$col: " . ($c && $c->num_rows ? 'EXISTS' : 'MISSING') . PHP_EOL;
}

$tables = ['tbl_user_biometrics', 'tbl_attendance_face_logs', 'tbl_user_leave_credits', 'tbl_office_locations', 'tbl_employee_application_form'];
foreach ($tables as $t) {
    $res = $m->query("SHOW CREATE TABLE `$t`");
    if ($res) {
        $r = $res->fetch_assoc();
        echo PHP_EOL . "/* $t */" . PHP_EOL . $r['Create Table'] . ';' . PHP_EOL;
    }
}
