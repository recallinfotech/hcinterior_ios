<?php
error_reporting(E_ALL);
 ini_set('display_errors', 0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;


class Proposals extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('proposal_model');
        $this->load->library('gst');
        
    }


    public function proposals_state_report()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $data = array();
        $pathonor_jonno['proposals_state_report_div'] = $this->load->view("admin/proposals/proposals_state_report", $data, true);
        echo json_encode($pathonor_jonno);
        exit;
    }


    public function index($action = NULL, $id = NULL, $item_id = NULL)
    {

        $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        if (!empty($item_id)) {
            $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
            if (!empty($can_edit)) {
                $data['item_info'] = $this->proposal_model->check_by(array('proposals_items_id' => $item_id), 'tbl_proposals_items');
            }
        }
        if ($action == 'edit_proposals') {
            $data['active'] = 2;
            $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
            if (!empty($can_edit)) {
                $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');
            }
        } else if ($action == 'client' || $action == 'leads') {
            $data['module_id'] = $id;
            $data['module'] = $action;
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }

        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }


        if ($action == 'proposals_details') {
            redirect('admin/proposals/proposals_details/' . $id);
        } elseif ($action == 'proposals_history') {
            // get all proposals
            $data['all_proposals_info'] = $this->proposal_model->get_proposals($filterBy);
            $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');
            $data['title'] = "proposals History"; //Page title
            $subview = 'proposals_history';
        } elseif ($action == 'email_proposals') {
            // get all proposals
            $data['all_proposals_info'] = $this->proposal_model->get_proposals($filterBy);
            $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');
            $data['title'] = "Email proposals"; //Page title
            $subview = 'email_proposals';
        } elseif ($action == 'pdf_proposals') {
            $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');
            $data['title'] = "proposals PDF"; //Page title
            $this->load->helper('dompdf');
            $viewfile = $this->load->view('admin/proposals/proposals_pdf', $data, TRUE);
            pdf_create($viewfile, slug_it('proposals  # ' . $data['proposals_info']->reference_no));
        } else {
            $data['title'] = lang('proposals'); //Page title
            $subview = 'proposals';
        }
        $data['subview'] = $this->load->view('admin/proposals/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

     
 public function proposalsList($filterBy = null, $search_type = null)
{
    if ($this->input->is_ajax_request()) {
        $this->load->model('datatables');
        $this->datatables->table = 'proposals';

        $custom_field  = custom_form_table_search(11);
        $action_array  = array('id');
        $main_column   = array('ref_no', 'proposal_date', 'phone', 'status', 'expiry_date',  'version_no');
        $result        = array_merge($main_column, $custom_field, $action_array);

        $this->datatables->column_order  = $result;
        $this->datatables->column_search = $result;
        $this->datatables->order         = array('id' => 'desc');

        $where    = null;
        $where_in = null;

        // 🔹 Current logged-in user
        $user_id = $this->session->userdata('user_id');
        $is_admin = $this->session->userdata('user_type') == 1;

        // 🔹 Get user's department
        $this->db->where('user_id', $user_id);
        $current_user = $this->db->get('tbl_account_details')->row();

        $departments_name = '';
        if (!empty($current_user)) {
            $this->db->where('designations_id', $current_user->designations_id);
            $designations = $this->db->get('tbl_designations')->row();

            if (!empty($designations)) {
                $this->db->where('departments_id', $designations->departments_id);
                $departments = $this->db->get('tbl_departments')->row();

                if (!empty($departments)) {
                    $departments_name = $departments->deptname;
                }
            }
        }

        // 🔹 Allow full access if Admin or from Billing department
        $is_billing_user = (strtolower($departments_name) == 'billing');

        if (!$is_admin && !$is_billing_user) {
            // 🔹 Get leads that user has permission to access
            $this->db->select('leads_id');
            $this->db->from('tbl_leads');
            $this->db->like('permission', '"' . $user_id . '"');
            $lead_result = $this->db->get()->result();
            $lead_ids = array_column($lead_result, 'leads_id');

            // 🔹 No accessible leads → show empty table
            if (empty($lead_ids)) {
                render_table([], $where, $where_in);
                return;
            }

            // 🔹 Restrict proposals to user's permitted leads
            $where_in = ['lead_id', $lead_ids];
        }

        // 🔹 Fetch proposals
        $fetch_data = $this->datatables->get_datatable_permission(null, $where_in);

        $data = [];
        foreach ($fetch_data as $_key => $v_proposals) {
            if (!empty($v_proposals)) {
                $sub_array = [];

                // Reference link
                $name = '<a class="text-info" href="' . base_url() . 'admin/proposals/index/proposals_details/' . $v_proposals->id . '">' . $v_proposals->ref_no . '</a>';
                $sub_array[] = $name;

                // Proposal date
                $sub_array[] = strftime(config_item('date_format'), strtotime($v_proposals->proposal_date));

                // Expiry + overdue
                $overdue = '';
                if (
                    (strtotime($v_proposals->expiry_date) < strtotime(date('Y-m-d'))) &&
                    ($v_proposals->status == 'pending' || $v_proposals->status == 'draft')
                ) {
                    $overdue = '<span class="label label-danger ">' . lang("expired") . '</span>';
                }
					//$sub_array[] = strftime(config_item('date_format'), strtotime($v_proposals->expiry_date)) . ' ' . $overdue;

                // Lead details
                $lead_info = $this->proposal_model->check_by(['leads_id' => $v_proposals->lead_id], 'tbl_leads');
                $lead_name = !empty($lead_info)
                    ? $lead_info->contact_name . " (" . $lead_info->phone . ")"
                    : '-';
                $sub_array[] = $lead_name;

                // Status label
                $label = ($v_proposals->status == 'draft')
                    ? "info"
                    : (($v_proposals->status == 'accepted') ? "success" : "danger");
                $sub_array[] = "<span class='tags label label-" . $label . "'>" . lang($v_proposals->status) . "</span>";

                // Custom fields
                $custom_form_table = custom_form_table(11, $v_proposals->id);
                if (!empty($custom_form_table)) {
                    foreach ($custom_form_table as $v_fields) {
                        $sub_array[] = $v_fields;
                    }
                }

                // Actions
                $action  = btn_edit('admin/proposals/createnewproposal/edit_proposals/' . $v_proposals->id) . ' ';
                $action .= '<a href="' . base_url("admin/proposals/pdf/generate_pdf/" . $v_proposals->id) . '" 
                    class="btn btn-xs btn-danger" title="Download PDF" target="_blank">
                    <i class="fa fa-file-pdf-o"></i></a> ';
                $action .= btn_view('admin/proposals/view/proposal/' . $v_proposals->id) . ' ';
                $action .= ajax_anchor(
                    base_url("admin/proposals/delete/delete_proposals/$v_proposals->id"),
                    "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>",
                    [
                        "title" => lang('delete'),
                        "data-fade-out-on-success" => "#table_" . $_key
                    ]
                );
                if($v_proposals->exclusive_product) {
              $action .= '<a href="#" 
    class="btn btn-xs btn-warning open-exclusive-modal" 
    data-proposal-id="' . $v_proposals->id . '" 
    data-toggle="modal" 
    data-target="#exclusive_product">
    <i class="fa fa-edit"></i>
</a>';
                }
                $sub_array[] = $v_proposals->version_no;
                $sub_array[] = $action;

                $data[] = $sub_array;
            }
        }

        render_table($data, $where, $where_in);
    } else {
        redirect('admin/dashboard');
    }
}


public function get_exclusive_products($proposal_id)
{
    $products = $this->db
        ->where('proposals_id', $proposal_id)
        ->get('exclusive_product')
        ->result_array();

    echo json_encode($products);
}

 
public function save_exclusive_products()
{
    $proposal_id = $this->input->post('proposal_id', true);
    $product_ids = $this->input->post('product_id');
    $item_names  = $this->input->post('item_name');
    $descriptions = $this->input->post('description');
    $areas       = $this->input->post('area');
    $units       = $this->input->post('unit');
    $prices      = $this->input->post('price');
    $totals      = $this->input->post('total');
    $brands      = $this->input->post('brand');

    // 🔹 1. Insert / Update rows
    if (!empty($item_names)) {
        for ($i = 0; $i < count($item_names); $i++) {
            // skip empty rows
            if (empty($item_names[$i])) continue;

            $data = [
                'proposals_id' => $proposal_id,
                'item_name'    => $item_names[$i],
                'description'  => $descriptions[$i],
                'area'         => $areas[$i],
                'unit'         => $units[$i],
                'price'        => $prices[$i],
                'total'        => $totals[$i],
                'brand'        => $brands[$i],
                'status'       => 'active',
                'created_at'   => date('Y-m-d H:i:s')
            ];

            // Update if product_id exists, otherwise insert
            if (!empty($product_ids[$i])) {
                $this->db->where('id', $product_ids[$i]);
                $this->db->update('exclusive_product', $data);
            } else {
                $this->db->insert('exclusive_product', $data);
            }
        }
    }

    // 🔹 2. Handle deleted rows
    $deleted_ids = $this->input->post('deleted_ids', true);
    if (!empty($deleted_ids)) {
        $ids = explode(',', $deleted_ids);
        $this->db->where_in('id', $ids)->delete('exclusive_product');
    }

    // 🔹 3. Response
    if ($this->input->is_ajax_request()) {
        echo json_encode(['success' => true]);
    } else {
        set_message('success', 'Products updated successfully');
        redirect('admin/proposals');
    }
}




    public function proposals_details($id, $pdf = null)
    {
        $data['title'] = lang('proposal');
        $data['sales_info'] = get_row('tbl_proposals', array('proposals_id' => $id));
        if (!empty($data['sales_info'])) {
            $data['sales_info']->ref_no = lang('proposal') . ' : ' . $data['sales_info']->reference_no;
            $data['sales_info']->start_date = lang('proposal_date') . ' : ' . display_date($data['sales_info']->proposal_date);
            $url = base_url('frontend/proposals/') . url_encode($id);
            $data['qrcode'] = generate_qrcode($url);
            // check overdue invoice
            if (strtotime($data['sales_info']->due_date) < strtotime(date('Y-m-d')) && $data['sales_info']->status == 'pending' || strtotime($data['sales_info']->due_date) < strtotime(date('Y-m-d')) && $data['sales_info']->status == ('draft')) {
                // check overdue how many days from due_date
                $date1 = new DateTime($data['sales_info']->due_date);
                $date2 = new DateTime(date('Y-m-d'));
                $interval = $date1->diff($date2);
                $overdue_days = $interval->format('%a');
                $data['sales_info']->overdue_days = lang('proposal_overdue') . ' ' . lang('by') . ' ' . $overdue_days . ' ' . lang('days');
            }
            $data['sales_info']->end_date = lang('due_date') . ' : ' . display_date($data['sales_info']->due_date);
            if (!empty($data['sales_info']->user_id)) {
                $data['sales_info']->sales_agent = lang('sales') . ' ' . lang('agent') . ' : ' . fullname($data['sales_info']->user_id);
            }
            if ($data['sales_info']->status == 'accepted') {
                $label = 'success';
            } else {
                $label = 'danger';
            }
            $data['sales_info']->status = lang('status') . ' :  <span class="label label-' . $label . '">' . lang($data['sales_info']->status) . '</span>';

            $data['sales_info']->custom_field = '';
            $show_custom_fields = custom_form_label(11, $data['sales_info']->proposals_id);
            if (!empty($show_custom_fields)) {
                foreach ($show_custom_fields as $c_label => $v_fields) {
                    if (!empty($v_fields)) {
                        $data['sales_info']->custom_field .= $c_label . ' : ' . $v_fields . '<br>';
                    }
                }
            }
            if ($data['sales_info']->module == 'client') {
                $clientInfo = get_row('tbl_client', array('client_id' => $data['sales_info']->module_id));
                $data['sales_info'] = (object)array_merge((array)$data['sales_info'], (array)$clientInfo);
            } else if ($data['sales_info']->module == 'leads') {
                $leadsInfo = get_row('tbl_leads', array('leads_id' => $data['sales_info']->module_id));
                // merge $data['sales_info'] and $leadsInfo and make it object
                $data['sales_info'] = (object)array_merge((array)$data['sales_info'], (array)$leadsInfo);
                $data['sales_info']->name = $data['sales_info']->lead_name;
                $data['sales_info']->zipcode = null;
            }
            $data['items_id'] = 'proposals_items_id';
            $data['all_items'] = $this->proposal_model->ordered_items_by_id($data['sales_info']->proposals_id);
            // print_r($data['all_items']);
            // exit();
            $data['sales_info']->sub_total = ($this->proposal_model->proposal_calculation('proposal_cost', $data['sales_info']->proposals_id));
            $data['sales_info']->discount = ($this->proposal_model->proposal_calculation('discount', $data['sales_info']->proposals_id));
            $data['sales_info']->total = ($this->proposal_model->proposal_calculation('total', $data['sales_info']->proposals_id));
            $data['footer'] = config_item('proposal_footer');
        } else {
            set_message('error', 'No data Found');
            redirect('admin/proposals');
        }
        if (!empty($pdf)) {
            $this->common_model->sales_pdf($data, $pdf);
        }
        $data['subview'] = $this->load->view('admin/proposals/proposals_details', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function pdf_proposals($id)
    {
        $this->proposals_details($id, true);
    }

      public function save_proposals($id = NULL)
{
    // echo '<pre>'; print_r($_POST); exit;

    $created = can_action('140', 'created');
    $edited = can_action('140', 'edited');

    if (!empty($created) || (!empty($edited) && !empty($id))) {

        // ---------- Save Proposal Header ----------
        $data = $this->proposal_model->array_from_post(array(
            'reference_no', 'discount_type', 'tags', 'discount_percent',
            'user_id', 'adjustment', 'discount_total', 'show_quantity_as'
        ));

        if (empty($data['discount_type'])) {
            $data['discount_type'] = 'none';
        }
        $data['proposal_date'] = date('Y-m-d', strtotime($this->input->post('proposal_date', TRUE)));
        if (empty($data['proposal_date'])) {
            $data['proposal_date'] = date('Y-m-d');
        }
        if (empty($data['discount_percent'])) {
            $data['discount_percent'] = 0;
        }
        if (empty($data['discount_total'])) {
            $data['discount_total'] = 0;
        }

        $data['warehouse_id']  = $this->input->post('warehouse_id', TRUE);
        $data['proposal_year'] = date('Y', strtotime($this->input->post('proposal_date', TRUE)));
        $data['proposal_month'] = date('Y-m', strtotime($this->input->post('proposal_date', TRUE)));
        $data['due_date']      = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
        $data['notes']         = $this->input->post('notes', TRUE);

        // ---------- TAX ----------
        $tax['tax_name']  = $this->input->post('total_tax_name', TRUE);
        $tax['total_tax'] = $this->input->post('total_tax', TRUE);
        $data['total_tax'] = json_encode($tax);

        $i_tax = 0;
        if (!empty($tax['total_tax'])) {
            foreach ($tax['total_tax'] as $v_tax) {
                $i_tax += $v_tax;
            }
        }
        $data['tax'] = $i_tax;

        // ---------- Status ----------
        $save_as_draft = $this->input->post('status', TRUE);
        $data['status'] = !empty($save_as_draft) ? $save_as_draft : 'draft';

        // ---------- Module ----------
        $data['module'] = $this->input->post('module', true);
        if (!empty($data['module']) && $data['module'] == 'leads') {
            $data['module_id'] = $this->input->post('leads_id', true);
            $curren = $this->input->post('currency', true);
        } else {
            $data['module_id'] = $this->input->post('client_id', true);

            $currency = $this->proposal_model->client_currency_symbol($data['module_id']);
            if (!empty($currency->code)) {
                $curren = $currency->code;
            } else {
                $curren = config_item('default_currency');
            }
        }
        $data['currency'] = $curren;

        // ---------- Permission ----------
        $permission = $this->input->post('permission', true);
        if (!empty($permission)) {
            if ($permission == 'everyone') {
                $assigned = 'all';
            } else {
                $assigned_to = $this->proposal_model->array_from_post(array('assigned_to'));
                if (!empty($assigned_to['assigned_to'])) {
                    foreach ($assigned_to['assigned_to'] as $assign_user) {
                        $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                    }
                }
            }
            if (!empty($assigned)) {
                if ($assigned != 'all') {
                    $assigned = json_encode($assigned);
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;
        } else {
            set_message('error', lang('assigned_to') . ' Field is required');
            redirect('admin/proposals');
        }

        // ---------- Save Proposal ----------
        $this->proposal_model->_table_name = 'tbl_proposals';
        $this->proposal_model->_primary_key = 'proposals_id';

        if (!empty($id)) {
            $proposals_id = $id;
            $this->proposal_model->save($data, $id);
            $action = ('activity_proposals_updated');
            $msg = lang('proposals_updated');
            $description = 'not_proposal_updated';
        } else {
            $proposals_id = $this->proposal_model->save($data);
            $action = ('activity_proposals_created');
            $msg = lang('proposals_created');
            $description = 'not_proposal_created';
        }

        save_custom_field(11, $proposals_id);

        // ---------- REMOVE deleted items ----------
        $removed_items = $this->input->post('removed_items', TRUE);
        if (!empty($removed_items)) {
            foreach ($removed_items as $r_id) {
                if ($r_id != 'undefined') {
                    $this->db->where('proposals_items_id', $r_id);
                    $this->db->delete('tbl_proposals_items');
                }
            }
        }
        // print_r($proposals_id);
        // die;
        // ---------- BUILD ITEMS FROM FLOORS ----------
        $items_data = [];
        $floors       = $this->input->post('floor'); 
        $item_ids   = $this->input->post('item_id', true);
        $item_names = $this->input->post('item_name', true);
        $descs      = $this->input->post('description', true);
        $qtys       = $this->input->post('qty', true);
        $prices     = $this->input->post('price', true);
        $tax_rates  = $this->input->post('tax_rate', true);
        $totals     = $this->input->post('total', true);

        if (!empty($item_names)) {
            foreach ($item_names as $floorId => $items) {
                foreach ($items as $i => $name) {
                   $items_data[] = [
                        'proposals_id'     => $proposals_id,   // ✅ correct proposal reference
                        'floor'            => $floors[$floorId] ?? 0,
                        'saved_items_id'   => $item_ids[$floorId][$i] ?? 0,
                        'item_name'        => $name,
                        'item_desc'        => $descs[$floorId][$i] ?? '',
                        'quantity'         => $qtys[$floorId][$i] ?? 1,
                        'unit_cost'        => $prices[$floorId][$i] ?? 0,
                        'item_tax_rate'    => $tax_rates[$floorId][$i] ?? 0,
                        'total_cost'       => $totals[$floorId][$i] ?? 0,
                    ];
                }
            }
        }

        // ---------- SAVE ITEMS ----------
        if (!empty($items_data)) {
            foreach ($items_data as $items) {
                $tax = 0;
                if (!empty($items['item_tax_rate'])) {
                    $tax = $items['item_tax_rate'];
                }

                $price = $items['quantity'] * $items['unit_cost'];
                $items['item_tax_total'] = ($price / 100 * $tax);
                $items['total_cost']     = $price;

                $this->proposal_model->_table_name  = 'tbl_proposals_items';
                $this->proposal_model->_primary_key = 'proposals_items_id';
                $this->proposal_model->save($items);
            }
        }

        // ---------- Activity ----------
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'proposals',
            'module_field_id' => $proposals_id,
            'activity' => $action,
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/proposals/index/proposals_details/' . $proposals_id,
            'value1' => $data['reference_no']
        );
        $this->proposal_model->_table_name = 'tbl_activities';
        $this->proposal_model->_primary_key = 'activities_id';
        $this->proposal_model->save($activity);

        // ---------- Notify Client ----------
        if (!empty($data['client_id'])) {
            $client_info = $this->proposal_model->check_by(array('client_id' => $data['client_id']), 'tbl_client');
            if (!empty($client_info->primary_contact)) {
                $notifyUser = array($client_info->primary_contact);
            } else {
                $user_info = $this->proposal_model->check_by(array('company' => $data['client_id']), 'tbl_account_details');
                if (!empty($user_info)) {
                    $notifyUser = array($user_info->user_id);
                }
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => $description,
                        'link' => 'client/proposals/index/proposals_details/' . $proposals_id,
                        'value' => $data['reference_no'],
                    ));
                }
            }
            show_notification($notifyUser);
        }

        // ---------- Message ----------
        $type = "success";
        $message = $msg;
        set_message($type, $message);
    }

    redirect('admin/proposals/index/proposals_details/' . $proposals_id);
}


    public function createproposal($action = NULL, $id = NULL, $item_id = NULL)
    {
        $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        if (!empty($item_id)) {
            $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
            if (!empty($can_edit)) {
                $data['item_info'] = $this->proposal_model->check_by(array('proposals_items_id' => $item_id), 'tbl_proposals_items');
            }
        }
        if ($action == 'edit_proposals') {
            $data['active'] = 2;
            $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
            if (!empty($can_edit)) {
                $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');
            }
        } else if ($action == 'client' || $action == 'leads') {
            $data['module_id'] = $id;
            $data['module'] = $action;
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }

        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }
        // get all proposals
        $data['all_proposals_info'] = $this->proposal_model->get_proposals($filterBy);


        $data['title'] = lang('proposals'); //Page title       
        $subview = 'createproposal';

        $data['subview'] = $this->load->view('admin/proposals/' . $subview, $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

     

    
    
     public function edit($proposal_id)
{
    $this->load->model('proposal_model');
    $data['proposal'] = $this->proposal_model->getProposal($proposal_id);

    if (!$data['proposal']) {
        show_404();
    }

      $subview = 'createnewproposal';
      
    $this->load->view('admin/proposals/edit', $data);
            $this->load->view('admin/_layout_main', $data); //page load

}

 public function get_all_leads() {
        $query = $this->db->select('leads_id, lead_name')
                          ->from('tbl_leads')
                          ->order_by('lead_name', 'ASC')
                          ->get();
        return $query->result();
    }


 public function search()
{
    $query = $this->input->post('query');
    $category_location_id = $this->input->post('category_location_id'); 
    $main_category_id = $this->input->post('main_category_id'); 
    $session = $this->input->post('session'); 

    // ✅ Dynamic table decide
    $table = 'tbl_saved_items'; // default

    if (!empty($session) && $session == '2025') {
        $table = 'tbl_saved_items_2025';
    }

    $this->db->select('si.*, 
        main_cat.category_name as main_category_name, 
        sub_cat.category_name as sub_category_name,
        loc.category_location as location_name');

    $this->db->from($table . ' si'); // ✅ dynamic table

    $this->db->join('tbl_category main_cat', 'main_cat.category_id = si.main_category_id', 'left');
    $this->db->join('tbl_category sub_cat', 'sub_cat.category_id = si.sub_category_id', 'left');
    $this->db->join('tbl_category_location loc', 'loc.category_location_id = si.category_location_id', 'left');

    $this->db->where('si.product_type', 'Main Product');

    // search
    if (!empty($query)) {
        $this->db->group_start();
        $this->db->like('si.item_name', $query);
        $this->db->or_like('si.code', $query);
        $this->db->group_end();
    }

    // location filter
    if (!empty($category_location_id)) {
        $this->db->where('si.category_location_id', $category_location_id);
    }

    // main category filter
    if (!empty($main_category_id)) {
        $this->db->where('si.main_category_id', $main_category_id);
    }

    $result = $this->db->get()->result_array();
    echo json_encode($result);
}
 


    public function get_subcategories()
    {
        $main_category_id = $this->input->post('main_category_id');
        $this->db->where('main_category_id', $main_category_id);
    // $this->db->where('status', 1);
        $result = $this->db->get('tbl_category')->result();
    
        echo json_encode($result);
    }

    public function search_by_category()
    {
        $main_category_id = $this->input->post('main_category_id');
        $sub_category_id  = $this->input->post('sub_category_id');

        $this->db->select('si.*, 
                        main_cat.category_name as main_category_name, 
                        main_cat.category_name as main_category_name, 
                        location.category_location as location_name');
        $this->db->from('tbl_saved_items si');
        $this->db->join('tbl_category main_cat', 'main_cat.category_id = si.main_category_id', 'left');
        $this->db->join('tbl_category sub_cat', 'sub_cat.category_id = si.sub_category_id', 'left');
        $this->db->join('tbl_category_location location', 'location.category_location_id = si.category_location_id', 'left');

        if(!empty($main_category_id)){
            $this->db->where('si.main_category_id', $main_category_id);
        }
        if(!empty($sub_category_id)){
            $this->db->where('si.sub_category_id', $sub_category_id);
        }

        $result = $this->db->get()->result_array();
        echo json_encode($result);
    }


    public function get_related_items($session = NULL)
    {

     $session = $this->input->post('session');
      $table = 'tbl_saved_items'; // default

if (!empty($session) && $session == 2025) {
    $table = 'tbl_saved_items_2025';
    // print_r( $table );
    // exit();
}

         $mainId = $this->input->post('main_id');

        // 1. Fetch related items with category & subcategory names
        $this->db->select('si.*, c.category_name as category_name, sc.category_name as sub_category_name');
        $this->db->from($table . ' si'); // ✅ dynamic table use
        $this->db->join('tbl_product_category c', 'c.product_category_id = si.product_category_id', 'left');
        $this->db->join('tbl_product_category sc', 'sc.product_category_id = si.product_sub_category_id', 'left');
        $this->db->where('si.main_product_id', $mainId);
        $relatedItems = $this->db->get()->result();

        // 2. Check association table
        $this->db->where('main_items_id', $mainId);
        $assoc = $this->db->get('tbl_associate_item')->row();

        $selectedIds = [];
        if ($assoc && !empty($assoc->associated_items)) {
            $selectedIds = explode(",", $assoc->associated_items); // e.g. [16,12,17]
        }
    
        // 3. Prepare grouped result
        $result = [];
        foreach ($relatedItems as $item) {
            $cat = $item->category_name ?: "Uncategorized";
            $sub = $item->sub_category_name ?: "General";
        
            if (!isset($result[$cat])) {
                $result[$cat] = [];
            }
            if (!isset($result[$cat][$sub])) {
                $result[$cat][$sub] = [];
            }

            $result[$cat][$sub][] = [
                "saved_items_id" => $item->saved_items_id,
                "item_name"      => $item->item_name,
                "item_desc"      => $item->item_desc,
                "unit_type"      => $item->unit_type,
                "unit_cost"      => $item->unit_cost,
                "item_tax_rate"  => $item->item_tax_rate,
                "selected"       => in_array($item->saved_items_id, $selectedIds) ? 1 : 0
            ];
        }

        echo json_encode($result);
    }

    public function get_related_items_for_proposal_item($proposalId, $mainId, $floorId, $session = NULL)
{

 
    // ✅ Dynamic table decide
  $table = 'tbl_saved_items'; // default

if (!empty($session) && $session == 2025) {
    $table = 'tbl_saved_items_2025';
    // print_r( $table );
    // exit();
}

// 1. Fetch related items with category & subcategory names
$this->db->select('si.*, c.category_name as category_name, sc.category_name as sub_category_name');
$this->db->from($table . ' si'); // ✅ dynamic table use
$this->db->join('tbl_product_category c', 'c.product_category_id = si.product_category_id', 'left');
$this->db->join('tbl_product_category sc', 'sc.product_category_id = si.product_sub_category_id', 'left');
$this->db->where('si.main_product_id', $mainId);

$relatedItems = $this->db->get()->result();

    // 2. Default associated IDs (from tbl_associate_item)
    $this->db->where('main_items_id', $mainId);
    $assoc = $this->db->get('tbl_associate_item')->row();

    $selectedIds = [];
    if ($assoc && !empty($assoc->associated_items)) {
        $selectedIds = explode(",", $assoc->associated_items);
    }

    // 3. Saved proposal-specific addons
    $proposalAddonIds = [];
    if ($proposalId) {
        $this->db->select('addon_id');
        $this->db->from('proposal_item_addons');
        $this->db->where('floor_id', $floorId);
        $this->db->where('proposal_id', $proposalId);
        $this->db->where('main_item_id', $mainId); // ensure linked to this item
        
        $addons = $this->db->get()->result();
        foreach ($addons as $a) {
            $proposalAddonIds[] = $a->addon_id;
        }
		
		 if (!empty($proposalAddonIds)) {
            $allSelectedIds = $proposalAddonIds;
        } else {
            $allSelectedIds = array_unique(array_merge($selectedIds, $proposalAddonIds));
        }
    }

    // 4. Merge both (default + saved)
    //$allSelectedIds = array_unique(array_merge($selectedIds, $proposalAddonIds));
   // $allSelectedIds = $proposalAddonIds;

    // 5. Prepare grouped result
    $result = [];
    foreach ($relatedItems as $item) {
        $cat = $item->category_name ?: "Uncategorized";
        $sub = $item->sub_category_name ?: "General";

        if (!isset($result[$cat])) {
            $result[$cat] = [];
        }
        if (!isset($result[$cat][$sub])) {
            $result[$cat][$sub] = [];
        }

        $result[$cat][$sub][] = [
            "saved_items_id" => $item->saved_items_id,
            "item_name"      => $item->item_name,
            "item_desc"      => $item->item_desc,
            "unit_cost"      => $item->unit_cost,
            "item_tax_rate"  => $item->item_tax_rate,
            "selected"       => in_array($item->saved_items_id, $allSelectedIds) ? 1 : 0
        ];
    }

    echo json_encode($result);
}


    //    public function get_related_items_for_proposal_item($mainId)
    // {
    //     // $mainId = $this->input->post('main_id');

    //     // 1. Fetch related items with category & subcategory names
    //     $this->db->select('si.*, c.category_name as category_name, sc.category_name as sub_category_name');
    //     $this->db->from('tbl_saved_items si');
    //     $this->db->join('tbl_product_category c', 'c.product_category_id = si.product_category_id', 'left');
    //     $this->db->join('tbl_product_category sc', 'sc.product_category_id = si.product_sub_category_id', 'left');
    //     $this->db->where('si.main_product_id', $mainId);
    //     $relatedItems = $this->db->get()->result();

    //     // 2. Check association table
    //     $this->db->where('main_items_id', $mainId);
    //     $assoc = $this->db->get('tbl_associate_item')->row();

    //     $selectedIds = [];
    //     if ($assoc && !empty($assoc->associated_items)) {
    //         $selectedIds = explode(",", $assoc->associated_items); // e.g. [16,12,17]
    //     }
    
    //     // 3. Prepare grouped result
    //     $result = [];
    //     foreach ($relatedItems as $item) {
    //         $cat = $item->category_name ?: "Uncategorized";
    //         $sub = $item->sub_category_name ?: "General";
        
    //         if (!isset($result[$cat])) {
    //             $result[$cat] = [];
    //         }
    //         if (!isset($result[$cat][$sub])) {
    //             $result[$cat][$sub] = [];
    //         }

    //         $result[$cat][$sub][] = [
    //             "saved_items_id" => $item->saved_items_id,
    //             "item_name"      => $item->item_name,
    //             "item_desc"      => $item->item_desc,
    //             "unit_cost"      => $item->unit_cost,
    //             "item_tax_rate"  => $item->item_tax_rate,
    //             "selected"       => in_array($item->saved_items_id, $selectedIds) ? 1 : 0
    //         ];
    //     }

    //     echo json_encode($result);
    // }


    public function insert_items($proposals_id)
    {
        $edited = can_action('140', 'edited');
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $proposals_id));
        if (!empty($can_edit) && !empty($edited)) {
            $data['proposals_id'] = $proposals_id;
            $data['modal_subview'] = $this->load->view('admin/proposals/_modal_insert_items', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function add_insert_items($proposals_id)
    {
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $proposals_id));
        $edited = can_action('140', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $saved_items_id = $this->input->post('saved_items_id', TRUE);
            if (!empty($saved_items_id)) {
                foreach ($saved_items_id as $v_items_id) {
                    $items_info = $this->proposal_model->check_by(array('saved_items_id' => $v_items_id), 'tbl_saved_items');
                    $tax_info = json_decode($items_info->tax_rates_id);
                    $tax_name = array();
                    if (!empty($tax_info)) {
                        foreach ($tax_info as $v_tax) {
                            $all_tax = $this->db->where('tax_rates_id', $v_tax)->get('tbl_tax_rates')->row();
                            $tax_name[] = $all_tax->tax_rate_name . '|' . $all_tax->tax_rate_percent;
                        }
                    }
                    if (!empty($tax_name)) {
                        $tax_name = $tax_name;
                    } else {
                        $tax_name = array();
                    }

                    $data['quantity'] = 1;
                    $data['proposals_id'] = $proposals_id;
                    $data['item_name'] = $items_info->item_name;
                    $data['item_desc'] = $items_info->item_desc;
                    $data['hsn_code'] = $items_info->hsn_code;
                    $data['unit_cost'] = $items_info->unit_cost;
                    $data['item_tax_rate'] = '0.00';
                    $data['item_tax_name'] = json_encode($tax_name);
                    $data['item_tax_total'] = $items_info->item_tax_total;
                    $data['total_cost'] = $items_info->unit_cost;

                    $this->proposal_model->_table_name = 'tbl_proposals_items';
                    $this->proposal_model->_primary_key = 'proposals_items_id';
                    $items_id = $this->proposal_model->save($data);
                    $action = 'activity_proposal_items_added';
                    $msg = lang('proposals_item_save');
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'proposals',
                        'module_field_id' => $items_id,
                        'activity' => $action,
                        'icon' => 'fa-shopping-cart',
                        'link' => 'admin/proposals/index/proposals_details/' . $proposals_id,
                        'value1' => $items_info->item_name
                    );
                    $this->proposal_model->_table_name = 'tbl_activities';
                    $this->proposal_model->_primary_key = 'activities_id';
                    $this->proposal_model->save($activity);
                }
                $type = "success";
                $this->update_invoice_tax($saved_items_id, $proposals_id);
            } else {
                $type = "error";
                $msg = 'Please Select a items';
            }
            $message = $msg;
            set_message($type, $message);
            redirect('admin/proposals/index/proposals_details/' . $proposals_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    function update_invoice_tax($saved_items_id, $proposals_id)
    {

        $invoice_info = $this->proposal_model->check_by(array('proposals_id' => $proposals_id), 'tbl_proposals');
        $tax_info = json_decode($invoice_info->total_tax);

        $tax_name = $tax_info->tax_name;
        $total_tax = $tax_info->total_tax;
        $invoice_tax = array();
        if (!empty($tax_name)) {
            foreach ($tax_name as $t_key => $v_tax_info) {
                array_push($invoice_tax, array('tax_name' => $v_tax_info, 'total_tax' => $total_tax[$t_key]));
            }
        }
        $all_tax_info = array();
        if (!empty($saved_items_id)) {
            foreach ($saved_items_id as $v_items_id) {
                $items_info = $this->proposal_model->check_by(array('saved_items_id' => $v_items_id), 'tbl_saved_items');

                $tax_info = json_decode($items_info->tax_rates_id);
                if (!empty($tax_info)) {
                    foreach ($tax_info as $v_tax) {
                        $all_tax = $this->db->where('tax_rates_id', $v_tax)->get('tbl_tax_rates')->row();
                        array_push($all_tax_info, array('tax_name' => $all_tax->tax_rate_name . '|' . $all_tax->tax_rate_percent, 'total_tax' => $items_info->unit_cost / 100 * $all_tax->tax_rate_percent));
                    }
                }
            }
        }
        if (!empty($invoice_tax) && is_array($invoice_tax) && !empty($all_tax_info)) {
            $all_tax_info = array_merge($all_tax_info, $invoice_tax);
        }

        $results = array();
        foreach ($all_tax_info as $value) {
            if (!isset($results[$value['tax_name']])) {
                $results[$value['tax_name']] = 0;
            }
            $results[$value['tax_name']] += $value['total_tax'];
        }
        if (!empty($results)) {
            foreach ($results as $key => $value) {
                $structured_results['tax_name'][] = $key;
                $structured_results['total_tax'][] = $value;
            }
            $invoice_data['tax'] = array_sum($structured_results['total_tax']);
            $invoice_data['total_tax'] = json_encode($structured_results);

            $this->proposal_model->_table_name = 'tbl_proposals';
            $this->proposal_model->_primary_key = 'proposals_id';
            $this->proposal_model->save($invoice_data, $proposals_id);
        }
        return true;
    }

    public function add_item($id = NULL)
    {
        $data = $this->proposal_model->array_from_post(array('proposals_id', 'item_order'));
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $data['proposals_id']));
        $edited = can_action('140', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $quantity = $this->input->post('quantity', TRUE);
            $array_data = $this->proposal_model->array_from_post(array('item_name', 'item_desc', 'item_tax_rate', 'unit_cost'));
            if (!empty($quantity)) {
                foreach ($quantity as $key => $value) {
                    $data['quantity'] = $value;
                    $data['item_name'] = $array_data['item_name'][$key];
                    $data['item_desc'] = $array_data['item_desc'][$key];
                    $data['unit_cost'] = $array_data['unit_cost'][$key];
                    $data['item_tax_rate'] = $array_data['item_tax_rate'][$key];
                    $sub_total = $data['unit_cost'] * $data['quantity'];

                    $data['item_tax_total'] = ($data['item_tax_rate'] / 100) * $sub_total;
                    $data['total_cost'] = $sub_total + $data['item_tax_total'];

                    // get all client
                    $this->proposal_model->_table_name = 'tbl_proposals_items';
                    $this->proposal_model->_primary_key = 'proposals_items_id';
                    if (!empty($id)) {
                        $proposals_items_id = $id;
                        $this->proposal_model->save($data, $id);
                        $action = ('activity_proposals_items_updated');
                    } else {
                        $proposals_items_id = $this->proposal_model->save($data);
                        $action = 'activity_proposals_items_added';
                    }
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'proposals',
                        'module_field_id' => $proposals_items_id,
                        'activity' => $action,
                        'icon' => 'fa-shopping-cart',
                        'value1' => $data['item_name']
                    );
                    $this->proposal_model->_table_name = 'tbl_activities';
                    $this->proposal_model->_primary_key = 'activities_id';
                    $this->proposal_model->save($activity);
                }
            }
            // messages for user
            $type = "success";
            $message = lang('proposals_item_save');
            set_message($type, $message);
            redirect('admin/proposals/index/proposals_details/' . $data['proposals_id']);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function clone_proposal($proposals_id)
    {
        $edited = can_action('140', 'edited');
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $proposals_id));
        if (!empty($can_edit) && !empty($edited)) {
            $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $proposals_id), 'tbl_proposals');
            // get all client
            $this->proposal_model->_table_name = 'tbl_client';
            $this->proposal_model->_order_by = 'client_id';
            $data['all_client'] = $this->proposal_model->get();

            $data['modal_subview'] = $this->load->view('admin/proposals/_modal_clone_proposals', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function cloned_proposals($id)
    {
        $edited = can_action('140', 'edited');
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
        if (!empty($can_edit) && !empty($edited)) {
            if (config_item('increment_proposals_number') == 'FALSE') {
                $this->load->helper('string');
                $reference_no = config_item('proposal_prefix') . ' ' . random_string('nozero', 6);
            } else {
                $reference_no = config_item('proposal_prefix') . ' ' . $this->proposal_model->generate_proposal_number();
            }

            $invoice_info = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');
            $module = $this->input->post('module', true);
            if (empty($module)) {
                $module = $invoice_info->module;
                $module_id = $invoice_info->module_id;
                $currency = $invoice_info->currency;
            } else {
                if ($module == 'leads') {
                    $module_id = $this->input->post('leads_id', true);
                    $currency = $this->input->post('currency', true);
                } else {
                    $module_id = $this->input->post('client_id', true);
                    $currenc = $this->proposal_model->client_currency_symbol($module_id);
                    $currency = $currenc->code;
                }
            }
            // save into invoice table
            $new_invoice = array(
                'reference_no' => $reference_no,
                'subject' => $invoice_info->subject,
                'module' => $module,
                'module_id' => $module_id,
                'proposal_date' => date('Y-m-d', strtotime($this->input->post('proposal_date', TRUE))),
                'proposal_month' => date('Y-m', strtotime($this->input->post('proposal_date', TRUE))),
                'proposal_year' => date('Y', strtotime($this->input->post('proposal_date', TRUE))),
                'due_date' => date('Y-m-d', strtotime($this->input->post('due_date', TRUE))),
                'notes' => $invoice_info->notes,
                'tags' => $invoice_info->tags,
                'total_tax' => $invoice_info->total_tax,
                'tax' => $invoice_info->tax,
                'discount_type' => $invoice_info->discount_type,
                'discount_percent' => $invoice_info->discount_percent,
                'user_id' => $invoice_info->user_id,
                'adjustment' => $invoice_info->adjustment,
                'discount_total' => $invoice_info->discount_total,
                'show_quantity_as' => $invoice_info->show_quantity_as,
                'currency' => $currency,
                'status' => $invoice_info->status,
                'date_sent' => $invoice_info->date_sent,
                'emailed' => $invoice_info->emailed,
                'show_client' => $invoice_info->show_client,
                'convert' => $invoice_info->convert,
                'convert_module' => $invoice_info->convert_module,
                'convert_module_id' => $invoice_info->convert_module_id,
                'converted_date' => $invoice_info->converted_date,
                'permission' => $invoice_info->permission,
            );
            $this->proposal_model->_table_name = "tbl_proposals"; //table name
            $this->proposal_model->_primary_key = "proposals_id";
            $new_invoice_id = $this->proposal_model->save($new_invoice);

            $invoice_items = $this->db->where('proposals_id', $id)->get('tbl_proposals_items')->result();

            if (!empty($invoice_items)) {
                foreach ($invoice_items as $new_item) {
                    $items = array(
                        'proposals_id' => $new_invoice_id,
                        'item_name' => $new_item->item_name,
                        'item_desc' => $new_item->item_desc,
                        'unit_cost' => $new_item->unit_cost,
                        'quantity' => $new_item->quantity,
                        'item_tax_rate' => $new_item->item_tax_rate,
                        'item_tax_name' => $new_item->item_tax_name,
                        'item_tax_total' => $new_item->item_tax_total,
                        'total_cost' => $new_item->total_cost,
                        'unit' => $new_item->unit,
                        'order' => $new_item->order,
                    );
                    $this->proposal_model->_table_name = "tbl_proposals_items"; //table name
                    $this->proposal_model->_primary_key = "proposals_items_id";
                    $this->proposal_model->save($items);
                }
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'proposals',
                'module_field_id' => $new_invoice_id,
                'activity' => ('activity_clone_proposal'),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/proposals/index/proposals_details/' . $new_invoice_id,
                'value1' => ' from ' . $invoice_info->reference_no . ' to ' . $reference_no,
            );
            // Update into tbl_project
            $this->proposal_model->_table_name = "tbl_activities"; //table name
            $this->proposal_model->_primary_key = "activities_id";
            $this->proposal_model->save($activities);

            // messages for user
            $type = "success";
            $message = lang('proposals_created');
            set_message($type, $message);
            redirect('admin/proposals/index/proposals_details/' . $new_invoice_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function change_status($action, $id)
    {
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
        $edited = can_action('140', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $where = array('proposals_id' => $id);
            if ($action == 'hide') {
                $data = array('show_client' => 'No');
            } elseif ($action == 'show') {
                $data = array('show_client' => 'Yes');
            } elseif ($action == 'sent') {
                $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()), 'status' => 'sent');
            } elseif (!empty($action)) {
                $data = array('status' => $action);
            } else {
                $data = array('show_client' => 'Yes');
            }
            $this->proposal_model->set_action($where, $data, 'tbl_proposals');
            // messages for user
            $type = "success";
            $message = lang('proposals_status', $action);
            set_message($type, $message);
            redirect('admin/proposals/index/proposals_details/' . $id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function delete($action, $proposals_id, $item_id = NULL)
    {
        $can_delete = $this->proposal_model->can_action('tbl_proposals', 'delete', array('proposals_id' => $proposals_id));
        $deleted = can_action('140', 'deleted');
        if (!empty($can_delete) && !empty($deleted)) {
            if ($action == 'delete_item') {
                $this->proposal_model->_table_name = 'proposals';
                $this->proposal_model->_primary_key = 'proposals_items_id';
                $this->proposal_model->delete($item_id);
            } elseif ($action == 'delete_proposals') {

                $this->proposal_model->_table_name = 'proposals';
                $this->proposal_model->delete_multiple(array('id' => $proposals_id));

                $this->proposal_model->_table_name = 'tbl_reminders';
                $this->proposal_model->delete_multiple(array('module' => 'proposal', 'module_id' => $proposals_id));

                $this->proposal_model->_table_name = 'id';
                $this->proposal_model->_primary_key = 'id';
                $this->proposal_model->delete($proposals_id);
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'proposals',
                'module_field_id' => $proposals_id,
                'activity' => ('activity_' . $action),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/proposals/index/proposals_details/' . $proposals_id,
                'value1' => $action
            );

          
            $type = 'success';

            if ($action == 'delete_item') {
                $text = lang('proposals_item_deleted');
                echo json_encode(array("status" => $type, 'message' => $text));
                exit();
            } else {
                $text = lang('proposals_deleted');
                echo json_encode(array("status" => $type, 'message' => $text));
                exit();
            }
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('there_in_no_value')));
            exit();
        }
    }

    public function send_proposals_email($proposals_id, $row = null)
    {
        if (!empty($row)) {
            $email_template = email_templates(array('email_group' => 'proposal_email'));
            $proposals_info = $this->proposal_model->check_by(array('proposals_id' => $proposals_id), 'tbl_proposals');
            if ($proposals_info->module == 'client') {
                $client_info = $this->proposal_model->check_by(array('client_id' => $proposals_info->module_id), 'tbl_client');
                $client = $client_info->name;
                $currency = $this->proposal_model->client_currency_symbol($proposals_info->module_id);
                $email_template = email_templates(array('email_group' => 'proposal_email'), $proposals_info->module_id);
            } else if ($proposals_info->module == 'leads') {
                $client_info = $this->proposal_model->check_by(array('leads_id' => $proposals_info->module_id), 'tbl_leads');
                $client = $client_info->lead_name;
                $currency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
            } else {
                $client = '-';
                $currency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
            }

            $amount = $this->proposal_model->proposal_calculation('total', $proposals_info->proposals_id);
            $currency = $currency->code;
            $message = $email_template->template_body;
            $ref = $proposals_info->reference_no;
            $subject = $email_template->subject;
        } else {
            $message = $this->input->post('message', TRUE);
            $ref = $this->input->post('ref', TRUE);
            $subject = $this->input->post('subject', TRUE);
            $client = $this->input->post('client_name', TRUE);
            $amount = $this->input->post('amount', true);
            $currency = $this->input->post('currency', TRUE);
        }

        $client_name = str_replace("{CLIENT}", $client, $message);
        $Ref = str_replace("{PROPOSAL_REF}", $ref, $client_name);
        $Amount = str_replace("{AMOUNT}", $amount, $Ref);
        $Currency = str_replace("{CURRENCY}", $currency, $Amount);
        $link = str_replace("{PROPOSAL_LINK}", base_url() . 'frontend/proposals/' . url_encode($proposals_id), $Currency);
        $message = str_replace("{SITE_NAME}", config_item('company_name'), $link);

        $this->send_email_proposals($proposals_id, $message, $subject); // Email proposals

        $data = array('status' => 'sent', 'emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->proposal_model->_table_name = 'tbl_proposals';
        $this->proposal_model->_primary_key = 'proposals_id';
        $this->proposal_model->save($data, $proposals_id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'proposals',
            'module_field_id' => $proposals_id,
            'activity' => 'activity_proposals_sent',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/proposals/index/proposals_details/' . $proposals_id,
            'value1' => $ref
        );
        $this->proposal_model->_table_name = 'tbl_activities';
        $this->proposal_model->_primary_key = 'activities_id';
        $this->proposal_model->save($activity);

        $type = 'success';
        $text = lang('proposals_email_sent');
        set_message($type, $text);
        redirect('admin/proposals/index/proposals_details/' . $proposals_id);
    }

    function send_email_proposals($proposals_id, $message, $subject)
    {
        $proposals_info = $this->proposal_model->check_by(array('proposals_id' => $proposals_id), 'tbl_proposals');
        if ($proposals_info->module == 'client') {
            $client_info = $this->proposal_model->check_by(array('client_id' => $proposals_info->module_id), 'tbl_client');
            $email = $client_info->email;
        } else if ($proposals_info->module == 'leads') {
            $client_info = $this->proposal_model->check_by(array('leads_id' => $proposals_info->module_id), 'tbl_leads');
            $email = $client_info->email;
        } else {
            $email = '-';
        }
        $recipient = $email;

        $data['message'] = $message;

        $message = $this->load->view('email_template', $data, TRUE);
        $params = array(
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message
        );
        $params['resourceed_file'] = 'uploads/' . slug_it(lang('proposals') . '_pdf_' . $proposals_info->reference_no) . '.pdf';
        $params['resourcement_url'] = base_url() . 'uploads/' . slug_it(lang('proposals') . '_pdf_' . $proposals_info->reference_no) . '.pdf';

        $this->attach_pdf($proposals_id);

        $this->proposal_model->send_email($params);
        //Delete estimate in tmp folder
        if (is_file('uploads/' . slug_it(lang('proposals') . '_pdf_' . $proposals_info->reference_no) . '.pdf')) {
            unlink('uploads/' . slug_it(lang('proposals') . '_pdf_' . $proposals_info->reference_no) . '.pdf');
        }
        // send notification to client
        if ($proposals_info->module == 'client') {
            if (!empty($client_info->primary_contact)) {
                $notifyUser = array($client_info->primary_contact);
            } else {
                $user_info = $this->proposal_model->check_by(array('company' => $proposals_info->module_id), 'tbl_account_details');
                if (!empty($user_info)) {
                    $notifyUser = array($user_info->user_id);
                }
            }
            if (!empty($notifyUser)) {
                foreach ($notifyUser as $v_user) {
                    if ($v_user != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $v_user,
                            'icon' => 'shopping-cart',
                            'description' => 'not_email_send_alert',
                            'link' => 'client/proposals/index/proposals_details/' . $proposals_id,
                            'value' => lang('estimate') . ' ' . $proposals_info->reference_no,
                        ));
                    }
                }
                show_notification($notifyUser);
            }
        }
    }

    public function attach_pdf($id)
    {
        $this->proposals_details($id, 'attach');
    }

    function proposals_email($proposals_id)
    {
        $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $proposals_id), 'tbl_proposals');
        $proposals_info = $data['proposals_info'];
        $client_info = $this->proposal_model->check_by(array('client_id' => $data['proposals_info']->client_id), 'tbl_client');

        $recipient = $client_info->email;

        $message = $this->load->view('admin/proposals/proposals_pdf', $data, TRUE);

        $data['message'] = $message;

        $message = $this->load->view('email_template', $data, TRUE);
        $params = array(
            'recipient' => $recipient,
            'subject' => '[ ' . config_item('company_name') . ' ]' . ' New proposals' . ' ' . $data['proposals_info']->reference_no,
            'message' => $message
        );
        $params['resourceed_file'] = '';

        $this->proposal_model->send_email($params);

        $data = array('status' => 'sent', 'emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->proposal_model->_table_name = 'tbl_proposals';
        $this->proposal_model->_primary_key = 'proposals_id';
        $this->proposal_model->save($data, $proposals_id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'proposals',
            'module_field_id' => $proposals_id,
            'activity' => 'activity_proposals_sent',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/proposals/index/proposals_details/' . $proposals_id,
            'value1' => $proposals_info->reference_no
        );
        $this->proposal_model->_table_name = 'tbl_activities';
        $this->proposal_model->_primary_key = 'activities_id';
        $this->proposal_model->save($activity);

        // send notification to client
        if (!empty($client_info->primary_contact)) {
            $notifyUser = array($client_info->primary_contact);
        } else {
            $user_info = $this->proposal_model->check_by(array('company' => $proposals_info->client_id), 'tbl_account_details');
            if (!empty($user_info)) {
                $notifyUser = array($user_info->user_id);
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'not_email_send_alert',
                        'link' => 'client/proposals/index/proposals_details/' . $proposals_id,
                        'value' => lang('estimate') . ' ' . $proposals_info->reference_no,
                    ));
                }
            }
            show_notification($notifyUser);
        }

        $type = 'success';
        $text = lang('proposals_email_sent');
        set_message($type, $text);
        redirect('admin/proposals/index/proposals_details/' . $proposals_id);
    }

    public function convert_to($type, $id)
    {

        $data['title'] = lang('convert') . ' ' . lang($type);
        $edited = can_action('140', 'edited');
        $can_edit = $this->proposal_model->can_action('tbl_proposals', 'edit', array('proposals_id' => $id));
        if (!empty($can_edit) && !empty($edited)) {
            // get all client
            $this->proposal_model->_table_name = 'tbl_client';
            $this->proposal_model->_order_by = 'client_id';
            $data['all_client'] = $this->proposal_model->get();
            // get permission user
            $data['permission_user'] = $this->proposal_model->all_permission_user('140');

            $data['proposals_info'] = $this->proposal_model->check_by(array('proposals_id' => $id), 'tbl_proposals');

            $data['modal_subview'] = $this->load->view('admin/proposals/convert_to_' . $type, $data, FALSE);
            $this->load->view('admin/_layout_modal_large', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function converted_to_invoice($proposal_id)
    {
        $data = $this->proposal_model->array_from_post(array('reference_no', 'client_id', 'project_id', 'warehouse_id', 'discount_type', 'discount_percent', 'user_id', 'adjustment', 'discount_total', 'show_quantity_as'));

        $all_payment = get_result('tbl_online_payment');
        foreach ($all_payment as $payment) {
            $allow_gateway = 'allow_' . slug_it(strtolower($payment->gateway_name));
            $gateway_status = slug_it(strtolower($payment->gateway_name)) . '_status';
            if (config_item($gateway_status) == 'active') {
                $data[$allow_gateway] = ($this->input->post($allow_gateway) == 'Yes') ? 'Yes' : 'No';
            }
        }

        $data['client_visible'] = ($this->input->post('client_visible') == 'Yes') ? 'Yes' : 'No';
        $data['invoice_date'] = date('Y-m-d', strtotime($this->input->post('invoice_date', TRUE)));
        if (empty($data['invoice_date'])) {
            $data['invoice_date'] = date('Y-m-d');
        }
        if (empty($data['discount_total'])) {
            $data['discount_total'] = 0;
        }
        $data['invoice_year'] = date('Y', strtotime($this->input->post('invoice_date', TRUE)));
        $data['invoice_month'] = date('Y-m', strtotime($this->input->post('invoice_date', TRUE)));
        $data['due_date'] = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
        $data['notes'] = $this->input->post('notes', TRUE);
        $tax['tax_name'] = $this->input->post('total_tax_name', TRUE);
        $tax['total_tax'] = $this->input->post('total_tax', TRUE);
        $data['total_tax'] = json_encode($tax);
        $i_tax = 0;
        if (!empty($tax['total_tax'])) {
            foreach ($tax['total_tax'] as $v_tax) {
                $i_tax += $v_tax;
            }
        }
        $data['tax'] = $i_tax;
        $save_as_draft = $this->input->post('save_as_draft', TRUE);
        if (!empty($save_as_draft)) {
            $data['status'] = 'draft';
        }
        $currency = $this->proposal_model->client_currency_symbol($data['client_id']);
        if (!empty($currency->code)) {
            $curren = $currency->code;
        } else {
            $curren = config_item('default_currency');
        }
        $data['currency'] = $curren;

        $permission = $this->input->post('permission', true);
        if (!empty($permission)) {
            if ($permission == 'everyone') {
                $assigned = 'all';
            } else {
                $assigned_to = $this->proposal_model->array_from_post(array('assigned_to'));
                if (!empty($assigned_to['assigned_to'])) {
                    foreach ($assigned_to['assigned_to'] as $assign_user) {
                        $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                    }
                }
            }
            if (!empty($assigned)) {
                if ($assigned != 'all') {
                    $assigned = json_encode($assigned);
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;
        } else {
            set_message('error', lang('assigned_to') . ' Field is required');
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

        // get all client
        $this->proposal_model->_table_name = 'tbl_invoices';
        $this->proposal_model->_primary_key = 'invoices_id';

        $invoice_id = $this->proposal_model->save($data);
        $recuring_frequency = $this->input->post('recuring_frequency', TRUE);

        if (!empty($recuring_frequency) && $recuring_frequency != 'none') {
            $recur_data = $this->proposal_model->array_from_post(array('recur_start_date', 'recur_end_date'));
            $recur_data['recuring_frequency'] = $recuring_frequency;
            $this->get_recuring_frequency($invoice_id, $recur_data); // set recurring
        }
        // save items
        $qty_calculation = config_item('qty_calculation_from_items');
        // save items
        $invoices_to_merge = $this->input->post('invoices_to_merge', TRUE);
        $cancel_merged_invoices = $this->input->post('cancel_merged_invoices', TRUE);
        if (!empty($invoices_to_merge)) {
            foreach ($invoices_to_merge as $inv_id) {
                if (empty($cancel_merged_invoices)) {
                    if (!empty($qty_calculation) && $qty_calculation == 'Yes') {
                        $all_items_info = $this->db->where('invoices_id', $inv_id)->get('tbl_items')->result();
                        if (!empty($all_items_info)) {
                            foreach ($all_items_info as $v_items) {
                                $this->proposal_model->return_items($v_items->saved_items_id, $v_items->quantity, $data['warehouse_id']);
                            }
                        }
                    }
                    $this->db->where('invoices_id', $inv_id);
                    $this->db->delete('tbl_invoices');

                    $this->db->where('invoices_id', $inv_id);
                    $this->db->delete('tbl_items');
                } else {
                    $mdata = array('status' => 'Cancelled');
                    $this->proposal_model->_table_name = 'tbl_invoices';
                    $this->proposal_model->_primary_key = 'invoices_id';
                    $this->proposal_model->save($mdata, $inv_id);
                }
            }
        }

        $removed_items = $this->input->post('removed_items', TRUE);
        if (!empty($removed_items)) {
            foreach ($removed_items as $r_id) {
                if ($r_id != 'undefined') {
                    if (!empty($qty_calculation) && $qty_calculation == 'Yes') {
                        $itemInfo = get_row('tbl_items', array('items_id' => $r_id));
                        $this->proposal_model->return_items($itemInfo->saved_items_id, $itemInfo->quantity, $data['warehouse_id']);
                    }

                    $this->db->where('items_id', $r_id);
                    $this->db->delete('tbl_items');
                }
            }
        }

        $itemsid = $this->input->post('items_id', TRUE);
        $items_data = $this->input->post('items', true);

        if (!empty($items_data)) {
            $index = 0;
            foreach ($items_data as $items) {
                $items['invoices_id'] = $invoice_id;
                $tax = 0;
                if (!empty($items['taxname'])) {
                    foreach ($items['taxname'] as $tax_name) {
                        $tax_rate = explode("|", $tax_name);
                        $tax += $tax_rate[1];
                    }
                    $items['item_tax_name'] = $items['taxname'];
                    unset($items['taxname']);
                    $items['item_tax_name'] = json_encode($items['item_tax_name']);
                }
                if (empty($items['saved_items_id'])) {
                    $items['saved_items_id'] = 0;
                }
                if (!empty($qty_calculation) && $qty_calculation == 'Yes') {
                    if (!empty($items['saved_items_id']) && $items['saved_items_id'] != 'undefined') {
                        $this->proposal_model->reduce_items($items['saved_items_id'], $items['quantity'], $data['warehouse_id']);
                    }
                }
                $price = $items['quantity'] * $items['unit_cost'];
                $items['item_tax_total'] = ($price / 100 * $tax);
                $items['total_cost'] = $price;
                // get all client
                $this->proposal_model->_table_name = 'tbl_items';
                $this->proposal_model->_primary_key = 'items_id';
                $this->proposal_model->save($items);
                $index++;
            }
        }

        $p_data = array('status' => 'accepted', 'convert' => 'Yes', 'convert_module' => 'invoice', 'convert_module_id' => $invoice_id);

        $this->proposal_model->_table_name = 'tbl_proposals';
        $this->proposal_model->_primary_key = 'proposals_id';
        $this->proposal_model->save($p_data, $proposal_id);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'proposals',
            'module_field_id' => $invoice_id,
            'activity' => 'convert_to_invoice_from_proposal',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/proposals/index/proposals_details/' . $proposal_id,
            'value1' => $data['reference_no']
        );
        $this->proposal_model->_table_name = 'tbl_activities';
        $this->proposal_model->_primary_key = 'activities_id';
        $this->proposal_model->save($activity);

        // send notification to client
        if (!empty($data['client_id'])) {
            $client_info = $this->proposal_model->check_by(array('client_id' => $data['client_id']), 'tbl_client');
            if (!empty($client_info->primary_contact)) {
                $notifyUser = array($client_info->primary_contact);
            } else {
                $user_info = $this->proposal_model->check_by(array('company' => $data['client_id']), 'tbl_account_details');
                if (!empty($user_info)) {
                    $notifyUser = array($user_info->user_id);
                }
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'proposal_convert_to_invoice',
                        'link' => 'client/invoice/manage_invoice/invoice_details/' . $invoice_id,
                        'value' => $data['reference_no'],
                    ));
                }
            }
            show_notification($notifyUser);
        }
        // messages for user
        $type = "success";
        $message = lang('convert_to_invoice') . ' ' . lang('successfully');
        set_message($type, $message);
        redirect('admin/proposals/index/proposals_details/' . $proposal_id);
    }

    function get_recuring_frequency($invoices_id, $recur_data)
    {
        $recur_days = $this->get_calculate_recurring_days($recur_data['recuring_frequency']);
        $due_date = $this->proposal_model->get_table_field('tbl_invoices', array('invoices_id' => $invoices_id), 'due_date');

        $next_date = date("Y-m-d", strtotime($due_date . "+ " . $recur_days . " days"));

        if ($recur_data['recur_end_date'] == '') {
            $recur_end_date = '0000-00-00';
        } else {
            $recur_end_date = date('Y-m-d', strtotime($recur_data['recur_end_date']));
        }
        $update_invoice = array(
            'recurring' => 'Yes',
            'recuring_frequency' => $recur_days,
            'recur_frequency' => $recur_data['recuring_frequency'],
            'recur_start_date' => date('Y-m-d', strtotime($recur_data['recur_start_date'])),
            'recur_end_date' => $recur_end_date,
            'recur_next_date' => $next_date
        );
        $this->proposal_model->_table_name = 'tbl_invoices';
        $this->proposal_model->_primary_key = 'invoices_id';
        $this->proposal_model->save($update_invoice, $invoices_id);
        return TRUE;
    }

    function get_calculate_recurring_days($recuring_frequency)
    {
        switch ($recuring_frequency) {
            case '7D':
                return 7;
                break;
            case '1M':
                return 31;
                break;
            case '3M':
                return 90;
                break;
            case '6M':
                return 182;
                break;
            case '1Y':
                return 365;
                break;
        }
    }

    public function converted_to_estimate($proposal_id)
    {
        $data = $this->proposal_model->array_from_post(array('reference_no', 'client_id', 'project_id', 'discount_type', 'discount_percent', 'user_id', 'adjustment', 'discount_total', 'show_quantity_as'));

        $data['client_visible'] = ($this->input->post('client_visible') == 'Yes') ? 'Yes' : 'No';
        $data['estimate_date'] = date('Y-m-d', strtotime($this->input->post('estimate_date', TRUE)));
        if (empty($data['estimate_date'])) {
            $data['estimate_date'] = date('Y-m-d');
        }
        if (empty($data['discount_total'])) {
            $data['discount_total'] = 0;
        }
        $data['estimate_year'] = date('Y', strtotime($this->input->post('estimate_date', TRUE)));
        $data['estimate_month'] = date('Y-m', strtotime($this->input->post('estimate_date', TRUE)));
        $data['due_date'] = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
        $data['notes'] = $this->input->post('notes', TRUE);
        $tax['tax_name'] = $this->input->post('total_tax_name', TRUE);
        $tax['total_tax'] = $this->input->post('total_tax', TRUE);
        $data['total_tax'] = json_encode($tax);
        $i_tax = 0;
        if (!empty($tax['total_tax'])) {
            foreach ($tax['total_tax'] as $v_tax) {
                $i_tax += $v_tax;
            }
        }
        $data['tax'] = $i_tax;
        $save_as_draft = $this->input->post('status', TRUE);
        if (!empty($save_as_draft)) {
            $data['status'] = $save_as_draft;
        } else {
            $data['status'] = 'pending';
        }
        $currency = $this->proposal_model->client_currency_symbol($data['client_id']);
        if (!empty($currency->code)) {
            $curren = $currency->code;
        } else {
            $curren = config_item('default_currency');
        }
        $data['currency'] = $curren;

        $permission = $this->input->post('permission', true);
        if (!empty($permission)) {
            if ($permission == 'everyone') {
                $assigned = 'all';
            } else {
                $assigned_to = $this->proposal_model->array_from_post(array('assigned_to'));
                if (!empty($assigned_to['assigned_to'])) {
                    foreach ($assigned_to['assigned_to'] as $assign_user) {
                        $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                    }
                }
            }
            if (!empty($assigned)) {
                if ($assigned != 'all') {
                    $assigned = json_encode($assigned);
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;
        } else {
            set_message('error', lang('assigned_to') . ' Field is required');
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/proposals');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
        // get all client
        $this->proposal_model->_table_name = 'tbl_estimates';
        $this->proposal_model->_primary_key = 'estimates_id';
        if (!empty($id)) {
            $estimates_id = $id;
            $this->proposal_model->save($data, $id);
        } else {
            $estimates_id = $this->proposal_model->save($data);
        }
        // save items
        $invoices_to_merge = $this->input->post('invoices_to_merge', TRUE);
        $cancel_merged_invoices = $this->input->post('cancel_merged_estimate', TRUE);
        if (!empty($invoices_to_merge)) {
            foreach ($invoices_to_merge as $inv_id) {
                if (empty($cancel_merged_invoices)) {
                    $this->db->where('estimates_id', $inv_id);
                    $this->db->delete('tbl_estimates');

                    $this->db->where('estimate_items_id', $inv_id);
                    $this->db->delete('tbl_estimate_items');
                } else {
                    $mdata = array('status' => 'cancelled');
                    $this->proposal_model->_table_name = 'tbl_estimates';
                    $this->proposal_model->_primary_key = 'estimates_id';
                    $this->proposal_model->save($mdata, $inv_id);
                }
            }
        }

        $removed_items = $this->input->post('removed_items', TRUE);
        if (!empty($removed_items)) {
            foreach ($removed_items as $r_id) {
                if ($r_id != 'undefined') {
                    $this->db->where('estimate_items_id', $r_id);
                    $this->db->delete('tbl_estimate_items');
                }
            }
        }

        $itemsid = $this->input->post('estimate_items_id', TRUE);
        $items_data = $this->input->post('items', true);

        if (!empty($items_data)) {
            $index = 0;
            foreach ($items_data as $items) {
                $items['estimates_id'] = $estimates_id;
                if (!empty($items['taxname'])) {
                    $tax = 0;
                    foreach ($items['taxname'] as $tax_name) {
                        $tax_rate = explode("|", $tax_name);
                        $tax += $tax_rate[1];
                    }
                    $price = $items['quantity'] * $items['unit_cost'];
                    $items['item_tax_total'] = ($price / 100 * $tax);
                    $items['total_cost'] = $price;

                    $items['item_tax_name'] = $items['taxname'];
                    unset($items['taxname']);
                    $items['item_tax_name'] = json_encode($items['item_tax_name']);
                }
                // get all client
                $this->proposal_model->_table_name = 'tbl_estimate_items';
                $this->proposal_model->_primary_key = 'estimate_items_id';
                if (!empty($itemsid[$index])) {
                    $items_id = $itemsid[$index];
                    $this->proposal_model->save($items, $items_id);
                } else {
                    $items_id = $this->proposal_model->save($items);
                }
                $index++;
            }
        }
        $p_data = array('status' => 'accepted', 'convert' => 'Yes', 'convert_module' => 'estimate', 'convert_module_id' => $estimates_id);
        $this->proposal_model->_table_name = 'tbl_proposals';
        $this->proposal_model->_primary_key = 'proposals_id';
        $this->proposal_model->save($p_data, $proposal_id);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'proposals',
            'module_field_id' => $estimates_id,
            'activity' => 'convert_to_estimate_from_proposal',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/proposals/index/proposals_details/' . $proposal_id,
            'value1' => $data['reference_no']
        );
        $this->proposal_model->_table_name = 'tbl_activities';
        $this->proposal_model->_primary_key = 'activities_id';
        $this->proposal_model->save($activity);

        // send notification to client
        if (!empty($data['client_id'])) {
            $client_info = $this->proposal_model->check_by(array('client_id' => $data['client_id']), 'tbl_client');
            if (!empty($client_info->primary_contact)) {
                $notifyUser = array($client_info->primary_contact);
            } else {
                $user_info = $this->proposal_model->check_by(array('company' => $data['client_id']), 'tbl_account_details');
                if (!empty($user_info)) {
                    $notifyUser = array($user_info->user_id);
                }
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'proposal_convert_to_estimate',
                        'link' => 'client/estimates/index/estimates_details/' . $estimates_id,
                        'value' => $data['reference_no'],
                    ));
                }
            }
            show_notification($notifyUser);
        }
        // messages for user
        $type = "success";
        $message = lang('convert_to_estimate') . ' ' . lang('successfully');
        set_message($type, $message);
        redirect('admin/proposals/index/proposals_details/' . $proposal_id);
    }


       public function get_lead_by_phone() {
        $phone = $this->input->post('phone');

        $lead = $this->db->select('leads_id, contact_name, phone, region')
                         ->from('tbl_leads')
                         ->where('phone', $phone)
                         ->where_not_in('lead_status_id', [5, 6])   // ⬅ Exclude status 5 & 6
                         ->get()
                         ->row();

        if ($lead) {
            echo json_encode([
                'status' => true,
                'lead'   => $lead
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'No lead found'
            ]);
        }
    }


     public function list($action = NULL, $id = NULL, $item_id = NULL)
    {
        $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }

        $data['title'] = lang('proposals'); //Page title       
  
        $this->proposal_model->_table_name = 'tbl_category_location';
        $this->proposal_model->_order_by = 'category_location';
        $data['category_location'] = $this->proposal_model->get_by(array('category_location_id >' => 0));
 
        $this->proposal_model->_table_name = 'tbl_category';
        $this->proposal_model->_order_by = 'category_name';
        $data['all_category'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id' => 0));
      
       
 
         $data['all_subcategory'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id !=' => 0));


         $data['leads'] = $this->db->select('*')
                                  ->from('tbl_leads')
                                  ->order_by('contact_name', 'ASC')
                                  ->get()
                                  ->result();
 
            if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposals');
                $this->db->where('id', $id);
                $data['proposals'] = $this->db->get()->result_array();
            // print_r($data['proposals']);
            }   
             
            
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floors');
                $this->db->where('proposal_id', $id);
                  $this->db->order_by('id', 'ASC'); // ✅ order floors by ID ascending
                $data['proposal_floors'] = $this->db->get()->result_array();
             }   

             if (!empty($id)) {
            $this->db->from('proposal_floors');
            $this->db->where('proposal_id', $id);
            
            $data['proposals_count'] = $this->db->count_all_results(); // ✅
            //print_r($data['proposals_count']);
        }
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floor_categories');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_floor_categories'] = $this->db->get()->result_array();
             }   
             
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_items');
                $this->db->where('proposal_id', $id);
 
                $data['proposal_items'] = $this->db->get()->result_array();
             }   
           //  print_r($data['proposal_items']);
                    if (!empty($id)) { 
                    $subview = 'createnewproposal';
                    } else {
                    $subview = 'createnewproposal_add';
                    }
        $data['proposal_id'] = $id;                
        $data['subview'] = $this->load->view('admin/proposals/' . $subview, $data, TRUE);
           
        $this->load->view('admin/_layout_main', $data); //page load
    }


    public function createnewproposal($action = NULL, $id = NULL, $item_id = NULL, $session = NULL)
    {
       // print_r($session);
        $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }

        $data['title'] = lang('proposals'); //Page title       
  
       $data['category_location'] = $this->db
    ->from('tbl_category_location')
    ->where('category_location_id >', 0)
    ->order_by('CASE WHEN set_order IS NULL OR set_order = "" THEN 1 ELSE 0 END', 'ASC', false)
    ->order_by('set_order', 'ASC', false)
    ->get()
    ->result();
	
        $this->proposal_model->_table_name = 'tbl_category';
        $this->proposal_model->_order_by = 'category_name';
        $data['all_category'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id' => 0));
      
       
 
         $data['proposals_info'] = get_row('proposals', array('id' => $id));
       
 
         $data['all_subcategory'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id !=' => 0));

                           

         $data['leads'] = $this->db->select('*')
                                  ->from('tbl_leads')
                                  ->order_by('contact_name', 'ASC')
                                  ->get()
                                  ->result();
 
            if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposals');
                $this->db->where('id', $id);
                $data['proposals'] = $this->db->get()->result_array();
            // print_r($data['proposals']);
            }   
             
            
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floors');
                $this->db->where('proposal_id', $id);
                  $this->db->order_by('id', 'ASC'); // ✅ order floors by ID ascending
                $data['proposal_floors'] = $this->db->get()->result_array();
             }   

             if (!empty($id)) {
            $this->db->from('proposal_floors');
            $this->db->where('proposal_id', $id);
            
            $data['proposals_count'] = $this->db->count_all_results(); // ✅
          //  print_r($data['proposals_count']);
        }
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floor_categories');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_floor_categories'] = $this->db->get()->result_array();
             }   
                 


$this->db->select('
    proposals.*, 
    tbl_leads.converted_client_id
');

$this->db->from('proposals');

$this->db->join(
    'tbl_leads', 
    'tbl_leads.leads_id = proposals.lead_id', 
    'left'
);

$this->db->where('proposals.id', $id); // dynamic id


 
    



$converted_client_id = $this->db->get()->row_array();

$data['converted_client_id'] = $converted_client_id['converted_client_id'] ?? 0;

if ($converted_client_id['converted_client_id'] > 0 && $session == 2025 ) {
    //  print_r('2025');
    // exit();
      if (!empty($id)) {
                    $this->db->select('proposal_items.*, COALESCE(tbl_saved_items_2025.unit_cost, proposal_items.price) AS unit_cost', false);
                    $this->db->from('proposal_items');
                    $this->db->join('tbl_saved_items_2025', 'tbl_saved_items_2025.saved_items_id = proposal_items.item_id', 'left');
                    $this->db->where('proposal_items.proposal_id', $id);
                    $data['proposal_items'] = $this->db->get()->result_array();
                }    

}

if ($converted_client_id['converted_client_id'] > 0 && $session == 2026 ) {
    //  print_r('2026');
    // exit();
      if (!empty($id)) {
                    $this->db->select('proposal_items.*, COALESCE(tbl_saved_items.unit_cost, proposal_items.price) AS unit_cost', false);
                    $this->db->from('proposal_items');
                    $this->db->join('tbl_saved_items', 'tbl_saved_items.saved_items_id = proposal_items.item_id', 'left');
                    $this->db->where('proposal_items.proposal_id', $id);
                    $data['proposal_items'] = $this->db->get()->result_array();
                }    

} if (empty($converted_client_id['converted_client_id'])) {
//    print_r('else');
            if (!empty($id)) {
                   $this->db->select('
    proposal_items.*,
    COALESCE(tbl_saved_items.unit_cost, proposal_items.price) AS unit_cost,
    COALESCE(SUM(proposal_item_addons.addon_price), 0) AS addon_total,

    (
        COALESCE(tbl_saved_items.unit_cost, proposal_items.price) 
        + COALESCE(SUM(proposal_item_addons.addon_price), 0)
    ) AS final_unit_price
', false);

$this->db->from('proposal_items');

$this->db->join(
    'tbl_saved_items',
    'tbl_saved_items.saved_items_id = proposal_items.item_id',
    'left'
);

$this->db->join(
    'proposal_item_addons',
    'proposal_item_addons.proposal_item_id = proposal_items.id',
    'left'
);

$this->db->where('proposal_items.proposal_id', $id);

$this->db->group_by('proposal_items.id');

$data['proposal_items'] = $this->db->get()->result_array();

//print_r($data['proposal_items']);
                }  

                    // $this->db->select('proposal_items.*, COALESCE(tbl_saved_items.unit_cost, proposal_items.price) AS unit_cost', false);
                    // $this->db->from('proposal_items');
                    // $this->db->join('tbl_saved_items', 'tbl_saved_items.saved_items_id = proposal_items.item_id', 'left');
                    // $this->db->where('proposal_items.proposal_id', $id);
                    // $data['proposal_items'] = $this->db->get()->result_array();

//  $this->db->select('
//     proposal_items.*, 
//     tbl_saved_items.unit_cost AS new_price
// ', false);

// $this->db->from('proposal_items');

// $this->db->join(
//     'tbl_saved_items', 
//     'tbl_saved_items.saved_items_id = proposal_items.item_id', 
//     'left'
// );

// $this->db->where('proposal_items.proposal_id', $id);

// $data['proposal_items'] = $this->db->get()->result_array();

                 
}

 
// exit();

                 


                   if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_item_addons');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_item_addons'] = $this->db->get()->result_array();
               // print_r($data['proposal_item_addons']);
             } 
                        
                $this->db->select('id');
                $this->db->from('proposals');
                $this->db->where('version_no', 1);
                $this->db->order_by('id', 'DESC');
                $this->db->limit(1);
                $lastProposal = $this->db->get()->row();

                $lastId = !empty($lastProposal) ? $lastProposal->id : 0;
                $newId  = $lastId + 1;

				$this->db->insert('proposal_ref_number', [
                    'proposal_id' => $id,
                    'createdate'  => date('Y-m-d H:i:s')
                ]);
				
                $refId = $this->db->insert_id();
//print_r($id);

                // ✅ Auto-generate referral number
                $financialYear = $this->getFinancialYear(); 
                $data['refNo'] = "HCIPPL/Quote/{$financialYear}/N/100" . $refId;
             //   print_r($data['refNo'] );



           //  print_r($data['proposal_items']);
                    if (!empty($id)) { 
                    $subview = 'createnewproposal';
                    } else {
                    $subview = 'createnewproposal_add';
                    }

                  
        $data['proposal_id'] = $id;  
		$data['client_id'] = $item_id; 		
        $data['leads_id'] = $action;                
        $data['session'] = $session;                
        $data['subview'] = $this->load->view('admin/proposals/' . $subview, $data, TRUE);
           
        $this->load->view('admin/_layout_main', $data); //page load
    }

    private function getFinancialYear()
{
    $year = date('y');
    $month = date('m');

    if ($month >= 4) {
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}


     public function view($action = NULL, $id = NULL, $item_id = NULL)
    {
        $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }

        $data['title'] = lang('proposals'); //Page title       
  
        $this->proposal_model->_table_name = 'tbl_category_location';
        $this->proposal_model->_order_by = 'category_location';
        $data['category_location'] = $this->proposal_model->get_by(array('category_location_id >' => 0));
 
        $this->proposal_model->_table_name = 'tbl_category';
        $this->proposal_model->_order_by = 'category_name';
        $data['all_category'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id' => 0));
      
       
 
         $data['proposals_info'] = get_row('proposals', array('id' => $id));
        
 
         $data['all_subcategory'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id !=' => 0));


         $data['leads'] = $this->db->select('*')
                                  ->from('tbl_leads')
                                  ->order_by('contact_name', 'ASC')
                                  ->get()
                                  ->result();
 
            if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposals');
                $this->db->where('id', $id);
                $data['proposals'] = $this->db->get()->result_array();
            // print_r($data['proposals']);
            }   
             
            
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floors');
                $this->db->where('proposal_id', $id);
                  $this->db->order_by('id', 'ASC'); // ✅ order floors by ID ascending
                $data['proposal_floors'] = $this->db->get()->result_array();
             }   

             if (!empty($id)) {
            $this->db->from('proposal_floors');
            $this->db->where('proposal_id', $id);
            
            $data['proposals_count'] = $this->db->count_all_results(); // ✅
            //print_r($data['proposals_count']);
        }
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floor_categories');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories
               // $this->db->order_by('id', 'ASC');
                $data['proposal_floor_categories'] = $this->db->get()->result_array();
             }   
             
               if (!empty($id)) {
            // $this->db->select('pi.*, si.item_name, si.code, si.unit_cost, si.upload_file');
            // $this->db->from('proposal_items pi');
            // $this->db->join('tbl_saved_items si', 'si.saved_items_id = pi.item_id', 'left');
            // $this->db->where('pi.proposal_id', $id);
			
			   $this->db->select('
                pi.*,
                si.item_name as saved_item_name,
                si.upload_file
            ');
            $this->db->from('proposal_items pi');
            $this->db->join('tbl_saved_items si', 'si.saved_items_id = pi.item_id', 'left');
            $this->db->where('pi.proposal_id', $id);
			
			
			//$this->db->order_by('id', 'DESC');
            $data['proposal_items'] = $this->db->get()->result_array();

    // Decode image JSON
            foreach ($data['proposal_items'] as &$item) {
                if (!empty($item['upload_file'])) {
                    $files = json_decode($item['upload_file'], true);
                    if (!empty($files) && isset($files[0]['path'])) {
                        $item['image_url'] = base_url($files[0]['path']); // ✅ final image URL
                    } else {
                        $item['image_url'] = base_url('uploads/no-image.png'); // fallback
                    }
                } else {
                    $item['image_url'] = base_url('uploads/no-image.png');
                }
            }
        }
        

             if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_item_addons');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_item_addons'] = $this->db->get()->result_array();
             } 


                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('exclusive_product');
                $this->db->where('proposals_id', $id);
 
                $data['exclusive_product'] = $this->db->get()->result_array();
             } 
           
        $subview = 'index';
        $data['proposal_id'] = $id;                
        $data['subview'] = $this->load->view('admin/proposals/hcinvoice/' . $subview, $data, TRUE);
           
        $this->load->view('admin/_layout_main', $data); //page load
    }





 public function save()
{
    $this->load->model('proposal_model');
    $postData = $this->input->post();
    $user_id = $this->session->userdata('user_id');
    
    
    if (!empty($postData['proposal_id'])) {
        // We are UPDATING → Instead of overwrite, create a new version
        $old_proposal_id = $postData['proposal_id'];

        // Get last version number for this proposal chain
        $lastVersion = $this->proposal_model->getLastVersion($old_proposal_id);
 
        $proposalData = [
            'ref_no'       => $postData['ref_no'],
            'lead_id'      => $postData['lead_id'],
            'phone'      => $postData['phone'],
            'notes'        => $postData['notes'],
            'proposal_date'=> $postData['proposal_date'],
            'expiry_date'  => $postData['expiry_date'],
            'grand_total'  => $postData['grand_total'],
            'status'       => $postData['status'],
             'siteHandling'       => $postData['siteHandling'],
            'siteHandlingAmount'       => $postData['siteHandlingAmount'],
            'session'       => $postData['session_value'],
            'client_id'       => $postData['client_id'],
            'totalToBePaid'       => $postData['totalToBePaid'],
            'created_at'   => $lastVersion['created_at'],
            'updated_at'   => date('Y-m-d H:i:s'),
            'parent_id'    => $lastVersion['parent_id'] ?? $old_proposal_id,
            'version_no'   => ($lastVersion['version_no'] ?? 1) + 1,
            'updated_by'   => $user_id,
            'created_by'   => $lastVersion['created_by'],
            'checked_by'   => $postData['checked_by'],
 
            'exclusive_product'       => $postData['exclusive_product'],
 
        ];

        // Insert a NEW proposal row instead of updating
        $proposal_id = $this->proposal_model->insertProposal($proposalData);

         $exclusiveProducts = $this->db
        ->where('proposals_id', $old_proposal_id)
        ->get('exclusive_product')
        ->result_array();

    if (!empty($exclusiveProducts)) {
        foreach ($exclusiveProducts as $product) {
            unset($product['id']); // remove old primary key
            $product['proposals_id'] = $proposal_id; // link to new proposal
            $product['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('exclusive_product', $product);
        }
    }


    } else {
        // New Proposal → version 1
        $proposalData = [
              'exclusive_product'    => $postData['exclusive_product'] ?? null,
            'ref_no'       => $postData['ref_no'],
            'lead_id'      => $postData['lead_id'],
            'notes'        => $postData['notes'],
             'phone'      => $postData['phone'],
            'proposal_date'=> $postData['proposal_date'],
            'expiry_date'  => $postData['expiry_date'],
            'grand_total'  => $postData['grand_total'],
            'status'       => $postData['status'],
            'siteHandling'       => $postData['siteHandling'],
            'siteHandlingAmount'       => $postData['siteHandlingAmount'],
            'totalToBePaid'       => $postData['totalToBePaid'],
            'created_at'   => date('Y-m-d H:i:s'),
            'parent_id'    => NULL,
            'version_no'   => 1,
            'created_by'   => $user_id,
             'checked_by'   => $postData['checked_by'],
        ];
        $proposal_id = $this->proposal_model->insertProposal($proposalData);
    }

    // Insert Floors, Categories, Items, Addons fresh
    foreach ($postData['floors'] as $floor) {
        $floorData = [
            'proposal_id' => $proposal_id,
            'floor_name'  => $floor['name'],
            'subtotal'    => $floor['subtotal'],
            'discount'    => $floor['discount'],
            'total'       => $floor['total']
        ];
        $floor_id = $this->proposal_model->insertFloor($floorData);

        foreach ($floor['categories'] as $cat) {
            $catData = [
                'proposal_id'      => $proposal_id,
                'floor_id'         => $floor_id,
                'category_name_id' => $cat['category_name_id'],
                'category_name'    => $cat['name'],
                'subtotal'         => $cat['subtotal'],
                'discount'         => $cat['discount'],
                'total'            => $cat['total']
            ];
            $cat_id = $this->proposal_model->insertCategory($catData);

            foreach ($cat['items'] as $item) {
                $itemData = [
                    'proposal_id'      => $proposal_id,
                    'category_id'      => $cat_id,
                    'floor_id'      => $floor_id,
                    'location_name_id' => $item['location_name_id'],
                    'location_name'    => $item['location'],
                    'item_id'          => $item['id'] ?? NULL,
                    'item_name'        => $item['name'],
                    'description'      => $item['desc'],
                    'qty'              => $item['qty'],
                    'price'            => $item['price'],
                    'unit_type'        => $item['unit_type'],
                    'tax_rate' => isset($item['tax_rate']) ? $item['tax_rate'] : null,
                    'total'            => $item['total']
                ];
                $item_id = $this->proposal_model->insertItem($itemData);
				//print_r($item['addons']);
                if (!empty($item['addons'])) {
                    foreach ($item['addons'] as $addon) {
                        $addonData = [
                            'proposal_id'       => $proposal_id,
                            'proposal_item_id'  => $item_id,
                            'floor_id'          => $addon['floorId'],
                            'addon_id'          => $addon['id'] ?? NULL,
                            'addon_name'        => $addon['name'],
                            'main_item_id'      => $addon['main_item_id'],
                            'addon_price'       => $addon['price']
                        ];
                        $this->proposal_model->insertAddon($addonData);
                    }
                }
            }
        }
    }

  $this->session->set_flashdata('success', 'Proposal saved/updated successfully!');

// echo json_encode([
    // 'status'  => true,
    // 'message' => 'Proposal saved/updated successfully!',
    // 'redirect_url' => base_url('admin/proposals')
// ]);

    if ($postData['client_id']) {
echo json_encode([
    'status'  => true,
    'message' => 'Proposal saved/updated successfully!',
    'redirect_url' => base_url('admin/client/details/'.$postData['client_id'].'/boq')
]);
     } else {
echo json_encode([
    'status'  => true,
    'message' => 'Proposal saved/updated successfully!',  
    'redirect_url' => base_url('admin/proposals')
]);
     }
	 
exit;
}





     public function pdf($action = NULL, $id = NULL, $item_id = NULL) {


    $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }

        $data['title'] = lang('proposals'); //Page title       
  
        $this->proposal_model->_table_name = 'tbl_category_location';
        $this->proposal_model->_order_by = 'category_location';
        $data['category_location'] = $this->proposal_model->get_by(array('category_location_id >' => 0));
 
        $this->proposal_model->_table_name = 'tbl_category';
        $this->proposal_model->_order_by = 'category_name';
        $data['all_category'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id' => 0));
      
       
 
         $data['proposals_info'] = get_row('proposals', array('id' => $id));
        
 
         $data['all_subcategory'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id !=' => 0));


         $data['leads'] = $this->db->select('*')
                                  ->from('tbl_leads')
                                  ->order_by('contact_name', 'ASC')
                                  ->get()
                                  ->result();
 
            if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposals');
                $this->db->where('id', $id);
                $data['proposals'] = $this->db->get()->result_array();
            // print_r($data['proposals']);
            }   
             
            
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floors');
                $this->db->where('proposal_id', $id);
                  $this->db->order_by('id', 'ASC'); // ✅ order floors by ID ascending
                $data['proposal_floors'] = $this->db->get()->result_array();
             }   

             if (!empty($id)) {
            $this->db->from('proposal_floors');
            $this->db->where('proposal_id', $id);
            
            $data['proposals_count'] = $this->db->count_all_results(); // ✅
            //print_r($data['proposals_count']);
        }
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floor_categories');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_floor_categories'] = $this->db->get()->result_array();
             }   
             
               if (!empty($id)) {
            // $this->db->select('pi.*, si.item_name, si.code, si.unit_cost, si.upload_file');
            // $this->db->from('proposal_items pi');
            // $this->db->join('tbl_saved_items si', 'si.saved_items_id = pi.item_id', 'left');
            // $this->db->where('pi.proposal_id', $id);
			
			  // $this->db->select('*');
            // $this->db->from('proposal_items');
            // $this->db->where('proposal_id', $id);
			    $this->db->select('
                pi.*,
                si.item_name as saved_item_name,
                si.upload_file
            ');
            $this->db->from('proposal_items pi');
            $this->db->join('tbl_saved_items si', 'si.saved_items_id = pi.item_id', 'left');
            $this->db->where('pi.proposal_id', $id);
			
			//$this->db->order_by('id', 'DESC');
            $data['proposal_items'] = $this->db->get()->result_array();

    // Decode image JSON
            foreach ($data['proposal_items'] as &$item) {
                if (!empty($item['upload_file'])) {
                    $files = json_decode($item['upload_file'], true);
                    if (!empty($files) && isset($files[0]['path'])) {
                        $item['image_url'] = base_url($files[0]['path']); // ✅ final image URL
                    } else {
                        $item['image_url'] = base_url('uploads/no-image.png'); // fallback
                    }
                } else {
                    $item['image_url'] = base_url('uploads/no-image.png');
                }
            }
        }
        

             if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_item_addons');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_item_addons'] = $this->db->get()->result_array();
             } 
           
    
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('exclusive_product');
                $this->db->where('proposals_id', $id);
 
                $data['exclusive_product'] = $this->db->get()->result_array();
             } 
 
        // Load Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        // Load your PDF template
          $subview = 'pdf';
        $data['proposal_id'] = $id;                
        $html = $this->load->view('admin/proposals/hcinvoice/' . $subview, $data, TRUE);

 
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Proposal_" . $id . ".pdf", ["Attachment" => false]);
    }

    
	  public function operation_pdf($action = NULL, $id = NULL, $item_id = NULL) {


    $data['page'] = lang('sales');
        $data['sub_active'] = lang('proposals');
        $this->proposal_model->_table_name = 'tbl_client';
        $this->proposal_model->_order_by = 'client_id';
        $data['all_client'] = $this->proposal_model->get();
        // get permission user
        $data['permission_user'] = $this->proposal_model->all_permission_user('140');
        $type = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
        if (!empty($type) && !is_numeric($type)) {
            $filterBy = $type;
        } else {
            $filterBy = null;
        }

        $data['title'] = lang('proposals'); //Page title       
  
        $this->proposal_model->_table_name = 'tbl_category_location';
        $this->proposal_model->_order_by = 'category_location';
        $data['category_location'] = $this->proposal_model->get_by(array('category_location_id >' => 0));
 
        $this->proposal_model->_table_name = 'tbl_category';
        $this->proposal_model->_order_by = 'category_name';
        $data['all_category'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id' => 0));
      
       
 
         $data['proposals_info'] = get_row('proposals', array('id' => $id));
        
 
         $data['all_subcategory'] = $this->proposal_model->get_by(array('status' => 1, 'main_category_id !=' => 0));


         $data['leads'] = $this->db->select('*')
                                  ->from('tbl_leads')
                                  ->order_by('contact_name', 'ASC')
                                  ->get()
                                  ->result();
 
            if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposals');
                $this->db->where('id', $id);
                $data['proposals'] = $this->db->get()->result_array();
            // print_r($data['proposals']);
            }   
             
            
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floors');
                $this->db->where('proposal_id', $id);
                  $this->db->order_by('id', 'ASC'); // ✅ order floors by ID ascending
                $data['proposal_floors'] = $this->db->get()->result_array();
             }   

             if (!empty($id)) {
            $this->db->from('proposal_floors');
            $this->db->where('proposal_id', $id);
            
            $data['proposals_count'] = $this->db->count_all_results(); // ✅
            //print_r($data['proposals_count']);
        }
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_floor_categories');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_floor_categories'] = $this->db->get()->result_array();
             }   
             
               if (!empty($id)) {
            // $this->db->select('pi.*, si.item_name, si.code, si.unit_cost, si.upload_file');
            // $this->db->from('proposal_items pi');
            // $this->db->join('tbl_saved_items si', 'si.saved_items_id = pi.item_id', 'left');
            // $this->db->where('pi.proposal_id', $id);
			
			  // $this->db->select('*');
            // $this->db->from('proposal_items');
            // $this->db->where('proposal_id', $id);
			
			
			$this->db->select('
                pi.*,
                si.item_name as saved_item_name,
                si.upload_file
            ');
            $this->db->from('proposal_items pi');
            $this->db->join('tbl_saved_items si', 'si.saved_items_id = pi.item_id', 'left');
            $this->db->where('pi.proposal_id', $id);

			
			
			//$this->db->order_by('id', 'DESC');
            $data['proposal_items'] = $this->db->get()->result_array();

    // Decode image JSON
            foreach ($data['proposal_items'] as &$item) {
                if (!empty($item['upload_file'])) {
                    $files = json_decode($item['upload_file'], true);
                    if (!empty($files) && isset($files[0]['path'])) {
                        $item['image_url'] = base_url($files[0]['path']); // ✅ final image URL
                    } else {
                        $item['image_url'] = base_url('uploads/no-image.png'); // fallback
                    }
                } else {
                    $item['image_url'] = base_url('uploads/no-image.png');
                }
            }
        }
        

             if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('proposal_item_addons');
                $this->db->where('proposal_id', $id);
                //$this->db->group_by('category_name');  // ensure unique categories

                $data['proposal_item_addons'] = $this->db->get()->result_array();
             } 
           
    
                 if (!empty($id)) {
                $this->db->select('*');
                $this->db->from('exclusive_product');
                $this->db->where('proposals_id', $id);
 
                $data['exclusive_product'] = $this->db->get()->result_array();
             } 
 
        // Load Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        // Load your PDF template
          $subview = 'operation_pdf';
        $data['proposal_id'] = $id;                
        $html = $this->load->view('admin/proposals/hcinvoice/' . $subview, $data, TRUE);

 
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Proposal_" . $id . ".pdf", ["Attachment" => false]);
    }
    
    
}
