<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('resolve_operation_role_from_designation')) {
    /**
     * Map designation ID / name / department to corresponding hc_roles.id
     */
    function resolve_operation_role_from_designation($designation_id = null, $designation_name = null, $department_name = null) {
        $CI =& get_instance();
        $CI->load->database();

        if (empty($designation_name) && !empty($designation_id)) {
            $desig_row = $CI->db->select('d.designations, dep.deptname')
                                ->from('tbl_designations d')
                                ->join('tbl_departments dep', 'dep.departments_id = d.departments_id', 'left')
                                ->where('d.designations_id', $designation_id)
                                ->get()
                                ->row();
            if (!empty($desig_row)) {
                $designation_name = $desig_row->designations;
                $department_name = $desig_row->deptname;
            }
        }

        $d = strtolower(trim($designation_name ?? ''));
        $dept = strtolower(trim($department_name ?? ''));

        // Project Coordinator / Sub Project Coordinator
        if ($designation_id == 150 || strpos($d, 'sub project cordinator') !== false || strpos($d, 'sub pc') !== false) {
            return 14; // Sub Project Coordinator
        }
        if ($designation_id == 45 || strpos($d, 'project cordinator') !== false || strpos($d, 'project coordinator') !== false || strpos($d, 'pc') === 0) {
            return 1; // Project Coordinator
        }

        // Operation Managers / Senior Project Manager (SPM)
        if ($designation_id == 71 || $designation_id == 72 || strpos($d, 'operation manager') !== false || strpos($d, 'spm') !== false) {
            return 2; // OP / SPM
        }

        // Design Manager
        if ($designation_id == 76 || strpos($d, 'design manager') !== false) {
            return 3; // Design Manager
        }

        // QC
        if ($designation_id == 77 || $designation_id == 111 || strpos($d, 'qc') !== false) {
            return 5; // QC
        }

        // 3D Designer
        if ($designation_id == 141 || strpos($d, '3d') !== false) {
            return 13; // 3D
        }

        // Factory roles
        if ($designation_id == 112 || strpos($d, 'floor manager') !== false || strpos($d, 'production') !== false) {
            return 8; // Factory Production
        }
        if ($designation_id == 113 || strpos($d, 'planning') !== false) {
            return 7; // Factory Planning
        }
        if ($designation_id == 114 || strpos($d, 'purchase manager') !== false) {
            return 10; // Factory Purchase
        }
        if ($designation_id == 116 || strpos($d, 'store manager') !== false) {
            return 9; // Factory Store
        }

        // Finance Manager
        if (strpos($d, 'finance') !== false) {
            return 15; // Finance Manager
        }

        // Accountant
        if ($designation_id == 128 || strpos($d, 'accountant') !== false || strpos($dept, 'account') !== false) {
            return 12; // Accountant
        }

        // Interior Designers
        if (strpos($d, 'designer') !== false || strpos($d, 'design') !== false || strpos($dept, 'designer team') !== false) {
            return 4; // Designer
        }

        // Operations: PM / APM / Supervisor / Engineer in OP
        if (strpos($dept, 'op team') !== false || strpos($dept, 'operation') !== false ||
            in_array($d, ['pm', 'apm', 'project manager', 'site superviser', 'site supervisor', 'supervisor', 'senior site superviser', 'jr site engineer', 'site engineer', 'sr site engineer', 'assistant project manager', 'jr. site superviser'])) {
            return 6; // PWSupervisor (PM / Supervisor)
        }

        return null;
    }
}

if (!function_exists('is_finance_manager')) {
    /**
     * Check if user is assigned Finance Manager role in operationpermission (hc_roles.id = 15) or is Bansal
     */
    function is_finance_manager($user_id = null) {
        $CI =& get_instance();
        $CI->load->database();

        $uid = !empty($user_id) ? $user_id : $CI->session->userdata('user_id');
        if (empty($uid)) {
            return false;
        }

        $op_role_id = null;
        $user_row = $CI->db->select('role_id, operation_role_id, username, email')->where('user_id', $uid)->get('tbl_users')->row();
        if (!empty($user_row)) {
            if (!empty($user_row->operation_role_id)) {
                $op_role_id = $user_row->operation_role_id;
            }
            $user_comb = strtolower(($user_row->username ?? '') . ' ' . ($user_row->email ?? ''));
            if (strpos($user_comb, 'bansal') !== false) {
                return true;
            }
        }

        if (!$op_role_id && $uid == $CI->session->userdata('user_id')) {
            $op_role_id = $CI->session->userdata('operation_role_id');
        }

        $acc = $CI->db->select('fullname, designations_id')->where('user_id', $uid)->get('tbl_account_details')->row();
        if (!empty($acc)) {
            if (strpos(strtolower($acc->fullname ?? ''), 'bansal') !== false) {
                return true;
            }
            if (!$op_role_id && !empty($acc->designations_id)) {
                $op_role_id = resolve_operation_role_from_designation($acc->designations_id);
            }
        }

        if (!$op_role_id) {
            return false;
        }

        if ($op_role_id == 15) {
            return true;
        }

        $role = $CI->db->select('role_name, role_slug')->where('id', $op_role_id)->get('hc_roles')->row();
        if (!empty($role)) {
            if (trim($role->role_slug) == 'finance_manager' || strcasecmp(trim($role->role_name), 'Finance Manager') == 0) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('can_export_client_data')) {
    /**
     * Check if user is allowed to export client data (Admin, Finance Manager, or Bansal)
     */
    function can_export_client_data($user_id = null) {
        $CI =& get_instance();
        $CI->load->database();

        $uid = !empty($user_id) ? $user_id : $CI->session->userdata('user_id');
        $user_type = $CI->session->userdata('user_type');

        if ($user_type == 1) {
            return true;
        }

        if (is_finance_manager($uid)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('check_permission')) {
    /**
     * Check operation permissions using operation_role_id
     */
    function check_permission($permission_key, $user_id = null) {
        $CI =& get_instance();
        $CI->load->database();
        
        if (empty($user_id) && $CI->session->userdata('user_type') == 1) {
            return TRUE;
        }

        $operation_role_id = null;
        if (!empty($user_id)) {
            $user_row = $CI->db->select('role_id, operation_role_id')->where('user_id', $user_id)->get('tbl_users')->row();
            if (!empty($user_row)) {
                if ($user_row->role_id == 1) {
                    return TRUE;
                }
                if (!empty($user_row->operation_role_id)) {
                    $operation_role_id = $user_row->operation_role_id;
                }
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

        // Auto-detect operation_role_id from designation if still missing
        if (!$operation_role_id) {
            $target_uid = !empty($user_id) ? $user_id : $CI->session->userdata('user_id');
            $desig_id = ($target_uid == $CI->session->userdata('user_id')) ? $CI->session->userdata('designations_id') : null;
            if (!$desig_id && $target_uid) {
                $acc = $CI->db->select('designations_id')->where('user_id', $target_uid)->get('tbl_account_details')->row();
                if (!empty($acc)) {
                    $desig_id = $acc->designations_id;
                }
            }
            if ($desig_id) {
                $resolved_role = resolve_operation_role_from_designation($desig_id);
                if ($resolved_role) {
                    $operation_role_id = $resolved_role;
                    if ($target_uid) {
                        // Persist to DB so future lookups are instant
                        $CI->db->where('user_id', $target_uid)->update('tbl_users', ['operation_role_id' => $resolved_role]);
                        if ($target_uid == $CI->session->userdata('user_id')) {
                            $CI->session->set_userdata('operation_role_id', $resolved_role);
                        }
                    }
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