<?php
define('FCPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/');
define('BASEPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/system/');
define('APPPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/');
define('VIEWPATH', APPPATH . 'views/');
define('MODULES_PATH', APPPATH . 'modules/');
define('ENVIRONMENT', 'development');

require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'database/DB.php';

$m = new mysqli('localhost', 'root', '', 'hcinterior_git');
$res = $m->query("SELECT p.permission_key FROM hc_role_permissions rp JOIN hc_permissions p ON p.id = rp.permission_id WHERE rp.operation_role_id = 4");
echo "Role 4 permissions:\n";
while ($r = $res->fetch_assoc()) {
    echo $r['permission_key'] . "\n";
}
