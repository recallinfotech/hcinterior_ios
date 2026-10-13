<div class="container-fluid  mt-3">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-custom">
                <div class="panel-heading">
                    <h4 class="panel-title">Operation Permission Matrix</h4>
                </div>
                <div class="panel-body">
                    
                    <!-- Alert Box -->
                    <div id="permission-alert" class="alert alert-success" style="display:none;"></div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th style="width: 250px; text-align: left;">Permission Name</th>
                                    <?php foreach ($roles as $role): ?>
                                        <th class="text-center" style="font-size: 11px;">
                                            <?php echo html_escape($role->role_name); ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permissions as $perm): ?>
                                    <tr>
                                        <!-- Permission Title -->
                                        <td>
                                            <strong><?php echo html_escape($perm->permission_name); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo html_escape($perm->permission_key); ?></small>
                                        </td>

                                        <!-- Roles Checkbox Grid -->
                                        <?php foreach ($roles as $role): ?>
                                            <?php 
                                                $isChecked = isset($mapped[$role->id][$perm->id]); 
                                            ?>
                                            <td class="text-center" style="vertical-align: middle;">
                                                <input 
                                                    type="checkbox" 
                                                    class="permission-checkbox" 
                                                    data-role-id="<?php echo $role->id; ?>" 
                                                    data-perm-id="<?php echo $perm->id; ?>"
                                                    <?php echo $isChecked ? 'checked' : ''; ?>
                                                    style="width: 18px; height: 18px; cursor: pointer;"
                                                >
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery AJAX Script -->
 <script type="text/javascript">
$(document).ready(function() {
    
    // Checkbox Change Event Listener
    $('.permission-checkbox').on('change', function() {
        var checkbox = $(this);
        var roleId = checkbox.data('role-id');
        var permId = checkbox.data('perm-id');
        var isStatus = checkbox.is(':checked') ? 1 : 0;

        checkbox.prop('disabled', true);

        $.ajax({
            // Updated URL according to OperationPermission Controller
            url: "<?php echo base_url('admin/operationpermission/update_permission'); ?>",
            type: "POST",
            data: {
                operation_role_id: roleId,
                permission_id: permId,
                status: isStatus
            },
            dataType: "json",
            success: function(response) {
                checkbox.prop('disabled', false);
                if(response.status) {
                    $('#permission-alert').removeClass('alert-danger').addClass('alert-success')
                        .text(response.message).fadeIn().delay(1500).fadeOut();
                } else {
                    checkbox.prop('checked', !isStatus);
                    $('#permission-alert').removeClass('alert-success').addClass('alert-danger')
                        .text(response.message).fadeIn().delay(1500).fadeOut();
                }
            },
            error: function() {
                checkbox.prop('disabled', false);
                checkbox.prop('checked', !isStatus);
                $('#permission-alert').removeClass('alert-success').addClass('alert-danger')
                    .text('Server Connection Error!').fadeIn().delay(1500).fadeOut();
            }
        });
    });

});
</script>