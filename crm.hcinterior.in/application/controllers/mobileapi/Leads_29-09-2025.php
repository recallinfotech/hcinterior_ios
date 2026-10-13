<?php
error_reporting(E_ALL);
 ini_set('display_errors', 1);
defined('BASEPATH') or exit('No direct script access allowed');

class Leads extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('items_model');
    }

    public function leadList()
{
    header('Content-Type: application/json');

    // Load helper if not already loaded
    $this->load->helper('api_helper');

    // Validate token from Authorization header
    $user = validate_api_token(); // This will exit if token is invalid

    // Read raw JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Extract inputs from JSON body
    $page       = $data['page'] ?? 1;
    $limit      = $data['limit'] ?? 20;
    $user_id    = $data['user_id'] ?? null;
    $lead_status_new = $data['lead_status_new'] ?? null;
    $budget_from     = $data['budget_from'] ?? null;
    $budget_to       = $data['budget_to'] ?? null;
    $from_date       = $data['from_date'] ?? null;
    $to_date         = $data['to_date'] ?? null;
    $sub_status      = $data['sub_status'] ?? null;

    // Sanitize and fallback defaults
    $page = (is_numeric($page) && $page > 0) ? (int) $page : 1;
    $limit = (is_numeric($limit) && $limit > 0) ? (int) $limit : 20;
    $offset = ($page - 1) * $limit;

    // Base query
    $this->db->from('tbl_leads');
    $this->db->join('tbl_lead_status', 'tbl_lead_status.lead_status_id=tbl_leads.lead_status_id', 'left');
    $this->db->join('tbl_lead_source', 'tbl_lead_source.lead_source_id=tbl_leads.lead_source_id', 'left');
    $this->db->where('converted_client_id', 0);

    if (!empty($user_id)) {
        $this->db->group_start();
        $this->db->where('tbl_leads.permission', 'all');
        $this->db->or_like('tbl_leads.permission', '"' . $user_id . '"');
        $this->db->group_end();
    } else {
        $this->db->or_like('tbl_leads.permission', 'all');
    }

    // Apply filters
    if (!empty($lead_status_new)) {
        $this->db->where('tbl_leads.lead_status_id', $lead_status_new);
    }

    if (!empty($budget_from)) {
        $this->db->where('tbl_leads.budget_from >=', $budget_from);
    }

    if (!empty($budget_to)) {
        $this->db->where('tbl_leads.budget_to <=', $budget_to);
    }

    if (!empty($from_date)) {
        $this->db->where('DATE(tbl_leads.created_time) >=', date('Y-m-d', strtotime($from_date)));
    }

    if (!empty($to_date)) {
        $this->db->where('DATE(tbl_leads.created_time) <=', date('Y-m-d', strtotime($to_date)));
    }

    if (!empty($sub_status)) {
        $this->db->where('sub_status', $sub_status);
    }

    // Get total records before pagination
    $totalRecords = $this->db->count_all_results('', false);

    // Apply limit and offset
    $this->db->limit($limit, $offset);

    $query = $this->db->get();
    $results = $query->result();

    $data_output = [];
    foreach ($results as $lead) {
        $custom_fields = custom_form_table(5, $lead->leads_id);

        $data_output[] = [
            'lead_id'       => $lead->leads_id,
            'lead_name'     => $lead->lead_name,
            'contact_name'  => $lead->contact_name,
            'email'         => $lead->email,
            'phone'         => $lead->phone,
            'city'          => $lead->city,
            'lead_source'   => $lead->lead_source,
            'lead_status'   => $lead->lead_status,
            'lead_type'     => $lead->lead_type,
            'sub_status'    => $lead->sub_status,
            'created_time'  => $lead->created_time,
            'budget_from'   => $lead->budget_from,
            'budget_to'     => $lead->budget_to,
            'is_duplicate'  => $lead->is_duplicate,
            'permission'  => $lead->permission,
            'custom_fields' => $custom_fields
        ];
    }

    echo json_encode([
        'status'  => true,
        'message' => 'Lead list fetched',
        'page'    => $page,
        'limit'   => $limit,
        'total'   => $totalRecords,
        'data'    => $data_output
    ]);
}



public function updateLead()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Read JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $lead_id = $data['lead_id'] ?? null;
    if (empty($lead_id)) {
        echo json_encode([
            'status' => false,
            'message' => 'Lead ID is required'
        ]);
        return;
    }

    // Prepare fields to update
    $updateData = [];
    if (isset($data['lead_name']))    $updateData['lead_name'] = $data['lead_name'];
    if (isset($data['contact_name'])) $updateData['contact_name'] = $data['contact_name'];
    if (isset($data['email']))        $updateData['email'] = $data['email'];
    if (isset($data['phone']))        $updateData['phone'] = $data['phone'];
    if (isset($data['city']))         $updateData['city'] = $data['city'];
    if (isset($data['lead_source_id']))  $updateData['lead_source_id'] ;
    if (isset($data['lead_status_id']))  $updateData['lead_status_id'] ;
    if (isset($data['lead_type']))    $updateData['lead_type'] = $data['lead_type'];
    if (isset($data['sub_status']))   $updateData['sub_status'] = $data['sub_status'];
    if (isset($data['budget_from']))  $updateData['budget_from'] = $data['budget_from'];
    if (isset($data['budget_to']))    $updateData['budget_to'] = $data['budget_to'];

    if (empty($updateData)) {
        echo json_encode([
            'status' => false,
            'message' => 'No fields provided to update'
        ]);
        return;
    }

    // Update record
    $this->db->where('leads_id', $lead_id);
    $success = $this->db->update('tbl_leads', $updateData);

    if ($success) {
        echo json_encode([
            'status' => true,
            'message' => 'Lead updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update lead'
        ]);
    }
}


public function leadStatusList()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Fetch all statuses
    $this->db->order_by('order_no', 'ASC');
    $query = $this->db->get('tbl_lead_status');
    $results = $query->result();

    $data_output = [];
    foreach ($results as $row) {
        $data_output[] = [
            'lead_status_id' => $row->lead_status_id,
            'lead_status'    => $row->lead_status,
            'lead_type'      => $row->lead_type,
            'order_no'       => $row->order_no
        ];
    }

    echo json_encode([
        'status'  => true,
        'message' => 'Lead status list fetched',
        'total'   => count($data_output),
        'data'    => $data_output
    ]);
}


public function reminderList()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Read input (JSON or POST)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $user_id   = $data['user_id'] ?? null;
    $from_date = $data['from_date'] ?? null;
    $to_date   = $data['to_date'] ?? null;

    if (empty($user_id)) {
        echo json_encode([
            'status' => false,
            'message' => 'User ID is required'
        ]);
        return;
    }

    // Base query
    $this->db->select('r.*, l.lead_name, l.contact_name, l.phone, l.email');
    $this->db->from('tbl_reminders r');
    $this->db->join('tbl_leads l', 'l.leads_id = r.module_id', 'left');
    $this->db->where('r.user_id', $user_id);
    $this->db->where('r.module', 'leads');

    // Apply date filters if given
    if (!empty($from_date)) {
        $this->db->where('DATE(r.date) >=', date('Y-m-d', strtotime($from_date)));
    }
    if (!empty($to_date)) {
        $this->db->where('DATE(r.date) <=', date('Y-m-d', strtotime($to_date)));
    }

    $this->db->order_by('r.date', 'DESC');
    $query = $this->db->get();

    $results = $query->result();
    $data_output = [];

    foreach ($results as $row) {
        $data_output[] = [
            'reminder_id'     => $row->reminder_id,
            'description'     => $row->description,
            'date'            => $row->date,
            'notified'        => $row->notified,
            'before_notify'   => $row->before_notify,
            'after_notify'    => $row->after_notify,
            'module'          => $row->module,
            'module_id'       => $row->module_id,
            'lead_name'       => $row->lead_name,
            'contact_name'    => $row->contact_name,
            'phone'           => $row->phone,
            'email'           => $row->email,
            'notify_by_email' => $row->notify_by_email,
            'created_by'      => $row->created_by
        ];
    }

    echo json_encode([
        'status'  => true,
        'message' => 'Reminder list fetched',
        'total'   => count($data_output),
        'data'    => $data_output
    ]);
}


public function addReminder()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Read input JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $description = $data['description'] ?? null;
    $date        = $data['date'] ?? null; // expected format: Y-m-d H:i:s
    $module_id   = $data['lead_id'] ?? null;
    $user_id     = $data['user_id'] ?? null;

    // Validation
    if (empty($description) || empty($date) || empty($module_id) || empty($user_id)) {
        echo json_encode([
            'status'  => false,
            'message' => 'Required fields: description, date, lead_id, user_id'
        ]);
        return;
    }

    $insertData = [
        'description'     => $description,
        'date'            => date('Y-m-d H:i:s', strtotime($date)),
        'notified'        => 'No',
        'before_notify'   => 'No',
        'after_notify'    => 'No',
        'module'          => 'leads',
        'module_id'       => $module_id,
        'user_id'         => $user_id,
        'notify_by_email' => 'No',
      'created_by'      => isset($user->user_id) ? $user->user_id : $user_id
    ];

    $this->db->insert('tbl_reminders', $insertData);
    $reminder_id = $this->db->insert_id();

    echo json_encode([
        'status'      => true,
        'message'     => 'Reminder added successfully',
        'reminder_id' => $reminder_id
    ]);
}



 // === API to upload & save audio recording ===
  public function saveRecording()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');
    $user = validate_api_token();

    $lead_id = $this->input->post('lead_id');
    $phone   = $this->input->post('phone');
    $duration = $this->input->post('duration');
 
    if (empty($lead_id) || empty($_FILES['file']['name'])) {
        echo json_encode([
            'status' => false,
            'message' => 'Lead ID and recording file are required'
        ]);
        return;
    }

    $config['upload_path']   = './uploads/recordings/';
    $config['allowed_types'] = 'mp3|wav|m4a|ogg|amr';
    $config['max_size']      = 20480; // 20 MB
    $config['encrypt_name']  = true;

    if (!is_dir($config['upload_path'])) {
        mkdir($config['upload_path'], 0777, true);
    }

    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('file')) {
        echo json_encode([
            'status' => false,
            'message' => $this->upload->display_errors()
        ]);
        return;
    }

    $uploadData = $this->upload->data();
    $file_name  = $uploadData['file_name'];
    $file_type  = $uploadData['file_ext'];
    $file_url   = base_url('uploads/recordings/' . $file_name);

    $insertData = [
        'lead_id'     => $lead_id,
        'phone'       => $phone,
        'duration'    => $duration,
        'record_file' => $file_name,  // ✅ only filename
        'file_type'   => $file_type,
        'createdate'  => date('Y-m-d H:i:s'),
        'updatedate'  => date('Y-m-d H:i:s'),
        'status'      => $status
    ];

    $this->db->insert('tbl_call_recording', $insertData);
    $recording_id = $this->db->insert_id();

    echo json_encode([
        'status' => true,
        'message' => 'Recording saved successfully',
        'recording_id' => $recording_id,
        'file_url' => $file_url
    ]);
}



public function addComment()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Read input JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $lead_id = $data['lead_id'] ?? null;
    $user_id = $data['user_id'] ?? null;
    $comment = $data['comment'] ?? null;

    if (empty($lead_id) || empty($user_id) || empty($comment)) {
        echo json_encode([
            'status' => false,
            'message' => 'Required fields: lead_id, user_id, comment'
        ]);
        return;
    }

    $insertData = [
        'user_id'           => $user_id,
        'comment'           => $comment,
        'comments_attachment' => null,
        'comment_datetime'  => date('Y-m-d H:i:s'),
        'module'            => 'leads',
        'module_field_id'   => $lead_id,
        'attachments_id'    => 0,
        'uploaded_files_id' => 0,
        'comments_reply_id' => 0
    ];

    $this->db->insert('tbl_task_comment', $insertData);

    if ($this->db->affected_rows() > 0) {
        $comment_id = $this->db->insert_id();
        echo json_encode([
            'status'  => true,
            'message' => 'Comment added successfully',
            'comment_id' => $comment_id
        ]);
    } else {
        echo json_encode([
            'status'  => false,
            'message' => 'Failed to add comment',
            'db_error' => $this->db->error()
        ]);
    }
}


public function updateComment()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Read input JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $comment_id = $data['comment_id'] ?? null;
    $user_id    = $data['user_id'] ?? null;
    $comment    = $data['comment'] ?? null;

    if (empty($comment_id) || empty($user_id) || empty($comment)) {
        echo json_encode([
            'status' => false,
            'message' => 'Required fields: comment_id, user_id, comment'
        ]);
        return;
    }

    // Make sure this comment belongs to the same user (optional security)
    $this->db->where('task_comment_id', $comment_id);
    $this->db->where('user_id', $user_id);
    $existing = $this->db->get('tbl_task_comment')->row();

    if (!$existing) {
        echo json_encode([
            'status' => false,
            'message' => 'Comment not found or not owned by user'
        ]);
        return;
    }

    // Update data
    $updateData = [
        'comment'          => $comment,
        'comment_datetime' => date('Y-m-d H:i:s') // update timestamp
    ];

    $this->db->where('task_comment_id', $comment_id);
    $this->db->update('tbl_task_comment', $updateData);

    if ($this->db->affected_rows() > 0) {
        echo json_encode([
            'status'  => true,
            'message' => 'Comment updated successfully',
            'comment_id' => $comment_id
        ]);
    } else {
        echo json_encode([
            'status'  => false,
            'message' => 'No changes made or update failed',
            'db_error' => $this->db->error()
        ]);
    }
}



public function listComments()
{
    header('Content-Type: application/json');
    $this->load->helper('api_helper');

    // Validate token
    $user = validate_api_token();

    // Read input JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $lead_id = $data['lead_id'] ?? null;

    if (empty($lead_id)) {
        echo json_encode([
            'status' => false,
            'message' => 'lead_id is required'
        ]);
        return;
    }

    $this->db->select('c.*, u.username, u.email');
    $this->db->from('tbl_task_comment c');
    $this->db->join('tbl_users u', 'u.user_id = c.user_id', 'left');
    $this->db->where('c.module', 'leads');
    $this->db->where('c.module_field_id', $lead_id);
    $this->db->order_by('c.comment_datetime', 'DESC');
    $query = $this->db->get();

    $results = $query->result();
    $data_output = [];

    foreach ($results as $row) {
        $data_output[] = [
            'task_comment_id'  => $row->task_comment_id,
            'user_id'          => $row->user_id,
            'username'         => $row->username,
            'comment'          => $row->comment,
            'comment_datetime' => $row->comment_datetime,
            'comments_reply_id'=> $row->comments_reply_id
        ];
    }

    echo json_encode([
        'status' => true,
        'message' => 'Comments fetched successfully',
        'total' => count($data_output),
        'data' => $data_output
    ]);
}





}