
<?php
       $url = $this->uri->segment(5);
  


   $kt_request = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('request_type', '3d Design')
    ->order_by('id', 'DESC')
    ->get('tbl_client_service_request')
    ->row();   // sirf ek record


    
 

 
    
      $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();  

    ?>
 

<section class="panel panel-custom">
    <div class="request">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('Request 3D Design') ?>
            </strong>


        </div>
    </div>

    <div class="panel-body">
 
        <div class="request_box">
            <?php if (empty($kt_request)) { ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="<?= base_url('admin/client/new_client_request') ?>" method="post"
                        class="form-horizontal">

                        <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                        <input type="hidden" name="request_type" value="3d Design">

                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                Request Date <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-6">
                                <input type="date" name="request_date" class="form-control" required>
                            </div>
                            <div class="col-sm-3 text-right">
                                <button class="btn btn-primary">
                                    <i class="fa fa-paper-plane"></i> Send Request
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <?php } ?>


            <?php if (!empty($kt_request)) { ?>


            <div class="row">
                <div class="col-sm-4">
                    <strong>Request Type</strong> :
                    <span class="text-muted"><?php echo $kt_request->request_type;?></span>
                </div>

                <div class="col-sm-4">
                    <strong>Request Date</strong> :
                    <?= date('d-m-Y', strtotime($kt_request->request_date)) ?>
                </div>

                <div class="col-sm-2">
                    <strong>Status</strong> :
                    <span class="label label-<?= 
                    $kt_request->status == 'Approved' ? 'success' :
                    ($kt_request->status == 'Reject' ? 'danger' : 'warning')
                ?>">
                        <?= $kt_request->status ?>
                    </span>

                    <?php if($kt_request->status == 'Reject') { ?>
                    <p> <?php echo $kt_request->remark;?>
                    </p>
                    <?php } ?>


                </div>
                <div class="col-sm-2">
                    <?php
        $disabled = ($kt_request->status == 'Approved') ? 'disabled' : '';
        if ($user_type == 1 || $designation_id == 76 || $designation_id == 45) {
        ?>
                    <div class="text-right">
                        <button class="btn btn-success approve_request_btn" data-toggle="modal"
                            data-target="#approveHandOverModal" data-design_id="<?= $kt_request->id ?>"
                            data-client_id="<?= $kt_request->client_id ?>" <?= $disabled ?>>
                            <i class="fa fa-check"></i>
                            Approve Request
                        </button>
                    </div>
                    <?php } ?>
                </div>
            </div>




            <?php } ?>
        </div>

    

    </div>
    </div>

    

<div class="modal fade" id="approveHandOverModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Approve Design Request</h4>
            </div>

            <form action="<?= base_url('admin/client/client_request_status') ?>" method="post">

                <div class="modal-body text-center">

                    <input type="hidden" name="design_id" id="approve_design_id">
                    <input type="hidden" name="client_id" id="status_client_id">
                    
                    <div class="form-group">
                        <select class="form-control" name="status" id="request_status" required>
                            <option value="Approved">Approved</option>
                            <option value="Reject">Reject</option>
                        </select>
                    </div>

                    <!-- Remarks Field -->
                    <div class="form-group" id="remarks_box" style="display:none;">
                        <textarea name="remark" id="remarks_text" class="form-control"
                            placeholder="Enter rejection remarks"></textarea>
                    </div>

                    <p>
                        Are you sure you want to <strong>Update</strong><br>
                        this design request?
                    </p>

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


<script>
$(document).on('click', '.approve_request_btn', function() {
    var design_id = $(this).data('design_id');
    var client_id = $(this).data('client_id');
    $('#approve_design_id').val(design_id);
    $('#status_client_id').val(client_id);
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
</script>
</section>







    <?php if($kt_request->status == 'Approved'){  ?>
 
 <section class="panel panel-custom">
    <div class="3d_design">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('Upload 3D Design') ?>
            </strong>
            <button id="new_design" class="btn btn-xs pull-right b0">Add 3D Design</button>
        </div>
    </div>



    
    <?php
    
    
    $three_d_design = get_result('tbl_client_three_d_design', array('client_id' => $client_details->client_id));
    
  
    if ($url == '3ddesign') {
         
        $id = $this->uri->segment(6);
          
        $design_info = $this->db->where('id', $id)->get('tbl_client_three_d_design')->row();
    } else {
        $id = null;
    }
    
    ?>
    <div class="panel-body">
        <div class="new_design mb-lg" style="display: <?= !empty($design_info) ? 'block' : 'none' ?>">
            <form action="<?php echo base_url() ?>admin/client/new_three_d_design/<?= $id ?>" method="post"
                class="form-horizontal">

                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">

                <div class="form-group">
                    <label class="col-lg-4 control-label">File Name
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <input type="text" name="file_name" class="form-control" value="<?= $design_info->file_name ?>">

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-lg-4 control-label">Upload URL
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <input type="text" name="upload_url" class="form-control" value="<?= $design_info->upload_url ?>">

                    </div>
                </div>


                <div class="form-group" style="margin-bottom: 0px">
                    <label for="field-1" class="col-sm-3 control-label"><?= lang('attachment') ?></label>

                    <div class="col-sm-5">
                        <div id="comments_file-dropzone" class="dropzone mb15">

                        </div>
                        <div id="comments_file-dropzone-scrollbar">
                            <div id="comments_file-previews">
                                <div id="file-upload-row" class="mt pull-left">

                                    <div class="preview box-content pr-lg" style="width:100px;">
                                        <span data-dz-remove class="pull-right" style="cursor: pointer">
                                            <i class="fa fa-times"></i>
                                        </span>
                                        <img data-dz-thumbnail class="upload-thumbnail-sm" />
                                        <input class="file-count-field" type="hidden" name="files[]" value="" />
                                        <div class="mb progress progress-striped upload-progress-sm active mt-sm"
                                            role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                            <div class="progress-bar progress-bar-success" style="width:0%;"
                                                data-dz-uploadprogress></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
            if (!empty($design_info->upload_file)) {
                $uploaded_file = json_decode($design_info->upload_file);
            }
           if (!empty($uploaded_file)) {
    foreach ($uploaded_file as $v_files_image) { ?>
                        <div class="pull-left mt pr-lg mb" style="width:100px;">
                            <span data-dz-remove class="pull-right existing_image" style="cursor:pointer">
                                <i class="fa fa-times"></i>
                            </span>

                            <?php if ($v_files_image->is_image == 1) { ?>
                            <img src="<?= base_url($v_files_image->path) ?>" class="upload-thumbnail-sm" />
                            <?php } else { ?>
                            <span class="mailbox-attachment-icon">
                                <i class="fa fa-file-text-o"></i>
                            </span>
                            <?php } ?>

                            <input type="hidden" name="path[]" value="<?= $v_files_image->path ?>">
                            <input type="hidden" name="fileName[]" value="<?= $v_files_image->fileName ?>">
                            <input type="hidden" name="fullPath[]" value="<?= $v_files_image->fullPath ?>">
                            <input type="hidden" name="size[]" value="<?= $v_files_image->size ?>">
                            <input type="hidden" name="is_image[]" value="<?= $v_files_image->is_image ?>">
                        </div>
                        <?php } } ?>

                        <script type="text/javascript">
                        $(document).ready(function() {
                            $(".existing_image").on("click", function() {
                                $(this).parent().remove();
                            });

                            fileSerial = 0;
                            // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
                            var previewNode = document.querySelector("#file-upload-row");
                            previewNode.id = "";
                            var previewTemplate = previewNode.parentNode.innerHTML;
                            previewNode.parentNode.removeChild(previewNode);
                            Dropzone.autoDiscover = false;
                            var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                                url: "<?= base_url()?>admin/common/upload_file",
                                thumbnailWidth: 80,
                                thumbnailHeight: 80,
                                parallelUploads: 20,
                                previewTemplate: previewTemplate,
                                dictDefaultMessage: '<?php echo lang("file_upload_instruction"); ?>',
                                autoQueue: true,
                                previewsContainer: "#comments_file-previews",
                                clickable: true,
                                accept: function(file, done) {
                                    if (file.name.length > 200) {
                                        done("Filename is too long.");
                                        $(file.previewTemplate).find(".description-field").remove();
                                    }
                                    //validate the file
                                    $.ajax({
                                        url: "<?= base_url()?>admin/common/validate_project_file",
                                        data: {
                                            file_name: file.name,
                                            file_size: file.size
                                        },
                                        cache: false,
                                        type: 'POST',
                                        dataType: "json",
                                        success: function(response) {
                                            if (response.success) {
                                                fileSerial++;
                                                $(file.previewTemplate).find(
                                                    ".description-field").attr(
                                                    "name", "comment_" + fileSerial);
                                                $(file.previewTemplate).append(
                                                    "<input type='hidden' name='file_name_" +
                                                    fileSerial + "' value='" + file
                                                    .name + "' />\n\
                                                                        <input type='hidden' name='file_size_" +
                                                    fileSerial + "' value='" + file
                                                    .size + "' />");
                                                $(file.previewTemplate).find(
                                                    ".file-count-field").val(
                                                    fileSerial);
                                                done();
                                            } else {
                                                $(file.previewTemplate).find("input")
                                                    .remove();
                                                done(response.message);
                                            }
                                        }
                                    });
                                },
                                processing: function() {
                                    $("#file-save-button").prop("disabled", true);
                                },
                                queuecomplete: function() {
                                    $("#file-save-button").prop("disabled", false);
                                },
                                fallback: function() {
                                    //add custom fallback;
                                    $("body").addClass("dropzone-disabled");
                                    $('.modal-dialog').find('[type="submit"]').removeAttr(
                                        'disabled');

                                    $("#comments_file-dropzone").hide();

                                    $("#file-modal-footer").prepend(
                                        "<button id='add-more-file-button' type='button' class='btn  btn-default pull-left'><i class='fa fa-plus-circle'></i> " +
                                        "<?php echo lang("add_more"); ?>" + "</button>");

                                    $("#file-modal-footer").on("click", "#add-more-file-button",
                                        function() {
                                            var newFileRow =
                                                "<div class='file-row pb pt10 b-b mb10'>" +
                                                "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>" +
                                                "<div class='mb5 pb5'><input class='form-control description-field'  name='comment[]'  type='text' style='cursor: auto;' placeholder='<?php echo lang("comment") ?>' /></div>" +
                                                "</div>";
                                            $("#comments_file-previews").prepend(newFileRow);
                                        });
                                    $("#add-more-file-button").trigger("click");
                                    $("#comments_file-previews").on("click", ".remove-file",
                                        function() {
                                            $(this).closest(".file-row").remove();
                                        });
                                },
                                success: function(file) {
                                    setTimeout(function() {
                                        $(file.previewElement).find(".progress-striped")
                                            .removeClass("progress-striped").addClass(
                                                "progress-bar-success");
                                    }, 1000);
                                }
                            });

                        })
                        </script>
                    </div>
                </div>


                <div class="">
                    <button class="btn btn-primary pull-right mt-lg mb-lg " type="submit"><?= lang('save') ?></button>
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
        <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?= lang('File Name' ) ?></th>
                    <th><?= lang('File') ?></th>
                    <th><?= lang('URL') ?></th>
                    <th class="col-sm-3"><?= lang('date') ?> </th>
                    <th class="col-sm-3"><?= lang('Status') ?> </th>
                    <th class="col-sm-2"><?= lang('action') ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
            if (!empty($three_d_design)) {
                foreach ($three_d_design as $design) {
                    
                    ?>
                <tr>

                    <td> <?= $design->file_name ?></a>
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
                     <td> <?= $design->upload_url ?></a>
                    </td>


                    <td>
                        <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                    </td>
                    <td>
                         <strong>Status</strong> :
                    <span class="label label-<?= 
                    $design->status == 'Approved' ? 'success' :
                    ($design->status == 'Reject' ? 'danger' : 'warning')
                ?>">
                        <?= $design->status ?>
                    </span>

                    <?php if($design->status == 'Reject') { ?>
                    <p> <?php echo $design->remark;?>
                    </p>
                    <?php } ?>
</td>
                    <td>
                        <?= btn_edit('admin/client/details/' . $client_details->client_id . '/3ddesign/' . $design->id) ?>
  <?php 
                           $user_id = $this->session->userdata('user_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();   
                           if($assign_team->member_profile != 'Operation Team') {
                          $disabled = ($design->status == 'Approved') ? 'disabled' : '';

                                                
                                                echo '
                            <button 
                                type="button"
                                class="btn btn-xs btn-success approve_request_btn"
                                title="Approve Design "
                                data-toggle="modal"
                                data-target="#three_d_design_approve_modal"
                                data-three_design_id="'.$design->id.'"
                                data-three_client_id="'.$design->client_id.'"
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
</section>



<!-- 2D Design Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">3D Design Preview</h5>
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




<div class="modal fade" id="three_d_design_approve_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Approve Design Request</h4>
            </div>

            <form action="<?= base_url('admin/client/three_ddesign_status') ?>" method="post">

                <div class="modal-body text-center">

                    <input type="hidden" name="design_id" id="approve_three_design_id">
                    <input type="hidden" name="client_id" id="status_three_client_id">
                  
                    <div class="form-group">
                        <select class="form-control" name="status" id="three_d_request_status" required>
                            <option value="Approved">Approved</option>
                            <option value="Reject">Reject</option>
                        </select>
                    </div>

                    <!-- Remarks Field -->
                    <div class="form-group" id="three_d_remarks_box" style="display:none;">
                        <textarea name="remark" id="remarks_text" class="form-control"
                            placeholder="Enter rejection remarks"></textarea>
                    </div>

                    <p>
                        Are you sure you want to <strong>Update</strong><br>
                        this design request?
                    </p>

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




<script>
$(document).on('click', '.approve_request_btn', function() {
    var three_design_id = $(this).data('three_design_id');
    var three_client_id = $(this).data('three_client_id');
    $('#approve_three_design_id').val(three_design_id);
    $('#status_three_client_id').val(three_client_id);
});


$(document).on('change', '#three_d_request_status', function() {

    var status = $(this).val();

    if (status === 'Reject') {
        $('#three_d_remarks_box').show();
        $('#three_d_remarks_box').prop('required', true);
    } else {
        $('#three_d_remarks_box').hide();
        $('#three_d_remarks_box').prop('required', false);
        $('#three_d_remarks_box').val('');
    }

});


</script>


</div>

<?php } ?>