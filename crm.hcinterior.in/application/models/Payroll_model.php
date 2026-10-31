<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of payroll_model
 *
 * @author NaYeM
 */
class Payroll_Model extends MY_Model
{

    public $_table_name;
    public $_order_by;
    public $_primary_key;

    public function get_department_by_id($departments_id)
    {
        $this->db->select('tbl_departments.deptname', FALSE);
        $this->db->select('tbl_designations.*', FALSE);
        $this->db->from('tbl_departments');
        $this->db->join('tbl_designations', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left');
        $this->db->where('tbl_departments.departments_id', $departments_id);
        $query_result = $this->db->get();
        $result = $query_result->result();

        return $result;
    }

    public function get_emp_info_by_id($designation_id)
    {
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->select('tbl_designations.designations', FALSE);
        $this->db->from('tbl_account_details');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id  = tbl_account_details.designations_id', 'left');
        $this->db->where('tbl_designations.designations_id', $designation_id);
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    public function get_emp_salary_list($id = NULL, $designation_id = NULL)
    {
        $this->db->select('tbl_employee_payroll.*', FALSE);
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->select('tbl_salary_template.*', FALSE);
        $this->db->select('tbl_hourly_rate.*', FALSE);
        $this->db->select('tbl_designations.*', FALSE);
        $this->db->select('tbl_departments.deptname', FALSE);
        $this->db->select('tbl_account_details.user_id as user_id', FALSE);
        $this->db->from('tbl_account_details');
        $this->db->join('tbl_users', 'tbl_users.user_id = tbl_account_details.user_id', 'inner');
        $this->db->join('tbl_employee_payroll', 'tbl_employee_payroll.user_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_salary_template', 'tbl_employee_payroll.salary_template_id = tbl_salary_template.salary_template_id', 'left');
        $this->db->join('tbl_hourly_rate', 'tbl_employee_payroll.hourly_rate_id = tbl_hourly_rate.hourly_rate_id', 'left');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id  = tbl_account_details.designations_id', 'left');
        $this->db->join('tbl_departments', 'tbl_departments.departments_id  = tbl_designations.departments_id', 'left');
        $this->db->where('tbl_users.activated', 1);
        $this->db->where('tbl_users.role_id !=', 2);

        if (!empty($designation_id)) {
            $this->db->where('tbl_designations.designations_id', $designation_id);
        }
        if (!empty($id)) {
            $this->db->where('tbl_account_details.user_id', $id);
            $query_result = $this->db->get();
            $result = $query_result->row();
        } else {
            if (!empty($_POST["length"]) && $_POST["length"] != -1) {
                $this->db->limit($_POST['length'], $_POST['start']);
            }
            $query_result = $this->db->get();
            $result = $query_result->result();
        }
        return $result;
    }

    public function get_salary_payment_info($salary_payment_id, $result = NULL, $search_type = null)
    {

        $this->db->select('tbl_salary_payment.*', FALSE);
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->select('tbl_designations.*', FALSE);
        $this->db->select('tbl_departments.deptname', FALSE);
        $this->db->from('tbl_salary_payment');
        $this->db->join('tbl_account_details', 'tbl_salary_payment.user_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id  = tbl_account_details.designations_id', 'left');
        $this->db->join('tbl_departments', 'tbl_departments.departments_id  = tbl_designations.departments_id', 'left');
        if (!empty($search_type)) {
            if ($search_type == 'employee') {
                $this->db->where("tbl_salary_payment.user_id", $salary_payment_id);
            } elseif ($search_type == 'month') {
                $this->db->where("tbl_salary_payment.payment_month", $salary_payment_id);
            } elseif ($search_type == 'period') {
                $this->db->where("tbl_salary_payment.payment_month >=", $salary_payment_id['start_month']);
                $this->db->where("tbl_salary_payment.payment_month <=", $salary_payment_id['end_month']);
            }
        } else {
            $this->db->where("tbl_salary_payment.salary_payment_id", $salary_payment_id);
        }
        if (!empty($_POST["length"]) && $_POST["length"] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query_result = $this->db->get();
        if (!empty($result)) {
            $result = $query_result->result();
        } else {
            $result = $query_result->row();
        }
        return $result;
    }

    public function get_advance_salary_info_by_date($payment_month = NULL, $id = NULL, $user_id = NULL)
    {
        
        $this->db->select('tbl_advance_salary.*', FALSE);
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->from('tbl_advance_salary');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_advance_salary.user_id', 'left');
        if ($this->session->userdata('user_type') != 1) {
            $this->db->where('tbl_advance_salary.user_id', $this->session->userdata('user_id'));
            $this->db->where('tbl_advance_salary.deduct_month', $payment_month);
            $query_result = $this->db->get();
            $result = $query_result->result();
        } elseif (!empty($id)) {
            $this->db->where('tbl_advance_salary.advance_salary_id', $id);
            $query_result = $this->db->get();
            $result = $query_result->row();
        } elseif (!empty($user_id)) {
            $this->db->where('tbl_advance_salary.status', '1');
            if (!empty($payment_month)) {
                $this->db->where('tbl_advance_salary.deduct_month', $payment_month);
            }            
            $this->db->where('tbl_account_details.user_id', $user_id);
            $query_result = $this->db->get();
            $result = $query_result->result();
        } else {
            $this->db->where('tbl_advance_salary.deduct_month', $payment_month);
            $query_result = $this->db->get();
            $result = $query_result->result();
        }
        return $result;
    }

    public function view_advance_salary($id = NULL)
    {
        $this->db->select('tbl_advance_salary.*', FALSE);
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->from('tbl_advance_salary');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_advance_salary.user_id', 'left');
        $this->db->where('tbl_advance_salary.advance_salary_id', $id);
        $query_result = $this->db->get();
        $result = $query_result->row();

        return $result;
    }

    public function my_advance_salary_info($all = null)
    {
        $this->db->select('tbl_advance_salary.*', FALSE);
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->from('tbl_advance_salary');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_advance_salary.user_id', 'left');
        if (!empty($all)) {
            $this->db->order_by('tbl_advance_salary.request_date', "DESC");
        } else {
            $this->db->where('tbl_advance_salary.user_id', $this->session->userdata('user_id'));
        }
        if (!empty($_POST["length"]) && $_POST["length"] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;

    }

    public function get_attendance_info_by_date($start_date, $end_date, $user_id)
    {
        $this->db->select('tbl_attendance.*', FALSE);
        $this->db->select('tbl_clock.*', FALSE);
        $this->db->from('tbl_attendance');
        $this->db->join('tbl_clock', 'tbl_clock.attendance_id  = tbl_attendance.attendance_id', 'left');
        $this->db->where('tbl_attendance.date_in >=', $start_date);
        $this->db->where('tbl_attendance.date_in <=', $end_date);
        $this->db->where('tbl_attendance.user_id', $user_id);
        $this->db->where('tbl_attendance.attendance_status', 1);
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    public function get_provident_fund_info_by_date($start_date, $end_date, $user_id = null)
    {
        $this->db->select('tbl_salary_payment.*', FALSE);
        $this->db->select('tbl_salary_payment_deduction.*', FALSE);
        $this->db->select('tbl_account_details.*', FALSE);
        $this->db->from('tbl_salary_payment');
        $this->db->join('tbl_salary_payment_deduction', 'tbl_salary_payment_deduction.salary_payment_id  = tbl_salary_payment.salary_payment_id', 'left');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id  = tbl_salary_payment.user_id', 'left');
        $this->db->where('tbl_salary_payment.payment_month >=', $start_date);
        $this->db->where('tbl_salary_payment.payment_month <=', $end_date);
        $this->db->where('tbl_salary_payment_deduction.salary_payment_deduction_label', lang('provident_fund'));
        if (!empty($user_id)) {
            $this->db->where('tbl_salary_payment.user_id', $user_id);
        }
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    /**
     * Compute full High Creation Salary Slip data for any employee and month.
     * Incorporates live CRM attendance (Present, Week Off, Holidays, CL, EL, SL, Absent),
     * employee bank details, joining date, and exact company formulas.
     * Overrides can be passed from CSV import or manual inputs.
     */
    public function get_high_creation_salary_data($user_id_or_code, $payment_month, $overrides = array())
    {
        if (empty($payment_month)) {
            $payment_month = date('Y-m');
        }
        $year = date('Y', strtotime($payment_month . '-01'));
        $month = date('m', strtotime($payment_month . '-01'));
        $month_name = date('F', strtotime($payment_month . '-01'));
        $start_date = date('Y-m-01', strtotime($payment_month . '-01'));
        $end_date = date('Y-m-t', strtotime($payment_month . '-01'));

        // Salary count days (configured default: 26 days in month)
        $salary_count_days_cfg = config_item('salary_count_days');
        if (!empty($overrides['salary_count_days']) && is_numeric($overrides['salary_count_days'])) {
            $salary_count_days = floatval($overrides['salary_count_days']);
        } elseif (!empty($salary_count_days_cfg) && is_numeric($salary_count_days_cfg) && floatval($salary_count_days_cfg) > 0) {
            $salary_count_days = floatval($salary_count_days_cfg);
        } else {
            $salary_count_days = 26.00;
        }

        $total_working_days = !empty($overrides['total_working_days']) ? floatval($overrides['total_working_days']) : $salary_count_days;

        // Fetch employee details
        $emp = null;
        if (is_numeric($user_id_or_code) && intval($user_id_or_code) > 0) {
            $emp = $this->db->select('tbl_account_details.*, tbl_designations.designations, tbl_departments.deptname, tbl_users.email, tbl_employee_payroll.salary_template_id, tbl_salary_template.basic_salary as template_basic, tbl_salary_template.salary_grade')
                ->from('tbl_account_details')
                ->join('tbl_users', 'tbl_users.user_id = tbl_account_details.user_id', 'left')
                ->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left')
                ->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left')
                ->join('tbl_employee_payroll', 'tbl_employee_payroll.user_id = tbl_account_details.user_id', 'left')
                ->join('tbl_salary_template', 'tbl_salary_template.salary_template_id = tbl_employee_payroll.salary_template_id', 'left')
                ->where('tbl_account_details.user_id', intval($user_id_or_code))
                ->get()->row();
        } elseif (!empty($user_id_or_code)) {
            $code = trim($user_id_or_code);
            $emp = $this->db->select('tbl_account_details.*, tbl_designations.designations, tbl_departments.deptname, tbl_users.email, tbl_employee_payroll.salary_template_id, tbl_salary_template.basic_salary as template_basic, tbl_salary_template.salary_grade')
                ->from('tbl_account_details')
                ->join('tbl_users', 'tbl_users.user_id = tbl_account_details.user_id', 'left')
                ->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left')
                ->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left')
                ->join('tbl_employee_payroll', 'tbl_employee_payroll.user_id = tbl_account_details.user_id', 'left')
                ->join('tbl_salary_template', 'tbl_salary_template.salary_template_id = tbl_employee_payroll.salary_template_id', 'left')
                ->group_start()
                    ->where('tbl_account_details.emp_code', $code)
                    ->or_where('tbl_account_details.employment_id', $code)
                ->group_end()
                ->get()->row();
        }

        $user_id = !empty($emp) ? $emp->user_id : (is_numeric($user_id_or_code) ? intval($user_id_or_code) : 0);

        // Bank info
        $bank = null;
        if (!empty($user_id)) {
            $bank = $this->db->where('user_id', $user_id)->get('tbl_employee_bank')->row();
        }

        // Basic Profile fields
        $emp_code = !empty($overrides['emp_code']) ? $overrides['emp_code'] : (!empty($emp->emp_code) ? $emp->emp_code : (!empty($emp->employment_id) ? $emp->employment_id : 'HC' . str_pad($user_id, 4, '0', STR_PAD_LEFT)));
        $emp_name = !empty($overrides['emp_name']) ? $overrides['emp_name'] : (!empty($emp->fullname) ? $emp->fullname : (!empty($overrides['name']) ? $overrides['name'] : 'Staff Member'));
        $dept_name = !empty($overrides['dept_name']) ? $overrides['dept_name'] : (!empty($emp->deptname) ? $emp->deptname : (!empty($overrides['department']) ? $overrides['department'] : 'Production'));
        $designation_name = !empty($overrides['designation_name']) ? $overrides['designation_name'] : (!empty($emp->designations) ? $emp->designations : (!empty($overrides['designation']) ? $overrides['designation'] : 'Staff'));
        
        $joining_date_raw = !empty($overrides['joining_date']) ? $overrides['joining_date'] : (!empty($emp->joining_date) ? $emp->joining_date : '');
        $joining_date_formatted = !empty($joining_date_raw) ? date('d-M-y', strtotime($joining_date_raw)) : '-';

        $bank_acc = !empty($overrides['bank_acc']) ? $overrides['bank_acc'] : (!empty($bank->account_number) ? $bank->account_number : '');
        $bank_ifsc = !empty($overrides['bank_ifsc']) ? $overrides['bank_ifsc'] : (!empty($bank->routing_number) ? $bank->routing_number : '');
        $bank_name = !empty($overrides['bank_name']) ? $overrides['bank_name'] : (!empty($bank->bank_name) ? $bank->bank_name : '');
        $pf_no = !empty($overrides['pf_no']) ? $overrides['pf_no'] : '-';

        // Salary CTC
        $salary_ctc = 0.0;
        if (isset($overrides['ctc']) && floatval($overrides['ctc']) > 0) {
            $salary_ctc = floatval($overrides['ctc']);
        } elseif (!empty($emp->salary_grade) && is_numeric($emp->salary_grade) && floatval($emp->salary_grade) > 0) {
            $salary_ctc = floatval($emp->salary_grade);
        } elseif (!empty($emp->template_basic) && floatval($emp->template_basic) > 0) {
            $allow_sum = 0.0;
            if (!empty($emp->salary_template_id)) {
                $allow_res = $this->db->select_sum('allowance_value')->where('salary_template_id', $emp->salary_template_id)->get('tbl_salary_allowance')->row();
                if (!empty($allow_res) && !empty($allow_res->allowance_value)) {
                    $allow_sum = floatval($allow_res->allowance_value);
                }
            }
            if ($allow_sum > 0) {
                $salary_ctc = floatval($emp->template_basic) + $allow_sum;
            } else {
                $salary_ctc = floatval($emp->template_basic) * 2;
            }
        }

        // Attendance stats
        if (isset($overrides['present_days']) || isset($overrides['present'])) {
            $present_days = isset($overrides['present_days']) ? floatval($overrides['present_days']) : floatval($overrides['present']);
            $week_off_days = isset($overrides['week_off_days']) ? floatval($overrides['week_off_days']) : (isset($overrides['week_off']) ? floatval($overrides['week_off']) : 0.0);
            $holiday_days = isset($overrides['holiday_days']) ? floatval($overrides['holiday_days']) : (isset($overrides['holidays']) ? floatval($overrides['holidays']) : 0.0);
            $el_avail_days = isset($overrides['el_avail_days']) ? floatval($overrides['el_avail_days']) : (isset($overrides['el_avail']) ? floatval($overrides['el_avail']) : 0.0);
            $cl_avail_days = isset($overrides['cl_avail_days']) ? floatval($overrides['cl_avail_days']) : (isset($overrides['cl_avail']) ? floatval($overrides['cl_avail']) : 0.0);
            $sl_avail_days = isset($overrides['sl_avail_days']) ? floatval($overrides['sl_avail_days']) : (isset($overrides['sl_avail']) ? floatval($overrides['sl_avail']) : 0.0);
            $absent_days = isset($overrides['absent_days']) ? floatval($overrides['absent_days']) : (isset($overrides['absent']) ? floatval($overrides['absent']) : 0.0);
            $paid_days = isset($overrides['paid_days']) ? floatval($overrides['paid_days']) : ($present_days + $week_off_days + $holiday_days + $el_avail_days + $cl_avail_days + $sl_avail_days);
        } else {
            // Live CRM Attendance Calculation
            $present_days = 0.0;
            if (!empty($user_id)) {
                $p_cnt = $this->db->where('user_id', $user_id)
                    ->where('attendance_status', 1)
                    ->where('date_in >=', $start_date)
                    ->where('date_in <=', $end_date)
                    ->count_all_results('tbl_attendance');
                $h_cnt = $this->db->where('user_id', $user_id)
                    ->group_start()
                        ->where('attendance_status', 'H')
                        ->or_where('attendance_status', 2)
                    ->group_end()
                    ->where('date_in >=', $start_date)
                    ->where('date_in <=', $end_date)
                    ->count_all_results('tbl_attendance');
                $present_days = floatval($p_cnt) + (floatval($h_cnt) * 0.5);
            }

            // Sundays in Month
            $week_off_days = 0.0;
            for ($d = 1; $d <= $total_working_days; $d++) {
                if (date('w', mktime(0, 0, 0, (int)$month, $d, (int)$year)) == 0) {
                    $week_off_days++;
                }
            }

            // Public Holidays
            $holiday_days = 0.0;
            $h_rows = $this->db->where('start_date >=', $start_date)
                ->where('start_date <=', $end_date)
                ->get('tbl_holiday')->result();
            if (!empty($h_rows)) {
                foreach ($h_rows as $hr) {
                    $holiday_days += 1.0;
                }
            }

            // Leaves (Approved)
            $cl_avail_days = 0.0;
            $el_avail_days = 0.0;
            $sl_avail_days = 0.0;
            if (!empty($user_id)) {
                $leaves = $this->db->select('tbl_leave_application.*, tbl_leave_category.leave_category')
                    ->from('tbl_leave_application')
                    ->join('tbl_leave_category', 'tbl_leave_category.leave_category_id = tbl_leave_application.leave_category_id', 'left')
                    ->where('tbl_leave_application.user_id', $user_id)
                    ->where('tbl_leave_application.application_status', 2)
                    ->where('tbl_leave_application.leave_start_date >=', $start_date)
                    ->where('tbl_leave_application.leave_start_date <=', $end_date)
                    ->get()->result();
                if (!empty($leaves)) {
                    foreach ($leaves as $lv) {
                        $c_code = strtoupper(trim($lv->leave_category));
                        $days_cnt = 1.0;
                        if (!empty($lv->leave_end_date) && $lv->leave_end_date != $lv->leave_start_date) {
                            $d1 = new DateTime($lv->leave_start_date);
                            $d2 = new DateTime($lv->leave_end_date);
                            $days_cnt = (float)($d1->diff($d2)->days + 1);
                        }
                        if ($c_code == 'CL') $cl_avail_days += $days_cnt;
                        elseif ($c_code == 'EL') $el_avail_days += $days_cnt;
                        elseif ($c_code == 'SL') $sl_avail_days += $days_cnt;
                    }
                }
            }

            $paid_days = min($total_working_days, $present_days + $week_off_days + $holiday_days + $el_avail_days + $cl_avail_days + $sl_avail_days);
            $absent_days = max(0, $total_working_days - $paid_days);
        }

        $nad_days = 0.0;

        // Salary Component Formulas
        $basic_salary = round($salary_ctc * 0.50, 2);
        $variable_amount = round($salary_ctc * 0.12, 2);
        $hra_amount = round($basic_salary * 0.40, 2);
        $conveyance_amount = 1000.00;
        $medical_amount = 1250.00;
        $spa_amount = max(0, round($salary_ctc - ($basic_salary + $variable_amount + $hra_amount + $conveyance_amount + $medical_amount), 2));
        $leave_encashment = isset($overrides['leave_encashment']) ? floatval($overrides['leave_encashment']) : 0.00;
        $total_earnings = round($basic_salary + $variable_amount + $hra_amount + $conveyance_amount + $medical_amount + $spa_amount + $leave_encashment, 2);

        // Deductions
        $salary_per_day = ($salary_count_days > 0) ? round($salary_ctc / $salary_count_days, 2) : 0;
        $absent_deduction = round($absent_days * $salary_per_day, 2);

        $epf_employee = isset($overrides['epf_employee']) ? floatval($overrides['epf_employee']) : 0.00;
        if ($epf_employee == 0.0 && !empty($emp->salary_template_id)) {
            $epf_row = $this->db->like('deduction_label', 'EPF')->where('salary_template_id', $emp->salary_template_id)->get('tbl_salary_deduction')->row();
            if (!empty($epf_row)) {
                $epf_employee = floatval($epf_row->deduction_value);
            }
        }
        $epf_employer = 0.00;
        $vpf_amount = isset($overrides['vpf_amount']) ? floatval($overrides['vpf_amount']) : 0.00;
        $tds_amount = isset($overrides['tds_amount']) ? floatval($overrides['tds_amount']) : (isset($overrides['tds']) ? floatval($overrides['tds']) : 0.00);
        $proftax_amount = isset($overrides['proftax_amount']) ? floatval($overrides['proftax_amount']) : 0.00;
        $loan_dedu_amount = isset($overrides['loan_dedu_amount']) ? floatval($overrides['loan_dedu_amount']) : 0.00;

        // Advance
        $advance_amount = 0.0;
        if (isset($overrides['advance_amount'])) {
            $advance_amount = floatval($overrides['advance_amount']);
        } elseif (isset($overrides['advance'])) {
            $advance_amount = floatval($overrides['advance']);
        } elseif (!empty($user_id)) {
            $adv = $this->db->where('user_id', $user_id)
                ->where('deduct_month', $payment_month)
                ->where('status', 1)
                ->get('tbl_advance_salary')->row();
            if (!empty($adv)) {
                $advance_amount = floatval($adv->advance_amount);
            }
        }

        $total_deductions = round($absent_deduction + $epf_employee + $vpf_amount + $tds_amount + $proftax_amount + $loan_dedu_amount + $advance_amount, 2);
        $gross_salary = round($salary_ctc - $absent_deduction, 2);

        // Incentive
        $incentive_amt = isset($overrides['incentive_amt']) ? floatval($overrides['incentive_amt']) : 0.00;
        $incentive_dedu = isset($overrides['incentive_dedu']) ? floatval($overrides['incentive_dedu']) : 0.00;
        $net_incentive = round($incentive_amt - $incentive_dedu, 2);

        $net_payable = round($salary_ctc - $total_deductions + $net_incentive, 2);

        // Base64 Logo
        $logo_file = ROOTPATH . '/uploads/HC-logo-3-withBg.jpg';
        if (!file_exists($logo_file)) {
            $logo_file = ROOTPATH . '/' . config_item('company_logo');
        }
        $logo_base64 = '';
        if (file_exists($logo_file)) {
            $ext = pathinfo($logo_file, PATHINFO_EXTENSION);
            $mime = ($ext == 'png') ? 'image/png' : 'image/jpeg';
            $logo_base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logo_file));
        }

        return array(
            'user_id'                 => $user_id,
            'payment_month'           => $payment_month,
            'month_name'              => $month_name,
            'year'                    => $year,
            'emp_code'                => $emp_code,
            'emp_name'                => $emp_name,
            'dept_name'               => $dept_name,
            'designation_name'        => $designation_name,
            'joining_date_formatted'  => $joining_date_formatted,
            'bank_acc'                => $bank_acc,
            'bank_ifsc'               => $bank_ifsc,
            'bank_name'               => $bank_name,
            'pf_no'                   => $pf_no,
            'salary_count_days'       => $salary_count_days,
            'total_working_days'      => $total_working_days,
            'paid_days'               => $paid_days,
            'salary_ctc'              => $salary_ctc,
            'present_days'            => $present_days,
            'week_off_days'           => $week_off_days,
            'holiday_days'            => $holiday_days,
            'el_avail_days'           => $el_avail_days,
            'cl_avail_days'           => $cl_avail_days,
            'sl_avail_days'           => $sl_avail_days,
            'absent_days'             => $absent_days,
            'nad_days'                => $nad_days,
            'basic_salary'            => $basic_salary,
            'variable_amount'         => $variable_amount,
            'hra_amount'              => $hra_amount,
            'conveyance_amount'       => $conveyance_amount,
            'medical_amount'          => $medical_amount,
            'spa_amount'              => $spa_amount,
            'leave_encashment'        => $leave_encashment,
            'total_earnings'          => $total_earnings,
            'salary_per_day'          => $salary_per_day,
            'absent_deduction'        => $absent_deduction,
            'epf_employee'            => $epf_employee,
            'epf_employer'            => $epf_employer,
            'vpf_amount'              => $vpf_amount,
            'tds_amount'              => $tds_amount,
            'proftax_amount'          => $proftax_amount,
            'loan_dedu_amount'        => $loan_dedu_amount,
            'advance_amount'          => $advance_amount,
            'total_deductions'        => $total_deductions,
            'gross_salary'            => $gross_salary,
            'incentive_amt'           => $incentive_amt,
            'incentive_dedu'          => $incentive_dedu,
            'net_incentive'           => $net_incentive,
            'net_payable'             => $net_payable,
            'logo_base64'             => $logo_base64
        );
    }

    /**
     * Parse uploaded CSV/Excel file or string into clean salary records
     */
    public function parse_salary_csv_data($csv_raw, $payment_month)
    {
        $lines = preg_split("/\r\n|\n|\r/", trim($csv_raw));
        if (empty($lines)) return array();

        $rows = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $row = str_getcsv($line);
            if (count(array_filter($row)) === 0) continue;
            $rows[] = $row;
        }
        if (empty($rows)) return array();

        $header_idx = -1;
        foreach ($rows as $i => $r) {
            $r_str = strtolower(implode(' ', $r));
            if (strpos($r_str, 'employee') !== false || strpos($r_str, 'emp code') !== false || strpos($r_str, 'ctc') !== false) {
                $header_idx = $i;
                break;
            }
        }

        $results = array();

        if ($header_idx >= 0) {
            $raw_headers = $rows[$header_idx];
            $norm_headers = array();
            foreach ($raw_headers as $col_i => $h_val) {
                $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $h_val)));
                $norm_headers[$col_i] = $clean;
            }

            for ($i = $header_idx + 1; $i < count($rows); $i++) {
                $r = $rows[$i];
                if (count(array_filter($r)) === 0) continue;

                $assoc = array();
                foreach ($r as $col_i => $val) {
                    if (isset($norm_headers[$col_i])) {
                        $assoc[$norm_headers[$col_i]] = trim($val);
                    }
                }

                $emp_code = '';
                foreach (array('employeeid', 'empcode', 'empid', 'id') as $k) {
                    if (!empty($assoc[$k])) { $emp_code = $assoc[$k]; break; }
                }

                $name = '';
                foreach (array('employeename', 'name', 'fullname') as $k) {
                    if (!empty($assoc[$k])) { $name = $assoc[$k]; break; }
                }

                $ctc = 0.0;
                foreach (array('ctcapr26', 'ctc', 'salaryctc', 'grossctc') as $k) {
                    if (isset($assoc[$k])) {
                        $clean_num = str_replace(array(',', ' ', '"'), '', $assoc[$k]);
                        if (is_numeric($clean_num) && floatval($clean_num) > 0) {
                            $ctc = floatval($clean_num);
                            break;
                        }
                    }
                }

                if (empty($emp_code) && empty($name) && $ctc <= 0) {
                    continue;
                }

                $overrides = array(
                    'emp_code'         => $emp_code,
                    'emp_name'         => $name,
                    'dept_name'        => !empty($assoc['department']) ? $assoc['department'] : '',
                    'designation_name' => !empty($assoc['designation']) ? $assoc['designation'] : '',
                    'joining_date'     => !empty($assoc['doj']) ? $assoc['doj'] : (!empty($assoc['joiningdate']) ? $assoc['joiningdate'] : ''),
                    'ctc'              => $ctc,
                    'absent'           => isset($assoc['absenta']) && is_numeric(str_replace(',', '', $assoc['absenta'])) ? floatval(str_replace(',', '', $assoc['absenta'])) : (isset($assoc['absent']) && is_numeric($assoc['absent']) ? floatval($assoc['absent']) : null),
                    'cl_avail'         => isset($assoc['casualleavecl']) && is_numeric(str_replace(',', '', $assoc['casualleavecl'])) ? floatval(str_replace(',', '', $assoc['casualleavecl'])) : 0.0,
                    'el_avail'         => isset($assoc['annualearnleaveel']) && is_numeric(str_replace(',', '', $assoc['annualearnleaveel'])) ? floatval(str_replace(',', '', $assoc['annualearnleaveel'])) : 0.0,
                    'week_off'         => isset($assoc['weakoffwo']) && is_numeric(str_replace(',', '', $assoc['weakoffwo'])) ? floatval(str_replace(',', '', $assoc['weakoffwo'])) : null,
                    'advance'          => isset($assoc['advancesalary']) && is_numeric(str_replace(',', '', $assoc['advancesalary'])) ? floatval(str_replace(',', '', $assoc['advancesalary'])) : 0.0,
                    'tds'              => isset($assoc['tdsonsalary']) && is_numeric(str_replace(',', '', $assoc['tdsonsalary'])) ? floatval(str_replace(',', '', $assoc['tdsonsalary'])) : 0.0,
                );

                $calc_slip = $this->get_high_creation_salary_data($emp_code, $payment_month, $overrides);
                $results[] = $calc_slip;
            }
        }

        return $results;
    }

}

