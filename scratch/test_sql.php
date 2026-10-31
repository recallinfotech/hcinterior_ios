<?php
$_SERVER['HTTP_HOST'] = 'localhost';
define('FCPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/');
define('BASEPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/system/');
define('APPPATH', 'C:/wamp64/www/hc_interior_live/crm.hcinterior.in/application/');
define('VIEWPATH', APPPATH . 'views/');
define('MODULES_PATH', APPPATH . 'modules/');
define('ENVIRONMENT', 'development');

require_once BASEPATH . 'core/Common.php';
require_once BASEPATH . 'database/DB.php';

$db = DB('default', TRUE);

$custom_where = [
    [
        'type' => 'where_raw',
        'condition' => '(tbl_client.client_id IN (42,151,78,178,249))'
    ]
];

$db->from('tbl_client');
foreach ($custom_where as $condition) {
    if ($condition['type'] == 'where_raw') {
        $db->where($condition['condition'], null, false);
    }
}
echo "Compiled SQL:\n" . $db->get_compiled_select() . "\n";
