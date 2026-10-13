<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Category extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('category_model');
    }

    public function manage()
    {
        $data['title'] = lang('manage_category');
       
        $data['subview'] = $this->load->view('admin/category/manage', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }
 public function create($id = NULL, $inline = NULL)
{
    $data['title'] = lang('create') . ' ' . lang('category');

    if (!empty($id)) {
        $can_edit = $this->category_model->can_action('tbl_category', 'edit', array('category_id' => $id));
        $edited = can_action('39', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $data['category_info'] = $this->category_model->check_by(array('category_id' => $id), 'tbl_category');

            // Decode JSON into array for selected items
            $data['selected_item_links'] = !empty($data['category_info']->item_link)
                ? json_decode($data['category_info']->item_link, true)
                : [];
        }
    } else {
        $data['selected_item_links'] = []; // No preselected items for new category
    }

    $data['permission_user'] = $this->category_model->all_permission_user('39');
    $data['inline'] = $inline;
    $data['main_categories'] = $this->db->where('type', 'Main')->get('tbl_category')->result();
 
    if (!empty($inline)) {
        $data['subview'] = $this->load->view('admin/category/create', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data); // page load
    } else {
        $data['subview'] = $this->load->view('admin/category/create', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); // page load
    }
}


    public function save_category($id = NULL, $inline = NULL)
    {
       
        if (!empty($inline)) {
            $id = NULL;
        }
        $created = can_action('39', 'created');
        $edited = can_action('39', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $this->category_model->_table_name = 'tbl_category';
            $this->category_model->_primary_key = 'category_id';
            $data = $this->category_model->array_from_post(array('category_name', 'type', 'main_category_id', 'sub_category_id', 'description', 'area', 'unit', 'unit_rate', 'amount', 'remark', 'item_link', 'status'));
            if (empty($data['status'])) {
                $data['status'] = 'published';
            }
        $item_links = $this->input->post('item_link', true);
        $data['item_link'] = !empty($item_links) ? json_encode($item_links) : null;

            // update root category
            $where = array('category_name' => $data['category_name']);
            // duplicate value check in DB
            if (!empty($id)) { // if id exist in db update data
                $category_id = array('category_id !=' => $id);
            } else { // if id is not exist then set id as null
                $category_id = null;
            }
   
            // check whether this input data already exist or not
            $check_category = $this->category_model->check_update('tbl_category', $where, $category_id);
           
            if (!empty($check_category)) { // if input data already exist show error alert
                // massage for user
                $type = 'error';
                $msg = "<strong style='color:#000'>" . $data['category_name'] . '</strong>  ' . lang('already_exist');
            } else { // save and update query
                if (!empty($_FILES['image']['name'])) {
                    $val = $this->category_model->uploadImage('image');
                    $val == TRUE || redirect('admin/category/create');
                    $data['image'] = $val['path'];
                }

                $permission = $this->input->post('permission', true);
                if (!empty($permission)) {
                    if ($permission == 'everyone') {
                        $assigned = 'all';
                    } else {
                        $assigned_to = $this->category_model->array_from_post(array('assigned_to'));
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
                        redirect('admin/category/create');
                    } else {
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                  
                if (!empty($id)) {
                    $can_edit = $this->category_model->can_action('tbl_category', 'edit', array('category_id' => $id));
                    if (!empty($can_edit)) {
                        $return_id = $this->category_model->save($data, $id);
                    } else {
                        set_message('error', lang('there_in_no_value'));
                        redirect('admin/category/create');
                    }
                } else {
                    
                    $return_id = $this->category_model->save($data);
                }
                if (!empty($id)) {
                    $id = $id;
                    $action = 'activity_update_category';
                    $msg = lang('update_category');
                } else {
                    $id = $return_id;
                    $action = 'activity_save_category';
                    $msg = lang('save_category');
                }
                save_custom_field(21, $id);
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'category',
                    'module_field_id' => $id,
                    'activity' => $action,
                    'icon' => 'fa-circle-o',
                    'value1' => $data['category_code'] . ' ' . $data['category_name']
                );
                $this->category_model->_table_name = 'tbl_activities';
                $this->category_model->_primary_key = 'activities_id';
                $this->category_model->save($activity);
                $type = "success";
            }
            $message = $msg;
            set_message($type, $message);
        }
        if (!empty($inline)) {
            if (!empty($id)) {
                $result = array(
                    'fieldName' => 'category_id',
                    'fieldId' => $id,
                    'fieldValue' => $data['category_name'],
                    'status' => $type,
                    'message' => $msg,
                    'modal' => 'myModal_lg',
                );
            } else {
                $result = array(
                    'modal' => 'myModal_lg',
                    'status' => $type,
                    'message' => $msg,
                );
            }
            echo json_encode($result);
            exit();
        } else {
            redirect('admin/category/manage');
        }
    }



    public function categoryList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_category';
            $this->datatables->column_order = array('category_name', 'type', 'status');
            $this->datatables->column_search = array('category_name', 'type', 'status');
            $this->datatables->order = array('category_id' => 'desc');


            
            $fetch_data = $this->datatables->get_datatable_permission();

            $data = array();

            $edited = can_action('39', 'edited');
            $deleted = can_action('39', 'deleted');
            foreach ($fetch_data as $key => $row) {
                $action = null;
                $can_edit = $this->category_model->can_action('tbl_category', 'edit', array('category_id' => $row->category_id));
                $can_delete = $this->category_model->can_action('tbl_category', 'delete', array('category_id' => $row->category_id));
                $sub_array = array();
                $sub_array[] = $row->category_name;
                $sub_array[] = $row->type;

                  $main_name = '-';
    $sub_name  = '-';
  if (!empty($row->main_category_id)) {
        $main = $this->db->select('category_name')
            ->where('category_id', $row->main_category_id)
            ->get('tbl_category')
            ->row();
        if ($main) {
            $main_name = $main->category_name;
        }
    }

    if (!empty($row->sub_category_id)) {
        $sub = $this->db->select('category_name')
            ->where('category_id', $row->sub_category_id)
            ->get('tbl_category')
            ->row();
        if ($sub) {
            $sub_name = $sub->category_name;
        }
    }

                  $sub_array[] = $main_name;
    $sub_array[] = $sub_name;
       

                if (!empty($can_edit) && !empty($edited)) {
                    if ($row->status == 'unpublished') {
                        // $action .= '<a href="' . base_url('admin/category/change_status/published/' . $row->category_id) . '" data-toggle="tooltip" title="' . lang('click_to_published') . '" class="btn btn-xs btn-danger mr">' . lang('sink') . ' </a> ' . ' ';
                    }
                    // $action .= '<a data-id="' . $row->category_id . '" href="' . base_url('admin/category/make_default/' . $row->category_id) . '" data-toggle="tooltip" title="' . lang('default') . '" class="btn btn-xs btn-warning "> ' . lang('default') . ' </a> ' . ' ';


                    if ($row->status == 'unpublished') {
                        $action .= '<a href="' . base_url('admin/category/change_status/published/' . $row->category_id) . '" data-toggle="tooltip" title="' . lang('click_to_published') . '" class="btn btn-xs btn-danger mr">' . lang('unpublished') . ' </a> ' . ' ';
                    } else {
                        $action .= '<a href="' . base_url('admin/category/change_status/unpublished/' . $row->category_id) . '" data-toggle="tooltip" title="' . lang('click_to_unpublished') . '" class="btn btn-xs btn-success"> ' . lang('published') . ' </a> ' . ' ';
                    }


                    $action .= btn_edit('admin/category/create/' . $row->category_id) . ' ';
                }
                if (!empty($can_delete) && !empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/category/delete_category/$row->category_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $key));
                }
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }


    public function delete_category($id)
    {
        $deleted = can_action('39', 'deleted');
        if (!empty($deleted)) {
            $category = $this->category_model->check_by(array('category_id' => $id), 'tbl_category');
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'settings',
                'module_field_id' => $id,
                'activity' => ('activity_delete_a_category'),
                'value1' => $category->category_name,
            );
            $this->category_model->_table_name = 'tbl_activities';
            $this->category_model->_primary_key = 'activities_id';
            $this->category_model->save($activity);

            $this->category_model->_table_name = 'tbl_category';
            $this->category_model->_primary_key = 'category_id';
            $this->category_model->delete($id);
            // messages for user
            $type = "success";
            $message = lang('category_deleted');
            // messages for user
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('there_in_no_value')));
            exit();
        }
    }

    public function change_status($type, $id)
    {
        $can_edit = $this->category_model->can_action('tbl_category', 'edit', array('category_id' => $id));
        $edited = can_action('39', 'edited');
        if (!empty($can_edit) && !empty($edited)) {

            $data['status'] = $type;
            $this->category_model->_table_name = 'tbl_category';
            $this->category_model->_primary_key = 'category_id';
            $this->category_model->save($data, $id);

            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'settings',
                'module_field_id' => $id,
                'activity' => ('activity_change_category_status'),
                'value1' => $type,
            );
            $this->category_model->_table_name = 'tbl_activities';
            $this->category_model->_primary_key = 'activities_id';
            $this->category_model->save($activity);

            // messages for user
            $type = "success";
            $message = lang('category_status_changed');
        } else {
            $type = "error";
            $message = lang('there_in_no_value');
        }
        set_message($type, $message);
        redirect('admin/category/manage');
    }

    public function make_default($id, $type = null)
    {
        $mm = get_result('tbl_saved_items');
        $this->items_model->_table_name = 'tbl_categorys_products';
        $this->items_model->_primary_key = 'id';

        foreach ($mm as $key) {
            $docheck = get_row('tbl_categorys_products', array('product_id' => $key->saved_items_id,));
            if (empty($docheck)) {
                $params['category_id'] = $id;
                $params['product_id'] = $key->saved_items_id;
                $params['quantity'] = $key->quantity;
                $this->items_model->save($params);
            } elseif ($docheck->category_id == 0) {
                $params['category_id'] = $id;
                $this->items_model->save($params, $docheck->id);
            }
        }

        // assign category_id to all staff if not exist
        $all_staff = get_staff_details(null, null, array('tbl_account_details.category_id' => null));
        $this->items_model->_table_name = 'tbl_account_details';
        $this->items_model->_primary_key = 'account_details_id';
        foreach ($all_staff as $key) {
            $wdata['category_id'] = $id;
            $this->items_model->save($wdata, $key->account_details_id);
        }

        if (!empty($id)) {
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'settings',
                'module_field_id' => $id,
                'activity' => ('activity_change_category_status'),
                'value1' => $type,
            );
            $this->category_model->_table_name = 'tbl_activities';
            $this->category_model->_primary_key = 'activities_id';
            $this->category_model->save($activity);

            // messages for user
            $type = "success";
            $message = lang('category_status_changed');
        } else {
            $type = "error";
            $message = lang('there_in_no_value');
        }
        set_message($type, $message);
        //        echo json_encode(array("status" => $type, 'message' => $message));
        //        exit();

        redirect('admin/category/manage');
    }


    public function get_sub_categories() {
    $main_id = $this->input->post('main_id');
    $sub_categories = $this->db->where('type', 'Sub Category')
                               ->where('main_category_id', $main_id)
                               ->get('tbl_category')
                               ->result();
    echo json_encode($sub_categories);
}
}