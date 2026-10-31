<?php
$mysqli = @new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($mysqli->connect_error) {
    echo "Direct connect error: " . $mysqli->connect_error . "\n";
    echo "ini mysql default port: " . ini_get('mysqli.default_port') . "\n";
} else {
    echo "SUCCESSFULLY CONNECTED via Apache!\n";
}
