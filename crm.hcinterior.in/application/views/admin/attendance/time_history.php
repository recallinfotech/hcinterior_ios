<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<?php
$user_type = $this->session->userdata('user_type');
if ($user_type == '1') { ?>
<div class="row">
    <div class="col-sm-12" data-offset="0">
        <div class="panel panel-custom" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <strong><?= lang('view_time_history') ?></strong>
                </div>
            </div>
            <div class="panel-body">
                <form id="attendance-form" role="form" enctype="multipart/form-data"
                    action="<?php echo base_url(); ?>admin/attendance/time_history" method="post"
                    class="form-horizontal form-groups-bordered">
                    <div class="form-group" id="border-none">
                        <label for="field-1" class="col-sm-3 control-label"><?= lang('employee') ?> <span
                                class="required">*</span></label>
                        <div class="col-sm-5">
                            <select name="user_id" style="width: 100%" id="employee" class="form-control select_box">
                                <option value=""><?= lang('select_employee') ?>...</option>
                                <?php if (!empty($all_employee)) : ?>
                                <?php foreach ($all_employee as $dept_name => $v_all_employee) : ?>
                                <optgroup label="<?php echo $dept_name; ?>">
                                    <?php if (!empty($v_all_employee)) : foreach ($v_all_employee as $v_employee) : ?>
                                    <option value="<?php echo $v_employee->user_id; ?>" <?php
                                                                                                            if (!empty($user_id)) {
                                                                                                                $user_id = $user_id;
                                                                                                            } else {
                                                                                                                $user_id = $this->session->userdata('user_id');
                                                                                                            }
                                                                                                            if (!empty($user_id)) {
                                                                                                                echo $v_employee->user_id == $user_id ? 'selected' : '';
                                                                                                            }
                                                                                                            ?>>
                                        <?php echo $v_employee->employment_id . '- ' . $v_employee->fullname . ' ( ' . $v_employee->designations . ' )' ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </optgroup>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-sm-2 ">
                            <button type="submit" name="search" value="1"
                                class="btn btn-primary"><?= lang('go') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php }
if (!empty($user_id)) {
    $user_id = $user_id;
} else {
    $user_id = $this->session->userdata('user_id');
}
$user_info = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
if (!empty($user_info->fullname)) {
    $name = $user_info->fullname;
} else {
    $name = lang('my');
}
$admin = admin();
?>

<link href="<?php echo base_url() ?>assets/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css">

<style>
.att-kpi-container {
    margin-bottom: 18px;
}
.att-card {
    border-radius: 8px;
    padding: 12px 16px;
    color: #ffffff;
    margin-bottom: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.25s ease;
}
.att-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}
.att-card .att-count {
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
}
.att-card .att-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 600;
    opacity: 0.95;
}
.att-card .att-icon {
    font-size: 28px;
    opacity: 0.35;
}
.card-att-present { background: linear-gradient(135deg, #11998e, #38ef7d); }
.card-att-pending { background: linear-gradient(135deg, #e67e22, #f39c12); }
.card-att-absent { background: linear-gradient(135deg, #eb3349, #f45c43); }
.card-att-leave { background: linear-gradient(135deg, #f2994a, #f2c94c); color: #222 !important; }
.card-att-leave .att-icon { color: #222; opacity: 0.35; }
.card-att-holiday { background: linear-gradient(135deg, #36d1dc, #5b86e5); }
.card-att-hours { background: linear-gradient(135deg, #2c3e50, #4ca1af); }

#attendance_calendar {
    background: #ffffff;
    padding: 15px;
    border-radius: 6px;
    min-height: 550px;
}
.fc-toolbar h2 {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #2c3e50 !important;
}
.fc-event {
    cursor: pointer;
    border-radius: 4px !important;
    padding: 4px 8px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border: none !important;
    margin-bottom: 3px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.fc-day:hover {
    background-color: #f0f7ff !important;
    cursor: pointer;
}
.fc-today {
    background-color: #eef7ff !important;
}
.fc-event-present {
    background: #27ae60 !important;
    color: #ffffff !important;
}
.fc-event-pending {
    background: #d35400 !important;
    color: #ffffff !important;
    border: 1px solid #ba4a00 !important;
    animation: pulse-border 2s infinite;
}
.fc-event-absent {
    background: #e74c3c !important;
    color: #ffffff !important;
}
.fc-event-leave {
    background: #f39c12 !important;
    color: #ffffff !important;
}
.fc-event-holiday {
    background: #3498db !important;
    color: #ffffff !important;
}
.fc-event-pub-holiday {
    background: #16a085 !important;
    color: #ffffff !important;
}
.fc-event-weekend {
    background: #7f8c8d !important;
    color: #ffffff !important;
}
.att-legend-item {
    display: inline-flex;
    align-items: center;
    margin-right: 15px;
    font-size: 12px;
    font-weight: 600;
    color: #555;
}
.att-legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 3px;
    display: inline-block;
    margin-right: 5px;
}
</style>

<div class="row" id="printableArea">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <!-- Default panel contents -->
            <div class="panel-heading">
                <div class="panel-title">
                    <strong><?= $name . ' ' . lang('time_logs') ?></strong>
                    <div class="pull-right hidden-print" style="margin-top: -3px;">
                        <!-- Face ID Biometric Admin Actions -->
                        <?php if (!empty($user_biometrics) && !empty($user_biometrics->face_descriptor)) { ?>
                            <?php if ($user_biometrics->is_approved == 1) { ?>
                                <span class="label label-success" style="font-size:11px; padding: 4px 7px; margin-right:6px;" title="Face ID is registered & approved">
                                    <i class="fa fa-check-circle"></i> Face ID Approved
                                </span>
                                <?php if ($user_type == 1 || !empty($admin)) { ?>
                                <a href="<?= base_url('admin/attendance/reset_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Are you sure you want to RESET this employee\'s Face ID? Once reset, they can re-register their face on the mobile app.');" 
                                   class="btn btn-warning btn-xs" style="margin-right: 4px;" title="Reset Face ID to allow re-enrollment">
                                    <i class="fa fa-refresh"></i> Reset Face ID
                                </a>
                                <a href="<?= base_url('admin/attendance/reject_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Reject this employee\'s Face ID?');" 
                                   class="btn btn-danger btn-xs" style="margin-right: 6px;" title="Reject Face ID">
                                    <i class="fa fa-times"></i> Reject
                                </a>
                                <?php } ?>
                            <?php } elseif ($user_biometrics->is_approved == 0) { ?>
                                <span class="label label-warning" style="font-size:11px; padding: 4px 7px; margin-right:6px;" title="Face ID registered, pending Admin approval">
                                    <i class="fa fa-clock-o"></i> Face ID Pending Approval
                                </span>
                                <?php if ($user_type == 1 || !empty($admin)) { ?>
                                <a href="<?= base_url('admin/attendance/approve_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Approve this employee\'s Face ID?');" 
                                   class="btn btn-success btn-xs" style="margin-right: 4px;">
                                    <i class="fa fa-check"></i> Approve
                                </a>
                                <a href="<?= base_url('admin/attendance/reject_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Reject this employee\'s Face ID request?');" 
                                   class="btn btn-danger btn-xs" style="margin-right: 4px;">
                                    <i class="fa fa-times"></i> Reject
                                </a>
                                <a href="<?= base_url('admin/attendance/reset_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Reset this Face ID so employee can re-enroll?');" 
                                   class="btn btn-warning btn-xs" style="margin-right: 6px;">
                                    <i class="fa fa-refresh"></i> Reset
                                </a>
                                <?php } ?>
                            <?php } elseif ($user_biometrics->is_approved == 3) { ?>
                                <span class="label label-danger" style="font-size:11px; padding: 4px 7px; margin-right:6px;" title="Face ID was rejected">
                                    <i class="fa fa-times-circle"></i> Face ID Rejected
                                </span>
                                <?php if ($user_type == 1 || !empty($admin)) { ?>
                                <a href="<?= base_url('admin/attendance/approve_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Approve this employee\'s Face ID?');" 
                                   class="btn btn-success btn-xs" style="margin-right: 4px;">
                                    <i class="fa fa-check"></i> Approve
                                </a>
                                <a href="<?= base_url('admin/attendance/reset_face_id/' . $user_id . '?redirect_to=admin/attendance/time_history?user_id=' . $user_id) ?>" 
                                   onclick="return confirm('Reset Face ID to allow re-enrollment?');" 
                                   class="btn btn-warning btn-xs" style="margin-right: 6px;">
                                    <i class="fa fa-refresh"></i> Reset
                                </a>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="label label-default" style="font-size:11px; padding: 4px 7px; margin-right:6px;">
                                    <i class="fa fa-user-times"></i> Face ID Reset (Allowed to Re-register)
                                </span>
                            <?php } ?>
                        <?php } else { ?>
                            <span class="label label-default" style="font-size:11px; padding: 4px 7px; margin-right:6px;">
                                <i class="fa fa-user-times"></i> Face ID Not Registered
                            </span>
                        <?php } ?>

                        <?php if ($user_type == 1 || !empty($admin)) { ?>
                        <a href="<?= base_url('admin/attendance/face_id_requests') ?>" class="btn btn-info btn-xs" style="margin-right: 6px;" title="View all Face ID requests">
                            <i class="fa fa-id-badge"></i> All Face ID Requests
                        </a>
                        <button type="button" class="btn btn-success btn-xs" onclick="openMarkAttendanceModal('<?= date('Y-m-d') ?>', null)" style="margin-right: 8px;">
                            <i class="fa fa-plus-circle"></i> Mark Attendance
                        </button>
                        <?php } ?>
                        <a href="<?= base_url() ?>admin/attendance/time_history_pdf/<?= $user_id ?>"
                            class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top"
                            title="<?= lang('pdf') ?>"><span><i class="fa fa-file-pdf-o"></i></span></a>
                        <a href="" onclick="printEmp_report('printableArea')" class="btn btn-danger btn-xs"
                            data-toggle="tooltip" data-placement="top" title="<?= lang('print') ?>"><span><i
                                    class="fa fa-print"></i></span></a>
                    </div>
                </div>
            </div>

            <!-- View Switch Tabs -->
            <ul class="nav nav-tabs" style="margin-bottom: 0; background: #fafafa; border-bottom: 2px solid #ddd; padding: 6px 15px 0;">
                <li class="active">
                    <a href="#tab_calendar_view" data-toggle="tab">
                        <i class="fa fa-calendar text-primary"></i> <strong>Attendance Calendar</strong>
                    </a>
                </li>
                <li>
                    <a href="#tab_time_logs_list" data-toggle="tab">
                        <i class="fa fa-list text-info"></i> <strong>Time Logs (List View)</strong>
                    </a>
                </li>
                <li id="li_pending_tab">
                    <a href="#tab_pending_approvals" data-toggle="tab">
                        <i class="fa fa-map-marker text-danger"></i> <strong>Out-of-Range Approvals</strong>
                        <span class="badge" style="background:#e67e22; margin-left:4px;" id="badge_pending_count"><?= !empty($pending_approvals_count) ? $pending_approvals_count : 0 ?></span>
                    </a>
                </li>
                <li id="li_face_requests_tab">
                    <a href="<?= base_url('admin/attendance/face_id_requests') ?>" style="color: #2c3e50;">
                        <i class="fa fa-id-badge text-primary"></i> <strong>Face ID Requests & Biometrics</strong>
                        <span class="badge" style="background:#d35400; margin-left:4px;" id="badge_face_pending_count"><?= !empty($pending_face_count) ? $pending_face_count : 0 ?></span>
                    </a>
                </li>
            </ul>

            <div class="tab-content" style="padding: 15px;">
                <!-- TAB 1: CALENDAR VIEW -->
                <div class="tab-pane active" id="tab_calendar_view">
                    <!-- Earn Leave & Sick Leave Balance Quick Banner -->
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-6 col-xs-12">
                            <div style="background: #e8f8f5; border: 1px solid #a3e4d7; border-radius: 6px; padding: 10px 15px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <span class="label" style="background: #27ae60; font-size: 11px;">EL</span>
                                    <strong style="color: #117a65; font-size: 13px; margin-left: 6px;">Earn Leave Balance:</strong>
                                    <span id="summary_el_bal" style="font-size: 16px; font-weight: 700; color: #0e6251; margin-left: 6px;">0.00</span> <small class="text-muted">Days</small>
                                </div>
                                <a href="<?= base_url('admin/leave_management/leave_credits') ?>" class="btn btn-xs btn-success" title="Manage Monthly Credits">
                                    <i class="fa fa-calendar-plus-o"></i> Leave Credits
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div style="background: #f4ecf7; border: 1px solid #d2b4de; border-radius: 6px; padding: 10px 15px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <span class="label" style="background: #8e44ad; font-size: 11px;">SL</span>
                                    <strong style="color: #6c3483; font-size: 13px; margin-left: 6px;">Sick Leave Balance:</strong>
                                    <span id="summary_sl_bal" style="font-size: 16px; font-weight: 700; color: #512e5f; margin-left: 6px;">0.00</span> <small class="text-muted">Days</small>
                                </div>
                                <span class="text-muted" style="font-size: 11px;"><i class="fa fa-info-circle"></i> Monthly Accrual</span>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Summary Row -->
                    <div class="att-kpi-container">
                        <div class="row">
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 16.66%;">
                                <div class="att-card card-att-present">
                                    <div>
                                        <div class="att-count" id="summary_present">0</div>
                                        <div class="att-label">Present</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-check-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 16.66%;">
                                <div class="att-card card-att-pending" style="cursor:pointer;" onclick="$('a[href=\'#tab_pending_approvals\']').tab('show');">
                                    <div>
                                        <div class="att-count" id="summary_pending">0</div>
                                        <div class="att-label">Pending Approval</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-exclamation-triangle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 16.66%;">
                                <div class="att-card card-att-absent">
                                    <div>
                                        <div class="att-count" id="summary_absent">0</div>
                                        <div class="att-label">Absent</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-times-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 16.66%;">
                                <div class="att-card card-att-leave">
                                    <div>
                                        <div class="att-count" id="summary_leave">0</div>
                                        <div class="att-label">On Leave</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-clock-o"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 16.66%;">
                                <div class="att-card card-att-holiday">
                                    <div>
                                        <div class="att-count" id="summary_holiday">0</div>
                                        <div class="att-label">Holidays / Off</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-calendar-check-o"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 16.66%;">
                                <div class="att-card card-att-hours">
                                    <div>
                                        <div class="att-count" id="summary_hours">0h 0m</div>
                                        <div class="att-label">Work Hours</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-hourglass-half"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend & Quick Info -->
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px 15px; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                        <div>
                            <span class="att-legend-item"><span class="att-legend-dot" style="background:#27ae60;"></span> Present</span>
                            <span class="att-legend-item"><span class="att-legend-dot" style="background:#e67e22;"></span> Pending (Out of Range)</span>
                            <span class="att-legend-item"><span class="att-legend-dot" style="background:#e74c3c;"></span> Absent</span>
                            <span class="att-legend-item"><span class="att-legend-dot" style="background:#f39c12;"></span> On Leave</span>
                            <span class="att-legend-item"><span class="att-legend-dot" style="background:#3498db;"></span> Holiday</span>
                            <span class="att-legend-item"><span class="att-legend-dot" style="background:#7f8c8d;"></span> Weekly Off</span>
                        </div>
                        <?php if ($user_type == 1 || !empty($admin)) { ?>
                        <div class="text-muted" style="font-size: 12px;">
                            <i class="fa fa-info-circle text-primary"></i> <em>Click on any date or event to mark/update attendance or mark absent!</em>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- Calendar Div -->
                    <div id="attendance_calendar"></div>
                </div>

                <!-- TAB 2: TIME LOGS LIST (ORIGINAL VIEW) -->
                <div class="tab-pane" id="tab_time_logs_list">
                    <div class="custom-tabs" role="tabpanel">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <?php
                            if (!empty($mytime_info)) : foreach ($mytime_info as $year => $v_time_info) :
                            ?>
                            <?php if (!empty($v_time_info)) : ?>
                            <li role="presentation" class="<?php
                                                                        if ($year == $active) {
                                                                            echo 'active';
                                                                        }
                                                                        ?>"><a href="#time_year_<?php echo $year ?>" aria-controls="home"
                                    role="tab" data-toggle="tab"><?php echo $year ?></a></li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content" id="custom-tab-content" style="padding-top: 15px;">
                            <?php if (!empty($mytime_info)) :
                                foreach ($mytime_info as $year => $v_time_info) :
                            ?>
                            <div role="tabpanel" class="tab-pane <?php
                                                                            if ($year == $active) {
                                                                                echo 'active';
                                                                            }
                                                                            ?>" id="time_year_<?php echo $year ?>">
                                <?php if (!empty($v_time_info)) : foreach ($v_time_info as $week => $time_info) : ?>
                                <?php if (!empty($time_info)) : ?>
                                <div class="panel-group" id="accordion_<?= $year ?>_<?= $week ?>" role="tablist" aria-multiselectable="true">
                                    <div class="panel panel-custom">
                                        <div class="panel-heading" role="tab" id="heading_<?= $year ?>_<?= $week ?>">
                                            <h4 class="panel-title time-color">
                                                <a data-toggle="collapse" data-parent="#accordion_<?= $year ?>_<?= $week ?>" href="#collapse_<?= $year ?>_<?= $week ?>"
                                                    aria-expanded="true" aria-controls="collapse_<?= $year ?>_<?= $week ?>">
                                                    Week : <?php echo $week; ?>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapse_<?= $year ?>_<?= $week ?>" class="panel-collapse collapse" role="tabpanel"
                                            aria-labelledby="heading_<?= $year ?>_<?= $week ?>">
                                            <div class="panel-body table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th><?= lang('clock_in_time') ?></th>
                                                            <th><?= lang('clock_out_time') ?></th>
                                                            <th><?= lang('ip_address') ?></th>
                                                            <th><?= lang('hours') ?></th>
                                                            <th class="hidden-print"><?= lang('action') ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $total_hh = 0;
                                                        $total_mm = 0;

                                                        if (!empty($time_info)) : foreach ($time_info as $key => $v_mytime) :
                                                        ?>
                                                        <td colspan="5" class="attendance_report"><?php echo $key; ?></td>

                                                        <?php
                                                        $vtimeinfo = $v_mytime[0];
                                                        if ($vtimeinfo->attendance_status == 1) {
                                                            $timeinfo = get_result('tbl_clock', array('attendance_id' => $vtimeinfo->attendance_id));

                                                            foreach ($timeinfo as $mytime) : ?>
                                                        <tr id="table_<?= strtotime($key) ?>">
                                                            <td><?php echo display_time($mytime->clockin_time); ?></td>
                                                            <td><?php
                                                                    if (empty($mytime->clockout_time)) {
                                                                        echo '<span class="text-danger">' . lang("currently_clock_in") . '<span>';
                                                                    } else {
                                                                        echo display_time($mytime->clockout_time);
                                                                    }
                                                                    ?>
                                                            </td>
                                                            <td>
                                                                <?= $mytime->ip_address ?>
                                                                <?php if (!empty($mytime->latitude) && !empty($mytime->longitude)) { ?>
                                                                    <br>
                                                                    <a href="https://www.google.com/maps?q=<?= urlencode($mytime->latitude . ',' . $mytime->longitude) ?>" target="_blank" class="btn btn-xs btn-link" style="padding:0; font-size:11px; color:#d35400; font-weight:600;" title="View exact location on Google Maps">
                                                                        <i class="fa fa-map-marker text-danger"></i> <?= !empty($mytime->location) ? htmlspecialchars($mytime->location) : 'View on Google Maps' ?>
                                                                    </a>
                                                                <?php } ?>
                                                            </td>
                                                            <td><?php
                                                                    if (!empty($mytime->clockout_time)) {
                                                                        $startdatetime = strtotime($vtimeinfo->date_in . " " . $mytime->clockin_time);
                                                                        $enddatetime = strtotime($vtimeinfo->date_out . " " . $mytime->clockout_time);
                                                                        $difference = $enddatetime - $startdatetime;
                                                                        $years = abs(floor($difference / 31536000));
                                                                        $days = abs(floor(($difference - ($years * 31536000)) / 86400));
                                                                        $hours = abs(floor(($difference - ($years * 31536000) - ($days * 86400)) / 3600));
                                                                        $mins = abs(floor(($difference - ($years * 31536000) - ($days * 86400)) - ($hours * 3600)) / 60);
                                                                        $total_mm += $mins;
                                                                        $total_hh += $hours;
                                                                        echo $hours . " : " . $mins . " m";
                                                                    }
                                                                    ?></td>
                                                            <?php if (!empty($mytime->clock_id)) { ?>
                                                            <td class="hidden-print">
                                                                <?php if (!empty($edited)) { ?>
                                                                <?php echo btn_edit_modal('admin/attendance/edit_mytime/' . $mytime->clock_id) ?>
                                                                <?php }
                                                                if (!empty($admin)) { ?>
                                                                <?php echo ajax_anchor(base_url('admin/attendance/delete_mytime/' . $mytime->clock_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . strtotime($key))) ?>
                                                                <?php } ?>
                                                            </td>
                                                            <?php } ?>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php } else if ($vtimeinfo->attendance_status == 2) { 
                                                            $timeinfo = get_result('tbl_clock', array('attendance_id' => $vtimeinfo->attendance_id));
                                                            $flog = $this->db->where('attendance_id', $vtimeinfo->attendance_id)->order_by('log_id', 'DESC')->get('tbl_attendance_face_logs')->row();
                                                            foreach ($timeinfo as $mytime) : 
                                                                $t_lat = !empty($mytime->latitude) ? $mytime->latitude : (!empty($flog) ? $flog->lat : '');
                                                                $t_lng = !empty($mytime->longitude) ? $mytime->longitude : (!empty($flog) ? $flog->lng : '');
                                                                $t_loc = !empty($mytime->location) ? $mytime->location : (!empty($flog) ? $flog->address : 'Outside Office Range');
                                                        ?>
                                                        <tr id="table_<?= strtotime($key) ?>" style="background: #fef9e7;">
                                                            <td>
                                                                <strong><?php echo display_time($mytime->clockin_time); ?></strong>
                                                                <br><span class="label" style="background:#e67e22; font-size:10px;"><i class="fa fa-exclamation-triangle"></i> Pending Approval (Out of Range)</span>
                                                            </td>
                                                            <td><?php
                                                                    if (empty($mytime->clockout_time)) {
                                                                        echo '<span class="text-danger">' . lang("currently_clock_in") . '<span>';
                                                                    } else {
                                                                        echo display_time($mytime->clockout_time);
                                                                    }
                                                                    ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($t_lat) && !empty($t_lng)) { ?>
                                                                    <a href="https://www.google.com/maps?q=<?= urlencode($t_lat . ',' . $t_lng) ?>" target="_blank" class="btn btn-xs btn-default" style="color:#d35400; font-weight:600; border-color:#e67e22;" title="View on Google Maps">
                                                                        <i class="fa fa-map-marker text-danger"></i> <?= htmlspecialchars($t_loc) ?>
                                                                    </a>
                                                                    <br><small class="text-muted"><i class="fa fa-globe"></i> <?= round($t_lat, 4) ?>, <?= round($t_lng, 4) ?></small>
                                                                <?php } else { ?>
                                                                    <?= $mytime->ip_address ?>
                                                                <?php } ?>
                                                            </td>
                                                            <td>-</td>
                                                            <td class="hidden-print">
                                                                <?php if (!empty($admin) || $user_type == 1) { ?>
                                                                <button type="button" class="btn btn-xs btn-success" onclick="approvePendingAtt(<?= $vtimeinfo->attendance_id ?>)" title="Approve Attendance">
                                                                    <i class="fa fa-check"></i> Approve
                                                                </button>
                                                                <button type="button" class="btn btn-xs btn-danger" onclick="rejectPendingAtt(<?= $vtimeinfo->attendance_id ?>)" title="Reject Attendance">
                                                                    <i class="fa fa-times"></i> Reject
                                                                </button>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php } else if ($vtimeinfo->attendance_status == 0) { ?>
                                                        <tr>
                                                            <td colspan="5"><span class="label label-danger"><?= lang('absent') ?></span></td>
                                                        </tr>
                                                        <?php } elseif ($vtimeinfo->attendance_status == 3) { ?>
                                                        <tr>
                                                            <td colspan="5"><span class="label label-warning"><?= lang('on_leave') ?></span></td>
                                                        </tr>
                                                        <?php } ?>

                                                        <?php endforeach; ?>
                                                        <table>
                                                            <tr>
                                                                <td colspan="2" class="text-right">
                                                                    <strong style="margin-right: 10px; "><?= lang('total_working_hour') ?>: </strong>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                    if ($total_mm > 59) {
                                                                        $total_hh += intval($total_mm / 60);
                                                                        $total_mm = intval($total_mm % 60);
                                                                    }
                                                                    echo $total_hh . " : " . $total_mm . " m";
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <?php else : ?>
                                                        <tr>
                                                            <td colspan="5"><?= lang('nothing_to_display') ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php else : ?>
                        <div class="alert alert-info"><?= lang('nothing_to_display') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB 3: PENDING APPROVALS (OUT OF RANGE) -->
                <div class="tab-pane" id="tab_pending_approvals">
                    <div style="background: #fcf3cf; border: 1px solid #f9e79f; border-radius: 6px; padding: 12px 18px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="color: #7d6608; font-size: 14px;"><i class="fa fa-map-marker text-danger"></i> Out-of-Range Attendance Queue</strong>
                            <p style="margin: 3px 0 0; font-size: 12px; color: #7d6608;">Employees whose attendance was marked outside the 250m geofence radius. Click "View on Google Maps" to verify location, then Approve or Reject.</p>
                        </div>
                        <button type="button" class="btn btn-xs btn-warning" onclick="loadPendingApprovalsTable()">
                            <i class="fa fa-refresh"></i> Refresh List
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tbl_pending_queue">
                            <thead style="background: #2c3e50; color: #ffffff;">
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Time In / Out</th>
                                    <th>Distance from Office</th>
                                    <th>Detected Punch Location</th>
                                    <th style="text-align: center;">Google Maps</th>
                                    <th class="hidden-print" style="text-align: center; width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pending_queue_body">
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 20px;">
                                        <i class="fa fa-spinner fa-spin text-muted"></i> Loading out-of-range punches...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Mark / Update Attendance -->
<div class="modal fade" id="modalMarkAttendance" tabindex="-1" role="dialog" aria-labelledby="markAttendanceLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e293b; color: #ffffff; border-top-left-radius: 5px; border-top-right-radius: 5px;">
                <button type="button" class="close" data-dismiss="modal" style="color: #ffffff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title" id="markAttendanceLabel">
                    <i class="fa fa-calendar-check-o text-success"></i> Mark / Update Attendance
                </h4>
            </div>
            <form id="form_mark_attendance" class="form-horizontal">
                <div class="modal-body" style="padding: 20px 25px;">
                    <input type="hidden" name="user_id" id="modal_user_id" value="<?= $user_id ?>">
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Employee</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" readonly style="background:#f8f9fa; font-weight:600;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Date <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="date" id="modal_att_date" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Attendance Status <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="status" id="modal_att_status" required style="font-weight:600;">
                                <option value="1" style="color:#27ae60;">✔ Present (Approved)</option>
                                <option value="2" style="color:#e67e22;">⚠ Pending Approval (Out of Range)</option>
                                <option value="0" style="color:#e74c3c;">✖ Absent</option>
                                <option value="3" style="color:#f39c12;">⏳ On Leave</option>
                                <option value="H" style="color:#3498db;">★ Holiday</option>
                                <option value="delete" style="color:#888;">🗑 Remove Record (Clear)</option>
                            </select>
                        </div>
                    </div>

                    <div id="leave_type_fields" style="display:none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Leave Type <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select name="leave_type" id="modal_leave_type" class="form-control">
                                    <option value="EL">Earn Leave (EL)</option>
                                    <option value="SL">Sick Leave (SL)</option>
                                    <option value="CL">Casual Leave (CL)</option>
                                </select>
                                <small class="text-muted">Recorded and deducted from employee leave balance.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Google Maps Info Box -->
                    <div id="modal_location_box" style="display:none; background:#fef9e7; border:1px solid #f9e79f; border-radius:6px; padding:12px 15px; margin-bottom:15px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                            <div>
                                <strong style="color:#7d6608; font-size:12px;"><i class="fa fa-map-marker text-danger"></i> Punch Location & Coordinates:</strong>
                                <div id="modal_loc_text" style="font-size:12px; font-weight:600; color:#333; margin-top:2px;">-</div>
                                <small id="modal_loc_coords" class="text-muted"></small>
                            </div>
                            <a id="modal_maps_link" href="#" target="_blank" class="btn btn-xs btn-danger" style="margin-top:4px; font-weight:600;">
                                <i class="fa fa-globe"></i> View on Google Maps
                            </a>
                        </div>
                    </div>

                    <div id="present_time_fields">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Clock In Time</label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control" name="clockin_time" id="modal_clockin" value="09:30">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Clock Out Time</label>
                            <div class="col-sm-9">
                                <input type="time" class="form-control" name="clockout_time" id="modal_clockout" value="18:30">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Remarks / Notes</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="comments" id="modal_comments" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="btn_modal_approve" onclick="approveFromModal()" style="display:none;">
                        <i class="fa fa-check"></i> Approve Attendance
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn_save_attendance">
                        <i class="fa fa-save"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src='<?= base_url() ?>assets/plugins/fullcalendar/moment.min.js'></script>
<script src='<?= base_url() ?>assets/plugins/fullcalendar/fullcalendar.min.js'></script>

<script type="text/javascript">
$(document).ready(function() {
    var current_user_id = '<?= $user_id ?>';

    // Toggle status specific fields
    $('#modal_att_status').on('change', function() {
        var val = $(this).val();
        if (val == '1') {
            $('#present_time_fields').slideDown(200);
            $('#leave_type_fields').slideUp(200);
        } else if (val == '3') {
            $('#present_time_fields').slideUp(200);
            $('#leave_type_fields').slideDown(200);
        } else {
            $('#present_time_fields').slideUp(200);
            $('#leave_type_fields').slideUp(200);
        }
    });

    // Initialize FullCalendar
    if ($('#attendance_calendar').length) {
        $('#attendance_calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,listMonth'
            },
            defaultView: 'month',
            editable: false,
            selectable: true,
            selectHelper: true,
            eventLimit: 3,
            events: function(start, end, timezone, callback) {
                var s_str = start.format('YYYY-MM-DD');
                var e_str = end.format('YYYY-MM-DD');
                $.ajax({
                    url: '<?= base_url() ?>admin/attendance/get_user_attendance_calendar',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        user_id: current_user_id,
                        start: s_str,
                        end: e_str
                    },
                    success: function(res) {
                        if (res && res.summary) {
                            $('#summary_present').text(res.summary.present);
                            $('#summary_absent').text(res.summary.absent);
                            $('#summary_leave').text(res.summary.leave);
                            $('#summary_holiday').text(res.summary.holiday);
                            $('#summary_hours').text(res.summary.total_working_hours);
                            if (res.summary.pending !== undefined) {
                                $('#summary_pending').text(res.summary.pending);
                                $('#badge_pending_count').text(res.summary.pending);
                            }
                            if (res.summary.el_balance !== undefined) {
                                $('#summary_el_bal').text(res.summary.el_balance);
                            }
                            if (res.summary.sl_balance !== undefined) {
                                $('#summary_sl_bal').text(res.summary.sl_balance);
                            }
                        }
                        callback(res.events || []);
                    },
                    error: function() {
                        callback([]);
                    }
                });
            },
            dayClick: function(date, jsEvent, view) {
                <?php if ($user_type == 1 || !empty($admin)) { ?>
                openMarkAttendanceModal(date.format('YYYY-MM-DD'), null);
                <?php } ?>
            },
            eventClick: function(calEvent, jsEvent, view) {
                <?php if ($user_type == 1 || !empty($admin)) { ?>
                if (calEvent && calEvent.date) {
                    openMarkAttendanceModal(calEvent.date, calEvent);
                }
                <?php } ?>
            }
        });
    }

    // Refresh calendar layout on tab switch
    $('a[href="#tab_calendar_view"]').on('shown.bs.tab', function() {
        $('#attendance_calendar').fullCalendar('render');
    });

    // Form submission
    $('#form_mark_attendance').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btn_save_attendance');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '<?= base_url() ?>admin/attendance/save_user_attendance',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
                if (res.status === 'success') {
                    $('#modalMarkAttendance').modal('hide');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    $('#attendance_calendar').fullCalendar('refetchEvents');
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error updating attendance');
                    } else {
                        alert(res.message || 'Error updating attendance');
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Server error: ' + error);
                } else {
                    alert('Server error: ' + error);
                }
            }
        });
    });
});

function openMarkAttendanceModal(date_str, calEvent) {
    $('#modal_att_date').val(date_str);
    if (calEvent && calEvent.status !== undefined) {
        $('#modal_att_status').val(calEvent.status).trigger('change');
        if (calEvent.status == '1') {
            if (calEvent.clockin) {
                var cin = calEvent.clockin.substring(0, 5);
                $('#modal_clockin').val(cin);
            } else {
                $('#modal_clockin').val('09:30');
            }
            if (calEvent.clockout) {
                var cout = calEvent.clockout.substring(0, 5);
                $('#modal_clockout').val(cout);
            } else {
                $('#modal_clockout').val('18:30');
            }
        } else if (calEvent.status == '3') {
            if (calEvent.leave_type) {
                $('#modal_leave_type').val(calEvent.leave_type);
            }
        }
        $('#modal_comments').val(calEvent.comments || '');
    } else {
        $('#modal_att_status').val('1').trigger('change');
        $('#modal_clockin').val('09:30');
        $('#modal_clockout').val('18:30');
        $('#modal_comments').val('');
    }
    $('#modalMarkAttendance').modal('show');
}

function printEmp_report(printableArea) {
    $('div.wrapper').find('.collapse').css('display', 'block');
    var printContents = document.getElementById(printableArea).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}

var current_editing_att_id = 0;

function openMarkAttendanceModal(date_str, calEvent) {
    $('#modal_att_date').val(date_str);
    current_editing_att_id = (calEvent && calEvent.attendance_id) ? calEvent.attendance_id : 0;

    // Location box display
    if (calEvent && calEvent.maps_url) {
        $('#modal_location_box').show();
        var locDisplay = (calEvent.location ? calEvent.location : 'Lat: ' + calEvent.lat + ', Lng: ' + calEvent.long);
        if (calEvent.distance) {
            locDisplay += ' (' + calEvent.distance + ' from office)';
        }
        $('#modal_loc_text').text(locDisplay);
        $('#modal_loc_coords').text('Coordinates: ' + calEvent.lat + ', ' + calEvent.long);
        $('#modal_maps_link').attr('href', calEvent.maps_url);
    } else {
        $('#modal_location_box').hide();
    }

    if (calEvent && (calEvent.status == '2' || calEvent.status == 2)) {
        $('#btn_modal_approve').show();
    } else {
        $('#btn_modal_approve').hide();
    }

    if (calEvent && calEvent.status !== undefined) {
        $('#modal_att_status').val(calEvent.status).trigger('change');
        if (calEvent.status == '1' || calEvent.status == '2') {
            if (calEvent.clockin) {
                var cin = calEvent.clockin.substring(0, 5);
                $('#modal_clockin').val(cin);
            } else {
                $('#modal_clockin').val('09:30');
            }
            if (calEvent.clockout) {
                var cout = calEvent.clockout.substring(0, 5);
                $('#modal_clockout').val(cout);
            } else {
                $('#modal_clockout').val('18:30');
            }
        } else if (calEvent.status == '3') {
            if (calEvent.leave_type) {
                $('#modal_leave_type').val(calEvent.leave_type);
            }
        }
        $('#modal_comments').val(calEvent.comments || '');
    } else {
        $('#modal_att_status').val('1').trigger('change');
        $('#modal_clockin').val('09:30');
        $('#modal_clockout').val('18:30');
        $('#modal_comments').val('');
    }
    $('#modalMarkAttendance').modal('show');
}

function approvePendingAtt(att_id) {
    if (!confirm('Approve this attendance as Present?')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/attendance/approve_attendance',
        type: 'POST',
        data: { attendance_id: att_id },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                if (typeof toastr !== 'undefined') toastr.success(res.message);
                else alert(res.message);
                $('#attendance_calendar').fullCalendar('refetchEvents');
                loadPendingApprovalsTable();
                setTimeout(function() { location.reload(); }, 800);
            } else {
                if (typeof toastr !== 'undefined') toastr.error(res.message);
                else alert(res.message);
            }
        }
    });
}

function rejectPendingAtt(att_id) {
    if (!confirm('Reject this attendance and mark as Absent?')) return;
    $.ajax({
        url: '<?= base_url() ?>admin/attendance/reject_attendance',
        type: 'POST',
        data: { attendance_id: att_id },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                if (typeof toastr !== 'undefined') toastr.warning(res.message);
                else alert(res.message);
                $('#attendance_calendar').fullCalendar('refetchEvents');
                loadPendingApprovalsTable();
                setTimeout(function() { location.reload(); }, 800);
            } else {
                if (typeof toastr !== 'undefined') toastr.error(res.message);
                else alert(res.message);
            }
        }
    });
}

function approveFromModal() {
    if (current_editing_att_id) {
        $('#modalMarkAttendance').modal('hide');
        approvePendingAtt(current_editing_att_id);
    }
}

function loadPendingApprovalsTable() {
    var tbody = $('#pending_queue_body');
    tbody.html('<tr><td colspan="7" class="text-center" style="padding:15px;"><i class="fa fa-spinner fa-spin"></i> Refreshing queue...</td></tr>');

    $.ajax({
        url: '<?= base_url() ?>admin/attendance/get_pending_attendances',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success' && res.data && res.data.length > 0) {
                $('#badge_pending_count').text(res.data.length);
                $('#summary_pending').text(res.data.length);
                var html = '';
                $.each(res.data, function(idx, item) {
                    var mapsBtn = '-';
                    if (item.maps_url) {
                        mapsBtn = '<a href="' + item.maps_url + '" target="_blank" class="btn btn-xs btn-default" style="color:#d35400; font-weight:700; border-color:#e67e22;" title="View Google Maps">' +
                                  '<i class="fa fa-map-marker text-danger"></i> View Map</a>';
                    }
                    var locDisplay = item.location ? item.location : (item.lat ? (item.lat + ', ' + item.lng) : 'Outside Range');
                    var distDisplay = item.distance ? '<span class="label label-warning" style="background:#ba4a00;">' + item.distance + '</span>' : '<span class="text-muted">Out of Range</span>';

                    html += '<tr>' +
                            '<td><strong>' + item.fullname + '</strong>' + (item.employment_id ? ' (' + item.employment_id + ')' : '') + '<br><small class="text-muted">' + item.designations + '</small></td>' +
                            '<td>' + item.date + '</td>' +
                            '<td>In: ' + item.clockin + '<br>Out: ' + item.clockout + '</td>' +
                            '<td>' + distDisplay + '</td>' +
                            '<td>' + locDisplay + '</td>' +
                            '<td style="text-align:center;">' + mapsBtn + '</td>' +
                            '<td class="hidden-print" style="text-align:center;">' +
                            '  <button type="button" class="btn btn-xs btn-success" onclick="approvePendingAtt(' + item.attendance_id + ')" title="Approve"><i class="fa fa-check"></i> Approve</button> ' +
                            '  <button type="button" class="btn btn-xs btn-danger" onclick="rejectPendingAtt(' + item.attendance_id + ')" title="Reject"><i class="fa fa-times"></i> Reject</button>' +
                            '</td>' +
                            '</tr>';
                });
                tbody.html(html);
            } else {
                $('#badge_pending_count').text('0');
                tbody.html('<tr><td colspan="7" class="text-center text-muted" style="padding:25px;"><i class="fa fa-check-circle text-success" style="font-size:24px;"></i><br><strong>No pending out-of-range attendances!</strong><br><small>All employee punches are inside range or have been reviewed.</small></td></tr>');
            }
        },
        error: function() {
            tbody.html('<tr><td colspan="7" class="text-center text-danger" style="padding:15px;">Failed to load pending queue.</td></tr>');
        }
    });
}

// Load pending table when tab is clicked
$('a[href="#tab_pending_approvals"]').on('shown.bs.tab', function() {
    loadPendingApprovalsTable();
});

// Auto load on ready
$(document).ready(function() {
    loadPendingApprovalsTable();
});
</script>