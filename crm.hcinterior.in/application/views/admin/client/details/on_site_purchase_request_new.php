<?php
$user_id        = $this->session->userdata('user_id');
$user_type      = $this->session->userdata('user_type');
$designation_id = $this->session->userdata('designations_id');
$assign_team    = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();

if (!$this->db->field_exists('delivery_status', 'tbl_client_on_site_purchase')) {
    $this->db->query("ALTER TABLE `tbl_client_on_site_purchase` ADD `delivery_status` VARCHAR(100) NULL");
}
if (!$this->db->field_exists('delivery_status', 'tbl_client_on_site_purchase_request')) {
    $this->db->query("ALTER TABLE `tbl_client_on_site_purchase_request` ADD `delivery_status` VARCHAR(100) NULL");
}

$db_purchase_categories = $this->db->where('status', 1)->order_by('category_name', 'ASC')->get('purchase_item_category')->result();
if (empty($db_purchase_categories)) {
    $db_purchase_categories = $this->db->where('status', 1)->order_by('category_name', 'ASC')->get('tbl_purchase_item_category')->result();
}

$db_purchase_brands = $this->db->where('status', 1)->order_by('brand_name', 'ASC')->get('purchase_item_brand')->result();
if (empty($db_purchase_brands)) {
    $db_purchase_brands = $this->db->where('status', 1)->order_by('brand_name', 'ASC')->get('tbl_purchase_item_brand')->result();
}

$cat_options_html = '<option value="">Select Category</option>';
if (!empty($db_purchase_categories)) {
    foreach ($db_purchase_categories as $c_val) {
        $c_name = html_escape($c_val->category_name);
        $cat_options_html .= '<option value="' . $c_name . '" data-id="' . $c_val->id . '">' . $c_name . '</option>';
    }
}

$brand_options_html = '<option value="">Select Brand</option>';
if (!empty($db_purchase_brands)) {
    foreach ($db_purchase_brands as $b_val) {
        $b_name = html_escape($b_val->brand_name);
        $brand_options_html .= '<option value="' . $b_name . '" data-category-id="' . $b_val->purchase_item_category_id . '">' . $b_name . '</option>';
    }
}
$brand_options_html .= '<option value="Other">Other</option>';
?>

<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('On Site Purchase Request (New)') ?>
            </strong>
            <?php if ($user_type == 1 || check_permission('create_on_site_purchase_request')) { ?>
                <button id="toggle_purchase_form" class="btn btn-xs btn-primary pull-right b0">
                    <i class="fa fa-plus"></i> Add Purchase Request
                </button>
            <?php } ?>
        </div>
    </div>

    <div class="panel-body">
        <!-- New Purchase Request Form (Hidden by default) -->
        <div class="new_design mb-lg" style="display: none;">
            <form action="<?= base_url('admin/client/new_on_site_purchase_new') ?>" method="post" class="form-horizontal">
                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">

                <div class="table-responsive mb-md">
                    <table class="table table-bordered table-hover" id="purchase_items_table">
                        <thead>
                            <tr class="bg-primary" style="background-color: #337ab7; color: white;">
                                <th style="width: 18%;">Item Category <span class="text-danger">*</span></th>
                                <th style="width: 22%;">Item <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Unit</th>
                                <th style="width: 18%;">Brand</th>
                                <th style="width: 15%;">Remarks</th>
                                <th style="width: 5%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="purchase_items_body">
                            <tr>
                                <td>
                                    <select name="item_category[]" class="form-control input-sm category-select" required>
                                        <?= $cat_options_html ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="item[]" class="form-control input-sm" placeholder="Item Name / Details" required>
                                </td>
                                <td>
                                    <input type="text" name="qty[]" class="form-control input-sm" placeholder="Qty" required>
                                </td>
                                <td>
                                    <select name="unit[]" class="form-control input-sm">
                                        <option value="Pcs">Pcs</option>
                                        <option value="Sqft">Sqft</option>
                                        <option value="Rft">Rft</option>
                                        <option value="Nos">Nos</option>
                                        <option value="Meter">Meter</option>
                                        <option value="Box">Box</option>
                                        <option value="Packet">Packet</option>
                                        <option value="Set">Set</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Ltr">Ltr</option>
                                        <option value="Feet">Feet</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="brand[]" class="form-control input-sm brand-select">
                                        <?= $brand_options_html ?>
                                    </select>
                                    <input type="text" name="other_brand[]" class="form-control input-sm other-brand-input" placeholder="Enter Brand Name" style="display:none; margin-top: 4px;">
                                </td>
                                <td>
                                    <input type="text" name="remarks[]" class="form-control input-sm" placeholder="Remarks">
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    <button type="button" class="btn btn-xs btn-danger remove-row" title="Remove Row">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mb-lg">
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-info btn-sm" id="add_row_btn">
                            <i class="fa fa-plus-circle"></i> Add Row
                        </button>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-save"></i> Save Purchase Request
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <script>
            var catOptionsHtml = <?= json_encode($cat_options_html) ?>;
            var brandOptionsHtml = <?= json_encode($brand_options_html) ?>;

            $(document).ready(function() {
                $('#toggle_purchase_form').click(function() {
                    $('.new_design').slideToggle('fast');
                });

                $(document).on('change', '.category-select', function() {
                    var selectedCatId = $(this).find('option:selected').data('id');
                    var brandSelect = $(this).closest('tr').find('.brand-select');

                    brandSelect.find('option').each(function() {
                        var catId = $(this).data('category-id');
                        if (!catId || !selectedCatId || catId == selectedCatId || $(this).val() === '' || $(this).val() === 'Other') {
                            $(this).show().prop('disabled', false);
                        } else {
                            $(this).hide().prop('disabled', true);
                        }
                    });

                    if (brandSelect.find('option:selected').is(':disabled')) {
                        brandSelect.val('');
                        brandSelect.trigger('change');
                    }
                });

                $(document).on('change', '.brand-select', function() {
                    var otherInput = $(this).siblings('.other-brand-input');
                    if ($(this).val() === 'Other') {
                        otherInput.show().attr('required', true);
                    } else {
                        otherInput.hide().val('').removeAttr('required');
                    }
                });

                $('#add_row_btn').click(function() {
                    var newRow = `
                    <tr>
                        <td>
                            <select name="item_category[]" class="form-control input-sm category-select" required>
                                ${catOptionsHtml}
                            </select>
                        </td>
                        <td>
                            <input type="text" name="item[]" class="form-control input-sm" placeholder="Item Name / Details" required>
                        </td>
                        <td>
                            <input type="text" name="qty[]" class="form-control input-sm" placeholder="Qty" required>
                        </td>
                        <td>
                            <select name="unit[]" class="form-control input-sm">
                                <option value="Pcs">Pcs</option>
                                <option value="Sqft">Sqft</option>
                                <option value="Rft">Rft</option>
                                <option value="Nos">Nos</option>
                                <option value="Meter">Meter</option>
                                <option value="Box">Box</option>
                                <option value="Packet">Packet</option>
                                <option value="Set">Set</option>
                                <option value="Kg">Kg</option>
                                <option value="Ltr">Ltr</option>
                                <option value="Feet">Feet</option>
                            </select>
                        </td>
                        <td>
                            <select name="brand[]" class="form-control input-sm brand-select">
                                ${brandOptionsHtml}
                            </select>
                            <input type="text" name="other_brand[]" class="form-control input-sm other-brand-input" placeholder="Enter Brand Name" style="display:none; margin-top: 4px;">
                        </td>
                        <td>
                            <input type="text" name="remarks[]" class="form-control input-sm" placeholder="Remarks">
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <button type="button" class="btn btn-xs btn-danger remove-row" title="Remove Row">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>`;
                    $('#purchase_items_body').append(newRow);
                });


                $(document).on('click', '.remove-row', function() {
                    if ($('#purchase_items_body tr').length > 1) {
                        $(this).closest('tr').remove();
                    } else {
                        alert('At least one item row is required.');
                    }
                });
            });
        </script>

        <!-- Master List of Purchase Requests -->
        <?php if ($user_type == 1 || check_permission('on_site_purchase_request_list') || check_permission('create_on_site_purchase_request') || check_permission('on_site_request_accept_reject') || check_permission('item_received_approved') || check_permission('onsite_purchase_request_only_view') || check_permission('onsite_purchase_request_edit')) { ?>
            <?php
            $purchase_requests = $this->db->where('client_id', $client_details->client_id)
                ->order_by('id', 'DESC')
                ->get('tbl_client_on_site_purchase')
                ->result();
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Request No.</th>
                            <th>Total Items</th>
                            <th>Requested By & Date</th>
                            <th style="width: 160px;">Request Status</th>
                            <th style="width: 150px;">Delivery Status</th>
                            <th style="width: 240px;"><?= lang('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($purchase_requests)) {
                            $sl = 1;
                            foreach ($purchase_requests as $req) {
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
                        ?>
                                <tr data-purchase-row-id="<?= $req->id ?>">
                                    <td><?= $sl++ ?></td>
                                    <td>
                                        <strong class="text-primary"><?= $req_no_str ?></strong>
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
                                            <a href="<?= base_url('admin/client/delete_on_site_purchase_request_new/' . $req->id . '/' . $client_details->client_id) ?>"
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
                                <td colspan="6" class="text-center text-muted">No purchase requests found for this client.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</section>

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
                        <?php if ($user_type == 1 || check_permission('onsite_purchase_request_edit')) { ?>
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
                        <?php } ?>

                        <div id="modal_photos_gallery_grid" class="row" style="margin-left: -5px; margin-right: -5px;">
                            <!-- Photo cards populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="form-inline" id="modal_delivery_status_footer_container" style="display: flex; align-items: center; gap: 8px;">
                    <label style="margin-bottom: 0; font-weight: 600; color: #334155; font-size: 13px;">Delivery Status: </label>
                    <select class="form-control input-sm modal_delivery_status_select" disabled="disabled" title="Delivery Status is auto-updated based on item material send status" style="font-weight: 600; width: 140px; border-radius: 6px; border: 1px solid #cbd5e1; height: 32px; padding: 4px 8px; color: #1e293b; background-color: #f1f5f9; cursor: not-allowed;">
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

<!-- Photo Fullscreen Lightbox Modal -->
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
    var isListAllowed = <?= ($user_type == 1 || check_permission('on_site_purchase_request_list') || check_permission('create_on_site_purchase_request') || check_permission('on_site_request_accept_reject') || check_permission('item_received_approved')) ? 'true' : 'false' ?>;
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
                        ${isCreateAllowed ? `<button type="button" class="btn btn-xs btn-danger delete_photo_btn" data-file="${p.fileName}" title="Delete Photo"><i class="fa fa-trash"></i></button>` : ''}
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

    // View Item List Modal Handler
    $(document).on('click', '.view_items_btn', function(e) {
        if ($(this).is(':disabled') || $(this).hasClass('disabled') || $(this).attr('disabled')) {
            e.preventDefault();
            return false;
        }
        var reqNo = $(this).data('req_no');
        var items = $(this).data('items');
        var photos = $(this).data('photos');
        var reqStatus = $(this).data('req_status') || 'Pending';
        var delStatus = $(this).data('delivery_status') || 'Pending';
        var remark = $(this).data('remark') || '';
        var approverName = $(this).data('approver_name') || '';
        var approveDate = $(this).data('approve_date') || '';
        currentActivePurchaseId = $(this).data('purchase_id');

        var parsedItems = [];
        if (typeof items === 'string') {
            try { parsedItems = JSON.parse(items); } catch(e) { parsedItems = []; }
        } else if (Array.isArray(items)) {
            parsedItems = items;
        }
        var hasItems = (parsedItems && parsedItems.length > 0);

        $('#modal_req_no').text(reqNo);
        updateModalHeaderStatus(reqStatus, currentActivePurchaseId, remark, approverName, approveDate, delStatus, hasItems);

        renderModalItems(items, reqStatus);
        renderModalPhotos(photos);
        $('#purchase_items_modal').modal('show');
    });

    // Accept / Partial Approve Request Handler
    $(document).on('click', '.req_accept_btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var purchaseId = btn.data('purchase_id') || currentActivePurchaseId;
        var actionType = btn.data('action') || 'accept';

        btn.prop('disabled', true);

        $.ajax({
            url: "<?= base_url('admin/client/accept_reject_purchase_request_ajax') ?>",
            type: "POST",
            data: {
                purchase_id: purchaseId,
                action: actionType
            },
            dataType: "json",
            success: function(response) {
                btn.prop('disabled', false);
                if (response.success) {
                    var masterBtn = $('.view_items_btn[data-purchase_id="' + purchaseId + '"]');
                    if (masterBtn.length) {
                        masterBtn.data('req_status', response.request_status);
                        masterBtn.data('items', response.items);
                        masterBtn.data('photos', JSON.stringify(response.photos));
                        masterBtn.data('approver_name', response.approver_name);
                        masterBtn.data('approve_date', response.approve_date);

                        var parentBadgeClass = response.request_status === 'Completed' ? 'label-success' : (response.request_status === 'Partial' ? 'label-info' : 'label-success');
                        var parentStatusText = response.request_status === 'Partial' ? 'Partial Approved' : (response.request_status === 'Accepted' ? 'Accepted' : response.request_status);
                        parentCell.html(`
                            <span class="label ${parentBadgeClass}">${parentStatusText}</span>
                            <small class="text-success" style="display:block; margin-top:2px; font-weight: 600;">
                                <i class="fa fa-check-circle"></i> Approved by ${response.approver_name}
                            </small>
                        `);
                    }

                    if ($('#purchase_items_modal').is(':visible') && currentActivePurchaseId == purchaseId) {
                        updateModalHeaderStatus(response.request_status, purchaseId, response.remark, response.approver_name, response.approve_date);
                        renderModalItems(response.items, response.request_status);
                        renderModalPhotos(response.photos);
                    } else {
                        masterBtn.trigger('click');
                    }
                } else {
                    alert(response.message || 'Error processing request');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Something went wrong, please try again.');
            }
        });
    });

    // Open Reject Modal Handler
    $(document).on('click', '.req_reject_btn', function(e) {
        e.preventDefault();
        var purchaseId = $(this).data('purchase_id') || currentActivePurchaseId;
        $('#reject_target_type').val('request');
        $('#reject_target_id').val(purchaseId);
        $('#reject_remark_text').val('');
        $('#rejection_remark_modal .modal-title').html('<i class="fa fa-times-circle"></i> Reject Purchase Request');
        $('#rejection_remark_modal').modal('show');
    });

    // Submit Rejection Modal Form Handler
    $(document).on('submit', '#rejection_remark_form', function(e) {
        e.preventDefault();
        var targetType = $('#reject_target_type').val();
        var targetId = $('#reject_target_id').val();
        var remark = $.trim($('#reject_remark_text').val());

        if (!remark) {
            alert("Please enter a rejection remark.");
            return;
        }

        $('#submit_rejection_btn').prop('disabled', true);

        if (targetType === 'request') {
            $.ajax({
                url: "<?= base_url('admin/client/accept_reject_purchase_request_ajax') ?>",
                type: "POST",
                data: {
                    purchase_id: targetId,
                    action: 'reject',
                    remark: remark
                },
                dataType: "json",
                success: function(response) {
                    $('#submit_rejection_btn').prop('disabled', false);
                    $('#rejection_remark_modal').modal('hide');
                    if (response.success) {
                        var masterBtn = $('.view_items_btn[data-purchase_id="' + targetId + '"]');
                        if (masterBtn.length) {
                            masterBtn.data('req_status', response.request_status);
                            masterBtn.data('items', response.items);

                            var parentCell = masterBtn.closest('tr').find('.parent-status-cell');
                            parentCell.html(`
                                <span class="label label-danger">Rejected</span>
                                <small class="text-danger" style="display:block; font-style:italic;">Reason: ${response.remark}</small>
                            `);
                        }

                        if ($('#purchase_items_modal').is(':visible') && currentActivePurchaseId == targetId) {
                            updateModalHeaderStatus(response.request_status, targetId, response.remark);
                            renderModalItems(response.items, response.request_status);
                        }
                    } else {
                        alert(response.message || 'Error rejecting request');
                    }
                },
                error: function() {
                    $('#submit_rejection_btn').prop('disabled', false);
                    alert('Something went wrong, please try again.');
                }
            });
        } else if (targetType === 'item') {
            $.ajax({
                url: "<?= base_url('admin/client/on_site_purchase_request_status_ajax') ?>",
                type: "POST",
                data: {
                    request_id: targetId,
                    status: 'Reject',
                    approval_remarks: remark
                },
                dataType: "json",
                success: function(response) {
                    $('#submit_rejection_btn').prop('disabled', false);
                    $('#rejection_remark_modal').modal('hide');
                    if (response.success) {
                        var masterBtn = $('.view_items_btn[data-purchase_id="' + response.purchase_id + '"]');
                        if (masterBtn.length) {
                            var itemsData = masterBtn.data('items');
                            if (itemsData) {
                                $.each(itemsData, function(i, itemObj) {
                                    if (itemObj.id == targetId) {
                                        itemObj.status = response.status;
                                        itemObj.approver_name = response.approver_name;
                                        itemObj.status_approve_date = response.approve_date;
                                        itemObj.approval_remarks = response.remarks;
                                    }
                                });
                                masterBtn.data('items', itemsData);
                                var reqStatus = masterBtn.data('req_status') || 'Partial';
                                renderModalItems(itemsData, reqStatus);
                            }
                        }
                    } else {
                        alert(response.message || 'Error rejecting item');
                    }
                },
                error: function() {
                    $('#submit_rejection_btn').prop('disabled', false);
                    alert('Something went wrong, please try again.');
                }
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
            data: {
                purchase_id: purchaseId,
                delivery_status: deliveryStatusVal
            },
            dataType: "json",
            success: function(response) {
                btn.prop('disabled', false);
                select.prop('disabled', false);
                if (response.success || response.status) {
                    alert(response.message || 'Delivery status saved successfully');
                    location.reload();
                    return;
                    var masterBtn = $('.view_items_btn[data-purchase_id="' + purchaseId + '"]');
                    if (masterBtn.length) {
                        masterBtn.data('delivery_status', response.delivery_status);
                        if (response.items && Array.isArray(response.items) && response.items.length > 0) {
                            masterBtn.data('items', response.items);
                            var reqStatus = masterBtn.data('req_status') || 'Partial';
                            renderModalItems(response.items, reqStatus);
                        }
                        var delBadgeClass = 'label-warning';
                        if (response.delivery_status === 'Completed' || response.delivery_status === 'Accept Full' || response.delivery_status === 'Recieved' || response.delivery_status === 'Received') {
                            delBadgeClass = 'label-success';
                        } else if (response.delivery_status === 'Partial' || response.delivery_status === 'Approve Partial' || response.delivery_status === 'Partially Received' || response.delivery_status === 'Partially Approved') {
                            delBadgeClass = 'label-info';
                        } else if (response.delivery_status === 'Reject' || response.delivery_status === 'Rejected') {
                            delBadgeClass = 'label-danger';
                        }
                        var delCell = masterBtn.closest('tr').find('.parent-delivery-status-cell');
                        if (delCell.length) {
                            delCell.html('<span class="label ' + delBadgeClass + '">' + response.delivery_status + '</span>');
                        }
                    }
                } else {
                    alert(response.message || 'Error updating delivery status');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                select.prop('disabled', false);
                alert('Something went wrong, please try again.');
            }
        });
    });

    // Photo Upload AJAX Handler
    $(document).on('change', '#modal_photo_upload_input', function() {
        var files = this.files;
        if (!files || files.length === 0) return;
        if (!currentActivePurchaseId) {
            alert("Please select a purchase request first.");
            return;
        }

        var formData = new FormData();
        formData.append('purchase_id', currentActivePurchaseId);
        $.each(files, function(i, file) {
            formData.append('photos[]', file);
        });

        $('#photo_upload_spinner').show();

        $.ajax({
            url: "<?= base_url('admin/client/upload_on_site_purchase_photos_ajax') ?>",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(response) {
                $('#photo_upload_spinner').hide();
                $('#modal_photo_upload_input').val('');
                if (response.success) {
                    renderModalPhotos(response.photos);
                    var masterBtn = $('.view_items_btn[data-purchase_id="' + response.purchase_id + '"]');
                    if (masterBtn.length) {
                        masterBtn.data('photos', JSON.stringify(response.photos));
                        var parentRow = masterBtn.closest('tr');
                        parentRow.find('.photo_count_badge').text(response.photos.length);
                    }
                } else {
                    alert(response.message || 'Error uploading photos');
                }
            },
            error: function() {
                $('#photo_upload_spinner').hide();
                alert('Something went wrong uploading photos.');
            }
        });
    });

    // Photo Delete AJAX Handler
    $(document).on('click', '.delete_photo_btn', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this photo?')) return;

        var fileName = $(this).data('file');

        $.ajax({
            url: "<?= base_url('admin/client/delete_on_site_purchase_photo_ajax') ?>",
            type: "POST",
            data: {
                purchase_id: currentActivePurchaseId,
                file_name: fileName
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    renderModalPhotos(response.photos);
                    var masterBtn = $('.view_items_btn[data-purchase_id="' + response.purchase_id + '"]');
                    if (masterBtn.length) {
                        masterBtn.data('photos', JSON.stringify(response.photos));
                        var parentRow = masterBtn.closest('tr');
                        parentRow.find('.photo_count_badge').text(response.photos.length);
                    }
                } else {
                    alert(response.message || 'Error deleting photo');
                }
            },
            error: function() {
                alert('Something went wrong deleting photo.');
            }
        });
    });

    // Lightbox Photo Preview Handler
    $(document).on('click', '.preview_photo_thumb', function() {
        var url = $(this).data('url');
        $('#lightbox_image').attr('src', url);
        $('#lightbox_download_btn').attr('href', url);
        $('#photo_lightbox_modal').modal('show');
    });

    // Item Order Toggle Handler (For Partial Request flow)
    $(document).on('click', '.item_order_toggle_btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var itemId = btn.data('id');
        var isOrdered = btn.data('ordered');

        btn.prop('disabled', true);

        $.ajax({
            url: "<?= base_url('admin/client/on_site_purchase_item_order_ajax') ?>",
            type: "POST",
            data: {
                item_id: itemId,
                is_ordered: isOrdered
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    var masterBtn = $('.view_items_btn[data-purchase_id="' + response.purchase_id + '"]');
                    if (masterBtn.length) {
                        var itemsData = response.items || masterBtn.data('items') || [];
                        masterBtn.data('items', itemsData);
                        if (response.delivery_status) {
                            masterBtn.data('delivery_status', response.delivery_status);
                            var delBadgeClass = 'label-warning';
                            if (response.delivery_status === 'Completed' || response.delivery_status === 'Accept Full' || response.delivery_status === 'Recieved' || response.delivery_status === 'Received') {
                                delBadgeClass = 'label-success';
                            } else if (response.delivery_status === 'Partial' || response.delivery_status === 'Approve Partial' || response.delivery_status === 'Partially Received' || response.delivery_status === 'Partially Approved') {
                                delBadgeClass = 'label-info';
                            } else if (response.delivery_status === 'Reject' || response.delivery_status === 'Rejected') {
                                delBadgeClass = 'label-danger';
                            }
                            var delCell = masterBtn.closest('tr').find('.parent-delivery-status-cell');
                            if (delCell.length) {
                                delCell.html('<span class="label ' + delBadgeClass + '">' + response.delivery_status + '</span>');
                            }
                        }
                        var reqStatus = masterBtn.data('req_status') || 'Partial';
                        renderModalItems(itemsData, reqStatus);
                    }
                } else {
                    alert(response.message || 'Error updating item order status');
                }
            },
            error: function() {
                alert('Something went wrong, please try again.');
            }
        });
    });

    // PDF Preview Modal Handler
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

    // Inline AJAX Receive / Reject Handler
    $(document).on('click', '.ajax_item_action_btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        var request_id = btn.data('id');
        var status = btn.data('status');

        if (status === 'Reject') {
            $('#reject_target_type').val('item');
            $('#reject_target_id').val(request_id);
            $('#reject_remark_text').val('');
            $('#rejection_remark_modal .modal-title').html('<i class="fa fa-times-circle"></i> Reject Item');
            $('#rejection_remark_modal').modal('show');
            return;
        }

        btn.prop('disabled', true);

        $.ajax({
            url: "<?= base_url('admin/client/on_site_purchase_request_status_ajax') ?>",
            type: "POST",
            data: {
                request_id: request_id,
                status: status,
                approval_remarks: ''
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    var masterBtn = $('.view_items_btn[data-purchase_id="' + response.purchase_id + '"]');
                    if (masterBtn.length) {
                        var itemsData = masterBtn.data('items');
                        if (itemsData) {
                            $.each(itemsData, function(i, itemObj) {
                                if (itemObj.id == request_id) {
                                    itemObj.status = response.status;
                                    itemObj.approver_name = response.approver_name;
                                    itemObj.status_approve_date = response.approve_date;
                                    itemObj.approval_remarks = response.remarks;
                                }
                            });
                            masterBtn.data('items', itemsData);
                            var reqStatus = masterBtn.data('req_status') || 'Partial';
                            renderModalItems(itemsData, reqStatus);
                        }
                    }
                } else {
                    alert(response.message || 'Error updating status');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Something went wrong, please try again.');
                btn.prop('disabled', false);
            }
        });
    });
</script>
