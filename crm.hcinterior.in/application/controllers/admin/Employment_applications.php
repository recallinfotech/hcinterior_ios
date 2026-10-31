<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Employment_applications extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->helper('api_helper');
        ensure_application_form_table();

        // Enforce User module permission (Module ID 24)
        $can_view = can_action('24', 'view');
        $user_type = $this->session->userdata('user_type');
        if ($user_type != 1 && empty($can_view)) {
            set_message('error', lang('there_in_no_value'));
            redirect('admin/dashboard');
        }
    }

    /**
     * List all employee application forms
     */
    public function index($filter = 'all')
    {
        $data['title'] = 'Employee Application & KYC Forms';
        $data['active'] = 'employment_applications';
        $data['filter'] = $filter;

        // Query all staff users with application form status
        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_users.activated, tbl_users.role_id');
        $this->db->select('tbl_account_details.fullname, tbl_account_details.employment_id, tbl_account_details.joining_date, tbl_account_details.mobile, tbl_account_details.avatar');
        $this->db->select('tbl_designations.designations');
        $this->db->select('tbl_departments.deptname');
        $this->db->select('tbl_employee_application_form.id as app_id, tbl_employee_application_form.status as app_status, tbl_employee_application_form.aadhaar_no, tbl_employee_application_form.pan_no, tbl_employee_application_form.total_experience_years, tbl_employee_application_form.created_at as app_created_at, tbl_employee_application_form.updated_at as app_updated_at, tbl_employee_application_form.approved_at, tbl_employee_application_form.rejection_reason');
        $this->db->from('tbl_users');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left');
        $this->db->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left');
        $this->db->join('tbl_employee_application_form', 'tbl_employee_application_form.user_id = tbl_users.user_id', 'left');
        $this->db->where('tbl_users.role_id !=', 2); // Exclude clients

        if ($filter === 'pending') {
            $this->db->where('tbl_employee_application_form.status', 'submitted');
        } elseif ($filter === 'approved') {
            $this->db->where('tbl_employee_application_form.status', 'approved');
        } elseif ($filter === 'rejected') {
            $this->db->where('tbl_employee_application_form.status', 'rejected');
        } elseif ($filter === 'draft') {
            $this->db->where('tbl_employee_application_form.status', 'draft');
        } elseif ($filter === 'not_submitted') {
            $this->db->where('tbl_employee_application_form.id IS NULL', null, false);
        }

        $this->db->order_by('tbl_employee_application_form.updated_at', 'DESC');
        $data['all_employees'] = $this->db->get()->result();

        // Count statistics
        $data['count_all'] = $this->db->where('role_id !=', 2)->count_all_results('tbl_users');
        $data['count_pending'] = $this->db->where('status', 'submitted')->count_all_results('tbl_employee_application_form');
        $data['count_approved'] = $this->db->where('status', 'approved')->count_all_results('tbl_employee_application_form');
        $data['count_rejected'] = $this->db->where('status', 'rejected')->count_all_results('tbl_employee_application_form');

        $data['subview'] = $this->load->view('admin/user/employment_applications_list', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * View full High Creation application form for an employee
     */
    public function view($user_id)
    {
        $user_id = (int)$user_id;
        $data['title'] = 'View Employment Application Form';
        $data['user_id'] = $user_id;

        $data['user_info'] = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        $data['profile_info'] = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
        $data['form_info'] = $this->db->where('user_id', $user_id)->get('tbl_employee_application_form')->row();

        // Parse JSON lists
        $data['edu_list'] = [];
        $data['ref_list'] = [];
        $data['fam_list'] = [];
        $data['exp_list'] = [];

        if (!empty($data['form_info'])) {
            if (!empty($data['form_info']->educational_qualifications)) {
                $data['edu_list'] = json_decode($data['form_info']->educational_qualifications, true) ?: [];
            }
            if (!empty($data['form_info']->professional_references)) {
                $data['ref_list'] = json_decode($data['form_info']->professional_references, true) ?: [];
            }
            if (!empty($data['form_info']->family_details)) {
                $data['fam_list'] = json_decode($data['form_info']->family_details, true) ?: [];
            }
            if (!empty($data['form_info']->work_experience)) {
                $data['exp_list'] = json_decode($data['form_info']->work_experience, true) ?: [];
            }
        }

        $data['subview'] = $this->load->view('admin/user/view_employment_application', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Edit / Fill application form from admin side
     */
    public function edit($user_id)
    {
        $user_id = (int)$user_id;
        $data['title'] = 'Edit Employment Application Form';
        $data['user_id'] = $user_id;

        $data['user_info'] = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        $data['profile_info'] = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
        $data['form_info'] = $this->db->where('user_id', $user_id)->get('tbl_employee_application_form')->row();

        // Parse JSON lists
        $data['edu_list'] = [];
        $data['ref_list'] = [];
        $data['fam_list'] = [];
        $data['exp_list'] = [];

        if (!empty($data['form_info'])) {
            if (!empty($data['form_info']->educational_qualifications)) {
                $data['edu_list'] = json_decode($data['form_info']->educational_qualifications, true) ?: [];
            }
            if (!empty($data['form_info']->professional_references)) {
                $data['ref_list'] = json_decode($data['form_info']->professional_references, true) ?: [];
            }
            if (!empty($data['form_info']->family_details)) {
                $data['fam_list'] = json_decode($data['form_info']->family_details, true) ?: [];
            }
            if (!empty($data['form_info']->work_experience)) {
                $data['exp_list'] = json_decode($data['form_info']->work_experience, true) ?: [];
            }
        }

        $data['subview'] = $this->load->view('admin/user/edit_employment_application', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Save application form by Admin
     */
    public function save()
    {
        $user_id = (int)$this->input->post('user_id', true);
        if (empty($user_id)) {
            set_message('error', 'Invalid User ID');
            redirect('admin/employment_applications');
        }

        $existing = $this->db->where('user_id', $user_id)->get('tbl_employee_application_form')->row();

        $upload_dir = 'uploads/employee_kyc/';
        if (!is_dir(FCPATH . $upload_dir)) {
            @mkdir(FCPATH . $upload_dir, 0777, true);
        }

        $photograph_path = !empty($existing->photograph) ? $existing->photograph : '';
        $aadhaar_doc_path = !empty($existing->aadhaar_card_doc) ? $existing->aadhaar_card_doc : '';
        $pan_doc_path = !empty($existing->pan_card_doc) ? $existing->pan_card_doc : '';
        $signature_path = !empty($existing->signature_doc) ? $existing->signature_doc : '';

        // Handle file uploads
        foreach (['photograph', 'aadhaar_card_doc', 'pan_card_doc', 'signature_doc'] as $fld) {
            if (!empty($_FILES[$fld]['name'])) {
                $ext = pathinfo($_FILES[$fld]['name'], PATHINFO_EXTENSION);
                $file_name = 'kyc_' . $fld . '_' . $user_id . '_' . time() . '.' . $ext;
                $target = FCPATH . $upload_dir . $file_name;
                if (move_uploaded_file($_FILES[$fld]['tmp_name'], $target)) {
                    if ($fld === 'photograph') $photograph_path = $upload_dir . $file_name;
                    if ($fld === 'aadhaar_card_doc') $aadhaar_doc_path = $upload_dir . $file_name;
                    if ($fld === 'pan_card_doc') $pan_doc_path = $upload_dir . $file_name;
                    if ($fld === 'signature_doc') $signature_path = $upload_dir . $file_name;
                }
            }
        }

        // Education array
        $edu_arr = [];
        $inst = $this->input->post('edu_institution', true) ?: [];
        $from = $this->input->post('edu_from', true) ?: [];
        $to = $this->input->post('edu_to', true) ?: [];
        $div = $this->input->post('edu_div', true) ?: [];
        $pct = $this->input->post('edu_percentage', true) ?: [];
        $subj = $this->input->post('edu_subjects', true) ?: [];
        $dist = $this->input->post('edu_distinctions', true) ?: [];

        foreach ($inst as $k => $in_val) {
            if (!empty(trim($in_val))) {
                $edu_arr[] = [
                    'institution'  => trim($in_val),
                    'from_year'    => $from[$k] ?? '',
                    'to_year'      => $to[$k] ?? '',
                    'div_grade'    => $div[$k] ?? '',
                    'percentage'   => $pct[$k] ?? '',
                    'subjects'     => $subj[$k] ?? '',
                    'distinctions' => $dist[$k] ?? ''
                ];
            }
        }

        // References array
        $ref_arr = [];
        $ref_name = $this->input->post('ref_name', true) ?: [];
        $ref_desig = $this->input->post('ref_designation', true) ?: [];
        $ref_cont = $this->input->post('ref_contact', true) ?: [];

        foreach ($ref_name as $rk => $r_val) {
            if (!empty(trim($r_val))) {
                $ref_arr[] = [
                    'name'                    => trim($r_val),
                    'designation_or_relation' => $ref_desig[$rk] ?? '',
                    'contact_no'              => $ref_cont[$rk] ?? ''
                ];
            }
        }

        // Family array
        $fam_arr = [];
        $fam_name = $this->input->post('fam_name', true) ?: [];
        $fam_rel = $this->input->post('fam_relation', true) ?: [];
        $fam_occ = $this->input->post('fam_occupation', true) ?: [];
        $fam_dob = $this->input->post('fam_dob', true) ?: [];

        foreach ($fam_name as $fk => $f_val) {
            if (!empty(trim($f_val))) {
                $fam_arr[] = [
                    'serial_no'  => $fk + 1,
                    'name'       => trim($f_val),
                    'relation'   => $fam_rel[$fk] ?? '',
                    'occupation' => $fam_occ[$fk] ?? '',
                    'dob'        => $fam_dob[$fk] ?? ''
                ];
            }
        }

        // Work experience array
        $exp_arr = [];
        $exp_comp = $this->input->post('exp_company', true) ?: [];
        $exp_from = $this->input->post('exp_from', true) ?: [];
        $exp_to = $this->input->post('exp_to', true) ?: [];
        $exp_pos = $this->input->post('exp_position', true) ?: [];
        $exp_ctc = $this->input->post('exp_ctc', true) ?: [];

        foreach ($exp_comp as $ek => $e_val) {
            if (!empty(trim($e_val))) {
                $exp_arr[] = [
                    'company_name'    => trim($e_val),
                    'from_date'       => $exp_from[$ek] ?? '',
                    'to_date'         => $exp_to[$ek] ?? '',
                    'position_nature' => $exp_pos[$ek] ?? '',
                    'ctc'             => $exp_ctc[$ek] ?? ''
                ];
            }
        }

        $status = $this->input->post('status', true) ?: 'approved';

        $save_data = [
            'user_id'                   => $user_id,
            'father_mob_no'             => $this->input->post('father_mob_no', true),
            'fresher_experience'        => $this->input->post('fresher_experience', true) ?: 'Fresher',
            'total_experience_years'    => $this->input->post('total_experience_years', true),
            'last_salary'               => $this->input->post('last_salary', true),
            'present_salary'            => $this->input->post('present_salary', true),
            'aadhaar_no'                => $this->input->post('aadhaar_no', true),
            'pan_no'                    => $this->input->post('pan_no', true),
            'date_of_marriage'          => $this->input->post('date_of_marriage', true) ?: null,
            'spouse_name'               => $this->input->post('spouse_name', true),
            'blood_group'               => $this->input->post('blood_group', true),
            'height_weight'             => $this->input->post('height_weight', true),
            'present_address'           => $this->input->post('present_address', true),
            'present_contact_no'        => $this->input->post('present_contact_no', true),
            'permanent_address'         => $this->input->post('permanent_address', true),
            'permanent_contact_no'      => $this->input->post('permanent_contact_no', true),
            'relative_friend_in_company'=> $this->input->post('relative_friend_in_company', true),
            'previously_applied'        => $this->input->post('previously_applied', true),
            'photograph'                => $photograph_path,
            'aadhaar_card_doc'          => $aadhaar_doc_path,
            'pan_card_doc'              => $pan_doc_path,
            'signature_doc'             => $signature_path,
            'educational_qualifications'=> json_encode($edu_arr),
            'professional_references'   => json_encode($ref_arr),
            'family_details'            => json_encode($fam_arr),
            'work_experience'           => json_encode($exp_arr),
            'declaration_date'          => $this->input->post('declaration_date', true) ?: date('Y-m-d'),
            'declaration_place'         => $this->input->post('declaration_place', true) ?: 'Noida',
            'status'                    => $status,
            'updated_at'                => date('Y-m-d H:i:s')
        ];

        if ($status === 'approved') {
            $save_data['approved_by'] = $this->session->userdata('user_id');
            $save_data['approved_at'] = date('Y-m-d H:i:s');
        }

        if (empty($existing)) {
            $save_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_employee_application_form', $save_data);
        } else {
            $this->db->where('user_id', $user_id)->update('tbl_employee_application_form', $save_data);
        }

        // Sync core fields to tbl_account_details
        $acc_updates = [];
        $father = $this->input->post('father_name', true);
        if (!empty($father)) $acc_updates['father_name'] = $father;
        $dob = $this->input->post('date_of_birth', true);
        if (!empty($dob)) $acc_updates['date_of_birth'] = $dob;
        $gender = $this->input->post('gender', true);
        if (!empty($gender)) $acc_updates['gender'] = $gender;
        $marital = $this->input->post('marital_status', true);
        if (!empty($marital)) $acc_updates['maratial_status'] = $marital;
        $pres_addr = $this->input->post('present_address', true);
        if (!empty($pres_addr)) $acc_updates['present_address'] = $pres_addr;
        $mobile = $this->input->post('candidate_mobile', true);
        if (!empty($mobile)) $acc_updates['mobile'] = $mobile;
        if (!empty($photograph_path)) $acc_updates['avatar'] = $photograph_path;

        if (!empty($acc_updates)) {
            $this->db->where('user_id', $user_id)->update('tbl_account_details', $acc_updates);
        }

        set_message('success', 'Employment Application form saved successfully!');
        redirect('admin/employment_applications/view/' . $user_id);
    }

    /**
     * Quick approve an application form
     */
    public function approve($user_id)
    {
        $user_id = (int)$user_id;
        $form = $this->db->where('user_id', $user_id)->get('tbl_employee_application_form')->row();

        if (empty($form)) {
            set_message('error', 'No application form found for this employee to approve.');
            redirect('admin/employment_applications');
        }

        $this->db->where('user_id', $user_id)->update('tbl_employee_application_form', [
            'status'           => 'approved',
            'rejection_reason' => null,
            'approved_by'      => $this->session->userdata('user_id'),
            'approved_at'      => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);

        // Sync core fields to account details
        $acc_updates = [];
        if (!empty($form->present_address)) $acc_updates['present_address'] = $form->present_address;
        if (!empty($form->photograph)) $acc_updates['avatar'] = $form->photograph;
        if (!empty($acc_updates)) {
            $this->db->where('user_id', $user_id)->update('tbl_account_details', $acc_updates);
        }

        set_message('success', 'Employment Application & KYC has been VERIFIED and APPROVED successfully.');
        $ref = $this->input->server('HTTP_REFERER');
        if ($ref) {
            redirect($ref);
        } else {
            redirect('admin/employment_applications/view/' . $user_id);
        }
    }

    /**
     * Reject application with reason
     */
    public function reject($user_id)
    {
        $user_id = (int)$user_id;
        $reason = $this->input->post('rejection_reason', true);

        if (empty(trim($reason))) {
            $reason = 'Details incomplete or documentation unclear. Please review and re-submit.';
        }

        $this->db->where('user_id', $user_id)->update('tbl_employee_application_form', [
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'approved_by'      => null,
            'approved_at'      => null,
            'updated_at'       => date('Y-m-d H:i:s')
        ]);

        set_message('error', 'Employment Application has been REJECTED. Employee will see feedback to correct and re-submit.');
        $ref = $this->input->server('HTTP_REFERER');
        if ($ref) {
            redirect($ref);
        } else {
            redirect('admin/employment_applications/view/' . $user_id);
        }
    }
}
