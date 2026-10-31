<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM `tbl_users` LIKE 'allow_manual_punch'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE `tbl_users` ADD COLUMN `allow_manual_punch` TINYINT(1) NOT NULL DEFAULT 0");
    echo "Added allow_manual_punch to tbl_users\n";
} else {
    echo "allow_manual_punch already exists in tbl_users\n";
}

$q2 = "CREATE TABLE IF NOT EXISTS `tbl_user_biometrics` (
    `biometric_id` int NOT NULL AUTO_INCREMENT,
    `user_id` int NOT NULL,
    `face_descriptor` LONGTEXT NULL,
    `face_image_path` VARCHAR(255) NULL,
    `face_image_base64` LONGTEXT NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `rejection_reason` VARCHAR(255) NULL,
    `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`biometric_id`),
    UNIQUE KEY `idx_user_bio` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($conn->query($q2)) echo "tbl_user_biometrics table ready\n";

$q3 = "CREATE TABLE IF NOT EXISTS `tbl_attendance_face_logs` (
    `log_id` INT NOT NULL AUTO_INCREMENT,
    `attendance_id` INT NULL,
    `user_id` INT NOT NULL,
    `punch_type` ENUM('in','out') NOT NULL DEFAULT 'in',
    `punch_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `face_score` DECIMAL(5,2) NULL,
    `lat` DECIMAL(10, 8) NULL,
    `lng` DECIMAL(11, 8) NULL,
    `address` TEXT NULL,
    `photo_thumbnail` LONGTEXT NULL,
    `distance_from_office` DECIMAL(8,2) NULL,
    `is_inside_geofence` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`log_id`),
    KEY `idx_att_user` (`user_id`, `punch_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($conn->query($q3)) echo "tbl_attendance_face_logs table ready\n";
