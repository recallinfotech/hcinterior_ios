<?php
$url = $this->uri->segment(5);
$edit_id = ($url == 'completion_file') ? $this->uri->segment(6) : null;
$completion_info = !empty($edit_id) ? $this->db->where('id', $edit_id)->get('tbl_client_completion_file')->row() : null;

$completion_files = $this->db
    ->where('client_id', $client_details->client_id)
    ->order_by('id', 'DESC')
    ->get('tbl_client_completion_file')
    ->result();

$user_id = $this->session->userdata('user_id');
$user_type = $this->session->userdata('user_type');

$can_view_completion = ($user_type == 1 || check_permission('completion_file'));
if (!$can_view_completion) {
?>
    <div class="alert alert-danger text-center" style="margin: 20px;">
        <i class="fa fa-lock fa-2x m-b-sm"></i>
        <p><strong>Access Denied</strong></p>
        <p class="text-muted">You do not have permission to view Completion File.</p>
    </div>
<?php
    return;
}
?>

<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>
                Completation File
            </strong>

            <button id="toggle_completion_form" class="btn btn-xs btn-primary pull-right">
                <?= !empty($completion_info) ? '<i class="fa fa-times"></i> Close Form' : '<i class="fa fa-plus"></i> Add Completation File' ?>
            </button>
        </div>
    </div>

    <div class="panel-body">
        <div class="completion_form_box mb-lg" style="display: <?= !empty($completion_info) ? 'block' : 'none' ?>; background: #fafafa; padding: 20px; border: 1px solid #e5e5e5; border-radius: 4px; margin-bottom: 25px;">
            <form action="<?= base_url('admin/client/new_completion_file/' . (!empty($edit_id) ? $edit_id : '')) ?>" method="post" enctype="multipart/form-data" class="form-horizontal" id="completionFileForm">
                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">

                <div class="form-group">
                    <label class="col-sm-3 control-label">File Name <span class="text-danger">*</span></label>
                    <div class="col-sm-7">
                        <input type="text" name="file_name" class="form-control" placeholder="Enter file / item name" value="<?= !empty($completion_info) ? html_escape($completion_info->file_name) : '' ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0px">
                    <label class="col-sm-3 control-label"><?= lang('attachment') ?> <span class="text-danger">*</span></label>

                    <div class="col-sm-7">
                        <!-- Dropzone Area -->
                        <div id="comments_file-dropzone" class="dropzone mb15" style="border: 2px dashed #337ab7; background: #fdfdfd; min-height: 120px; border-radius: 6px;">
                        </div>

                        <div id="comments_file-dropzone-scrollbar">
                            <div id="comments_file-previews" class="clearfix">
                                <div id="file-upload-row" class="mt pull-left">
                                    <div class="preview box-content pr-lg" style="width:100px;">
                                        <span data-dz-remove class="pull-right" style="cursor: pointer">
                                            <i class="fa fa-times text-danger"></i>
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

                        <!-- Direct File Input Fallback / Alternative -->
                        <div class="mt-sm" style="margin-top: 12px;">
                            <label style="font-weight: 600; font-size: 13px; color: #555;">
                                <i class="fa fa-folder-open-o"></i> Or Choose Image / PDF File(s):
                            </label>
                            <input type="file" name="completion_files[]" id="completion_files_input" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.pdf">
                            <small class="text-muted">Supported: JPG, JPEG, PNG, WEBP, GIF, PDF</small>
                            <div id="manual_preview_box" class="mt-sm clearfix" style="margin-top: 10px;"></div>
                        </div>

                        <?php
                        $uploaded_file = array();
                        if (!empty($completion_info->upload_file)) {
                            $uploaded_file = json_decode($completion_info->upload_file);
                        }
                        if (!empty($uploaded_file)) {
                            echo '<div class="clearfix mt-sm" style="margin-top: 15px;"><strong>Existing Files:</strong><br>';
                            foreach ($uploaded_file as $v_files_image) { ?>
                                <div class="pull-left mt pr-lg mb" style="width:100px; margin-right: 12px; position: relative;">
                                    <span data-dz-remove class="pull-right existing_image" style="cursor:pointer; position: absolute; right: 5px; top: 0;">
                                        <i class="fa fa-times text-danger"></i>
                                    </span>

                                    <?php if (!empty($v_files_image->is_image)) { ?>
                                        <img src="<?= base_url($v_files_image->path) ?>" class="upload-thumbnail-sm" style="max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" />
                                    <?php } else { ?>
                                        <span class="mailbox-attachment-icon" style="font-size: 28px; display: block; text-align: center;">
                                            <i class="fa fa-file-pdf-o text-danger"></i>
                                        </span>
                                        <div style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= html_escape($v_files_image->fileName) ?></div>
                                    <?php } ?>

                                    <input type="hidden" name="path[]" value="<?= $v_files_image->path ?>">
                                    <input type="hidden" name="fileName[]" value="<?= $v_files_image->fileName ?>">
                                    <input type="hidden" name="fullPath[]" value="<?= $v_files_image->fullPath ?>">
                                    <input type="hidden" name="size[]" value="<?= $v_files_image->size ?>">
                                    <input type="hidden" name="is_image[]" value="<?= $v_files_image->is_image ?>">
                                </div>
                        <?php }
                            echo '</div>';
                        } ?>

                        <script type="text/javascript">
                            $(document).ready(function() {
                                $(".existing_image").on("click", function() {
                                    $(this).parent().remove();
                                });

                                // Instant preview for direct file upload input
                                $("#completion_files_input").on("change", function(e) {
                                    var files = e.target.files;
                                    var container = $("#manual_preview_box");
                                    container.empty();
                                    if (files && files.length > 0) {
                                        for (var i = 0; i < files.length; i++) {
                                            var file = files[i];
                                            var isImg = file.type.match('image.*') || /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(file.name);
                                            if (isImg) {
                                                var imgUrl = URL.createObjectURL(file);
                                                container.append(
                                                    '<div class="pull-left mr-sm mb-sm" style="margin: 5px; text-align: center;">' +
                                                    '<img src="' + imgUrl + '" style="width: 70px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;"><br>' +
                                                    '<small style="font-size: 10px; max-width: 70px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + file.name + '</small>' +
                                                    '</div>'
                                                );
                                            } else {
                                                container.append(
                                                    '<div class="pull-left mr-sm mb-sm" style="margin: 5px; text-align: center; width: 70px;">' +
                                                    '<i class="fa fa-file-pdf-o text-danger" style="font-size: 36px;"></i><br>' +
                                                    '<small style="font-size: 10px; max-width: 70px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + file.name + '</small>' +
                                                    '</div>'
                                                );
                                            }
                                        }
                                    }
                                });

                                var fileSerial = 0;
                                var previewNode = document.querySelector("#file-upload-row");
                                if (previewNode) {
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
                                        dictDefaultMessage: 'Drag image / pdf files to upload (or click)',
                                        autoQueue: true,
                                        previewsContainer: "#comments_file-previews",
                                        clickable: true,
                                        acceptedFiles: ".jpg,.jpeg,.png,.gif,.webp,.bmp,.pdf,image/*,application/pdf",
                                        accept: function(file, done) {
                                            if (file.name.length > 200) {
                                                done("Filename is too long.");
                                                return;
                                            }
                                            $.ajax({
                                                url: "<?= base_url() ?>admin/common/validate_project_file",
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
                                                        var targetNode = file.previewElement || file.previewTemplate;
                                                        $(targetNode).append(
                                                            "<input type='hidden' name='file_name_" + fileSerial + "' value='" + $('<div/>').text(file.name).html() + "' />" +
                                                            "<input type='hidden' name='file_size_" + fileSerial + "' value='" + file.size + "' />"
                                                        );
                                                        $(targetNode).find(".file-count-field").val(fileSerial);
                                                        done();
                                                    } else {
                                                        // Check if it's standard image/pdf extension
                                                        var ext = file.name.split('.').pop().toLowerCase();
                                                        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'].indexOf(ext) !== -1) {
                                                            fileSerial++;
                                                            var targetNode = file.previewElement || file.previewTemplate;
                                                            $(targetNode).append(
                                                                "<input type='hidden' name='file_name_" + fileSerial + "' value='" + $('<div/>').text(file.name).html() + "' />" +
                                                                "<input type='hidden' name='file_size_" + fileSerial + "' value='" + file.size + "' />"
                                                            );
                                                            $(targetNode).find(".file-count-field").val(fileSerial);
                                                            done();
                                                        } else {
                                                            $(file.previewElement || file.previewTemplate).find("input").remove();
                                                            done(response.message);
                                                        }
                                                    }
                                                },
                                                error: function() {
                                                    // On AJAX error, still allow standard image/pdf files
                                                    var ext = file.name.split('.').pop().toLowerCase();
                                                    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'].indexOf(ext) !== -1) {
                                                        fileSerial++;
                                                        var targetNode = file.previewElement || file.previewTemplate;
                                                        $(targetNode).append(
                                                            "<input type='hidden' name='file_name_" + fileSerial + "' value='" + $('<div/>').text(file.name).html() + "' />" +
                                                            "<input type='hidden' name='file_size_" + fileSerial + "' value='" + file.size + "' />"
                                                        );
                                                        $(targetNode).find(".file-count-field").val(fileSerial);
                                                        done();
                                                    } else {
                                                        done("Error validating file.");
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
                                        success: function(file) {
                                            setTimeout(function() {
                                                $(file.previewElement).find(".progress-striped").removeClass("progress-striped").addClass("progress-bar-success");
                                            }, 800);
                                        }
                                    });

                                    // Prevent submitting while dropzone is uploading
                                    $('#completionFileForm').on('submit', function(e) {
                                        if (projectFilesDropzone.getUploadingFiles && projectFilesDropzone.getUploadingFiles().length > 0) {
                                            e.preventDefault();
                                            alert('Please wait for file upload to complete.');
                                            return false;
                                        }
                                    });
                                }
                            });
                        </script>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <div class="col-sm-offset-3 col-sm-7 text-right">
                        <button class="btn btn-primary" id="file-save-button" type="submit"><?= lang('save') ?></button>
                        <?php if (!empty($completion_info)) { ?>
                            <a href="<?= base_url('admin/client/details/' . $client_details->client_id . '/completion_file') ?>" class="btn btn-default">Cancel</a>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>

        <script>
            $(document).ready(function() {
                $('#toggle_completion_form').click(function() {
                    $('.completion_form_box').slideToggle('fast');
                });
            });
        </script>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Image / File</th>
                        <th class="col-sm-2"><?= lang('date') ?></th>
                        <th class="col-sm-1"><?= lang('Status') ?></th>
                        <th class="col-sm-1"><?= lang('action') ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (!empty($completion_files)) {
                        foreach ($completion_files as $file_item) {
                    ?>
                            <tr>
                                <td><strong><?= html_escape($file_item->file_name) ?></strong></td>

                                <td>
                                    <?php
                                    $files = json_decode($file_item->upload_file);
                                    if (!empty($files) && is_array($files)) {
                                        foreach ($files as $file) {
                                            $file_url = base_url($file->path);
                                            $file_ext = strtolower(pathinfo($file->path, PATHINFO_EXTENSION));

                                            if (!empty($file->is_image) || in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                                                echo '<img src="' . $file_url . '" width="60" height="60" style="object-fit:cover; border-radius:4px; margin-right:8px; cursor:pointer; border:1px solid #ddd;" class="preview-completion-file" data-path="' . $file_url . '" data-type="image" title="' . html_escape($file->fileName) . '">';
                                            } elseif ($file_ext === 'pdf') {
                                                echo '<a href="javascript:void(0)" class="btn btn-xs btn-danger preview-completion-file" data-path="' . $file_url . '" data-type="pdf" style="margin-right:5px;"><i class="fa fa-file-pdf-o"></i> ' . html_escape($file->fileName) . '</a>';
                                            } else {
                                                echo '<a href="' . $file_url . '" target="_blank" class="btn btn-xs btn-info" style="margin-right:5px;"><i class="fa fa-download"></i> ' . html_escape($file->fileName) . '</a>';
                                            }
                                        }
                                    } else {
                                        echo '<span class="text-muted">No Attachment</span>';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?= !empty($file_item->created_date) ? date('d-m-Y h:i A', strtotime($file_item->created_date)) : '-' ?>
                                </td>

                                <td>
                                    <span class="label label-<?= ($file_item->status == 'Approved' || empty($file_item->status)) ? 'success' : 'warning' ?>">
                                        <?= !empty($file_item->status) ? $file_item->status : 'Approved' ?>
                                    </span>
                                </td>

                                <td>
                                    <?= btn_edit('admin/client/details/' . $client_details->client_id . '/completion_file/' . $file_item->id) ?>
                                    <a href="<?= base_url('admin/client/delete_completion_file/' . $file_item->id . '/' . $client_details->client_id) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this completion file?');" title="<?= lang('delete') ?>">
                                        <i class="fa fa-trash-o"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted" style="padding: 20px;">
                                No completion files uploaded yet.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Completion File Preview Modal -->
<div class="modal fade" id="completionPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">File Preview</h4>
            </div>
            <div class="modal-body text-center" id="completionPreviewContent" style="min-height: 250px;">
                <!-- Dynamic content will be injected here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.preview-completion-file', function() {
    var filePath = $(this).data('path');
    var fileType = $(this).data('type');
    var html = '';

    if (fileType === 'image') {
        html = '<img src="' + filePath + '" class="img-responsive center-block" style="max-height:75vh; border-radius:4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">';
    } else if (fileType === 'pdf' || filePath.toLowerCase().endsWith('.pdf')) {
        html = '<iframe src="' + filePath + '" width="100%" height="600px" style="border:none;"></iframe>';
    } else {
        html = '<div style="padding: 40px;"><a href="' + filePath + '" target="_blank" class="btn btn-primary btn-lg"><i class="fa fa-download"></i> Download File</a></div>';
    }

    $('#completionPreviewContent').html(html);
    $('#completionPreviewModal').modal('show');
});
</script>
