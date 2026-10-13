<style>
    .wd-100 {
        width: 100px !important;
    }

    .pointer {
        cursor: pointer !important;
    }

    .wd-0 {
        width: 0px !important;
    }
</style>
<?php

$comment_details = $this->common_model->get_comment_details($id, $module);
$lead = $id;

?>



 
             <div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('comments') ?></h3>
    </div>
    <div class="panel-body chat" id="chat-box">
        <?php echo form_open(base_url("admin/common/save_comments"), array("id" => $module . "-comment-form", "class" => "form-horizontal general-form", "enctype" => "multipart/form-data", "role" => "form")); ?>
        
        <input type="hidden" name="module" value="<?php if (!empty($module)) {
            echo $module;
        } ?>">
        <input type="hidden" name="module_field_id" value="<?php if (!empty($id)) {
            echo $id;
        } ?>">
        
        
        <div class="form-group">
            <div class="col-sm-12">
                <?php
                echo form_textarea(array(
                    "id" => "comment_description",
                    "name" => "comment",
                    "class" => "form-control comment_description",
                    "placeholder" =>  $module.' '.lang('comments'),
                    "data-rule-required" => true,
                    "rows" => 4,
                    "data-msg-required" => lang("field_required"),
                ));
                ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <div id="comments_file-dropzone" class="dropzone mb15">
                
                </div>
                <div id="comments_file-dropzone-scrollbar">
                    <div id="comments_file-previews">
                        <div id="file-upload-row" class="mt pull-left">
                            <div class="preview box-content pr-lg wd-100">
                                                <span data-dz-remove class="pull-right pointer">
                                                    <i class="fa fa-times"></i>
                                                </span>
                                <img data-dz-thumbnail class="upload-thumbnail-sm"/>
                                <input class="file-count-field" type="hidden" name="files[]" value=""/>
                                <div class="mb progress progress-striped upload-progress-sm active mt-sm"
                                     role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                     aria-valuenow="0">
                                    <div class="progress-bar progress-bar-success wd-0"
                                         data-dz-uploadprogress></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
        
        <div class="form-group">
            <div class="col-sm-12">
                <div class="pull-right">
                    <button type="submit" id="file-save-button"
                            class="btn btn-primary"><?= lang('post_comment') ?></button>
                </div>
            </div>
        </div>
        <hr/>
        <?php echo form_close(); ?>
        <?php $this->load->view('admin/common/comments_list', array('comment_type' => $module,
            'comment_details' => $comment_details)) ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#file-save-button').on('click', function (e) {
                    var ubtn = $(this);
                    ubtn.html('Please wait...');
                    ubtn.addClass('disabled');
                });
                $("#<?php echo $module; ?>-comment-form").appForm({
                    isModal: false,
                    onSuccess: function (result) {
                        $(".comment_description").val("");
                        $(".dz-complete").remove();
                        $('#file-save-button').removeClass("disabled").html(
                            '<?= lang('post_comment') ?>');
                        $(result.data).insertAfter(
                            "#<?php echo $module; ?>-comment-form");
                        toastr[result.status](result.message);
                    }
                });
                var fileSerial = 0;
                // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
                var previewNode = document.querySelector("#file-upload-row");
                previewNode.id = "";
                var previewTemplate = previewNode.parentNode.innerHTML;
                previewNode.parentNode.removeChild(previewNode);
                Dropzone.autoDiscover = false;
                var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                    url: "<?= base_url() ?>admin/common/upload_file",
                    thumbnailWidth: 80,
                    thumbnailHeight: 80,
                    parallelUploads: 20,
                    previewTemplate: previewTemplate,
                    dictDefaultMessage: '<?php echo lang("file_upload_instruction"); ?>',
                    autoQueue: true,
                    previewsContainer: "#comments_file-previews",
                    clickable: true,
                    accept: function (file, done) {
                        if (file.name.length > 200) {
                            done("Filename is too long.");
                            $(file.previewTemplate).find(".description-field").remove();
                        }
                        //validate the file
                        $.ajax({
                            url: "<?= base_url() ?>admin/common/validate_project_file",
                            data: {
                                file_name: file.name,
                                file_size: file.size
                            },
                            cache: false,
                            type: 'POST',
                            dataType: "json",
                            success: function (response) {
                                if (response.success) {
                                    fileSerial++;
                                    $(file.previewTemplate).find(
                                        ".description-field").attr(
                                        "name", "comment_" + fileSerial);
                                    $(file.previewTemplate).append(
                                        "<input type='hidden' name='file_name_" +
                                        fileSerial + "' value='" + file
                                            .name + "' />\n\
                                     <input type='hidden' name='file_size_" + fileSerial + "' value='" + file.size +
                                        "' />");
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
                    processing: function () {
                        $("#file-save-button").prop("disabled", true);
                    },
                    queuecomplete: function () {
                        $("#file-save-button").prop("disabled", false);
                    },
                    fallback: function () {
                        //add custom fallback;
                        $("body").addClass("dropzone-disabled");
                        $('.modal-dialog').find('[type="submit"]').removeAttr(
                            'disabled');

                        $("#comments_file-dropzone").hide();

                        $("#file-modal-footer").prepend(
                            "<button id='add-more-file-button' type='button' class='btn  btn-default pull-left'><i class='fa fa-plus-circle'></i> " +
                            "<?php echo lang("add_more"); ?>" + "</button>");

                        $("#file-modal-footer").on("click", "#add-more-file-button",
                            function () {
                                var newFileRow =
                                    "<div class='file-row pb pt10 b-b mb10'>" +
                                    "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>" +
                                    "<div class='mb5 pb5'><input class='form-control description-field'  name='comment[]'  type='text' style='cursor: auto;' placeholder='<?php echo lang("comment") ?>' /></div>" +
                                    "</div>";
                                $("#comments_file-previews").prepend(newFileRow);
                            });
                        $("#add-more-file-button").trigger("click");
                        $("#comments_file-previews").on("click", ".remove-file",
                            function () {
                                $(this).closest(".file-row").remove();
                            });
                    },
                    success: function (file) {
                        setTimeout(function () {
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



             <div class="panel panel-custom">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('Reminder') ?></h3>
    </div>

     <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form" action="<?php echo base_url(); ?>admin/invoice/reminder/leads/<?= $leads_details->leads_id ?>/<?php
                                                                                                                                                                                                            if (!empty($reminder_info)) {
                                                                                                                                                                                                                echo $reminder_info->reminder_id;
                                                                                                                                                                                                            }
                                                                                                                                                                                                            ?>" method="post" class="form-horizontal  ">
                 <div class="form-group">
                     <label class="col-lg-3 control-label"><?= lang('date_to_notified') ?> <span class="text-danger">*</span></label>
                     <div class="col-lg-5">
                         <div class="input-group">
                             <input type="text" name="date" class="form-control datetimepicker" value="<?php
                                                                                                        if (!empty($reminder_info->date)) {
                                                                                                            echo $reminder_info->date;
                                                                                                        } else {
                                                                                                            echo date('Y-m-d h:i');
                                                                                                        }
                                                                                                        ?>" data-date-min-date="<?= date('Y-m-d'); ?>">
                             <div class="input-group-addon">
                                 <a href="#"><i class="fa fa-calendar"></i></a>
                             </div>
                         </div>
                     </div>

                 </div>
                 <!-- End discount Fields -->
                 <div class="form-group terms">
                     <label class="col-lg-3 control-label"><?= lang('description') ?> </label>
                     <div class="col-lg-5">
                         <textarea name="description" class="form-control"><?php
                                                                            if (!empty($reminder_info)) {
                                                                                echo $reminder_info->description;
                                                                            }
                                                                            ?></textarea>
                     </div>
                 </div>
                 <!-- <div class="form-group" style="display:none">
                    <?php echo $this->session->userdata('user_id') ;?>
                     <label class="col-lg-3 control-label"><?= lang('set_reminder_to') ?> <span class="text-danger">*</span></label>
                     <div class="col-lg-5">
                         <select class="form-control select_box" name="user_id" style="width: 100%" >
                             <?php
                                $permission_user = $this->items_model->all_permission_user('55');
                                if (!empty($permission_user)) {
                                    foreach ($permission_user as $key => $v_users) {
                                ?>
                                     <option <?php
                                                if (!empty($reminder_info)) {
                                                    echo $reminder_info->user_id == $v_users->user_id ? 'selected' : null;
                                                }
                                                ?> value="<?= $v_users->user_id ?>">
                                         <?= $v_users->fullname ?></option>
                             <?php }
                                }
                                ?>
                         </select>
                     </div>
                 </div> -->

               <?php
// Get permission JSON for the current lead
$lead_row = $this->db->get_where('tbl_leads', ['leads_id' => $lead])->row();
$lead_permission_array = [];
if (!empty($lead_row->permission)) {
    $lead_permission_array = json_decode($lead_row->permission, true);
}

// Get selected user IDs from reminder_info
$selected_users = [];

// For edit case
if (!empty($reminder_info)) {
    if (is_array($reminder_info->user_id)) {
        $selected_users = $reminder_info->user_id;
    } else {
        $selected_users = explode(',', $reminder_info->user_id);
    }
} else {
    // For add case, default select all users with permission on this lead
    $selected_users = array_keys($lead_permission_array);
}
?>
<div class="form-group" style="display:none">
    <label class="col-lg-3 control-label"><?= lang('set_reminder_to') ?> <span class="text-danger">*</span></label>
    <div class="col-lg-5">
        <select class="form-control select_box" name="user_id[]" multiple style="width: 100%">
            <?php
            $permission_user = $this->items_model->all_permission_user('55');

            if (!empty($permission_user)) {
                foreach ($permission_user as $v_users) {
                    // show only users who have permission on this lead
                    if (array_key_exists($v_users->user_id, $lead_permission_array)) {
                        $is_selected = in_array($v_users->user_id, $selected_users) ? 'selected' : '';
                        ?>
                        <option value="<?= $v_users->user_id ?>" <?= $is_selected ?>>
                            <?= $v_users->fullname ?>
                        </option>
                        <?php
                    }
                }
            }
            ?>
        </select>
    </div>
</div>



                 <div class="form-group terms" style="display:none">
                     <label class="col-lg-3 control-label"></label>
                     <div class="col-lg-5">
                         <div class="checkbox c-checkbox">
                             <label class="needsclick">
                                 <input type="checkbox" value="Yes" <?php if (!empty($reminder_info) && $reminder_info->notify_by_email == 'Yes') {
                                                                        echo 'checked';
                                                                    } ?> name="notify_by_email">
                                 <span class="fa fa-check"></span>
                                 <?= lang('send_also_email_this_reminder') ?>
                             </label>
                         </div>
                     </div>
                 </div>

                 <div class="form-group">
                     <label class="col-lg-3 control-label"></label>
                     <div class="col-lg-5">
                         <button type="submit" class="btn btn-purple"><?= lang('update') ?></button>
                     </div>
                 </div>
             </form>

</div> 

 

 <link rel="stylesheet" href="<?= base_url() ?>assets/plugins/datetimepicker/jquery.datetimepicker.min.css">
 <?php include_once 'assets/plugins/datetimepicker/jquery.datetimepicker.full.php'; ?>

 <script type="text/javascript">
     init_datepicker();

     // Date picker init with selected timeformat from settings
     function init_datepicker() {
         var datetimepickers = $('.datetimepicker');
         if (datetimepickers.length == 0) {
             return;
         }
         var opt_time;
         // Datepicker with time
         $.each(datetimepickers, function() {
             opt_time = {
                 lazyInit: true,
                 scrollInput: false,
                 format: 'Y-m-d H:i',
             };

             opt_time.formatTime = 'H:i';
             // Check in case the input have date-end-date or date-min-date
             var max_date = $(this).data('date-end-date');
             var min_date = $(this).data('date-min-date');
             if (max_date) {
                 opt_time.maxDate = max_date;
             }
             if (min_date) {
                 opt_time.minDate = min_date;
             }
             // Init the picker
             $(this).datetimepicker(opt_time);
         });
     }
 </script>
