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

// Mock CI Session and Helpers
class MockCI {
    public $db;
    public $session;
    public function __construct($db) {
        $this->db = $db;
    }
}

$db = DB('default', TRUE);

// Let's test the Datatables query
// We can check how CodeIgniter builds the query
$db->select('tbl_client.*, tbl_customer_group.customer_group, tbl_account_details.fullname');
$db->from('tbl_client');
$db->join('tbl_customer_group', 'tbl_customer_group.customer_group_id=tbl_client.customer_group_id', 'left');
$db->join('tbl_account_details', 'tbl_account_details.user_id=tbl_client.primary_contact', 'left');

// custom_where for user 92:
$custom_where = [
    [
        'type' => 'where_raw',
        'condition' => '(tbl_client.client_id IN (42, 151, 78, 178, 249))'
    ]
];

foreach ($custom_where as $cond) {
    if ($cond['type'] == 'where_raw') {
        $db->where($cond['condition'], null, false);
    }
}

echo "Compiled SQL with custom_where:\n" . $db->get_compiled_select() . "\n";
