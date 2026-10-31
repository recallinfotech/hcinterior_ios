<?php
define('BASEPATH', '1');
define('ENVIRONMENT', 'development');
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once 'c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/config/database.php';
$conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to DB!\n";

$tables = ['tbl_attendance_face_logs', 'tbl_clock', 'tbl_attendance'];
foreach ($tables as $t) {
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    if ($res && $res->num_rows > 0) {
        $c = $conn->query("SELECT count(*) as cnt FROM $t")->fetch_assoc()['cnt'];
        echo "$t: $c rows\n";
    } else {
        echo "$t: DOES NOT EXIST\n";
    }
}

// Check columns of tbl_attendance_face_logs
$res = $conn->query("SHOW COLUMNS FROM tbl_clock");
if ($res) {
    echo "\nColumns in tbl_clock:\n";
    while ($col = $res->fetch_assoc()) {
        echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
$sample = $conn->query("SELECT * FROM tbl_clock ORDER BY clock_id DESC LIMIT 5");
if ($sample) {
    echo "\nRecent clocks:\n";
    while ($row = $sample->fetch_assoc()) {
        print_r($row);
    }
}
