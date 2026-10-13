<?php

// Parent count
$parent_count = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('parent_bom_id IS NULL', null, false)
    ->count_all_results('tbl_client_bom');

// Child count (default 0, JS se update karenge)
$child_count = 0;


       $url = $this->uri->segment(5);
  


   $kt_request = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('request_type', 'bom')
    ->order_by('id', 'DESC')
    ->get('tbl_client_service_request')
    ->row();   // sirf ek record


    function get_child_bom($parent_id) {
    $CI =& get_instance();
    return $CI->db
        ->where('parent_bom_id', $parent_id)
        ->get('tbl_client_bom')
        ->result();
}
 

 
    
      $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();  

    ?>







<section class="panel panel-custom">
    <div class="3d_design">
        <div class="panel-heading">
            <div class="panel-title">
                <strong>
                    <?= lang('Upload BOM/ Check List') ?>
                </strong>
                <button id="new_design" class="btn btn-xs pull-right b0">Add BOM/ Check List</button>
            </div>
        </div>




        <?php
    
    
    // $three_d_design = get_result('tbl_client_bom', array('client_id' => $client_details->client_id));
    $parent_bom = $this->db
    ->where('client_id', $client_details->client_id)
    ->where('parent_bom_id IS NULL', null, false)
    ->get('tbl_client_bom')
    ->result();

    $bom_items = [];
    $cutting_list_items = [];
    if (!empty($parent_bom)) {
        foreach ($parent_bom as $p_item) {
            if (!empty($p_item->type) && strpos($p_item->type, 'Cutting List') !== false) {
                $cutting_list_items[] = $p_item;
            } else {
                $bom_items[] = $p_item;
            }
        }
    }
  
    if ($url == 'bom') {
         
        $id = $this->uri->segment(6);
          
        $design_info = $this->db->where('id', $id)->get('tbl_client_bom')->row();
    } else {
        $id = null;
    }
    
    ?>
        <div class="panel-body">
<div class="new_design mb-lg" style="display: <?= !empty($design_info) ? 'block' : 'none' ?>">
    <form action="<?php echo base_url() ?>admin/client/new_bom/<?= $id ?>" method="post"
                    class="form-horizontal">

                    <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">

                    <input type="hidden" name="parent_bom_id" id="parent_bom_id"
                        value="<?= $design_info->parent_bom_id ?? '' ?>">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">File Name
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-lg-8">
                         <input type="text" name="file_name" id="file_name" class="form-control"
    value="<?= $design_info->file_name ?? 'BOM' ?>">

                        </div>
                    </div>

                    <?php 
$is_child = !empty($design_info->parent_bom_id) || !empty($_POST['parent_bom_id']);
?>
<?php if (!$is_child) { ?>
                    
                    <div class="form-group category-field">
                        <label class="col-lg-4 control-label">Category
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-lg-8">
                            <select class="form-control " name="category"  >
                                <option value="">Select Category</option>
                                <option value="Category 1"
                                    <?= (isset($design_info->category) && $design_info->category == 'Category 1') ? 'selected' : '' ?>>
                                    Category 1
                                </option>
                                <option value="Category 2"
                                    <?= (isset($design_info->category) && $design_info->category == 'Category 2') ? 'selected' : '' ?>>
                                    Category 2
                                </option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group type-field">
                        <label class="col-lg-4 control-label">Type
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-lg-8">
                            <select class="form-control" name="type"  >
                                <option value="">Select BOM Type</option>

                                <option value="BOM 1"
                                    <?= (isset($design_info->type) && $design_info->type == 'BOM 1') ? 'selected' : '' ?>>
                                    BOM 1 (Kitchen)
                                </option>

                                <option value="BOM 2"
                                    <?= (isset($design_info->type) && $design_info->type == 'BOM 2') ? 'selected' : '' ?>>
                                    BOM 2 (Wordrobe)
                                </option>

                                <option value="BOM 3"
                                    <?= (isset($design_info->type) && $design_info->type == 'BOM 3') ? 'selected' : '' ?>>
                                    BOM 3 (On Site Handover)
                                </option>

                                <option value="BOM 4"
                                    <?= (isset($design_info->type) && $design_info->type == 'BOM 4') ? 'selected' : '' ?>>
                                    BOM 4 (Loose Furniture)
                                </option>

                                <option value="Cutting List Kitchen"
                                    <?= (isset($design_info->type) && $design_info->type == 'Cutting List Kitchen') ? 'selected' : '' ?>>
                                    Cutting List Kitchen
                                </option>

                                <option value="Cutting List Wordrobe"
                                    <?= (isset($design_info->type) && $design_info->type == 'Cutting List Wordrobe') ? 'selected' : '' ?>>
                                    Cutting List Wordrobe
                                </option>
                            </select>
                        </div>
                    </div>
<?php } ?>

                    <!-- <div class="form-group">
                    <label class="col-lg-4 control-label">Upload URL
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <input type="text" name="upload_url" class="form-control" value="<?= $design_info->upload_url ?>">

                    </div>
                </div> -->


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
                                                role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                                aria-valuenow="0">
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
                                            $(file.previewTemplate).find(".description-field")
                                                .remove();
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
                                                        "name", "comment_" +
                                                        fileSerial);
                                                    $(file.previewTemplate).append(
                                                        "<input type='hidden' name='file_name_" +
                                                        fileSerial + "' value='" +
                                                        file
                                                        .name + "' />\n\
                                                                        <input type='hidden' name='file_size_" +
                                                        fileSerial + "' value='" +
                                                        file
                                                        .size + "' />");
                                                    $(file.previewTemplate).find(
                                                        ".file-count-field").val(
                                                        fileSerial);
                                                    done();
                                                } else {
                                                    $(file.previewTemplate).find(
                                                            "input")
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
                                                $("#comments_file-previews").prepend(
                                                newFileRow);
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
                        <button class="btn btn-primary pull-right mt-lg mb-lg "
                            type="submit"><?= lang('save') ?></button>
                    </div>

                </form>
            </div>
             <?php 
             $can_view_bom          = ($user_type == 1 || check_permission('bom_list'));
             $can_view_cutting_list = ($user_type == 1 || check_permission('cutting_list'));
             $can_view_section      = ($can_view_bom || $can_view_cutting_list || check_permission('bom_only_view'));
             ?>
             <?php if ($can_view_section) { ?>

            <?php
            if (!function_exists('render_client_details_bom_table')) {
                function render_client_details_bom_table($items, $user_type, $client_details, $empty_message = 'No Records Found') {
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><?= lang('File Name') ?></th>
                                    <th><?= lang('Category') ?></th>
                                    <th><?= lang('Type') ?></th>
                                    <th><?= lang('File') ?></th>
                                    <th class="col-sm-1"><?= lang('date') ?> </th>
                                    <th class="col-sm-2"><?= lang('Status') ?> </th>
                                    <th class="col-sm-1"><?= lang('action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $design): ?>
                                        <tr>
                                            <td><?= html_escape($design->file_name) ?></td>
                                            <td><?= !empty($design->category) ? html_escape($design->category) : '-' ?></td>
                                            <td><span class="label label-default"><?= !empty($design->type) ? html_escape($design->type) : '-' ?></span></td>
                                            <td>
                                                <?php
                                                $files = json_decode($design->upload_file);
                                                if (!empty($files)) {
                                                    foreach ($files as $file) {
                                                        if ($file->is_image) {
                                                            echo '<img src="' . base_url($file->path) . '" width="60" class="mr-sm preview-design" data-path="' . base_url($file->path) . '" data-type="' . ($file->is_image ? 'image' : 'file') . '" style="cursor:pointer; margin-right:5px;">';
                                                        } else {
                                                            echo '<a href="javascript:void(0)" class="preview-design label label-info" data-path="' . base_url($file->path) . '" data-type="pdf" style="margin-right:5px;">' . html_escape($file->fileName) . '</a>';
                                                        }
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?= strftime(config_item('date_format'), strtotime($design->created_date)) . ' ' . display_time($design->created_date); ?>
                                            </td>
                                            <td>
                                                <strong>Status</strong> :
                                                <span class="label label-<?= $design->status == 'Accepted' ? 'success' : ($design->status == 'Reject' ? 'danger' : 'warning') ?>">
                                                    <?= html_escape($design->status) ?>
                                                </span>
                                                <?php if ($design->status == 'Reject' && !empty($design->remark)): ?>
                                                    <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= html_escape($design->remark); ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user_type == 1 || check_permission('bom_edit')): ?>
                                                    <?= btn_edit('admin/client/details/' . $client_details->client_id . '/bom/' . $design->id) ?>
                                                    <button class="btn btn-xs btn-info add-child-bom" data-parent_id="<?= $design->id ?>">
                                                        Add Child BOM
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Child Rows (Additional BOM) -->
                                        <?php
                                        $children = get_child_bom($design->id);
                                        if (!empty($children)):
                                            foreach ($children as $child):
                                        ?>
                                                <tr>
                                                    <td style="padding-left:40px;">↳ <?= html_escape($child->file_name) ?></td>
                                                    <td><?= !empty($child->category) ? html_escape($child->category) : '-' ?></td>
                                                    <td><span class="label label-default"><?= !empty($child->type) ? html_escape($child->type) : '-' ?></span></td>
                                                    <td>
                                                        <?php
                                                        $c_files = json_decode($child->upload_file);
                                                        if (!empty($c_files)) {
                                                            foreach ($c_files as $file) {
                                                                if ($file->is_image) {
                                                                    echo '<img src="' . base_url($file->path) . '" width="60" class="mr-sm preview-design" data-path="' . base_url($file->path) . '" data-type="' . ($file->is_image ? 'image' : 'file') . '" style="cursor:pointer; margin-right:5px;">';
                                                                } else {
                                                                    echo '<a href="javascript:void(0)" class="preview-design label label-info" data-path="' . base_url($file->path) . '" data-type="pdf" style="margin-right:5px;">' . html_escape($file->fileName) . '</a>';
                                                                }
                                                            }
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?= strftime(config_item('date_format'), strtotime($child->created_date)) . ' ' . display_time($child->created_date); ?>
                                                    </td>
                                                    <td>
                                                        <strong>Status</strong> :
                                                        <span class="label label-<?= $child->status == 'Accepted' ? 'success' : ($child->status == 'Reject' ? 'danger' : 'warning') ?>">
                                                            <?= html_escape($child->status) ?>
                                                        </span>
                                                        <?php if ($child->status == 'Reject' && !empty($child->remark)): ?>
                                                            <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= html_escape($child->remark); ?></p>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($user_type == 1 || check_permission('bom_edit')): ?>
                                                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/bom/' . $child->id) ?>
                                                            <?php
                                                            $CI =& get_instance();
                                                            $u_id = $CI->session->userdata('user_id');
                                                            $assign_team = $CI->db->where('user_id', $u_id)->get('tbl_client_assign_team')->row();
                                                            if (empty($assign_team) || (isset($assign_team->member_profile) && $assign_team->member_profile != 'Operation Team')) {
                                                                $disabled = ($child->status == 'Accepted') ? 'disabled' : '';
                                                                echo '
                                                                <button 
                                                                    type="button"
                                                                    class="btn btn-xs btn-success child_approve_request_btn"
                                                                    data-toggle="modal"
                                                                    data-target="#three_d_design_approve_modal"
                                                                    data-three_design_id="' . $child->id . '"
                                                                    data-three_client_id="' . $child->client_id . '"
                                                                    data-is_child="1"
                                                                    ' . $disabled . '
                                                                >
                                                                    <span class="fa fa-check"></span>
                                                                </button>';
                                                            }
                                                            ?>
                                                        <?php endif; ?>
                                                    </td>
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

            <?php
            $is_bom_tab_active          = $can_view_bom;
            $is_cutting_list_tab_active = (!$can_view_bom && $can_view_cutting_list);
            ?>

            <?php if ($can_view_bom || $can_view_cutting_list) { ?>
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs" style="margin-top: 20px; margin-bottom: 0;">
                    <?php if ($can_view_bom): ?>
                        <li class="<?= $is_bom_tab_active ? 'active' : '' ?>">
                            <a href="#client_tab_bom" data-toggle="tab" aria-expanded="<?= $is_bom_tab_active ? 'true' : 'false' ?>" style="font-size: 14px; font-weight: 600; padding: 10px 18px;">
                                <i class="fa fa-list-alt"></i> BOM
                                <span class="label label-primary" style="margin-left: 5px; font-size: 11px;"><?= count($bom_items) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($can_view_cutting_list): ?>
                        <li class="<?= $is_cutting_list_tab_active ? 'active' : '' ?>">
                            <a href="#client_tab_cutting_list" data-toggle="tab" aria-expanded="<?= $is_cutting_list_tab_active ? 'true' : 'false' ?>" style="font-size: 14px; font-weight: 600; padding: 10px 18px;">
                                <i class="fa fa-scissors"></i> Cutting List
                                <span class="label label-info" style="margin-left: 5px; font-size: 11px;"><?= count($cutting_list_items) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content bg-white" style="padding: 15px; border: 1px solid #ddd; border-top: none; margin-bottom: 20px;">
                    <?php if ($can_view_bom): ?>
                        <!-- BOM Tab Pane -->
                        <div class="tab-pane <?= $is_bom_tab_active ? 'active' : '' ?>" id="client_tab_bom">
                            <?php render_client_details_bom_table($bom_items, $user_type, $client_details, 'No BOM Found'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($can_view_cutting_list): ?>
                        <!-- Cutting List Tab Pane -->
                        <div class="tab-pane <?= $is_cutting_list_tab_active ? 'active' : '' ?>" id="client_tab_cutting_list">
                            <?php render_client_details_bom_table($cutting_list_items, $user_type, $client_details, 'No Cutting List Found'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>

            <?php } ?>
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
                <h5 class="modal-title">BOM Preview</h5>
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

            <form action="<?= base_url('admin/client/bom_status') ?>" method="post">

                <div class="modal-body text-center">

                    <input type="hidden" name="design_id" id="approve_three_design_id">
                    <input type="hidden" name="client_id" id="status_three_client_id">

                    <div class="form-group">
                        <select class="form-control" name="status" id="three_d_request_status" required>
                            <option value="Accepted">Accepted</option>
                              <!-- <option value="Reject">Reject</option> -->
                        </select>
                    </div>

                    <!-- Remarks Field -->
                    <div class="form-group" id="three_d_remarks_box" style="display:none;">
                        <textarea name="remark" id="remarks_text" class="form-control"
                            placeholder="Enter rejection remarks"></textarea>
                    </div>

                    <p>
                        Are you sure you want to <strong>Update</strong><br>
                        this request?
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
 
$('#new_design').click(function() {

    $('#parent_bom_id').val('');

    $('.new_design').show();

    $('.category-field').show();
    $('.type-field').show();

    $('.panel-title strong').text('Add BOM/ Check List');

    // ✅ Parent default
    $('#file_name').val('BOM');
});

 $(document).on('click', '.child_approve_request_btn', function() {

    var design_id = $(this).data('three_design_id');
    var client_id = $(this).data('three_client_id');

    $('#approve_three_design_id').val(design_id);
    $('#status_three_client_id').val(client_id);

    // Child → Order Placed
    // $('#three_d_request_status').html(
    //     '<option value="Order Placed">Order Placed</option>'
    // );
});


$(document).on('change', 'select[name="type"]', function() {
    var typeVal = $(this).val();
    var clientSrId = "<?= !empty($client_details->client_sr_id) ? $client_details->client_sr_id : ('HC' . $client_details->client_id) ?>";
    if (typeVal) {
        var cleanType = typeVal.replace(/\s+/g, '');
        var newFileName = clientSrId + '_' + cleanType;
        $('#file_name').val(newFileName);
    }
});

$('.add-child-bom').click(function() {
    $('.panel-title strong').text('Add Child BOM');
});


$(document).on('click', '.add-child-bom', function() {

    var parent_id = $(this).data('parent_id');

    $('#parent_bom_id').val(parent_id);

    $('.new_design').show();

    $('.category-field').hide();
    $('.type-field').hide();

    $('.panel-title strong').text('Add Child BOM');

    // ✅ Child ke liye auto name
    $.ajax({
        url: "<?= base_url('admin/client/get_child_count') ?>",
        type: "POST",
        data: { parent_id: parent_id },
        success: function(res) {
            var count = parseInt(res) || 0;
            $('#file_name').val('Additional ' + (count + 1));
        }
    });

});
</script>


</div>

     <style>
.category-field,
.type-field {
    display: block;
}
</style>