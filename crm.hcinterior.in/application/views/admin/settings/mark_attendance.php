<style>
    .att-tabs-nav {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 16px;
        padding-left: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .att-tabs-nav > li {
        margin-bottom: -2px;
    }
    .att-tabs-nav > li > a {
        display: inline-block;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        border-bottom: 3px solid transparent;
        text-decoration: none;
        transition: all .2s;
        border-radius: 4px 4px 0 0;
    }
    .att-tabs-nav > li > a:hover {
        color: #0284c7;
        background: #f1f5f9;
    }
    .att-tabs-nav > li.active > a,
    .att-tabs-nav > li.active > a:focus,
    .att-tabs-nav > li.active > a:hover {
        color: #0284c7;
        border-bottom-color: #0284c7;
        background: #fff;
        font-weight: 700;
    }
    .att-search-bar {
        margin-bottom: 15px;
    }
    .att-bulk-toolbar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 10px 16px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .att-bulk-left {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 22px;
    }
    .att-bulk-title {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .att-custom-check {
        display: inline-flex;
        align-items: center;
        margin: 0;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        user-select: none;
    }
    .att-custom-check input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        width: 0;
        height: 0;
    }
    .att-custom-check .att-box {
        width: 18px;
        height: 18px;
        border-radius: 3px;
        border: 2px solid #cbd5e1;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        transition: all .15s ease;
        flex-shrink: 0;
    }
    .att-custom-check .att-box i {
        font-size: 10px;
        color: #fff;
        display: none;
    }
    .att-custom-check input[type="checkbox"]:checked + .att-box {
        background: #0284c7;
        border-color: #0284c7;
    }
    .att-custom-check input[type="checkbox"]:checked + .att-box i {
        display: inline-block;
    }
    .att-custom-check.green {
        color: #16a34a;
    }
    .att-custom-check.green input[type="checkbox"]:checked + .att-box {
        background: #16a34a;
        border-color: #16a34a;
    }
    .att-custom-check.red {
        color: #dc2626;
    }
    .att-custom-check.red input[type="checkbox"]:checked + .att-box {
        background: #dc2626;
        border-color: #dc2626;
    }
    .att-custom-check.dark {
        color: #334155;
    }
    .att-custom-check.dark input[type="checkbox"]:checked + .att-box {
        background: #334155;
        border-color: #334155;
    }
    .att-avatar-wrap {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 38px;
        vertical-align: middle;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .att-avatar-wrap img {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .att-online-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
    }
    .att-online-dot.online {
        background-color: #16a34a;
    }
    .att-online-dot.offline {
        background-color: #cbd5e1;
    }
    @keyframes att-pulse {
        0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
        100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
    }
    .att-pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #16a34a;
        border-radius: 50%;
        margin-right: 5px;
        animation: att-pulse 1.8s infinite;
        vertical-align: middle;
    }
    .att-emp-title {
        font-weight: 700;
        font-size: 13px;
        color: #1e293b;
        display: block;
        line-height: 1.3;
    }
    .att-emp-sub {
        font-size: 11px;
        color: #64748b;
        display: block;
        line-height: 1.3;
    }
    .status-badge-lg {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 3px;
        display: inline-block;
        font-weight: 600;
        letter-spacing: .3px;
    }
</style>

<?php
// Pre-calculate statistics
$total_staff = !empty($users) ? count($users) : 0;
$count_logged_in = 0;
$count_not_logged_in = 0;
$count_clocked_out = 0;
$count_outside_logout = 0;
$count_on_leave = 0;

$clock_button_loc = '';
$allow_geo_clock_in = config_item('allow_geo_clock_in');
if (!empty($allow_geo_clock_in) && $allow_geo_clock_in == 'TRUE') {
    $clock_button_loc = 'clock_button_loc';
}

$processed_users = array();

if (!empty($users)) {
    foreach ($users as $nkey => $u) {
        $v_attendace_info = isset($attendace_info[$nkey]) ? $attendace_info[$nkey] : array();

        $already_clock = false;
        $currently_clock_in = false;
        $clock_id = null;
        $holiday_type = null;
        $clockin_time_display = '';
        $clockout_time_display = '';
        $total_hh = 0;
        $total_mm = 0;

        $is_outside_logout = false;
        $outside_info = null;

        $clockin_lat = null;
        $clockin_lng = null;
        $clockin_address = '';

        $clockout_lat = null;
        $clockout_lng = null;
        $clockout_address = '';
        $clockout_dist_str = '';

        // Check if user has an outside logout log in outside_logout_map
        if (!empty($outside_logout_map) && isset($outside_logout_map[$u->user_id])) {
            $is_outside_logout = true;
            $outside_info = $outside_logout_map[$u->user_id];
            $clockout_lat = $outside_info->lat;
            $clockout_lng = $outside_info->lng;
            $clockout_address = $outside_info->address;
            if (!empty($outside_info->distance_from_office)) {
                $dist_m = floatval($outside_info->distance_from_office);
                $clockout_dist_str = ($dist_m >= 1000) ? round($dist_m / 1000, 1) . ' km' : round($dist_m) . ' m';
            }
        }

        // Check unified punch in map (with live WebP photo)
        $clockin_photo = null;
        $clockin_log = null;
        if (!empty($punch_in_map) && isset($punch_in_map[$u->user_id])) {
            $in_log = $punch_in_map[$u->user_id];
            $clockin_lat = $in_log->lat;
            $clockin_lng = $in_log->lng;
            $clockin_address = $in_log->address;
            $clockin_log = $in_log;
            $p_src = !empty($in_log->photo_path) ? $in_log->photo_path : (!empty($in_log->photo_thumbnail) ? $in_log->photo_thumbnail : null);
            if (!empty($p_src)) {
                $clockin_photo = (strpos($p_src, 'uploads/') === 0) ? base_url($p_src) : $p_src;
            }
        }

        // Check unified punch out map
        if (!empty($punch_out_map) && isset($punch_out_map[$u->user_id])) {
            $out_log = $punch_out_map[$u->user_id];
            if (empty($clockout_lat)) {
                $clockout_lat = $out_log->lat;
                $clockout_lng = $out_log->lng;
                $clockout_address = $out_log->address;
            }
            if (empty($clockout_dist_str) && !empty($out_log->distance_from_office)) {
                $dist_m = floatval($out_log->distance_from_office);
                $clockout_dist_str = ($dist_m >= 1000) ? round($dist_m / 1000, 1) . ' km' : round($dist_m) . ' m';
            }
        }

        if (!empty($v_attendace_info)) {
            foreach ($v_attendace_info as $v_mytime) {
                // Check tbl_clock coordinates for clock-in (login)
                if (!empty($v_mytime->latitude) && !empty($v_mytime->longitude)) {
                    if (empty($clockin_lat)) {
                        $clockin_lat = $v_mytime->latitude;
                        $clockin_lng = $v_mytime->longitude;
                        if (!empty($v_mytime->location)) {
                            $clockin_address = $v_mytime->location;
                        }
                    }
                }

                // Check tbl_clock coordinates for clock-out (logout)
                if (!empty($v_mytime->clockout_latitude) && !empty($v_mytime->clockout_longitude)) {
                    if (empty($clockout_lat)) {
                        $clockout_lat = $v_mytime->clockout_latitude;
                        $clockout_lng = $v_mytime->clockout_longitude;
                        if (!empty($v_mytime->clockout_location)) {
                            $clockout_address = $v_mytime->clockout_location;
                        }
                    }
                }

                // Check tbl_clock comments or location for outside logout
                if (!empty($v_mytime->comments) && (stripos($v_mytime->comments, 'outside') !== false || stripos($v_mytime->comments, 'outsider') !== false)) {
                    $is_outside_logout = true;
                    if (empty($outside_info)) {
                        $outside_info = (object)[
                            'punch_time'           => !empty($v_mytime->clockout_time) ? $v_mytime->clockout_time : '',
                            'distance_from_office' => null,
                            'lat'                  => !empty($v_mytime->clockout_latitude) ? $v_mytime->clockout_latitude : (!empty($v_mytime->latitude) ? $v_mytime->latitude : null),
                            'lng'                  => !empty($v_mytime->clockout_longitude) ? $v_mytime->clockout_longitude : (!empty($v_mytime->longitude) ? $v_mytime->longitude : null),
                            'address'              => !empty($v_mytime->clockout_location) ? $v_mytime->clockout_location : (!empty($v_mytime->location) ? $v_mytime->location : $v_mytime->comments)
                        ];
                    }
                }

                if ($v_mytime->attendance_status == 1 || $v_mytime->attendance_status == 2) {
                    if (!empty($v_mytime->clockout_time) && $v_mytime->clockout_time !== '00:00:00') {
                        $startdatetime = strtotime($v_mytime->date_in . " " . $v_mytime->clockin_time);
                        $enddatetime = strtotime($v_mytime->date_out . " " . $v_mytime->clockout_time);
                        $difference = $enddatetime - $startdatetime;

                        $years = abs(floor($difference / 31536000));
                        $days = abs(floor(($difference - ($years * 31536000)) / 86400));
                        $hours = abs(floor(($difference - ($years * 31536000) - ($days * 86400)) / 3600));
                        $mins = abs(floor(($difference - ($years * 31536000) - ($days * 86400)) - ($hours * 3600)) / 60);
                        $total_mm += $mins;
                        $total_hh += $hours;
                        $already_clock = true;
                        $clockout_time_display = date('h:i A', strtotime($v_mytime->clockout_time));
                    } elseif ((int)$v_mytime->clocking_status === 1) {
                        $currently_clock_in = true;
                        $clock_id = $v_mytime->clock_id;
                        if (!empty($v_mytime->clockin_time)) {
                            $clockin_time_display = date('h:i A', strtotime($v_mytime->clockin_time));
                        }
                    }
                } elseif ($v_mytime->attendance_status == 'H') {
                    $holiday_type = 'holiday';
                } elseif ($v_mytime->attendance_status == '3') {
                    $holiday_type = 'on_leave';
                } elseif ($v_mytime->attendance_status == '0') {
                    $holiday_type = 'absent';
                }
            }
        }

        // Count outside logout
        if ($is_outside_logout) {
            $count_outside_logout++;
        }

        // Determine main status
        if ($currently_clock_in) {
            $status_key = 'logged_in';
            $status_label = 'Logged In';
            $count_logged_in++;
            $checkbox_name = 'clock_out';
        } elseif ($already_clock) {
            $status_key = 'clocked_out';
            $status_label = 'Clocked Out';
            $count_clocked_out++;
            $checkbox_name = 'clock_in';
        } elseif ($holiday_type == 'on_leave') {
            $status_key = 'on_leave';
            $status_label = 'On Leave';
            $count_on_leave++;
            $checkbox_name = 'clock_in';
        } elseif ($holiday_type == 'holiday') {
            $status_key = 'holiday';
            $status_label = 'Holiday';
            $count_on_leave++;
            $checkbox_name = 'clock_in';
        } else {
            $status_key = 'not_logged_in';
            $status_label = 'Not Logged In';
            $count_not_logged_in++;
            $checkbox_name = 'clock_in';
        }

        if ($total_mm > 59) {
            $total_hh += intval($total_mm / 60);
            $total_mm = intval($total_mm % 60);
        }

        if (empty($clockout_lat) && !empty($u->last_logout_lat) && !empty($u->last_logout_time)) {
            if (date('Y-m-d', strtotime($u->last_logout_time)) == $date) {
                $clockout_lat = $u->last_logout_lat;
                $clockout_lng = $u->last_logout_long;
                $clockout_address = !empty($u->last_logout_location) ? $u->last_logout_location : 'App Logout';
            }
        }

        $processed_users[] = array(
            'user'               => $u,
            'status_key'         => $status_key,
            'status_label'       => $status_label,
            'currently_clock_in' => $currently_clock_in,
            'already_clock'      => $already_clock,
            'is_outside_logout'  => $is_outside_logout,
            'outside_info'       => $outside_info,
            'holiday_type'       => $holiday_type,
            'clock_id'           => $clock_id,
            'checkbox_name'      => $checkbox_name,
            'clockin_time'       => $clockin_time_display,
            'clockout_time'      => $clockout_time_display,
            'clockin_lat'        => $clockin_lat,
            'clockin_lng'        => $clockin_lng,
            'clockin_address'    => $clockin_address,
            'clockin_photo'      => $clockin_photo,
            'clockin_log'        => $clockin_log,
            'clockout_lat'       => $clockout_lat,
            'clockout_lng'       => $clockout_lng,
            'clockout_address'   => $clockout_address,
            'clockout_dist_str'  => $clockout_dist_str,
            'total_hh'           => $total_hh,
            'total_mm'           => $total_mm,
        );
    }
}
?>

<!-- Main Attendance & Clock Management Form -->
<form action="<?= base_url() ?>admin/dashboard/update_clock" method="post" id="attendance_bulk_form">
    <div class="panel panel-custom">
        <!-- Clean, Balanced Panel Header -->
        <div class="panel-heading" style="padding: 12px 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <!-- Left Title -->
                <div>
                    <strong style="font-size: 16px; color: #fff;">
                        <i class="fa fa-calendar-check-o"></i> <?= lang('mark_attendance'); ?>
                    </strong>
                    <span style="color: #cbd5e1; font-size: 13px; font-weight: normal; margin-left: 10px;">
                        <i class="fa fa-calendar"></i> <?= date('d M Y, l', strtotime($date)); ?>
                    </span>
                </div>

                <!-- Right Date Filter & Top Update Button -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <span class="input-group-addon" style="background: #fff; padding: 4px 8px;"><i class="fa fa-calendar text-muted"></i></span>
                        <input type="text" id="attend_date_picker" class="form-control datepicker" value="<?= $date; ?>" data-format="yyyy-mm-dd" style="font-weight: 600; height: 31px;" placeholder="Select Date">
                        <span class="input-group-btn">
                            <button type="button" id="btn_change_date" class="btn btn-sm btn-info" style="font-weight: 600; height: 31px;" title="Filter by date">
                                <i class="fa fa-filter"></i> Date
                            </button>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary" style="font-weight: 700; height: 31px; padding: 4px 16px;">
                        <i class="fa fa-refresh"></i> <?= lang('update') ?>
                    </button>
                    <button type="button" onclick="confirmClearAttendancePhotos()" class="btn btn-sm btn-danger" style="font-weight: 700; height: 31px; padding: 4px 12px; background: #ef4444; border: none; box-shadow: 0 1px 2px rgba(239,68,68,0.3);" title="Delete all photos from server folder to free storage space">
                        <i class="fa fa-trash-o"></i> Clear Photos
                    </button>
                </div>
            </div>
        </div>

        <div class="panel-body" style="padding: 18px;">
            <!-- Tabs Navigation ("tab bna dena alag alag") -->
            <ul class="att-tabs-nav clearfix">
                <li class="active">
                    <a href="javascript:void(0);" class="att-tab-btn" data-status="all">
                        <i class="fa fa-users text-primary"></i> All Staff
                        <span class="badge" style="background: #0284c7; margin-left: 5px; font-weight: 600;"><?= $total_staff; ?></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="att-tab-btn" data-status="logged_in">
                        <span class="att-pulse-dot"></span> Logged In / Clocked In
                        <span class="badge" style="background: #16a34a; margin-left: 5px; font-weight: 600;"><?= $count_logged_in; ?></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="att-tab-btn" data-status="not_logged_in">
                        <i class="fa fa-times-circle text-danger"></i> Not Logged In
                        <span class="badge" style="background: #dc2626; margin-left: 5px; font-weight: 600;"><?= $count_not_logged_in; ?></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="att-tab-btn" data-status="clocked_out">
                        <i class="fa fa-sign-out text-purple"></i> Clocked Out
                        <span class="badge" style="background: #7c3aed; margin-left: 5px; font-weight: 600;"><?= $count_clocked_out; ?></span>
                    </a>
                </li>
                <!-- Dedicated Outside Logout Tab -->
                <li>
                    <a href="javascript:void(0);" class="att-tab-btn" data-status="outside_logout" title="Staff who clocked out from outside office location">
                        <i class="fa fa-map-marker text-warning"></i> 📍 Outside Logout
                        <span class="badge" style="background: #f59e0b; margin-left: 5px; font-weight: 600;"><?= $count_outside_logout; ?></span>
                    </a>
                </li>
                <?php if ($count_on_leave > 0) : ?>
                <li>
                    <a href="javascript:void(0);" class="att-tab-btn" data-status="on_leave,holiday">
                        <i class="fa fa-calendar-times-o text-warning"></i> Leave / Holiday
                        <span class="badge" style="background: #ea580c; margin-left: 5px; font-weight: 600;"><?= $count_on_leave; ?></span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Search Bar with Search Button ("search bhi rehga button ok") -->
            <div class="att-search-bar">
                <div class="row">
                    <div class="col-md-7 col-sm-8">
                        <div class="input-group">
                            <span class="input-group-addon" style="background: #fff; border-right: none;">
                                <i class="fa fa-search text-muted"></i>
                            </span>
                            <input type="text" id="attendance_search_input" class="form-control" placeholder="Search by name, employee ID, department, designation..." style="border-left: none; height: 38px; font-size: 13px;">
                            <span class="input-group-btn">
                                <button type="button" id="btn_attendance_search" class="btn btn-primary" style="height: 38px; padding: 0 18px; font-weight: 600;">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button type="button" id="btn_attendance_clear" class="btn btn-default" style="height: 38px; padding: 0 14px;" title="Clear Search">
                                    <i class="fa fa-times text-muted"></i> Clear
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-5 col-sm-4 text-right" style="padding-top: 9px;">
                        <span id="attendance_count_display" style="font-weight: 600; color: #475569; font-size: 13px;">
                            Showing <?= $total_staff; ?> of <?= $total_staff; ?> staff members
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dedicated Bulk Selection & Action Toolbar -->
            <div class="att-bulk-toolbar">
                <div class="att-bulk-left">
                    <span class="att-bulk-title">
                        <i class="fa fa-tasks text-primary"></i> Bulk Actions:
                    </span>

                    <label class="att-custom-check green" title="<?= lang('mark_as_clock_in_help') ?>">
                        <input id="select_all_clock_in" class="clock_in_toggle" type="checkbox">
                        <span class="att-box"><i class="fa fa-check"></i></span>
                        <?= lang('mark_as_clock_in') ?>
                    </label>

                    <label class="att-custom-check red" title="<?= lang('mark_as_clock_out_help') ?>">
                        <input id="select_all_clock_out" class="clock_out_toggle" type="checkbox">
                        <span class="att-box"><i class="fa fa-check"></i></span>
                        <?= lang('mark_as_clock_out') ?>
                    </label>

                    <label class="att-custom-check dark" title="<?= lang('mark_all_help') ?>">
                        <input id="select_all" type="checkbox">
                        <span class="att-box"><i class="fa fa-check"></i></span>
                        <?= lang('mark_all') ?>
                    </label>
                </div>

                <div class="att-bulk-right">
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 6px 18px; border-radius: 4px;">
                        <i class="fa fa-refresh"></i> <?= lang('update') ?> Attendance
                    </button>
                </div>
            </div>

            <!-- Attendance Data Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="Transation_DataTables" style="margin-bottom: 0;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .5px;">
                            <th style="width: 45px; text-align: center;">#</th>
                            <th style="min-width: 220px;"><?= lang('name') ?></th>
                            <th style="min-width: 130px;"><?= lang('emp_id') ?></th>
                            <th style="min-width: 160px;"><?= lang('department') ?> & Role</th>
                            <th style="min-width: 150px; text-align: center;">Attendance Status</th>
                            <th style="width: 90px; text-align: center;">Punch In Photo</th>
                            <th style="min-width: 140px; text-align: center;">Punch In (Login)</th>
                            <th style="min-width: 140px; text-align: center;">Punch Out (Logout)</th>
                            <th style="min-width: 120px; text-align: center;"><?= lang('clocking_hours') ?></th>
                            <th style="min-width: 110px; text-align: center;">Admin Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($processed_users)) : ?>
                            <?php foreach ($processed_users as $p_item) :
                                $u = $p_item['user'];
                                $status_key = $p_item['status_key'];
                                $status_label = $p_item['status_label'];
                                $currently_clock_in = $p_item['currently_clock_in'];
                                $already_clock = $p_item['already_clock'];
                                $is_outside_logout = !empty($p_item['is_outside_logout']);
                                $outside_info = !empty($p_item['outside_info']) ? $p_item['outside_info'] : null;
                                $holiday_type = $p_item['holiday_type'];
                                $clock_id = $p_item['clock_id'];
                                $chk_name = $p_item['checkbox_name'];
                                $clockin_time = $p_item['clockin_time'];
                                $clockout_time = $p_item['clockout_time'];
                                $clockin_lat = !empty($p_item['clockin_lat']) ? $p_item['clockin_lat'] : null;
                                $clockin_lng = !empty($p_item['clockin_lng']) ? $p_item['clockin_lng'] : null;
                                $clockin_address = !empty($p_item['clockin_address']) ? $p_item['clockin_address'] : '';
                                $clockout_lat = !empty($p_item['clockout_lat']) ? $p_item['clockout_lat'] : null;
                                $clockout_lng = !empty($p_item['clockout_lng']) ? $p_item['clockout_lng'] : null;
                                $clockout_address = !empty($p_item['clockout_address']) ? $p_item['clockout_address'] : '';
                                $clockout_dist_str = !empty($p_item['clockout_dist_str']) ? $p_item['clockout_dist_str'] : '';
                                $total_hh = $p_item['total_hh'];
                                $total_mm = $p_item['total_mm'];

                                // Search string combining all searchable text
                                $search_haystack = strtolower(trim(
                                    $u->fullname . ' ' .
                                    $u->employment_id . ' ' .
                                    $u->email . ' ' .
                                    (!empty($u->deptname) ? $u->deptname : '') . ' ' .
                                    (!empty($u->designations) ? $u->designations : '') . ' ' .
                                    $status_label . ' ' .
                                    $clockin_address . ' ' .
                                    $clockout_address . ' ' .
                                    ($is_outside_logout ? 'outside logout bahar se punch out field logout' : '')
                                ));

                                $avatar_img = !empty($u->avatar) ? base_url($u->avatar) : base_url('uploads/default_avatar.jpg');
                                $row_status_str = $status_key . ($is_outside_logout ? ',outside_logout' : '');
                            ?>
                                <tr data-status="<?= $row_status_str ?>" data-search-text="<?= htmlspecialchars($search_haystack, ENT_QUOTES) ?>">
                                    <!-- Custom Checkbox for Bulk Update -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <label class="att-custom-check" style="margin: 0;">
                                            <input type="checkbox" class="<?= $chk_name ?>" name="<?= $chk_name ?>[]" value="<?= $u->user_id; ?>">
                                            <span class="att-box" style="margin-right: 0;"><i class="fa fa-check"></i></span>
                                        </label>
                                        <input type="hidden" name="<?= $u->user_id; ?>" value="<?= $clock_id; ?>">
                                    </td>

                                    <!-- Employee Info -->
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; align-items: center;">
                                            <div class="att-avatar-wrap">
                                                <img src="<?= $avatar_img ?>" alt="<?= htmlspecialchars($u->fullname) ?>" onerror="this.src='<?= base_url('uploads/default_avatar.jpg') ?>'">
                                            </div>
                                            <div>
                                                <span class="att-emp-title"><?= htmlspecialchars($u->fullname); ?></span>
                                                <span class="att-emp-sub"><i class="fa fa-envelope-o text-muted"></i> <?= htmlspecialchars($u->email); ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Emp ID -->
                                    <td style="vertical-align: middle;">
                                        <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; padding: 4px 8px;">
                                            <?= !empty($u->employment_id) ? htmlspecialchars($u->employment_id) : 'EMP-' . $u->user_id ?>
                                        </span>
                                    </td>

                                    <!-- Department & Role -->
                                    <td style="vertical-align: middle;">
                                        <div style="font-weight: 600; color: #334155; font-size: 12px;">
                                            <i class="fa fa-building-o text-muted"></i> <?= !empty($u->deptname) ? htmlspecialchars($u->deptname) : 'General' ?>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b;">
                                            <i class="fa fa-id-badge text-muted"></i> <?= !empty($u->designations) ? htmlspecialchars($u->designations) : 'Staff' ?>
                                        </div>
                                    </td>

                                    <!-- Attendance Status ("kon login hai or kon nhi") -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php if ($currently_clock_in) : ?>
                                            <span class="status-badge-lg" style="background: #16a34a; color: #fff;">
                                                <span class="att-pulse-dot" style="background: #fff; width: 6px; height: 6px;"></span> Logged In
                                            </span>
                                            <div style="font-size: 11px; color: #16a34a; margin-top: 3px; font-weight: 600;">
                                                Present (Clocked In)
                                            </div>
                                        <?php elseif ($already_clock) : ?>
                                            <span class="status-badge-lg" style="background: #7c3aed; color: #fff;">
                                                <i class="fa fa-sign-out"></i> Clocked Out
                                            </span>
                                            <?php if ($is_outside_logout) : ?>
                                                <div style="font-size: 11px; color: #d97706; margin-top: 3px; font-weight: 600;">
                                                    <i class="fa fa-map-marker"></i> Outside Office
                                                </div>
                                            <?php else : ?>
                                                <div style="font-size: 11px; color: #7c3aed; margin-top: 3px;">
                                                    Shift Completed
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($holiday_type == 'on_leave') : ?>
                                            <span class="status-badge-lg" style="background: #ea580c; color: #fff;">
                                                <i class="fa fa-plane"></i> On Leave
                                            </span>
                                        <?php elseif ($holiday_type == 'holiday') : ?>
                                            <span class="status-badge-lg" style="background: #0284c7; color: #fff;">
                                                <i class="fa fa-calendar"></i> Holiday
                                            </span>
                                        <?php else : ?>
                                            <span class="status-badge-lg" style="background: #dc2626; color: #fff;">
                                                <i class="fa fa-times-circle"></i> Not Logged In
                                            </span>
                                            <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">
                                                Absent Today
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Punch In Photo Column -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php if (!empty($p_item['clockin_photo'])) : 
                                            $is_in_office = !empty($p_item['clockin_log']) && !empty($p_item['clockin_log']->is_inside_geofence);
                                            $border_clr = $is_in_office ? '#10b981' : '#f59e0b';
                                            $badge_lbl = $is_in_office ? 'Office' : 'Field';
                                        ?>
                                            <a href="javascript:void(0)" onclick="viewPunchPhotoModal('<?= htmlspecialchars($p_item['clockin_photo']) ?>', '<?= htmlspecialchars(addslashes($u->fullname)) ?>', '<?= htmlspecialchars($clockin_time) ?>', 'IN', '<?= htmlspecialchars(addslashes($clockin_address)) ?>', '<?= $is_in_office ? 'Office Range' : 'Field Punch' ?>')" title="Click to view verified Punch In Photo">
                                                <div style="position: relative; display: inline-block;">
                                                    <img src="<?= htmlspecialchars($p_item['clockin_photo']) ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 2px solid <?= $border_clr ?>; box-shadow: 0 2px 4px rgba(0,0,0,0.12); cursor: pointer;" alt="Photo">
                                                    <span class="badge" style="position: absolute; bottom: -4px; right: -4px; font-size: 8px; padding: 2px 4px; background: <?= $border_clr ?>; color: #fff;">
                                                        <?= $badge_lbl ?>
                                                    </span>
                                                </div>
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted" style="font-size: 11px;" title="No Punch In photo recorded">
                                                <i class="fa fa-camera" style="opacity: 0.3; font-size: 16px;"></i>
                                                <div style="font-size: 9px; color: #9ca3af; margin-top: 2px;">No Photo</div>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Punch In (Login) Time & Location -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php if (!empty($clockin_time)) : ?>
                                            <span class="text-success" style="font-weight: 700; font-size: 12px;">
                                                <i class="fa fa-sign-in"></i> <?= $clockin_time ?>
                                            </span>
                                            <?php if (!empty($clockin_lat) && !empty($clockin_lng)) : ?>
                                                <div style="margin-top: 4px;">
                                                    <?php if (!empty($clockin_address)) : ?>
                                                        <div style="font-size: 11px; color: #1e293b; font-weight: 600; line-height: 1.2; max-width: 170px; margin: 0 auto;" title="<?= htmlspecialchars($clockin_address) ?>">
                                                            <i class="fa fa-map-marker text-success"></i> <?= htmlspecialchars(mb_strimwidth($clockin_address, 0, 24, '...')) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div style="font-size: 10px; color: #64748b; font-family: monospace; margin-top: 1px;">
                                                        <?= round(floatval($clockin_lat), 4) ?>, <?= round(floatval($clockin_lng), 4) ?>
                                                    </div>
                                                    <a href="https://maps.google.com/?q=<?= $clockin_lat ?>,<?= $clockin_lng ?>" target="_blank" class="btn btn-xs btn-default" style="font-size: 10px; padding: 1px 7px; color: #0284c7; border-color: #cbd5e1; margin-top: 3px; font-weight: 600;" title="View Login Location on Google Maps">
                                                        <i class="fa fa-external-link text-primary"></i> Map
                                                    </a>
                                                </div>
                                            <?php else : ?>
                                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                                    <i class="fa fa-map-marker text-muted"></i> No GPS
                                                </div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Punch Out (Logout) Time & Location -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php if (!empty($clockout_time)) : ?>
                                            <span class="text-purple" style="font-weight: 700; font-size: 12px; color: #7c3aed;">
                                                <i class="fa fa-sign-out"></i> <?= $clockout_time ?>
                                            </span>
                                            <?php if ($is_outside_logout) : ?>
                                                <div style="margin-top: 3px;">
                                                    <span class="label" style="background: #f59e0b; color: #fff; font-size: 10px; padding: 2px 6px; font-weight: 600; border-radius: 2px;" title="Logged out from outside office location">
                                                        <i class="fa fa-map-marker"></i> Outside Logout
                                                    </span>
                                                    <?php 
                                                        $dist_str = !empty($clockout_dist_str) ? $clockout_dist_str : '';
                                                        if (empty($dist_str)) {
                                                            if (!empty($outside_info->distance_from_office)) {
                                                                $dist_str = ($outside_info->distance_from_office >= 1000) ? round($outside_info->distance_from_office / 1000, 1) . ' km' : round($outside_info->distance_from_office) . ' m';
                                                            } elseif (!empty($outside_info->address) && preg_match('/\(~([0-9]+)m\)/', $outside_info->address, $matches)) {
                                                                $dist_m = intval($matches[1]);
                                                                $dist_str = ($dist_m >= 1000) ? round($dist_m / 1000, 1) . ' km' : $dist_m . ' m';
                                                            }
                                                        }
                                                    ?>
                                                    <?php if (!empty($dist_str)) : ?>
                                                        <div style="font-size: 10px; color: #b45309; font-weight: 600; margin-top: 1px;">
                                                            ~<?= $dist_str ?> from office
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($clockout_lat) && !empty($clockout_lng)) : ?>
                                                <div style="margin-top: 4px;">
                                                    <?php if (!empty($clockout_address)) : ?>
                                                        <div style="font-size: 11px; color: #1e293b; font-weight: 600; line-height: 1.2; max-width: 170px; margin: 0 auto;" title="<?= htmlspecialchars($clockout_address) ?>">
                                                            <i class="fa fa-map-marker text-danger"></i> <?= htmlspecialchars(mb_strimwidth($clockout_address, 0, 24, '...')) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div style="font-size: 10px; color: #64748b; font-family: monospace; margin-top: 1px;">
                                                        <?= round(floatval($clockout_lat), 4) ?>, <?= round(floatval($clockout_lng), 4) ?>
                                                    </div>
                                                    <a href="https://maps.google.com/?q=<?= $clockout_lat ?>,<?= $clockout_lng ?>" target="_blank" class="btn btn-xs btn-default" style="font-size: 10px; padding: 1px 7px; color: #7c3aed; border-color: #cbd5e1; margin-top: 3px; font-weight: 600;" title="View Logout Location on Google Maps">
                                                        <i class="fa fa-external-link text-purple"></i> Map
                                                    </a>
                                                </div>
                                            <?php elseif (!empty($outside_info->lat) && !empty($outside_info->lng)) : ?>
                                                <div style="margin-top: 4px;">
                                                    <a href="https://maps.google.com/?q=<?= $outside_info->lat ?>,<?= $outside_info->lng ?>" target="_blank" class="btn btn-xs btn-default" style="font-size: 10px; padding: 1px 7px; color: #7c3aed; border-color: #cbd5e1; margin-top: 3px; font-weight: 600;" title="View Logout Location on Google Maps">
                                                        <i class="fa fa-external-link text-purple"></i> Map
                                                    </a>
                                                </div>
                                            <?php else : ?>
                                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                                    <i class="fa fa-map-marker text-muted"></i> No GPS
                                                </div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Worked Hours -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php if ($already_clock || $currently_clock_in) : ?>
                                            <span class="badge" style="background: #e2e8f0; color: #1e293b; font-size: 12px; font-weight: 700; padding: 4px 8px;">
                                                <?= $total_hh ?>h : <?= str_pad($total_mm, 2, '0', STR_PAD_LEFT) ?>m
                                            </span>
                                        <?php else : ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Single Action Button (Clock In / Clock Out) -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <?php if (!empty($currently_clock_in)) : ?>
                                            <a class="btn btn-xs btn-danger <?= $clock_button_loc ?> clock_in_button" href="<?= base_url() ?>admin/dashboard/set_clocking/<?= $clock_id ?>/<?= $u->user_id ?>" style="padding: 4px 10px; font-weight: 600;" title="Clock out this staff">
                                                <i class="fa fa-sign-out"></i> <?= lang('clock_out') ?>
                                            </a>
                                        <?php else : ?>
                                            <a class="btn btn-xs btn-success <?= $clock_button_loc ?> clock_in_button" href="<?= base_url() ?>admin/dashboard/set_clocking/0/<?= $u->user_id ?>/1" style="padding: 4px 10px; font-weight: 600;" title="Clock in this staff">
                                                <i class="fa fa-sign-in"></i> <?= lang('clock_in') ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center" style="padding: 30px; color: #888;">
                                    <i class="fa fa-info-circle fa-2x"></i><br>
                                    No staff records found for the selected date.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
$(document).ready(function () {
    // 1. Initialize DataTable
    var attendanceTable = $('#Transation_DataTables').DataTable({
        paging: false,
        bSort: false,
        info: false,
        dom: 't' // Use our custom search bar and tabs
    });

    var currentTabStatus = 'all';
    var currentSearchQuery = '';

    // Custom filtering for both Tabs and Search Query
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'Transation_DataTables') {
                return true;
            }
            var rowNode = $(attendanceTable.row(dataIndex).node());
            var rowStatus = rowNode.attr('data-status') || '';
            var rowSearch = rowNode.attr('data-search-text') || '';

            // Tab Filter (supports comma-separated multi-statuses e.g. "clocked_out,outside_logout")
            if (currentTabStatus !== 'all') {
                var rowStatuses = rowStatus.split(',');
                var validStatuses = currentTabStatus.split(',');
                var match = false;
                for (var s = 0; s < validStatuses.length; s++) {
                    if ($.inArray(validStatuses[s], rowStatuses) !== -1) {
                        match = true;
                        break;
                    }
                }
                if (!match) {
                    return false;
                }
            }

            // Search Filter
            if (currentSearchQuery !== '') {
                if (rowSearch.indexOf(currentSearchQuery) === -1) {
                    return false;
                }
            }

            return true;
        }
    );

    function updateCounters() {
        var visibleCount = attendanceTable.rows({ filter: 'applied' }).count();
        var totalCount = attendanceTable.rows().count();
        $('#attendance_count_display').html('Showing <strong>' + visibleCount + '</strong> of <strong>' + totalCount + '</strong> staff members');
    }

    function executeSearch() {
        currentSearchQuery = $('#attendance_search_input').val().toLowerCase().trim();
        attendanceTable.draw();
        updateCounters();
    }

    // 2. Tab Navigation Click Handler
    $('.att-tab-btn').on('click', function (e) {
        e.preventDefault();
        $('.att-tab-btn').parent().removeClass('active');
        $(this).parent().addClass('active');

        currentTabStatus = $(this).attr('data-status');
        attendanceTable.draw();
        updateCounters();
    });

    // 3. Search Button Click Handler ("search bhi rehga button ok")
    $('#btn_attendance_search').on('click', function () {
        executeSearch();
    });

    // Live search on keyup + Enter key support
    $('#attendance_search_input').on('keyup', function (e) {
        executeSearch();
    });

    // Clear Search Button
    $('#btn_attendance_clear').on('click', function () {
        $('#attendance_search_input').val('');
        currentSearchQuery = '';
        attendanceTable.draw();
        updateCounters();
    });

    // 4. Bulk Checkboxes Handling (Only apply to currently visible rows)
    $('#select_all').change(function () {
        var isChecked = this.checked;
        $('#Transation_DataTables tbody tr:visible').find('input[type="checkbox"]').prop('checked', isChecked);
    });

    $('#select_all_clock_in').change(function () {
        var isChecked = this.checked;
        $('#Transation_DataTables tbody tr:visible').find('.clock_in').prop('checked', isChecked);
        if (isChecked) {
            $('#select_all_clock_out').prop('checked', false);
            $('#Transation_DataTables tbody tr:visible').find('.clock_out').prop('checked', false);
        }
    });

    $('#select_all_clock_out').change(function () {
        var isChecked = this.checked;
        $('#Transation_DataTables tbody tr:visible').find('.clock_out').prop('checked', isChecked);
        if (isChecked) {
            $('#select_all_clock_in').prop('checked', false);
            $('#Transation_DataTables tbody tr:visible').find('.clock_in').prop('checked', false);
        }
    });

    // 5. Date Change Button Handler
    $('#btn_change_date').on('click', function () {
        var selectedDate = $('#attend_date_picker').val();
        if (selectedDate) {
            var form = $('<form action="<?= base_url() ?>admin/mark_attendance" method="post">' +
                         '<input type="hidden" name="attend_date" value="' + selectedDate + '">' +
                         '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // 6. Action Button Loading State
    $(".clock_in_button").on("click", function () {
        var ubtn = $(this);
        ubtn.html('<i class="fa fa-spinner fa-spin"></i> <?= lang('please_wait') ?>...');
        ubtn.addClass('disabled');
    });

    // Initial counter update
    updateCounters();
});
</script>
<!-- Full-Size Attendance Photo Modal -->
<div class="modal fade" id="punchPhotoModal" tabindex="-1" role="dialog" style="z-index: 100000;">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 380px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);">
            <div class="modal-header" style="background: #1e293b; color: #ffffff; padding: 12px 16px; border: none;">
                <button type="button" class="close" data-dismiss="modal" style="color: #ffffff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title" style="font-size: 14px; font-weight: 700;" id="photoModalStaffName">Punch In Verification Photo</h4>
            </div>
            <div class="modal-body text-center" style="padding: 16px; background: #0f172a;">
                <img id="photoModalImg" src="" style="width: 100%; max-height: 380px; object-fit: contain; border-radius: 8px; border: 1px solid #334155;" alt="Punch Photo">
                <div style="margin-top: 12px; text-align: left; background: #1e293b; padding: 10px 14px; border-radius: 8px; color: #e2e8f0; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-muted">Punch Time:</span>
                        <strong id="photoModalTime" style="color: #38bdf8;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-muted">Type:</span>
                        <strong id="photoModalType" class="text-success">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-muted">Geofence:</span>
                        <span id="photoModalGeofence" class="badge" style="background: #10b981;">-</span>
                    </div>
                    <div style="margin-top: 6px; font-size: 11px; color: #94a3b8; word-break: break-word;" id="photoModalLoc"></div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 8px 16px; background: #1e293b; border-top: 1px solid #334155; text-align: right;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="border-radius: 6px;">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function viewPunchPhotoModal(url, name, time, type, loc, geofence) {
    if (!url) return;
    $('#photoModalStaffName').text(name || 'Punch In Photo');
    $('#photoModalImg').attr('src', url);
    $('#photoModalTime').text(time || '-');
    $('#photoModalType').text(type || 'CLOCK IN');
    $('#photoModalGeofence').text(geofence || 'Office Range');
    if (geofence && geofence.indexOf('Field') !== -1) {
        $('#photoModalGeofence').css('background', '#f59e0b');
    } else {
        $('#photoModalGeofence').css('background', '#10b981');
    }
    $('#photoModalLoc').text(loc ? 'Location: ' + loc : '');
    $('#punchPhotoModal').modal('show');
}

function confirmClearAttendancePhotos() {
    if (!confirm('Are you sure you want to delete all stored attendance photos from the server folder?\n\nThis will permanently delete the .webp photo files to free up disk storage space.')) {
        return;
    }
    window.location.href = '<?= base_url() ?>admin/dashboard/clear_attendance_photos';
}
</script>
