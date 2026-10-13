<?php
$user_type      = $this->session->userdata('user_type');
$get_client_id  = $this->input->get('client_id', true);
$get_status     = $this->input->get('status', true);
$get_start_date = $this->input->get('start_date', true);
$get_end_date   = $this->input->get('end_date', true);
$get_limit      = (int)$this->input->get('limit', true);
if ($get_limit <= 0) {
    $get_limit = isset($limit) ? $limit : 20;
}

$total_rows   = isset($total_rows) ? $total_rows : (isset($purchase_list) ? count($purchase_list) : 0);
$total_pages  = isset($total_pages) ? $total_pages : 1;
$current_page = isset($current_page) ? $current_page : 1;
$offset       = isset($offset) ? $offset : 0;

if (!$this->db->field_exists('delivery_status', 'tbl_client_on_site_purchase')) {
    $this->db->query("ALTER TABLE `tbl_client_on_site_purchase` ADD `delivery_status` VARCHAR(100) NULL");
}
if (!$this->db->field_exists('delivery_status', 'tbl_client_on_site_purchase_request')) {
    $this->db->query("ALTER TABLE `tbl_client_on_site_purchase_request` ADD `delivery_status` VARCHAR(100) NULL");
}
?>

<div class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>On Site Purchase Request List (All Clients)</strong>
        </div>
    </div>

    <div class="panel-body">
        
        <!-- Filter Bar -->
        <form method="get" action="" class="form-horizontal mb-lg">
            <div class="row" style="margin-bottom: 15px;">
                
                <div class="col-md-2">
                    <label>Client ID / Search</label>
                    <input type="text" name="client_id" class="form-control" placeholder="Search Client ID" value="<?= html_escape($get_client_id) ?>">
                </div>

                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Pending" <?= ($get_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Accepted" <?= ($get_status == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                        <option value="Partial" <?= ($get_status == 'Partial') ? 'selected' : '' ?>>Partial Approved</option>
                        <option value="Completed" <?= ($get_status == 'Completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="Reject" <?= ($get_status == 'Reject' || $get_status == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= html_escape($get_start_date) ?>">
                </div>

                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= html_escape($get_end_date) ?>">
                </div>

                <div class="col-md-2">
                    <label>Per Page</label>
                    <select name="limit" class="form-control" onchange="this.form.submit();">
                        <option value="10" <?= ($get_limit == 10) ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= ($get_limit == 20) ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= ($get_limit == 50) ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($get_limit == 100) ? 'selected' : '' ?>>100</option>
                    </select>
                </div>

                <div class="col-md-2" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= current_url(); ?>" class="btn btn-default btn-sm">Reset</a>
                </div>

            </div>
        </form>

        <!-- Master List of Purchase Requests -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Request No.</th>
                        <th>Client</th>
                        <th>Total Items</th>
                        <th>Requested By & Date</th>
                        <th style="width: 160px;">Request Status</th>
                        <th style="width: 150px;">Delivery Status</th>
                        <th style="width: 240px;"><?= lang('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($purchase_list)) {
                        $sl = !empty($offset) ? ($offset + 1) : 1;
                        foreach ($purchase_list as $req) {
                            $creator = !empty($req->uploaded_by) ? get_any_field('tbl_account_details', array('user_id' => $req->uploaded_by), 'fullname') : '';
                            $parent_approver = !empty($req->status_approve_by) ? get_any_field('tbl_account_details', array('user_id' => $req->status_approve_by), 'fullname') : '';
                            $parent_approve_date_str = !empty($req->status_approve_date) ? date('d M Y, h:i A', strtotime($req->status_approve_date)) : '';

                            // Fetch items belonging to this purchase request
                            $items = $this->db->where('purchase_id', $req->id)
                                ->order_by('id', 'ASC')
                                ->get('tbl_client_on_site_purchase_request')
                                ->result_array();

                            // Attach approver names to items array for modal display
                            foreach ($items as &$item_row) {
                                $item_row['approver_name'] = !empty($item_row['status_approve_by']) ? get_any_field('tbl_account_details', array('user_id' => $item_row['status_approve_by']), 'fullname') : '';
                            }
                            unset($item_row);

                            $item_count = count($items);
                            $req_no_str = html_escape($req->purchase_no ?: ('OSPR-' . sprintf('%04d', $req->id)));
                            $curr_req_status = $req->request_status ?: (($req->status == 'Recieved' || $req->status == 'Received') ? 'Completed' : (($req->status == 'Partially Received') ? 'Partial' : 'Pending'));
                            $client_display_name = !empty($req->client_name) ? html_escape($req->client_name) : ('Client #' . $req->client_id);
                            $client_sr = !empty($req->client_sr_id) ? (' (' . html_escape($req->client_sr_id) . ')') : '';
                    ?>
                            <tr data-purchase-row-id="<?= $req->id ?>">
                                <td><?= $sl++ ?></td>
                                <td>
                                    <strong class="text-primary"><?= $req_no_str ?></strong>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/client/details/' . $req->client_id . '/on_site_purchase_request') ?>" target="_blank" class="font-bold text-info">
                                        <?= $client_display_name . $client_sr ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="label label-info"><?= $item_count ?> Item<?= $item_count > 1 ? 's' : '' ?></span>
                                </td>
                                <td>
                                    <small>
                                        <strong><?= html_escape($creator) ?: 'User' ?></strong><br>
                                        <span class="text-muted"><i class="fa fa-clock-o"></i> <?= !empty($req->created_date) ? date('d M Y, h:i A', strtotime($req->created_date)) : '-' ?></span>
                                    </small>
                                </td>
                                <td class="parent-status-cell">
                                    <?php if ($curr_req_status == 'Pending') { ?>
                                        <?php if ($user_type == 1 || check_permission('on_site_request_accept_reject')) { ?>
                                            <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                                <button type="button" class="btn btn-xs btn-success req_accept_btn" data-purchase_id="<?= $req->id ?>" data-action="accept" title="Accept Purchase Request">
                                                    <i class="fa fa-check"></i> Accept
                                                </button>
                                                <button type="button" class="btn btn-xs btn-danger req_reject_btn" data-purchase_id="<?= $req->id ?>" title="Reject Purchase Request">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            </div>
                                        <?php } else { ?>
                                            <span class="label label-warning">Pending Approval</span>
                                        <?php } ?>
                                    <?php } else if ($curr_req_status == 'Rejected' || $req->status == 'Reject') { ?>
                                        <span class="label label-danger">Rejected</span>
                                        <?php if (!empty($req->remark)) { ?>
                                            <small class="text-danger" style="display:block; font-style:italic;">Reason: <?= html_escape($req->remark) ?></small>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php if ($curr_req_status == 'Completed') { ?>
                                            <span class="label label-success">Completed</span>
                                        <?php } else if ($curr_req_status == 'Partial') { ?>
                                            <span class="label label-info">Partial Approved</span>
                                        <?php } else { ?>
                                            <span class="label label-success">Accepted</span>
                                        <?php } ?>
                                        <?php if (!empty($parent_approver)) { ?>
                                            <small class="text-success" style="display:block; margin-top:2px; font-weight: 600;">
                                                <i class="fa fa-check-circle"></i> Approved by <?= html_escape($parent_approver) ?>
                                            </small>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                                <td class="parent-delivery-status-cell">
                                    <?php
                                    $del_status = !empty($req->delivery_status) ? $req->delivery_status : 'Pending';

                                    $del_badge_class = 'label-warning';
                                    if ($del_status == 'Completed' || $del_status == 'Accept Full' || $del_status == 'Recieved' || $del_status == 'Received') {
                                        $del_badge_class = 'label-success';
                                    } else if ($del_status == 'Partial' || $del_status == 'Approve Partial' || $del_status == 'Partially Received' || $del_status == 'Partially Approved') {
                                        $del_badge_class = 'label-info';
                                    } else if ($del_status == 'Reject' || $del_status == 'Rejected') {
                                        $del_badge_class = 'label-danger';
                                    }
                                    ?>
                                    <span class="label <?= $del_badge_class ?>"><?= html_escape($del_status) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $is_req_accepted = !empty($curr_req_status) && !in_array($curr_req_status, array('Pending', 'Reject', 'Rejected', 'Unaccepted'));
                                    $disabled_btn_attr = $is_req_accepted ? '' : 'disabled="disabled"';
                                    $disabled_btn_class = $is_req_accepted ? '' : ' disabled';
                                    ?>
                                    <!-- Items List Button -->
                                    <button type="button"
                                        class="btn btn-xs btn-info view_items_btn<?= $disabled_btn_class ?>"
                                        <?= $disabled_btn_attr ?>
                                        data-purchase_id="<?= $req->id ?>"
                                        data-req_no="<?= $req_no_str ?>"
                                        data-req_status="<?= html_escape($curr_req_status) ?>"
                                        data-delivery_status="<?= html_escape($del_status) ?>"
                                        data-remark="<?= html_escape($req->remark ?: '') ?>"
                                        data-approver_name="<?= html_escape($parent_approver) ?>"
                                        data-approve_date="<?= html_escape($parent_approve_date_str) ?>"
                                        data-items='<?= json_encode($items, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                        data-photos='<?= html_escape($req->upload_file ?: '[]') ?>'
                                        title="<?= $is_req_accepted ? 'View Item List & Photos' : 'Accept request to view items' ?>">
                                        <i class="fa fa-list"></i> Item List
                                    </button>

                                    <?php
                                    $photo_list = !empty($req->upload_file) ? json_decode($req->upload_file, true) : [];
                                    $photo_count = is_array($photo_list) ? count($photo_list) : 0;
                                    ?>
                                    <!-- Photos Gallery Badge Button -->
                                    <button type="button"
                                        class="btn btn-xs btn-default view_items_btn<?= $disabled_btn_class ?>"
                                        <?= $disabled_btn_attr ?>
                                        data-purchase_id="<?= $req->id ?>"
                                        data-req_no="<?= $req_no_str ?>"
                                        data-req_status="<?= html_escape($curr_req_status) ?>"
                                        data-delivery_status="<?= html_escape($del_status) ?>"
                                        data-remark="<?= html_escape($req->remark ?: '') ?>"
                                        data-items='<?= json_encode($items, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                        data-photos='<?= html_escape($req->upload_file ?: '[]') ?>'
                                        title="<?= $is_req_accepted ? 'View / Upload Site Photos' : 'Accept request to view photos' ?>">
                                        <i class="fa fa-camera text-primary"></i> Photos (<span class="photo_count_badge"><?= $photo_count ?></span>)
                                    </button>

                                    <!-- PDF Preview / Download Button -->
                                    <button type="button"
                                        class="btn btn-xs btn-danger pdf_preview_btn"
                                        data-id="<?= $req->id ?>"
                                        data-req_no="<?= $req_no_str ?>"
                                        data-pdf_url="<?= base_url('admin/client/on_site_purchase_pdf/' . $req->id . '/0') ?>"
                                        data-download_url="<?= base_url('admin/client/on_site_purchase_pdf/' . $req->id . '/1') ?>"
                                        title="View & Download PDF">
                                        <i class="fa fa-file-pdf-o"></i> View PDF
                                    </button>

                                    <?php if ($user_type == 1 || check_permission('create_on_site_purchase_request')) { ?>
                                        <!-- Delete Request Button -->
                                        <a href="<?= base_url('admin/client/delete_on_site_purchase_request_new/' . $req->id . '/' . $req->client_id) ?>"
                                            class="btn btn-xs btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this purchase request and all its items?');"
                                            title="Delete Request">
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No purchase requests found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php
        // Build pagination query string helper
        $query_params = array_filter(array(
            'client_id'  => $get_client_id,
            'status'     => $get_status,
            'start_date' => $get_start_date,
            'end_date'   => $get_end_date,
            'limit'      => $get_limit
        ), function($v) { return $v !== '' && $v !== null; });

        $start_entry = ($total_rows > 0) ? ($offset + 1) : 0;
        $end_entry   = min($offset + $get_limit, $total_rows);

        function build_on_site_page_url($page_num, $params) {
            $params['page'] = $page_num;
            return current_url() . '?' . http_build_query($params);
        }
        ?>

        <!-- Pagination Controls -->
        <div class="row" style="margin-top: 15px; display: flex; align-items: center; flex-wrap: wrap;">
            <div class="col-sm-6">
                <span class="text-muted" style="font-size: 13px;">
                    Showing <strong><?= $start_entry ?></strong> to <strong><?= $end_entry ?></strong> of <strong><?= $total_rows ?></strong> entries
                </span>
            </div>
            <div class="col-sm-6 text-right">
                <?php if (!empty($total_pages) && $total_pages > 1): ?>
                    <ul class="pagination pagination-sm" style="margin: 0;">
                        <!-- First & Previous -->
                        <?php if ($current_page > 1): ?>
                            <li><a href="<?= build_on_site_page_url(1, $query_params) ?>" title="First Page">&laquo;&laquo;</a></li>
                            <li><a href="<?= build_on_site_page_url($current_page - 1, $query_params) ?>" title="Previous Page">&laquo;</a></li>
                        <?php else: ?>
                            <li class="disabled"><span>&laquo;&laquo;</span></li>
                            <li class="disabled"><span>&laquo;</span></li>
                        <?php endif; ?>

                        <!-- Page Numbers Range -->
                        <?php
                        $start_p = max(1, $current_page - 2);
                        $end_p   = min($total_pages, $current_page + 2);

                        for ($p = $start_p; $p <= $end_p; $p++):
                        ?>
                            <li class="<?= ($p == $current_page) ? 'active' : '' ?>">
                                <a href="<?= build_on_site_page_url($p, $query_params) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next & Last -->
                        <?php if ($current_page < $total_pages): ?>
                            <li><a href="<?= build_on_site_page_url($current_page + 1, $query_params) ?>" title="Next Page">&raquo;</a></li>
                            <li><a href="<?= build_on_site_page_url($total_pages, $query_params) ?>" title="Last Page">&raquo;&raquo;</a></li>
                        <?php else: ?>
                            <li class="disabled"><span>&raquo;</span></li>
                            <li class="disabled"><span>&raquo;&raquo;</span></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Custom Professional CSS for On-Site Purchase Modal -->
<style>
#purchase_items_modal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
#purchase_items_modal .modal-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    color: #ffffff !important;
    padding: 16px 24px;
    border-bottom: 1px solid #334155;
}
#purchase_items_modal .modal-title {
    color: #ffffff !important;
    font-weight: 600;
    font-size: 16px;
    margin: 0;
}
#purchase_items_modal .table-responsive {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
}
#purchase_items_modal .table > thead > tr > th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px 14px;
    vertical-align: middle;
}
#purchase_items_modal .table > tbody > tr > td {
    padding: 12px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 13px;
}
#purchase_items_modal .table > tbody > tr:hover {
    background-color: #f8fafc;
}
.badge-pill-custom {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.02em;
}
.badge-pill-pending { background-color: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
.badge-pill-ordered { background-color: #f0f9ff; color: #0284c7; border: 1px solid #e0f2fe; }
.badge-pill-received { background-color: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
.badge-pill-reject { background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
.badge-qty {
    background-color: #eff6ff;
    color: #2563eb;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    border: 1px solid #dbeafe;
    display: inline-block;
}
.btn-material-select {
    background-color: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
    padding: 5px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
    transition: all 0.2s ease;
}
.btn-material-select:hover {
    background-color: #f8fafc;
    color: #0f172a;
    border-color: #94a3b8;
}
.btn-material-sent {
    background-color: #2563eb;
    color: #ffffff;
    border: 1px solid #1d4ed8;
    padding: 5px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12px;
    box-shadow: 0 1px 3px 0 rgba(37, 99, 235, 0.3);
    transition: all 0.2s ease;
}
.btn-material-sent:hover {
    background-color: #1d4ed8;
    color: #ffffff;
}
.btn-action-receive {
    background-color: #10b981;
    color: #ffffff;
    border: none;
    padding: 5px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 11px;
    box-shadow: 0 1px 3px 0 rgba(16, 185, 129, 0.3);
    transition: all 0.2s ease;
}
.btn-action-receive:hover {
    background-color: #059669;
    color: #ffffff;
}
.btn-action-reject {
    background-color: #ef4444;
    color: #ffffff;
    border: none;
    padding: 5px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 11px;
    box-shadow: 0 1px 3px 0 rgba(239, 68, 68, 0.3);
    transition: all 0.2s ease;
}
.btn-action-reject:hover {
    background-color: #dc2626;
    color: #ffffff;
}
#purchase_photos_section {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 24px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
}
#purchase_photos_section .panel-heading {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 18px;
}
#purchase_items_modal .modal-footer {
    background-color: #f8fafc !important;
    border-top: 1px solid #e2e8f0 !important;
    padding: 14px 24px;
}
.btn-footer-save {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    padding: 7px 16px !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 4px 0 rgba(16, 185, 129, 0.25) !important;
    transition: all 0.2s ease !important;
}
.btn-footer-save:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}
.btn-footer-close {
    background-color: #ffffff !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    padding: 7px 18px !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
}
.btn-footer-close:hover {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
}
</style>

<!-- Item List View Modal -->
<div class="modal fade" id="purchase_items_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span style="color:white; opacity:0.8;">&times;</span>
                </button>
                <div class="row" style="display: flex; align-items: center;">
                    <div class="col-md-6">
                        <h4 class="modal-title">
                            <i class="fa fa-cubes" style="color:#38bdf8; margin-right:8px;"></i> Purchase Request Items: <span id="modal_req_no" style="color:#38bdf8; font-weight:700;"></span>
                        </h4>
                    </div>
                    <div class="col-md-6 text-right" id="modal_status_action_container">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">#</th>
                                <th>Item Category</th>
                                <th>Item Description</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Brand</th>
                                <th>Remarks</th>
                                <th style="width: 120px;">Material</th>
                                <th>Delivery Status</th>
                                <th>Received / Approved By & Date</th>
                                <th style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="modal_items_body">
                            <!-- Items populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Photos & Gallery Section -->
                <div class="panel panel-default" id="purchase_photos_section">
                    <div class="panel-heading">
                        <h4 class="panel-title" style="font-size: 14px; font-weight: 600; color: #1e293b; margin: 0;">
                            <i class="fa fa-camera text-primary" style="margin-right:6px; color:#2563eb;"></i> Purchase Request Site Photos & Attachments (<span id="modal_photo_count_text">0</span>)
                            <small class="pull-right text-muted" id="photo_section_subtitle" style="font-size: 12px; font-weight: normal;">Upload photos for this purchase request</small>
                        </h4>
                    </div>
                    <div class="panel-body" style="padding: 16px;">
                        <div class="well well-sm upload-well">
                            <div class="row" style="display: flex; align-items: center;">
                                <div class="col-sm-8">
                                    <label class="btn btn-sm btn-primary b0" style="cursor: pointer; margin-bottom: 0; background-color: #2563eb; border-color: #1d4ed8; border-radius: 6px; font-weight: 600; padding: 6px 14px;">
                                        <i class="fa fa-cloud-upload"></i> Click / Upload Photos
                                        <input type="file" id="modal_photo_upload_input" multiple accept="image/*" capture="environment" style="display: none;">
                                    </label>
                                    <span class="text-muted text-xs" style="margin-left: 10px; font-size: 12px;">
                                        <i class="fa fa-info-circle text-info"></i> Multiple images (JPG, PNG, WEBP) supported.
                                    </span>
                                </div>
                                <div class="col-sm-4 text-right">
                                    <span id="photo_upload_spinner" style="display: none;" class="text-info font-bold">
                                        <i class="fa fa-spinner fa-spin"></i> Uploading photos...
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div id="modal_photos_gallery_grid" class="row" style="margin-left: -5px; margin-right: -5px;">
                            <!-- Photo cards populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="form-inline" id="modal_delivery_status_footer_container" style="display: flex; align-items: center; gap: 8px;">
                    <label style="margin-bottom: 0; font-weight: 600; color: #334155; font-size: 13px;">Delivery Status: </label>
                    <select class="form-control input-sm modal_delivery_status_select" style="font-weight: 600; width: 140px; border-radius: 6px; border: 1px solid #cbd5e1; height: 32px; padding: 4px 8px; color: #1e293b; background-color: #ffffff;">
                        <option value="Pending">Pending</option>
                        <option value="Partial">Partial</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <button type="button" class="btn btn-footer-save" id="save_delivery_status_btn">
                        <i class="fa fa-save"></i> Save Status
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- PDF Preview & Download Modal -->
<div class="modal fade" id="pdf_preview_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 85%;">
        <div class="modal-content">
            <div class="modal-header bg-danger" style="background-color: #d9534f; color: white; padding: 12px 15px;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" style="color: white; display: inline-block; margin-right: 15px;">
                    <i class="fa fa-file-pdf-o"></i> PDF Preview: <span id="pdf_modal_req_no"></span>
                </h4>
                <div class="pull-right" style="margin-right: 25px;">
                    <a href="" id="pdf_modal_fullscreen_btn" target="_blank" class="btn btn-xs btn-default">
                        <i class="fa fa-external-link"></i> Open in New Tab
                    </a>
                    <a href="" id="pdf_modal_download_btn" class="btn btn-xs btn-success" style="margin-left: 5px;">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                </div>
            </div>

            <div class="modal-body" style="padding: 0; background: #525659;">
                <div id="pdf_iframe_container" style="height: 650px; width: 100%;">
                    <!-- Iframe inserted dynamically -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Photo Lightbox Modal -->
<div class="modal fade" id="photo_lightbox_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 70%; max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header bg-primary" style="background-color: #337ab7; color: white; padding: 10px 15px;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
                <h4 class="modal-title" style="color: white;"><i class="fa fa-image"></i> Photo Preview</h4>
            </div>
            <div class="modal-body text-center" style="background: #111; padding: 20px;">
                <img id="lightbox_image" src="" class="img-responsive" style="max-height: 550px; margin: 0 auto; border-radius: 4px; box-shadow: 0 0 15px rgba(0,0,0,0.7);">
            </div>
            <div class="modal-footer" style="padding: 10px 15px;">
                <a href="" id="lightbox_download_btn" class="btn btn-success btn-sm" target="_blank" download><i class="fa fa-download"></i> Download</a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Remark Bootstrap Modal -->
<div class="modal fade" id="rejection_remark_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header bg-danger" style="background-color: #d9534f; color: white; padding: 12px 15px;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
                <h4 class="modal-title" style="color: white;"><i class="fa fa-times-circle"></i> Reject Purchase Request</h4>
            </div>
            <form id="rejection_remark_form">
                <input type="hidden" id="reject_target_type" value="request">
                <input type="hidden" id="reject_target_id" value="">

                <div class="modal-body" style="padding: 20px;">
                    <div class="form-group mb0">
                        <label for="reject_remark_text" class="control-label font-bold" style="margin-bottom: 8px;">
                            Reason for Rejection <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reject_remark_text" rows="4" placeholder="Enter reason for rejecting..." required style="resize: vertical;"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 10px 15px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm" id="submit_rejection_btn">
                        <i class="fa fa-paper-plane"></i> Submit Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var isListAllowed = <?= ($user_type == 1 || check_permission('on_site_purchase_request_list') || check_permission('create_on_site_purchase_request') || check_permission('on_site_request_accept_reject') || check_permission('item_received_approved') || check_permission('onsite_purchase_request_only_view') || check_permission('onsite_purchase_request_edit')) ? 'true' : 'false' ?>;
    var isCreateAllowed = <?= ($user_type == 1 || check_permission('create_on_site_purchase_request')) ? 'true' : 'false' ?>;
    var isAcceptRejectAllowed = <?= ($user_type == 1 || check_permission('on_site_request_accept_reject')) ? 'true' : 'false' ?>;
    var isItemApproveAllowed = <?= ($user_type == 1 || check_permission('item_received_approved')) ? 'true' : 'false' ?>;
    var currentActivePurchaseId = 0;

    function updateModalHeaderStatus(reqStatus, purchaseId, remark, approverName, approveDate, deliveryStatus, hasItems) {
        var html = '';
        var approverBadge = (approverName && approveDate) ? `<span style="color:#dff0d8; font-size:11px; margin-left:8px; font-weight:bold;"><i class="fa fa-check-circle"></i> Approved by ${approverName} (${approveDate})</span>` : ((approverName) ? `<span style="color:#dff0d8; font-size:11px; margin-left:8px; font-weight:bold;"><i class="fa fa-check-circle"></i> Approved by ${approverName}</span>` : '');

        if (reqStatus === 'Pending') {
            if (isAcceptRejectAllowed) {
                html = `
                    <div style="display: flex; gap: 5px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                        <span style="color:white; font-size:12px; margin-right: 5px;">Approval Actions: </span>
                        <button type="button" class="btn btn-xs btn-success req_accept_btn" data-purchase_id="${purchaseId}" data-action="accept" title="Accept Full Request">
                            <i class="fa fa-check"></i> Accept Full
                        </button>
                        <button type="button" class="btn btn-xs btn-info req_accept_btn" data-purchase_id="${purchaseId}" data-action="accept_partial" title="Approve Partial Request">
                            <i class="fa fa-check-circle"></i> Approve Partial
                        </button>
                        <button type="button" class="btn btn-xs btn-danger req_reject_btn" data-purchase_id="${purchaseId}">
                            <i class="fa fa-times"></i> Reject
                        </button>
                    </div>`;
            } else {
                html = '<span class="label label-warning">Pending Approval</span>';
            }
        } else if (reqStatus === 'Rejected') {
            var rText = remark ? ` (${remark})` : '';
            html = `<span class="label label-danger">Rejected${rText}</span>`;
        } else {
            var reqBadgeClass = reqStatus === 'Completed' ? 'label-success' : (reqStatus === 'Partial' ? 'label-info' : 'label-success');
            var reqStatusText = reqStatus === 'Partial' ? 'Partial Approved' : (reqStatus === 'Accepted' ? 'Accepted' : reqStatus);
            html = `<span class="label ${reqBadgeClass}" style="font-size:11px; padding:4px 8px;">${reqStatusText}</span> ${approverBadge}`;
        }
        $('#modal_status_action_container').html(html);

        var currentDelStatus = deliveryStatus || 'Pending';
        var selectedVal = (currentDelStatus === 'Completed' || currentDelStatus === 'Accept Full' || currentDelStatus === 'Recieved' || currentDelStatus === 'Received') ? 'Completed' : ((currentDelStatus === 'Partial' || currentDelStatus === 'Approve Partial' || currentDelStatus === 'Partially Received') ? 'Partial' : 'Pending');

        $('.modal_delivery_status_select').val(selectedVal).data('purchase_id', purchaseId).prop('disabled', true);
        if (hasItems === false || reqStatus === 'Pending' || reqStatus === 'Rejected') {
            $('#save_delivery_status_btn').prop('disabled', true).attr('title', 'Disabled for this request');
        } else {
            $('#save_delivery_status_btn').prop('disabled', false).removeAttr('title');
        }
    }

    function renderModalPhotos(photos) {
        var html = '';
        var count = 0;

        if (typeof photos === 'string') {
            try {
                photos = JSON.parse(photos);
            } catch (e) {
                photos = [];
            }
        }
        if (photos && Array.isArray(photos)) {
            count = photos.length;
        } else {
            photos = [];
        }

        $('#modal_photo_count_text').text(count);

        if (photos.length > 0) {
            $.each(photos, function(index, p) {
                var imgUrl = "<?= base_url() ?>" + (p.path || '');
                var isImg = p.is_image !== false;
                var dateStr = p.date || '';

                html += `<div class="col-xs-6 col-sm-4 col-md-3" style="padding: 5px;" data-file-name="${p.fileName}">
                    <div class="thumbnail" style="margin-bottom: 10px; position: relative; border: 1px solid #ddd; padding: 4px; background: #fff;">`;

                if (isImg) {
                    html += `<div style="height: 120px; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center; cursor: pointer;" class="preview_photo_thumb" data-url="${imgUrl}">
                        <img src="${imgUrl}" style="max-height: 120px; width: 100%; object-fit: cover;" class="img-rounded">
                    </div>`;
                } else {
                    html += `<div style="height: 120px; background: #e9ecef; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <i class="fa fa-file-pdf-o text-danger" style="font-size: 40px;"></i>
                        <small class="text-muted mt-xs" style="font-size: 10px; word-break: break-all; padding: 0 4px;">${p.fileName}</small>
                    </div>`;
                }

                html += `<div class="caption text-center" style="padding: 6px 0 2px 0;">
                    <small class="text-muted" style="font-size: 10px; display: block;">${dateStr}</small>
                    <div style="margin-top: 4px;">
                        <a href="${imgUrl}" target="_blank" class="btn btn-xs btn-info" title="View Full"><i class="fa fa-eye"></i> View</a>
                        <button type="button" class="btn btn-xs btn-danger delete_photo_btn" data-file="${p.fileName}" title="Delete Photo"><i class="fa fa-trash"></i></button>
                    </div>
                </div>`;

                html += `</div></div>`;
            });
        } else {
            html = '<div class="col-xs-12 text-center text-muted" style="padding: 20px;">No photos uploaded for this purchase request yet.</div>';
        }

        $('#modal_photos_gallery_grid').html(html);
    }

    function renderModalItems(items, reqStatus, previewDeliveryStatus) {
        window.currentActiveItems = items;
        window.currentActiveReqStatus = reqStatus;

        var allItemsSent = (items && items.length > 0);
        var anyItemSent = false;
        if (items && items.length > 0) {
            $.each(items, function(i, it) {
                if (it.status === 'Ordered' || it.status === 'Recieved' || it.status === 'Received') {
                    anyItemSent = true;
                } else {
                    allItemsSent = false;
                }
            });
        }

        var autoDelStatus = 'Pending';
        if (allItemsSent) {
            autoDelStatus = 'Completed';
        } else if (anyItemSent) {
            autoDelStatus = 'Partial';
        }

        var currentDelStatus = previewDeliveryStatus || autoDelStatus;
        $('.modal_delivery_status_select').val(currentDelStatus);

        var html = '';
        if (items && items.length > 0) {
            $.each(items, function(index, item) {
                var statusBadge = '';
                if (item.status === 'Recieved' || item.status === 'Received') {
                    statusBadge = '<span class="badge-pill-custom badge-pill-received"><i class="fa fa-check-circle"></i> Received at Site</span>';
                } else if (item.status === 'Reject' || item.status === 'Rejected') {
                    statusBadge = '<span class="badge-pill-custom badge-pill-reject"><i class="fa fa-times-circle"></i> Reject</span>';
                } else if (item.status === 'Ordered') {
                    statusBadge = '<span class="badge-pill-custom badge-pill-ordered"><i class="fa fa-clock-o"></i> Approve Partial</span>';
                } else {
                    statusBadge = '<span class="badge-pill-custom badge-pill-pending"><i class="fa fa-clock-o"></i> Pending</span>';
                }

                var approverText = '-';
                if (item.approver_name && item.status_approve_date) {
                    approverText = '<small><strong style="color:#1e293b;">' + item.approver_name + '</strong><br><span class="text-muted" style="font-size:11px;"><i class="fa fa-calendar" style="margin-right:2px;"></i> ' + item.status_approve_date + '</span></small>';
                }

                var isPartialOrAccepted = (reqStatus === 'Partial' || reqStatus === 'Partial Approved' || reqStatus === 'Approve Partial' || reqStatus === 'Accepted' || reqStatus === 'Approved');
                var orderBtn = '-';
                if (isItemApproveAllowed || isAcceptRejectAllowed) {
                    if (currentDelStatus === 'Completed' || currentDelStatus === 'Accept Full') {
                        orderBtn = `<button type="button" class="btn btn-xs btn-material-sent item_order_toggle_btn" data-id="${item.id}" data-ordered="0" title="Click to Un-send"><i class="fa fa-check-square"></i> Send</button>`;
                    } else {
                        if (item.status === 'Ordered' || item.status === 'Recieved' || item.status === 'Received') {
                            orderBtn = `<button type="button" class="btn btn-xs btn-material-sent item_order_toggle_btn" data-id="${item.id}" data-ordered="0" title="Click to Un-send"><i class="fa fa-check-square"></i> Send</button>`;
                        } else {
                            orderBtn = `<button type="button" class="btn btn-xs btn-material-select item_order_toggle_btn" data-id="${item.id}" data-ordered="1" title="Click to Send"><i class="fa fa-square-o"></i> Select to Send</button>`;
                        }
                    }
                } else {
                    if (item.status === 'Ordered' || item.status === 'Recieved' || item.status === 'Received' || currentDelStatus === 'Completed') {
                        orderBtn = '<span class="badge-pill-custom badge-pill-received"><i class="fa fa-check"></i> Send</span>';
                    } else {
                        orderBtn = '<span class="badge-pill-custom badge-pill-pending">Select to Send</span>';
                    }
                }

                var actionBtn = '-';
                if (isItemApproveAllowed && item.status !== 'Recieved' && item.status !== 'Received' && item.status !== 'Reject') {
                    if (reqStatus === 'Completed' || item.status === 'Ordered') {
                        actionBtn = `
                            <div style="display:flex; gap:4px; align-items:center;">
                                <button type="button" class="btn btn-xs btn-action-receive ajax_item_action_btn" data-id="${item.id}" data-status="Recieved" title="Receive Item">
                                    <i class="fa fa-check"></i> Receive
                                </button>
                                <button type="button" class="btn btn-xs btn-action-reject ajax_item_action_btn" data-id="${item.id}" data-status="Reject" title="Reject Item">
                                    <i class="fa fa-times"></i> Reject
                                </button>
                            </div>`;
                    } else {
                        actionBtn = '<small class="text-muted" style="font-style:italic;">Send first</small>';
                    }
                }

                var remarkText = item.approval_remarks ? `<p class="text-muted text-xs mt-xs" style="margin-bottom:0; font-style:italic; margin-top:4px;">Remark: ${item.approval_remarks}</p>` : '';

                html += `<tr data-item-id="${item.id}">
                    <td style="font-weight:600; color:#64748b;">${index + 1}</td>
                    <td><strong style="color:#0f172a;">${item.item_category || '-'}</strong></td>
                    <td>${item.item || '-'}</td>
                    <td><span class="badge-qty">${item.qty || '-'}</span></td>
                    <td>${item.unit || '-'}</td>
                    <td>${item.brand || '-'}</td>
                    <td style="color:#64748b;">${item.remarks || '-'}</td>
                    <td class="item-order-cell">${orderBtn}</td>
                    <td class="item-status-cell">${statusBadge} ${remarkText}</td>
                    <td class="item-approver-cell">${approverText}</td>
                    <td class="item-action-cell">${actionBtn}</td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="11" class="text-center text-muted">No items found for this purchase request.</td></tr>';
        }

        $('#modal_items_body').html(html);
        $('.modal_delivery_status_select').prop('disabled', true);
        if (!items || items.length === 0) {
            $('#save_delivery_status_btn').prop('disabled', true).attr('title', 'No items in purchase request');
        } else {
            $('#save_delivery_status_btn').prop('disabled', false).removeAttr('title');
        }
    }

    $(document).ready(function() {
        $(document).on('click', '.view_items_btn', function(e) {
            if ($(this).is(':disabled') || $(this).hasClass('disabled') || $(this).attr('disabled')) {
                e.preventDefault();
                return false;
            }
            var $btn = $(this);
            var purchaseId = $btn.data('purchase_id');
            currentActivePurchaseId = purchaseId;
            var reqNo = $btn.data('req_no');
            var reqStatus = $btn.data('req_status') || 'Pending';
            var delStatus = $btn.data('delivery_status') || 'Pending';
            var remark = $btn.data('remark') || '';
            var approverName = $btn.data('approver_name') || '';
            var approveDate = $btn.data('approve_date') || '';
            var rawItems = $btn.data('items');
            var rawPhotos = $btn.data('photos');

            var items = [];
            if (typeof rawItems === 'string') {
                try { items = JSON.parse(rawItems); } catch (e) { items = []; }
            } else if (Array.isArray(rawItems)) {
                items = rawItems;
            }
            var hasItems = (items && items.length > 0);

            $('#modal_req_no').text(reqNo);
            updateModalHeaderStatus(reqStatus, purchaseId, remark, approverName, approveDate, delStatus, hasItems);
            renderModalItems(items, reqStatus);
            renderModalPhotos(rawPhotos);

            $('#purchase_items_modal').modal('show');
        });

        $(document).on('click', '.pdf_preview_btn', function(e) {
            e.preventDefault();
            var reqNo = $(this).data('req_no');
            var pdfUrl = $(this).data('pdf_url');
            var downloadUrl = $(this).data('download_url');

            $('#pdf_modal_req_no').text(reqNo);
            $('#pdf_modal_fullscreen_btn').attr('href', pdfUrl);
            $('#pdf_modal_download_btn').attr('href', downloadUrl);

            $('#pdf_iframe_container').html('<iframe src="' + pdfUrl + '" width="100%" height="650px" style="border:none;"></iframe>');
            $('#pdf_preview_modal').modal('show');
        });

        $(document).on('click', '.preview_photo_thumb', function() {
            var imgUrl = $(this).data('url');
            $('#lightbox_image').attr('src', imgUrl);
            $('#lightbox_download_btn').attr('href', imgUrl);
            $('#photo_lightbox_modal').modal('show');
        });

        $(document).on('click', '.req_accept_btn', function() {
            var purchaseId = $(this).data('purchase_id');
            var actionType = $(this).data('action') || 'accept';
            var confirmMsg = actionType === 'accept_partial' ? 'Are you sure you want to approve this request as Partial?' : 'Are you sure you want to Accept this purchase request?';

            if (confirm(confirmMsg)) {
                $.ajax({
                    url: "<?= base_url('admin/client/accept_reject_purchase_request_ajax') ?>",
                    type: "POST",
                    dataType: "json",
                    data: { purchase_id: purchaseId, action: actionType },
                    success: function(res) {
                        if (res.success || res.status) {
                            alert(res.message || 'Request updated successfully');
                            location.reload();
                        } else {
                            alert(res.message || 'Failed to update request');
                        }
                    },
                    error: function() { alert('AJAX Error updating request status'); }
                });
            }
        });

        $(document).on('click', '.req_reject_btn', function() {
            var purchaseId = $(this).data('purchase_id');
            $('#reject_target_type').val('request');
            $('#reject_target_id').val(purchaseId);
            $('#reject_remark_text').val('');
            $('#rejection_remark_modal').modal('show');
        });

        $(document).on('click', '.ajax_item_action_btn', function() {
            var itemId = $(this).data('id');
            var status = $(this).data('status');

            if (status === 'Reject') {
                $('#reject_target_type').val('item');
                $('#reject_target_id').val(itemId);
                $('#reject_remark_text').val('');
                $('#rejection_remark_modal').modal('show');
                return;
            }

            if (confirm('Are you sure you want to mark this item as Received?')) {
                $.ajax({
                    url: "<?= base_url('admin/client/on_site_purchase_request_status_ajax') ?>",
                    type: "POST",
                    dataType: "json",
                    data: { request_id: itemId, status: status },
                    success: function(res) {
                        if (res.success || res.status) {
                            var masterBtn = $('.view_items_btn[data-purchase_id="' + res.purchase_id + '"]');
                            if (masterBtn.length) {
                                var itemsData = masterBtn.data('items');
                                if (typeof itemsData === 'string') {
                                    try { itemsData = JSON.parse(itemsData); } catch(e) { itemsData = []; }
                                }
                                if (itemsData && Array.isArray(itemsData)) {
                                    $.each(itemsData, function(i, itemObj) {
                                        if (itemObj.id == itemId) {
                                            itemObj.status = res.item_status || status;
                                            itemObj.approver_name = res.approver_name;
                                            itemObj.status_approve_date = res.approve_date;
                                            itemObj.approval_remarks = res.remarks;
                                        }
                                    });
                                    masterBtn.data('items', itemsData);
                                    var reqStatus = masterBtn.data('req_status') || 'Partial';
                                    renderModalItems(itemsData, reqStatus);
                                }
                            }
                        } else {
                            alert(res.message || 'Action failed');
                        }
                    },
                    error: function() { alert('AJAX Error'); }
                });
            }
        });

        $('#rejection_remark_form').on('submit', function(e) {
            e.preventDefault();
            var targetType = $('#reject_target_type').val();
            var targetId = $('#reject_target_id').val();
            var remark = $('#reject_remark_text').val().trim();

            if (!remark) {
                alert('Please enter a reason for rejection.');
                return;
            }

            if (targetType === 'request') {
                $.ajax({
                    url: "<?= base_url('admin/client/accept_reject_purchase_request_ajax') ?>",
                    type: "POST",
                    dataType: "json",
                    data: { purchase_id: targetId, action: 'reject', remark: remark },
                    success: function(res) {
                        if (res.success || res.status) {
                            $('#rejection_remark_modal').modal('hide');
                            var masterBtn = $('.view_items_btn[data-purchase_id="' + targetId + '"]');
                            if (masterBtn.length) {
                                masterBtn.data('req_status', res.request_status);
                                masterBtn.data('items', res.items);

                                var parentCell = masterBtn.closest('tr').find('.parent-status-cell');
                                parentCell.html(`
                                    <span class="label label-danger">Rejected</span>
                                    <small class="text-danger" style="display:block; font-style:italic;">Reason: ${res.remark}</small>
                                `);
                            }

                            if ($('#purchase_items_modal').is(':visible') && currentActivePurchaseId == targetId) {
                                updateModalHeaderStatus(res.request_status, targetId, res.remark);
                                renderModalItems(res.items, res.request_status);
                            }
                        } else {
                            alert(res.message || 'Rejection failed');
                        }
                    },
                    error: function() { alert('AJAX Error'); }
                });
            } else {
                $.ajax({
                    url: "<?= base_url('admin/client/on_site_purchase_request_status_ajax') ?>",
                    type: "POST",
                    dataType: "json",
                    data: { request_id: targetId, status: 'Reject', approval_remarks: remark },
                    success: function(res) {
                        if (res.success || res.status) {
                            $('#rejection_remark_modal').modal('hide');
                            var masterBtn = $('.view_items_btn[data-purchase_id="' + res.purchase_id + '"]');
                            if (masterBtn.length) {
                                var itemsData = masterBtn.data('items');
                                if (typeof itemsData === 'string') {
                                    try { itemsData = JSON.parse(itemsData); } catch(e) { itemsData = []; }
                                }
                                if (itemsData && Array.isArray(itemsData)) {
                                    $.each(itemsData, function(i, itemObj) {
                                        if (itemObj.id == targetId) {
                                            itemObj.status = 'Reject';
                                            itemObj.approver_name = res.approver_name;
                                            itemObj.status_approve_date = res.approve_date;
                                            itemObj.approval_remarks = remark;
                                        }
                                    });
                                    masterBtn.data('items', itemsData);
                                    var reqStatus = masterBtn.data('req_status') || 'Partial';
                                    renderModalItems(itemsData, reqStatus);
                                }
                            }
                        } else {
                            alert(res.message || 'Rejection failed');
                        }
                    },
                    error: function() { alert('AJAX Error'); }
                });
            }
        });

        $(document).on('change', '.modal_delivery_status_select', function() {
            var selVal = $(this).val();
            var items = window.currentActiveItems || [];
            var reqStatus = window.currentActiveReqStatus || 'Partial';
            renderModalItems(items, reqStatus, selVal);
        });

        $(document).on('click', '#save_delivery_status_btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var select = $('.modal_delivery_status_select');
            var purchaseId = select.data('purchase_id') || currentActivePurchaseId;
            var deliveryStatusVal = select.val();

            btn.prop('disabled', true);
            select.prop('disabled', true);

            $.ajax({
                url: "<?= base_url('admin/client/update_on_site_purchase_parent_status_ajax') ?>",
                type: "POST",
                dataType: "json",
                data: { purchase_id: purchaseId, delivery_status: deliveryStatusVal },
                success: function(res) {
                    btn.prop('disabled', false);
                    select.prop('disabled', false);
                    if (res.success || res.status) {
                        alert(res.message || 'Delivery status saved successfully');
                        location.reload();
                        return;
                        var masterBtn = $('.view_items_btn[data-purchase_id="' + purchaseId + '"]');
                        if (masterBtn.length) {
                            masterBtn.data('delivery_status', res.delivery_status);
                            if (res.items && Array.isArray(res.items) && res.items.length > 0) {
                                masterBtn.data('items', res.items);
                                var reqStatus = masterBtn.data('req_status') || 'Partial';
                                renderModalItems(res.items, reqStatus);
                            }
                            var delBadgeClass = 'label-warning';
                            if (res.delivery_status === 'Completed' || res.delivery_status === 'Accept Full' || res.delivery_status === 'Recieved' || res.delivery_status === 'Received') {
                                delBadgeClass = 'label-success';
                            } else if (res.delivery_status === 'Partial' || res.delivery_status === 'Approve Partial' || res.delivery_status === 'Partially Received' || res.delivery_status === 'Partially Approved') {
                                delBadgeClass = 'label-info';
                            } else if (res.delivery_status === 'Reject' || res.delivery_status === 'Rejected') {
                                delBadgeClass = 'label-danger';
                            }
                            var delCell = masterBtn.closest('tr').find('.parent-delivery-status-cell');
                            if (delCell.length) {
                                delCell.html('<span class="label ' + delBadgeClass + '">' + res.delivery_status + '</span>');
                            }
                        }
                    } else {
                        alert(res.message || 'Status update failed');
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    select.prop('disabled', false);
                    alert('AJAX Error');
                }
            });
        });

        $(document).on('click', '.item_order_toggle_btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var itemId = btn.data('id');
            var isOrdered = btn.data('ordered');

            btn.prop('disabled', true);

            $.ajax({
                url: "<?= base_url('admin/client/on_site_purchase_item_order_ajax') ?>",
                type: "POST",
                dataType: "json",
                data: { item_id: itemId, is_ordered: isOrdered },
                success: function(res) {
                    btn.prop('disabled', false);
                    if (res.success || res.status) {
                        var masterBtn = $('.view_items_btn[data-purchase_id="' + res.purchase_id + '"]');
                        if (masterBtn.length) {
                            var itemsData = res.items || masterBtn.data('items') || [];
                            masterBtn.data('items', itemsData);
                            if (res.delivery_status) {
                                masterBtn.data('delivery_status', res.delivery_status);
                                var delBadgeClass = 'label-warning';
                                if (res.delivery_status === 'Completed' || res.delivery_status === 'Accept Full' || res.delivery_status === 'Recieved' || res.delivery_status === 'Received') {
                                    delBadgeClass = 'label-success';
                                } else if (res.delivery_status === 'Partial' || res.delivery_status === 'Approve Partial' || res.delivery_status === 'Partially Received' || res.delivery_status === 'Partially Approved') {
                                    delBadgeClass = 'label-info';
                                } else if (res.delivery_status === 'Reject' || res.delivery_status === 'Rejected') {
                                    delBadgeClass = 'label-danger';
                                }
                                var delCell = masterBtn.closest('tr').find('.parent-delivery-status-cell');
                                if (delCell.length) {
                                    delCell.html('<span class="label ' + delBadgeClass + '">' + res.delivery_status + '</span>');
                                }
                            }
                            var reqStatus = masterBtn.data('req_status') || 'Partial';
                            renderModalItems(itemsData, reqStatus);
                        }
                    } else {
                        alert(res.message || 'Update failed');
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    alert('AJAX Error');
                }
            });
        });

        $(document).on('change', '#modal_photo_upload_input', function() {
            var files = this.files;
            if (!files || files.length === 0) return;

            var formData = new FormData();
            formData.append('purchase_id', currentActivePurchaseId);
            for (var i = 0; i < files.length; i++) {
                formData.append('photos[]', files[i]);
            }

            $('#photo_upload_spinner').show();

            $.ajax({
                url: "<?= base_url('admin/client/upload_on_site_purchase_photos_ajax') ?>",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res) {
                    $('#photo_upload_spinner').hide();
                    if (res.status) {
                        renderModalPhotos(res.photos);
                        var $rowBtn = $('tr[data-purchase-row-id="' + currentActivePurchaseId + '"]').find('.view_items_btn');
                        if ($rowBtn.length) {
                            $rowBtn.data('photos', JSON.stringify(res.photos));
                            $rowBtn.find('.photo_count_badge').text(res.photos.length);
                        }
                    } else {
                        alert(res.message || 'Photo upload failed');
                    }
                },
                error: function() {
                    $('#photo_upload_spinner').hide();
                    alert('AJAX error during photo upload');
                }
            });
        });

        $(document).on('click', '.delete_photo_btn', function() {
            var fileName = $(this).data('file');
            if (!confirm('Are you sure you want to delete this photo?')) return;

            $.ajax({
                url: "<?= base_url('admin/client/delete_on_site_purchase_photo_ajax') ?>",
                type: "POST",
                dataType: "json",
                data: { purchase_id: currentActivePurchaseId, file_name: fileName },
                success: function(res) {
                    if (res.status) {
                        renderModalPhotos(res.photos);
                        var $rowBtn = $('tr[data-purchase-row-id="' + currentActivePurchaseId + '"]').find('.view_items_btn');
                        if ($rowBtn.length) {
                            $rowBtn.data('photos', JSON.stringify(res.photos));
                            $rowBtn.find('.photo_count_badge').text(res.photos.length);
                        }
                    } else {
                        alert(res.message || 'Photo delete failed');
                    }
                },
                error: function() { alert('AJAX error deleting photo'); }
            });
        });
    });
</script>