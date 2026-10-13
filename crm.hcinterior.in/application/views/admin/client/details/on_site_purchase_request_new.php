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
if (!$this->db->field_exists('dispatch_status', 'tbl_client_on_site_purchase')) {
    $this->db->query("ALTER TABLE `tbl_client_on_site_purchase` ADD `dispatch_status` VARCHAR(50) DEFAULT 'Not Dispatched'");
}
if (!$this->db->table_exists('tbl_client_on_site_purchase_dispatch')) {
    $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_client_on_site_purchase_dispatch` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `purchase_id` int(11) NOT NULL,
        `client_id` int(11) NOT NULL,
        `no_of_boxes` int(11) NOT NULL DEFAULT '0',
        `dispatch_photos` text DEFAULT NULL,
        `dispatch_remarks` text DEFAULT NULL,
        `dispatched_by` int(11) DEFAULT NULL,
        `dispatched_date` datetime DEFAULT NULL,
        `site_dispatch_status` varchar(50) DEFAULT 'Pending',
        `site_dispatch_by` int(11) DEFAULT NULL,
        `site_dispatch_date` datetime DEFAULT NULL,
        `site_dispatch_remark` text DEFAULT NULL,
        `field_dispatch_status` varchar(50) DEFAULT 'Pending',
        `field_dispatch_by` int(11) DEFAULT NULL,
        `field_dispatch_date` datetime DEFAULT NULL,
        `field_dispatch_remark` text DEFAULT NULL,
        `field_dispatch_photos` text DEFAULT NULL,
        `overall_status` varchar(50) DEFAULT 'Dispatched',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_purchase_id` (`purchase_id`),
        KEY `idx_client_id` (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
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

        
<!-- Dispatch Management Modal -->
<div class="modal fade" id="purchase_dispatch_modal" tabindex="-1" role="dialog" style="z-index: 1055;">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 900px; width: 95%;">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; padding: 14px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.85;">&times;</button>
                <h4 class="modal-title" style="font-weight: 600; color: white; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-truck text-warning"></i> Dispatch Management &mdash; <span id="dispatch_modal_req_no" style="color: #38bdf8;"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px; background: #f8fafc; max-height: calc(85vh - 120px); overflow-y: auto;">
                
                <!-- Loading State -->
                <div id="dispatch_modal_loading" class="text-center" style="padding: 50px 0;">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="text-muted" style="margin-top: 10px;">Loading dispatch details...</p>
                </div>

                <!-- Main Content Container -->
                <div id="dispatch_modal_content" style="display: none;">

                    <!-- Overall Status Header Card -->
                    <div class="panel panel-default mb-md" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: white;">
                        <div class="panel-body" style="padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <span class="text-muted" style="font-size: 12px; font-weight: 500;">Overall Dispatch Status:</span>
                                <span id="dispatch_overall_badge" class="label label-default" style="font-size: 13px; margin-left: 6px; padding: 4px 10px;">Not Dispatched</span>
                            </div>
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <div>
                                    <span class="text-muted" style="font-size: 12px;">Total Boxes:</span>
                                    <strong id="dispatch_boxes_summary_val" class="text-dark" style="font-size: 14px; margin-left: 4px;">-</strong>
                                </div>
                                <div id="dispatch_photos_summary_box" style="display: none;">
                                    <span class="text-muted" style="font-size: 12px;">Photos:</span>
                                    <span id="dispatch_photos_summary_count" class="badge badge-info" style="margin-left: 4px; background: #3b82f6;">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Dispatch Request Section (No. of Box, Attachment Photo, Dispatch Process) -->
                    <div class="panel panel-default mb-md" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="panel-heading" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; color: #1e293b; font-size: 14px;">
                                <i class="fa fa-cubes text-primary"></i> 1. Dispatch Request (Boxes & Photos)
                            </span>
                            <span id="dispatch_stage1_status_badge" class="label label-default">Pending</span>
                        </div>
                        <div class="panel-body" style="padding: 18px; background: #ffffff;">
                            
                            <!-- Form to Submit Dispatch -->
                            <form id="dispatch_initiate_form" enctype="multipart/form-data">
                                <input type="hidden" name="purchase_id" id="dispatch_form_purchase_id" value="">
                                
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label font-bold" style="color: #334155;">
                                                No. of Box <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-archive"></i></span>
                                                <input type="number" name="no_of_boxes" id="dispatch_input_boxes" class="form-control" placeholder="e.g. 5" min="1" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label class="control-label font-bold" style="color: #334155;">
                                                Attachment Photo
                                            </label>
                                            <input type="file" name="dispatch_photos[]" id="dispatch_input_photos" class="form-control" multiple accept="image/*,.pdf">
                                            <small class="text-muted" style="display: block; margin-top: 2px;">Upload boxes / materials packed photos</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="control-label font-bold">&nbsp;</label>
                                            <button type="submit" class="btn btn-success btn-block" id="btn_process_dispatch" style="font-weight: 600;">
                                                <i class="fa fa-send"></i> Dispatch Process
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group mb0">
                                            <label class="control-label" style="font-size: 12px; color: #64748b;">Dispatch Remarks / Notes (Optional)</label>
                                            <input type="text" name="dispatch_remarks" id="dispatch_input_remarks" class="form-control input-sm" placeholder="e.g. Transport details, driver name, vehicle number">
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Read-only View of Submitted Dispatch Information -->
                            <div id="dispatch_submitted_view" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 6px;">
                                            <small class="text-muted" style="display: block; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">Confirmed Box Count</small>
                                            <span style="font-size: 17px; font-weight: 700; color: #0f172a;" id="display_box_count">0 Boxes</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 6px;">
                                            <small class="text-muted" style="display: block; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">Dispatched By & Date</small>
                                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;" id="display_dispatched_by_date">-</span>
                                            <div id="display_dispatch_remarks_box" style="margin-top: 4px; display: none;">
                                                <small class="text-muted" style="font-style: italic;" id="display_dispatch_remarks"></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attached Photos Preview Gallery -->
                                <div style="margin-top: 12px;">
                                    <label class="font-bold" style="font-size: 12px; color: #475569;">Uploaded Photos of Boxes:</label>
                                    <div id="display_dispatch_photos_container" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px;">
                                        <!-- Dynamic thumbnails -->
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 2. Site Dispatcher Task Section -->
                    <?php if ($user_type == 1 || check_permission('site_dispatch')) { ?>
                    <div class="panel panel-default mb-md" id="site_dispatcher_panel" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="panel-heading" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; color: #1e293b; font-size: 14px;">
                                <i class="fa fa-user-circle text-info"></i> 2. Site Dispatcher Task (Verification & Process)
                            </span>
                            <span id="site_dispatch_status_badge" class="label label-default">Pending</span>
                        </div>
                        <div class="panel-body" style="padding: 18px; background: #ffffff;">

                            <div id="site_dispatch_checklist_box" class="alert" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <strong style="font-size: 13px;"><i class="fa fa-check-square-o"></i> Verification Tasks:</strong>
                                        <div style="margin-top: 5px; font-size: 13px;">
                                            <span style="margin-right: 15px;">📦 Box Count Check: <strong id="site_check_box_count" style="color: #0f172a;">-</strong></span>
                                            <span>📷 Uploaded Photos Check: <strong id="site_check_photo_count" style="color: #0f172a;">-</strong></span>
                                        </div>
                                    </div>
                                    <div id="site_dispatcher_action_buttons" style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                        <!-- Rendered dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Remark Form (Shown when Site Dispatcher clicks Reject) -->
                            <div id="site_reject_remark_container" style="display: none; margin-bottom: 15px; padding: 14px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px;">
                                <label class="text-danger font-bold" style="font-size: 12px;">
                                    Reason for Rejection <span class="text-danger">*</span>
                                </label>
                                <textarea id="site_reject_remark_text" class="form-control" rows="2" placeholder="Enter rejection reason..."></textarea>
                                <div style="margin-top: 8px; text-align: right; display: flex; gap: 6px; justify-content: flex-end;">
                                    <button type="button" class="btn btn-default btn-xs" id="btn_cancel_site_reject">Cancel</button>
                                    <button type="button" class="btn btn-danger btn-xs" id="btn_confirm_site_reject"><i class="fa fa-times"></i> Confirm Reject</button>
                                </div>
                            </div>

                            <!-- History / Log for Site Dispatcher -->
                            <div id="site_dispatch_history" style="display: none; padding: 8px 12px; background: #f8fafc; border-radius: 6px; font-size: 12px; color: #334155;">
                                <p class="mb0" id="site_dispatch_history_text"></p>
                            </div>

                        </div>
                    </div>
                    <?php } ?>

                    <!-- 3. Field Dispatch Section (Received Field Dispatcher Request) -->
                    <?php if ($user_type == 1 || check_permission('field_dispatch')) { ?>
                    <div class="panel panel-default mb0" id="field_dispatcher_panel" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="panel-heading" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; color: #1e293b; font-size: 14px;">
                                <i class="fa fa-map-marker text-danger"></i> 3. Field Dispatch (Received Field Dispatcher Request)
                            </span>
                            <span id="field_dispatch_status_badge" class="label label-default">Pending</span>
                        </div>
                        <div class="panel-body" style="padding: 18px; background: #ffffff;">

                            <div id="field_dispatch_inactive_alert" class="alert alert-warning" style="margin-bottom: 0;">
                                <i class="fa fa-info-circle"></i> Field Dispatch actions will unlock once the Site Dispatcher approves and processes the request to Field Dispatcher.
                            </div>

                            <div id="field_dispatch_active_section" style="display: none;">
                                <form id="field_dispatch_form" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label font-bold" style="color: #334155;">
                                                    Photo Upload Attached (Field Receiving / Delivery)
                                                </label>
                                                <input type="file" name="field_photos[]" id="field_input_photos" class="form-control" multiple accept="image/*,.pdf">
                                                <small class="text-muted">Attach site delivery / received proof photos</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="control-label font-bold" style="color: #334155;">
                                                    Field Remark (Required if Rejecting)
                                                </label>
                                                <input type="text" name="field_remark" id="field_input_remark" class="form-control" placeholder="Optional notes or rejection remark">
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-end; flex-wrap: wrap; margin-top: 5px;">
                                        <span class="text-muted font-bold" style="font-size: 12px; margin-right: 5px;">Field Dispatch Action:</span>
                                        
                                        <button type="button" class="btn btn-sm btn-danger field_action_btn" data-action="reject" title="Reject Field Request">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success field_action_btn" data-action="received" title="Mark as Received & Complete">
                                            <i class="fa fa-check-circle"></i> Received
                                        </button>
                                    </div>
                                </form>

                                <!-- Field Uploaded Photos Preview -->
                                <div id="display_field_photos_section" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1; display: none;">
                                    <label class="font-bold" style="font-size: 12px; color: #475569;">Field Delivery Proof Photos:</label>
                                    <div id="display_field_photos_container" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px;">
                                        <!-- Field thumbnails -->
                                    </div>
                                </div>

                                <!-- Field Status Log -->
                                <div id="field_dispatch_history" style="margin-top: 12px; padding: 8px 12px; background: #f8fafc; border-radius: 6px; font-size: 12px; color: #334155; display: none;">
                                    <p class="mb0" id="field_dispatch_history_text"></p>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php } ?>

                </div>

            </div>
            <div class="modal-footer" style="background: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 10px 20px;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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

            $dispatch_records = $this->db->where('client_id', $client_details->client_id)
                ->get('tbl_client_on_site_purchase_dispatch')
                ->result();
            $dispatch_map = [];
            if (!empty($dispatch_records)) {
                foreach ($dispatch_records as $dr) {
                    $dispatch_map[$dr->purchase_id] = $dr;
                }
            }
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Request No.</th>
                            <th>Total Items</th>
                            <th>Requested By & Date</th>
                            <th style="width: 150px;">Request Status</th>
                            <th style="width: 150px;">Dispatch Status</th>
                            <th style="width: 130px;">Delivery Status</th>
                            <th style="width: 300px;"><?= lang('action') ?></th>
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
                                                                            <td class="parent-dispatch-status-cell">
                                        <?php
                                        $disp = isset($dispatch_map[$req->id]) ? $dispatch_map[$req->id] : null;
                                        $d_status = !empty($disp->overall_status) ? $disp->overall_status : (!empty($req->dispatch_status) ? $req->dispatch_status : "Not Dispatched");

                                        if ($d_status == "Dispatched") {
                                            $disp_badge = "label-warning";
                                            $disp_label = "<i class=\"fa fa-truck\"></i> Dispatched" . (!empty($disp->no_of_boxes) ? " (" . $disp->no_of_boxes . " Boxes)" : "");
                                        } else if ($d_status == "Processed to Field Dispatcher") {
                                            $disp_badge = "label-info";
                                            $disp_label = "<i class=\"fa fa-arrow-right\"></i> Field Dispatch";
                                        } else if ($d_status == "Site Dispatch Rejected") {
                                            $disp_badge = "label-danger";
                                            $disp_label = "<i class=\"fa fa-times\"></i> Site Rejected";
                                        } else if ($d_status == "Field Dispatch Accepted") {
                                            $disp_badge = "label-primary";
                                            $disp_label = "<i class=\"fa fa-check\"></i> Field Accepted";
                                        } else if ($d_status == "Field Dispatch Rejected") {
                                            $disp_badge = "label-danger";
                                            $disp_label = "<i class=\"fa fa-times\"></i> Field Rejected";
                                        } else if ($d_status == "Received") {
                                            $disp_badge = "label-success";
                                            $disp_label = "<i class=\"fa fa-check-circle\"></i> Received";
                                        } else {
                                            $disp_badge = "label-default";
                                            $disp_label = "Not Dispatched";
                                        }
                                        ?>
                                        <span class="label <?= $disp_badge ?>"><?= $disp_label ?></span>
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

                                            <!-- Dispatch Process Button -->
                                            <button type="button"
                                                class="btn btn-xs btn-warning open_dispatch_modal_btn<?= $disabled_btn_class ?>"
                                                <?= $disabled_btn_attr ?>
                                                data-purchase_id="<?= $req->id ?>"
                                                data-req_no="<?= $req_no_str ?>"
                                                title="<?= $is_req_accepted ? 'Dispatch Process & Management' : 'Accept request to enable dispatch' ?>">
                                                <i class="fa fa-truck"></i> Dispatch
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
<div class="modal fade" id="rejection_remark_modal" tabindex="-1" role="dialog" style="z-index: 1060;">
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
        var btn = $(this).closest('.req_accept_btn');
        var purchaseId = btn.data('purchase_id') || btn.attr('data-purchase_id') || currentActivePurchaseId;
        var actionType = btn.data('action') || btn.attr('data-action') || 'accept';

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
                    var targetRow = $('tr[data-purchase-row-id="' + purchaseId + '"]');
                    var parentCell = targetRow.find('.parent-status-cell');
                    var masterBtn = targetRow.find('.view_items_btn');

                    var parentBadgeClass = response.request_status === 'Completed' ? 'label-success' : (response.request_status === 'Partial' ? 'label-info' : 'label-success');
                    var parentStatusText = response.request_status === 'Partial' ? 'Partial Approved' : (response.request_status === 'Accepted' ? 'Accepted' : response.request_status);

                    if (parentCell.length) {
                        parentCell.html(`
                            <span class="label ${parentBadgeClass}">${parentStatusText}</span>
                            <small class="text-success" style="display:block; margin-top:2px; font-weight: 600;">
                                <i class="fa fa-check-circle"></i> Approved by ${response.approver_name}
                            </small>
                        `);
                    }

                    if (masterBtn.length) {
                        masterBtn.data('req_status', response.request_status);
                        masterBtn.data('items', response.items);
                        masterBtn.data('photos', JSON.stringify(response.photos));
                        masterBtn.data('approver_name', response.approver_name);
                        masterBtn.data('approve_date', response.approve_date);
                        masterBtn.prop('disabled', false).removeClass('disabled').removeAttr('disabled');
                    var dispatchBtn = targetRow.find('.open_dispatch_modal_btn');
                    if (dispatchBtn.length) {
                        dispatchBtn.prop('disabled', false).removeClass('disabled').removeAttr('disabled').attr('title', 'Dispatch Process & Management');
                    }
                    var photoBtn = targetRow.find('.photo_count_badge').closest('button');
                    if (photoBtn.length) {
                        photoBtn.prop('disabled', false).removeClass('disabled').removeAttr('disabled').attr('title', 'View / Upload Site Photos');
                    }
                    }

                    if ($('#purchase_items_modal').is(':visible') && currentActivePurchaseId == purchaseId) {
                        updateModalHeaderStatus(response.request_status, purchaseId, response.remark, response.approver_name, response.approve_date);
                        renderModalItems(response.items, response.request_status);
                        renderModalPhotos(response.photos);
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
        var btn = $(this).closest('.req_reject_btn');
        var purchaseId = btn.data('purchase_id') || btn.attr('data-purchase_id') || currentActivePurchaseId;
        $('#reject_target_type').val('request');
        $('#reject_target_id').val(purchaseId);
        $('#reject_remark_text').val('');
        $('#rejection_remark_modal .modal-title').html('<i class="fa fa-times-circle"></i> Reject Purchase Request');
        $('#rejection_remark_modal').modal('show');
    });

    // Nested modal backdrop/scroll fix
    $('#rejection_remark_modal').on('hidden.bs.modal', function () {
        if ($('#purchase_items_modal').is(':visible')) {
            $('body').addClass('modal-open');
        }
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
                        var targetRow = $('tr[data-purchase-row-id="' + targetId + '"]');
                        var parentCell = targetRow.find('.parent-status-cell');
                        var masterBtn = targetRow.find('.view_items_btn');

                        if (parentCell.length) {
                            parentCell.html(`
                                <span class="label label-danger">Rejected</span>
                                <small class="text-danger" style="display:block; font-style:italic;">Reason: ${response.remark}</small>
                            `);
                        }

                        if (masterBtn.length) {
                            masterBtn.data('req_status', response.request_status);
                            masterBtn.data('items', response.items);
                            masterBtn.prop('disabled', true).addClass('disabled').attr('disabled', 'disabled');
                            var dispatchBtn = targetRow.find('.open_dispatch_modal_btn');
                            if (dispatchBtn.length) {
                                dispatchBtn.prop('disabled', true).addClass('disabled').attr('disabled', 'disabled').attr('title', 'Accept request to enable dispatch');
                            }
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


    // ==========================================
    // DISPATCH MANAGEMENT WORKFLOW
    // ==========================================

    var currentActiveDispatchPurchaseId = 0;

    // Helper: update dispatch status badge in parent table
    function updateParentTableDispatchStatus(purchaseId, overallStatus, boxesCount) {
        var row = $('tr[data-purchase-row-id="' + purchaseId + '"]');
        var cell = row.find('.parent-dispatch-status-cell');
        if (!cell.length) return;

        var badgeClass = 'label-default';
        var badgeLabel = 'Not Dispatched';

        if (overallStatus === 'Dispatched') {
            badgeClass = 'label-warning';
            var bText = boxesCount ? ' (' + boxesCount + ' Boxes)' : '';
            badgeLabel = '<i class="fa fa-truck"></i> Dispatched' + bText;
        } else if (overallStatus === 'Processed to Field Dispatcher') {
            badgeClass = 'label-info';
            badgeLabel = '<i class="fa fa-arrow-right"></i> Field Dispatch';
        } else if (overallStatus === 'Site Dispatch Rejected') {
            badgeClass = 'label-danger';
            badgeLabel = '<i class="fa fa-times"></i> Site Rejected';
        } else if (overallStatus === 'Field Dispatch Accepted') {
            badgeClass = 'label-primary';
            badgeLabel = '<i class="fa fa-check"></i> Field Accepted';
        } else if (overallStatus === 'Field Dispatch Rejected') {
            badgeClass = 'label-danger';
            badgeLabel = '<i class="fa fa-times"></i> Field Rejected';
        } else if (overallStatus === 'Received') {
            badgeClass = 'label-success';
            badgeLabel = '<i class="fa fa-check-circle"></i> Received';
            // Also update delivery status cell to Completed
            var delCell = row.find('.parent-delivery-status-cell');
            if (delCell.length) {
                delCell.html('<span class="label label-success">Completed</span>');
            }
        }

        cell.html('<span class="label ' + badgeClass + '">' + badgeLabel + '</span>');
    }

    // Open Dispatch Modal Handler
    $(document).on('click', '.open_dispatch_modal_btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        if (btn.is(':disabled') || btn.hasClass('disabled') || btn.attr('disabled')) {
            return false;
        }

        var purchaseId = btn.data('purchase_id');
        var reqNo = btn.data('req_no') || ('OSPR-' + purchaseId);
        currentActiveDispatchPurchaseId = purchaseId;

        $('#dispatch_form_purchase_id').val(purchaseId);
        $('#dispatch_modal_req_no').text(reqNo);

        // Reset forms & states
        $('#dispatch_input_boxes').val('');
        $('#dispatch_input_photos').val('');
        $('#dispatch_input_remarks').val('');
        $('#site_reject_remark_container').hide();
        $('#site_reject_remark_text').val('');
        $('#field_input_photos').val('').prop('disabled', false);
        $('#field_input_remark').val('').prop('disabled', false);
        $('.field_action_btn').prop('disabled', false).removeClass('disabled').removeAttr('title');

        $('#dispatch_modal_loading').show();
        $('#dispatch_modal_content').hide();
        $('#purchase_dispatch_modal').modal('show');

        // Fetch Dispatch Details
        loadDispatchDetails(purchaseId);
    });

    function loadDispatchDetails(purchaseId) {
        $.ajax({
            url: "<?= base_url('admin/client/get_on_site_purchase_dispatch_ajax') ?>",
            type: "POST",
            data: { purchase_id: purchaseId },
            dataType: "json",
            success: function(res) {
                $('#dispatch_modal_loading').hide();
                if (!res.success) {
                    alert(res.message || 'Error loading dispatch details');
                    return;
                }

                $('#dispatch_modal_content').show();
                renderDispatchData(res);
            },
            error: function() {
                $('#dispatch_modal_loading').hide();
                alert('Something went wrong fetching dispatch details.');
            }
        });
    }

    function renderDispatchData(data) {
        var d = data.dispatch;
        var hasDispatch = data.has_dispatch && d;

        // Overall status banner
        if (hasDispatch) {
            var overallStatus = d.overall_status || 'Dispatched';
            var overallClass = 'label-default';
            if (overallStatus === 'Dispatched') overallClass = 'label-warning';
            else if (overallStatus === 'Processed to Field Dispatcher') overallClass = 'label-info';
            else if (overallStatus === 'Site Dispatch Rejected' || overallStatus === 'Field Dispatch Rejected') overallClass = 'label-danger';
            else if (overallStatus === 'Field Dispatch Accepted') overallClass = 'label-primary';
            else if (overallStatus === 'Received') overallClass = 'label-success';

            $('#dispatch_overall_badge').attr('class', 'label ' + overallClass).text(overallStatus);
            $('#dispatch_boxes_summary_val').text(d.no_of_boxes + ' Boxes');

            var photoCount = d.dispatch_photos ? d.dispatch_photos.length : 0;
            if (photoCount > 0) {
                $('#dispatch_photos_summary_box').show();
                $('#dispatch_photos_summary_count').text(photoCount);
            } else {
                $('#dispatch_photos_summary_box').hide();
            }

            // Section 1: Dispatch Request
            $('#dispatch_input_boxes').val(d.no_of_boxes);
            $('#dispatch_input_remarks').val(d.dispatch_remarks || '');
            $('#display_box_count').text(d.no_of_boxes + ' Boxes');
            $('#display_dispatched_by_date').text((d.dispatched_by_name || 'User') + (d.dispatched_date ? ' (' + d.dispatched_date + ')' : ''));
            if (d.dispatch_remarks) {
                $('#display_dispatch_remarks').text('Remarks: ' + d.dispatch_remarks);
                $('#display_dispatch_remarks_box').show();
            } else {
                $('#display_dispatch_remarks_box').hide();
            }

            // Render Photos
            var photosHtml = '';
            if (d.dispatch_photos && d.dispatch_photos.length > 0) {
                $.each(d.dispatch_photos, function(idx, p) {
                    var fullUrl = '<?= base_url() ?>' + p.path;
                    if (p.is_image) {
                        photosHtml += `
                            <div style="position: relative; border: 1px solid #cbd5e1; border-radius: 6px; padding: 3px; background: white;">
                                <img src="${fullUrl}" class="preview-lightbox" data-src="${fullUrl}" style="width: 75px; height: 75px; object-fit: cover; border-radius: 4px; cursor: pointer;">
                            </div>
                        `;
                    } else {
                        photosHtml += `
                            <a href="${fullUrl}" target="_blank" class="btn btn-default btn-xs" style="height: 75px; display: inline-flex; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                <i class="fa fa-file-pdf-o text-danger fa-2x"></i>
                                <span style="margin-left: 8px; font-size: 11px; max-width: 80px; overflow: hidden; text-overflow: ellipsis;">${p.fileName}</span>
                            </a>
                        `;
                    }
                });
            } else {
                photosHtml = '<span class="text-muted" style="font-size: 12px; font-style: italic;">No photos attached</span>';
            }
            $('#display_dispatch_photos_container').html(photosHtml);
            $('#dispatch_submitted_view').show();
            $('#dispatch_stage1_status_badge').attr('class', 'label label-success').html('<i class="fa fa-check"></i> Recorded');

            // Section 2: Site Dispatcher
            $('#site_check_box_count').text(d.no_of_boxes + ' Boxes');
            $('#site_check_photo_count').text((d.dispatch_photos ? d.dispatch_photos.length : 0) + ' Photos');

            var siteStatus = d.site_dispatch_status || 'Pending';
            var siteStatusClass = 'label-default';
            if (siteStatus === 'Accepted') siteStatusClass = 'label-success';
            else if (siteStatus === 'Rejected') siteStatusClass = 'label-danger';
            else siteStatusClass = 'label-warning';
            $('#site_dispatch_status_badge').attr('class', 'label ' + siteStatusClass).text(siteStatus === 'Accepted' ? 'Accepted & Processed' : siteStatus);

            // Site Action Buttons
            var siteBtns = '';
            if (data.can_site_dispatch) {
                if (siteStatus === 'Accepted') {
                    siteBtns = '<span class="label label-success"><i class="fa fa-check-circle"></i> Processed to Field Dispatcher</span>';
                } else {
                    siteBtns = `
                        <button type="button" class="btn btn-xs btn-success site_action_btn" data-action="accept" title="Accept & Process to Field Dispatcher">
                            <i class="fa fa-check"></i> Accept (Process to Field)
                        </button>
                        <button type="button" class="btn btn-xs btn-danger site_action_btn" data-action="reject" title="Reject Site Dispatch">
                            <i class="fa fa-times"></i> Reject
                        </button>
                    `;
                }
            } else {
                siteBtns = '<small class="text-muted">Site Dispatch permission required</small>';
            }
            $('#site_dispatcher_action_buttons').html(siteBtns);

            if (siteStatus === 'Accepted' && d.site_dispatch_by_name) {
                $('#site_dispatch_history_text').html('<strong><i class="fa fa-check text-success"></i> Accepted by:</strong> ' + d.site_dispatch_by_name + ' on ' + d.site_dispatch_date);
                $('#site_dispatch_history').show();
            } else if (siteStatus === 'Rejected') {
                var rText = d.site_dispatch_remark ? ('<br><span class="text-danger font-bold">Reason: ' + d.site_dispatch_remark + '</span>') : '';
                $('#site_dispatch_history_text').html('<strong><i class="fa fa-times text-danger"></i> Rejected by:</strong> ' + (d.site_dispatch_by_name || 'Site Dispatcher') + (d.site_dispatch_date ? ' on ' + d.site_dispatch_date : '') + rText);
                $('#site_dispatch_history').show();
            } else {
                $('#site_dispatch_history').hide();
            }

            // Section 3: Field Dispatch
            if (siteStatus === 'Accepted') {
                $('#field_dispatch_inactive_alert').hide();
                $('#field_dispatch_active_section').show();

                var fieldStatus = d.field_dispatch_status || 'Pending';
                var fieldStatusClass = 'label-default';
                if (fieldStatus === 'Received') fieldStatusClass = 'label-success';
                else if (fieldStatus === 'Accepted') fieldStatusClass = 'label-info';
                else if (fieldStatus === 'Rejected') fieldStatusClass = 'label-danger';
                else fieldStatusClass = 'label-warning';

                $('#field_dispatch_status_badge').attr('class', 'label ' + fieldStatusClass).text(fieldStatus);

                // Field Photos preview
                if (d.field_dispatch_photos && d.field_dispatch_photos.length > 0) {
                    var fPhotosHtml = '';
                    $.each(d.field_dispatch_photos, function(idx, p) {
                        var fullUrl = '<?= base_url() ?>' + p.path;
                        if (p.is_image) {
                            fPhotosHtml += `
                                <div style="position: relative; border: 1px solid #cbd5e1; border-radius: 6px; padding: 3px; background: white;">
                                    <img src="${fullUrl}" class="preview-lightbox" data-src="${fullUrl}" style="width: 75px; height: 75px; object-fit: cover; border-radius: 4px; cursor: pointer;">
                                </div>
                            `;
                        } else {
                            fPhotosHtml += `
                                <a href="${fullUrl}" target="_blank" class="btn btn-default btn-xs" style="height: 75px; display: inline-flex; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                    <i class="fa fa-file-pdf-o text-danger fa-2x"></i>
                                    <span style="margin-left: 8px; font-size: 11px; max-width: 80px; overflow: hidden; text-overflow: ellipsis;">${p.fileName}</span>
                                </a>
                            `;
                        }
                    });
                    $('#display_field_photos_container').html(fPhotosHtml);
                    $('#display_field_photos_section').show();
                } else {
                    $('#display_field_photos_section').hide();
                }

                // Disable action buttons & inputs if already Received
                if (fieldStatus === 'Received') {
                    $('.field_action_btn').prop('disabled', true).addClass('disabled').attr('title', 'Material has already been received');
                    $('#field_input_photos').prop('disabled', true);
                    $('#field_input_remark').prop('disabled', true);
                } else {
                    $('.field_action_btn').prop('disabled', false).removeClass('disabled').removeAttr('title');
                    $('#field_input_photos').prop('disabled', false);
                    $('#field_input_remark').prop('disabled', false);
                }

                // Field log
                if (fieldStatus !== 'Pending' && d.field_dispatch_by_name) {
                    var fLog = '<strong>Status: ' + fieldStatus + ' by ' + d.field_dispatch_by_name + '</strong> (' + d.field_dispatch_date + ')';
                    if (d.field_dispatch_remark) {
                        fLog += '<br><span class="text-muted">Remarks: ' + d.field_dispatch_remark + '</span>';
                    }
                    $('#field_dispatch_history_text').html(fLog);
                    $('#field_dispatch_history').show();
                } else {
                    $('#field_dispatch_history').hide();
                }
            } else {
                $('#field_dispatch_inactive_alert').show();
                $('#field_dispatch_active_section').hide();
                $('#field_dispatch_status_badge').attr('class', 'label label-default').text('Waiting Site Dispatch');
            }

        } else {
            // No dispatch initiated yet
            $('#dispatch_overall_badge').attr('class', 'label label-default').text('Not Dispatched');
            $('#dispatch_boxes_summary_val').text('-');
            $('#dispatch_photos_summary_box').hide();
            $('#dispatch_submitted_view').hide();
            $('#dispatch_stage1_status_badge').attr('class', 'label label-warning').text('Pending Initiation');

            $('#site_check_box_count').text('-');
            $('#site_check_photo_count').text('-');
            $('#site_dispatch_status_badge').attr('class', 'label label-default').text('Waiting Initiation');
            $('#site_dispatcher_action_buttons').html('<small class="text-muted">Waiting for Dispatch Initiation</small>');
            $('#site_dispatch_history').hide();

            $('#field_dispatch_inactive_alert').show();
            $('#field_dispatch_active_section').hide();
            $('#field_dispatch_status_badge').attr('class', 'label label-default').text('Waiting Initiation');
        }
    }

    // Submit Dispatch Request Form (Stage 1)
    $(document).on('submit', '#dispatch_initiate_form', function(e) {
        e.preventDefault();
        var form = this;
        var btn = $('#btn_process_dispatch');
        var formData = new FormData(form);

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: "<?= base_url('admin/client/save_on_site_purchase_dispatch_ajax') ?>",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-send"></i> Dispatch Process');
                if (res.success) {
                    var boxes = $('#dispatch_input_boxes').val();
                    updateParentTableDispatchStatus(res.purchase_id, res.overall_status, boxes);
                    loadDispatchDetails(res.purchase_id);
                } else {
                    alert(res.message || 'Error processing dispatch');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-send"></i> Dispatch Process');
                alert('Something went wrong processing dispatch.');
            }
        });
    });

    // Site Dispatcher Actions (Accept / Reject)
    $(document).on('click', '.site_action_btn', function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        if (action === 'reject') {
            $('#site_reject_remark_container').slideDown();
            $('#site_reject_remark_text').focus();
            return;
        }

        // Accept & Process to Field
        if (!confirm('Are you sure you want to Accept this dispatch and process to Field Dispatcher?')) {
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: "<?= base_url('admin/client/site_dispatch_action_ajax') ?>",
            type: "POST",
            data: {
                purchase_id: currentActiveDispatchPurchaseId,
                action: 'accept'
            },
            dataType: "json",
            success: function(res) {
                btn.prop('disabled', false);
                if (res.success) {
                    var boxes = $('#display_box_count').text().replace(/[^0-9]/g, '');
                    updateParentTableDispatchStatus(currentActiveDispatchPurchaseId, res.overall_status, boxes);
                    loadDispatchDetails(currentActiveDispatchPurchaseId);
                } else {
                    alert(res.message || 'Error accepting site dispatch');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Something went wrong.');
            }
        });
    });

    $(document).on('click', '#btn_cancel_site_reject', function() {
        $('#site_reject_remark_container').slideUp();
        $('#site_reject_remark_text').val('');
    });

    $(document).on('click', '#btn_confirm_site_reject', function(e) {
        e.preventDefault();
        var remark = $.trim($('#site_reject_remark_text').val());
        if (!remark) {
            alert('Please enter a remark for rejection.');
            $('#site_reject_remark_text').focus();
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: "<?= base_url('admin/client/site_dispatch_action_ajax') ?>",
            type: "POST",
            data: {
                purchase_id: currentActiveDispatchPurchaseId,
                action: 'reject',
                remark: remark
            },
            dataType: "json",
            success: function(res) {
                btn.prop('disabled', false);
                $('#site_reject_remark_container').slideUp();
                if (res.success) {
                    var boxes = $('#display_box_count').text().replace(/[^0-9]/g, '');
                    updateParentTableDispatchStatus(currentActiveDispatchPurchaseId, res.overall_status, boxes);
                    loadDispatchDetails(currentActiveDispatchPurchaseId);
                } else {
                    alert(res.message || 'Error rejecting site dispatch');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Something went wrong.');
            }
        });
    });

    // Field Dispatch Actions (Reject / Received)
    $(document).on('click', '.field_action_btn', function(e) {
        e.preventDefault();
        var btn = $(this);
        if (btn.is(':disabled') || btn.hasClass('disabled') || btn.attr('disabled')) {
            return false;
        }
        var action = btn.data('action');
        var remark = $.trim($('#field_input_remark').val());

        if (action === 'reject' && !remark) {
            alert('Please enter a remark before rejecting.');
            $('#field_input_remark').focus();
            return;
        }

        var confirmMsg = (action === 'received') ? 'Mark this purchase dispatch as Received & Completed?' : 'Reject this field dispatch request?';
        if (!confirm(confirmMsg)) return;

        var btn = $(this);
        btn.prop('disabled', true);

        var formElem = document.getElementById('field_dispatch_form');
        var formData = new FormData(formElem);
        formData.append('purchase_id', currentActiveDispatchPurchaseId);
        formData.append('action', action);
        formData.append('remark', remark);

        $.ajax({
            url: "<?= base_url('admin/client/field_dispatch_action_ajax') ?>",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res) {
                btn.prop('disabled', false);
                if (res.success) {
                    var boxes = $('#display_box_count').text().replace(/[^0-9]/g, '');
                    updateParentTableDispatchStatus(currentActiveDispatchPurchaseId, res.overall_status, boxes);
                    loadDispatchDetails(currentActiveDispatchPurchaseId);
                } else {
                    alert(res.message || 'Error processing field dispatch');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Something went wrong.');
            }
        });
    });

</script>
