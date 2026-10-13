<div class="panel panel-custom">

    <header class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('Final Validation') . ' ' . lang('Design') ?></strong>
        </div>
    </header>

    <div class="panel-body">

        <!-- Filter Form Section -->
        <div class="well well-sm" style="background-color: #fcfcfc; border: 1px solid #e5e5e5; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;">Is Final</label>
                            <select name="is_final" class="form-control">
                                <option value="">All</option>
                                <option value="1" <?= ($this->input->get('is_final') == '1') ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= ($this->input->get('is_final') == '0') ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="Approved" <?= ($this->input->get('status') == 'Approved') ? 'selected' : '' ?>>Approved</option>
                                <option value="Pending" <?= ($this->input->get('status') == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="Reject" <?= ($this->input->get('status') == 'Reject') ? 'selected' : '' ?>>Reject</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        <a href="<?= current_url() ?>" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#pre_validation_tab" data-toggle="tab" style="font-size: 14px; font-weight: 600; padding: 10px 18px;">
                        Pre Validation Design
                        <span class="label label-primary" style="margin-left: 5px; font-size: 11px;"><?= !empty($pre_validation_design) ? count($pre_validation_design) : 0 ?></span>
                    </a>
                </li>
                <li>
                    <a href="#post_validation_tab" data-toggle="tab" style="font-size: 14px; font-weight: 600; padding: 10px 18px;">
                        Post Validation Design
                        <span class="label label-info" style="margin-left: 5px; font-size: 11px;"><?= !empty($post_validation_design) ? count($post_validation_design) : 0 ?></span>
                    </a>
                </li>
            </ul>

            <div class="tab-content bg-white" style="padding: 15px; border: 1px solid #ddd; border-top: none;">

                <!-- TAB 1: Pre Validation Design -->
                <div class="tab-pane active" id="pre_validation_tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>File Name</th>
                                    <th>File</th>
                                    <th>Is Final</th>
                                    <th>Design</th>
                                    <th width="10%">URL</th>
                                    <th>Status</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(!empty($pre_validation_design)){ ?>
                                <?php foreach($pre_validation_design as $design){ ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('admin/client/details/'.$design->client_id) ?>" target="_blank" style="font-weight:600;color:#23b7e5;">
                                                <?= $design->client_name ?> (<?= $design->client_sr_id ?>)
                                            </a>
                                        </td>
                                        <td><?= $design->file_name ?></td>
                                        <td>
                                            <?php
                                            $files = json_decode($design->upload_file);
                                            if(!empty($files)){
                                                foreach($files as $file){
                                                    if($file->is_image){
                                                        echo '<img src="'.base_url($file->path).'" width="60" class="preview-design" style="margin-right:5px;">';
                                                    } else {
                                                        echo '<a target="_blank" href="'.base_url($file->path).'">'.$file->fileName.'</a><br>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="label label-<?= ($design->is_final == 1) ? 'success' : 'warning'; ?>">
                                                <?= ($design->is_final == 1) ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td><?= $design->design_style ?></td>
                                        <td><a class="btn btn-primary" target="_blank" href="<?= $design->upload_url ?>"> View </a></td>
                                        <td>
                                            <span class="label label-<?= $design->status == 'Approved' ? 'success' : ($design->status == 'Pending' ? 'warning' : 'danger'); ?>">
                                                <?= $design->status ?>
                                            </span>
                                        </td>
                                        <td><?= !empty($design->fullname) ? $design->fullname : '-' ?></td>
                                        <td><?= date('d M Y h:i A', strtotime($design->created_date)) ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="9" class="text-center">No Pre Validation Designs Found</td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Post Validation Design -->
                <div class="tab-pane" id="post_validation_tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>File Name</th>
                                    <th>File</th>
                                    <th>Is Final</th>
                                    <th>Design</th>
                                    <th width="10%">URL</th>
                                    <th>Status</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(!empty($post_validation_design)){ ?>
                                <?php foreach($post_validation_design as $design){ ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('admin/client/details/'.$design->client_id) ?>" target="_blank" style="font-weight:600;color:#23b7e5;">
                                                <?= $design->client_name ?> (<?= $design->client_sr_id ?>)
                                            </a>
                                        </td>
                                        <td><?= $design->file_name ?></td>
                                        <td>
                                            <?php
                                            $files = json_decode($design->upload_file);
                                            if(!empty($files)){
                                                foreach($files as $file){
                                                    if($file->is_image){
                                                        echo '<img src="'.base_url($file->path).'" width="60" class="preview-design" style="margin-right:5px;">';
                                                    } else {
                                                        echo '<a target="_blank" href="'.base_url($file->path).'">'.$file->fileName.'</a><br>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="label label-<?= ($design->is_final == 1) ? 'success' : 'warning'; ?>">
                                                <?= ($design->is_final == 1) ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td><?= $design->design_style ?></td>
                                        <td><a class="btn btn-primary" target="_blank" href="<?= $design->upload_url ?>"> View </a></td>
                                        <td>
                                            <span class="label label-<?= $design->status == 'Approved' ? 'success' : ($design->status == 'Pending' ? 'warning' : 'danger'); ?>">
                                                <?= $design->status ?>
                                            </span>
                                        </td>
                                        <td><?= !empty($design->fullname) ? $design->fullname : '-' ?></td>
                                        <td><?= date('d M Y h:i A', strtotime($design->created_date)) ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="9" class="text-center">No Post Validation Designs Found</td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
.timeline-thread{
    max-width:100%;
}
.comment-card{
    background:#fff;
    border-left:4px solid #23b7e5;
    padding:12px 15px;
    margin-bottom:12px;
    border-radius:6px;
    box-shadow:0 1px 3px rgba(0,0,0,.08);
}
.comment-header{
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
}
.comment-user{
    font-weight:600;
    color:#333;
}
.comment-date{
    color:#888;
    font-size:12px;
}
.comment-body{
    color:#555;
    line-height:22px;
}
</style>