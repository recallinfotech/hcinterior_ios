<?php
$mysqli = new mysqli('localhost', 'root', '', 'hcinterior_git');

$t0 = microtime(true);

$year = 2026;
$month = 10;
$start_date = sprintf('%04d-%02d-01', $year, $month);
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$end_date = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);

// 1. Week-offs
$non_working_days = [];
$res = $mysqli->query("SELECT d.day FROM tbl_working_days w JOIN tbl_days d ON d.day_id = w.day_id WHERE w.flag = 0");
while($r = $res->fetch_assoc()) {
    $non_working_days[] = strtolower($r['day']);
}
if (empty($non_working_days)) {
    $non_working_days = ['sunday'];
}

$week_off_dates = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $cur_date = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $day_name = strtolower(date('l', strtotime($cur_date)));
    if (in_array($day_name, $non_working_days)) {
        $week_off_dates[$cur_date] = true;
    }
}
$total_week_offs = count($week_off_dates);

// 2. Holidays
$holiday_dates = [];
$res = $mysqli->query("SELECT start_date, end_date, event_name FROM tbl_holiday WHERE (start_date <= '$end_date' AND end_date >= '$start_date')");
while($r = $res->fetch_assoc()) {
    $s = max($start_date, $r['start_date']);
    $e = !empty($r['end_date']) ? min($end_date, $r['end_date']) : $s;
    $cur = strtotime($s);
    $last = strtotime($e);
    while($cur <= $last) {
        $dt = date('Y-m-d', $cur);
        if (!isset($week_off_dates[$dt])) {
            $holiday_dates[$dt] = $r['event_name'];
        }
        $cur = strtotime('+1 day', $cur);
    }
}
$total_holidays = count($holiday_dates);

// 3. Batch fetch all attendance for the month
$att_by_user = [];
$res = $mysqli->query("SELECT user_id, date_in, attendance_status FROM tbl_attendance WHERE date_in >= '$start_date' AND date_in <= '$end_date'");
while($r = $res->fetch_assoc()) {
    $att_by_user[$r['user_id']][$r['date_in']] = $r['attendance_status'];
}

// 4. Batch fetch all approved leaves for the month
$leaves_by_user = [];
$res = $mysqli->query("SELECT user_id, leave_start_date, leave_end_date, leave_type FROM tbl_leave_application WHERE application_status = 2 AND leave_start_date <= '$end_date' AND (leave_end_date >= '$start_date' OR leave_end_date IS NULL)");
while($r = $res->fetch_assoc()) {
    $uid = $r['user_id'];
    $s = max($start_date, $r['leave_start_date']);
    $e = !empty($r['leave_end_date']) ? min($end_date, $r['leave_end_date']) : $s;
    $cur = strtotime($s);
    $last = strtotime($e);
    while($cur <= $last) {
        $dt = date('Y-m-d', $cur);
        if (!isset($week_off_dates[$dt]) && !isset($holiday_dates[$dt])) {
            $leaves_by_user[$uid][$dt] = $r['leave_type'];
        }
        $cur = strtotime('+1 day', $cur);
    }
}

// 5. Fetch all users
$users = [];
$res = $mysqli->query("SELECT 
    u.user_id,
    ad.fullname,
    ad.phone,
    ad.mobile,
    ad.emp_code,
    ad.employment_id,
    ad.avatar,
    d.designations,
    dep.departments_id,
    dep.deptname,
    COALESCE(ad.office_location_id, ad.office_id) as office_id,
    COALESCE(ol.location_name, 'Head Office, Noida') as office_branch
FROM tbl_users u
JOIN tbl_account_details ad ON ad.user_id = u.user_id
LEFT JOIN tbl_designations d ON d.designations_id = ad.designations_id
LEFT JOIN tbl_departments dep ON dep.departments_id = d.departments_id
LEFT JOIN tbl_office_locations ol ON ol.id = COALESCE(ad.office_location_id, ad.office_id)
WHERE u.activated = 1 AND u.role_id != 2
ORDER BY ad.fullname ASC");

$today = date('Y-m-d');
$calc_end = ($end_date > $today) ? $today : $end_date;
$total_days_so_far = max(0, (strtotime($calc_end) - strtotime($start_date)) / 86400 + 1);

// Week-offs and holidays passed so far
$wo_so_far = 0;
foreach($week_off_dates as $wod => $val) {
    if ($wod <= $calc_end) $wo_so_far++;
}
$hol_so_far = 0;
foreach($holiday_dates as $hd => $val) {
    if ($hd <= $calc_end) $hol_so_far++;
}
$working_days_so_far = max(0, $total_days_so_far - $wo_so_far - $hol_so_far);

$summary = [
    'total_staff' => 0,
    'total_present' => 0,
    'total_absent' => 0,
    'total_half_day' => 0,
    'total_leave' => 0,
];

$staff_records = [];

while($u = $res->fetch_assoc()) {
    $uid = $u['user_id'];
    $present = 0;
    $half_day = 0;
    $explicit_absent = 0;
    
    if (isset($att_by_user[$uid])) {
        foreach($att_by_user[$uid] as $dt => $st) {
            if ($st == 1) $present++;
            elseif ($st == 2) $half_day++;
            elseif ($st == 0) $explicit_absent++;
        }
    }
    
    $leave = 0;
    if (isset($leaves_by_user[$uid])) {
        $leave = count($leaves_by_user[$uid]);
    }
    
    // Absent count
    if ($explicit_absent > 0) {
        $absent = $explicit_absent;
    } else {
        $absent = max(0, $working_days_so_far - $present - $half_day - $leave);
    }
    
    $summary['total_staff']++;
    $summary['total_present'] += $present;
    $summary['total_absent'] += $absent;
    $summary['total_half_day'] += $half_day;
    $summary['total_leave'] += $leave;
    
    $phone = !empty(trim($u['mobile'])) ? trim($u['mobile']) : (!empty(trim($u['phone'])) ? trim($u['phone']) : '-');
    $emp_id = !empty(trim($u['emp_code'])) ? trim($u['emp_code']) : (!empty(trim($u['employment_id'])) ? trim($u['employment_id']) : '-');
    
    $staff_records[] = [
        'user_id' => $uid,
        'fullname' => trim($u['fullname']),
        'phone' => $phone,
        'employee_id' => $emp_id,
        'job_title' => !empty($u['designations']) ? $u['designations'] : '-',
        'department' => !empty($u['deptname']) ? $u['deptname'] : '-',
        'office_branch' => $u['office_branch'],
        'present' => $present,
        'absent' => $absent,
        'half_day' => $half_day,
        'leave' => $leave,
        'week_off' => $total_week_offs,
        'holiday' => $total_holidays,
    ];
}

$t1 = microtime(true);
echo sprintf("Processed %d staff in %.4f seconds!\n", count($staff_records), ($t1 - $t0));
echo "Summary: " . json_encode($summary) . "\n";
echo "First 3 records:\n" . json_encode(array_slice($staff_records, 0, 3), JSON_PRETTY_PRINT) . "\n";
