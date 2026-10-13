<?php
    
   $mom = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('design_type', 'Civil Design')
    ->order_by('id', 'DESC')
    ->get('tbl_client_mom')
    ->result();
    
$momCount = is_array($mom) ? count($mom) : 0;



   $kt_request = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('request_type', 'KT')
    ->order_by('id', 'DESC')
    ->get('tbl_client_service_request')
    ->row();   // sirf ek record


    
 

 //print_r($two_d_design);
    $url = $this->uri->segment(5);
     
    if ($url == 'kt') {        
        $id = $this->uri->segment(6);
        $design_info = $this->db->where('id', $id)->get('tbl_client_service_request')->row();
    } else {
        $id = null;
    }
    
      $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();  

    ?>


<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('KT') ?>
            </strong>


        </div>
    </div>

    <div class="panel-body">

     <?php if ($momCount == 0) { ?>

<div class="alert alert-warning">
  <strong>Warning!</strong> Please Upload MOM 
</div>


     <?php }  else { ?>
         <div class="request_box">
        <?php if (empty($kt_request)) { ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <form action="<?= base_url('admin/client/new_client_request') ?>" method="post" class="form-horizontal">

                    <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                    <input type="hidden" name="request_type" value="KT">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            Request Date <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="date" name="request_date" class="form-control" required>
                        </div>
                        <div class="col-sm-3 text-right">
                            <button class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> Submit KT Request
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
                        <span class="text-muted">KT</span>
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
                                Approve KT
                            </button>
                        </div>
                        <?php } ?>
                    </div>
                </div>



          
        <?php } ?>
</div>

<?php } ?>

    </div>
    </div>
</section>



<!-- 2D Design Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">2D Design Preview</h5>
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

            <form action="<?= base_url('admin/client/client_request_status') ?>" method="post">

                <div class="modal-body text-center">

                    <input type="hidden" name="design_id" id="approve_design_id">
                    <input type="hidden" name="client_id" id="status_client_id">
                    <select class="form-control" name="status" requied>
                        <option value="Approved">Approved</option>
                        <option value="Reject">Reject</option>
                    </select>

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
</script>