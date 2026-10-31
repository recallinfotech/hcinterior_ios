<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$tables = $m->query("SHOW TABLES");
while ($t = $tables->fetch_array()) {
    $tbl = $t[0];
    $cols = $m->query("SHOW COLUMNS FROM `$tbl`");
    $has_office = false;
    while ($c = $cols->fetch_assoc()) {
        if (stripos($c['Field'], 'office') !== false) {
            echo "Table $tbl has column {$c['Field']}\n";
            $has_office = true;
        }
    }
    if ($has_office) {
        $data = $m->query("SELECT * FROM `$tbl` LIMIT 5");
        // just to see
    }
}
