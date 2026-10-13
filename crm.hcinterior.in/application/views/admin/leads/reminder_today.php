<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>

<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('55', 'created');
$edited = can_action('55', 'edited');
$deleted = can_action('55', 'deleted');
$kanban = $this->session->userdata('leads_kanban');
$uri_segment = $this->uri->segment(4);
if (!empty($kanban)) {
    $k_leads = 'kanban';
} elseif ($uri_segment == 'kanban') {
    $k_leads = 'kanban';
} else {
    $k_leads = 'list';
}

if ($k_leads == 'kanban') {
    $text = 'list';
    $btn = 'purple';
} else {
    $text = 'kanban';
    $btn = 'danger';
}
?>

<?php
$user_id = $this->session->userdata('user_id');
$this->db->where('user_id', $user_id);
$current_user = $this->db->get('tbl_account_details')->row();

$is_team_leader = !empty($current_user) && $current_user->team_leader == 1;
$is_regional_manager = !empty($current_user) && $current_user->regional_manager == 1;
$is_admin = $this->session->userdata('user_type') == 1;
?>


<div class="row">
    <div class="col-sm-12">


        <?php if (!empty($created) || !empty($edited)) {
            ?>
        <?php $is_department_head = is_department_head();
         if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>



        <?php } ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads/reminder_today') ?>"><?= lang('Today Reminder') ?></a>
                </li>
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads/reminder_list') ?>"><?= lang('All Reminder') ?></a>
                </li>

            </ul>
            <div class="tab-content bg-white">
                <!-- ************** general *************-->
                <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
                    <?php } else { ?>
                    <div class="panel panel-custom">
                        <header class="panel-heading ">
                            <div class="panel-title"><strong><?= lang('all_leads') ?></strong></div>
                        </header>
                        <?php } ?>
                        <style type="text/css">
                        .custom-bulk-button {
                            display: initial;
                        }
                        </style>


                        <div class="table-responsive">
                            <table class="table table-striped dataTable DataTables bulk_table" cellspacing="0"
                                width="100%" cellpadding="5">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th>Date</th>


                                        <th>Lead </th>
                                        <!-- <th>User ID</th> -->
                                        <th>Notify by Email</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    <?php if (!empty($reminders)): ?>
                                    <?php foreach ($reminders as $key => $reminder): ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= htmlspecialchars($reminder->description) ?></td>
                                        <td><?= date('d-m-Y h:i A', strtotime($reminder->date)) ?></td>

                                        <td><a
                                                href="<?php echo base_url();?>admin/leads/leads_details/<?= $reminder->leads_id ?>"><?= $reminder->contact_name ?? 'N/A' ?>
                                                (<?= $reminder->phone ?? 'N/A' ?>)</a></td>


                                        <!-- <td><?= $reminder->user_id ?></td> -->
                                        <td><?= $reminder->notify_by_email ? 'Yes' : 'No' ?></td>
                                        <td><?= $reminder->fullname ?></td>
                                         <td>
 
     <strong data-toggle="tooltip" d  style="cursor:pointer"  onclick="confirmDelete(<?= $reminder->reminder_id ?>)"><i class="btn btn-xs btn-danger fa fa-trash-o"></i></strong>
 
</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="11">No reminders found.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>


                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/datetimepicker/jquery.datetimepicker.min.css">
<?php include_once 'assets/plugins/datetimepicker/jquery.datetimepicker.full.php'; ?>

   <script>
    function confirmDelete(reminderId) {
        if (confirm("Are you sure you want to delete this reminder?")) {
            window.location.href = "<?= base_url('admin/leads/reminder_delete/') ?>" + reminderId;
        }
    }
</script>