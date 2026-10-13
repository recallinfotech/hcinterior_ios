 
<?php  $user_id = $this->session->userdata('user_id');
$assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();    
?>
<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('Team') ?></strong>
        </div>
    </div>

    <div class="panel-body">

        <?php 
$roles = $this->db->where('status',1)->get('roles')->result();
?>

        <ul class="nav nav-tabs row">
            <?php foreach($roles as $index => $role): ?>
                <?php if($role->display == 1) { ?>
            <li class="<?= $index == 0 ? 'active' : '' ?>" style="width:25%">
                <a href="#role_<?= $role->id ?>" data-toggle="tab" data-profile="<?= $role->role_name ?>">
                    <i class="fa fa-users"></i>
                    <?= $role->role_name ?>
                </a>
            </li>
            <?php } ?>
            <?php endforeach; ?>
        </ul>

        <!-- ================= SHARED FORM ================= -->

        <?php if($assign_team->member_profile != 'Operation Team') { ?>
        <div style="margin-top:15px">
            <form action="<?= base_url('admin/client/new_team_assgin') ?>" method="post" class="form-horizontal">
                <input type="hidden" name="active_tab" id="active_tab" value="Project">

                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
               <input type="hidden" name="member_profile" id="member_profile" value="<?= !empty($roles) ? $roles[0]->role_name : '' ?>">

                <div class="form-group">
                    <div class="col-lg-1"> </div>
                    <label class="col-lg-2 control-label">
                        Select Department <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select class="form-control select_box" id="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach (get_result('tbl_departments') as $dept) { 
                if($dept->associate_with == '1') {
                
                ?>
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
                        <button class="btn btn-primary pull-right"><?= lang('save') ?></button>
                    </div>
                </div>
            </form>
        </div>

        <?php } ?>

        

            <div class="tab-content" style="margin-top:30px">

<?php foreach($roles as $index => $role): ?>

<div class="tab-pane fade <?= $index == 0 ? 'in active' : '' ?>" 
     id="role_<?= $role->id ?>">

<?php
$team_members = $this->db->where([
    'client_id' => $client_details->client_id,
    'member_profile' => $role->role_name
])->get('tbl_client_assign_team')->result();
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Designation</th>
            <th>Team Member</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php if(!empty($team_members)): ?>
        <?php foreach($team_members as $team): 
            $user = $this->db->where('user_id',$team->user_id)
                             ->get('tbl_account_details')->row();
            $designation = $this->db->where('designations_id',$team->designation)
                                    ->get('tbl_designations')->row();
        ?>
        <tr>
            <td><?= $designation->designations ?></td>
            <td><?= $user->fullname ?></td>
            <td><?= date('d-m-Y H:i', strtotime($team->created_date)) ?></td>
            <td>
                <?= btn_delete('admin/client/delete_team_member/'.$team->id.'/'.$client_details->client_id); ?>

               <button 
    class="btn btn-primary btn-xs assign_permission_btn"
    data-user_id="<?= $team->user_id ?>"
    data-client_id="<?= $client_details->client_id ?>"
    data-role_id="<?= $role->id ?>"
    data-profile="<?= $role->role_name ?>"
    data-name="<?= $user->fullname ?>"
    data-toggle="modal"
    data-target="#permissionModal">
    Assign Role & Permission
</button>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" class="text-center">No team member assigned</td>
        </tr>
    <?php endif; ?>

    </tbody>
</table>

</div>

<?php endforeach; ?>

</div>
 
<!-- <?php if(has_client_permission($client_details->client_id,'kt')): ?>
    <a class="btn btn-primary">KT Section</a>
<?php endif; ?> -->

    </div>
</section>


<script>
$(document).ready(function() {

    // Change profile on tab switch
    // $('.nav-tabs a').on('shown.bs.tab', function (e) {
    //     var profile = $(e.target).data('profile');
    //     $('#member_profile').val(profile);
    // });

    $('.nav-tabs a').on('shown.bs.tab', function(e) {

    var profile = $(e.target).data('profile');
    var tabId = $(e.target).attr('href').replace('#', '');

    $('#member_profile').val(profile);
    $('#active_tab').val(tabId);

});

});
</script>




<script>
$(document).ready(function() {
    var hash = window.location.hash;

    if (hash) {
        // Activate tab
        $('.nav-tabs a[href="' + hash + '"]').tab('show');

        // Fix: show tab content properly
        $('.tab-pane').removeClass('in active');
        $(hash).addClass('in active');
    }
});
</script>

<script>
$(document).ready(function() {

    // 1️⃣ Department → Designation
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
                        html += '<option value="' + v.designations_id + '">' + v
                            .designations + '</option>';
                    });
                    $('#designation').html(html);
                }
            });
        } else {
            $('#designation').html('<option value="">Select Designation</option>');
        }
    });

    // 2️⃣ Designation → User
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
                        html += '<option value="' + v.user_id + '">' + v.fullname +
                            '</option>';
                    });
                    $('#user_id').html(html);
                }
            });
        } else {
            $('#user_id').html('<option value="">Select Team Member</option>');
        }
    });

});



$(document).on('click', '.assign_permission_btn', function() {

    var role_id = $(this).data('role_id');
    var user_id = $(this).data('user_id');
    var client_id = $(this).data('client_id');
    var name = $(this).data('name');
    var profile = $(this).data('profile');

    $('#modal_user_id').val(user_id);
    $('#modal_client_id').val(client_id);
    $('#modal_profile').val(profile);   // ✅ IMPORTANT
    $('#modalUserName').text(name);

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
    }
});

});


</script>

 

<style>
.control-label {
    text-align: left !important;
}
</style>



<div class="modal fade" id="permissionModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    Assign Permissions - <span id="modalUserName"></span>
                </h4>
            </div>

            <form action="<?= base_url('admin/client/save_client_permissions') ?>" method="post">

                <div class="modal-body">

                   <input type="hidden" name="client_id" id="modal_client_id">
                    <input type="hidden" name="user_id" id="modal_user_id">
                    <input type="hidden" name="profile" id="modal_profile">
                    <div class="row" id="permission_container">
                        <!-- AJAX permissions load here -->
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        Save Permissions
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>



<style>
    .nav-tabs > li.active > a {
    background: #efab77 !important;
    border:none !important;
}

.nav-tabs>li {
    margin: 0px  !important;; 
    float: left  !important;;
    padding: 4px  !important;;
}
</style>