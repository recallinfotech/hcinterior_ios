<?php
$get_client_id  = $this->input->get('client_id', true);
$get_category   = $this->input->get('category', true);
$get_type       = $this->input->get('type', true);
$get_status     = $this->input->get('status', true);
$get_start_date = $this->input->get('start_date', true);
$get_end_date   = $this->input->get('end_date', true);
?>

<div class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>BOM / Checklist List</strong>
        </div>
    </div>

    <div class="panel-body">
        
        <!-- Filter Bar -->
        <form method="get" action="" class="form-horizontal mb-lg">
            <div class="row" style="margin-bottom: 15px;">
                
                <div class="col-md-2">
                    <label>Client ID</label>
                    <input type="text" name="client_id" class="form-control" placeholder="Search Client ID" value="<?= html_escape($get_client_id) ?>">
                </div>

                <div class="col-md-2">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="Category 1" <?= ($get_category == 'Category 1') ? 'selected' : '' ?>>Category 1</option>
                        <option value="Category 2" <?= ($get_category == 'Category 2') ? 'selected' : '' ?>>Category 2</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="BOM 1" <?= ($get_type == 'BOM 1') ? 'selected' : '' ?>>BOM 1 (Kitchen)</option>
                        <option value="BOM 2" <?= ($get_type == 'BOM 2') ? 'selected' : '' ?>>BOM 2 (Wardrobe)</option>
                        <option value="BOM 3" <?= ($get_type == 'BOM 3') ? 'selected' : '' ?>>BOM 3 (On Site Handover)</option>
                        <option value="BOM 4" <?= ($get_type == 'BOM 4') ? 'selected' : '' ?>>BOM 4 (Loose Furniture)</option>
                        <option value="Cutting List Kitchen" <?= ($get_type == 'Cutting List Kitchen') ? 'selected' : '' ?>>Cutting List Kitchen</option>
                        <option value="Cutting List Wordrobe" <?= ($get_type == 'Cutting List Wordrobe') ? 'selected' : '' ?>>Cutting List Wordrobe</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Accepted" <?= ($get_status == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                        <option value="Pending" <?= ($get_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Reject" <?= ($get_status == 'Reject') ? 'selected' : '' ?>>Reject</option>
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

                <div class="col-md-12 text-right" style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= current_url(); ?>" class="btn btn-default btn-sm">Reset</a>
                </div>

            </div>
        </form>

        <!-- BOM Table List -->
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
                    <?php if (!empty($parent_boms)): ?>
                        <?php foreach ($parent_boms as $bom): ?>
                            <!-- Parent Row -->
                            <tr style="background-color: #f9f9f9;">
                                <td><?= !empty($bom->client_name) ? $bom->client_name : $bom->client_id ?></td>
                                <td><strong><?= $bom->file_name ?></strong></td>
                                <td><?= !empty($bom->category) ? $bom->category : '-' ?></td>
                                <td><?= !empty($bom->type) ? $bom->type : '-' ?></td>
                                <td>
                                    <?php
                                    $files = json_decode($bom->upload_file);
                                    if (!empty($files)) {
                                        foreach ($files as $file) {
                                            $filePath = base_url($file->path);
                                            if (!empty($file->is_image)) {
                                                echo '<img src="'.$filePath.'" width="50" height="40" class="preview-design" data-path="'.$filePath.'" data-type="image" style="cursor:pointer; object-fit:cover; margin-right:5px; border:1px solid #ccc;">';
                                            } else {
                                                echo '<a href="javascript:void(0)" class="preview-design label label-info" data-path="'.$filePath.'" data-type="pdf" style="margin-right:5px;">'.$file->fileName.'</a>';
                                            }
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="label label-<?= ($bom->status == 'Accepted') ? 'success' : (($bom->status == 'Reject') ? 'danger' : 'warning') ?>">
                                        <?= !empty($bom->status) ? $bom->status : 'Pending' ?>
                                    </span>

                                    <?php if ($bom->status == 'Reject' && !empty($bom->remark)): ?>
                                        <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= $bom->remark ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($bom->created_date) ? date('d M Y h:i A', strtotime($bom->created_date)) : '-' ?></td>
                            </tr>

                            <!-- Child Rows (If exists) -->
                            <?php if (!empty($bom->children)): ?>
                                <?php foreach ($bom->children as $child): ?>
                                    <tr>
                                        <td><?= !empty($bom->client_name) ? $bom->client_name : $bom->client_id ?></td>
                                        <td style="padding-left:30px;">↳ <?= $child->file_name ?></td>
                                        <td><?= !empty($child->category) ? $child->category : '-' ?></td>
                                        <td><?= !empty($child->type) ? $child->type : '-' ?></td>
                                        <td>
                                            <?php
                                            $c_files = json_decode($child->upload_file);
                                            if (!empty($c_files)) {
                                                foreach ($c_files as $c_file) {
                                                    $c_filePath = base_url($c_file->path);
                                                    if (!empty($c_file->is_image)) {
                                                        echo '<img src="'.$c_filePath.'" width="50" height="40" class="preview-design" data-path="'.$c_filePath.'" data-type="image" style="cursor:pointer; object-fit:cover; margin-right:5px; border:1px solid #ccc;">';
                                                    } else {
                                                        echo '<a href="javascript:void(0)" class="preview-design label label-info" data-path="'.$c_filePath.'" data-type="pdf" style="margin-right:5px;">'.$c_file->fileName.'</a>';
                                                    }
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="label label-<?= ($child->status == 'Accepted') ? 'success' : (($child->status == 'Reject') ? 'danger' : 'warning') ?>">
                                                <?= !empty($child->status) ? $child->status : 'Pending' ?>
                                            </span>

                                            <?php if ($child->status == 'Reject' && !empty($child->remark)): ?>
                                                <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= $child->remark ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($child->created_date) ? date('d M Y h:i A', strtotime($child->created_date)) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No BOM / Checklist Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">BOM Preview</h4>
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