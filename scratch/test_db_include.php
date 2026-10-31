<?php
define('BASEPATH', 1);
define('ENVIRONMENT', 'production');
include('c:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/config/database.php');
echo "DB Name: " . $db['default']['database'] . "\n";
echo "DB Host: " . $db['default']['hostname'] . "\n";
