<?php
$get_client_id  = $this->input->get('client_id', true);
$get_status     = $this->input->get('status', true);
$get_start_date = $this->input->get('start_date', true);
$get_end_date   = $this->input->get('end_date', true);
?>

<div class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>Loose Furniture Purchase List</strong>
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
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Recieved" <?= ($get_status == 'Recieved') ? 'selected' : '' ?>>Recieved</option>
                        <option value="Pending" <?= ($get_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Reject" <?= ($get_status == 'Reject') ? 'selected' : '' ?>>Reject</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= html_escape($get_start_date) ?>">
                </div>

                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= html_escape($get_end_date) ?>">
                </div>

                <div class="col-md-2" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= current_url(); ?>" class="btn btn-default btn-sm">Reset</a>
                </div>

            </div>
        </form>

        <!-- Loose Furniture Table List -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>File Name</th>
                        <th>File</th>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($furniture_list)): ?>
                        <?php foreach ($furniture_list as $item): ?>
                            <tr>
                                <td><a href="<?= base_url('admin/client/details/'.$item->client_id) ?>" target="_blank"> <?= html_escape($item->client_name) ?> (<?= $item->client_sr_id ?>)
                             </a>
                             
                            </td>
                                <td><?= !empty($item->file_name) ? $item->file_name : '-' ?></td>
                                <td>
                                    <?php
                                    $files = json_decode($item->upload_file);
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
                                    <?php if (!empty($item->upload_url)): ?>
                                        <a href="<?= $item->upload_url ?>" target="_blank" class="btn btn-xs btn-info">View URL</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="label label-<?= ($item->status == 'Recieved') ? 'success' : (($item->status == 'Reject') ? 'danger' : 'warning') ?>">
                                        <?= !empty($item->status) ? $item->status : 'Pending' ?>
                                    </span>

                                    <?php if ($item->status == 'Reject' && !empty($item->remark)): ?>
                                        <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= $item->remark ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($item->created_date) ? date('d M Y h:i A', strtotime($item->created_date)) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No Loose Furniture Purchases Found</td>
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
                <h4 class="modal-title">Loose Furniture Document Preview</h4>
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