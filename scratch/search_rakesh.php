<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');
$r = $mysqli->query("SELECT * FROM tbl_account_details WHERE fullname LIKE '%Rakesh%' OR fullname LIKE '%Vennu%'");
while($row = $r->fetch_assoc()) echo json_encode($row) . "\n";
