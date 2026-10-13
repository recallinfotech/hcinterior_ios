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
                <button id="new_design" class="btn btn-xs pull-right b0">Add Modular Drawing</button>
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
                                <?= (!empty($design_info) && $design_info->design_style == 'Post Validation Design') ? 'selected' : '' ?>>
                                Post Validation Design
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
                            value="<?= $design_info->upload_url ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-4 control-label">
                        Upload Files
                        <span class="text-danger">*</span>
                    </label>

                    <div class="col-lg-8">
                        <input type="file" name="design_files[]" class="form-control" multiple
                            accept=".jpg,.jpeg,.png,.pdf,.dwg,.ppt,.pptx">

                        <small class="text-muted">
                            Allowed: JPG, PNG, PDF, DWG
                        </small>
                    </div>
                </div>



                <div class="text-right">
                    <button class="btn btn-primary  mt-lg mb-lg " type="submit"><?= lang('save') ?></button>
                </div>

            </form>
        </div>
        <script>
        $(document).ready(function() {
            $('#new_design').click(function() {
                $('.new_design').toggle('slow');
            });
        });
        </script>




    </div>
</section>






<!-- 2D Design Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Moduler Design Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="designPreviewContent">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.preview-design', function() {

    var filePath = $(this).data('path');
    var fileType = $(this).data('type');
    var html = '';

    if (fileType === 'image') {
        html = '<img src="' + filePath + '" class="img-fluid" style="width:100%;">';
    } else if (filePath.toLowerCase().endsWith('.pdf')) {
        html = '<iframe src="' + filePath + '" width="100%" height="600px"></iframe>';
    } else {
        html = '<a href="' + filePath + '" target="_blank" class="btn btn-primary">Download File</a>';
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


    $url = $this->uri->segment(5);
    if ($url == '2ddesign') {        
        $id = $this->uri->segment(6);
       $design_info = $this->db
    ->where('id', $id)
    ->where('design_type', 'Modular Design')
    ->where('design_style', 'Pre Validation Design')
    ->get('tbl_client_two_d_design')
    ->row();

    } else {
        $id = null;
    }
    
    ?>
    <div class="panel-body">




        <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= lang('File Name' ) ?></th>
                    <th><?= lang('File') ?></th>
                    <th><?= lang('Design Type') ?></th>
                    <th class="col-sm-3"><?= lang('URL') ?></th>
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

                    <td> <?= $design->design_type ?></a> </td>
                    <td>
                        <p class="nowrap"> <?= $design->upload_url ?></a> </p>
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
                        <?= btn_edit('admin/client/details/' . $client_details->client_id . '/2ddesign/' . $design->id) ?>
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
        <a href="#demo1" class="btn btn-info" data-toggle="collapse">See Old Design</a>
        <hr>
        <div id="demo1" class="collapse">
            <table class="table table-striped " cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('File Name' ) ?></th>
                        <th><?= lang('File') ?></th>
                        <th><?= lang('Design Type') ?></th>
                        <th class="col-sm-3"><?= lang('URL') ?></th>
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

                            <td> <?= $design->design_type ?></a> </td>
                            <td>
                                <p class="nowrap"> <?= $design->upload_url ?></a> </p>
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
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/2ddesign/' . $design->id) ?>
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


    $url = $this->uri->segment(5);
    if ($url == '2ddesign') {        
        $id = $this->uri->segment(6);
       $design_info = $this->db
    ->where('id', $id)
    ->where('design_type', 'Modular Design')
     ->where('design_style', 'Post Validation Design')
    ->get('tbl_client_two_d_design')
    ->row();

    } else {
        $id = null;
    }
    
    ?>
    <div class="panel-body">

        <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= lang('File Name' ) ?></th>
                    <th><?= lang('File') ?></th>
                    <th><?= lang('Design Type') ?></th>
                    <th class="col-sm-3"><?= lang('URL') ?></th>
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

                    <td> <?= $design->design_type ?></a> </td>
                    <td>
                        <p class="nowrap"> <?= $design->upload_url ?></a> </p>
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
                        <?= btn_edit('admin/client/details/' . $client_details->client_id . '/2ddesign/' . $design->id) ?>
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
        <a href="#demo2" class="btn btn-info" data-toggle="collapse">See Old Design</a>
        <hr>
        <div id="demo2" class="collapse">
            <table class="table table-striped " cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th><?= lang('File Name' ) ?></th>
                        <th><?= lang('File') ?></th>
                        <th><?= lang('Design Type') ?></th>
                        <th class="col-sm-3"><?= lang('URL') ?></th>
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

                        <td> <?= $design->design_type ?></a> </td>
                        <td>
                            <p class="nowrap"> <?= $design->upload_url ?></a> </p>
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
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/2ddesign/' . $design->id) ?>
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
                            && !empty($validation_files->manual_correction_file);
                ?>

            <div class="row">
                <div class="col-md-6">
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


                <div class="col-md-6">

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

            </div>
            <?php if(!$hide_inputs || $user_type == 1  || check_permission('3d_design_edit')  ) { ?>
            <button type="submit" class="btn btn-primary">Upload</button>
            <?php } ?>
        </form>



    </div>

</section>

<!-- 2D Design Preview Modal -->

<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modular Design Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="designPreviewContent">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
</div>


<script>
$(document).on('click', '.preview-design', function() {

    var filePath = $(this).data('path');
    var fileType = $(this).data('type');
    var html = '';

    if (fileType === 'image') {
        html = '<img src="' + filePath + '" class="img-fluid" style="width:100%;">';
    } else if (filePath.toLowerCase().endsWith('.pdf')) {
        html = '<iframe src="' + filePath + '" width="100%" height="600px"></iframe>';
    } else {
        html = '<a href="' + filePath + '" target="_blank" class="btn btn-primary">Download File</a>';
    }

    $('#designPreviewContent').html(html);
    $('#designPreviewModal').modal('show');
});
</script>



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