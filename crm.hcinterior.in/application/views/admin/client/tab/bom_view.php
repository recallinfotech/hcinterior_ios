<?php
$get_client_id  = $this->input->get('client_id', true);
$get_category   = $this->input->get('category', true);
$get_type       = $this->input->get('type', true);
$get_status     = $this->input->get('status', true);
$get_start_date = $this->input->get('start_date', true);
$get_end_date   = $this->input->get('end_date', true);

$bom_items          = isset($bom_items) ? $bom_items : [];
$cutting_list_items = isset($cutting_list_items) ? $cutting_list_items : [];

$user_type = $this->session->userdata('user_type');
$can_view_bom = ($user_type == 1 || check_permission('bom_list'));
$can_view_cutting_list = ($user_type == 1 || check_permission('cutting_list'));

if ($can_view_bom && $can_view_cutting_list) {
    $is_cutting_list_active = (!empty($get_type) && strpos($get_type, 'Cutting List') !== false);
    $is_bom_active = !$is_cutting_list_active;
} elseif ($can_view_bom) {
    $is_bom_active = true;
    $is_cutting_list_active = false;
} elseif ($can_view_cutting_list) {
    $is_bom_active = false;
    $is_cutting_list_active = true;
} else {
    $is_bom_active = false;
    $is_cutting_list_active = false;
}
?>

<div class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>BOM / Cutting List</strong>
        </div>
    </div>

    <div class="panel-body">

        <!-- Filter Accordion Section -->
        <?php 
        $has_active_filter = (!empty($get_client_id) || !empty($get_category) || !empty($get_type) || !empty($get_status) || !empty($get_start_date) || !empty($get_end_date));
        ?>
        <div class="panel-group" id="filterAccordion" style="margin-bottom: 20px;">
            <div class="panel panel-default" style="border: 1px solid #e3e6f0; border-radius: 4px; box-shadow: none;">
                <div class="panel-heading" style="background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0; padding: 10px 15px; cursor: pointer;" data-toggle="collapse" data-target="#collapseFilter">
                    <h4 class="panel-title" style="font-size: 14px; font-weight: 600; color: #333; margin: 0;">
                        <i class="fa fa-filter" style="color: #337ab7; margin-right: 5px;"></i> Filter / Search Options
                        <?php if ($has_active_filter): ?>
                            <span class="label label-warning" style="margin-left: 8px;">Filter Active</span>
                        <?php endif; ?>
                        <span class="pull-right text-muted"><i class="fa fa-chevron-down"></i></span>
                    </h4>
                </div>
                <div id="collapseFilter" class="panel-collapse collapse">
                    <div class="panel-body" style="background-color: #fcfcfc; padding: 15px;">
                        <form method="get" action="">
                            <div class="row">

                                <div class="col-md-2 col-sm-4" style="margin-bottom: 10px;">
                                    <label>Client ID</label>
                                    <input type="text" name="client_id" class="form-control" placeholder="Search Client ID" value="<?= html_escape($get_client_id) ?>">
                                </div>

                                <div class="col-md-2 col-sm-4" style="margin-bottom: 10px;">
                                    <label>Category</label>
                                    <select name="category" class="form-control">
                                        <option value="">All Categories</option>
                                        <option value="Category 1" <?= ($get_category == 'Category 1') ? 'selected' : '' ?>>Category 1</option>
                                        <option value="Category 2" <?= ($get_category == 'Category 2') ? 'selected' : '' ?>>Category 2</option>
                                    </select>
                                </div>

                                <div class="col-md-2 col-sm-4" style="margin-bottom: 10px;">
                                    <label>Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">All Types</option>
                                        <optgroup label="BOM Types">
                                            <option value="BOM 1" <?= ($get_type == 'BOM 1') ? 'selected' : '' ?>>BOM 1 (Kitchen)</option>
                                            <option value="BOM 2" <?= ($get_type == 'BOM 2') ? 'selected' : '' ?>>BOM 2 (Wardrobe)</option>
                                            <option value="BOM 3" <?= ($get_type == 'BOM 3') ? 'selected' : '' ?>>BOM 3 (On Site Handover)</option>
                                            <option value="BOM 4" <?= ($get_type == 'BOM 4') ? 'selected' : '' ?>>BOM 4 (Loose Furniture)</option>
                                        </optgroup>
                                        <optgroup label="Cutting List Types">
                                            <option value="Cutting List Kitchen" <?= ($get_type == 'Cutting List Kitchen') ? 'selected' : '' ?>>Cutting List Kitchen</option>
                                            <option value="Cutting List Wordrobe" <?= ($get_type == 'Cutting List Wordrobe') ? 'selected' : '' ?>>Cutting List Wordrobe</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-md-2 col-sm-4" style="margin-bottom: 10px;">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="Accepted" <?= ($get_status == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                                        <option value="Pending" <?= ($get_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="Reject" <?= ($get_status == 'Reject') ? 'selected' : '' ?>>Reject</option>
                                    </select>
                                </div>

                                <div class="col-md-2 col-sm-4" style="margin-bottom: 10px;">
                                    <label>From Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?= html_escape($get_start_date) ?>">
                                </div>

                                <div class="col-md-2 col-sm-4" style="margin-bottom: 10px;">
                                    <label>To Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?= html_escape($get_end_date) ?>">
                                </div>

                                <div class="col-md-12 text-right" style="margin-top: 5px;">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                                    <a href="<?= current_url(); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($can_view_bom || $can_view_cutting_list): ?>
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                <?php if ($can_view_bom): ?>
                    <li class="<?= $is_bom_active ? 'active' : '' ?>">
                        <a href="#tab_bom" data-toggle="tab" aria-expanded="<?= $is_bom_active ? 'true' : 'false' ?>">
                            <i class="fa fa-list-alt"></i> <strong>BOM</strong>
                            <span class="badge badge-primary" style="background-color: #337ab7; margin-left: 5px;"><?= count($bom_items) ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view_cutting_list): ?>
                    <li class="<?= $is_cutting_list_active ? 'active' : '' ?>">
                        <a href="#tab_cutting_list" data-toggle="tab" aria-expanded="<?= $is_cutting_list_active ? 'true' : 'false' ?>">
                            <i class="fa fa-scissors"></i> <strong>Cutting List</strong>
                            <span class="badge badge-info" style="background-color: #5bc0de; margin-left: 5px;"><?= count($cutting_list_items) ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <?php
        /**
         * Helper function to render table rows for BOM / Cutting List
         */
        if (!function_exists('render_bom_table_rows')) {
            function render_bom_table_rows($items, $empty_message = 'No Records Found') {
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>File Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Files</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?> 
                                <?php foreach ($items as $bom): ?>
                                    <!-- Parent Row -->
                                    <tr style="background-color: #f9f9f9;">
                                        <td>
                                            <a href="<?= base_url('admin/client/details/' . $bom->client_id) ?>" target="_blank">
                                                <?= html_escape($bom->client_name) ?> (<?= html_escape($bom->client_sr_id) ?>)
                                            </a>
                                        </td>
                                        <td><strong><?= html_escape($bom->file_name) ?></strong></td>
                                        <td><?= !empty($bom->category) ? html_escape($bom->category) : '-' ?></td>
                                        <td><span class="label label-default"><?= !empty($bom->type) ? html_escape($bom->type) : '-' ?></span></td>
                                        <td>
                                            <?php
                                            $files = json_decode($bom->upload_file);
                                            if (!empty($files)) {
                                                foreach ($files as $file) {
                                                    $filePath = base_url($file->path);
                                                    if (!empty($file->is_image)) {
                                                        echo '<img src="' . $filePath . '" width="50" height="40" class="preview-design" data-path="' . $filePath . '" data-type="image" style="cursor:pointer; object-fit:cover; margin-right:5px; border:1px solid #ccc;">';
                                                    } else {
                                                        echo '<a href="javascript:void(0)" class="preview-design label label-info" data-path="' . $filePath . '" data-type="pdf" style="margin-right:5px;">' . html_escape($file->fileName) . '</a>';
                                                    }
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="label label-<?= ($bom->status == 'Accepted') ? 'success' : (($bom->status == 'Reject') ? 'danger' : 'warning') ?>">
                                                <?= !empty($bom->status) ? html_escape($bom->status) : 'Pending' ?>
                                            </span>

                                            <?php if ($bom->status == 'Reject' && !empty($bom->remark)): ?>
                                                <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= html_escape($bom->remark) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($bom->created_date) ? date('d M Y h:i A', strtotime($bom->created_date)) : '-' ?></td>
                                    </tr>

                                    <!-- Child Rows (If exists) -->
                                    <?php if (!empty($bom->children)): ?>
                                        <?php foreach ($bom->children as $child): ?>
                                            <tr>
                                                <td><?= !empty($bom->client_name) ? html_escape($bom->client_name) : html_escape($bom->client_id) ?></td>
                                                <td style="padding-left:30px;">↳ <?= html_escape($child->file_name) ?></td>
                                                <td><?= !empty($child->category) ? html_escape($child->category) : '-' ?></td>
                                                <td><span class="label label-default"><?= !empty($child->type) ? html_escape($child->type) : '-' ?></span></td>
                                                <td>
                                                    <?php
                                                    $c_files = json_decode($child->upload_file);
                                                    if (!empty($c_files)) {
                                                        foreach ($c_files as $c_file) {
                                                            $c_filePath = base_url($c_file->path);
                                                            if (!empty($c_file->is_image)) {
                                                                echo '<img src="' . $c_filePath . '" width="50" height="40" class="preview-design" data-path="' . $c_filePath . '" data-type="image" style="cursor:pointer; object-fit:cover; margin-right:5px; border:1px solid #ccc;">';
                                                            } else {
                                                                echo '<a href="javascript:void(0)" class="preview-design label label-info" data-path="' . $c_filePath . '" data-type="pdf" style="margin-right:5px;">' . html_escape($c_file->fileName) . '</a>';
                                                            }
                                                        }
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="label label-<?= ($child->status == 'Accepted') ? 'success' : (($child->status == 'Reject') ? 'danger' : 'warning') ?>">
                                                        <?= !empty($child->status) ? html_escape($child->status) : 'Pending' ?>
                                                    </span>

                                                    <?php if ($child->status == 'Reject' && !empty($child->remark)): ?>
                                                        <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= html_escape($child->remark) ?></p>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= !empty($child->created_date) ? date('d M Y h:i A', strtotime($child->created_date)) : '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 20px; color: #777; font-weight: 600;"><?= html_escape($empty_message) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
        }
        ?>

        <!-- Tab Contents -->
        <div class="tab-content" style="padding: 15px; border: 1px solid #ddd; border-top: none; background-color: #fff;">
            <?php if ($can_view_bom): ?>
                <!-- BOM Tab Pane -->
                <div class="tab-pane <?= $is_bom_active ? 'active' : '' ?>" id="tab_bom">
                    <?php render_bom_table_rows($bom_items, 'No BOM Records Found'); ?>
                </div>
            <?php endif; ?>

            <?php if ($can_view_cutting_list): ?>
                <!-- Cutting List Tab Pane -->
                <div class="tab-pane <?= $is_cutting_list_active ? 'active' : '' ?>" id="tab_cutting_list">
                    <?php render_bom_table_rows($cutting_list_items, 'No Cutting List Records Found'); ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">BOM / Cutting List Preview</h4>
            </div>
            <div class="modal-body text-center" id="designPreviewContent"></div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.preview-design', function() {
        var filePath = $(this).data('path');
        var fileType = $(this).data('type');
        var html = '';

        if (fileType === 'image') {
            html = '<img src="' + filePath + '" class="img-responsive" style="margin:0 auto; max-height: 500px;">';
        } else if (filePath.toLowerCase().endsWith('.pdf')) {
            html = '<iframe src="' + filePath + '" width="100%" height="500px"></iframe>';
        } else {
            html = '<a href="' + filePath + '" target="_blank" class="btn btn-primary">Download File</a>';
        }

        $('#designPreviewContent').html(html);
        $('#designPreviewModal').modal('show');
    });
</script>