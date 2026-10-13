<section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong><?= lang('Team') ?></strong>
        </div>
    </div>

    <div class="panel-body">

        <!-- Tabs -->
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#Project" data-toggle="tab" data-profile="Project Coordinator">
                    <i class="fa fa-pencil"></i> Project Coordinator
                </a>
            </li>
            <li>
                <a href="#Interior" data-toggle="tab" data-profile="Interior Designer">
                    <i class="fa fa-building"></i> Interior Designer
                </a>
            </li>
            <li>
                <a href="#Operation" data-toggle="tab" data-profile="Operation Team">
                    <i class="fa fa-cubes"></i> Operation Team
                </a>
            </li>
        </ul>

        <!-- ================= SHARED FORM ================= -->
        <div style="margin-top:15px">
            <form action="<?= base_url('admin/client/new_team_assgin') ?>"
                  method="post"
                  class="form-horizontal">

                <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                <input type="hidden" name="member_profile" id="member_profile" value="Project Coordinator">

                <div class="form-group">
                     <div class="col-lg-1">  </div>
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
     <div class="col-lg-1">  </div>
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
                 <div class="col-lg-1">  </div>
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
     <div class="col-lg-1">  </div>
 <div class="col-lg-2">  </div>
 <div class="col-lg-8">
                <button class="btn btn-primary pull-right"><?= lang('save') ?></button>
                </div>
</div>
            </form>
        </div>

        <!-- ================= TAB CONTENT ================= -->
        <div class="tab-content" style="margin-top:30px">

            <!-- PROJECT -->
            <div class="tab-pane fade in active" id="Project">
                <?php
                $project_team = get_result('tbl_client_assign_team', [
                    'client_id' => $client_details->client_id,
                    'member_profile' => 'Project Coordinator'
                ]);
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
    <?php if (!empty($project_team)) {
        foreach ($project_team as $team) {
            $user = $this->db->where('user_id', $team->user_id)->get('tbl_account_details')->row();
            $designation = $this->db->where('designations_id', $team->designation)->get('tbl_designations')->row();
            ?>
            <tr>
                <td><?= $designation->designations ?></td>
                <td><?= $user->fullname ?></td>
                <td><?= date('d-m-Y H:i', strtotime($team->created_date)) ?></td>
                <td>
                    <?= btn_delete('admin/client/delete_team_member/' . $team->id . '/' . $client_details->client_id); ?>
                </td>
            </tr>
        <?php }
    } else { ?>
        <tr>
            <td colspan="4" class="text-center">No team member assigned</td>
        </tr>
    <?php } ?>
    </tbody>
</table>


            </div>

            <!-- INTERIOR -->
            <div class="tab-pane fade" id="Interior">
                <?php
                $interior_team = get_result('tbl_client_assign_team', [
                    'client_id' => $client_details->client_id,
                    'member_profile' => 'Interior Designer'
                ]);
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
    <?php if (!empty($interior_team)) {
        foreach ($interior_team as $team) {
            $user = $this->db->where('user_id', $team->user_id)->get('tbl_account_details')->row();
            $designation = $this->db->where('designations_id', $team->designation)->get('tbl_designations')->row();
            ?>
            <tr>
                <td><?= $designation->designations ?></td>
                <td><?= $user->fullname ?></td>
                <td><?= date('d-m-Y H:i', strtotime($team->created_date)) ?></td>
                <td>
                    <?= btn_delete('admin/client/delete_team_member/' . $team->id . '/' . $client_details->client_id); ?>
                </td>
            </tr>
        <?php }
    } else { ?>
        <tr>
            <td colspan="4" class="text-center">No team member assigned</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

            </div>

            <!-- OPERATION -->
            <div class="tab-pane fade" id="Operation">
                <?php
                $operation_team = get_result('tbl_client_assign_team', [
                    'client_id' => $client_details->client_id,
                    'member_profile' => 'Operation Team'
                ]);
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
    <?php if (!empty($operation_team)) {
        foreach ($operation_team as $team) {
            $user = $this->db->where('user_id', $team->user_id)->get('tbl_account_details')->row();
            $designation = $this->db->where('designations_id', $team->designation)->get('tbl_designations')->row();
            ?>
            <tr>
                <td><?= $designation->designations ?></td>
                <td><?= $user->fullname ?></td>
                <td><?= date('d-m-Y H:i', strtotime($team->created_date)) ?></td>
                <td>
                    <?= btn_delete('admin/client/delete_team_member/' . $team->id . '/' . $client_details->client_id); ?>
                </td>
            </tr>
        <?php }
    } else { ?>
        <tr>
            <td colspan="4" class="text-center">No team member assigned</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
            </div>

        </div>
    </div>
</section>


<script>
$(document).ready(function () {

    // Change profile on tab switch
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        var profile = $(e.target).data('profile');
        $('#member_profile').val(profile);
    });

   

});
</script>

<script>
$(document).ready(function () {

    // 1️⃣ Department → Designation
    $('#department_id').change(function () {
        var department_id = $(this).val();

        $('#designation').html('<option value="">Loading...</option>');
        $('#user_id').html('<option value="">Select Team Member</option>');

        if (department_id) {
            $.ajax({
                url: "<?= base_url('admin/client/get_designations_by_department') ?>",
                type: "POST",
                data: { department_id: department_id },
                dataType: "json",
                success: function (res) {
                    var html = '<option value="">Select Designation</option>';
                    $.each(res, function (i, v) {
                        html += '<option value="' + v.designations_id + '">' + v.designations + '</option>';
                    });
                    $('#designation').html(html);
                }
            });
        } else {
            $('#designation').html('<option value="">Select Designation</option>');
        }
    });

    // 2️⃣ Designation → User
    $('#designation').change(function () {
        var designation_id = $(this).val();

        $('#user_id').html('<option value="">Loading...</option>');

        if (designation_id) {
            $.ajax({
                url: "<?= base_url('admin/client/get_users_by_designation') ?>",
                type: "POST",
                data: { designation_id: designation_id },
                dataType: "json",
                success: function (res) {
                    var html = '<option value="">Select Team Member</option>';
                    $.each(res, function (i, v) {
                        html += '<option value="' + v.user_id + '">' + v.fullname + '</option>';
                    });
                    $('#user_id').html(html);
                }
            });
        } else {
            $('#user_id').html('<option value="">Select Team Member</option>');
        }
    });

});
</script>


<style>
    .control-label {
        text-align:left !important;
    }
    </style>