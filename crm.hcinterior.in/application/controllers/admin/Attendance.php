<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of attendance
 *
 * @author NaYeM
 */
class Attendance extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('attendance_model');
        $this->lang->load('utilities');
        $this->lang->load('main');
    }

    public function time_history()
    {
        $data['title'] = lang('time_history');
        $search = $this->input->post('search', TRUE);
        $user_id = $this->input->post('user_id', TRUE);
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', TRUE);
        }
        if (!empty($user_id)) {
            $data['user_id'] = $user_id;
            $data['edit'] = true;
        } else {
            $data['user_id'] = $this->session->userdata('user_id');
        }
        if (!empty($search)) {
            $data['edit'] = true;
        }
        $data['active'] = date('Y');

        $attendance_info = get_order_by('tbl_attendance', array('user_id' => $data['user_id']), 'date_in', true);
        $data['mytime_info'] = $this->get_mytime_info($attendance_info);

        // retrive all data from department table
        $data['all_employee'] = $this->attendance_model->get_all_employee();
        $data['selected_user'] = $this->db->where('user_id', $data['user_id'])->get('tbl_account_details')->row();
        $has_bio = $this->db->table_exists('tbl_user_biometrics');
        $data['pending_approvals_count'] = $this->db->where('attendance_status', 2)->count_all_results('tbl_attendance');
        $data['pending_face_count'] = $has_bio ? $this->db->where('is_approved', 0)->where('face_descriptor IS NOT NULL', null, false)->count_all_results('tbl_user_biometrics') : 0;
        $data['user_biometrics'] = $has_bio ? $this->db->where('user_id', $data['user_id'])->get('tbl_user_biometrics')->row() : null;

        $data['subview'] = $this->load->view('admin/attendance/time_history', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_user_attendance_calendar($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = $this->input->post('user_id', TRUE);
        }
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', TRUE);
        }
        if (empty($user_id)) {
            $user_id = $this->session->userdata('user_id');
        }

        $start_date = $this->input->post('start', TRUE);
        if (empty($start_date)) {
            $start_date = $this->input->get('start', TRUE);
        }
        $end_date = $this->input->post('end', TRUE);
        if (empty($end_date)) {
            $end_date = $this->input->get('end', TRUE);
        }

        if (!empty($start_date) && is_numeric($start_date)) {
            $start_date = date('Y-m-d', $start_date);
        } elseif (!empty($start_date)) {
            $start_date = date('Y-m-d', strtotime($start_date));
        } else {
            $start_date = date('Y-m-01');
        }

        if (!empty($end_date) && is_numeric($end_date)) {
            $end_date = date('Y-m-d', $end_date);
        } elseif (!empty($end_date)) {
            $end_date = date('Y-m-d', strtotime($end_date));
        } else {
            $end_date = date('Y-m-t');
        }

        // Active middle month for monthly KPI summary
        $mid_timestamp = (strtotime($start_date) + strtotime($end_date)) / 2;
        $active_month = date('Y-m', $mid_timestamp);
        $active_month_start = $active_month . '-01';
        $active_month_end = date('Y-m-t', $mid_timestamp);
        $active_month_name = date('F Y', $mid_timestamp);

        $user_info = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();

        // 1. Fetch attendance records
        $this->db->where('user_id', $user_id);
        $this->db->where('date_in >=', $start_date);
        $this->db->where('date_in <=', $end_date);
        $attendance_list = $this->db->get('tbl_attendance')->result();

        $events = array();
        $recorded_dates = array();

        $summary = array(
            'month_label' => $active_month_name,
            'present' => 0,
            'pending' => 0,
            'absent' => 0,
            'leave' => 0,
            'holiday' => 0,
            'total_working_seconds' => 0,
            'total_working_hours' => '0h 0m'
        );

        foreach ($attendance_list as $att) {
            $recorded_dates[$att->date_in] = true;
            $att_date = $att->date_in;
            $is_in_active_month = ($att_date >= $active_month_start && $att_date <= $active_month_end);

            if ($att->attendance_status == 1) {
                // Present
                if ($is_in_active_month) {
                    $summary['present']++;
                }
                $clock_records = $this->db->where('attendance_id', $att->attendance_id)->order_by('clock_id', 'ASC')->get('tbl_clock')->result();

                $day_seconds = 0;
                $clockin_str = '';
                $clockout_str = '';
                $clockin_raw = '';
                $clockout_raw = '';
                $clock_comments = '';
                $clock_id = 0;

                if (!empty($clock_records)) {
                    $first_clock = reset($clock_records);
                    $last_clock = end($clock_records);
                    $clock_id = $first_clock->clock_id;
                    $clockin_raw = $first_clock->clockin_time;
                    $clockin_str = display_time($first_clock->clockin_time);
                    if (!empty($last_clock->clockout_time)) {
                        $clockout_raw = $last_clock->clockout_time;
                        $clockout_str = display_time($last_clock->clockout_time);
                    }
                    $clock_comments = $first_clock->comments;

                    foreach ($clock_records as $clk) {
                        if (!empty($clk->clockin_time) && !empty($clk->clockout_time)) {
                            $s_time = strtotime($att->date_in . ' ' . $clk->clockin_time);
                            $e_time = strtotime($att->date_out . ' ' . $clk->clockout_time);
                            if ($e_time > $s_time) {
                                $day_seconds += ($e_time - $s_time);
                            }
                        }
                    }
                }

                if ($is_in_active_month) {
                    $summary['total_working_seconds'] += $day_seconds;
                }
                $hrs = floor($day_seconds / 3600);
                $mins = floor(($day_seconds % 3600) / 60);
                $hours_str = ($day_seconds > 0) ? ($hrs . 'h ' . $mins . 'm') : '';

                $title = 'Present';
                if (!empty($clockin_str) && !empty($clockout_str)) {
                    $title .= ' (' . $clockin_str . ' - ' . $clockout_str . ($hours_str ? ' | ' . $hours_str : '') . ')';
                } elseif (!empty($clockin_str)) {
                    $title .= ' (In: ' . $clockin_str . ')';
                }

                $clock_lat = (!empty($first_clock) && !empty($first_clock->latitude)) ? $first_clock->latitude : '';
                $clock_long = (!empty($first_clock) && !empty($first_clock->longitude)) ? $first_clock->longitude : '';
                $clock_loc = (!empty($first_clock) && !empty($first_clock->location)) ? $first_clock->location : '';
                if (empty($clock_lat) || empty($clock_long)) {
                    $flog = $this->db->where('attendance_id', $att->attendance_id)->order_by('log_id', 'DESC')->get('tbl_attendance_face_logs')->row();
                    if (!empty($flog)) {
                        $clock_lat = $flog->lat;
                        $clock_long = $flog->lng;
                        if (empty($clock_loc)) $clock_loc = $flog->address;
                    }
                }
                $maps_url = (!empty($clock_lat) && !empty($clock_long)) ? 'https://www.google.com/maps?q=' . urlencode($clock_lat . ',' . $clock_long) : '';

                $events[] = array(
                    'id' => 'att_' . $att->attendance_id,
                    'title' => $title,
                    'start' => $att_date,
                    'end' => $att_date,
                    'allDay' => true,
                    'color' => '#27ae60',
                    'textColor' => '#ffffff',
                    'className' => 'fc-event-present',
                    'attendance_id' => $att->attendance_id,
                    'clock_id' => $clock_id,
                    'date' => $att_date,
                    'status' => '1',
                    'status_label' => 'Present',
                    'clockin' => $clockin_raw,
                    'clockout' => $clockout_raw,
                    'hours' => $hours_str,
                    'comments' => $clock_comments,
                    'lat' => $clock_lat,
                    'long' => $clock_long,
                    'location' => $clock_loc,
                    'maps_url' => $maps_url
                );
            } elseif ($att->attendance_status == 2 || $att->attendance_status === '2') {
                // Pending Approval (Out of Range)
                if ($is_in_active_month) {
                    $summary['pending']++;
                }
                $clock_records = $this->db->where('attendance_id', $att->attendance_id)->order_by('clock_id', 'ASC')->get('tbl_clock')->result();
                $face_log = $this->db->where('attendance_id', $att->attendance_id)->order_by('log_id', 'DESC')->get('tbl_attendance_face_logs')->row();

                $clockin_str = '';
                $clockout_str = '';
                $clockin_raw = '';
                $clockout_raw = '';
                $clock_comments = '';
                $clock_lat = '';
                $clock_long = '';
                $clock_loc = '';
                $clock_dist = '';
                $clock_id = 0;

                if (!empty($clock_records)) {
                    $first_clock = reset($clock_records);
                    $last_clock = end($clock_records);
                    $clock_id = $first_clock->clock_id;
                    $clockin_raw = $first_clock->clockin_time;
                    $clockin_str = display_time($first_clock->clockin_time);
                    if (!empty($last_clock->clockout_time)) {
                        $clockout_raw = $last_clock->clockout_time;
                        $clockout_str = display_time($last_clock->clockout_time);
                    }
                    $clock_comments = $first_clock->comments;
                    $clock_lat = !empty($first_clock->latitude) ? $first_clock->latitude : '';
                    $clock_long = !empty($first_clock->longitude) ? $first_clock->longitude : '';
                    $clock_loc = !empty($first_clock->location) ? $first_clock->location : '';
                }

                if (!empty($face_log)) {
                    if (empty($clock_lat) && !empty($face_log->lat)) $clock_lat = $face_log->lat;
                    if (empty($clock_long) && !empty($face_log->lng)) $clock_long = $face_log->lng;
                    if (empty($clock_loc) && !empty($face_log->address)) $clock_loc = $face_log->address;
                    if (!empty($face_log->distance_from_office)) $clock_dist = round($face_log->distance_from_office) . 'm';
                }

                $title = '⚠ Pending Approval (Out of Range)';
                if (!empty($clockin_str)) {
                    $title .= ' (' . $clockin_str . ')';
                }

                $maps_url = (!empty($clock_lat) && !empty($clock_long)) ? 'https://www.google.com/maps?q=' . urlencode($clock_lat . ',' . $clock_long) : '';

                $events[] = array(
                    'id' => 'att_' . $att->attendance_id,
                    'title' => $title,
                    'start' => $att_date,
                    'end' => $att_date,
                    'allDay' => true,
                    'color' => '#d35400',
                    'textColor' => '#ffffff',
                    'className' => 'fc-event-pending',
                    'attendance_id' => $att->attendance_id,
                    'clock_id' => $clock_id,
                    'date' => $att_date,
                    'status' => '2',
                    'status_label' => 'Pending Approval (Out of Range)',
                    'clockin' => $clockin_raw,
                    'clockout' => $clockout_raw,
                    'hours' => '',
                    'comments' => $clock_comments,
                    'lat' => $clock_lat,
                    'long' => $clock_long,
                    'location' => $clock_loc,
                    'distance' => $clock_dist,
                    'maps_url' => $maps_url
                );
            } elseif ($att->attendance_status === '0' || $att->attendance_status === 0) {
                // Absent
                if ($is_in_active_month) {
                    $summary['absent']++;
                }
                $events[] = array(
                    'id' => 'att_' . $att->attendance_id,
                    'title' => '✖ Absent',
                    'start' => $att_date,
                    'end' => $att_date,
                    'allDay' => true,
                    'color' => '#e74c3c',
                    'textColor' => '#ffffff',
                    'className' => 'fc-event-absent',
                    'attendance_id' => $att->attendance_id,
                    'clock_id' => 0,
                    'date' => $att_date,
                    'status' => '0',
                    'status_label' => 'Absent',
                    'clockin' => '',
                    'clockout' => '',
                    'hours' => '',
                    'comments' => ''
                );
            } elseif ($att->attendance_status == 3 || $att->attendance_status === '3') {
                // On Leave
                if ($is_in_active_month) {
                    $summary['leave']++;
                }
                $leave_cat_code = 'Leave';
                if (!empty($att->leave_application_id)) {
                    $l_app = $this->db->where('leave_application_id', $att->leave_application_id)->get('tbl_leave_application')->row();
                    if (!empty($l_app)) {
                        $l_cat = $this->db->where('leave_category_id', $l_app->leave_category_id)->get('tbl_leave_category')->row();
                        if (!empty($l_cat)) {
                            $leave_cat_code = $l_cat->leave_category;
                        }
                    }
                }
                $leave_title = '⏳ ' . $leave_cat_code . ' (On Leave)';
                $events[] = array(
                    'id' => 'att_' . $att->attendance_id,
                    'title' => $leave_title,
                    'start' => $att_date,
                    'end' => $att_date,
                    'allDay' => true,
                    'color' => '#f39c12',
                    'textColor' => '#ffffff',
                    'className' => 'fc-event-leave',
                    'attendance_id' => $att->attendance_id,
                    'clock_id' => 0,
                    'date' => $att_date,
                    'status' => '3',
                    'status_label' => $leave_cat_code . ' Leave',
                    'leave_type' => $leave_cat_code,
                    'clockin' => '',
                    'clockout' => '',
                    'hours' => '',
                    'comments' => ''
                );
            } elseif ($att->attendance_status === 'H') {
                // Holiday
                if ($is_in_active_month) {
                    $summary['holiday']++;
                }
                $events[] = array(
                    'id' => 'att_' . $att->attendance_id,
                    'title' => '★ Holiday',
                    'start' => $att_date,
                    'end' => $att_date,
                    'allDay' => true,
                    'color' => '#3498db',
                    'textColor' => '#ffffff',
                    'className' => 'fc-event-holiday',
                    'attendance_id' => $att->attendance_id,
                    'clock_id' => 0,
                    'date' => $att_date,
                    'status' => 'H',
                    'status_label' => 'Holiday',
                    'clockin' => '',
                    'clockout' => '',
                    'hours' => '',
                    'comments' => ''
                );
            }
        }

        // 2. Fetch Public Holidays from tbl_holiday
        $this->db->where('start_date <=', $end_date);
        $this->db->where('end_date >=', $start_date);
        $public_holidays = $this->db->get('tbl_holiday')->result();
        foreach ($public_holidays as $p_hol) {
            $cur = strtotime($p_hol->start_date);
            $last = strtotime($p_hol->end_date);
            while ($cur <= $last) {
                $p_date = date('Y-m-d', $cur);
                if ($p_date >= $start_date && $p_date <= $end_date) {
                    if (empty($recorded_dates[$p_date])) {
                        if ($p_date >= $active_month_start && $p_date <= $active_month_end) {
                            $summary['holiday']++;
                        }
                        $recorded_dates[$p_date] = true;
                        $events[] = array(
                            'id' => 'hol_' . $p_hol->holiday_id . '_' . $p_date,
                            'title' => '★ ' . $p_hol->event_name,
                            'start' => $p_date,
                            'end' => $p_date,
                            'allDay' => true,
                            'color' => '#2980b9',
                            'textColor' => '#ffffff',
                            'className' => 'fc-event-pub-holiday',
                            'attendance_id' => 0,
                            'clock_id' => 0,
                            'date' => $p_date,
                            'status' => 'H',
                            'status_label' => 'Holiday (' . $p_hol->event_name . ')',
                            'clockin' => '',
                            'clockout' => '',
                            'hours' => '',
                            'comments' => $p_hol->event_name
                        );
                    }
                }
                $cur = strtotime('+1 day', $cur);
            }
        }

        // 3. Fetch Weekend non-working days from tbl_working_days where flag = 0
        $this->load->model('common_model');
        $non_working_days = $this->common_model->get_holidays(); // e.g. Sunday
        $off_days = array();
        if (!empty($non_working_days)) {
            foreach ($non_working_days as $nwd) {
                $off_days[strtolower($nwd->day)] = true;
            }
        }

        if (!empty($off_days)) {
            $cur_d = strtotime($start_date);
            $end_d = strtotime($end_date);
            while ($cur_d <= $end_d) {
                $c_date = date('Y-m-d', $cur_d);
                $day_l = strtolower(date('l', $cur_d));
                if (!empty($off_days[$day_l]) && empty($recorded_dates[$c_date])) {
                    if ($c_date >= $active_month_start && $c_date <= $active_month_end) {
                        $summary['holiday']++;
                    }
                    $events[] = array(
                        'id' => 'weekend_' . $c_date,
                        'title' => '★ ' . ucfirst($day_l) . ' (Off)',
                        'start' => $c_date,
                        'end' => $c_date,
                        'allDay' => true,
                        'color' => '#7f8c8d',
                        'textColor' => '#ffffff',
                        'className' => 'fc-event-weekend',
                        'attendance_id' => 0,
                        'clock_id' => 0,
                        'date' => $c_date,
                        'status' => 'H',
                        'status_label' => ucfirst($day_l) . ' Off',
                        'clockin' => '',
                        'clockout' => '',
                        'hours' => '',
                        'comments' => 'Weekly Off'
                    );
                }
                $cur_d = strtotime('+1 day', $cur_d);
            }
        }

        $tot_hrs = floor($summary['total_working_seconds'] / 3600);
        $tot_mins = floor(($summary['total_working_seconds'] % 3600) / 60);
        $summary['total_working_hours'] = $tot_hrs . 'h ' . $tot_mins . 'm';

        // Include leave balances (EL, SL, CL)
        $leave_balances = get_user_leave_balance_details($user_id, date('Y', $mid_timestamp));
        $summary['el_balance'] = isset($leave_balances['EL']) ? number_format($leave_balances['EL']['balance'], 2) : '0.00';
        $summary['sl_balance'] = isset($leave_balances['SL']) ? number_format($leave_balances['SL']['balance'], 2) : '0.00';
        $summary['cl_balance'] = isset($leave_balances['CL']) ? number_format($leave_balances['CL']['balance'], 2) : '0.00';
        $summary['leave_balances'] = $leave_balances;

        echo json_encode(array(
            'events' => $events,
            'summary' => $summary,
            'user' => array(
                'user_id' => $user_id,
                'name' => !empty($user_info->fullname) ? $user_info->fullname : 'Employee'
            )
        ));
        exit();
    }

    public function save_user_attendance()
    {
        $user_type = $this->session->userdata('user_type');
        $admin = admin();
        if ($user_type != 1 && empty($admin) && !can_do(26)) {
            echo json_encode(array('status' => 'error', 'message' => 'Permission denied. Only admins can mark or update attendance.'));
            exit();
        }

        $user_id = $this->input->post('user_id', TRUE);
        $date = $this->input->post('date', TRUE);
        $status = $this->input->post('status', TRUE);
        $leave_type = strtoupper(trim($this->input->post('leave_type', TRUE)));
        $clockin_time = $this->input->post('clockin_time', TRUE);
        $clockout_time = $this->input->post('clockout_time', TRUE);
        $comments = $this->input->post('comments', TRUE);

        if (empty($user_id) || empty($date)) {
            echo json_encode(array('status' => 'error', 'message' => 'User ID and Date are required.'));
            exit();
        }

        $formatted_date = date('Y-m-d', strtotime($date));

        $existing = $this->db->where('user_id', $user_id)->where('date_in', $formatted_date)->get('tbl_attendance')->row();

        if ($status === 'delete') {
            if (!empty($existing)) {
                if (!empty($existing->leave_application_id)) {
                    $this->db->where('leave_application_id', $existing->leave_application_id)->delete('tbl_leave_application');
                }
                $this->db->where('attendance_id', $existing->attendance_id)->delete('tbl_clock');
                $this->db->where('attendance_id', $existing->attendance_id)->delete('tbl_attendance');
            }
            echo json_encode(array('status' => 'success', 'message' => 'Attendance record removed for ' . $formatted_date));
            exit();
        }

        if ($status === '3') {
            // On Leave - link to tbl_leave_application with selected category (EL, SL, CL)
            if (empty($leave_type)) {
                $leave_type = 'EL';
            }
            $cat_row = $this->db->where('leave_category', $leave_type)->get('tbl_leave_category')->row();
            $cat_id = !empty($cat_row) ? $cat_row->leave_category_id : 2;

            $leave_app_id = 0;
            if (!empty($existing) && !empty($existing->leave_application_id)) {
                $this->db->where('leave_application_id', $existing->leave_application_id)->update('tbl_leave_application', array(
                    'leave_category_id' => $cat_id,
                    'reason' => !empty($comments) ? $comments : 'Marked from attendance',
                    'comments' => $comments,
                    'application_status' => 2
                ));
                $leave_app_id = $existing->leave_application_id;
            } else {
                $this->db->insert('tbl_leave_application', array(
                    'user_id' => $user_id,
                    'leave_category_id' => $cat_id,
                    'reason' => !empty($comments) ? $comments : 'Marked from attendance',
                    'leave_type' => 'single_day',
                    'leave_start_date' => $formatted_date,
                    'leave_end_date' => $formatted_date,
                    'application_status' => 2,
                    'view_status' => 2,
                    'comments' => $comments,
                    'approve_by' => $this->session->userdata('user_id')
                ));
                $leave_app_id = $this->db->insert_id();
            }

            if (!empty($existing)) {
                $this->db->where('attendance_id', $existing->attendance_id)->update('tbl_attendance', array(
                    'attendance_status' => 3,
                    'leave_application_id' => $leave_app_id,
                    'clocking_status' => 0,
                    'date_out' => $formatted_date
                ));
                $this->db->where('attendance_id', $existing->attendance_id)->delete('tbl_clock');
            } else {
                $this->db->insert('tbl_attendance', array(
                    'user_id' => $user_id,
                    'date_in' => $formatted_date,
                    'date_out' => $formatted_date,
                    'attendance_status' => 3,
                    'leave_application_id' => $leave_app_id,
                    'clocking_status' => 0
                ));
            }
            echo json_encode(array('status' => 'success', 'message' => 'Attendance marked as On Leave (' . $leave_type . ') for ' . $formatted_date));
            exit();
        }

        if ($status === '0' || $status === 'H') {
            if (!empty($existing)) {
                if (!empty($existing->leave_application_id)) {
                    $this->db->where('leave_application_id', $existing->leave_application_id)->delete('tbl_leave_application');
                }
                $this->db->where('attendance_id', $existing->attendance_id)->update('tbl_attendance', array(
                    'attendance_status' => $status,
                    'leave_application_id' => 0,
                    'clocking_status' => 0,
                    'date_out' => $formatted_date
                ));
                $this->db->where('attendance_id', $existing->attendance_id)->delete('tbl_clock');
            } else {
                $this->db->insert('tbl_attendance', array(
                    'user_id' => $user_id,
                    'date_in' => $formatted_date,
                    'date_out' => $formatted_date,
                    'attendance_status' => $status,
                    'leave_application_id' => 0,
                    'clocking_status' => 0
                ));
            }
            $status_text = ($status === '0') ? 'Absent' : 'Holiday';
            echo json_encode(array('status' => 'success', 'message' => 'Attendance marked as ' . $status_text . ' for ' . $formatted_date));
            exit();
        }

        if ($status === '1') {
            if (!empty($existing)) {
                if (!empty($existing->leave_application_id)) {
                    $this->db->where('leave_application_id', $existing->leave_application_id)->delete('tbl_leave_application');
                }
                $this->db->where('attendance_id', $existing->attendance_id)->update('tbl_attendance', array(
                    'attendance_status' => 1,
                    'leave_application_id' => 0,
                    'clocking_status' => 0,
                    'date_out' => $formatted_date
                ));
                $attendance_id = $existing->attendance_id;
            } else {
                $this->db->insert('tbl_attendance', array(
                    'user_id' => $user_id,
                    'date_in' => $formatted_date,
                    'date_out' => $formatted_date,
                    'attendance_status' => 1,
                    'clocking_status' => 0
                ));
                $attendance_id = $this->db->insert_id();
            }

            $in_time = !empty($clockin_time) ? date('H:i:s', strtotime($clockin_time)) : '09:30:00';
            $out_time = !empty($clockout_time) ? date('H:i:s', strtotime($clockout_time)) : '18:30:00';

            $existing_clock = $this->db->where('attendance_id', $attendance_id)->get('tbl_clock')->row();
            if (!empty($existing_clock)) {
                $this->db->where('clock_id', $existing_clock->clock_id)->update('tbl_clock', array(
                    'clockin_time' => $in_time,
                    'clockout_time' => $out_time,
                    'clocking_status' => 0,
                    'comments' => $comments,
                    'ip_address' => $this->input->ip_address()
                ));
            } else {
                $this->db->insert('tbl_clock', array(
                    'attendance_id' => $attendance_id,
                    'clockin_time' => $in_time,
                    'clockout_time' => $out_time,
                    'clocking_status' => 0,
                    'comments' => $comments,
                    'ip_address' => $this->input->ip_address()
                ));
            }

            echo json_encode(array('status' => 'success', 'message' => 'Attendance marked as Present for ' . $formatted_date . ' (' . display_time($in_time) . ' - ' . display_time($out_time) . ')'));
            exit();
        }

        if ($status === '2') {
            // Half Day
            if (!empty($existing)) {
                if (!empty($existing->leave_application_id)) {
                    $this->db->where('leave_application_id', $existing->leave_application_id)->delete('tbl_leave_application');
                }
                $this->db->where('attendance_id', $existing->attendance_id)->update('tbl_attendance', array(
                    'attendance_status' => 2,
                    'leave_application_id' => 0,
                    'clocking_status' => 0,
                    'date_out' => $formatted_date
                ));
                $attendance_id = $existing->attendance_id;
            } else {
                $this->db->insert('tbl_attendance', array(
                    'user_id' => $user_id,
                    'date_in' => $formatted_date,
                    'date_out' => $formatted_date,
                    'attendance_status' => 2,
                    'clocking_status' => 0
                ));
                $attendance_id = $this->db->insert_id();
            }

            $in_time = !empty($clockin_time) ? date('H:i:s', strtotime($clockin_time)) : '09:30:00';
            $out_time = !empty($clockout_time) ? date('H:i:s', strtotime($clockout_time)) : '14:00:00';

            $existing_clock = $this->db->where('attendance_id', $attendance_id)->get('tbl_clock')->row();
            if (!empty($existing_clock)) {
                $this->db->where('clock_id', $existing_clock->clock_id)->update('tbl_clock', array(
                    'clockin_time' => $in_time,
                    'clockout_time' => $out_time,
                    'clocking_status' => 0,
                    'comments' => $comments,
                    'ip_address' => $this->input->ip_address()
                ));
            } else {
                $this->db->insert('tbl_clock', array(
                    'attendance_id' => $attendance_id,
                    'clockin_time' => $in_time,
                    'clockout_time' => $out_time,
                    'clocking_status' => 0,
                    'comments' => $comments,
                    'ip_address' => $this->input->ip_address()
                ));
            }

            echo json_encode(array('status' => 'success', 'message' => 'Attendance marked as Half Day for ' . $formatted_date));
            exit();
        }

        if ($status === 'WO') {
            // Week Off
            if (!empty($existing)) {
                if (!empty($existing->leave_application_id)) {
                    $this->db->where('leave_application_id', $existing->leave_application_id)->delete('tbl_leave_application');
                }
                $this->db->where('attendance_id', $existing->attendance_id)->delete('tbl_clock');
                $this->db->where('attendance_id', $existing->attendance_id)->delete('tbl_attendance');
            }
            echo json_encode(array('status' => 'success', 'message' => 'Attendance marked as Week Off for ' . $formatted_date));
            exit();
        }

        echo json_encode(array('status' => 'error', 'message' => 'Invalid status provided.'));
        exit();
    }

    public function get_attendance_for_date()
    {
        $user_id = $this->input->post('user_id', TRUE) ?: $this->input->get('user_id', TRUE);
        $date = $this->input->post('date', TRUE) ?: $this->input->get('date', TRUE);

        if (empty($user_id) || empty($date)) {
            echo json_encode(array('status' => 'error', 'message' => 'User ID and Date are required.'));
            exit();
        }

        $formatted_date = date('Y-m-d', strtotime($date));
        $day_name = strtolower(date('l', strtotime($formatted_date)));

        // Working day check
        $non_working_q = $this->db->select('tbl_days.day')
            ->from('tbl_working_days')
            ->join('tbl_days', 'tbl_days.day_id = tbl_working_days.day_id', 'left')
            ->where('tbl_working_days.flag', 0)
            ->get()->result();
        $is_week_off = false;
        if (!empty($non_working_q)) {
            foreach ($non_working_q as $wd) {
                if (strtolower($wd->day) === $day_name) {
                    $is_week_off = true;
                    break;
                }
            }
        } else {
            if ($day_name === 'sunday') $is_week_off = true;
        }

        // Holiday check
        $holiday_row = $this->db->where('start_date <=', $formatted_date)->where('end_date >=', $formatted_date)->get('tbl_holiday')->row();
        $is_holiday = !empty($holiday_row) ? $holiday_row->event_name : false;

        // Existing attendance
        $att = $this->db->where('user_id', $user_id)->where('date_in', $formatted_date)->get('tbl_attendance')->row();
        $clock = null;
        $leave = null;
        if (!empty($att)) {
            $clock = $this->db->where('attendance_id', $att->attendance_id)->order_by('clock_id', 'DESC')->get('tbl_clock')->row();
            if (!empty($att->leave_application_id)) {
                $leave = $this->db->where('leave_application_id', $att->leave_application_id)->get('tbl_leave_application')->row();
            }
        }

        $current_status = 'absent';
        if (!empty($att)) {
            if ($att->attendance_status == 1) $current_status = 'present';
            elseif ($att->attendance_status == 2) $current_status = 'half_day';
            elseif ($att->attendance_status == 3) $current_status = 'leave';
            elseif ($att->attendance_status == 'H') $current_status = 'holiday';
            elseif ($att->attendance_status == 0) $current_status = 'absent';
        } else {
            if ($is_holiday) $current_status = 'holiday';
            elseif ($is_week_off) $current_status = 'week_off';
            else $current_status = 'absent';
        }

        $clockin = (!empty($clock) && !empty($clock->clockin_time)) ? date('h:i A', strtotime($clock->clockin_time)) : '';
        $clockout = (!empty($clock) && !empty($clock->clockout_time)) ? date('h:i A', strtotime($clock->clockout_time)) : '';
        $comments = !empty($clock->comments) ? $clock->comments : (!empty($leave->comments) ? $leave->comments : (!empty($leave->reason) ? $leave->reason : ''));

        $leave_type = 'paid';
        if (!empty($leave)) {
            if ($leave->leave_type == 'hours') {
                $leave_type = 'half_day';
            } elseif (stripos($leave->reason, 'unpaid') !== false) {
                $leave_type = 'unpaid';
            }
        }

        // Fetch attendance punch photo for this date if available
        $face_photo_url = null;
        $face_punch_time = null;
        $face_punch_type = null;
        $face_address = null;
        $face_is_inside = true;

        if ($this->db->table_exists('tbl_attendance_face_logs')) {
            $face_log = null;
            if (!empty($att)) {
                $face_log = $this->db->where('attendance_id', $att->attendance_id)
                    ->order_by('log_id', 'DESC')
                    ->get('tbl_attendance_face_logs')
                    ->row();
            }
            if (empty($face_log)) {
                $face_log = $this->db->where('user_id', $user_id)
                    ->like('punch_time', $formatted_date)
                    ->order_by('log_id', 'DESC')
                    ->get('tbl_attendance_face_logs')
                    ->row();
            }

            if (!empty($face_log)) {
                $p_src = !empty($face_log->photo_path) ? $face_log->photo_path : $face_log->photo_thumbnail;
                if (!empty($p_src)) {
                    $face_photo_url = (strpos($p_src, 'uploads/') === 0) ? base_url($p_src) : $p_src;
                }
                $face_punch_time = !empty($face_log->punch_time) ? date('h:i A', strtotime($face_log->punch_time)) : null;
                $face_punch_type = $face_log->punch_type;
                $face_address = $face_log->address;
                $face_is_inside = (bool)$face_log->is_inside_geofence;
            }
        }

        echo json_encode(array(
            'status' => 'success',
            'date' => $formatted_date,
            'display_date' => date('jS F Y', strtotime($formatted_date)),
            'current_status' => $current_status,
            'clockin_time' => $clockin,
            'clockout_time' => $clockout,
            'comments' => $comments,
            'leave_type' => $leave_type,
            'is_week_off' => $is_week_off,
            'is_holiday' => $is_holiday,
            'photo_url' => $face_photo_url,
            'punch_time' => $face_punch_time,
            'punch_type' => $face_punch_type,
            'punch_address' => $face_address,
            'is_inside_geofence' => $face_is_inside
        ));
        exit();
    }

    public function time_history_pdf($user_id)
    {
        $data['title'] = lang('time_logs'); //Page title
        $this->attendance_model->_table_name = "tbl_attendance"; // table name
        $this->attendance_model->_order_by = "user_id"; // $id
        $attendance_info = $this->attendance_model->get_by(array('user_id' => $user_id), FALSE);
        $data['user_info'] = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
        $data['mytime_info'] = $this->get_mytime_info($attendance_info);
        $this->load->helper('dompdf');
        $viewfile = $this->load->view('admin/attendance/all_timehistory_pdf', $data, TRUE);
        pdf_create($viewfile, slug_it(lang('time_logs') . '-' . $data['user_info']->fullname));
    }

    public function get_mytime_info($attendance_info)
    {
        if (!empty($attendance_info)) {
            foreach ($attendance_info as $v_info) {
                if ($v_info->date_in == $v_info->date_out) {
                    $date = strftime(config_item('date_format'), strtotime($v_info->date_in));
                } else {
                    $date = lang('date_in') . ' : ' . strftime(config_item('date_format'), strtotime($v_info->date_in)) . ', ' . lang('day_out') . ': ' . strftime(config_item('date_format'), strtotime($v_info->date_out));
                }
                $clock_info[date('Y', strtotime($v_info->date_in))][date('W', strtotime($v_info->date_in))][$date] = get_result('tbl_attendance', array('attendance_id' => $v_info->attendance_id));
                //                    $this->attendance_model->get_mytime_info($v_info->attendance_id);
            }
            return $clock_info;
        }
    }

    public function edit_mytime($clock_id)
    {

        $data['title'] = lang('edit_time');
        $attendance_id = NULL;
        $data['clock_info'] = $this->attendance_model->get_mytime_info($attendance_id, $clock_id);
        $data['modal_subview'] = $this->load->view('admin/attendance/edit_mytime', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function delete_mytime($clock_id)
    {
        $clock_info = get_row('tbl_clock', array('clock_id' => $clock_id));
        if (!empty($clock_info)) {
            $attendance_info = count(get_result('tbl_clock', array('attendance_id' => $clock_info->attendance_id)));
            if ($attendance_info == 1) {
                // *********** Delete into tbl_clock *******************
                $this->attendance_model->_table_name = "tbl_attendance"; // table name
                $this->attendance_model->_primary_key = "attendance_id"; // $id
                $this->attendance_model->delete($clock_id);
            }
            // *********** Delete into tbl_clock *******************
            $this->attendance_model->_table_name = "tbl_clock"; // table name
            $this->attendance_model->_primary_key = "clock_id"; // $id
            $this->attendance_model->delete($clock_id);

            // *********** Delete into tbl_clock_history *******************
            $this->attendance_model->_table_name = "tbl_clock_history"; // table name
            $this->attendance_model->delete_multiple(array('clock_id' => $clock_id));

            echo json_encode(array("status" => 'success', 'message' => lang('deleted')));
            exit();
        } else {
            redirect('admin/attendance/time_history');
        }
    }

    public function cheanged_mytime($clock_id)
    {

        $cdata = $this->attendance_model->array_from_post(array('reason', 'clockin_edit', 'clockout_edit'));

        $data['clock_id'] = $clock_id;
        $data['user_id'] = $this->session->userdata('user_id');
        $data['clockin_edit'] = date('H:i:s', strtotime($cdata['clockin_edit']));
        $data['clockout_edit'] = date('H:i:s', strtotime($cdata['clockout_edit']));
        $data['reason'] = $cdata['reason'];

        //save data in database
        $this->attendance_model->_table_name = "tbl_clock_history"; // table name
        $this->attendance_model->_order_by = "clock_history_id"; // $id
        $history_id = $this->attendance_model->save($data);

        $user_type = $this->session->userdata('user_type');
        if ($user_type == 1) {
            $msg = lang('time_change_request_admin');
            $cldata['clockin_time'] = $data['clockin_edit'];
            $cldata['clockout_time'] = $data['clockout_edit'];

            $this->attendance_model->_table_name = 'tbl_clock';
            $this->attendance_model->_primary_key = 'clock_id';
            $this->attendance_model->save($cldata, $clock_id);

            $adata['status'] = '2';

            $this->attendance_model->_table_name = 'tbl_clock_history';
            $this->attendance_model->_primary_key = 'clock_history_id';
            $this->attendance_model->save($adata, $history_id);
        } else {
            $msg = lang('time_change_request_staff');
            $all_admin = $this->db->where('role_id', 1)->get('tbl_users')->result();
            $notifyUser = array();
            if (!empty($all_admin)) {
                foreach ($all_admin as $v_admin) {
                    if (!empty($v_admin)) {
                        if ($v_admin->user_id != $this->session->userdata('user_id')) {
                            array_push($notifyUser, $v_admin->user_id);
                            add_notification(array(
                                'to_user_id' => $v_admin->user_id,
                                'description' => 'not_timechange_request',
                                'icon' => 'file-text',
                                'link' => 'admin/attendance/view_changerequest/' . $history_id,
                                'value' => lang('edited') . ' ' . lang('by') . ' ' . $this->session->userdata('name'),
                            ));
                        }
                    }
                }
            }
            if (!empty($notifyUser)) {
                show_notification($notifyUser);
            }
        }

        $user_info = $this->db->where('user_id', $this->session->userdata('user_id'))->get('tbl_account_details')->row();
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'attendance',
            'module_field_id' => $clock_id,
            'activity' => 'activity_time_change_request',
            'icon' => 'fa-clock-o',
            'link' => 'admin/attendance/view_changerequest/' . $history_id,
            'value1' => $user_info->fullname,
            'value2' => $data['reason'],
        );

        // Update into tbl_project
        $this->attendance_model->_table_name = "tbl_activities"; //table name
        $this->attendance_model->_primary_key = "activities_id";
        $this->attendance_model->save($activities);

        // messages for user
        $type = "success";
        $message = $msg;
        set_message($type, $message);
        redirect('admin/attendance/time_history');
    }

    public function add_time_manually()
    {

        $data['title'] = lang('view_timerequest');
        // retrive all data from department table
        $data['all_employee'] = $this->attendance_model->get_all_employee();

        $data['subview'] = $this->load->view('admin/attendance/add_time_manually', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function saved_manual_time()
    {

        $adata = $this->attendance_model->array_from_post(array('user_id', 'date_in', 'date_out'));
        $date_in = date('Y', strtotime($adata['date_in']));
        if (empty($adata['date_in'])) {
            $adata['date_in'] = date('Y-m-d');
        } elseif ($date_in >= 1969 && $date_in <= 1999) {
            $adata['date_in'] = date('Y-m-d');
        }
        $date_in = date('Y', strtotime($adata['date_out']));
        if (empty($adata['date_out'])) {
            $adata['date_out'] = date('Y-m-d');
        } elseif ($date_in >= 1969 && $date_in <= 1999) {
            $adata['date_out'] = date('Y-m-d');
        }
        $check_date = $this->attendance_model->check_by(array('user_id' => $adata['user_id'], 'date_in' => $adata['date_in']), 'tbl_attendance');
        $this->attendance_model->_table_name = "tbl_attendance"; // table name
        $this->attendance_model->_primary_key = "attendance_id"; // $id

        if (!empty($check_date)) { // if exis do not save date and return id
            if ($check_date->attendance_status != '1') {
                $adata['attendance_status'] = 1;
                $this->attendance_model->save($adata, $check_date->attendance_id);
            }
            $data['attendance_id'] = $check_date->attendance_id;
        } else { // else save data into tbl attendance
            $adata['attendance_status'] = 1;
            //save data into attendance table
            $data['attendance_id'] = $this->attendance_model->save($adata);
        }

        $data['clockin_time'] = date('H:i:s', strtotime($this->input->post('clockin_time', TRUE)));
        $data['clockout_time'] = date('H:i:s', strtotime($this->input->post('clockout_time', TRUE)));
        $data['clocking_status'] = 0;
        //save data in database
        $this->attendance_model->_table_name = "tbl_clock"; // table name
        $this->attendance_model->_primary_key = "clock_id"; // $id
        $id = $this->attendance_model->save($data);
        $user_info = $this->db->where('user_id', $adata['user_id'])->get('tbl_account_details')->row();
        $hdata['user_id'] = $adata['user_id'];
        $hdata['clock_id'] = $id;
        $hdata['clockin_edit'] = $data['clockin_time'];
        $hdata['clockout_edit'] = $data['clockout_time'];
        $hdata['reason'] = lang('manually_added') . ' ' . lang('by') . ' ' . $user_info->fullname;
        $user_type = $this->session->userdata('user_type');
        if ($user_type == 1) {
            $hdata['status'] = '2';
        }
        $this->attendance_model->_table_name = "tbl_clock_history"; // table name
        $this->attendance_model->_primary_key = "clock_history_id"; // $id
        $clock_history_id = $this->attendance_model->save($hdata);


        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'attendance',
            'module_field_id' => $data['attendance_id'],
            'activity' => 'activity_time_manually',
            'icon' => 'fa-clock-o',
            'link' => 'admin/attendance/view_changerequest/' . $clock_history_id,
            'value1' => $user_info->fullname,
            'value2' => $adata['date_in'] . ' To ' . $adata['date_out'],
        );

        // Update into tbl_project
        $this->attendance_model->_table_name = "tbl_activities"; //table name
        $this->attendance_model->_primary_key = "activities_id";
        $this->attendance_model->save($activities);

        if ($user_type != 1) {
            $all_admin = $this->db->where('role_id', 1)->get('tbl_users')->result();
            $notifyUser = array();
            if (!empty($all_admin)) {
                foreach ($all_admin as $v_admin) {
                    if (!empty($v_admin)) {
                        if ($v_admin->user_id != $this->session->userdata('user_id')) {
                            array_push($notifyUser, $v_admin->user_id);
                            add_notification(array(
                                'to_user_id' => $v_admin->user_id,
                                'description' => 'not_timechange_request',
                                'icon' => 'file-text',
                                'link' => 'admin/attendance/view_changerequest/' . $clock_history_id,
                                'value' => lang('manually_added') . ' ' . lang('by') . ' ' . $user_info->fullname,
                            ));
                        }
                    }
                }
            }
            if (!empty($notifyUser)) {
                show_notification($notifyUser);
            }
        }

        $type = "success";
        $message = lang('time_manually_added');
        set_message($type, $message);
        redirect('admin/attendance/timechange_request'); //redirect page

    }

    public function timechange_request()
    {
        $data['title'] = lang('timechange_request');
        $data['all_clock_history'] = $this->attendance_model->get_all_clock_history();
        $data['subview'] = $this->load->view('admin/attendance/list_all_request', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function timechange_requestList($type = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_clock_history';
            $this->datatables->column_order = array('clockin_edit', 'clockout_edit', 'user_id');
            $this->datatables->column_search = array('clockin_edit', 'clockout_edit', 'user_id');
            $this->datatables->order = array('clock_history_id' => 'desc');
            // get all invoice
            $fetch_data = $this->datatables->get_all_clock_history($type);;

            $data = array();
            foreach ($fetch_data as $_key => $v_clock_history) {

                $action = null;
                $sub_array = array();
                $emp_id = null;
                $emp_id .= '<a class="text-info" href="' . base_url() . 'admin/user/user_details/' . $v_clock_history->user_id . '">' . $v_clock_history->employment_id . '</a>';
                $sub_array[] = $emp_id;

                $name = null;
                $name .= '<a class="text-info" href="' . base_url() . 'admin/user/user_details/' . $v_clock_history->user_id . '">' . $v_clock_history->fullname . '</a>';
                $sub_array[] = $name;

                if ($v_clock_history->clockin_edit != "00:00:00") {
                    $clockin_edit = display_time($v_clock_history->clockin_edit);
                } else {
                    $clockin_edit = '-';
                }
                $sub_array[] = $clockin_edit;
                if ($v_clock_history->clockout_edit != "00:00:00") {
                    $clockout_edit = display_time($v_clock_history->clockout_edit);
                } else {
                    $clockout_edit = '-';
                }
                $sub_array[] = $clockout_edit;
                $label = null;
                $text = '-';
                if ($v_clock_history->status == 1) {
                    $label = 'warning';
                    $text = lang('pending');
                } elseif ($v_clock_history->status == 2) {
                    $label = 'success';
                    $text = lang('accepted');
                } elseif ($v_clock_history->status == 3) {
                    $label = 'danger';
                    $text = lang('rejected');
                }
                $sub_array[] = '<span class="label label-' . $label . '">' . $text . '</span>';
                $action .= '<a href="' . base_url() . 'admin/attendance/view_timerequest/' . $v_clock_history->clock_history_id . '"
                       class="btn btn-primary btn-xs"
                       title="' . lang('view') . '" data-toggle="modal" data-placement="top" data-target="#myModal"><span
                            class="fa fa-list-alt"></span></a>' . ' ';
                if ($this->session->userdata('user_type') == 1) {
                    $action .= ajax_anchor(base_url("admin/attendance/delete_request/" . $v_clock_history->clock_history_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $sub_array[] = $action;
                $data[] = $sub_array;
            }

            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function view_changerequest($clock_history_id)
    {
        $data['title'] = lang('view_timerequest');
        $data['clock_history'] = $this->attendance_model->get_all_clock_history($clock_history_id);
        $data['subview'] = $this->load->view('admin/attendance/request_details', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function view_timerequest($clock_history_id)
    {
        $data['title'] = lang('view_timerequest');
        $data['clock_history'] = $this->attendance_model->get_all_clock_history($clock_history_id);
        $data['subview'] = $this->load->view('admin/attendance/request_details', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }

    public function delete_request($id)
    {
        $clock_history = $this->attendance_model->check_by(array('clock_history_id' => $id), 'tbl_clock_history');
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'attendance',
            'module_field_id' => $id,
            'activity' => 'activity_delete_timechage_request',
            'icon' => 'fa-clock-o',
            'value1' => lang('clock_in') . ' ' . display_time($clock_history->clockin_edit),
            'value2' => lang('clock_out') . ' ' . display_time($clock_history->clockout_edit),
        );
        // Update into tbl_project
        $this->attendance_model->_table_name = "tbl_activities"; //table name
        $this->attendance_model->_primary_key = "activities_id";
        $this->attendance_model->save($activities);

        //save data into table.
        $this->attendance_model->_table_name = "tbl_clock_history"; // table name
        $this->attendance_model->_primary_key = "clock_history_id"; // $id
        $this->attendance_model->delete($id);

        $type = "success";
        $message = lang('delete_timechage_request');

        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function set_time_status($history_id)
    {
        // get input status
        // if status == 1 its pending
        // if status == 2 its accept  and set timein/timeout into tbl_clock
        // and 3 == reject
        $status = $this->input->post('status', TRUE);
        $clock_history_info = $this->invoice_model->check_by(array('clock_history_id' => $history_id), 'tbl_clock_history');

        if ($status == 2) {
            $clock_id = $this->input->post('clock_id', TRUE);
            $clockin_time = $this->input->post('clockin_time', TRUE);
            if (!empty($clockin_time)) {
                $data['clockin_time'] = $clockin_time;
            }
            $clockout_time = $this->input->post('clockout_time', TRUE);
            if (!empty($clockout_time)) {
                $data['clockout_time'] = $clockout_time;
            }
            $this->attendance_model->_table_name = 'tbl_clock';
            $this->attendance_model->_primary_key = 'clock_id';
            $this->attendance_model->save($data, $clock_id);
            $adata['status'] = $status;

            $this->attendance_model->_table_name = 'tbl_clock_history';
            $this->attendance_model->_primary_key = 'clock_history_id';
            $this->attendance_model->save($adata, $history_id);

            $type = "success";
            $message = lang('time_change_request') . ' ' . lang('accepted');
            $description = 'time_change_request_accepted';
        } else {
            $data['status'] = $status;
            $this->attendance_model->_table_name = 'tbl_clock_history';
            $this->attendance_model->_primary_key = 'clock_history_id';
            $this->attendance_model->save($data, $history_id);

            $type = "error";
            $message = lang('time_change_request') . ' ' . lang('rejected');
            $description = 'time_change_request_rejected';
        }
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'attendance',
            'module_field_id' => $history_id,
            'activity' => $description,
            'icon' => 'fa-clock-o',
            'link' => 'admin/attendance/view_changerequest/' . $history_id,
            'value1' => lang('by') . ' ' . $this->session->userdata('name'),
        );
        // Update into tbl_project
        $this->attendance_model->_table_name = "tbl_activities"; //table name
        $this->attendance_model->_primary_key = "activities_id";
        $this->attendance_model->save($activities);
        $notifyUser = array($clock_history_info->user_id);
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                add_notification(array(
                    'to_user_id' => $v_user,
                    'description' => $description,
                    'icon' => 'clock-o',
                    'link' => 'admin/attendance/view_changerequest/' . $history_id,
                    'value' => lang('by') . ' ' . $this->session->userdata('name'),
                ));
            }
        }
        if (!empty($notifyUser)) {
            show_notification($notifyUser);
        }

        set_message($type, $message);
        redirect('admin/attendance/timechange_request'); //redirect page
    }

    /**
     * Clear / Delete all attendance punch photos from uploads folder to save storage
     */
    public function clear_attendance_photos()
    {
        $dir = FCPATH . 'uploads/attendance_photos/';
        $deleted_count = 0;
        $freed_bytes = 0;

        if (is_dir($dir)) {
            $files = glob($dir . '*');
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $freed_bytes += filesize($file);
                        if (@unlink($file)) {
                            $deleted_count++;
                        }
                    }
                }
            }
        }

        if ($this->db->table_exists('tbl_attendance_face_logs')) {
            $this->db->update('tbl_attendance_face_logs', [
                'photo_thumbnail' => null,
                'photo_path'      => null
            ]);
        }

        $freed_mb = round($freed_bytes / (1024 * 1024), 2);
        $msg = "Attendance photos cleared successfully! Total " . $deleted_count . " photos removed (" . $freed_mb . " MB storage freed).";

        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 'success', 'message' => $msg, 'deleted_count' => $deleted_count, 'freed_mb' => $freed_mb]);
            exit();
        } else {
            set_message('success', $msg);
            redirect('admin/attendance/attendance_report');
        }
    }

    public function attendance_report($departments_id = null, $type = null, $date = null, $pdf = null, $end_date = null)
    {
        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['all_offices'] = $this->db->table_exists('tbl_office_locations') ? $this->db->where('status', 1)->get('tbl_office_locations')->result() : [];

        if (empty($departments_id)) {
            $departments_id = $this->input->post('departments_id', TRUE) ?: $this->input->get('departments_id', TRUE);
        }
        $office_location_id = $this->input->post('office_location_id', TRUE) ?: $this->input->get('office_location_id', TRUE);

        if (empty($date)) {
            $date = $this->input->post('date', TRUE) ?: $this->input->get('date', TRUE);
        }
        if (empty($date)) {
            $date = date('Y-m');
        }

        $report_data = $this->build_attendance_summary_report($departments_id, $office_location_id, $date);
        $data = array_merge($data, $report_data);

        $data['subview'] = $this->load->view('admin/attendance/attendance_report', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_report($departments_id = null, $type = null, $date = null, $pdf = null, $end_date = null)
    {
        $this->attendance_report($departments_id, $type, $date, $pdf, $end_date);
    }

    private function build_attendance_summary_report($departments_id = null, $office_location_id = null, $date = null)
    {
        if (empty($date)) {
            $date = date('Y-m');
        }

        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = sprintf('%04d-%02d-%02d', $year, $month, $days_in_month);

        // 1. Week-offs from tbl_working_days
        $non_working_days = [];
        $working_days_q = $this->db->select('tbl_days.day')
            ->from('tbl_working_days')
            ->join('tbl_days', 'tbl_days.day_id = tbl_working_days.day_id', 'left')
            ->where('tbl_working_days.flag', 0)
            ->get()->result();
        if (!empty($working_days_q)) {
            foreach ($working_days_q as $wd) {
                $non_working_days[] = strtolower($wd->day);
            }
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

        // 2. Public holidays
        $holiday_dates = [];
        $holidays_q = $this->db->where('start_date <=', $end_date)
            ->where('end_date >=', $start_date)
            ->get('tbl_holiday')->result();
        if (!empty($holidays_q)) {
            foreach ($holidays_q as $h) {
                $s = max($start_date, $h->start_date);
                $e = !empty($h->end_date) ? min($end_date, $h->end_date) : $s;
                $cur = strtotime($s);
                $last = strtotime($e);
                while ($cur <= $last) {
                    $dt = date('Y-m-d', $cur);
                    if (!isset($week_off_dates[$dt])) {
                        $holiday_dates[$dt] = $h->event_name;
                    }
                    $cur = strtotime('+1 day', $cur);
                }
            }
        }
        $total_holidays = count($holiday_dates);

        // 3. Batch fetch all attendance for the month
        $att_by_user = [];
        $att_q = $this->db->select('user_id, date_in, attendance_status')
            ->where('date_in >=', $start_date)
            ->where('date_in <=', $end_date)
            ->get('tbl_attendance')->result();
        if (!empty($att_q)) {
            foreach ($att_q as $ar) {
                $att_by_user[$ar->user_id][$ar->date_in] = $ar->attendance_status;
            }
        }

        // 4. Batch fetch all approved leaves for the month
        $leaves_by_user = [];
        $leaves_q = $this->db->select('user_id, leave_start_date, leave_end_date, leave_type')
            ->where('application_status', 2)
            ->where('leave_start_date <=', $end_date)
            ->group_start()
                ->where('leave_end_date >=', $start_date)
                ->or_where('leave_end_date IS NULL', null, false)
            ->group_end()
            ->get('tbl_leave_application')->result();
        if (!empty($leaves_q)) {
            foreach ($leaves_q as $lr) {
                $uid = $lr->user_id;
                $s = max($start_date, $lr->leave_start_date);
                $e = !empty($lr->leave_end_date) ? min($end_date, $lr->leave_end_date) : $s;
                $cur = strtotime($s);
                $last = strtotime($e);
                while ($cur <= $last) {
                    $dt = date('Y-m-d', $cur);
                    if (!isset($week_off_dates[$dt]) && !isset($holiday_dates[$dt])) {
                        $leaves_by_user[$uid][$dt] = $lr->leave_type;
                    }
                    $cur = strtotime('+1 day', $cur);
                }
            }
        }

        // 5. Query active staff
        $has_office_col = $this->db->field_exists('office_location_id', 'tbl_account_details');
        $has_office_tbl = $this->db->table_exists('tbl_office_locations');

        $office_select = $has_office_col ? 'ad.office_location_id as office_id' : 'NULL as office_id';
        $branch_select = ($has_office_col && $has_office_tbl) ? 'COALESCE(ol.location_name, "HC Interior Head Office, Noida") as office_branch' : '"HC Interior Head Office, Noida" as office_branch';

        $this->db->select('
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
            ' . $office_select . ',
            ' . $branch_select . '
        ', false);
        $this->db->from('tbl_users u');
        $this->db->join('tbl_account_details ad', 'ad.user_id = u.user_id', 'inner');
        $this->db->join('tbl_designations d', 'd.designations_id = ad.designations_id', 'left');
        $this->db->join('tbl_departments dep', 'dep.departments_id = d.departments_id', 'left');
        if ($has_office_col && $has_office_tbl) {
            $this->db->join('tbl_office_locations ol', 'ol.id = ad.office_location_id', 'left');
        }
        $this->db->where('u.activated', 1);
        $this->db->where('u.role_id !=', 2);

        if (!empty($departments_id)) {
            $this->db->where('dep.departments_id', $departments_id);
        }
        if (!empty($office_location_id) && $has_office_col) {
            $this->db->where('ad.office_location_id', intval($office_location_id));
        }
        $this->db->order_by('ad.fullname', 'ASC');
        $staff_list = $this->db->get()->result();

        // 6. Elapsed working days calculation for Absent estimation
        $today = date('Y-m-d');
        $calc_end = ($end_date > $today) ? $today : $end_date;
        $total_days_so_far = max(0, (strtotime($calc_end) - strtotime($start_date)) / 86400 + 1);

        $wo_so_far = 0;
        foreach ($week_off_dates as $wod => $val) {
            if ($wod <= $calc_end) $wo_so_far++;
        }
        $hol_so_far = 0;
        foreach ($holiday_dates as $hd => $val) {
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

        // Batch fetch latest attendance punch photo per staff
        $latest_face_logs = [];
        if ($this->db->table_exists('tbl_attendance_face_logs')) {
            $face_logs_q = $this->db->query("
                SELECT f1.user_id, f1.photo_thumbnail, f1.photo_path, f1.punch_time, f1.punch_type, f1.address, f1.distance_from_office, f1.is_inside_geofence
                FROM tbl_attendance_face_logs f1
                INNER JOIN (
                    SELECT user_id, MAX(log_id) as max_id
                    FROM tbl_attendance_face_logs
                    WHERE (photo_thumbnail IS NOT NULL AND photo_thumbnail != '') 
                       OR (photo_path IS NOT NULL AND photo_path != '')
                    GROUP BY user_id
                ) f2 ON f1.log_id = f2.max_id
            ")->result();

            if (!empty($face_logs_q)) {
                foreach ($face_logs_q as $fl) {
                    $p_src = !empty($fl->photo_path) ? $fl->photo_path : $fl->photo_thumbnail;
                    if (!empty($p_src)) {
                        $p_url = (strpos($p_src, 'uploads/') === 0) ? base_url($p_src) : $p_src;
                        $latest_face_logs[$fl->user_id] = [
                            'photo_url'   => $p_url,
                            'punch_time'  => date('d M, h:i A', strtotime($fl->punch_time)),
                            'punch_type'  => strtoupper($fl->punch_type),
                            'address'     => $fl->address,
                            'distance'    => round($fl->distance_from_office),
                            'is_inside'   => (bool)$fl->is_inside_geofence
                        ];
                    }
                }
            }
        }

        $staff_records = [];
        if (!empty($staff_list)) {
            foreach ($staff_list as $u) {
                $uid = $u->user_id;
                $present = 0;
                $half_day = 0;
                $explicit_absent = 0;

                if (isset($att_by_user[$uid])) {
                    foreach ($att_by_user[$uid] as $dt => $st) {
                        if ($st == 1) $present++;
                        elseif ($st == 2) $half_day++;
                        elseif ($st == 0) $explicit_absent++;
                    }
                }

                $leave = 0;
                if (isset($leaves_by_user[$uid])) {
                    $leave = count($leaves_by_user[$uid]);
                }

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

                $phone = !empty($u->mobile) ? trim((string)$u->mobile) : (!empty($u->phone) ? trim((string)$u->phone) : '-');
                $emp_id = !empty($u->emp_code) ? trim((string)$u->emp_code) : (!empty($u->employment_id) ? trim((string)$u->employment_id) : '-');

                $name = trim((string)$u->fullname);
                // Compute 2-letter uppercase initials for avatar
                $parts = preg_split('/\s+/', $name);
                $initials = '';
                if (count($parts) >= 2) {
                    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
                } elseif (count($parts) == 1 && strlen($parts[0]) > 0) {
                    $initials = strtoupper(substr($parts[0], 0, min(2, strlen($parts[0]))));
                }
                if (empty($initials)) {
                    $initials = 'EM';
                }

                $staff_records[] = [
                    'user_id' => $uid,
                    'fullname' => $name,
                    'initials' => $initials,
                    'avatar' => $u->avatar,
                    'phone' => $phone,
                    'employee_id' => $emp_id,
                    'job_title' => !empty($u->designations) ? $u->designations : '-',
                    'department' => !empty($u->deptname) ? $u->deptname : '-',
                    'office_branch' => !empty($u->office_branch) ? $u->office_branch : 'HC Interior Head Office, Noida',
                    'present' => $present,
                    'absent' => $absent,
                    'half_day' => $half_day,
                    'leave' => $leave,
                    'week_off' => $total_week_offs,
                    'holiday' => $total_holidays,
                    'attendance_photo' => $latest_face_logs[$uid] ?? null,
                ];
            }
        }

        return [
            'staff_records' => $staff_records,
            'summary' => $summary,
            'date' => $date,
            'month' => date('F Y', strtotime($start_date)),
            'month_num' => $month,
            'year' => $year,
            'departments_id' => $departments_id,
            'office_location_id' => $office_location_id,
            'total_days_in_month' => $days_in_month,
            'total_week_offs' => $total_week_offs,
            'total_holidays' => $total_holidays,
        ];
    }

    public function get_report_7($departments_id, $type, $date, $pdf = null)
    {
        if (is_array($date)) {
            $start_date = $date['start_date'];
            $end_date = $date['end_date'];
            // get month and year from start date and end date and check if they are equal or not
            $start_month = date('m', strtotime($start_date));
            $start_year = date('Y', strtotime($start_date));
            $end_month = date('m', strtotime($end_date));
            $end_year = date('Y', strtotime($end_date));
            if ($start_month == $end_month && $start_year == $end_year) {
                $yymm = $start_year . '-' . $start_month;
            } else {
                $yymm = array();
                $yymm['start_month'] = $start_year . '-' . $start_month;
                $yymm['end_month'] = $end_year . '-' . $end_month;
            }
            $all_dates = $this->attendance_model->GetDays($start_date, $end_date);
            $data['dates'] = $all_dates;
            $data['dateSl'] = $all_dates;
            $data['total_days'] = count(array($all_dates));
        } else {
            $month = date('n', strtotime($date));
            $year = date('Y', strtotime($date));
            $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            if ($month >= 1 && $month <= 9) {
                $yymm = $year . '-' . '0' . $month;
            } else {
                $yymm = $year . '-' . $month;
            }
            $all_dates = array();
            for ($i = 1; $i <= $num; $i++) {
                $data['dateSl'][] = $i;
                $data['dates'][] = $yymm . '-' . $i;
                $all_dates[] = $yymm . '-' . $i;
            }
            $data['total_days'] = $num;
        }
        if (!empty($departments_id)) {
            $data['employee'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['employee'] = get_staff_details();
        }
        $total_holiday = 0;
        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday
        // total keys of $holidays array using array_keys
        if (!empty($holidays)) {
            $total_holiday += count(array($holidays));
        }
        if (is_array($yymm)) {
            $public_holiday_start_month = $this->common_model->get_public_holidays($yymm['start_month']); //tbl a_calendar Days Holiday
            $public_holiday_end_month = $this->common_model->get_public_holidays($yymm['end_month']); //tbl a_calendar Days Holiday
            // merge two array
            $public_holiday = array_merge($public_holiday_start_month, $public_holiday_end_month);
        } else {
            $public_holiday = $this->common_model->get_public_holidays($yymm);
        }


        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->attendance_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }

        if (!empty($p_hday)) {
            $total_holiday += count(array($p_hday));
        }


        foreach ($data['employee'] as $sl => $v_employee) {
            foreach ($all_dates as $key => $sdate) {
                $day_name = date('l', strtotime($sdate));
                if (!empty($holidays)) {
                    foreach ($holidays as $v_holiday) {
                        if ($v_holiday->day == $day_name) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($p_hday)) {
                    foreach ($p_hday as $v_hday) {
                        if ($v_hday == $sdate) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($flag)) {
                    $data['attendance'][$sl][] = $this->attendance_model->attendance_report_2_by_empid($v_employee->user_id, $sdate, $flag);
                } else {
                    $data['attendance'][$sl][] = $this->attendance_model->attendance_report_2_by_empid($v_employee->user_id, $sdate);
                }
                $flag = '';
            }
        }

        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['departments_id'] = $this->input->post('departments_id', TRUE);
        $data['date'] = $this->input->post('date', TRUE);
        $where = array('departments_id' => $departments_id);
        $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');
        // total holiday
        $data['total_holiday'] = $total_holiday;

        if (!empty($yymm['start_month'])) {
            $data['month'] = date('F-Y', strtotime($yymm['start_month']));
        } else {
            $data['month'] = date('F-Y', strtotime($yymm));
        }
        $data['search_type'] = $type;
        $data['start_date'] = (!empty($start_date) ? $start_date : '');
        $data['end_date'] = (!empty($end_date) ? $end_date : '');
        if (!empty($pdf)) {
            $viewfile = $this->load->view('admin/attendance/attendance_report_7_pdf', $data, TRUE);
            $this->load->helper('dompdf');
            pdf_create($viewfile, lang('attendance_report') . ' ' . $data['month']);
        } else {
            $data['subview'] = $this->load->view('admin/attendance/attendance_report_7', $data, TRUE);
            $this->load->view('admin/_layout_main', $data);
        }


    }

    public function get_report_2()
    {
        $departments_id = $this->input->post('departments_id', TRUE);
        $date = $this->input->post('date', TRUE);
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if (!empty($departments_id)) {
            $data['employee'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['employee'] = get_staff_details();
        }
        $day = date('d', strtotime($date));
        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday

        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }
        for ($i = 1; $i <= $num; $i++) {
            $data['dateSl'][] = $i;
        }

        $public_holiday = $this->common_model->get_public_holidays($yymm);


        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->attendance_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }
        foreach ($data['employee'] as $sl => $v_employee) {
            $key = 1;
            $x = 0;
            for ($i = 1; $i <= $num; $i++) {

                if ($i >= 1 && $i <= 9) {

                    $sdate = $yymm . '-' . '0' . $i;
                } else {
                    $sdate = $yymm . '-' . $i;
                }
                $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));


                if (!empty($holidays)) {
                    foreach ($holidays as $v_holiday) {

                        if ($v_holiday->day == $day_name) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($p_hday)) {
                    foreach ($p_hday as $v_hday) {
                        if ($v_hday == $sdate) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($flag)) {
                    $data['attendance'][$sl][] = $this->attendance_model->attendance_report_2_by_empid($v_employee->user_id, $sdate, $flag);
                } else {
                    $data['attendance'][$sl][] = $this->attendance_model->attendance_report_2_by_empid($v_employee->user_id, $sdate);
                }

                $key++;
                $flag = '';
            }
        }

        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['departments_id'] = $this->input->post('departments_id', TRUE);
        $data['date'] = $this->input->post('date', TRUE);
        $where = array('departments_id' => $departments_id);
        $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');

        $data['month'] = date('F-Y', strtotime($yymm));
        $data['subview'] = $this->load->view('admin/attendance/attendance_report_2', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_report_3()
    {
        $departments_id = $this->input->post('departments_id', TRUE);
        $date = $this->input->post('date', TRUE);

        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);


        if (!empty($departments_id)) {
            $data['employee'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['employee'] = get_staff_details();
        }

        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday

        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }

        $public_holiday = $this->common_model->get_public_holidays($yymm);

        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->attendance_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }

        foreach ($data['employee'] as $sl => $v_employee) {
            $key = 1;
            $x = 0;
            for ($i = 1; $i <= $num; $i++) {

                if ($i >= 1 && $i <= 9) {
                    $sdate = $yymm . '-' . '0' . $i;
                } else {
                    $sdate = $yymm . '-' . $i;
                }
                $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));

                $data['week_info'][date('W', strtotime($sdate))][$sdate] = $sdate;
                // get leave info

                if (!empty($holidays)) {
                    foreach ($holidays as $v_holiday) {
                        if ($v_holiday->day == $day_name) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($p_hday)) {
                    foreach ($p_hday as $v_hday) {
                        if ($v_hday == $sdate) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($flag)) {
                    $data['attendace_info'][$sl][date('W', strtotime($sdate))][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                } else {
                    $data['attendace_info'][$sl][date('W', strtotime($sdate))][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                }
                $key++;
                $flag = '';
            }
        }

        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['departments_id'] = $this->input->post('departments_id', TRUE);
        $data['date'] = $this->input->post('date', TRUE);
        $where = array('departments_id' => $departments_id);
        $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');

        $data['month'] = date('F Y', strtotime($yymm));
        $data['subview'] = $this->load->view('admin/attendance/attendance_report_3', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function attendance_pdf($type, $departments_id = null, $date)
    {
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if (!empty($departments_id)) {
            $data['employee'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['employee'] = get_staff_details();
        }
        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday

        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }

        $public_holiday = $this->common_model->get_public_holidays($yymm);

        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->attendance_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }
        if ($type == 1) {
            foreach ($data['employee'] as $sl => $v_employee) {
                $key = 1;
                $x = 0;
                for ($i = 1; $i <= $num; $i++) {

                    if ($i >= 1 && $i <= 9) {
                        $sdate = $yymm . '-' . '0' . $i;
                    } else {
                        $sdate = $yymm . '-' . $i;
                    }
                    $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));

                    $data['week_info'][date('W', strtotime($sdate))][$sdate] = $sdate;

                    // get leave info
                    if (!empty($holidays)) {
                        foreach ($holidays as $v_holiday) {
                            if ($v_holiday->day == $day_name) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($p_hday)) {
                        foreach ($p_hday as $v_hday) {
                            if ($v_hday == $sdate) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($flag)) {
                        $data['attendace_info'][date('W', strtotime($sdate))][$sdate][$v_employee->user_id] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                    } else {
                        $data['attendace_info'][date('W', strtotime($sdate))][$sdate][$v_employee->user_id] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                    }
                    $key++;
                    $flag = '';
                }
            }
            $data['title'] = lang('attendance_report');
            $where = array('departments_id' => $departments_id);
            $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');

            $data['month'] = date('F-Y', strtotime($yymm));
            $subview = 'attendance_report_pdf';
        } elseif ($type == 2) {

            foreach ($data['employee'] as $sl => $v_employee) {
                $key = 1;
                $x = 0;
                for ($i = 1; $i <= $num; $i++) {

                    if ($i >= 1 && $i <= 9) {

                        $sdate = $yymm . '-' . '0' . $i;
                    } else {
                        $sdate = $yymm . '-' . $i;
                    }
                    $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));


                    if (!empty($holidays)) {
                        foreach ($holidays as $v_holiday) {

                            if ($v_holiday->day == $day_name) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($p_hday)) {
                        foreach ($p_hday as $v_hday) {
                            if ($v_hday == $sdate) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($flag)) {
                        $data['attendance'][$sl][] = $this->attendance_model->attendance_report_2_by_empid($v_employee->user_id, $sdate, $flag);
                    } else {
                        $data['attendance'][$sl][] = $this->attendance_model->attendance_report_2_by_empid($v_employee->user_id, $sdate);
                    }

                    $key++;
                    $flag = '';
                }
            }

            $data['title'] = lang('attendance_report');
            $where = array('departments_id' => $departments_id);
            $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');
            $data['month'] = date('F-Y', strtotime($yymm));
            $subview = 'attendance_report_2_pdf';
        } elseif ($type == 6) {
            foreach ($data['employee'] as $sl => $v_employee) {
                $key = 1;
                $x = 0;
                for ($i = 1; $i <= $num; $i++) {
                    if ($i >= 1 && $i <= 9) {
                        $sdate = $yymm . '-' . '0' . $i;
                    } else {
                        $sdate = $yymm . '-' . $i;
                    }
                    $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));


                    // get leave info

                    if (!empty($holidays)) {
                        foreach ($holidays as $v_holiday) {
                            if ($v_holiday->day == $day_name) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($p_hday)) {
                        foreach ($p_hday as $v_hday) {
                            if ($v_hday == $sdate) {
                                $flag = 'H';
                            }
                        }
                    }
                    $data['dateName'][$sdate] = $sdate;
                    if (!empty($flag)) {
                        $data['attendace_info'][$sdate][$v_employee->fullname . '(' . $v_employee->employment_id . ')'] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                    } else {
                        $data['attendace_info'][$sdate][$v_employee->fullname . '(' . $v_employee->employment_id . ')'] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                    }
                    $key++;
                    $flag = '';
                }
            }
            $data['title'] = lang('attendance_report');
            $where = array('departments_id' => $departments_id);
            $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');

            $data['month'] = date('F Y', strtotime($yymm));
            $subview = 'attendance_report_6_pdf';
        } else {
            foreach ($data['employee'] as $sl => $v_employee) {
                $key = 1;
                $x = 0;
                for ($i = 1; $i <= $num; $i++) {

                    if ($i >= 1 && $i <= 9) {
                        $sdate = $yymm . '-' . '0' . $i;
                    } else {
                        $sdate = $yymm . '-' . $i;
                    }
                    $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));

                    $data['week_info'][date('W', strtotime($sdate))][$sdate] = $sdate;
                    // get leave info

                    if (!empty($holidays)) {
                        foreach ($holidays as $v_holiday) {
                            if ($v_holiday->day == $day_name) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($p_hday)) {
                        foreach ($p_hday as $v_hday) {
                            if ($v_hday == $sdate) {
                                $flag = 'H';
                            }
                        }
                    }
                    if (!empty($flag)) {
                        $data['attendace_info'][$sl][date('W', strtotime($sdate))][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                    } else {
                        $data['attendace_info'][$sl][date('W', strtotime($sdate))][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                    }
                    $key++;
                    $flag = '';
                }
            }

            $data['title'] = lang('attendance_report');
            $where = array('departments_id' => $departments_id);
            $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');

            $data['month'] = date('F Y', strtotime($yymm));
            $subview = 'attendance_report_3_pdf';
        }
        $data['title'] = lang('attendance_report'); //Page title
        $this->load->helper('dompdf');

        $viewfile = $this->load->view('admin/attendance/' . $subview, $data, TRUE);
        pdf_create($viewfile, slug_it($data['month'] . '-' . $data['dept_name']->deptname));
    }

    public function get_report_4()
    {
        $departments_id = $this->input->post('departments_id', TRUE);

        $date = $this->input->post('date', TRUE);


        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);


        if (!empty($departments_id)) {
            $data['users'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['users'] = get_staff_details();
        }
        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday

        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }

        $public_holiday = $this->common_model->get_public_holidays($yymm);

        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->attendance_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }

        foreach ($data['users'] as $sl => $v_employee) {
            $key = 1;
            $x = 0;
            for ($i = 1; $i <= $num; $i++) {

                if ($i >= 1 && $i <= 9) {
                    $sdate = $yymm . '-' . '0' . $i;
                } else {
                    $sdate = $yymm . '-' . $i;
                }
                $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));

                // get leave info

                if (!empty($holidays)) {
                    foreach ($holidays as $v_holiday) {
                        if ($v_holiday->day == $day_name) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($p_hday)) {
                    foreach ($p_hday as $v_hday) {
                        if ($v_hday == $sdate) {
                            $flag = 'H';
                        }
                    }
                }
                $data['dateName'][$sdate] = $sdate;
                if (!empty($flag)) {
                    $data['attendace_info'][$sl][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                } else {
                    $data['attendace_info'][$sl][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                }
                $key++;
                $flag = '';
            }
        }


        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['departments_id'] = $this->input->post('departments_id', TRUE);
        $data['date'] = $this->input->post('date', TRUE);
        $where = array('departments_id' => $departments_id);
        $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');
        $data['month'] = date('F Y', strtotime($yymm));
        $data['subview'] = $this->load->view('admin/attendance/attendance_report_4', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_report_5()
    {
        $departments_id = $this->input->post('departments_id', TRUE);
        $date = $this->input->post('date', TRUE);

        $data['date'] = $date;
        $payment_month = $date;

        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $day = date('d', strtotime($date));
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);


        if (!empty($departments_id)) {
            $data['users'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['users'] = get_staff_details();
        }
        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday

        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }

        $public_holiday = $this->common_model->get_public_holidays($yymm);

        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->common_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }
        foreach ($data['users'] as $sl => $v_employee) {
            $key = 1;
            $x = 0;
            for ($i = 1; $i <= $num; $i++) {
                if ($i >= 1 && $i <= 9) {
                    $sdate = $yymm . '-' . '0' . $i;
                } else {
                    $sdate = $yymm . '-' . $i;
                }
                $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));


                // get leave info

                if (!empty($holidays)) {
                    foreach ($holidays as $v_holiday) {
                        if ($v_holiday->day == $day_name) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($p_hday)) {
                    foreach ($p_hday as $v_hday) {
                        if ($v_hday == $sdate) {
                            $flag = 'H';
                        }
                    }
                }
                $data['dateName'][$sdate] = $sdate;
                if (!empty($flag)) {
                    $data['attendace_info'][$sl][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                } else {
                    $data['attendace_info'][$sl][$sdate] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                }
                $key++;
                $flag = '';
                // $data['overtime_info'][$sl][$sdate] = $this->attendance_model->get_overtime_info_by_id($v_employee->user_id, $sdate);
                // $data['overtime_info'][$sl][$sdate] = $this->attendance_model->get_overtime_info_by_id($v_employee->user_id, $payment_month);
            }
        }

        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['departments_id'] = $this->input->post('departments_id', TRUE);
        $data['date'] = $this->input->post('date', TRUE);
        $where = array('departments_id' => $departments_id);
        $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');
        $data['month'] = date('F Y', strtotime($yymm));
        $data['subview'] = $this->load->view('admin/attendance/attendance_report_5', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_report_6()
    {
        $departments_id = $this->input->post('departments_id', TRUE);
        $date = $this->input->post('date', TRUE);

        $data['date'] = $date;
        $payment_month = $date;

        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        $day = date('d', strtotime($date));
        $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        if (!empty($departments_id)) {
            $data['users'] = $this->attendance_model->get_employee_id_by_dept_id($departments_id);
        } else {
            $data['users'] = get_staff_details();
        }


        $holidays = $this->common_model->get_holidays(); //tbl working Days Holiday

        if ($month >= 1 && $month <= 9) {
            $yymm = $year . '-' . '0' . $month;
        } else {
            $yymm = $year . '-' . $month;
        }

        $public_holiday = $this->common_model->get_public_holidays($yymm);

        //tbl a_calendar Days Holiday
        if (!empty($public_holiday)) {
            foreach ($public_holiday as $p_holiday) {
                $p_hday = $this->common_model->GetDays($p_holiday->start_date, $p_holiday->end_date);
            }
        }
        foreach ($data['users'] as $sl => $v_employee) {
            $key = 1;
            $x = 0;
            for ($i = 1; $i <= $num; $i++) {
                if ($i >= 1 && $i <= 9) {
                    $sdate = $yymm . '-' . '0' . $i;
                } else {
                    $sdate = $yymm . '-' . $i;
                }
                $day_name = date('l', strtotime("+$x days", strtotime($year . '-' . $month . '-' . $key)));


                // get leave info

                if (!empty($holidays)) {
                    foreach ($holidays as $v_holiday) {
                        if ($v_holiday->day == $day_name) {
                            $flag = 'H';
                        }
                    }
                }
                if (!empty($p_hday)) {
                    foreach ($p_hday as $v_hday) {
                        if ($v_hday == $sdate) {
                            $flag = 'H';
                        }
                    }
                }
                $data['dateName'][$sdate] = $sdate;
                if (!empty($flag)) {
                    $data['attendace_info'][$sdate][$v_employee->fullname . '(' . $v_employee->employment_id . ')'] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate, $flag);
                } else {
                    $data['attendace_info'][$sdate][$v_employee->fullname . '(' . $v_employee->employment_id . ')'] = $this->attendance_model->attendance_report_by_empid($v_employee->user_id, $sdate);
                }
                $key++;
                $flag = '';
            }
        }

        $data['title'] = lang('attendance_report');
        $data['all_department'] = $this->attendance_model->select_data('tbl_departments', 'departments_id', 'deptname');
        $data['departments_id'] = $this->input->post('departments_id', TRUE);
        $data['date'] = $this->input->post('date', TRUE);
        $where = array('departments_id' => $departments_id);
        $data['dept_name'] = $this->attendance_model->check_by($where, 'tbl_departments');
        $data['month'] = date('F Y', strtotime($yymm));
        $data['subview'] = $this->load->view('admin/attendance/attendance_report_6', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function workplace()
    {
        $data['title'] = lang('workplace');
        $data['active'] = 1;
        $data['subview'] = $this->load->view('admin/attendance/workplace', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function new_workplace($id = null)
    {

        $data['title'] = lang('new_workplace');
        if ($id) {
            $data['active'] = 2;
            $can_edit = $this->attendance_model->can_action('tbl_workplace', 'edit', array('workplace_id' => $id));
            $edited = can_action('36', 'edited');
            if (!empty($can_edit) && !empty($edited)) {
                $data['workplace_info'] = $this->attendance_model->check_by(array('workplace_id' => $id), 'tbl_workplace');
            }
        }
        $data['active'] = 2;
        $data['subview'] = $this->load->view('admin/attendance/new_workplace', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function save_workplace($id = null)
    {
        $created = can_action('36', 'created');
        $edited = can_action('36', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $this->attendance_model->_table_name = 'tbl_workplace';
            $this->attendance_model->_primary_key = 'workplace_id';
            $data = $this->attendance_model->array_from_post(array('location_name', 'workplace_details', 'workplace_location', 'latitude', 'longitude', 'allowed_radius'));
            // update root category
            $where = array('location_name' => $data['location_name']);
            // duplicate value check in DB
            if (!empty($id)) { // if id exist in db update data
                $workplace_id = array('workplace_id !=' => $id);
            } else { // if id is not exist then set id as null
                $workplace_id = null;
            }

            $user_id = $this->input->post('user_id', true);
            if (!empty($user_id)) {
                foreach ($user_id as $value) {
                    $check_workplace = get_row('tbl_workplace', array('user_id' => $value));
                    $sdata = $data;
                    $sdata['user_id'] = $value;
                    if (!empty($check_workplace)) {
                        $return_id = $this->attendance_model->save($sdata, $check_workplace->workplace_id);
                    } else {
                        $return_id = $this->attendance_model->save($sdata);
                    }
                }
            }

            if (!empty($id)) {
                $id = $id;
                $action = 'activity_update_workplace';
                $msg = lang('update_workplace');
            } else {
                $id = $return_id;
                $action = 'activity_save_workplace';
                $msg = lang('save_workplace');
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'attendance',
                'module_field_id' => $id,
                'activity' => $action,
                'icon' => 'fa-circle-o',
                'value1' => $data['location_name']
            );
            $this->attendance_model->_table_name = 'tbl_activities';
            $this->attendance_model->_primary_key = 'activities_id';
            $this->attendance_model->save($activity);
            // messages for user
            $type = "success";
            $message = $msg;
            set_message($type, $message);
        }
        redirect('admin/attendance/workplace');
    }

    public function attendancetList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_workplace';
            $this->datatables->join_table = array('tbl_account_details');
            $this->datatables->join_where = array('tbl_workplace.user_id=tbl_account_details.user_id');
            $this->datatables->column_order = array('location_name', 'workplace_details', 'workplace_location', 'latitude', 'longitude', 'allowed_radius');
            $this->datatables->column_search = array('location_name', 'workplace_details', 'workplace_location', 'latitude', 'longitude', 'allowed_radius');
            $this->datatables->order = array('workplace_id' => 'desc');

            $fetch_data = make_datatables();

            $data = array();
            $edited = can_action_by_label('workplace', 'edited');
            $deleted = can_action_by_label('workplace', 'deleted');
            foreach ($fetch_data as $key => $row) {
                $action = null;
                $sub_array = array();
                $sub_array[] = $row->fullname;
                $sub_array[] = $row->location_name;
                $sub_array[] = $row->workplace_details;
                $sub_array[] = $row->workplace_location;
                $sub_array[] = $row->allowed_radius;

                if (!empty($edited)) {
                    $action .= btn_edit('admin/attendance/new_workplace/' . $row->workplace_id) . ' ';
                }
                if (!empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/attendance/delete_workplace/$row->workplace_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $key));
                }

                //            echo $action;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function delete_workplace($id)
    {
        $deleted = $deleted = can_action('36', 'deleted');
        if (!empty($deleted)) {
            $workplace_info = $this->attendance_model->check_by(array('workplace_id' => $id), 'tbl_workplace');
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'attendance',
                'module_field_id' => $id,
                'activity' => ('activity_deleted_attendance'),
                'value1' => $workplace_info->location_name
            );
            $this->attendance_model->_table_name = 'tbl_activities';
            $this->attendance_model->_primary_key = 'activities_id';
            $this->attendance_model->save($activity);

            $this->attendance_model->_table_name = 'tbl_workplace';
            $this->attendance_model->_primary_key = 'workplace_id';
            $this->attendance_model->delete($id);

            $type = "success";
            $msg = lang('delete_successfully');
            echo json_encode(array("status" => $type, 'message' => $msg));
            exit();
        }
    }

    public function approve_attendance($attendance_id = null)
    {
        if (empty($attendance_id)) {
            $attendance_id = $this->input->post('attendance_id', TRUE);
        }
        $admin = admin();
        $user_type = $this->session->userdata('user_type');
        if (empty($admin) && $user_type != 1) {
            echo json_encode(array('status' => 'error', 'message' => 'Unauthorized action.'));
            exit();
        }

        $att = $this->db->where('attendance_id', $attendance_id)->get('tbl_attendance')->row();
        if (empty($att)) {
            echo json_encode(array('status' => 'error', 'message' => 'Attendance record not found.'));
            exit();
        }

        $this->db->where('attendance_id', $attendance_id)->update('tbl_attendance', array(
            'attendance_status' => 1
        ));

        $user_info = $this->db->where('user_id', $att->user_id)->get('tbl_account_details')->row();
        $emp_name = !empty($user_info->fullname) ? $user_info->fullname : 'Employee';

        echo json_encode(array(
            'status' => 'success',
            'message' => 'Attendance for ' . $emp_name . ' on ' . $att->date_in . ' has been APPROVED as Present!'
        ));
        exit();
    }

    public function reject_attendance($attendance_id = null)
    {
        if (empty($attendance_id)) {
            $attendance_id = $this->input->post('attendance_id', TRUE);
        }
        $admin = admin();
        $user_type = $this->session->userdata('user_type');
        if (empty($admin) && $user_type != 1) {
            echo json_encode(array('status' => 'error', 'message' => 'Unauthorized action.'));
            exit();
        }

        $att = $this->db->where('attendance_id', $attendance_id)->get('tbl_attendance')->row();
        if (empty($att)) {
            echo json_encode(array('status' => 'error', 'message' => 'Attendance record not found.'));
            exit();
        }

        $this->db->where('attendance_id', $attendance_id)->update('tbl_attendance', array(
            'attendance_status' => 0
        ));

        $user_info = $this->db->where('user_id', $att->user_id)->get('tbl_account_details')->row();
        $emp_name = !empty($user_info->fullname) ? $user_info->fullname : 'Employee';

        echo json_encode(array(
            'status' => 'success',
            'message' => 'Attendance for ' . $emp_name . ' on ' . $att->date_in . ' has been marked as Absent / Rejected.'
        ));
        exit();
    }

    public function get_pending_attendances()
    {
        $user_id = $this->input->post('user_id', TRUE) ?: $this->input->get('user_id', TRUE);
        
        $this->db->select('tbl_attendance.*, tbl_account_details.fullname, tbl_account_details.employment_id, tbl_designations.designations');
        $this->db->from('tbl_attendance');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_attendance.user_id', 'left');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left');
        $this->db->where('tbl_attendance.attendance_status', 2);
        if (!empty($user_id)) {
            $this->db->where('tbl_attendance.user_id', $user_id);
        }
        $this->db->order_by('tbl_attendance.date_in', 'DESC');
        $pending = $this->db->get()->result();

        $result = array();
        foreach ($pending as $p) {
            $clock = $this->db->where('attendance_id', $p->attendance_id)->order_by('clock_id', 'ASC')->get('tbl_clock')->row();
            $face_log = $this->db->where('attendance_id', $p->attendance_id)->order_by('log_id', 'DESC')->get('tbl_attendance_face_logs')->row();

            $lat = (!empty($clock) && !empty($clock->latitude)) ? $clock->latitude : (!empty($face_log) ? $face_log->lat : '');
            $lng = (!empty($clock) && !empty($clock->longitude)) ? $clock->longitude : (!empty($face_log) ? $face_log->lng : '');
            $location = (!empty($clock) && !empty($clock->location)) ? $clock->location : (!empty($face_log) ? $face_log->address : '');
            $distance = (!empty($face_log) && !empty($face_log->distance_from_office)) ? round($face_log->distance_from_office) . 'm' : '';

            $maps_url = (!empty($lat) && !empty($lng)) ? 'https://www.google.com/maps?q=' . urlencode($lat . ',' . $lng) : '';

            $result[] = array(
                'attendance_id' => $p->attendance_id,
                'user_id'       => $p->user_id,
                'fullname'      => $p->fullname ?: 'Employee #' . $p->user_id,
                'employment_id' => $p->employment_id ?: '',
                'designations'  => $p->designations ?: '',
                'date'          => $p->date_in,
                'clockin'       => (!empty($clock) && !empty($clock->clockin_time)) ? display_time($clock->clockin_time) : '-',
                'clockout'      => (!empty($clock) && !empty($clock->clockout_time)) ? display_time($clock->clockout_time) : '-',
                'lat'           => $lat,
                'lng'           => $lng,
                'location'      => $location,
                'distance'      => $distance,
                'maps_url'      => $maps_url,
                'comments'      => !empty($clock) ? $clock->comments : ''
            );
        }

        echo json_encode(array('status' => 'success', 'data' => $result));
        exit();
    }

    /**
     * Face ID Requests & Biometric Management List
     */
    public function face_id_requests()
    {
        $this->lang->load('utilities');
        $this->lang->load('main');
        $data['title'] = (lang('face_id_management') && lang('face_id_management') !== 'face_id_management') ? lang('face_id_management') : 'Face ID Management';
        $status_filter = $this->input->get('status', true);
        $dept_filter   = $this->input->get('department_id', true);
        $office_filter = $this->input->get('office_id', true);
        $search_query  = $this->input->get('search', true);

        $data['office_location'] = config_item('office_location') ?: 'HC Interior Head Office, Noida';
        $data['office_lat']      = config_item('office_lat') ?: '28.535517';
        $data['office_long']     = config_item('office_long') ?: '77.391029';
        $data['allowed_radius']  = config_item('allowed_radius') ?: '250';
        $data['allow_geo_clock_in'] = config_item('allow_geo_clock_in') ?: 'TRUE';

        $this->ensure_biometric_tables_and_columns();

        // Fetch counts for summary cards
        $data['total_employees'] = $this->db->where('role_id !=', 2)->count_all_results('tbl_users');

        $has_bio_table = $this->db->table_exists('tbl_user_biometrics');
        $data['pending_count']   = $has_bio_table ? $this->db->where('is_approved', 0)->where('face_descriptor IS NOT NULL', null, false)->count_all_results('tbl_user_biometrics') : 0;
        $data['approved_count']  = $has_bio_table ? $this->db->where('is_approved', 1)->count_all_results('tbl_user_biometrics') : 0;
        $data['rejected_count']  = $has_bio_table ? $this->db->where('is_approved', 3)->count_all_results('tbl_user_biometrics') : 0;
        $data['reset_count']     = $has_bio_table ? $this->db->where('is_approved', 2)->count_all_results('tbl_user_biometrics') : 0;

        $has_manual_punch_col = $this->db->field_exists('allow_manual_punch', 'tbl_users');
        $data['outside_allowed_count'] = $has_manual_punch_col ? $this->db->where('role_id !=', 2)->where('allow_manual_punch', 1)->count_all_results('tbl_users') : 0;

        $manual_col_select = $has_manual_punch_col ? 'u.allow_manual_punch,' : '0 as allow_manual_punch,';

        // Build list query joining tbl_users, tbl_account_details, tbl_departments, tbl_designations, tbl_user_biometrics, tbl_office_locations
        $this->db->select('u.user_id, u.username, u.email, u.role_id, u.activated, u.office_location_id, ' . $manual_col_select . '
                           ad.fullname, ad.employment_id, ad.avatar,
                           off.location_name as office_name, off.allowed_radius as office_radius,
                           dept.departments_id, dept.deptname, desig.designations,
                           bio.biometric_id, bio.face_descriptor, bio.face_image_path, bio.face_image_base64,
                           bio.is_approved, bio.approved_by, bio.approved_at, bio.enrolled_at, bio.rejection_reason,
                           approver.fullname as approver_name');
        $this->db->from('tbl_users u');
        $this->db->join('tbl_account_details ad', 'ad.user_id = u.user_id', 'left');
        $this->db->join('tbl_office_locations off', 'off.id = u.office_location_id', 'left');
        $this->db->join('tbl_designations desig', 'desig.designations_id = ad.designations_id', 'left');
        $this->db->join('tbl_departments dept', 'dept.departments_id = desig.departments_id', 'left');
        $this->db->join('tbl_user_biometrics bio', 'bio.user_id = u.user_id', 'left');
        $this->db->join('tbl_account_details approver', 'approver.user_id = bio.approved_by', 'left');
        $this->db->where('u.role_id !=', 2); // Exclude client logins

        if (!empty($dept_filter)) {
            $this->db->where('dept.departments_id', $dept_filter);
        }

        if (!empty($office_filter)) {
            if ($office_filter === 'none') {
                $this->db->group_start();
                $this->db->where('u.office_location_id IS NULL', null, false);
                $this->db->or_where('u.office_location_id', 0);
                $this->db->group_end();
            } else {
                $this->db->where('u.office_location_id', intval($office_filter));
            }
        }

        if ($status_filter === 'pending') {
            $this->db->where('bio.is_approved', 0);
            $this->db->where('bio.face_descriptor IS NOT NULL', null, false);
        } elseif ($status_filter === 'approved') {
            $this->db->where('bio.is_approved', 1);
        } elseif ($status_filter === 'rejected') {
            $this->db->where('bio.is_approved', 3);
        } elseif ($status_filter === 'reset') {
            $this->db->where('bio.is_approved', 2);
        } elseif ($status_filter === 'not_enrolled') {
            $this->db->group_start();
            $this->db->where('bio.biometric_id IS NULL', null, false);
            $this->db->or_where('bio.face_descriptor IS NULL', null, false);
            $this->db->group_end();
        }

        if (!empty($search_query)) {
            $this->db->group_start();
            $this->db->like('ad.fullname', $search_query);
            $this->db->or_like('ad.employment_id', $search_query);
            $this->db->or_like('u.email', $search_query);
            $this->db->or_like('u.username', $search_query);
            $this->db->group_end();
        }

        // Pending requests first, then rejected, then approved, then others
        $this->db->order_by('CASE 
            WHEN bio.is_approved = 0 AND bio.face_descriptor IS NOT NULL THEN 1 
            WHEN bio.is_approved = 3 THEN 2 
            WHEN bio.is_approved = 1 THEN 3 
            ELSE 4 END', 'ASC', false);
        $this->db->order_by('bio.enrolled_at', 'DESC');
        $this->db->order_by('ad.fullname', 'ASC');

        $data['face_requests']   = $this->db->get()->result();
        $data['all_departments'] = $this->db->get('tbl_departments')->result();
        $data['all_offices']     = $this->db->order_by('is_default DESC, location_name ASC')->get('tbl_office_locations')->result();
        $data['status_filter']   = $status_filter;
        $data['dept_filter']     = $dept_filter;
        $data['office_filter']   = $office_filter;
        $data['search_query']    = $search_query;

        $data['subview'] = $this->load->view('admin/attendance/face_id_requests', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function approve_face_id($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = $this->input->post('user_id', true);
        }
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', true);
        }
        $admin_id = $this->session->userdata('user_id');
        $this->db->where('user_id', $user_id)->update('tbl_user_biometrics', [
            'is_approved'      => 1,
            'approved_by'      => $admin_id,
            'approved_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => null
        ]);
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'status'  => 'success', 
                'message' => 'Face ID approved successfully. Attendance punch unlocked for employee.',
                'user_id' => $user_id
            ]);
            exit();
        }
        set_message('success', 'Face ID approved successfully.');
        $ref = $this->input->get_post('redirect_to', true) ?: 'admin/attendance/face_id_requests';
        redirect($ref);
    }

    public function reject_face_id($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = $this->input->post('user_id', true);
        }
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', true);
        }
        $reason = $this->input->get_post('rejection_reason', true) ?: 'Face verification rejected by Admin';
        $admin_id = $this->session->userdata('user_id');
        $this->db->where('user_id', $user_id)->update('tbl_user_biometrics', [
            'is_approved'      => 3, // 3 = Rejected
            'approved_by'      => $admin_id,
            'approved_at'      => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'status'  => 'success', 
                'message' => 'Face ID request rejected. Reason: ' . $reason,
                'user_id' => $user_id
            ]);
            exit();
        }
        set_message('warning', 'Face ID rejected: ' . $reason);
        $ref = $this->input->get_post('redirect_to', true) ?: 'admin/attendance/face_id_requests';
        redirect($ref);
    }

    public function reset_face_id($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = $this->input->post('user_id', true);
        }
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', true);
        }
        $admin_id = $this->session->userdata('user_id');
        $this->db->where('user_id', $user_id)->update('tbl_user_biometrics', [
            'is_approved'       => 2, // 2 = Reset
            'face_descriptor'   => null,
            'face_image_path'   => null,
            'face_image_base64' => null,
            'rejection_reason'  => null,
            'approved_by'       => $admin_id,
            'approved_at'       => date('Y-m-d H:i:s')
        ]);
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'status'  => 'success', 
                'message' => 'Face ID has been reset. The user can now re-enroll on mobile app.',
                'user_id' => $user_id
            ]);
            exit();
        }
        set_message('success', 'Face ID reset successfully. Employee can now re-enroll on app.');
        $ref = $this->input->get_post('redirect_to', true) ?: 'admin/attendance/face_id_requests';
        redirect($ref);
    }

    public function bulk_face_id_action()
    {
        $user_ids = $this->input->post('user_ids', true);
        $action   = $this->input->post('bulk_action', true);
        $reason   = $this->input->post('rejection_reason', true) ?: 'Face registration rejected by Admin';

        if (empty($user_ids) || !is_array($user_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Please select at least one employee from the list.']);
            exit();
        }

        $admin_id = $this->session->userdata('user_id');
        $count = count($user_ids);

        if ($action === 'approve') {
            $this->db->where_in('user_id', $user_ids)->update('tbl_user_biometrics', [
                'is_approved'      => 1,
                'approved_by'      => $admin_id,
                'approved_at'      => date('Y-m-d H:i:s'),
                'rejection_reason' => null
            ]);
            $msg = "Successfully APPROVED Face ID for {$count} employee(s).";
        } elseif ($action === 'reject') {
            $this->db->where_in('user_id', $user_ids)->update('tbl_user_biometrics', [
                'is_approved'      => 3,
                'approved_by'      => $admin_id,
                'approved_at'      => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ]);
            $msg = "Successfully REJECTED Face ID for {$count} employee(s).";
        } elseif ($action === 'reset') {
            $this->db->where_in('user_id', $user_ids)->update('tbl_user_biometrics', [
                'is_approved'       => 2,
                'face_descriptor'   => null,
                'face_image_path'   => null,
                'face_image_base64' => null,
                'rejection_reason'  => null,
                'approved_by'       => $admin_id,
                'approved_at'       => date('Y-m-d H:i:s')
            ]);
            $msg = "Successfully RESET Face ID for {$count} employee(s). They can now re-enroll on mobile app.";
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid bulk action selected.']);
            exit();
        }

        echo json_encode([
            'status'  => 'success', 
            'message' => $msg, 
            'count'   => $count, 
            'action'  => $action
        ]);
        exit();
    }

    /**
     * Office Locations & Geofence Section
     */
    public function office_locations()
    {
        $this->lang->load('utilities');
        $this->lang->load('main');
        $data['title'] = 'Office Locations & Geofence Management';

        $this->ensure_biometric_tables_and_columns();

        $data['all_offices'] = $this->db->order_by('is_default DESC, id ASC')->get('tbl_office_locations')->result();

        // Count users assigned per office
        $user_counts = [];
        $uc_res = $this->db->select('office_location_id, COUNT(*) as cnt')->group_by('office_location_id')->get('tbl_users')->result();
        foreach ($uc_res as $uc) {
            $user_counts[$uc->office_location_id ?: 0] = $uc->cnt;
        }
        $data['user_counts'] = $user_counts;

        $data['subview'] = $this->load->view('admin/attendance/office_locations', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Save / Update an Office Location & Geofence Coordinates
     */
    public function save_office_location($id = null)
    {
        $this->ensure_biometric_tables_and_columns();

        if (empty($id)) {
            $id = $this->input->post('id', true);
        }

        $location_name = trim($this->input->post('location_name', true) ?: $this->input->post('office_location', true));
        $latitude = trim($this->input->post('latitude', true) ?: $this->input->post('office_lat', true));
        $longitude = trim($this->input->post('longitude', true) ?: $this->input->post('office_long', true));
        $allowed_radius = intval($this->input->post('allowed_radius', true) ?: 250);
        $address = $this->input->post('address', true);
        $is_default = $this->input->post('is_default', true) ? 1 : 0;
        $status = $this->input->post('status', true) !== null ? intval($this->input->post('status', true)) : 1;

        if (empty($location_name) || empty($latitude) || empty($longitude)) {
            set_message('error', 'Office Name, Latitude and Longitude are required.');
            redirect('admin/attendance/office_locations');
            return;
        }

        // If this is set as default, reset others
        if ($is_default == 1) {
            $this->db->update('tbl_office_locations', ['is_default' => 0]);
            // Also update tbl_config for backward compatibility
            $configs = [
                'office_location' => $location_name,
                'office_lat'      => $latitude,
                'office_long'     => $longitude,
                'allowed_radius'  => $allowed_radius,
            ];
            foreach ($configs as $k => $v) {
                $existing = $this->db->where('config_key', $k)->get('tbl_config')->row();
                if (!empty($existing)) {
                    $this->db->where('config_key', $k)->update('tbl_config', ['value' => $v]);
                } else {
                    $this->db->insert('tbl_config', ['config_key' => $k, 'value' => $v]);
                }
            }
        }

        $save_data = [
            'location_name'  => $location_name,
            'address'        => $address,
            'latitude'       => $latitude,
            'longitude'      => $longitude,
            'allowed_radius' => $allowed_radius,
            'status'         => $status,
            'is_default'     => $is_default,
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        if (!empty($id)) {
            $this->db->where('id', $id)->update('tbl_office_locations', $save_data);
            set_message('success', 'Office location updated successfully: ' . $location_name);
        } else {
            $save_data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_office_locations', $save_data);
            set_message('success', 'New office location added successfully: ' . $location_name);
        }

        $ref = $this->input->post('redirect_to', true) ?: 'admin/attendance/office_locations';
        redirect($ref);
    }

    /**
     * Set an Office Location as the Default Fallback
     */
    public function set_default_office($id)
    {
        $office = $this->db->where('id', $id)->get('tbl_office_locations')->row();
        if (!empty($office)) {
            $this->db->update('tbl_office_locations', ['is_default' => 0]);
            $this->db->where('id', $id)->update('tbl_office_locations', ['is_default' => 1]);

            // Sync with tbl_config
            $this->db->where('config_key', 'office_location')->update('tbl_config', ['value' => $office->location_name]);
            $this->db->where('config_key', 'office_lat')->update('tbl_config', ['value' => $office->latitude]);
            $this->db->where('config_key', 'office_long')->update('tbl_config', ['value' => $office->longitude]);
            $this->db->where('config_key', 'allowed_radius')->update('tbl_config', ['value' => $office->allowed_radius]);

            set_message('success', 'Default office set to: ' . $office->location_name);
        }
        redirect('admin/attendance/office_locations');
    }

    /**
     * Delete an Office Location
     */
    public function delete_office_location($id)
    {
        $office = $this->db->where('id', $id)->get('tbl_office_locations')->row();
        if (empty($office)) {
            set_message('error', 'Office location not found');
            redirect('admin/attendance/office_locations');
            return;
        }

        if ($office->is_default == 1) {
            set_message('error', 'Cannot delete the default primary office. Please set another office as default first.');
            redirect('admin/attendance/office_locations');
            return;
        }

        // Reassign users to default office
        $def = $this->db->where('is_default', 1)->get('tbl_office_locations')->row();
        $fallback_id = !empty($def) ? $def->id : null;

        $this->db->where('office_location_id', $id)->update('tbl_users', ['office_location_id' => $fallback_id]);
        $this->db->where('office_location_id', $id)->update('tbl_account_details', ['office_location_id' => $fallback_id]);

        $this->db->where('id', $id)->delete('tbl_office_locations');
        set_message('success', 'Office location removed successfully');
        redirect('admin/attendance/office_locations');
    }

    /**
     * Toggle Active / Inactive Status for an Office Location
     */
    public function toggle_office_status($id)
    {
        $office = $this->db->where('id', $id)->get('tbl_office_locations')->row();
        if (!empty($office)) {
            $new_status = ($office->status == 1) ? 0 : 1;
            $this->db->where('id', $id)->update('tbl_office_locations', ['status' => $new_status]);
            set_message('success', 'Office status updated');
        }
        redirect('admin/attendance/office_locations');
    }

    /**
     * Assign / Link an Employee to an Office Location via AJAX
     */
    public function update_user_office()
    {
        header('Content-Type: application/json');
        $user_id = $this->input->post('user_id', true);
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', true);
        }
        $office_id = $this->input->post('office_location_id', true);
        if ($office_id === null) {
            $office_id = $this->input->get('office_location_id', true);
        }

        if (empty($user_id)) {
            echo json_encode(['status' => false, 'message' => 'User ID is required']);
            exit();
        }

        $office_id = (!empty($office_id) && $office_id !== 'none') ? intval($office_id) : null;

        $this->db->where('user_id', $user_id)->update('tbl_users', [
            'office_location_id' => $office_id
        ]);
        if ($this->db->field_exists('office_location_id', 'tbl_account_details')) {
            $this->db->where('user_id', $user_id)->update('tbl_account_details', [
                'office_location_id' => $office_id
            ]);
        }

        $office_name = 'All / Default Office';
        if (!empty($office_id)) {
            $off = $this->db->where('id', $office_id)->get('tbl_office_locations')->row();
            if (!empty($off)) {
                $office_name = $off->location_name;
            }
        }

        $user_name = get_any_field('tbl_account_details', ['user_id' => $user_id], 'fullname') ?: 'User #' . $user_id;

        echo json_encode([
            'status'             => true,
            'user_id'            => (int)$user_id,
            'office_location_id' => $office_id,
            'office_name'        => $office_name,
            'message'            => "Office updated for {$user_name}: {$office_name}"
        ]);
        exit();
    }

    /**
     * Toggle Allow Outside / Field Punch permission for an employee
     */
    public function toggle_manual_punch($user_id = null)
    {
        header('Content-Type: application/json');
        if (empty($user_id)) {
            $user_id = $this->input->post('user_id', true);
        }
        if (empty($user_id)) {
            $user_id = $this->input->get('user_id', true);
        }
        if (empty($user_id)) {
            echo json_encode(['status' => false, 'message' => 'User ID is required']);
            exit();
        }

        $this->ensure_biometric_tables_and_columns();

        $user = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        if (empty($user)) {
            echo json_encode(['status' => false, 'message' => 'User not found']);
            exit();
        }

        $new_status = empty($user->allow_manual_punch) ? 1 : 0;
        $this->db->where('user_id', $user_id)->update('tbl_users', [
            'allow_manual_punch' => $new_status
        ]);

        $name = get_any_field('tbl_account_details', ['user_id' => $user_id], 'fullname') ?: $user->username;
        $status_label = ($new_status === 1) ? 'Allowed (Outside / Field Punch enabled)' : 'Strict Office Geofence Only';

        echo json_encode([
            'status'             => true,
            'allow_manual_punch' => $new_status,
            'user_id'            => (int)$user_id,
            'label'              => $status_label,
            'message'            => "Permission updated for {$name}: {$status_label}"
        ]);
        exit();
    }

    /**
     * Self-healing helper: Ensures all required biometric tables and columns exist in DB
     */
    private function ensure_biometric_tables_and_columns()
    {
        // 1. Ensure allow_manual_punch in tbl_users
        if (!$this->db->field_exists('allow_manual_punch', 'tbl_users')) {
            @$this->db->query("ALTER TABLE `tbl_users` ADD COLUMN `allow_manual_punch` TINYINT(1) NOT NULL DEFAULT 0 AFTER `activated`");
        }

        // 2. Ensure tbl_user_biometrics table
        @$this->db->query("CREATE TABLE IF NOT EXISTS `tbl_user_biometrics` (
            `biometric_id` int NOT NULL AUTO_INCREMENT,
            `user_id` int NOT NULL,
            `face_descriptor` LONGTEXT NULL,
            `face_image_path` VARCHAR(255) NULL,
            `face_image_base64` LONGTEXT NULL,
            `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
            `approved_by` INT NULL,
            `approved_at` DATETIME NULL,
            `rejection_reason` VARCHAR(255) NULL,
            `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`biometric_id`),
            UNIQUE KEY `idx_user_bio` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Ensure approval columns in tbl_user_biometrics if table already existed without them
        if ($this->db->table_exists('tbl_user_biometrics')) {
            if (!$this->db->field_exists('is_approved', 'tbl_user_biometrics')) {
                @$this->db->query("ALTER TABLE `tbl_user_biometrics` ADD COLUMN `is_approved` TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!$this->db->field_exists('approved_by', 'tbl_user_biometrics')) {
                @$this->db->query("ALTER TABLE `tbl_user_biometrics` ADD COLUMN `approved_by` INT NULL");
            }
            if (!$this->db->field_exists('approved_at', 'tbl_user_biometrics')) {
                @$this->db->query("ALTER TABLE `tbl_user_biometrics` ADD COLUMN `approved_at` DATETIME NULL");
            }
            if (!$this->db->field_exists('rejection_reason', 'tbl_user_biometrics')) {
                @$this->db->query("ALTER TABLE `tbl_user_biometrics` ADD COLUMN `rejection_reason` VARCHAR(255) NULL");
            }
        }

        // 3. Ensure tbl_attendance_face_logs table
        @$this->db->query("CREATE TABLE IF NOT EXISTS `tbl_attendance_face_logs` (
            `log_id` INT NOT NULL AUTO_INCREMENT,
            `attendance_id` INT NULL,
            `user_id` INT NOT NULL,
            `punch_type` ENUM('in', 'out') NOT NULL,
            `punch_time` DATETIME NOT NULL,
            `face_score` DECIMAL(5,2) NULL,
            `lat` DECIMAL(10,8) NULL,
            `lng` DECIMAL(11,8) NULL,
            `address` TEXT NULL,
            `photo_thumbnail` LONGTEXT NULL,
            `distance_from_office` DECIMAL(8,2) NULL,
            `is_inside_geofence` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`log_id`),
            KEY `idx_user_punch` (`user_id`, `punch_time`),
            KEY `idx_punch_type_geofence` (`punch_type`, `is_inside_geofence`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
}


