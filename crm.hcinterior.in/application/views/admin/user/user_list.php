<?php include_once 'assets/admin-ajax.php'; ?>

<style>
.region-dropdown-menu {
    min-width: 185px;
    padding: 10px 14px;
    z-index: 99999 !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.18);
    border: 1px solid #d2d6de;
    border-radius: 4px;
    background-color: #fff;
}
.region-dropdown-menu label {
    font-size: 13px;
    cursor: pointer;
    margin-bottom: 5px;
    display: block;
    user-select: none;
}
.region-dropdown-menu input[type="checkbox"] {
    margin-right: 6px;
    vertical-align: middle;
}
.user-region-display {
    display: flex;
    align-items: center;
    gap: 3px;
    flex-wrap: wrap;
}
.user-region-display .label {
    cursor: pointer;
}
.user-region-editor .input-group-btn {
    vertical-align: top;
}
</style>

<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('24', 'created');
$edited = can_action('24', 'edited');
$deleted = can_action('24', 'deleted');
if (!empty($created) || !empty($edited)) {
?>
    <?php $is_department_head = is_department_head();
    if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>
        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip" data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-right group animated zoomIn" style="width:300px;">
                <li class="filter_by"><a href="#"><?php echo lang('all'); ?></a></li>
                <li class="divider"></li>

                <li class="filter_by" id="admin"><a href="#"><?php echo lang('admin'); ?></a></li>
                <li class="filter_by" id="client"><a href="#"><?php echo lang('client'); ?></a></li>
                <li class="filter_by" id="staff"><a href="#"><?php echo lang('staff'); ?></a></li>
                <li class="filter_by" id="active"><a href="#"><?php echo lang('active'); ?></a></li>
                <li class="filter_by" id="deactive"><a href="#"><?php echo lang('deactive'); ?></a></li>
                <li class="filter_by" id="banned"><a href="#"><?php echo lang('banned'); ?></a></li>
                <div class="clearfix"></div>
            </ul>
        </div>
    <?php } ?>
    <div class="nav-tabs-custom">
        <!-- Tabs within a box -->
        <ul class="nav nav-tabs">
            <li class="<?= $active == 1 ? 'active' : ''; ?>"><a href="<?= base_url('admin/user/user_list') ?>"><?= lang('all_users') ?></a></li>
            <li class="<?= $active == 2 ? 'active' : ''; ?>"><a href="<?= base_url('admin/user/create') ?>"><?= lang('new_user') ?></a>
            </li>
            <li class="<?= $active == 3 ? 'active' : ''; ?>"><a href="<?= base_url('admin/operationpermission') ?>"><?= lang('Operation Permission') ?></a>
            </li>
        </ul>
        <div class="tab-content bg-white">
            <!-- ************** general *************-->
            <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
            <?php } else { ?>
                <div class="panel panel-custom">
                    <header class="panel-heading ">
                        <div class="panel-title"><strong><?= lang('all_users') ?></strong></div>
                    </header>
                <?php } ?>
                <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="col-sm-1"><?= lang('photo') ?></th>
                            <th><?= lang('name') ?></th>
                            <th class="col-sm-2">Emp Code</th>
                            <th class="col-sm-2">User Region</th>
                            <th class="col-sm-2"><?= lang('username') ?></th>
                            <th class="col-sm-1"><?= lang('active') ?></th>
                            <th class="col-sm-1"><?= lang('user_type') ?></th>
                            <th class="col-sm-2">Operation Role</th>
                            <?php $show_custom_fields = custom_form_table(13, null);
                            if (!empty($show_custom_fields)) {
                                foreach ($show_custom_fields as $c_label => $v_fields) {
                                    if (!empty($c_label)) {
                            ?>
                                        <th><?= $c_label ?> </th>
                            <?php }
                                }
                            }
                            ?>
                            <th class="col-sm-2"><?= lang('action') ?></th>

                        </tr>
                    </thead>
                    <tbody>
                        <script type="text/javascript">
                            $(document).ready(function() {
                                list = base_url + "admin/user/userList";
                                $('.filtered > .dropdown-toggle').on('click', function() {
                                    if ($('.group').css('display') == 'block') {
                                        $('.group').css('display', 'none');
                                    } else {
                                        $('.group').css('display', 'block')
                                    }
                                });
                                $('.filter_by').on('click', function() {
                                    $('.filter_by').removeClass('active');
                                    $('.group').css('display', 'block');
                                    $(this).addClass('active');
                                    var filter_by = $(this).attr('id');
                                    if (filter_by) {
                                        filter_by = filter_by;
                                    } else {
                                        filter_by = '';
                                    }
                                    table_url(base_url + "admin/user/userList/" + filter_by);
                                });
                            });
                        </script>
                    </tbody>
                </table>
                </div>
        </div>
    </div>

    <script>
        $(document).on("click", '.change_user_status input[type="checkbox"]', function() {
            var user_id = $(this).data().id;
            var status = $(this).is(":checked");
            if (status == true) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                url: '<?= base_url() ?>admin/user/change_status/' + status + '/' +
                    user_id, // the url where we want to POST
                dataType: 'json', // what type of data do we expect back from the server
                encode: true,
                success: function(res) {
                    console.log(res);
                    if (res) {
                        toastr[res.status](res.message);
                    } else {
                        alert('There was a problem with AJAX');
                    }
                }
            })
        });
    </script>
    
    <!-- Bootstrap Modal for Operation Role Assignment -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" role="dialog" aria-labelledby="assignRoleModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="assignRoleModalLabel">Assign Operation Role</h4>
            </div>
            <form id="assignRoleForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="modal_user_id">
                    
                    <div class="form-group">
                        <label>User Name:</label>
                        <p id="modal_user_name" class="form-control-static bold"></p>
                    </div>

                    <div class="form-group">
                        <label for="modal_operation_role_id">Select Operation Role:</label>
                        <select class="form-control" name="operation_role_id" id="modal_operation_role_id">
                            <option value="">-- No Role --</option>
                            <?php 
                                $hc_roles_list = $this->db->get('hc_roles')->result();
                                foreach ($hc_roles_list as $hr_item): 
                            ?>
                                <option value="<?= $hr_item->id; ?>"><?= html_escape($hr_item->role_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btn_save_role">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Logic Script -->
<script>
$(document).ready(function() {
    
    
    // Save Emp Code via AJAX and hide button on success
    $(document).on('click', '.save_emp_code_btn', function() {
        var btn = $(this);
        var userId = btn.attr('data-user-id');
        var inputField = $('#emp_code_' + userId);
        var empCode = inputField.val().trim();

        if (!empCode) {
            toastr.error('Please enter an Employee Code before saving.');
            return false;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/user/update_emp_code',
            data: {
                user_id: userId,
                emp_code: empCode
            },
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    toastr.success(res.message);
                    var wrap = $('#emp_code_wrap_' + userId);
                    wrap.replaceWith('<span class="label label-info" style="font-size:13px;padding:6px 10px;display:inline-block;" id="emp_code_span_' + userId + '">' + empCode + '</span>');
                } else {
                    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                    toastr.error(res ? res.message : 'Error updating Emp Code');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                toastr.error('Server error occurred');
            }
        });
    });

    // Prevent dropdown menu from closing on click inside
    $(document).on('click', '.region-dropdown-menu', function(e) {
        e.stopPropagation();
    });

    // Helper to update dropdown button text based on checked checkboxes
    function updateRegionBtnText(userId) {
        var total = $('.user_region_cb[data-user-id="' + userId + '"]').length;
        var checkedVals = [];
        $('.user_region_cb[data-user-id="' + userId + '"]:checked').each(function() {
            checkedVals.push($(this).val());
        });

        var btnText = 'Select Region';
        if (checkedVals.length > 0) {
            if (checkedVals.length === total) {
                btnText = 'All Regions';
            } else {
                btnText = checkedVals.join(', ');
            }
        }
        $('#region_display_text_' + userId).text(btnText);
    }

    // Toggle "All Regions" checkbox
    $(document).on('change', '.region_select_all', function() {
        var userId = $(this).data('user-id');
        var isChecked = $(this).prop('checked');
        $('.user_region_cb[data-user-id="' + userId + '"]').prop('checked', isChecked);
        updateRegionBtnText(userId);
    });

    // Individual checkbox change
    $(document).on('change', '.user_region_cb', function() {
        var userId = $(this).data('user-id');
        var total = $('.user_region_cb[data-user-id="' + userId + '"]').length;
        var checked = $('.user_region_cb[data-user-id="' + userId + '"]:checked').length;
        $('.region_select_all[data-user-id="' + userId + '"]').prop('checked', (total > 0 && total === checked));
        updateRegionBtnText(userId);
    });

    // Edit Region button clicked or badges clicked
    $(document).on('click', '.btn-edit-region, .user-region-badges', function() {
        var userId = $(this).data('user-id') || $(this).closest('.user-region-display').find('.btn-edit-region').data('user-id');
        if (!userId) return;

        $('#user_region_display_' + userId).hide();
        var editor = $('#user_region_editor_' + userId);
        editor.show();
        $('.cancel_user_region_btn[data-user-id="' + userId + '"]').show();

        // Open dropdown menu
        var btn = $('#user_region_btn_' + userId);
        if (!btn.closest('.btn-group').hasClass('open')) {
            btn.dropdown('toggle');
        }
    });

    // Cancel edit
    $(document).on('click', '.cancel_user_region_btn', function() {
        var userId = $(this).data('user-id');
        $('#user_region_editor_' + userId).hide();
        $('#user_region_display_' + userId).css('display', 'flex');
    });

    // Save User Region via AJAX
    $(document).on('click', '.save_user_region_btn', function(e) {
        e.stopPropagation();
        var btn = $(this);
        var userId = btn.data('user-id');
        var selectedRegions = [];
        $('.user_region_cb[data-user-id="' + userId + '"]:checked').each(function() {
            selectedRegions.push($(this).val());
        });

        if (selectedRegions.length === 0) {
            toastr.warning('Please select at least one region before saving.');
            return false;
        }

        var allSaveBtns = $('.save_user_region_btn[data-user-id="' + userId + '"]');
        allSaveBtns.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/user/update_user_region',
            data: {
                user_id: userId,
                user_region: selectedRegions
            },
            dataType: 'json',
            success: function(res) {
                allSaveBtns.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                if (res && res.status === 'success') {
                    toastr.success(res.message);

                    // Close dropdown menu
                    $('#user_region_btn_' + userId).closest('.btn-group').removeClass('open');

                    // Build badges html
                    var badgesHtml = '';
                    if (res.user_regions && res.user_regions.length > 0) {
                        $.each(res.user_regions, function(idx, reg) {
                            badgesHtml += '<span class="label label-warning" style="font-size:12px;padding:4px 7px;margin:1px;display:inline-block;">' + reg + '</span> ';
                        });
                    }
                    $('#user_region_badges_' + userId).html(badgesHtml);

                    // Update button label text
                    var total = $('.user_region_cb[data-user-id="' + userId + '"]').length;
                    var labelText = (res.user_regions && res.user_regions.length === total) ? 'All Regions' : (res.user_regions ? res.user_regions.join(', ') : 'Select Region');
                    $('#region_display_text_' + userId).text(labelText);

                    // Switch view to display mode
                    $('#user_region_editor_' + userId).hide();
                    $('#user_region_display_' + userId).css('display', 'flex');
                    $('.cancel_user_region_btn[data-user-id="' + userId + '"]').show();
                } else {
                    toastr.error(res ? res.message : 'Error updating User Region');
                }
            },
            error: function() {
                allSaveBtns.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                toastr.error('Server error occurred');
            }
        });
    });

    // Open Modal
    $(document).on('click', '.open_role_modal', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        var roleId = $(this).data('role-id');

        $('#modal_user_id').val(userId);
        $('#modal_user_name').text(userName);
        $('#modal_operation_role_id').val(roleId ? roleId : '');

        $('#assignRoleModal').modal('show');
    });

    // Save Role via AJAX
    $('#assignRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        var userId = $('#modal_user_id').val();
        var roleId = $('#modal_operation_role_id').val();
        var selectedRoleText = $('#modal_operation_role_id option:selected').text();

        $('#btn_save_role').prop('disabled', true).text('Saving...');

        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/user/update_operation_role',
            data: {
                user_id: userId,
                operation_role_id: roleId
            },
            dataType: 'json',
            success: function(res) {
                $('#btn_save_role').prop('disabled', false).text('Save Changes');
                if (res && res.status === 'success') {
                    $('#assignRoleModal').modal('hide');
                    toastr.success(res.message);

                    // Update UI Label Live
                    var labelText = roleId ? selectedRoleText : 'Not Assigned';
                    var labelObj = $('#role_label_' + userId);
                    labelObj.text(labelText);

                    if (roleId) {
                        labelObj.removeClass('label-default').addClass('label-info');
                    } else {
                        labelObj.removeClass('label-info').addClass('label-default');
                    }

                    // Refresh Datatable if needed
                    if (typeof list !== 'undefined') {
                        // table_url(list);
                    }
                } else {
                    toastr.error(res ? res.message : 'Error updating role');
                }
            },
            error: function() {
                $('#btn_save_role').prop('disabled', false).text('Save Changes');
                toastr.error('Server connection error!');
            }
        });
    });

});
</script>