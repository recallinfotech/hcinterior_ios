<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Generate a new API token for a user
 * 
 * @param int $user_id
 * @param int $expiry_in_hours Optional, default 24 hours
 * @return string $token
 */
function generate_api_token($user_id, $expiry_in_hours = 24)
{
    $CI =& get_instance();

    $token = bin2hex(random_bytes(32)); // 64-char random string
    $expiry = date('Y-m-d H:i:s', strtotime("+$expiry_in_hours hours"));

    $CI->db->where('user_id', $user_id);
    $CI->db->update('tbl_users', [
        'api_token'       => $token,
        'token_expiry'    => $expiry
    ]);

    return $token;
}

/**
 * Validate the token from request headers
 * 
 * @return object $user
 */
function validate_api_token()
{
    $CI =& get_instance();

    $headers = $CI->input->request_headers();
    $token_raw = '';

    if (!empty($headers['Authorization'])) {
        $token_raw = $headers['Authorization'];
    } else if (!empty($headers['authorization'])) {
        $token_raw = $headers['authorization'];
    } else if (!empty($headers['token'])) {
        $token_raw = $headers['token'];
    } else if (!empty($headers['X-API-TOKEN'])) {
        $token_raw = $headers['X-API-TOKEN'];
    } else if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $token_raw = $_SERVER['HTTP_AUTHORIZATION'];
    } else if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $token_raw = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } else {
        $token_raw = $CI->input->get_post('token', true) ?: $CI->input->get_post('api_token', true);
        if (empty($token_raw)) {
            $raw_json = json_decode(file_get_contents('php://input'), true);
            if (is_array($raw_json)) {
                $token_raw = $raw_json['token'] ?? ($raw_json['api_token'] ?? '');
            }
        }
    }

    $token = trim(str_replace('Bearer ', '', (string)$token_raw));

    if (empty($token)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Authorization token missing or empty']);
        exit;
    }

    $user = $CI->db->where('api_token', $token)
                   ->where('api_token !=', '')
                   ->where('api_token IS NOT NULL', null, false)
                   ->get('tbl_users')
                   ->row();

    if (!$user || empty($user->user_id)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Invalid API token']);
        exit;
    }

    // Check token expiry
    if (!empty($user->token_expiry) && strtotime($user->token_expiry) < time()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Token expired']);
        exit;
    }

    return $user;
}

/**
 * Revoke a token (logout)
 * 
 * @param int $user_id
 */
function revoke_api_token($user_id)
{
    $CI =& get_instance();
    $CI->db->where('user_id', $user_id);
    $CI->db->update('tbl_users', [
        'api_token'    => null,
        'token_expiry' => null
    ]);
}

/**
 * Ensure tbl_employee_application_form exists
 */
function ensure_application_form_table()
{
    $CI =& get_instance();
    $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_employee_application_form` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `father_mob_no` varchar(50) DEFAULT NULL,
      `fresher_experience` varchar(50) DEFAULT 'Fresher',
      `total_experience_years` varchar(50) DEFAULT '',
      `last_salary` varchar(100) DEFAULT '',
      `present_salary` varchar(100) DEFAULT '',
      `aadhaar_no` varchar(50) DEFAULT '',
      `pan_no` varchar(50) DEFAULT '',
      `date_of_marriage` date DEFAULT NULL,
      `spouse_name` varchar(150) DEFAULT '',
      `blood_group` varchar(20) DEFAULT '',
      `height_weight` varchar(50) DEFAULT '',
      `present_address` text DEFAULT NULL,
      `present_contact_no` varchar(50) DEFAULT '',
      `permanent_address` text DEFAULT NULL,
      `permanent_contact_no` varchar(50) DEFAULT '',
      `relative_friend_in_company` text DEFAULT NULL,
      `previously_applied` text DEFAULT NULL,
      `photograph` varchar(255) DEFAULT '',
      `aadhaar_card_doc` varchar(255) DEFAULT '',
      `pan_card_doc` varchar(255) DEFAULT '',
      `signature_doc` varchar(255) DEFAULT '',
      `educational_qualifications` longtext DEFAULT NULL,
      `professional_references` longtext DEFAULT NULL,
      `family_details` longtext DEFAULT NULL,
      `work_experience` longtext DEFAULT NULL,
      `declaration_date` date DEFAULT NULL,
      `declaration_place` varchar(150) DEFAULT '',
      `status` enum('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
      `rejection_reason` text DEFAULT NULL,
      `approved_by` int(11) DEFAULT NULL,
      `approved_at` datetime DEFAULT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `idx_user_app` (`user_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    try {
        // Ensure menu 24 acts as parent
        $CI->db->where('menu_id', 24)->update('tbl_menu', ['link' => '#']);

        // Ensure all_users submenu exists under 24
        $chk_all_users = $CI->db->where('link', 'admin/user/user_list')->where('parent', 24)->get('tbl_menu')->row();
        if (empty($chk_all_users)) {
            $max_row = $CI->db->select_max('menu_id')->get('tbl_menu')->row();
            $next_all_users_id = !empty($max_row->menu_id) ? ((int)$max_row->menu_id + 1) : 221;
            $CI->db->insert('tbl_menu', [
                'menu_id' => $next_all_users_id,
                'label' => 'all_users',
                'link' => 'admin/user/user_list',
                'icon' => 'fa fa-circle-o',
                'parent' => 24,
                'sort' => 1,
                'status' => 1
            ]);
            $u_roles = $CI->db->where('menu_id', 24)->get('tbl_user_role')->result();
            if (!empty($u_roles)) {
                foreach ($u_roles as $ur) {
                    $CI->db->insert('tbl_user_role', [
                        'designations_id' => $ur->designations_id,
                        'menu_id' => $next_all_users_id,
                        'view' => $next_all_users_id,
                        'created' => $next_all_users_id,
                        'edited' => $next_all_users_id,
                        'deleted' => $next_all_users_id
                    ]);
                }
            }
        }

        // Ensure employment_applications exists under parent = 24
        $chk_menu = $CI->db->where('link', 'admin/employment_applications')->get('tbl_menu')->row();
        if (empty($chk_menu)) {
            $max_row = $CI->db->select_max('menu_id')->get('tbl_menu')->row();
            $next_menu_id = !empty($max_row->menu_id) ? ((int)$max_row->menu_id + 1) : 220;
            $CI->db->insert('tbl_menu', [
                'menu_id' => $next_menu_id,
                'label' => 'employment_applications',
                'link' => 'admin/employment_applications',
                'icon' => 'fa fa-id-card-o',
                'parent' => 24,
                'sort' => 2,
                'status' => 1
            ]);
            $roles = $CI->db->where('menu_id', 24)->get('tbl_user_role')->result();
            if (!empty($roles)) {
                foreach ($roles as $r) {
                    $CI->db->insert('tbl_user_role', [
                        'designations_id' => $r->designations_id,
                        'menu_id' => $next_menu_id,
                        'view' => $next_menu_id,
                        'created' => $next_menu_id,
                        'edited' => $next_menu_id,
                        'deleted' => $next_menu_id
                    ]);
                }
            }
        } else {
            // Make sure it is under parent = 24
            if ($chk_menu->parent != 24) {
                $CI->db->where('menu_id', $chk_menu->menu_id)->update('tbl_menu', ['parent' => 24, 'sort' => 2]);
            }
        }
    } catch (Exception $e) {}
}
