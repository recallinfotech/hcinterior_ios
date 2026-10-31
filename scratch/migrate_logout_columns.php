<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

// 1. Add clockout columns to tbl_clock if not exists
$res = $conn->query("SHOW COLUMNS FROM tbl_clock LIKE 'clockout_latitude'");
if ($res->num_rows === 0) {
    $sql = "ALTER TABLE `tbl_clock` 
            ADD COLUMN `clockout_latitude` varchar(300) NULL AFTER `location`,
            ADD COLUMN `clockout_longitude` varchar(300) NULL AFTER `clockout_latitude`,
            ADD COLUMN `clockout_location` text NULL AFTER `clockout_longitude`";
    if ($conn->query($sql)) {
        echo "Successfully added clockout columns to tbl_clock.\n";
    } else {
        echo "Error adding columns to tbl_clock: " . $conn->error . "\n";
    }
} else {
    echo "Columns already exist in tbl_clock.\n";
}

// 2. Add logout columns to tbl_users if not exists
$res2 = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'last_logout_lat'");
if ($res2->num_rows === 0) {
    $sql2 = "ALTER TABLE `tbl_users` 
             ADD COLUMN `last_logout_lat` varchar(100) NULL,
             ADD COLUMN `last_logout_long` varchar(100) NULL,
             ADD COLUMN `last_logout_time` datetime NULL,
             ADD COLUMN `last_logout_location` text NULL";
    if ($conn->query($sql2)) {
        echo "Successfully added logout columns to tbl_users.\n";
    } else {
        echo "Error adding columns to tbl_users: " . $conn->error . "\n";
    }
} else {
    echo "Logout columns already exist in tbl_users.\n";
}
