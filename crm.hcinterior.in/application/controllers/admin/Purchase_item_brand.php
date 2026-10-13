<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Purchase_item_brand extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_item_brand_model');
        $this->load->model('purchase_item_category_model');
    }

    public function index()
    {
        $this->manage();
    }

    public function manage()
    {
        $data['title'] = 'Purchase Item Brand';
        $data['subview'] = $this->load->view('admin/purchase_item_brand/manage', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function create($id = NULL)
    {
        $data['title'] = (!empty($id) ? 'Edit' : 'Create') . ' Purchase Item Brand';

        $data['categories'] = $this->db->where('status', 1)->get('purchase_item_category')->result();
        if (empty($data['categories'])) {
            $data['categories'] = $this->db->where('status', 1)->get('tbl_purchase_item_category')->result();
        }

        if (!empty($id) && is_numeric($id)) {
            $data['brand_info'] = $this->purchase_item_brand_model->check_by(array('id' => $id), 'purchase_item_brand');
            if (empty($data['brand_info'])) {
                $data['brand_info'] = $this->purchase_item_brand_model->check_by(array('id' => $id), 'tbl_purchase_item_brand');
            }
        }

        $data['subview'] = $this->load->view('admin/purchase_item_brand/create', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function save_brand($id = NULL)
    {
        $this->purchase_item_brand_model->_table_name = 'purchase_item_brand';
        $this->purchase_item_brand_model->_primary_key = 'id';

        $category_id = $this->input->post('purchase_item_category_id', true);
        $brand_name = $this->input->post('brand_name', true);
        $status = $this->input->post('status', true);
        if ($status === null || $status === '') {
            $status = 1;
        } else {
            $status = ($status == 1 || $status == 'published' || $status == '1') ? 1 : 0;
        }

        $data = array(
            'purchase_item_category_id' => $category_id,
            'brand_name' => $brand_name,
            'status' => $status
        );

        if (empty($id)) {
            $data['createdate'] = date('Y-m-d H:i:s');
        }

        // Duplicate check within same category
        $where = array('purchase_item_category_id' => $category_id, 'brand_name' => $brand_name);
        $check_id = !empty($id) ? array('id !=' => $id) : null;
        $check_brand = $this->purchase_item_brand_model->check_update('purchase_item_brand', $where, $check_id);

        if (!empty($check_brand)) {
            $type = 'error';
            $msg = "<strong style='color:#000'>" . $brand_name . '</strong> already exists in this category';
        } else {
            // Save to purchase_item_brand
            $return_id = $this->purchase_item_brand_model->save($data, $id);

            // Sync to tbl_purchase_item_brand as well
            $this->purchase_item_brand_model->_table_name = 'tbl_purchase_item_brand';
            $this->purchase_item_brand_model->save($data, $id);

            $type = "success";
            $msg = !empty($id) ? 'Purchase Item Brand Updated Successfully' : 'Purchase Item Brand Created Successfully';
        }

        set_message($type, $msg);
        redirect('admin/purchase_item_brand/manage');
    }

    public function brandList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'purchase_item_brand';
            $this->datatables->select = array('purchase_item_brand.*', 'purchase_item_category.category_name');
            $this->datatables->join_table = array('purchase_item_category');
            $this->datatables->join_where = array('purchase_item_brand.purchase_item_category_id = purchase_item_category.id');
            $this->datatables->column_order = array('purchase_item_brand.id', 'purchase_item_category.category_name', 'purchase_item_brand.brand_name', 'purchase_item_brand.status', 'purchase_item_brand.createdate');
            $this->datatables->column_search = array('purchase_item_brand.brand_name', 'purchase_item_category.category_name');
            $this->datatables->order = array('purchase_item_brand.id' => 'desc');

            $fetch_data = make_datatables();

            $data = array();

            foreach ($fetch_data as $_key => $row) {
                $action = null;
                $sub_array = array();
                $sub_array[] = $row->id;
                $sub_array[] = !empty($row->category_name) ? $row->category_name : '-';
                $sub_array[] = $row->brand_name;

                if ($row->status == 1 || $row->status == 'published') {
                    $sub_array[] = '<a href="' . base_url('admin/purchase_item_brand/change_status/0/' . $row->id) . '" class="label label-success">Active</a>';
                } else {
                    $sub_array[] = '<a href="' . base_url('admin/purchase_item_brand/change_status/1/' . $row->id) . '" class="label label-danger">Inactive</a>';
                }

                $sub_array[] = date('d-m-Y H:i', strtotime($row->createdate));

                $action .= btn_edit('admin/purchase_item_brand/create/' . $row->id) . ' ';
                $action .= ajax_anchor(base_url("admin/purchase_item_brand/delete_brand/$row->id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => "Delete", "data-fade-out-on-success" => "#table_" . $_key)) . ' ';

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
        $this->purchase_item_brand_model->_table_name = 'purchase_item_brand';
        $this->purchase_item_brand_model->_primary_key = 'id';
        $this->purchase_item_brand_model->save($data, $id);

        $this->purchase_item_brand_model->_table_name = 'tbl_purchase_item_brand';
        $this->purchase_item_brand_model->save($data, $id);

        set_message('success', 'Status Changed Successfully');
        redirect('admin/purchase_item_brand/manage');
    }

    public function delete_brand($id)
    {
        $this->purchase_item_brand_model->_table_name = 'purchase_item_brand';
        $this->purchase_item_brand_model->_primary_key = 'id';
        $this->purchase_item_brand_model->delete($id);

        $this->purchase_item_brand_model->_table_name = 'tbl_purchase_item_brand';
        $this->purchase_item_brand_model->delete($id);

        echo json_encode(array("status" => "success", 'message' => "Brand Deleted Successfully"));
        exit();
    }
}
