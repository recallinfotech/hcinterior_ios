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

  $this->load->helper('permission_helper');
  $can_view = ($user_type == 1 || check_permission('final_production_drawing'));
  if (!$can_view) {
?>
    <div class="alert alert-danger text-center" style="margin: 20px;">
        <i class="fa fa-lock fa-2x m-b-sm"></i>
        <p><strong>Access Denied</strong></p>
        <p class="text-muted">You do not have permission to view Final Production Drawing.</p>
    </div>
<?php
    return;
  }
?>

<?php
    $validation_files = $this->db
    ->where('client_id', $client_details->client_id)
    ->order_by('id','desc')
    ->get('tbl_client_validation_files')
    ->row();
?>

  


  

      

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




 

<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('Modular Drawing') ?>
            </strong>
            <button id="new_design" class="btn btn-xs pull-right b0">Add Modular Drawing</button>
        </div>
    </div>
    <?php
    
    
  
$two_d_design = $this->db
    ->select('d.*, a.fullname')
    ->from('tbl_client_two_d_design d')
    ->join('tbl_account_details a', 'a.user_id = d.uploaded_by', 'left')
    ->where('d.client_id', $client_details->client_id)
    ->where('d.design_type', 'Modular Design')
    ->where('d.is_final', 1)
    ->order_by('d.id', 'DESC')
    ->get()
    ->result();


$latest_proposal = null;

if (!empty($two_d_design)) {
    $latest_proposal = $two_d_design[0];
}
    
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
    ->where('is_final', 1)
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
                    <th  class="col-sm-3"><?= lang('URL') ?></th>
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
<?php } ?>  <?= $design->file_name ?></a> </td>
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
                                        <td> <p class="nowrap"> <?= $design->upload_url ?></a> </p> </td>
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
                <img src="<?= $filePath ?>" 
                     width="60"
                     class="preview-design"
                     data-path="<?= $filePath ?>"
                     data-type="image"
                     style="cursor:pointer">
            <?php } else { ?>
                <a href="javascript:void(0)" 
                   class="preview-design label label-info"
                   data-path="<?= $filePath ?>"
                   data-type="pdf">
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


<?php  if($user_type == 1 || has_client_permission($client_details->client_id,'final_drawing') || has_client_permission($client_details->client_id,'all_access')){ 
    
    if($design->status == 'Approved' && $design->design_type == 'Modular Design' ){ ?>
 

                    <button href="javascript:void(0)" 
   class="btn btn-xs btn-success final_design"
   data-id="<?= $design->id ?>"
   data-client_id="<?= $design->client_id ?>"
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


                <?php if ($user_type != 54) {
                    if($design->status == 'Pending') { ?>
                <?= btn_edit('admin/client/details/' . $client_details->client_id . '/2ddesign/' . $design->id) ?>
                <?php }} ?>
                        <?php 
                          
                        //   print_r( $designation_id) ;
                         //  echo 'he';
                          if($user_type == 1 || $designation_id == 76 || $designation_id == 45 || $designation_id == 77) {
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

            <form action="<?= base_url('admin/client/twoddesign_status') ?>" method="post" enctype="multipart/form-data">

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
                     <div class="form-group" >
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

   
<style>
   .remarks-box {
    background: #ffffff;
    padding: 10px;
}

hr {
    display:none; 
}
.remarks-box h3{
    display:none;
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



$(document).on("click",".final_design",function(){

    var $btn = $(this);
    var design_id = $btn.data("id");
    var client_id = $btn.data("client_id");

    if(confirm("Make this Final Design?")){

        $btn.prop('disabled', true).text('Please wait...');

        $.ajax({
            url: "<?= base_url('admin/client/set_final_design') ?>",
            type: "POST",
            data: {
                design_id: design_id,
                client_id: client_id
            },
            success:function(res){
                location.reload();
            },
            error: function(){
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

$(document).on('change', 'select[name="design_type"]', function () {
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
$(document).ready(function () {
    $('select[name="design_type"]').trigger('change');
});
</script>

<style>
    .nowrap {
word-break: break-all; 
   }

   .d-none {
    display:none;
   }

   .table tr th {
    white-space: nowrap;
   }
   </style>