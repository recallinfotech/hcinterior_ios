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
                        <?php if ($user_type == 1 || !empty($admin)) { ?>
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
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                                <div class="att-card card-att-present">
                                    <div>
                                        <div class="att-count" id="summary_present">0</div>
                                        <div class="att-label">Present</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-check-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                                <div class="att-card card-att-absent">
                                    <div>
                                        <div class="att-count" id="summary_absent">0</div>
                                        <div class="att-label">Absent</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-times-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                                <div class="att-card card-att-leave">
                                    <div>
                                        <div class="att-count" id="summary_leave">0</div>
                                        <div class="att-label">On Leave</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-clock-o"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                                <div class="att-card card-att-holiday">
                                    <div>
                                        <div class="att-count" id="summary_holiday">0</div>
                                        <div class="att-label">Holidays / Off</div>
                                    </div>
                                    <div class="att-icon"><i class="fa fa-calendar-check-o"></i></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
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
                                                            <td><?= $mytime->ip_address ?></td>
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
                                <option value="1" style="color:#27ae60;">✔ Present</option>
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
</script>