<?php  
$user_id = $this->session->userdata('user_id');
$assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();   
$user_type = $this->session->userdata('user_type'); 
$can_view = ($user_type == 1 || check_permission('assign_team_view')); 

// Get unique active roles configured for display
$raw_roles = $this->db->where('status', 1)->where('display', 1)->order_by('id', 'asc')->get('roles')->result();
$roles = [];
$seen = [];
foreach ($raw_roles as $r) {
    $r_name = trim($r->role_name);
    if (!isset($seen[$r_name])) {
        $r->role_name = $r_name;
        $seen[$r_name] = true;
        $roles[] = $r;
    }
}
$first_role = !empty($roles) ? $roles[0] : null;
$first_role_id = $first_role ? $first_role->id : 0;
$first_role_name = $first_role ? $first_role->role_name : '';
?>

<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><i class="fa fa-users"></i> <?= lang('Team') ?></strong>
        </div>
    </div>
 
    <div class="panel-body" style="<?php echo $can_view ? '' : 'display: none !important;'; ?>">

        <!-- Role Tabs -->
        <ul class="nav nav-tabs row" id="team_role_tabs">
            <?php foreach($roles as $index => $role): 
                $count = $this->db->where([
                    'client_id' => $client_details->client_id,
                    'member_profile' => $role->role_name
                ])->from('tbl_client_assign_team')->count_all_results();
            ?>
            <li class="<?= $index == 0 ? 'active' : '' ?>" style="width:25%">
                <a href="#role_<?= $role->id ?>" data-toggle="tab" data-role-id="<?= $role->id ?>" data-profile="<?= html_escape($role->role_name) ?>">
                    <i class="fa fa-users"></i>
                    <?= html_escape($role->role_name) ?>
                    <span class="badge pull-right" style="margin-top:2px; <?= $count > 0 ? 'background-color:#27c24c;' : 'background-color:#999;' ?>"><?= $count ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- ================= SHARED FORM ================= -->
        <?php if($user_type == 1 || check_permission('assign_team_add')) { ?>
        <div style="margin-top:15px">
            <div class="alert alert-info" style="margin-bottom:15px; border-left: 4px solid #23b7e5; padding: 10px 15px;">
                <i class="fa fa-info-circle"></i> Currently assigning member to: 
                <strong id="current_role_title" style="font-size: 15px; color: #1a7bb9;"><?= html_escape($first_role_name) ?></strong>
            </div>

            <form action="<?= base_url('admin/client/new_team_assgin') ?>" method="post" id="form_assign_team" class="form-horizontal">
                <input type="hidden" name="active_tab" id="active_tab" value="role_<?= $first_role_id ?>">
                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                <input type="hidden" name="member_profile" id="member_profile" value="<?= html_escape($first_role_name) ?>">

                <div class="form-group">
                    <div class="col-lg-1"> </div>
                    <label class="col-lg-2 control-label">
                        Select Department <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control select_box" id="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach (get_result('tbl_departments') as $dept) { 
                                if($dept->associate_with == '1') { ?>
                                    <option value="<?= $dept->departments_id ?>">
                                        <?= $dept->deptname ?>
                                    </option>
                            <?php }} ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-1"> </div>
                    <label class="col-lg-2 control-label">
                        Select Designation <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control select_box" name="designation" id="designation" required>
                            <option value="">Select Designation</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-1"> </div>
                    <label class="col-lg-2 control-label">
                        Select Team Member <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control select_box" name="user_id" id="user_id" required>
                            <option value="">Select Team Member</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-1"> </div>
                    <div class="col-lg-2"> </div>
                    <div class="col-lg-8">
                        <button type="submit" id="btn_save_team" class="btn btn-primary pull-right">
                            <i class="fa fa-save"></i> <?= lang('save') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php } ?>

        <!-- ================= TAB CONTENT & TABLES ================= -->
        <div class="tab-content" style="margin-top:25px">
            <?php foreach($roles as $index => $role): ?>
            <div class="tab-pane fade <?= $index == 0 ? 'in active' : '' ?>" id="role_<?= $role->id ?>">
                <?php
                $team_members = $this->db->where([
                    'client_id' => $client_details->client_id,
                    'member_profile' => $role->role_name
                ])->get('tbl_client_assign_team')->result();
                ?>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="active">
                                <th style="width: 25%">Designation</th>
                                <th style="width: 25%">Team Member</th>
                                <th style="width: 20%">Date</th>
                                <th style="width: 30%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($team_members)): ?>
                            <?php foreach($team_members as $team): 
                                $user = $this->db->where('user_id', $team->user_id)->get('tbl_account_details')->row();
                                $designation = $this->db->where('designations_id', $team->designation)->get('tbl_designations')->row();
                                $user_fullname = !empty($user) ? $user->fullname : 'Unknown User';
                                $desig_name = !empty($designation) ? $designation->designations : 'N/A';
                            ?>
                            <tr>
                                <td><?= html_escape($desig_name) ?></td>
                                <td><strong><?= html_escape($user_fullname) ?></strong></td>
                                <td><?= !empty($team->created_date) ? date('d-m-Y H:i', strtotime($team->created_date)) : '-' ?></td>
                                <td>
                                    <?= btn_delete('admin/client/delete_team_member/'.$team->id.'/'.$client_details->client_id); ?>
                                    <button 
                                        type="button"
                                        class="btn btn-primary btn-xs assign_permission_btn"
                                        data-user_id="<?= $team->user_id ?>"
                                        data-client_id="<?= $client_details->client_id ?>"
                                        data-role_id="<?= $role->id ?>"
                                        data-profile="<?= html_escape($role->role_name) ?>"
                                        data-name="<?= html_escape($user_fullname) ?>"
                                        data-toggle="modal"
                                        data-target="#permissionModal">
                                        <i class="fa fa-key"></i> Assign Role & Permission
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted" style="padding: 20px;">
                                    <i class="fa fa-info-circle"></i> No team member assigned for <strong><?= html_escape($role->role_name) ?></strong>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Permission Modal -->
<div class="modal fade" id="permissionModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-key"></i> Assign Permissions - <span id="modalUserName"></span>
                </h4>
            </div>

            <form action="<?= base_url('admin/client/save_client_permissions') ?>" method="post" id="form_save_permissions">
                <div class="modal-body">
                    <input type="hidden" name="client_id" id="modal_client_id">
                    <input type="hidden" name="user_id" id="modal_user_id">
                    <input type="hidden" name="profile" id="modal_profile">
                    <div class="row" id="permission_container">
                        <!-- AJAX permissions load here -->
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="btn_save_permissions" class="btn btn-success">
                        <i class="fa fa-check"></i> Save Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.control-label {
    text-align: left !important;
}

.nav-tabs > li.active > a {
    background: #efab77 !important;
    color: #fff !important;
    border: none !important;
    font-weight: bold;
}

.nav-tabs > li {
    margin: 0px !important; 
    float: left !important;
    padding: 4px !important;
}

.nav-tabs > li > a .badge {
    margin-left: 5px;
    font-size: 11px;
}

.nav-tabs > li.active > a .badge {
    background-color: #fff !important;
    color: #d26a1b !important;
    font-weight: bold;
}
</style>

<script>
$(document).ready(function() {

    // Helper: update active tab tracking and role banner
    function setRoleActive(tabId, profile) {
        if (!tabId || !profile) return;
        $('#member_profile').val(profile);
        $('#active_tab').val(tabId);
        $('#current_role_title').text(profile);
    }

    // Function to activate tab by hash
    function activateRoleTab(targetHash) {
        if (!targetHash) return;
        var cleanHash = targetHash.indexOf('#') === 0 ? targetHash : '#' + targetHash;
        var $link = $('#team_role_tabs a[href="' + cleanHash + '"]');
        if ($link.length > 0) {
            $link.tab('show');
            var profile = $link.data('profile');
            var tabId = cleanHash.replace('#', '');
            setRoleActive(tabId, profile);
            $('.tab-pane').removeClass('in active');
            $(cleanHash).addClass('in active');
        }
    }

    // Tab switch listener
    $('#team_role_tabs a').on('shown.bs.tab', function(e) {
        var profile = $(this).data('profile');
        var href = $(this).attr('href');
        var tabId = href ? href.replace('#', '') : '';
        setRoleActive(tabId, profile);

        if (window.history && window.history.replaceState && href) {
            window.history.replaceState(null, null, href);
        }
    });

    // Check URL hash on initial load
    var hash = window.location.hash;
    if (hash && $('#team_role_tabs a[href="' + hash + '"]').length > 0) {
        activateRoleTab(hash);
    } else {
        var $firstTab = $('#team_role_tabs a:first');
        if ($firstTab.length > 0) {
            var firstProfile = $firstTab.data('profile');
            var firstTabId = $firstTab.attr('href').replace('#', '');
            setRoleActive(firstTabId, firstProfile);
        }
    }

    // Button processing state & duplicate submit prevention
    $('#form_assign_team').on('submit', function(e) {
        var $btn = $('#btn_save_team');

        if ($btn.data('is_submitting') === true) {
            e.preventDefault();
            return false;
        }

        var dept = $('#department_id').val();
        var desig = $('#designation').val();
        var user = $('#user_id').val();

        if (!dept || !desig || !user) {
            return true; // Browser HTML5 validation handles missing fields
        }

        $btn.data('is_submitting', true);
        $btn.addClass('disabled').css('pointer-events', 'none');
        $btn.html('<i class="fa fa-spinner fa-spin"></i> <?= lang('processing') ?? 'Processing...' ?>');

        setTimeout(function() {
            $btn.prop('disabled', true);
        }, 100);

        return true;
    });

    // Modal submit processing
    $('#form_save_permissions').on('submit', function() {
        var $btn = $('#btn_save_permissions');
        $btn.addClass('disabled').css('pointer-events', 'none');
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        setTimeout(function() {
            $btn.prop('disabled', true);
        }, 100);
        return true;
    });

    // 1️⃣ Department -> Designation AJAX
    $('#department_id').change(function() {
        var department_id = $(this).val();

        $('#designation').html('<option value="">Loading...</option>');
        $('#user_id').html('<option value="">Select Team Member</option>');

        if (department_id) {
            $.ajax({
                url: "<?= base_url('admin/client/get_designations_by_department') ?>",
                type: "POST",
                data: {
                    department_id: department_id
                },
                dataType: "json",
                success: function(res) {
                    var html = '<option value="">Select Designation</option>';
                    $.each(res, function(i, v) {
                        html += '<option value="' + v.designations_id + '">' + v.designations + '</option>';
                    });
                    $('#designation').html(html);
                }
            });
        } else {
            $('#designation').html('<option value="">Select Designation</option>');
        }
    });

    // 2️⃣ Designation -> User AJAX
    $('#designation').change(function() {
        var designation_id = $(this).val();

        $('#user_id').html('<option value="">Loading...</option>');

        if (designation_id) {
            $.ajax({
                url: "<?= base_url('admin/client/get_users_by_designation') ?>",
                type: "POST",
                data: {
                    designation_id: designation_id
                },
                dataType: "json",
                success: function(res) {
                    var html = '<option value="">Select Team Member</option>';
                    $.each(res, function(i, v) {
                        html += '<option value="' + v.user_id + '">' + v.fullname + '</option>';
                    });
                    $('#user_id').html(html);
                }
            });
        } else {
            $('#user_id').html('<option value="">Select Team Member</option>');
        }
    });

    // 3️⃣ Open Permissions Modal
    $(document).on('click', '.assign_permission_btn', function() {
        var role_id = $(this).data('role_id');
        var user_id = $(this).data('user_id');
        var client_id = $(this).data('client_id');
        var name = $(this).data('name');
        var profile = $(this).data('profile');

        $('#modal_user_id').val(user_id);
        $('#modal_client_id').val(client_id);
        $('#modal_profile').val(profile);
        $('#modalUserName').text(name);

        $('#permission_container').html('<div class="col-xs-12 text-center" style="padding:20px;"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading permissions...</div>');

        $.ajax({
            url: "<?= base_url('admin/client/get_role_permissions') ?>",
            type: "POST",
            data: { 
                role_id: role_id,
                client_id: client_id,
                user_id: user_id
            },
            success: function(response) {
                $('#permission_container').html(response);
            },
            error: function() {
                $('#permission_container').html('<div class="col-xs-12 alert alert-danger">Failed to load permissions.</div>');
            }
        });
    });

});
</script>