<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $conn->query("
    SELECT TABLE_NAME, COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'hcinterior_git' 
      AND (COLUMN_NAME LIKE '%lat%' OR COLUMN_NAME LIKE '%long%' OR COLUMN_NAME LIKE '%loc%')
");
while ($r = $res->fetch_assoc()) {
    echo $r['TABLE_NAME'] . '.' . $r['COLUMN_NAME'] . "\n";
}
