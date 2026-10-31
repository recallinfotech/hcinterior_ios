<?php
$this->load->view('admin/attendance/attendance_report_search');

if (!function_exists('get_avatar_color')) {
    function get_avatar_color($name) {
        $colors = [
            ['bg' => '#e0e7ff', 'text' => '#4338ca'],
            ['bg' => '#ede9fe', 'text' => '#6d28d9'],
            ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
            ['bg' => '#ccfbf1', 'text' => '#0f766e'],
            ['bg' => '#dcfce7', 'text' => '#15803d'],
            ['bg' => '#fef3c7', 'text' => '#b45309'],
            ['bg' => '#ffedd5', 'text' => '#c2410c'],
            ['bg' => '#fee2e2', 'text' => '#b91c1c'],
            ['bg' => '#fce7f3', 'text' => '#be185d'],
        ];
        $idx = abs(crc32((string)$name)) % count($colors);
        return $colors[$idx];
    }
}
?>

<style>
/* Modern Report & Drawer Styles */
.att-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
.att-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
}
.att-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 16px;
    flex-shrink: 0;
}
.att-card-val {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    color: #111827;
}
.att-card-lbl {
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    margin-top: 2px;
}

/* Table styling matching reference image */
#attendance_report_table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100% !important;
}
#attendance_report_table thead th {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    font-size: 13px;
    text-transform: capitalize;
    border-bottom: 2px solid #e5e7eb;
    border-top: none;
    padding: 12px 14px;
    vertical-align: middle;
}
#attendance_report_table tbody td {
    padding: 12px 14px;
    vertical-align: middle;
    font-size: 13px;
    color: #374151;
    border-top: 1px solid #f3f4f6;
    background: #ffffff;
}
#attendance_report_table tbody tr:hover td {
    background-color: #f8fafc;
}
.att-avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    margin-right: 10px;
    flex-shrink: 0;
}
.att-staff-info {
    display: inline-flex;
    align-items: center;
}
.att-staff-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
    display: inline-block;
}
.att-badge-branch {
    background: #eef2ff;
    color: #4338ca;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 12px;
    display: inline-block;
    border: 1px solid #e0e7ff;
}
.btn-manage-att {
    background: #ffffff;
    border: 1px solid #d1d5db;
    color: #374151;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.15s;
}
.btn-manage-att:hover {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #ffffff;
}

/* Slide-in Edit Attendance Drawer */
.att-drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 9998;
    display: none;
    transition: opacity 0.3s;
}
.att-drawer-panel {
    position: fixed;
    top: 0;
    right: -480px;
    width: 440px;
    max-width: 90vw;
    height: 100vh;
    background: #ffffff;
    z-index: 9999;
    box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
    transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.att-drawer-panel.open {
    right: 0;
}
.att-drawer-header {
    padding: 18px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.att-drawer-title {
    font-size: 17px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.att-drawer-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #9ca3af;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
}
.att-drawer-close:hover {
    color: #111827;
}
.att-drawer-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

/* Status Pills */
.pill-btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}
.pill-btn {
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    background: #ffffff;
    border: 1.5px solid #d1d5db;
    color: #4b5563;
    outline: none;
}
.pill-btn:hover {
    transform: scale(1.03);
}

/* Specific pill colors matching user screenshot */
.pill-btn.pill-absent {
    border-color: #ef4444;
    color: #dc2626;
}
.pill-btn.pill-absent.active {
    background: #ef4444 !important;
    color: #ffffff !important;
    border-color: #ef4444 !important;
}

.pill-btn.pill-halfday {
    border-color: #f59e0b;
    color: #d97706;
}
.pill-btn.pill-halfday.active {
    background: #f59e0b !important;
    color: #ffffff !important;
    border-color: #f59e0b !important;
}

.pill-btn.pill-present {
    border-color: #10b981;
    color: #059669;
}
.pill-btn.pill-present.active {
    background: #10b981 !important;
    color: #ffffff !important;
    border-color: #10b981 !important;
}

.pill-btn.pill-weekoff {
    border-color: #9ca3af;
    color: #4b5563;
}
.pill-btn.pill-weekoff.active {
    background: #6b7280 !important;
    color: #ffffff !important;
    border-color: #6b7280 !important;
}

.pill-btn.pill-holiday {
    border-color: #9ca3af;
    color: #4b5563;
}
.pill-btn.pill-holiday.active {
    background: #06b6d4 !important;
    color: #ffffff !important;
    border-color: #06b6d4 !important;
}

/* Leave Pills */
.pill-btn.pill-leave-paid {
    border-color: #ec4899;
    color: #db2777;
}
.pill-btn.pill-leave-paid.active {
    background: #ec4899 !important;
    color: #ffffff !important;
    border-color: #ec4899 !important;
}

.pill-btn.pill-leave-half {
    border-color: #d946ef;
    color: #c026d3;
}
.pill-btn.pill-leave-half.active {
    background: #d946ef !important;
    color: #ffffff !important;
    border-color: #d946ef !important;
}

.pill-btn.pill-leave-unpaid {
    border-color: #0ea5e9;
    color: #0284c7;
}
.pill-btn.pill-leave-unpaid.active {
    background: #0ea5e9 !important;
    color: #ffffff !important;
    border-color: #0ea5e9 !important;
}
</style>

<!-- Stat Metric Cards -->
<div class="row">
    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
        <div class="att-card">
            <div class="att-card-icon" style="background: #eef2ff; color: #4f46e5;">
                <i class="fa fa-users"></i>
            </div>
            <div>
                <div class="att-card-val"><?= number_format($summary['total_staff'] ?? count($staff_records)) ?></div>
                <div class="att-card-lbl">Total Staff</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
        <div class="att-card">
            <div class="att-card-icon" style="background: #ecfdf5; color: #10b981;">
                <i class="fa fa-check-circle"></i>
            </div>
            <div>
                <div class="att-card-val" style="color: #10b981;"><?= number_format($summary['total_present'] ?? 0) ?></div>
                <div class="att-card-lbl">Present Days</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
        <div class="att-card">
            <div class="att-card-icon" style="background: #fef2f2; color: #ef4444;">
                <i class="fa fa-times-circle"></i>
            </div>
            <div>
                <div class="att-card-val" style="color: #ef4444;"><?= number_format($summary['total_absent'] ?? 0) ?></div>
                <div class="att-card-lbl">Absent Days</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
        <div class="att-card">
            <div class="att-card-icon" style="background: #fffbeb; color: #f59e0b;">
                <i class="fa fa-clock-o"></i>
            </div>
            <div>
                <div class="att-card-val" style="color: #f59e0b;"><?= number_format($summary['total_half_day'] ?? 0) ?></div>
                <div class="att-card-lbl">Half Days</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
        <div class="att-card">
            <div class="att-card-icon" style="background: #fdf2f8; color: #ec4899;">
                <i class="fa fa-calendar-minus-o"></i>
            </div>
            <div>
                <div class="att-card-val" style="color: #ec4899;"><?= number_format($summary['total_leave'] ?? 0) ?></div>
                <div class="att-card-lbl">Leave Days</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Attendance Report Table Panel -->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom" style="border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;">
            <div class="panel-heading" style="background: #ffffff; border-bottom: 1px solid #f3f4f6; border-radius: 8px 8px 0 0; padding: 16px 20px;">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <h4 class="panel-title" style="color: #111827; font-size: 16px; font-weight: 700; margin: 0; line-height: 24px;">
                            <i class="fa fa-calendar-check-o text-primary" style="margin-right: 6px;"></i>
                            Monthly Attendance Summary &mdash; <span class="text-primary"><?= $month ?></span>
                            <span class="badge badge-info" style="margin-left: 8px; font-size: 12px; background: #3b82f6;"><?= count($staff_records) ?> Staff</span>
                        </h4>
                    </div>
                    <div class="col-md-6 col-sm-6 text-right">
                        <small class="text-muted" style="font-size: 12px; margin-right: 10px;">
                            <i class="fa fa-info-circle"></i> Click <b>Manage</b> on any staff to edit their daily attendance
                        </small>
                    </div>
                </div>
            </div>

            <div class="panel-body" style="padding: 16px;">
                <div class="table-responsive">
                    <table class="table table-hover" id="attendance_report_table">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Name</th>
                                <th style="min-width: 120px;">Phone Number</th>
                                <th style="min-width: 110px;">Employee ID</th>
                                <th style="min-width: 140px;">Job Title</th>
                                <th style="min-width: 180px;">Office Branch</th>
                                <th class="text-center" style="width: 75px;">Present</th>
                                <th class="text-center" style="width: 75px;">Absent</th>
                                <th class="text-center" style="width: 75px;">Half Day</th>
                                <th class="text-center" style="width: 75px;">Leave</th>
                                <th class="text-center" style="width: 75px;">Week Off</th>
                                <th class="text-center" style="width: 75px;">Holiday</th>
                                <th class="text-center" style="width: 90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($staff_records)) : foreach ($staff_records as $emp) : 
                                $clr = get_avatar_color($emp['fullname']);
                            ?>
                                <tr id="staff_row_<?= $emp['user_id'] ?>">
                                    <td>
                                        <div class="att-staff-info">
                                            <div class="att-avatar-circle" style="background: <?= $clr['bg'] ?>; color: <?= $clr['text'] ?>;">
                                                <?= $emp['initials'] ?>
                                            </div>
                                            <div>
                                                <a href="javascript:void(0)" onclick="openAttendanceDrawer(<?= $emp['user_id'] ?>, '<?= htmlspecialchars(addslashes($emp['fullname'])) ?>', '<?= htmlspecialchars(addslashes($emp['office_branch'])) ?>')" class="att-staff-name">
                                                    <?= htmlspecialchars($emp['fullname']) ?>
                                                </a>
                                                <?php if (!empty($emp['department']) && $emp['department'] != '-') : ?>
                                                    <div style="font-size: 11px; color: #9ca3af;"><?= htmlspecialchars($emp['department']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: #4b5563; font-weight: 500;">
                                            <?= !empty($emp['phone']) && $emp['phone'] != '-' ? $emp['phone'] : '<span class="text-muted">-</span>' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($emp['employee_id']) && $emp['employee_id'] != '-') : ?>
                                            <span class="badge" style="background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; font-weight: 600; font-size: 12px; padding: 4px 8px;">
                                                <?= $emp['employee_id'] ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #4b5563; font-size: 13px;">
                                            <?= htmlspecialchars($emp['job_title']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="att-badge-branch">
                                            <i class="fa fa-building-o" style="margin-right: 4px;"></i> <?= htmlspecialchars($emp['office_branch']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="staff-present-<?= $emp['user_id'] ?>" style="font-weight: 700; color: #16a34a; font-size: 14px;">
                                            <?= $emp['present'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="staff-absent-<?= $emp['user_id'] ?>" style="font-weight: 700; color: #dc2626; font-size: 14px;">
                                            <?= $emp['absent'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="staff-half-<?= $emp['user_id'] ?>" style="font-weight: 700; color: #d97706; font-size: 14px;">
                                            <?= $emp['half_day'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="staff-leave-<?= $emp['user_id'] ?>" style="font-weight: 700; color: #7c3aed; font-size: 14px;">
                                            <?= $emp['leave'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span style="font-weight: 600; color: #6b7280; font-size: 13px;">
                                            <?= $emp['week_off'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span style="font-weight: 600; color: #0891b2; font-size: 13px;">
                                            <?= $emp['holiday'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-manage-att" onclick="openAttendanceDrawer(<?= $emp['user_id'] ?>, '<?= htmlspecialchars(addslashes($emp['fullname'])) ?>', '<?= htmlspecialchars(addslashes($emp['office_branch'])) ?>')" title="Manage Attendance">
                                            <i class="fa fa-pencil text-primary"></i> Manage
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f9fafb; font-weight: 700;">
                                <td colspan="5" class="text-right" style="padding: 12px 14px; color: #111827;">Total:</td>
                                <td class="text-center" style="color: #16a34a; font-size: 14px;"><?= $summary['total_present'] ?? 0 ?></td>
                                <td class="text-center" style="color: #dc2626; font-size: 14px;"><?= $summary['total_absent'] ?? 0 ?></td>
                                <td class="text-center" style="color: #d97706; font-size: 14px;"><?= $summary['total_half_day'] ?? 0 ?></td>
                                <td class="text-center" style="color: #7c3aed; font-size: 14px;"><?= $summary['total_leave'] ?? 0 ?></td>
                                <td class="text-center" style="color: #6b7280; font-size: 14px;">-</td>
                                <td class="text-center" style="color: #0891b2; font-size: 14px;">-</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Slide-in Drawer: Edit Attendance (Matches user screenshot) -->
<div class="att-drawer-overlay" id="drawer_overlay" onclick="closeAttendanceDrawer()"></div>

<div class="att-drawer-panel" id="drawer_panel">
    <!-- Drawer Header -->
    <div class="att-drawer-header">
        <h4 class="att-drawer-title">
            Edit Attendance : <span id="drawer_staff_name" class="text-primary">-</span>
        </h4>
        <button type="button" class="att-drawer-close" onclick="closeAttendanceDrawer()">&times;</button>
    </div>

    <!-- Drawer Body -->
    <div class="att-drawer-body">
        <!-- Hidden Inputs -->
        <input type="hidden" id="drawer_user_id" value="">
        <input type="hidden" id="drawer_selected_status" value="absent">
        <input type="hidden" id="drawer_selected_leave_type" value="">

        <!-- Date Header & Picker -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div>
                <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #111827;" id="drawer_date_heading">
                    1st October
                </h4>
                <div style="font-size: 12px; color: #6b7280;" id="drawer_branch_subheading">
                    HC Interior Head Office, Noida
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="date" id="drawer_date_input" class="form-control input-sm" style="height: 32px; border-radius: 6px; font-size: 12px; font-weight: 600;" value="<?= date('Y-m-d') ?>">
                <button type="button" class="btn btn-default btn-sm" onclick="fetchDateAttendance()" title="Refresh" style="height: 32px; border-radius: 6px;">
                    <i class="fa fa-refresh" id="drawer_refresh_icon"></i>
                </button>
            </div>
        </div>

        <hr style="margin: 12px 0 16px 0; border-top: 1px solid #f3f4f6;">

        <!-- Primary Status Pills (Red Absent, Amber Half Day, Green Present, Week Off, Holiday) -->
        <div style="margin-bottom: 20px;">
            <div class="pill-btn-group">
                <button type="button" class="pill-btn pill-absent" id="pill_absent" onclick="selectStatus('0', 'absent')">
                    ABSENT
                </button>
                <button type="button" class="pill-btn pill-halfday" id="pill_half_day" onclick="selectStatus('2', 'half_day')">
                    HALF DAY
                </button>
                <button type="button" class="pill-btn pill-present" id="pill_present" onclick="selectStatus('1', 'present')">
                    PRESENT
                </button>
                <button type="button" class="pill-btn pill-weekoff" id="pill_week_off" onclick="selectStatus('WO', 'week_off')">
                    WEEK OFF
                </button>
                <button type="button" class="pill-btn pill-holiday" id="pill_holiday" onclick="selectStatus('H', 'holiday')">
                    HOLIDAY
                </button>
            </div>
        </div>

        <!-- Leaves Section (Paid Leave, Half Day Leave, Unpaid Leave) -->
        <div style="margin-bottom: 20px;">
            <label style="font-size: 14px; font-weight: 700; color: #111827; display: block; margin-bottom: 10px;">
                Leaves
            </label>
            <div class="pill-btn-group">
                <button type="button" class="pill-btn pill-leave-paid" id="pill_paid_leave" onclick="selectStatus('3', 'leave', 'PAID')">
                    PAID LEAVE
                </button>
                <button type="button" class="pill-btn pill-leave-half" id="pill_half_day_leave" onclick="selectStatus('3', 'leave', 'HALF_DAY')">
                    HALF DAY LEAVE
                </button>
                <button type="button" class="pill-btn pill-leave-unpaid" id="pill_unpaid_leave" onclick="selectStatus('3', 'leave', 'UNPAID')">
                    UNPAID LEAVE
                </button>
            </div>
        </div>

        <hr style="margin: 16px 0; border-top: 1px solid #f3f4f6;">

        <!-- Add Punch In Toggle -->
        <div style="margin-bottom: 16px;">
            <a href="javascript:void(0)" onclick="togglePunchInFields()" style="font-size: 13px; font-weight: 700; color: #0284c7; text-decoration: none; display: inline-block;">
                <i class="fa fa-plus-circle"></i> <span id="punch_toggle_text">ADD PUNCH IN</span>
            </a>

            <div id="punch_fields_wrap" style="display: none; margin-top: 12px; background: #f9fafb; padding: 14px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div class="row">
                    <div class="col-xs-6">
                        <label style="font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 4px;">Clock In Time</label>
                        <input type="time" id="drawer_clockin" class="form-control input-sm" value="09:30" style="border-radius: 6px;">
                    </div>
                    <div class="col-xs-6">
                        <label style="font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 4px;">Clock Out Time</label>
                        <input type="time" id="drawer_clockout" class="form-control input-sm" value="18:30" style="border-radius: 6px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Note Textarea -->
        <div style="margin-bottom: 24px;">
            <label style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block;">Notes / Remarks</label>
            <textarea id="drawer_note" rows="3" class="form-control" placeholder="Add Note" style="border-radius: 8px; border: 1px solid #d1d5db; resize: vertical;"></textarea>
        </div>

        <!-- Save Button -->
        <button type="button" class="btn btn-primary btn-block btn-lg" id="btn_save_attendance" onclick="saveAttendanceRecord()" style="font-weight: 700; border-radius: 8px; background: #3b82f6; border: none; padding: 12px; box-shadow: 0 2px 4px rgba(59,130,246,0.3);">
            <i class="fa fa-save" style="margin-right: 6px;"></i> Save Attendance
        </button>

        <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-link text-danger" onclick="deleteAttendanceRecord()" style="font-size: 12px; padding: 0; text-decoration: none;">
                <i class="fa fa-trash"></i> Clear Record
            </button>
            <button type="button" class="btn btn-default btn-sm" onclick="closeAttendanceDrawer()" style="border-radius: 6px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#attendance_report_table')) {
        $('#attendance_report_table').DataTable().destroy();
    }
    
    var attTable = $('#attendance_report_table').DataTable({
        pageLength: 25,
        responsive: true,
        order: [[0, 'asc']],
        dom: '<"row"<"col-sm-6"B><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                className: 'btn btn-default btn-sm',
                title: 'Attendance_Report_<?= str_replace(" ", "_", $month) ?>',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-text-o"></i> CSV',
                className: 'btn btn-default btn-sm',
                title: 'Attendance_Report_<?= str_replace(" ", "_", $month) ?>',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i> Print',
                className: 'btn btn-default btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search staff, phone, ID, branch...",
            lengthMenu: "Show _MENU_ entries"
        }
    });

    // Date picker change triggers re-fetch
    $('#drawer_date_input').on('change', function() {
        fetchDateAttendance();
    });
});

// State
var currentStaffId = null;
var currentStaffName = '';
var currentBranchName = '';
var currentStatusVal = '0';
var currentLeaveType = '';

function openAttendanceDrawer(userId, name, branch) {
    currentStaffId = userId;
    currentStaffName = name;
    currentBranchName = branch || 'Head Office';

    $('#drawer_user_id').val(userId);
    $('#drawer_staff_name').text(name);
    $('#drawer_branch_subheading').text(currentBranchName);

    // Default date to today or 1st of report month
    var reportDate = $('input[name="date"]').val();
    if (reportDate) {
        var parts = reportDate.split('-');
        var year = parts[0];
        var month = parts[1];
        var today = new Date();
        var curY = today.getFullYear();
        var curM = (today.getMonth() + 1).toString().padStart(2, '0');
        var curD = today.getDate().toString().padStart(2, '0');

        if (year == curY && month == curM) {
            $('#drawer_date_input').val(curY + '-' + curM + '-' + curD);
        } else {
            $('#drawer_date_input').val(year + '-' + month.padStart(2, '0') + '-01');
        }
    }

    // Open drawer
    $('#drawer_overlay').fadeIn(200);
    $('#drawer_panel').addClass('open');

    // Fetch details for this date
    fetchDateAttendance();
}

function closeAttendanceDrawer() {
    $('#drawer_panel').removeClass('open');
    $('#drawer_overlay').fadeOut(200);
}

function fetchDateAttendance() {
    if (!currentStaffId) return;

    var selectedDate = $('#drawer_date_input').val();
    if (!selectedDate) return;

    $('#drawer_refresh_icon').addClass('fa-spin');

    $.ajax({
        url: '<?= base_url() ?>admin/attendance/get_attendance_for_date',
        type: 'POST',
        data: {
            user_id: currentStaffId,
            date: selectedDate
        },
        dataType: 'json',
        success: function(res) {
            $('#drawer_refresh_icon').removeClass('fa-spin');
            if (res.status === 'success') {
                $('#drawer_date_heading').text(res.display_date);
                $('#drawer_note').val(res.comments || '');

                if (res.clockin_time || res.clockout_time) {
                    $('#punch_fields_wrap').show();
                    $('#punch_toggle_text').text('PUNCH IN DETAILS');
                    if (res.clockin_time) {
                        $('#drawer_clockin').val(convertTo24Hour(res.clockin_time));
                    }
                    if (res.clockout_time) {
                        $('#drawer_clockout').val(convertTo24Hour(res.clockout_time));
                    }
                } else {
                    $('#punch_fields_wrap').hide();
                    $('#punch_toggle_text').text('+ ADD PUNCH IN');
                    $('#drawer_clockin').val('09:30');
                    $('#drawer_clockout').val('18:30');
                }

                // Activate pill
                highlightPill(res.current_status, res.leave_type);
            }
        },
        error: function() {
            $('#drawer_refresh_icon').removeClass('fa-spin');
        }
    });
}

function highlightPill(status, leaveType) {
    $('.pill-btn').removeClass('active');

    if (status === 'present') {
        $('#pill_present').addClass('active');
        currentStatusVal = '1';
        currentLeaveType = '';
    } else if (status === 'half_day') {
        $('#pill_half_day').addClass('active');
        currentStatusVal = '2';
        currentLeaveType = '';
    } else if (status === 'week_off') {
        $('#pill_week_off').addClass('active');
        currentStatusVal = 'WO';
        currentLeaveType = '';
    } else if (status === 'holiday') {
        $('#pill_holiday').addClass('active');
        currentStatusVal = 'H';
        currentLeaveType = '';
    } else if (status === 'leave') {
        currentStatusVal = '3';
        if (leaveType === 'half_day') {
            $('#pill_half_day_leave').addClass('active');
            currentLeaveType = 'HALF_DAY';
        } else if (leaveType === 'unpaid') {
            $('#pill_unpaid_leave').addClass('active');
            currentLeaveType = 'UNPAID';
        } else {
            $('#pill_paid_leave').addClass('active');
            currentLeaveType = 'PAID';
        }
    } else {
        // absent
        $('#pill_absent').addClass('active');
        currentStatusVal = '0';
        currentLeaveType = '';
    }
}

function selectStatus(statusVal, statusName, leaveType) {
    currentStatusVal = statusVal;
    currentLeaveType = leaveType || '';

    highlightPill(statusName, leaveType);

    if (statusVal === '1' || statusVal === '2') {
        $('#punch_fields_wrap').slideDown(150);
        $('#punch_toggle_text').text('PUNCH IN DETAILS');
        if (statusVal === '2') {
            $('#drawer_clockout').val('14:00');
        } else {
            $('#drawer_clockout').val('18:30');
        }
    }
}

function togglePunchInFields() {
    $('#punch_fields_wrap').slideToggle(150, function() {
        if ($(this).is(':visible')) {
            $('#punch_toggle_text').text('HIDE PUNCH IN');
        } else {
            $('#punch_toggle_text').text('+ ADD PUNCH IN');
        }
    });
}

function saveAttendanceRecord() {
    if (!currentStaffId) return;

    var date = $('#drawer_date_input').val();
    if (!date) {
        alert('Please select a date.');
        return;
    }

    var $btn = $('#btn_save_attendance');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    var clockin = $('#drawer_clockin').val();
    var clockout = $('#drawer_clockout').val();
    var comments = $('#drawer_note').val();

    $.ajax({
        url: '<?= base_url() ?>admin/attendance/save_user_attendance',
        type: 'POST',
        data: {
            user_id: currentStaffId,
            date: date,
            status: currentStatusVal,
            leave_type: currentLeaveType,
            clockin_time: clockin,
            clockout_time: clockout,
            comments: comments
        },
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
            if (res.status === 'success') {
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message);
                } else {
                    alert(res.message);
                }
                closeAttendanceDrawer();
                // Reload report to refresh consolidated monthly tallies
                setTimeout(function() {
                    location.reload();
                }, 600);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(res.message);
                } else {
                    alert(res.message);
                }
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
            alert('Failed to save attendance. Please try again.');
        }
    });
}

function deleteAttendanceRecord() {
    if (!currentStaffId) return;
    if (!confirm('Are you sure you want to remove attendance record for this date?')) return;

    var date = $('#drawer_date_input').val();
    $.ajax({
        url: '<?= base_url() ?>admin/attendance/save_user_attendance',
        type: 'POST',
        data: {
            user_id: currentStaffId,
            date: date,
            status: 'delete'
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message);
                } else {
                    alert(res.message);
                }
                closeAttendanceDrawer();
                setTimeout(function() {
                    location.reload();
                }, 600);
            } else {
                alert(res.message);
            }
        }
    });
}

function convertTo24Hour(timeStr) {
    if (!timeStr) return '09:30';
    var time = timeStr.trim();
    if (time.indexOf(':') === -1) return '09:30';

    // If already 24-hr (e.g. 14:00)
    if (time.indexOf('AM') === -1 && time.indexOf('PM') === -1 && time.indexOf('am') === -1 && time.indexOf('pm') === -1) {
        var p = time.split(':');
        return p[0].padStart(2, '0') + ':' + p[1].substring(0, 2);
    }

    // 12-hr format (e.g. 09:30 AM)
    var isPM = (time.indexOf('PM') !== -1 || time.indexOf('pm') !== -1);
    var clean = time.replace(/[AaPp][Mm]/, '').trim();
    var parts = clean.split(':');
    var h = parseInt(parts[0], 10);
    var m = parts[1] ? parts[1].substring(0, 2) : '00';

    if (isPM && h < 12) h += 12;
    if (!isPM && h === 12) h = 0;

    return h.toString().padStart(2, '0') + ':' + m;
}
</script>

<script type="text/javascript">
function confirmClearAttendancePhotos() {
    if (!confirm('Are you sure you want to delete all stored attendance photos from the server folder?\n\nThis will permanently delete the .webp photo files to free up disk storage space.')) {
        return;
    }
    window.location.href = '<?= base_url() ?>admin/attendance/clear_attendance_photos';
}
</script>
