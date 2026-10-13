<?php
    
    
    $mom = get_order_by('tbl_client_check_list', array('client_id' => $client_details->client_id),'id');
$momCount = is_array($mom) ? count($mom) : 0;

    
//      $all_proposals_info = get_order_by(
//     'proposals',
//     array('lead_id' => $client_details->leads_id),
//     'id'
// );


    
    $latest_proposal = null;
    $other_proposals = [];

    if (!empty($mom)) {
        $latest_proposal = $mom[0]; // first = latest
        $other_proposals = array_slice($mom);
    }


    $url = $this->uri->segment(5);
    if ($url == 'check_list') {        
        $id = $this->uri->segment(6);
        $design_info = $this->db->where('id', $id)->get('tbl_client_check_list')->row();
    } else {
        $id = null;
    }
    
    ?>
    
    <section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                <?= lang('Check List') ?>
            </strong>
      
     <button id="new_design" class="btn btn-xs pull-right b0">
        Add Check List
    </button>
         </div>
    </div>
    
    <div class="panel-body">
        <div class="new_design mb-lg" style="display: <?= !empty($design_info) ? 'block' : 'none' ?>">
            <form action="<?php echo base_url() ?>admin/client/new_check_list/<?= $id ?>" method="post"
                  enctype="multipart/form-data" class="form-horizontal">
                                
                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                
                 <!-- <div class="form-group">
                    <label class="col-lg-4 control-label">Design Type
                        <span
                            class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                         <select class="form-control" name="design_type">
                           <option value="None"
        <?= (!empty($design_info) && $design_info->design_type == 'None') ? 'selected' : '' ?>>
        None
    </option>

    <option value="Modular Design"
        <?= (!empty($design_info) && $design_info->design_type == 'Modular Design') ? 'selected' : '' ?>>
        Modular Design
    </option>

    <option value="Civil Design"
        <?= (!empty($design_info) && $design_info->design_type == 'Civil Design') ? 'selected' : '' ?>>
        Civil Design
    </option>
                            
                        </select>
                    </div>
                </div> -->
                
                 <div class="form-group">
                    <label class="col-lg-4 control-label">File Name
                        <span
                            class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <input type="text" name="file_name" class="form-control" value="<?= $design_info->file_name ?>">

                    </div>
                </div>
                
                <div class="form-group">
    <label class="col-lg-4 control-label">
        Upload Files
        <span class="text-danger">*</span>
    </label>

    <div class="col-lg-8">
        <input 
            type="file" 
            name="design_files[]" 
            class="form-control"
            multiple
            accept=".jpg,.jpeg,.png,.pdf,.dwg,.ppt,.pptx"
        >

        <small class="text-muted">
            Allowed: JPG, PNG, PDF, DWG
        </small>
    </div>
</div>

 

                <div class="">
                    <button class="btn btn-primary pull-right mt-lg mb-lg " type="submit"><?= lang('save') ?></button>
                </div>
            
            </form>
        </div>
        <script>
            $(document).ready(function () {
                $('#new_design').click(function () {
                    $('.new_design').toggle('slow');
                });
            });
        </script>
        <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('File Name' ) ?></th>
                <th><?= lang('File') ?></th>
                <!-- <th><?= lang('Design Type') ?></th> -->
                <th><?= lang('Status') ?></th>
                <th class="col-sm-3"><?= lang('date') ?> </th>
                <th class="col-sm-2"><?= lang('action') ?></th>
            </tr>
            </thead>
            
            <tbody>
            <?php
            if (!empty($latest_proposal)) {
                 $design = $latest_proposal;
                    
                    ?>
                    <tr>
                        
                        <td> <?= $design->file_name ?></a>  </td>
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

                    <!-- <td> <?= $design->design_type ?></a> </td> -->
                    <td> <?= $design->status ?></a>  </td>

                      
                        <td>
                        <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                        </td>
                        <td>
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/check_list/' . $design->id) ?>

                          <?php 
                           $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();  
                        //   print_r( $designation_id) ;
                         //  echo 'he';
                          if($user_type == 1 || $designation_id == 76 || $designation_id == 45 ) {
                         $disabled = ($design->status == 'Approved') ? 'disabled' : '';

                                                
                                                echo '
                            <button 
                                type="button"
                                class="btn btn-xs btn-success approve_request_btn"
                                title="Approve Design "
                                data-toggle="modal"
                                data-target="#approveHandOverModal"
                                data-design_id="'.$design->id.'"
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


            <!-- <a href="#demo" class="btn btn-info" data-toggle="collapse">See Old Design</a> -->
             <div id="demo" class="collapse">
          <table class="table table-striped " cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('File Name' ) ?></th>
                <th><?= lang('File') ?></th>
                <!-- <th><?= lang('Design Type') ?></th> -->
                <th><?= lang('Status') ?></th>
                <th class="col-sm-3"><?= lang('date') ?> </th>
                <th class="col-sm-2"><?= lang('action') ?></th>
            </tr>
            </thead>
            
            <tbody>
            <?php
            if (!empty($other_proposals)) {
                foreach ($other_proposals as $design) {
                    
                    ?>
                    <tr>
                        
                        <td> <?= $design->file_name ?></a>  </td>
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

                    <!-- <td> <?= $design->design_type ?></a> </td> -->
                    <td> <?= $design->status ?></a>  </td>

                      
                        <td>
                        <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                        </td>
                        <td>
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/check_list/' . $design->id) ?>

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
                                data-target="#approveHandOverModal"
                                data-design_id="'.$design->id.'"
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
 

<!-- 2D Design Preview Modal -->
<div class="modal fade" id="designPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Check List Preview</h5>
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
$(document).on('click', '.preview-design', function () {

    var filePath = $(this).data('path');
    var fileType = $(this).data('type');
    var html = '';

    if (fileType === 'image') {
        html = '<img src="'+filePath+'" class="img-fluid" style="width:100%;">';
    } 
    else if (filePath.toLowerCase().endsWith('.pdf')) {
        html = '<iframe src="'+filePath+'" width="100%" height="600px"></iframe>';
    } 
    else {
        html = '<a href="'+filePath+'" target="_blank" class="btn btn-primary">Download File</a>';
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
                <h4 class="modal-title">Approve Check List Request</h4>
            </div>

            <form action="<?= base_url('admin/client/check_list_status') ?>"
                  method="post">

                <div class="modal-body text-center">

                    <input type="hidden" name="design_id" id="approve_design_id">
                    <input type="hidden" name="client_id" id="status_client_id">
                    <select class="form-control" name="status" requied>
                        <option value="Approved">Approved</option>
                        <option value="Reject">Reject</option>
                    </select>

                    <p>
                        Are you sure you want to <strong>Update</strong><br>
                        this  request?
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
$(document).on('click', '.approve_request_btn', function () {
    var design_id = $(this).data('design_id');
    var client_id = $(this).data('client_id');
    $('#approve_design_id').val(design_id);
    $('#status_client_id').val(client_id);
});
</script>
