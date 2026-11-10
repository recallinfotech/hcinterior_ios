<?php 
$this->db->select('start_date');
$this->db->from('tbl_client_timeline');
$this->db->where('task_name', 'validation');
$this->db->where('client_id', $client_details->client_id);
$query = $this->db->get()->row();
$validation_start_date = $query ? $query->start_date : '';
?>

<?php 
  $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row(); 
                           ?>

<?php
$validation_files = $this->db
    ->where('client_id', $client_details->client_id)
    ->order_by('id','desc')
    ->get('tbl_client_validation_files')
    ->row();
?>

<?php   $final_design = $this->db
                            ->where('client_id', $client_details->client_id)
                            ->where('design_style', 'Post Validation Design')
                            ->where('is_final', 1)
                            ->get('tbl_client_two_d_design')
                            ->num_rows();

                       // echo $final_design;
                            ?>

<?php
$url = $this->uri->segment(5);
$edit_id = ($url == 'validation') ? $this->uri->segment(6) : null;
$design_info = null;
if (!empty($edit_id)) {
    $design_info = $this->db
        ->where('id', $edit_id)
        ->where('client_id', $client_details->client_id)
        ->get('tbl_client_two_d_design')
        ->row();
}
$id = !empty($design_info) ? $design_info->id : null;

// 🔥 Check if Pre Validation file is uploaded and approved
$latest_pre_val_check = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('design_style', 'Pre Validation Design')
    ->order_by('id', 'desc')
    ->limit(1)
    ->get('tbl_client_two_d_design')
    ->row();

$pre_val_is_uploaded = !empty($latest_pre_val_check);
$pre_val_is_approved = ($pre_val_is_uploaded && $latest_pre_val_check->status == 'Approved');
$can_upload_post_val = $pre_val_is_approved;
$disable_post_opt = (!$can_upload_post_val && (empty($design_info) || $design_info->design_style != 'Post Validation Design'));
?>


<section class="panel panel-custom ">
    <div class="request">
        <div class="panel-heading">
            <div class="panel-title">
                <strong>
                    <?= lang('Validation ') ?>
                </strong>

                <?php if (!empty($validation_start_date)) { ?>
                <p style="float:right"><strong>Date:</strong> <?= date('d M Y', strtotime($validation_start_date)); ?>
                </p>
                <?php } ?>

                <?php if($final_design != 1 && $designation_id  != 77) { ?>
                <button id="new_design" class="btn btn-purple btn-xs pull-right" style="color: #ffffff !important;"><i class="fa fa-plus"></i> <?= !empty($design_info) ? 'Edit Modular Drawing' : 'Add Modular Drawing' ?></button>
                <?php } ?>


            </div>
        </div>

        <div class="new_design mb-lg" style="display: <?= !empty($design_info) ? 'block' : 'none' ?>">
            <form action="<?php echo base_url() ?>admin/client/new_two_d_design/<?= $id ?>" method="post"
                enctype="multipart/form-data" class="form-horizontal">

                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                <input type="hidden" name="url" value="validation">

                <div class="form-group">
                    <label class="col-lg-4 control-label">Design Style
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control" name="design_style" required>


                            <option value="None"
                                <?= (!empty($design_info) && $design_info->design_style == 'None') ? 'selected' : '' ?>>
                                None
                            </option>

                            <option value="Pre Validation Design"
                                <?= (!empty($design_info) && $design_info->design_style == 'Pre Validation Design') ? 'selected' : '' ?>>
                                Pre Validation Design
                            </option>

                            <option value="Post Validation Design"
                                <?= $disable_post_opt ? 'disabled' : '' ?>
                                <?= (!empty($design_info) && $design_info->design_style == 'Post Validation Design') ? 'selected' : '' ?>>
                                Post Validation Design<?= $disable_post_opt ? ' (Disabled: Pre Validation must be approved first)' : '' ?>
                            </option>

                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-lg-4 control-label">Design Type
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control" name="design_type" required>


                            <!-- <option value="None"
                                <?= (!empty($design_info) && $design_info->design_type == 'None') ? 'selected' : '' ?>>
                                None
                            </option> -->

                            <option value="Modular Design"
                                <?= (!empty($design_info) && $design_info->design_type == 'Modular Design') ? 'selected' : '' ?>>
                                Modular Design
                            </option>

                            <!-- <option value="Civil Design"
                                <?= (!empty($design_info) && $design_info->design_type == 'Civil Design') ? 'selected' : '' ?>>
                                Civil Design
                            </option> -->
                            <!-- <option value="Wall Designs Modular 2D Working Drawings" >Wall Designs Modular 2D Working Drawings</option>
                            <option value="Material Selection (Includes Handles & Paint)" >Material Selection (Includes Handles & Paint)</option>
                            <option value="Wall Electrical Drawings" >Wall Electrical Drawings</option> -->
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-4 control-label">File Name
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <?php
                            $default_file_name = !empty($design_info->file_name)
                                ? $design_info->file_name
                                : $client_details->name . '_Drawing_file';
                            ?>

                        <input type="text" name="file_name" class="form-control" required
                            value="<?= $default_file_name ?>">

                    </div>
                </div>

                <div class="form-group" id="upload_url_box">
                    <label class="col-lg-4 control-label">Upload URL</label>
                    <div class="col-lg-8">
                        <input type="text" name="upload_url" id="upload_url" class="form-control"
                            value="<?= !empty($design_info->upload_url) ? htmlspecialchars($design_info->upload_url) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-4 control-label">
                        Modular Design File
                        <?= empty($design_info) ? '<span class="text-danger">*</span>' : '' ?>
                    </label>

                    <div class="col-lg-8">
                        <input type="file" name="design_files[]" class="form-control" multiple
                            accept=".jpg,.jpeg,.png,.pdf,.dwg,.ppt,.pptx">

                        <small class="text-muted">
                            Allowed: JPG, PNG, PDF, DWG <?= !empty($design_info) ? '(Leave empty to keep current file)' : '' ?>
                        </small>

                        <?php if (!empty($design_info->upload_file)) {
                            $ex_files = json_decode($design_info->upload_file);
                            if (!empty($ex_files)) {
                                echo '<div class="file-preview mt-xs"><strong>Current File(s):</strong> ';
                                foreach ($ex_files as $ef) {
                                    echo '<a href="'.base_url($ef->path).'" target="_blank" class="label label-info mr-xs"><i class="fa fa-paperclip"></i> '.htmlspecialchars($ef->fileName).'</a> ';
                                }
                                echo '</div>';
                            }
                        } ?>
                    </div>
                </div>

                <?php 
                $has_lf = (!empty($design_info) && (($design_info->is_loose_furniture ?? '') == 'Yes' || !empty($design_info->loose_furniture_file)));
                ?>
                <div class="form-group">
                    <label class="col-lg-4 control-label">
                        Is Loose Furniture
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control" name="is_loose_furniture" id="is_loose_furniture" required>
                            <option value="No" <?= (!$has_lf) ? 'selected' : '' ?>>No</option>
                            <option value="Yes" <?= ($has_lf) ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="loose_furniture_box" style="display: <?= $has_lf ? 'block' : 'none' ?>;">
                    <label class="col-lg-4 control-label">
                        Loose Furniture File
                    </label>

                    <div class="col-lg-8">
                        <input type="file" name="loose_furniture_files[]" id="loose_furniture_files" class="form-control" multiple
                            accept=".jpg,.jpeg,.png,.pdf,.dwg,.ppt,.pptx">

                        <small class="text-muted">
                            Allowed: JPG, PNG, PDF, DWG <?= !empty($design_info) ? '(Leave empty to keep current file)' : '' ?>
                        </small>

                        <?php if (!empty($design_info->loose_furniture_file)) {
                            $ex_lfiles = json_decode($design_info->loose_furniture_file);
                            if (!empty($ex_lfiles)) {
                                echo '<div class="file-preview mt-xs"><strong>Current File(s):</strong> ';
                                foreach ($ex_lfiles as $elf) {
                                    echo '<a href="'.base_url($elf->path).'" target="_blank" class="label label-default mr-xs"><i class="fa fa-paperclip"></i> '.htmlspecialchars($elf->fileName).'</a> ';
                                }
                                echo '</div>';
                            }
                        } ?>
                    </div>
                </div>



                <div class="text-right">
                    <?php if (!empty($design_info)) { ?>
                        <a href="<?= base_url('admin/client/details/' . $client_details->client_id . '/validation') ?>" class="btn btn-default mt-lg mb-lg">Cancel</a>
                    <?php } ?>
                    <button class="btn btn-primary mt-lg mb-lg" type="submit"><?= !empty($design_info) ? 'Update' : lang('save') ?></button>
                </div>

            </form>
        </div>
        <script>
        $(document).ready(function() {
            $('#new_design').click(function() {
                $('.new_design').toggle('slow');
            });

            $(document).on('change', '#is_loose_furniture', function() {
                if ($(this).val() === 'Yes') {
                    $('#loose_furniture_box').slideDown();
                } else {
                    $('#loose_furniture_box').slideUp();
                    $('#loose_furniture_files').val('');
                }
            });

            $(document).on('change', 'select[name="design_style"]', function() {
                var selectedVal = $(this).val();
                var canUploadPost = <?= $can_upload_post_val ? 'true' : 'false' ?>;
                var isEditingPost = <?= (!empty($design_info) && $design_info->design_style == 'Post Validation Design') ? 'true' : 'false' ?>;

                if (selectedVal === 'Post Validation Design' && !canUploadPost && !isEditingPost) {
                    <?php if(!$pre_val_is_uploaded) { ?>
                        alert('Pre validation file has not been uploaded yet. Post validation drawing cannot be uploaded until Pre validation is uploaded and approved.');
                    <?php } else { ?>
                        alert('Pre validation file is not approved yet (Current Status: <?= !empty($latest_pre_val_check) ? $latest_pre_val_check->status : '' ?>). Post validation drawing cannot be uploaded until Pre validation is approved.');
                    <?php } ?>
                    $(this).val('Pre Validation Design');
                    return false;
                }
            });

            $('.new_design form').on('submit', function(e) {
                var selectedVal = $(this).find('select[name="design_style"]').val();
                var canUploadPost = <?= $can_upload_post_val ? 'true' : 'false' ?>;
                var isEditingPost = <?= (!empty($design_info) && $design_info->design_style == 'Post Validation Design') ? 'true' : 'false' ?>;

                if (selectedVal === 'Post Validation Design' && !canUploadPost && !isEditingPost) {
                    e.preventDefault();
                    <?php if(!$pre_val_is_uploaded) { ?>
                        alert('Pre validation file has not been uploaded yet. Post validation drawing cannot be uploaded until Pre validation is uploaded and approved.');
                    <?php } else { ?>
                        alert('Pre validation file is not approved yet. Post validation drawing cannot be uploaded until Pre validation is approved.');
                    <?php } ?>
                    return false;
                }
            });
        });
        </script>




    </div>
</section>






<!-- Design Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog" aria-labelledby="designPreviewModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 92%; max-width: 1400px; margin: 25px auto;">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.5);">
            <div class="modal-header" style="background: #232733; color: #fff; padding: 12px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8; font-size: 24px; margin-top: -2px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <a href="#" target="_blank" class="btn btn-xs btn-primary pull-right previewModalFullBtn" style="margin-right: 15px; font-weight: 600; padding: 4px 12px; display: none;">
                    <i class="fa fa-external-link"></i> View Full PDF
                </a>
                <h4 class="modal-title" id="designPreviewModalLabel" style="color: #fff; font-weight: 600; font-size: 16px; margin: 0;">
                    <i class="fa fa-file-text-o mr-sm"></i> Modular Design Preview
                </h4>
            </div>
            <div class="modal-body text-center" id="designPreviewContent" style="padding: 10px; background: #525659; min-height: 400px;">
                <!-- Dynamic content -->
            </div>
            <div class="modal-footer" style="padding: 10px 20px; background: #f8fafc; margin-top: 0; border-top: 1px solid #e2e8f0;">
                <a href="#" target="_blank" class="btn btn-sm btn-info pull-left previewModalFullBtn" style="display: none;">
                    <i class="fa fa-external-link"></i> View Full PDF (Open in New Tab)
                </a>
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.preview-design', function(e) {
    e.preventDefault();
    var filePath = $(this).data('path');
    var fileType = $(this).data('type') || '';
    var fileName = $(this).text().trim() || 'File Preview';
    var html = '';
    var cleanPath = (filePath || '').split('?')[0].toLowerCase();
    var isPdf = cleanPath.endsWith('.pdf') || fileType === 'pdf';
    var isImage = fileType === 'image' || cleanPath.endsWith('.jpg') || cleanPath.endsWith('.jpeg') || cleanPath.endsWith('.png') || cleanPath.endsWith('.gif') || cleanPath.endsWith('.webp');

    if (fileName && fileName !== 'Download File') {
        $('#designPreviewModalLabel').html('<i class="fa fa-file-text-o mr-sm"></i> ' + $('<div>').text(fileName).html());
    }

    if (isPdf) {
        var pdfViewerUrl = filePath + (filePath.indexOf('#') === -1 ? '#toolbar=1' : '');
        $('.previewModalFullBtn').attr('href', filePath).html('<i class="fa fa-external-link"></i> View Full PDF').show();
        html = '<iframe src="' + pdfViewerUrl + '" width="100%" style="width:100%; height:82vh; min-height:680px; border:none; display:block; background:#525659;"></iframe>';
    } else if (isImage) {
        $('.previewModalFullBtn').attr('href', filePath).html('<i class="fa fa-external-link"></i> View Full Image').show();
        html = '<div style="background:#fff; padding:15px; border-radius:4px;"><img src="' + filePath + '" class="img-responsive" style="max-height:80vh; margin:0 auto; box-shadow:0 2px 10px rgba(0,0,0,0.15);"></div>';
    } else {
        $('.previewModalFullBtn').attr('href', filePath).html('<i class="fa fa-download"></i> Download File').show();
        html = '<div style="background:#fff; padding:60px 20px; border-radius:4px;"><i class="fa fa-file-text-o fa-4x text-muted"></i><br><br><a href="' + filePath + '" target="_blank" class="btn btn-lg btn-primary"><i class="fa fa-download"></i> Download / View File</a></div>';
    }

    $('#designPreviewContent').html(html);
    $('#designPreviewModal').modal('show');
});
</script>


<style>
.file-preview {
    margin-top: 8px;
}

.file-preview img {
    border: 1px solid #ddd;
    padding: 2px;
    border-radius: 6px;
    cursor: pointer;
}

#designPreviewModal .modal-dialog {
    width: 92% !important;
    max-width: 1400px !important;
    margin: 25px auto !important;
}
#designPreviewModal .modal-body {
    padding: 10px !important;
}
#designPreviewModal iframe {
    width: 100% !important;
    height: 82vh !important;
    min-height: 680px !important;
    border: none !important;
}

</style>







    <?php if($user_type == 1  || check_permission('validation_only_view')) { ?>


<section class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('Pre Validation Drawing') ?>
            </strong>

        </div>
    </div>
    <?php
    
    
  
        $two_d_design = get_order_by_with_user(
            'tbl_client_two_d_design d',
            [
                'd.client_id' => $client_details->client_id,
                'd.design_type' => 'Modular Design',
        'd.design_style' => 'Pre Validation Design',
            ],
            'd.id',
            [
                [
                    'table' => 'tbl_account_details a',
                    'condition' => 'a.user_id = d.uploaded_by',
                    'type' => 'left'
                ]
            ]
        );

            
    $latest_proposal = null;
    $other_proposals = [];

    if (!empty($two_d_design)) {
        $latest_proposal = $two_d_design[0]; // first = latest
        $other_proposals = array_slice($two_d_design, 1);
    }
    ?>
    <div class="panel-body">




        <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= lang('File Name' ) ?></th>
                    <th>Modular Design File</th>
                    <th>Loose Furniture File</th>
                    <th><?= lang('Design Type') ?></th>
                    <th><?= lang('URL') ?></th>
                    <th><?= lang('Status') ?></th>
                    <th><?= lang('Uploaded By') ?></th>
                    <th class="col-sm-2"><?= lang('date') ?> </th>
                    <th class="col-sm-2"><?= lang('action') ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
            if (!empty($latest_proposal)) {
                 $design = $latest_proposal;
                    
                    ?>
                <tr>

                    <td>

                        <?php if($design->is_final == 1){ ?>
                        <span class="label label-success">Final Design</span>
                        <?php } ?> <?= $design->file_name ?></a>
                    </td>
                    <td>
                        <?php
                        $files = json_decode($design->upload_file);
                        if (!empty($files)) {
                            foreach ($files as $file) {
                                if ($file->is_image) {
                                echo '<img src="'.base_url($file->path).'" 
                                width="60" 
                                class="mr-sm preview-design" 
                                data-path="'.base_url($file->path).'"
                                data-type="'.($file->is_image ? 'image' : 'file').'"
                                style="cursor:pointer">';
                                } else {
                                    echo '<a href="javascript:void(0)" 
                                class="preview-design"
                                data-path="'.base_url($file->path).'"
                                data-type="pdf">'.$file->fileName.'</a>';
                                }
                            }
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $loose_files = !empty($design->loose_furniture_file) ? json_decode($design->loose_furniture_file) : [];
                        if (!empty($loose_files)) {
                            foreach ($loose_files as $file) {
                                if ($file->is_image) {
                                echo '<img src="'.base_url($file->path).'" 
                                width="60" 
                                class="mr-sm preview-design" 
                                data-path="'.base_url($file->path).'"
                                data-type="'.($file->is_image ? 'image' : 'file').'"
                                style="cursor:pointer">';
                                } else {
                                    echo '<a href="javascript:void(0)" 
                                class="preview-design"
                                data-path="'.base_url($file->path).'"
                                data-type="pdf">'.$file->fileName.'</a> ';
                                }
                            }
                        } else {
                            echo '<span class="text-muted">-</span>';
                        }
                        ?>
                    </td>

                    <td> <?= $design->design_type ?></a> </td>
                    <td>
                        <?php if (!empty($design->upload_url)) { 
                            $url = trim($design->upload_url);
                            $href = (preg_match('#^https?://#i', $url)) ? $url : 'http://' . $url;
                        ?>
                            <a href="<?= htmlspecialchars($href) ?>" target="_blank" class="btn btn-xs btn-info" title="<?= htmlspecialchars($url) ?>">
                                <i class="fa fa-external-link"></i> View URL
                            </a>
                        <?php } else { ?>
                            <span class="text-muted">-</span>
                        <?php } ?>
                    </td>
                    <td>
                        <span class="label label-<?= 
                $design->status == 'Approved' ? 'success' :
                ($design->status == 'Reject' ? 'danger' : 'warning')
            ?>">
                            <?= $design->status ?>
                        </span>

                        <?php if($design->status == 'Reject') { ?>
                        <p><?= $design->remark; ?></p>

                        <?php if(!empty($design->remark_file)) { ?>

                        <?php 
                $filePath = base_url($design->remark_file);
                $ext = pathinfo($design->remark_file, PATHINFO_EXTENSION);
                $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
            ?>

                        <?php if($is_image) { ?>
                        <img src="<?= $filePath ?>" width="60" class="preview-design" data-path="<?= $filePath ?>"
                            data-type="image" style="cursor:pointer">
                        <?php } else { ?>
                        <a href="javascript:void(0)" class="preview-design label label-info"
                            data-path="<?= $filePath ?>" data-type="pdf">
                            View Remark File
                        </a>
                        <?php } ?>

                        <?php } ?>
                        <?php } ?>
                    </td>

                    <td> <?= !empty($design->fullname) ? $design->fullname : '-' ?> </td>

                    <td>
                        <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                    </td>
                    <td>


                         <?php  if($user_type == 1 ||  check_permission('final_design_done')){ 
    
                 if($design->status == 'Approved' && $design->design_type == 'Modular Design' ){ ?>


                        <!-- <button href="javascript:void(0)" class="btn btn-xs btn-success final_design"
                            data-id="<?= $design->id ?>" data-client_id="<?= $design->client_id ?>"
                            <?= ($design->is_final == 1) ? 'disabled' : '' ?>>
                            <?= ($design->is_final == 1) ? 'Final Design' : 'Final' ?>
                        </button> -->
                        <?php }  } ?>

                        <?php   $final_design = $this->db
                            ->where('client_id', $client_details->client_id)
                            ->where('is_final', 1)
                            ->get('tbl_client_two_d_design')
                            ->num_rows();

                        //  echo $final_design;
                            ?>


                        <?php if (  $user_type == 1  || check_permission('validation_only_view') || $user_type != 54) {
                    if($design->status == 'Pending') { ?>
                        <?= btn_edit('admin/client/details/' . $client_details->client_id . '/validation/' . $design->id) ?>
                        <?php }} ?>
                        <?php 
                          
                        //   print_r( $designation_id) ;
                         //  echo 'he';
                          if($user_type == 1  || check_permission('modular_drawing_approval_reject')) {
                         $disabled = ($design->status == 'Approved'  || $design->status == 'Reject') ? 'disabled' : '';
                         $d_none = ($design->status == 'Approved') ? 'd-none' : '';

                                                
                                                echo '
                            <button 
                                type="button"
                                class="btn btn-xs btn-success approve_request_btn '.$d_none.'"
                                title="Approve Design "
                                data-toggle="modal"
                                data-target="#approveHandOverModal"
                                data-design_id="'.$design->id.'"
                                data-uploaded_by="'.$design->uploaded_by.'"
                                data-client_id="'.$design->client_id.'"
                                '.$disabled.'
                                >
                                <span class="fa fa-check"></span>
                            </button>';
                                }
                            ?>
                    </td>
                </tr>
                <?php
                }
            
            ?>
            </tbody>
        </table>


        <hr>
        <a href="#demo1" class="btn btn-info" data-toggle="collapse">See Old Drawing</a>
        <hr>
        <div id="demo1" class="collapse">
            <table class="table table-striped " cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('File Name' ) ?></th>
                        <th>Modular Design File</th>
                        <th>Loose Furniture File</th>
                        <th><?= lang('Design Type') ?></th>
                        <th><?= lang('URL') ?></th>
                        <th><?= lang('Status') ?></th>
                        <th><?= lang('Uploaded By') ?></th>
                        <th class="col-sm-2"><?= lang('date') ?> </th>
                        <th class="col-sm-2"><?= lang('action') ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                  if (!empty($other_proposals)) {
                    foreach ($other_proposals as $design) {
                        
                        ?>
                        <tr>

                            <td> <?= $design->file_name ?></a> </td>
                            <td>
                                <?php
                            $files = json_decode($design->upload_file);
                            if (!empty($files)) {
                                foreach ($files as $file) {
                                    if ($file->is_image) {
                                    echo '<img src="'.base_url($file->path).'" 
                                    width="60" 
                                    class="mr-sm preview-design" 
                                    data-path="'.base_url($file->path).'"
                                    data-type="'.($file->is_image ? 'image' : 'file').'"
                                    style="cursor:pointer">';
                                    } else {
                                        echo '<a href="javascript:void(0)" 
                                    class="preview-design"
                                    data-path="'.base_url($file->path).'"
                                    data-type="pdf">'.$file->fileName.'</a>';
                                    }
                                }
                            }
                            ?>
                            </td>
                            <td>
                                <?php
                            $loose_files = !empty($design->loose_furniture_file) ? json_decode($design->loose_furniture_file) : [];
                            if (!empty($loose_files)) {
                                foreach ($loose_files as $file) {
                                    if ($file->is_image) {
                                    echo '<img src="'.base_url($file->path).'" 
                                    width="60" 
                                    class="mr-sm preview-design" 
                                    data-path="'.base_url($file->path).'"
                                    data-type="'.($file->is_image ? 'image' : 'file').'"
                                    style="cursor:pointer">';
                                    } else {
                                        echo '<a href="javascript:void(0)" 
                                    class="preview-design"
                                    data-path="'.base_url($file->path).'"
                                    data-type="pdf">'.$file->fileName.'</a> ';
                                    }
                                }
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                            </td>

                            <td> <?= $design->design_type ?></a> </td>
                            <td>
                                <?php if (!empty($design->upload_url)) { 
                                    $url = trim($design->upload_url);
                                    $href = (preg_match('#^https?://#i', $url)) ? $url : 'http://' . $url;
                                ?>
                                    <a href="<?= htmlspecialchars($href) ?>" target="_blank" class="btn btn-xs btn-info" title="<?= htmlspecialchars($url) ?>">
                                        <i class="fa fa-external-link"></i> View URL
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                            <!-- <td> <?= $design->status ?></a> </td> -->

                            <td>
                                <span class="label label-<?= 
                    $design->status == 'Approved' ? 'success' :
                    ($design->status == 'Reject' ? 'danger' : 'warning')
                    ?>">
                                <?= $design->status ?>
                            </span>

                            <?php if($design->status == 'Reject') { ?>
                            <p><?= $design->remark; ?></p>

                            <?php if(!empty($design->remark_file)) { ?>

                            <?php 
                $filePath = base_url($design->remark_file);
                $ext = pathinfo($design->remark_file, PATHINFO_EXTENSION);
                $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
            ?>

                            <?php if($is_image) { ?>
                            <img src="<?= $filePath ?>" width="60" class="preview-design" data-path="<?= $filePath ?>"
                                data-type="image" style="cursor:pointer">
                            <?php } else { ?>
                            <a href="javascript:void(0)" class="preview-design label label-info"
                                data-path="<?= $filePath ?>" data-type="pdf">
                                View Remark File
                            </a>
                            <?php } ?>

                            <?php } ?>
                            <?php } ?>
                        </td>


                        <td>


                        <td> <?= !empty($design->fullname) ? $design->fullname : '-' ?> </td>
                        <td>
                            <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                        </td>
                        <td>


                            <?php if ( $user_type == 1  || check_permission('validation_only_view') || $user_type != 54) { 
                                 if($design->status == 'Pending') { ?>
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/validation/' . $design->id) ?>
                            <?php }} ?>
                            <?php 
                           $user_id = $this->session->userdata('user_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();   
                           if($assign_team->member_profile != 'Operation Team') {
                          $disabled = ($design->status == 'Approved' || $design->status == 'Reject') ? 'disabled' : '';

                                                
                                                echo '
                            <button 
                                type="button"
                                class="btn btn-xs btn-success approve_request_btn"
                                title="Approve Design "
                                data-toggle="modal"
                                data-target="#approveHandOverModal"
                                data-design_id="'.$design->id.'"
                                  data-uploaded_by="'.$design->uploaded_by.'"
                                data-client_id="'.$design->client_id.'"
                                '.$disabled.'
                                >
                                <span class="fa fa-check"></span>
                            </button>';
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
                </tbody>
            </table>
        </div>

      


    </div>


</section>

  <?php } ?>



<!-- pre validation design -->


    <?php if($user_type == 1  || check_permission('validation_only_view')) { ?>

<section class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('Post Validation Drawing') ?>
            </strong>

        </div>
    </div>
    <?php if(!$can_upload_post_val) { ?>
    <div class="alert alert-warning" style="margin: 10px 15px 0 15px;">
        <i class="fa fa-exclamation-triangle"></i> 
        <?php if(!$pre_val_is_uploaded) { ?>
            <strong>Notice:</strong> Pre validation file has not been uploaded yet. Post validation drawing can only be uploaded once the Pre validation drawing is uploaded and approved.
        <?php } else { ?>
            <strong>Notice:</strong> Pre validation file is not approved yet (Current Status: <span class="label label-warning"><?= htmlspecialchars($latest_pre_val_check->status) ?></span>). Post validation drawing can only be uploaded once Pre validation is approved.
        <?php } ?>
    </div>
    <?php } ?>
    <?php
    
    
  
        $two_d_design = get_order_by_with_user(
            'tbl_client_two_d_design d',
            [
                'd.client_id' => $client_details->client_id,
               'd.design_type' => 'Modular Design',
        'd.design_style' => 'Post Validation Design'
            ],
            'd.id',
            [
                [
                    'table' => 'tbl_account_details a',
                    'condition' => 'a.user_id = d.uploaded_by',
                    'type' => 'left'
                ]
            ]
        );

            
    $latest_proposal = null;
    $other_proposals = [];

    if (!empty($two_d_design)) {
        $latest_proposal = $two_d_design[0]; // first = latest
        $other_proposals = array_slice($two_d_design, 1);
    }
    ?>
    <div class="panel-body">

        <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= lang('File Name' ) ?></th>
                    <th>Modular Design File</th>
                    <th>Loose Furniture File</th>
                    <th><?= lang('Design Type') ?></th>
                    <th><?= lang('URL') ?></th>
                    <th><?= lang('Status') ?></th>
                    <th><?= lang('Uploaded By') ?></th>
                    <th class="col-sm-2"><?= lang('date') ?> </th>
                    <th class="col-sm-2"><?= lang('action') ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
            if (!empty($latest_proposal)) {
                 $design = $latest_proposal;
                    
                    ?>
                <tr>

                    <td>

                        <?php if($design->is_final == 1){ ?>
                        <span class="label label-success">Final Design</span>
                        <?php } ?> <?= $design->file_name ?></a>
                    </td>
                    <td>
                        <?php
                        $files = json_decode($design->upload_file);
                        if (!empty($files)) {
                            foreach ($files as $file) {
                                if ($file->is_image) {
                                echo '<img src="'.base_url($file->path).'" 
                                width="60" 
                                class="mr-sm preview-design" 
                                data-path="'.base_url($file->path).'"
                                data-type="'.($file->is_image ? 'image' : 'file').'"
                                style="cursor:pointer">';
                                } else {
                                    echo '<a href="javascript:void(0)" 
                                class="preview-design"
                                data-path="'.base_url($file->path).'"
                                data-type="pdf">'.$file->fileName.'</a>';
                                }
                            }
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $loose_files = !empty($design->loose_furniture_file) ? json_decode($design->loose_furniture_file) : [];
                        if (!empty($loose_files)) {
                            foreach ($loose_files as $file) {
                                if ($file->is_image) {
                                echo '<img src="'.base_url($file->path).'" 
                                width="60" 
                                class="mr-sm preview-design" 
                                data-path="'.base_url($file->path).'"
                                data-type="'.($file->is_image ? 'image' : 'file').'"
                                style="cursor:pointer">';
                                } else {
                                    echo '<a href="javascript:void(0)" 
                                class="preview-design"
                                data-path="'.base_url($file->path).'"
                                data-type="pdf">'.$file->fileName.'</a> ';
                                }
                            }
                        } else {
                            echo '<span class="text-muted">-</span>';
                        }
                        ?>
                    </td>

                    <td> <?= $design->design_type ?></a> </td>
                    <td>
                        <?php if (!empty($design->upload_url)) { 
                            $url = trim($design->upload_url);
                            $href = (preg_match('#^https?://#i', $url)) ? $url : 'http://' . $url;
                        ?>
                            <a href="<?= htmlspecialchars($href) ?>" target="_blank" class="btn btn-xs btn-info" title="<?= htmlspecialchars($url) ?>">
                                <i class="fa fa-external-link"></i> View URL
                            </a>
                        <?php } else { ?>
                            <span class="text-muted">-</span>
                        <?php } ?>
                    </td>

                    <td>
                        <span class="label label-<?= 
                        $design->status == 'Approved' ? 'success' :
                        ($design->status == 'Reject' ? 'danger' : 'warning')
                      ?>">
                            <?= $design->status ?>
                        </span>

                        <?php if($design->status == 'Reject') { ?>
                        <p><?= $design->remark; ?></p>

                        <?php if(!empty($design->remark_file)) { ?>

                        <?php 
                        $filePath = base_url($design->remark_file);
                        $ext = pathinfo($design->remark_file, PATHINFO_EXTENSION);
                        $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
                        ?>

                        <?php if($is_image) { ?>
                        <img src="<?= $filePath ?>" width="60" class="preview-design" data-path="<?= $filePath ?>"
                            data-type="image" style="cursor:pointer">
                        <?php } else { ?>
                        <a href="javascript:void(0)" class="preview-design label label-info"
                            data-path="<?= $filePath ?>" data-type="pdf">
                            View Remark File
                        </a>
                        <?php } ?>

                        <?php } ?>
                        <?php } ?>
                    </td>

                    <td> <?= !empty($design->fullname) ? $design->fullname : '-' ?> </td>

                    <td>
                        <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                    </td>
                    <td>


  <?php  if($user_type == 1 ||  check_permission('final_design_done')){     
                 if($design->status == 'Approved' && $design->design_type == 'Modular Design' ){ ?>


                        <button href="javascript:void(0)" class="btn btn-xs btn-success final_design"
                            data-id="<?= $design->id ?>" data-client_id="<?= $design->client_id ?>"
                            <?= ($design->is_final == 1) ? 'disabled' : '' ?>>
                            <?= ($design->is_final == 1) ? 'Final Design' : 'Final' ?>
                        </button>
                        <?php }  } ?>

                        <?php   $final_design = $this->db
                            ->where('client_id', $client_details->client_id)
                            ->where('is_final', 1)
                            ->get('tbl_client_two_d_design')
                            ->num_rows();

                        //  echo $final_design;
                            ?>

                         <?php if ( $user_type == 1  || check_permission('validation_only_view') || $user_type != 54) {
                    if($design->status == 'Pending') { ?>
                        <?= btn_edit('admin/client/details/' . $client_details->client_id . '/validation/' . $design->id) ?>
                        <?php }} ?>
                        <?php 
                          
                        //   print_r( $designation_id) ;
                         //  echo 'he';
                         if($user_type == 1  || check_permission('modular_drawing_approval_reject')) {
                         $disabled = ($design->status == 'Approved'  || $design->status == 'Reject') ? 'disabled' : '';
                         $d_none = ($design->status == 'Approved') ? 'd-none' : '';

                                                
                                                echo '
                            <button 
                                type="button"
                                class="btn btn-xs btn-success approve_request_btn '.$d_none.'"
                                title="Approve Design "
                                data-toggle="modal"
                                data-target="#approveHandOverModal"
                                data-design_id="'.$design->id.'"
                                data-uploaded_by="'.$design->uploaded_by.'"
                                data-client_id="'.$design->client_id.'"
                                '.$disabled.'
                                >
                                <span class="fa fa-check"></span>
                            </button>';
                                }
                            ?>
                    </td>
                </tr>
                <?php
                }
            
            ?>
            </tbody>
        </table>


        <hr>
        <a href="#demo2" class="btn btn-info" data-toggle="collapse">See Old Drawing</a>
        <hr>
        <div id="demo2" class="collapse">
            <table class="table table-striped " cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('File Name' ) ?></th>
                        <th>Modular Design File</th>
                        <th>Loose Furniture File</th>
                        <th><?= lang('Design Type') ?></th>
                        <th><?= lang('URL') ?></th>
                        <th><?= lang('Status') ?></th>
                        <th><?= lang('Uploaded By') ?></th>
                        <th class="col-sm-2"><?= lang('date') ?> </th>
                        <th class="col-sm-2"><?= lang('action') ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php
            if (!empty($other_proposals)) {
                foreach ($other_proposals as $design) {
                    
                    ?>
                    <tr>

                        <td> <?= $design->file_name ?></a> </td>
                        <td>
                            <?php
                        $files = json_decode($design->upload_file);
                        if (!empty($files)) {
                            foreach ($files as $file) {
                                if ($file->is_image) {
                                echo '<img src="'.base_url($file->path).'" 
                                width="60" 
                                class="mr-sm preview-design" 
                                data-path="'.base_url($file->path).'"
                                data-type="'.($file->is_image ? 'image' : 'file').'"
                                style="cursor:pointer">';
                                } else {
                                    echo '<a href="javascript:void(0)" 
                                class="preview-design"
                                data-path="'.base_url($file->path).'"
                                data-type="pdf">'.$file->fileName.'</a>';
                                }
                            }
                        }
                        ?>
                        </td>
                        <td>
                            <?php
                        $loose_files = !empty($design->loose_furniture_file) ? json_decode($design->loose_furniture_file) : [];
                        if (!empty($loose_files)) {
                            foreach ($loose_files as $file) {
                                if ($file->is_image) {
                                echo '<img src="'.base_url($file->path).'" 
                                width="60" 
                                class="mr-sm preview-design" 
                                data-path="'.base_url($file->path).'"
                                data-type="'.($file->is_image ? 'image' : 'file').'"
                                style="cursor:pointer">';
                                } else {
                                    echo '<a href="javascript:void(0)" 
                                class="preview-design"
                                data-path="'.base_url($file->path).'"
                                data-type="pdf">'.$file->fileName.'</a> ';
                                }
                            }
                        } else {
                            echo '<span class="text-muted">-</span>';
                        }
                        ?>
                        </td>

                        <td> <?= $design->design_type ?></a> </td>
                        <td>
                            <?php if (!empty($design->upload_url)) { 
                                $url = trim($design->upload_url);
                                $href = (preg_match('#^https?://#i', $url)) ? $url : 'http://' . $url;
                            ?>
                                <a href="<?= htmlspecialchars($href) ?>" target="_blank" class="btn btn-xs btn-info" title="<?= htmlspecialchars($url) ?>">
                                    <i class="fa fa-external-link"></i> View URL
                                </a>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                        <!-- <td> <?= $design->status ?></a> </td> -->

                        <td>
                            <span class="label label-<?= 
        $design->status == 'Approved' ? 'success' :
        ($design->status == 'Reject' ? 'danger' : 'warning')
        ?>">
                                <?= $design->status ?>
                            </span>

                            <?php if($design->status == 'Reject') { ?>
                            <p><?= $design->remark; ?></p>

                            <?php if(!empty($design->remark_file)) { ?>

                            <?php 
                $filePath = base_url($design->remark_file);
                $ext = pathinfo($design->remark_file, PATHINFO_EXTENSION);
                $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
            ?>

                            <?php if($is_image) { ?>
                            <img src="<?= $filePath ?>" width="60" class="preview-design" data-path="<?= $filePath ?>"
                                data-type="image" style="cursor:pointer">
                            <?php } else { ?>
                            <a href="javascript:void(0)" class="preview-design label label-info"
                                data-path="<?= $filePath ?>" data-type="pdf">
                                View Remark File
                            </a>
                            <?php } ?>

                            <?php } ?>
                            <?php } ?>
                        </td>


                        <td>


                        <td> <?= !empty($design->fullname) ? $design->fullname : '-' ?> </td>
                        <td>
                            <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                        </td>
                        <td>


                            <?php if (  $user_type == 1  || check_permission('validation_only_view') || $user_type != 54) { 
                             if($design->status == 'Pending') { ?>
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/validation/' . $design->id) ?>
                            <?php }} ?>
                            <?php 
                           $user_id = $this->session->userdata('user_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();   
                           if($assign_team->member_profile != 'Operation Team') {
                          $disabled = ($design->status == 'Approved' || $design->status == 'Reject') ? 'disabled' : '';

                                                
                                                echo '
                            <button 
                                type="button"
                                class="btn btn-xs btn-success approve_request_btn"
                                title="Approve Design "
                                data-toggle="modal"
                                data-target="#approveHandOverModal"
                                data-design_id="'.$design->id.'"
                                  data-uploaded_by="'.$design->uploaded_by.'"
                                data-client_id="'.$design->client_id.'"
                                '.$disabled.'
                                >
                                <span class="fa fa-check"></span>
                            </button>';
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
                </tbody>
            </table>
        </div>




    </div>


</section>

<?php } ?>

<!-- design upload -->

<section class="panel panel-custom">

    <div class="panel-body">


        <form action="<?= base_url('admin/client/save_validation_files') ?>" method="post"
            enctype="multipart/form-data">

            <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">

            <?php 
                $hide_inputs = !empty($validation_files->validation_checklist) 
                            && !empty($validation_files->manual_correction_file)
                            && !empty($validation_files->masking_photo);
                ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Validation Checklist File</label>
                        <?php if(!$hide_inputs || $user_type == 1 ) { ?>
                        <input type="file" name="validation_checklist" class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx">
                        <?php } ?>


                        <!-- Preview -->
                        <?php if(!empty($validation_files->validation_checklist)) { ?>

                        <?php 
                                $filePath = base_url($validation_files->validation_checklist);
                                $ext = pathinfo($validation_files->validation_checklist, PATHINFO_EXTENSION);
                                $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
                            ?>

    <?php if($user_type == 1  || check_permission('validation_only_view')) { ?>

                        <div class="file-preview mt-sm">
                            <?php if($is_image) { ?>
                            <img src="<?= $filePath ?>" width="70" class="preview-design" data-path="<?= $filePath ?>"
                                data-type="image">
                            <?php } else { ?>
                            <a href="javascript:void(0)" class="btn btn-xs btn-info preview-design"
                                data-path="<?= $filePath ?>" data-type="pdf">
                                View Checklist
                            </a>
                            <?php } ?>
                        </div>

                        <?php } } ?>

                    </div>
                </div>


                <div class="col-md-4">

                    <div class="form-group">
                        <label>Manual Correction File</label>
                        <?php if(!$hide_inputs || $user_type == 1  ) { ?>
                        <input type="file" name="manual_correction_file" class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx">
                        <?php } ?>


                        <!-- Preview -->
                        <?php if(!empty($validation_files->manual_correction_file)) { ?>

                        <?php 
                                    $filePath = base_url($validation_files->manual_correction_file);
                                    $ext = pathinfo($validation_files->manual_correction_file, PATHINFO_EXTENSION);
                                    $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
                                ?>
    <?php if($user_type == 1  || check_permission('validation_only_view')) { ?>

                        <div class="file-preview mt-sm">
                            <?php if($is_image) { ?>
                            <img src="<?= $filePath ?>" width="70" class="preview-design" data-path="<?= $filePath ?>"
                                data-type="image">
                            <?php } else { ?>
                            <a href="javascript:void(0)" class="btn btn-xs btn-warning preview-design"
                                data-path="<?= $filePath ?>" data-type="pdf">
                                View Manual File
                            </a>
                            <?php }} ?>
                        </div>

                        <?php } ?>

                    </div>
                </div>

                <div class="col-md-4">

                    <div class="form-group">
                        <label>Masking Photo</label>
                        <?php if(!$hide_inputs || $user_type == 1  ) { ?>
                        <input type="file" name="masking_photo" class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx">
                        <?php } ?>


                        <!-- Preview -->
                        <?php if(!empty($validation_files->masking_photo)) { ?>

                        <?php 
                                    $filePath = base_url($validation_files->masking_photo);
                                    $ext = pathinfo($validation_files->masking_photo, PATHINFO_EXTENSION);
                                    $is_image = in_array(strtolower($ext), ['jpg','jpeg','png']);
                                ?>
    <?php if($user_type == 1  || check_permission('validation_only_view')) { ?>

                        <div class="file-preview mt-sm">
                            <?php if($is_image) { ?>
                            <img src="<?= $filePath ?>" width="70" class="preview-design" data-path="<?= $filePath ?>"
                                data-type="image">
                            <?php } else { ?>
                            <a href="javascript:void(0)" class="btn btn-xs btn-success preview-design"
                                data-path="<?= $filePath ?>" data-type="pdf">
                                View Masking Photo
                            </a>
                            <?php }} ?>
                        </div>

                        <?php } ?>

                    </div>
                </div>

            </div>
            <?php if(!$hide_inputs || $user_type == 1  || check_permission('3d_design_edit')  ) { ?>
            <button type="submit" class="btn btn-primary">Upload</button>
            <?php } ?>
        </form>



    </div>

</section>





<div class="modal fade" id="approveHandOverModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Approve Design Request</h4>
            </div>

            <form action="<?= base_url('admin/client/twoddesign_status') ?>" method="post"
                enctype="multipart/form-data">

                <div class="modal-body text-center">

                    <input type="hidden" name="url" value="validation">
                    <input type="hidden" name="design_id" id="approve_design_id">
                    <input type="hidden" name="client_id" id="status_client_id">
                    <input type="hidden" name="uploaded_by" id="status_uploaded_by">
                    <select class="form-control" name="status" id="request_status" requied>
                        <option value="Approved">Approved</option>
                        <option value="Reject">Reject</option>
                    </select>

                    <div id="remarks_box" style="display:none; margin-bottom:10px;">
                        <div class="form-group">
                            <textarea name="remark" id="remarks_text" class="form-control"
                                placeholder="Enter rejection remarks"></textarea>

                        </div>

                        <div class="form-group" style="margin-bottom:10px;">
                            <label class="col-lg-4 control-label">
                                Upload Files
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-lg-8">
                                <input type="file" name="remark_file" class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf,.dwg,.ppt,.pptx">

                                <small class="text-muted">
                                    Allowed: JPG, PNG, PDF, DWG
                                </small>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        Submit
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>




<div class="remark_section">
    <?php 
$module = $this->uri->segment(5) ?: 'details';

$data['remarks'] = get_module_remarks($client_details->client_id, $module);

$this->load->view('admin/client/details/remarks_component', [
    'remarks'   => $data['remarks'],
    'client_id' => $client_details->client_id,
    'module'    => $module,
]);
?>
</div>
<style>
.remarks-box {
    background: #ffffff;
    padding: 10px;
}

hr {
    display: none;
}

.remarks-box h3 {
    display: none;
}
</style>

<script>
$(document).on('click', '.approve_request_btn', function() {
    var design_id = $(this).data('design_id');
    var client_id = $(this).data('client_id');
    var uploaded_by = $(this).data('uploaded_by');
    $('#approve_design_id').val(design_id);
    $('#status_client_id').val(client_id);
    $('#status_uploaded_by').val(uploaded_by);
});



$(document).on("click", ".final_design", function() {

    var $btn = $(this);
    var design_id = $btn.data("id");
    var client_id = $btn.data("client_id");

    if (confirm("Make this Final Design?")) {

        $btn.prop('disabled', true).text('Please wait...');

        $.ajax({
            url: "<?= base_url('admin/client/set_final_design') ?>",
            type: "POST",
            data: {
                design_id: design_id,
                client_id: client_id
            },
            success: function(res) {
                location.reload();
            },
            error: function() {
                $btn.prop('disabled', false).text('Make Final');
            }
        });

    }

});


$(document).on('change', '#request_status', function() {

    var status = $(this).val();

    if (status === 'Reject') {
        $('#remarks_box').show();
        $('#remarks_text').prop('required', true);
    } else {
        $('#remarks_box').hide();
        $('#remarks_text').prop('required', false);
        $('#remarks_text').val('');
    }

});

$(document).on('change', 'select[name="design_type"]', function() {
    var type = $(this).val();

    if (type === 'Civil Design') {
        $('#upload_url_box').hide();
        $('#upload_url').val('').prop('required', false);
    } else {
        $('#upload_url_box').show();
        $('#upload_url').prop('required', true);
    }
});

// 🔁 Page load pe bhi check
$(document).ready(function() {
    $('select[name="design_type"]').trigger('change');
});
</script>

<style>
.nowrap {
    word-break: break-all;
}

.d-none {
    display: none;
}

.table tr th {
    white-space: nowrap;
}
</style>