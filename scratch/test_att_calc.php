<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

$year = 2026;
$month = 10;
$start_date = sprintf('%04d-%02d-01', $year, $month);
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$end_date = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);

echo "Month: $start_date to $end_date ($days_in_month days)\n";

// 1. Get week off days from tbl_working_days
$non_working_days = [];
$res = $mysqli->query("SELECT d.day FROM tbl_working_days w JOIN tbl_days d ON d.day_id = w.day_id WHERE w.flag = 0");
while($r = $res->fetch_assoc()) {
    $non_working_days[] = strtolower($r['day']);
}
echo "Non working days of week: " . implode(', ', $non_working_days) . "\n";

// Count week offs in this month
$week_off_dates = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $cur_date = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $day_name = strtolower(date('l', strtotime($cur_date)));
    if (in_array($day_name, $non_working_days)) {
        $week_off_dates[$cur_date] = true;
    }
}
echo "Total week off days in month: " . count($week_off_dates) . "\n";

// 2. Public holidays in this month
$holiday_dates = [];
$res = $mysqli->query("SELECT start_date, end_date, event_name FROM tbl_holiday WHERE (start_date <= '$end_date' AND end_date >= '$start_date')");
while($r = $res->fetch_assoc()) {
    $s = max($start_date, $r['start_date']);
    $e = min($end_date, $r['end_date']);
    $cur = strtotime($s);
    $last = strtotime($e);
    while($cur <= $last) {
        $dt = date('Y-m-d', $cur);
        // If not already a week-off
        if (!isset($week_off_dates[$dt])) {
            $holiday_dates[$dt] = $r['event_name'];
        }
        $cur = strtotime('+1 day', $cur);
    }
}
echo "Total public holidays in month (excluding week-offs): " . count($holiday_dates) . "\n";
echo "Holidays: " . json_encode($holiday_dates) . "\n";

// 3. Check employees and their attendance
$users_query = "SELECT u.user_id, ad.fullname, ad.phone, ad.mobile, ad.emp_code, ad.employment_id, 
                d.designations, dep.deptname, ol.location_name as office_branch
                FROM tbl_users u
                LEFT JOIN tbl_account_details ad ON ad.user_id = u.user_id
                LEFT JOIN tbl_designations d ON d.designations_id = ad.designations_id
                LEFT JOIN tbl_departments dep ON dep.departments_id = d.departments_id
                LEFT JOIN tbl_office_locations ol ON ol.id = COALESCE(ad.office_location_id, ad.office_id)
                WHERE u.activated = 1 AND u.role_id != 2
                ORDER BY ad.fullname ASC LIMIT 10";
$users_res = $mysqli->query($users_query);

while($user = $users_res->fetch_assoc()) {
    $uid = $user['user_id'];
    
    // Attendances
    $att_res = $mysqli->query("SELECT date_in, attendance_status FROM tbl_attendance WHERE user_id = $uid AND date_in >= '$start_date' AND date_in <= '$end_date'");
    $present_cnt = 0;
    $half_cnt = 0;
    $absent_marked = 0;
    $att_dates = [];
    while($ar = $att_res->fetch_assoc()) {
        $att_dates[$ar['date_in']] = $ar['attendance_status'];
        if ($ar['attendance_status'] == 1) {
            $present_cnt++;
        } elseif ($ar['attendance_status'] == 2) {
            $half_cnt++;
        } elseif ($ar['attendance_status'] == 0) {
            $absent_marked++;
        }
    }
    
    // Approved Leaves
    $lv_res = $mysqli->query("SELECT leave_start_date, leave_end_date, leave_type FROM tbl_leave_application 
                              WHERE user_id = $uid AND application_status = 2 
                              AND (leave_start_date <= '$end_date' AND (leave_end_date >= '$start_date' OR leave_end_date IS NULL))");
    $leave_cnt = 0;
    $leave_dates = [];
    while($lr = $lv_res->fetch_assoc()) {
        $ls = max($start_date, $lr['leave_start_date']);
        $le = !empty($lr['leave_end_date']) ? min($end_date, $lr['leave_end_date']) : $ls;
        $c = strtotime($ls);
        $l = strtotime($le);
        while($c <= $l) {
            $ld = date('Y-m-d', $c);
            if (!isset($week_off_dates[$ld]) && !isset($holiday_dates[$ld])) {
                $leave_dates[$ld] = true;
                $leave_cnt++;
            }
            $c = strtotime('+1 day', $c);
        }
    }
    
    // Total days elapsed or in month
    // If current month (Oct 2026), days up to today (e.g. 25th) or full month:
    // Notice in the reference screenshot: Vennu Rakesh: Present = 0, Absent = 1, Half Day = 0, Week Off = 0, Holiday = 0.
    // If Vennu Rakesh was marked absent on 1 day (or total days elapsed minus present/leave/holiday/weekoff).
    // Let's see: Absent count:
    // If explicit absent_marked in tbl_attendance:
    // Or (days up to min(today, end_date) - present - half_day - leave - week_off - holiday)
    $today = '2026-10-25';
    $calc_end = ($end_date > $today) ? $today : $end_date;
    $total_days_so_far = (strtotime($calc_end) - strtotime($start_date)) / 86400 + 1;
    
    // Week offs up to calc_end
    $wo_so_far = 0;
    foreach($week_off_dates as $wod => $val) {
        if ($wod <= $calc_end) $wo_so_far++;
    }
    // Holidays up to calc_end
    $hol_so_far = 0;
    foreach($holiday_dates as $hd => $val) {
        if ($hd <= $calc_end) $hol_so_far++;
    }
    
    // Absent
    if ($absent_marked > 0) {
        $absent_cnt = $absent_marked;
    } else {
        $absent_cnt = max(0, $total_days_so_far - $present_cnt - $half_cnt - $leave_cnt - $wo_so_far - $hol_so_far);
    }
    
    echo sprintf("UID: %d | %s | Branch: %s | P: %d, A: %d, HD: %d, Lv: %d, WO: %d, Hol: %d\n",
        $uid, $user['fullname'], $user['office_branch'] ?? 'Main Office',
        $present_cnt, $absent_cnt, $half_cnt, $leave_cnt, count($week_off_dates), count($holiday_dates)
    );
}
