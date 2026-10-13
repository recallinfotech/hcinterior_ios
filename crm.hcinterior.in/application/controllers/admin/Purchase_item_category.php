<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Purchase_item_category extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_item_category_model');
    }

    public function index()
    {
        $this->manage();
    }

    public function manage()
    {
        $data['title'] = 'Purchase Item Category';
        $data['subview'] = $this->load->view('admin/purchase_item_category/manage', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function create($id = NULL)
    {
        $data['title'] = (!empty($id) ? 'Edit' : 'Create') . ' Purchase Item Category';
        if (!empty($id) && is_numeric($id)) {
            $data['category_info'] = $this->purchase_item_category_model->check_by(array('id' => $id), 'purchase_item_category');
            if (empty($data['category_info'])) {
                $data['category_info'] = $this->purchase_item_category_model->check_by(array('id' => $id), 'tbl_purchase_item_category');
            }
        }

        $data['subview'] = $this->load->view('admin/purchase_item_category/create', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function save_category($id = NULL)
    {
        $this->purchase_item_category_model->_table_name = 'purchase_item_category';
        $this->purchase_item_category_model->_primary_key = 'id';

        $category_name = $this->input->post('category_name', true);
        $status = $this->input->post('status', true);
        if ($status === null || $status === '') {
            $status = 1;
        } else {
            $status = ($status == 1 || $status == 'published' || $status == '1') ? 1 : 0;
        }

        $data = array(
            'category_name' => $category_name,
            'status' => $status
        );

        if (empty($id)) {
            $data['createdate'] = date('Y-m-d H:i:s');
        }

        // duplicate check
        $where = array('category_name' => $category_name);
        $check_id = !empty($id) ? array('id !=' => $id) : null;
        $check_category = $this->purchase_item_category_model->check_update('purchase_item_category', $where, $check_id);

        if (!empty($check_category)) {
            $type = 'error';
            $msg = "<strong style='color:#000'>" . $category_name . '</strong> already exists';
        } else {
            // Save to purchase_item_category
            $return_id = $this->purchase_item_category_model->save($data, $id);

            // Sync to tbl_purchase_item_category as well
            $this->purchase_item_category_model->_table_name = 'tbl_purchase_item_category';
            $this->purchase_item_category_model->save($data, $id);

            $type = "success";
            $msg = !empty($id) ? 'Purchase Item Category Updated Successfully' : 'Purchase Item Category Created Successfully';
        }

        set_message($type, $msg);
        redirect('admin/purchase_item_category/manage');
    }

    public function categoryList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'purchase_item_category';
            $this->datatables->column_order = array('id', 'category_name', 'status', 'createdate');
            $this->datatables->column_search = array('category_name', 'status');
            $this->datatables->order = array('id' => 'desc');

            $fetch_data = make_datatables();

            $data = array();

            foreach ($fetch_data as $_key => $row) {
                $action = null;
                $sub_array = array();
                $sub_array[] = $row->id;
                $sub_array[] = $row->category_name;

                if ($row->status == 1 || $row->status == 'published') {
                    $sub_array[] = '<a href="' . base_url('admin/purchase_item_category/change_status/0/' . $row->id) . '" class="label label-success">Active</a>';
                } else {
                    $sub_array[] = '<a href="' . base_url('admin/purchase_item_category/change_status/1/' . $row->id) . '" class="label label-danger">Inactive</a>';
                }

                $sub_array[] = date('d-m-Y H:i', strtotime($row->createdate));

                $action .= btn_edit('admin/purchase_item_category/create/' . $row->id) . ' ';
                $action .= ajax_anchor(base_url("admin/purchase_item_category/delete_category/$row->id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => "Delete", "data-fade-out-on-success" => "#table_" . $_key)) . ' ';

                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function change_status($status, $id)
    {
        $data['status'] = $status;
        $this->purchase_item_category_model->_table_name = 'purchase_item_category';
        $this->purchase_item_category_model->_primary_key = 'id';
        $this->purchase_item_category_model->save($data, $id);

        $this->purchase_item_category_model->_table_name = 'tbl_purchase_item_category';
        $this->purchase_item_category_model->save($data, $id);

        set_message('success', 'Status Changed Successfully');
        redirect('admin/purchase_item_category/manage');
    }

    public function delete_category($id)
    {
        $this->purchase_item_category_model->_table_name = 'purchase_item_category';
        $this->purchase_item_category_model->_primary_key = 'id';
        $this->purchase_item_category_model->delete($id);

        $this->purchase_item_category_model->_table_name = 'tbl_purchase_item_category';
        $this->purchase_item_category_model->delete($id);

        echo json_encode(array("status" => "success", 'message' => "Category Deleted Successfully"));
        exit();
    }
}
