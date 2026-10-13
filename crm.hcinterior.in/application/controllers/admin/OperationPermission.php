<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OperationPermission extends Admin_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Operation Permission Management Page View
    public function index()
    {
        // 1. Fetch All Roles
        $data['roles'] = $this->db->get('hc_roles')->result();

        // Auto-ensure required permissions exist
        $default_new_perms = [
            'region_wise_client' => 'Region Wise Client',
            'completion_file'    => 'Completion File',
            'payment_record'     => 'Payment Records'
        ];
        foreach ($default_new_perms as $pkey => $pname) {
            $check_perm = $this->db->get_where('hc_permissions', ['permission_key' => $pkey])->row();
            if (!$check_perm) {
                $this->db->insert('hc_permissions', [
                    'permission_name' => $pname,
                    'permission_key'  => $pkey
                ]);
            }
        }

        // 2. Fetch All Permissions (Order: all_client_list, region_wise_client, only_assigned_client, then others)
        $this->db->order_by("CASE 
            WHEN permission_key = 'all_client_list' THEN 1 
            WHEN permission_key = 'region_wise_client' THEN 2 
            WHEN permission_key = 'only_assigned_client' THEN 3 
            ELSE 100 END, id ASC", "", FALSE);
        $data['permissions'] = $this->db->get('hc_permissions')->result();

        // 3. Fetch Existing Role Permissions Mapping
        $role_permissions = $this->db->get('hc_role_permissions')->result();

        // Restructure permissions: $mapped[operation_role_id][permission_id] = true
        $mapped = [];
        foreach ($role_permissions as $rp) {
            $mapped[$rp->operation_role_id][$rp->permission_id] = true;
        }
        $data['mapped'] = $mapped;

        $data['title'] = "Manage Operation Permissions";
        
        // Dynamic subview setup
        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/permissions/index', $data);
        } else {
            $data['subview'] = $this->load->view('admin/permissions/index', $data, TRUE);
            $this->load->view('admin/_layout_main', $data);
        }
    }

    // AJAX Endpoint: Update DB on Permission Checkbox Change
    public function update_permission()
    {
        header('Content-Type: application/json');

        $operation_role_id = $this->input->post('operation_role_id', true);
        $permission_id     = $this->input->post('permission_id', true);
        $status            = $this->input->post('status', true); // 1 = Enable, 0 = Disable

        if (empty($operation_role_id) || empty($permission_id)) {
            echo json_encode(['status' => false, 'message' => 'Invalid Parameters!']);
            return;
        }

        if ($status == 1) {
            // Enable Permission: Check if already exists
            $check = $this->db->get_where('hc_role_permissions', [
                'operation_role_id' => $operation_role_id,
                'permission_id'     => $permission_id
            ])->row();

            if (!$check) {
                $this->db->insert('hc_role_permissions', [
                    'operation_role_id' => $operation_role_id,
                    'permission_id'     => $permission_id
                ]);
            }
            $message = "Permission Enabled Successfully!";
        } else {
            // Disable Permission: Remove record from DB
            $this->db->delete('hc_role_permissions', [
                'operation_role_id' => $operation_role_id,
                'permission_id'     => $permission_id
            ]);
            $message = "Permission Disabled Successfully!";
        }

        echo json_encode([
            'status'  => true, 
            'message' => $message
        ]);
    }
}