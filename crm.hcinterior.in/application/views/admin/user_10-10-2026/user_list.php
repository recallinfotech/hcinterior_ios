<?php include_once 'assets/admin-ajax.php'; ?>

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