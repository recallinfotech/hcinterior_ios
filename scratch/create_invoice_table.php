<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($m->connect_error) {
    die("Connection failed: " . $m->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `tbl_client_invoices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
  `invoice_file` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ci_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($m->query($sql) === TRUE) {
    echo "Table tbl_client_invoices created or exists successfully.\n";
} else {
    echo "Error creating table: " . $m->error . "\n";
}

$res = $m->query("SHOW COLUMNS FROM `tbl_client_invoices`");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
