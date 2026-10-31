<?php echo message_box('success'); ?>
<?php echo message_box('error');
$edited = can_action('92', 'edited');
$deleted = can_action('92', 'deleted');
?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <!-- *********     Employee Search Panel ***************** -->
            <div class="panel-heading">
                <div class="panel-title">
                    <strong><?= lang('make_payment') ?></strong>
                </div>
            </div>
            <form id="form" role="form" enctype="multipart/form-data"
                  action="<?php echo base_url() ?>admin/payroll/make_payment" method="post"
                  class="form-horizontal form-groups-bordered">
                <input type="hidden" name="flag" value="1">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label"><?= lang('select_department') ?> <span
                                    class="required"> *</span></label>
                        
                        <div class="col-sm-5">
                            <select required name="departments_id" class="form-control select_box">
                                <option value=""><?= lang('select_department') ?></option>
                                <option value="all" <?= (!empty($departments_id) && $departments_id == 'all') ? 'selected' : ''; ?>>
                                    &#9733; Select All Teams (All Departments)
                                </option>
                                <?php if (!empty($all_department_info)) : foreach ($all_department_info as $v_department_info) :
                                    if (!empty($v_department_info->deptname)) {
                                        $deptname = $v_department_info->deptname;
                                    } else {
                                        $deptname = lang('undefined_department');
                                    }
                                    ?>
                                    <option value="<?php echo $v_department_info->departments_id; ?>" <?php
                                    if (!empty($departments_id)) {
                                        echo $v_department_info->departments_id == $departments_id ? 'selected' : '';
                                    }
                                    ?>>
                                        <?php echo $deptname ?></option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('month') ?> <span
                                    class="required"> *</span></label>
                        <div class="col-sm-5">
                            <div class="input-group">
                                <input required type="text" value="<?php
                                if (!empty($payment_month)) {
                                    echo $payment_month;
                                }
                                ?>" class="form-control monthyear"
                                       name="payment_month" data-format="yyyy/mm/dd">
                                
                                <div class="input-group-addon">
                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="border-none">
                        <label for="field-1" class="col-sm-3 control-label"></label>
                        <div class="col-sm-5">
                            <button id="submit" type="submit" name="flag" value="1"
                                    class="btn btn-primary btn-block"><?= lang('go') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div><!-- ******************** Employee Search Panel Ends ******************** -->

        <?php if (!empty($flag)) : ?>
            
            <div class="panel panel-custom">
                <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div class="panel-title" style="margin: 0;">
                        <span>
                            <strong><?= lang('payment_info_for') ?><?php
                                if (!empty($payment_month)) {
                                    echo ' <span class="text-danger">' . date('F Y', strtotime($payment_month)) . '</span>';
                                }
                                if (!empty($departments_id)) {
                                    if ($departments_id == 'all') {
                                        echo ' <span class="label label-primary" style="font-size: 11px; margin-left: 8px; vertical-align: middle;">All Teams</span>';
                                    } else {
                                        foreach ($all_department_info as $dep) {
                                            if ($dep->departments_id == $departments_id) {
                                                echo ' <span class="label label-info" style="font-size: 11px; margin-left: 8px; vertical-align: middle;">' . html_escape($dep->deptname) . '</span>';
                                                break;
                                            }
                                        }
                                    }
                                }
                                ?></strong>
                        </span>
                    </div>

                    <!-- Bulk Action Toolbar -->
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="badge" id="selected_count_badge" style="background: #3b82f6; font-size: 12px; padding: 5px 10px; font-weight: 600;">0 Selected</span>
                        <button type="button" class="btn btn-sm btn-success" id="btn_bulk_paid" style="font-weight: 600;">
                            <i class="fa fa-check-circle"></i> Mark Paid
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" id="btn_bulk_hold" style="font-weight: 600;">
                            <i class="fa fa-pause-circle"></i> Mark Hold
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="btn_bulk_unpaid" style="font-weight: 600;">
                            <i class="fa fa-times-circle"></i> Mark Unpaid
                        </button>
                    </div>
                </div>

                <div class="panel-body">
                    <!-- Table -->
                    <table class="table table-striped table-hover" id="datatable_action" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">
                                <input type="checkbox" id="select_all_employees" style="cursor: pointer; transform: scale(1.2);" title="Select All">
                            </th>
                            <th class="col-sm-1"><?= lang('emp_id') ?></th>
                            <th><strong><?= lang('name') ?></strong></th>
                            <th><strong><?= lang('salary_type') ?></strong></th>
                            <th><strong><?= lang('basic_salary') ?></strong></th>
                            <th><strong><?= lang('net_salary') ?></strong></th>
                            <th style="text-align: center;"><strong><?= lang('details') ?></strong></th>
                            <th style="text-align: center;"><strong><?= lang('status') ?></strong></th>
                            <th style="text-align: right;"><strong><?= lang('action') ?></strong></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $akey = 0;
                        if (!empty($employee_info)) : foreach ($employee_info as $v_emp_info) :
                            ?>
                            <?php if (!empty($v_emp_info)) :
                            $akey += count(array($v_emp_info));
                            $key = $akey - 1;
                            foreach ($v_emp_info as $v_employee) : 
                                $set_salary = empty($v_employee->salary_grade) && empty($v_employee->hourly_grade);
                                $salary_info = $this->payroll_model->check_by(array('user_id' => $v_employee->user_id, 'payment_month' => $payment_month), 'tbl_salary_payment');
                            ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <?php if (empty($set_salary)): ?>
                                            <input type="checkbox" class="employee_checkbox" value="<?php echo $v_employee->user_id; ?>" data-name="<?php echo htmlspecialchars($v_employee->fullname); ?>" style="cursor: pointer; transform: scale(1.15);">
                                        <?php else: ?>
                                            <span class="text-muted" title="Salary not set"><i class="fa fa-minus"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-primary text-bold"><?php echo html_escape($v_employee->employment_id); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id) { ?>
                                            <a href="<?php echo base_url() ?>admin/payroll/salary_payment_details/<?php echo $salary_info->salary_payment_id ?>"
                                               title="View Payment Details" data-toggle="modal"
                                               data-target="#myModal_lg" style="font-weight: 600;"><?php echo html_escape($v_employee->fullname); ?></a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url() ?>admin/payroll/view_payment_details/<?php echo $v_employee->user_id . '/' . $payment_month ?>"
                                               title="View Calculation Details" data-toggle="modal"
                                               data-target="#myModal_lg" style="font-weight: 600;"><?php echo html_escape($v_employee->fullname); ?></a>
                                        <?php } ?>
                                        <?php 
                                        $sub_titles = array();
                                        if (!empty($v_employee->deptname)) {
                                            $sub_titles[] = html_escape($v_employee->deptname);
                                        }
                                        if (!empty($v_employee->designations)) {
                                            $sub_titles[] = html_escape($v_employee->designations);
                                        }
                                        if (!empty($sub_titles)): ?>
                                            <div style="font-size: 11px; color: #64748b;"><?php echo implode(' &bull; ', $sub_titles); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php
                                        if (!empty($v_employee->salary_grade)) {
                                            echo html_escape($v_employee->salary_grade) . ' <small class="text-muted">(' . lang('monthly') . ')</small>';
                                        } else if (!empty($v_employee->hourly_grade)) {
                                            echo html_escape($v_employee->hourly_grade) . ' <small class="text-muted">(' . lang('hourly') . ')</small>';
                                        } else {
                                            echo '<span class="text-danger">' . lang('did_not_set_salary_yet') . '</span>';
                                        }
                                        ?></td>
                                    <td><?php
                                        if (!empty($v_employee->basic_salary)) {
                                            echo display_money($v_employee->basic_salary);
                                        } else if (!empty($v_employee->hourly_grade)) {
                                            echo display_money($v_employee->hourly_rate) . ' <small>(' . lang('per_hour') . ')</small>';
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?></td>
                                    <td style="font-weight: 700; color: #166534;"><?php
                                        if (!empty($total_hours)) {
                                            foreach ($total_hours as $index => $v_total_hours) {
                                                if ($index == $v_employee->user_id) {
                                                    if (!empty($v_total_hours)) {
                                                        $total_hour = $v_total_hours['total_hours'];
                                                        $total_minutes = $v_total_hours['total_minutes'];
                                                        if ($total_hour > 0) {
                                                            $hours_ammount = $total_hour * $v_employee->hourly_rate;
                                                        } else {
                                                            $hours_ammount = 0;
                                                        }
                                                        if ($total_minutes > 0) {
                                                            $amount = round($v_employee->hourly_rate / 60, 2);
                                                            $minutes_ammount = $total_minutes * $amount;
                                                        } else {
                                                            $minutes_ammount = 0;
                                                        }
                                                        if (!empty($advance_salary[$index])) {
                                                            $advance_amount = $advance_salary[$index]['advance_amount'];
                                                        } else {
                                                            $advance_amount = 0;
                                                        }
                                                        if (!empty($award_info[$index])) {
                                                            $total_award = $award_info[$index]['award_amount'];
                                                        } else {
                                                            $total_award = 0;
                                                        }
                                                        $total_amount = $hours_ammount + $minutes_ammount + $total_award - $advance_amount;
                                                        echo display_money(round($total_amount, 2));
                                                    }
                                                }
                                            }
                                        }
                                        if (!empty($v_employee->basic_salary)) {
                                            if (!empty($allowance_info)) {
                                                foreach ($allowance_info as $al_index => $v_allowance) {
                                                    if ($al_index == $v_employee->user_id) {
                                                        $total_allowance = $v_allowance;
                                                    }
                                                }
                                            }
                                            if (!empty($deduction_info)) {
                                                foreach ($deduction_info as $dd_index => $v_deduction) {
                                                    if ($dd_index == $v_employee->user_id) {
                                                        $total_deduction = $v_deduction;
                                                    }
                                                }
                                            }
                                            if (!empty($advance_salary)) {
                                                foreach ($advance_salary as $add_index => $v_advance) {
                                                    if ($add_index == $v_employee->user_id) {
                                                        $total_advance = $v_advance['advance_amount'];
                                                    }
                                                }
                                            }
                                            if (!empty($award_info)) {
                                                foreach ($award_info as $aw_index => $v_award_info) {
                                                    if ($aw_index == $v_employee->user_id) {
                                                        $total_award = $v_award_info['award_amount'];
                                                    }
                                                }
                                            }
                                            
                                            if (!empty($overtime_info) && !empty($v_employee->overtime_salary)) {
                                                foreach ($overtime_info as $over_index => $v_overtime) {
                                                    if ($over_index == $v_employee->user_id) {
                                                        $total_hour = $v_overtime['overtime_hours'];
                                                        $total_minutes = $v_overtime['overtime_minutes'];
                                                        if ($total_hour > 0) {
                                                            $hours_ammount = $total_hour * $v_employee->overtime_salary;
                                                        } else {
                                                            $hours_ammount = 0;
                                                        }
                                                        if ($total_minutes > 0) {
                                                            $amount = round($v_employee->overtime_salary / 60, 2);
                                                            $minutes_ammount = $total_minutes * $amount;
                                                        } else {
                                                            $minutes_ammount = 0;
                                                        }
                                                        $total_amount = $hours_ammount + $minutes_ammount;
                                                    }
                                                }
                                            }
                                            
                                            if (empty($total_advance)) {
                                                $total_advance = 0;
                                            }
                                            if (empty($total_deduction)) {
                                                $total_deduction = 0;
                                            }
                                            if (empty($total_award)) {
                                                $total_award = 0;
                                            }
                                            if (empty($total_allowance)) {
                                                $total_allowance = 0;
                                            }
                                            if (empty($total_amount)) {
                                                $total_amount = 0;
                                            }
                                            if (empty($v_employee->basic_salary)) {
                                                $basic_salary = 0;
                                            } else {
                                                $basic_salary = $v_employee->basic_salary;
                                            }
                                            $new_salary = $basic_salary + $total_allowance + $total_amount + $total_award - $total_advance - $total_deduction;
                                            $w_days = !empty($total_working_days) ? $total_working_days : 30;
                                            $p_days = !empty($total_present[$v_employee->user_id]) ? $total_present[$v_employee->user_id] : 0;
                                            $per_day_salary = $new_salary / $w_days;
                                            $total_salary = $per_day_salary * $p_days;
                                            
                                            echo display_money($total_salary);
                                        }
                                        ?></td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id) { ?>
                                            <a href="<?php echo base_url() ?>admin/payroll/salary_payment_details/<?php echo $salary_info->salary_payment_id ?>"
                                               class="btn btn-info btn-xs" title="View Payment Details" data-toggle="modal"
                                               data-target="#myModal_lg"><span class="fa fa-list-alt"></span></a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url() ?>admin/payroll/view_payment_details/<?php echo $v_employee->user_id . '/' . $payment_month ?>"
                                               class="btn btn-info btn-xs" title="View Calculation Details" data-toggle="modal"
                                               data-target="#myModal_lg"><span class="fa fa-list-alt"></span></a>
                                        <?php } ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id): ?>
                                            <?php if (isset($salary_info->payment_status) && $salary_info->payment_status == 'hold'): ?>
                                                <span class="label label-warning" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-pause-circle"></i> Hold</span>
                                            <?php elseif (isset($salary_info->payment_status) && $salary_info->payment_status == 'unpaid'): ?>
                                                <span class="label label-danger" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-clock-o"></i> <?= lang('unpaid') ?></span>
                                            <?php else: ?>
                                                <span class="label label-success" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-check-circle"></i> <?= lang('paid') ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (empty($set_salary)): ?>
                                                <span class="label label-danger" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-clock-o"></i> <?= lang('unpaid') ?></span>
                                            <?php else: ?>
                                                <span class="label label-default" style="padding: 4px 8px;">Not Set</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id): ?>
                                            <?php if (!isset($salary_info->payment_status) || $salary_info->payment_status == 'paid'): ?>
                                                <a class="btn btn-xs btn-success" target="_blank"
                                                   href="<?php echo base_url() ?>admin/payroll/receive_generated/<?php echo $salary_info->salary_payment_id; ?>" title="View / Print Payslip">
                                                    <i class="fa fa-file-text-o"></i> Payslip
                                                </a>
                                                <div class="btn-group" style="margin-left: 3px;">
                                                    <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" title="Change Status">
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                        <li><a href="javascript:void(0);" class="btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="hold"><i class="fa fa-pause-circle text-warning"></i> Mark as Hold</a></li>
                                                        <li><a href="javascript:void(0);" class="btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="unpaid"><i class="fa fa-times-circle text-danger"></i> Mark as Unpaid</a></li>
                                                    </ul>
                                                </div>
                                            <?php elseif ($salary_info->payment_status == 'hold'): ?>
                                                <button type="button" class="btn btn-xs btn-success btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="paid" title="Mark as Paid">
                                                    <i class="fa fa-check"></i> Paid
                                                </button>
                                                <button type="button" class="btn btn-xs btn-danger btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="unpaid" title="Mark as Unpaid" style="margin-left: 2px;">
                                                    <i class="fa fa-times"></i> Unpaid
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-xs btn-success btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="paid" title="Mark as Paid">
                                                    <i class="fa fa-check"></i> Paid
                                                </button>
                                                <button type="button" class="btn btn-xs btn-warning btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="hold" title="Mark as Hold" style="margin-left: 2px;">
                                                    <i class="fa fa-pause"></i> Hold
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!empty($set_salary)): ?>
                                                <a class="btn btn-xs btn-warning text-bold" target="_blank"
                                                   href="<?php echo base_url('admin/payroll/create_salarytemplate/' . $v_employee->user_id . '?user_id=' . $v_employee->user_id); ?>">
                                                    <i class="fa fa-plus"></i> <?= lang('set_slary') ?>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-xs btn-success btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="paid" title="Mark as Paid">
                                                    <i class="fa fa-check"></i> Paid
                                                </button>
                                                <button type="button" class="btn btn-xs btn-warning btn-single-status" data-user-id="<?php echo $v_employee->user_id; ?>" data-status="hold" title="Mark as Hold" style="margin-left: 2px;">
                                                    <i class="fa fa-pause"></i> Hold
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    function update_selected_count() {
        var total = $('.employee_checkbox').length;
        var checked = $('.employee_checkbox:checked').length;
        $('#selected_count_badge').text(checked + ' Selected');
        if (checked > 0) {
            $('#selected_count_badge').css('background', '#16a34a');
        } else {
            $('#selected_count_badge').css('background', '#3b82f6');
        }
        if (checked === total && total > 0) {
            $('#select_all_employees').prop('checked', true);
        } else {
            $('#select_all_employees').prop('checked', false);
        }
    }

    // Select All Checkbox Handler
    $(document).on('change click', '#select_all_employees', function() {
        var isChecked = $(this).is(':checked');
        $('.employee_checkbox').prop('checked', isChecked);
        update_selected_count();
    });

    // Individual Employee Checkbox Handler
    $(document).on('change click', '.employee_checkbox', function() {
        var $tr = $(this).closest('tr');
        if ($(this).is(':checked')) {
            $tr.addClass('selected');
        } else {
            $tr.removeClass('selected');
        }
        update_selected_count();
    });

    // Also support clicking anywhere on table row to select
    $(document).on('click', '#datatable_action tbody tr', function(e) {
        if ($(e.target).is('input, a, button, i, span.label, span.badge') || $(e.target).closest('a, button, input').length) {
            return;
        }
        var cb = $(this).find('.employee_checkbox');
        if (cb.length > 0) {
            var newState = !cb.is(':checked');
            cb.prop('checked', newState);
            if (newState) {
                $(this).addClass('selected');
            } else {
                $(this).removeClass('selected');
            }
            update_selected_count();
        }
    });

    // Listen to DataTables select/deselect events
    $(document).on('select.dt deselect.dt', '#datatable_action', function() {
        $('table#datatable_action tbody tr').each(function() {
            var $tr = $(this);
            var cb = $tr.find('.employee_checkbox');
            if (cb.length > 0 && $tr.hasClass('selected')) {
                cb.prop('checked', true);
            }
        });
        update_selected_count();
    });

    // Helper: collect all checked employee IDs
    function get_selected_user_ids() {
        var selected = [];
        $('.employee_checkbox:checked').each(function() {
            var val = $(this).val();
            if (val && selected.indexOf(val) === -1) {
                selected.push(val);
            }
        });
        // Fallback: check DataTables selected rows
        $('table#datatable_action tbody tr.selected').each(function() {
            var cb = $(this).find('.employee_checkbox');
            if (cb.length > 0) {
                var val = cb.val();
                if (val && selected.indexOf(val) === -1) {
                    selected.push(val);
                }
            }
        });
        return selected;
    }

    // Processing Overlay Helpers
    function show_processing_indicator(title, subtitle) {
        if ($('#payroll_loading_overlay').length === 0) {
            $('body').append(`
                <div id="payroll_loading_overlay" style="display:flex; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.75); z-index:999999; justify-content:center; align-items:center; backdrop-filter:blur(3px);">
                    <div style="background:#ffffff; border-radius:12px; padding:32px 38px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); text-align:center; max-width:440px; width:90%; border:1px solid #e2e8f0;">
                        <div style="display:inline-block; width:52px; height:52px; border:5px solid #e2e8f0; border-top-color:#2563eb; border-radius:50%; animation:spin_payroll 0.8s linear infinite; margin-bottom:18px;"></div>
                        <h4 id="loading_overlay_title" style="margin:0 0 8px; font-weight:700; color:#1e293b; font-size:18px;">Processing Request...</h4>
                        <p id="loading_overlay_subtitle" style="margin:0; color:#64748b; font-size:13px; line-height:1.5;">Please wait, updating payment records...</p>
                    </div>
                </div>
                <style>
                @keyframes spin_payroll {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                </style>
            `);
        }
        $('#loading_overlay_title').html(title || 'Processing Request...');
        $('#loading_overlay_subtitle').html(subtitle || 'Please wait, updating records. Do not close or refresh this page.');
        $('#payroll_loading_overlay').css('display', 'flex').show();
        $('body').css('cursor', 'wait');
    }

    function hide_processing_indicator() {
        $('#payroll_loading_overlay').hide();
        $('body').css('cursor', 'default');
    }

    // Perform Status Change (Bulk or Single)
    function change_payment_status(userIds, status, $triggerBtn) {
        if (!userIds || userIds.length === 0) {
            alert('Please select at least one employee from the list.');
            return;
        }

        var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        var confirmMsg = 'Are you sure you want to mark ' + userIds.length + ' employee(s) as ' + statusLabel + '?';
        if (!confirm(confirmMsg)) return;

        var paymentMonth = '<?= !empty($payment_month) ? $payment_month : "" ?>';
        var deptId = '<?= !empty($departments_id) ? $departments_id : "" ?>';

        var originalBtnHtml = '';
        if ($triggerBtn && $triggerBtn.length) {
            originalBtnHtml = $triggerBtn.html();
            $triggerBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        }

        var $buttons = $('#btn_bulk_paid, #btn_bulk_hold, #btn_bulk_unpaid, .btn-single-status');
        $buttons.prop('disabled', true);

        show_processing_indicator(
            'Marking as ' + statusLabel + '...',
            'Processing payroll for <strong>' + userIds.length + ' employee(s)</strong>. Please wait, updating status and generating payslip records.'
        );

        $.ajax({
            url: '<?= base_url("admin/payroll/update_bulk_payment_status") ?>',
            type: 'POST',
            data: {
                user_ids: userIds,
                status: status,
                payment_month: paymentMonth,
                departments_id: deptId
            },
            dataType: 'json',
            success: function(res) {
                if (res && res.success) {
                    $('#loading_overlay_title').html('<span style="color:#16a34a;"><i class="fa fa-check-circle"></i> ' + (res.message || 'Updated Successfully!') + '</span>');
                    $('#loading_overlay_subtitle').html('Reloading page to display updated records...');
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    hide_processing_indicator();
                    $buttons.prop('disabled', false);
                    if ($triggerBtn && $triggerBtn.length) {
                        $triggerBtn.html(originalBtnHtml);
                    }
                    alert(res && res.message ? res.message : 'Error updating payment status.');
                }
            },
            error: function(xhr, statusText, err) {
                hide_processing_indicator();
                $buttons.prop('disabled', false);
                if ($triggerBtn && $triggerBtn.length) {
                    $triggerBtn.html(originalBtnHtml);
                }
                console.error('AJAX Error:', xhr.responseText);
                alert('An unexpected error occurred while updating status. Please try again.');
            }
        });
    }

    // Bulk Action Buttons
    $(document).on('click', '#btn_bulk_paid', function(e) {
        e.preventDefault();
        var selected = get_selected_user_ids();
        change_payment_status(selected, 'paid', $(this));
    });

    $(document).on('click', '#btn_bulk_hold', function(e) {
        e.preventDefault();
        var selected = get_selected_user_ids();
        change_payment_status(selected, 'hold', $(this));
    });

    $(document).on('click', '#btn_bulk_unpaid', function(e) {
        e.preventDefault();
        var selected = get_selected_user_ids();
        change_payment_status(selected, 'unpaid', $(this));
    });

    // Single row status button
    $(document).on('click', '.btn-single-status', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var uid = $(this).data('user-id');
        var status = $(this).data('status');
        if (uid && status) {
            change_payment_status([uid], status, $(this));
        }
    });

    // Initial check on load in case any checkboxes are pre-checked
    update_selected_count();
});
</script>