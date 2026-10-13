<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('check_permission')) {
    /**
     * Check operation permissions using operation_role_id
     */
    function check_permission($permission_key, $user_id = null) {
        $CI =& get_instance();
        $CI->load->database();
        
        $operation_role_id = null;
        if (!empty($user_id)) {
            $user_row = $CI->db->select('operation_role_id')->where('user_id', $user_id)->get('tbl_users')->row();
            if (!empty($user_row) && !empty($user_row->operation_role_id)) {
                $operation_role_id = $user_row->operation_role_id;
            }
        }

        if (!$operation_role_id) {
            $operation_role_id = $CI->session->userdata('operation_role_id'); 
        }

        // Fresh check from DB for current session user if not in session
        if (!$operation_role_id) {
            $sess_uid = $CI->session->userdata('user_id');
            if ($sess_uid) {
                $user_row = $CI->db->select('operation_role_id')->where('user_id', $sess_uid)->get('tbl_users')->row();
                if (!empty($user_row) && !empty($user_row->operation_role_id)) {
                    $operation_role_id = $user_row->operation_role_id;
                    $CI->session->set_userdata('operation_role_id', $operation_role_id);
                }
            }
        }

        if (!$operation_role_id) {
            return FALSE;
        }

        // Query role permissions
        $CI->db->select('rp.id');
        $CI->db->from('hc_role_permissions rp');
        $CI->db->join('hc_permissions p', 'p.id = rp.permission_id');
        $CI->db->where('rp.operation_role_id', $operation_role_id);
        $CI->db->where('p.permission_key', $permission_key);
        
        $query = $CI->db->get();
        
        return ($query->num_rows() > 0);
    }
}