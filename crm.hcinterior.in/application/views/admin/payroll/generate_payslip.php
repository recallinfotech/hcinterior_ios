<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom" data-collapsed="0">
            <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="panel-title" style="margin: 0;">
                    <strong><i class="fa fa-calculator"></i> <?= lang('generate_payslip') ?></strong>
                </div>
                <div>
                    <a href="<?= base_url('admin/payroll/employee_salary_list') ?>" class="btn btn-xs btn-primary" style="font-weight: 600; color: #ffffff !important;">
                        <i class="fa fa-users"></i> <?= lang('employee_salary_list') ?>
                    </a>
                </div>
            </div>
            <form id="form" role="form" enctype="multipart/form-data"
                  action="<?php echo base_url() ?>admin/payroll/generate_payslip" method="post"
                  class="form-horizontal form-groups-bordered" style="padding-top: 15px;">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label" style="font-weight: 600;">
                            <?= lang('select_department') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-5">
                            <select name="departments_id" class="form-control select_box" style="width: 100%;">
                                <option value=""><?= lang('select_department') ?>...</option>
                                <?php if (!empty($all_department_info)): foreach ($all_department_info as $v_department_info) :
                                    $deptname = !empty($v_department_info->deptname) ? $v_department_info->deptname : lang('undefined_department');
                                    ?>
                                    <option value="<?php echo $v_department_info->departments_id; ?>"
                                        <?php
                                        if (!empty($departments_id)) {
                                            echo $v_department_info->departments_id == $departments_id ? 'selected' : '';
                                        }
                                        ?>><?php echo html_escape($deptname); ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" style="font-weight: 600;">
                            <?= lang('select') . ' ' . lang('month') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-5">
                            <div class="input-group">
                                <input type="text" value="<?php
                                if (!empty($payment_month)) {
                                    echo $payment_month;
                                } else {
                                    echo date('Y-m');
                                }
                                ?>" class="form-control monthyear" name="payment_month" data-format="yyyy-mm">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-5">
                            <button id="submit" type="submit" name="flag" value="1"
                                    class="btn btn-primary btn-block" style="font-weight: 600; padding: 9px;">
                                <i class="fa fa-search"></i> <?= lang('go') ?> &mdash; <?= lang('generate_payslip') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($flag)): ?>
    <div class="row">
        <div class="col-sm-12" data-offset="0">
            <div class="panel panel-custom">
                <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; padding: 12px 20px;">
                    <div class="panel-title" style="margin:0;">
                        <span>
                            <strong><i class="fa fa-calendar-check-o"></i> <?= lang('generate_payslip_for') ?><?php
                                if (!empty($payment_month)) {
                                    echo ' <span class="text-danger" style="font-weight: 700;">' . date('F Y', strtotime($payment_month)) . '</span>';
                                }
                                ?></strong>
                        </span>
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="<?= base_url('admin/payroll/import_salary_sheet?payment_month=' . (!empty($payment_month) ? $payment_month : date('Y-m'))) ?>" class="btn btn-xs btn-warning" style="font-weight:600; padding: 4px 10px;">
                            <i class="fa fa-file-excel-o"></i> ⚡ Upload Excel/CSV Sheet
                        </a>
                        <a href="<?= base_url('admin/payroll/bulk_high_creation_pdf?payment_month=' . (!empty($payment_month) ? $payment_month : date('Y-m')) . (!empty($departments_id) ? '&departments_id=' . $departments_id : '')) ?>" target="_blank" class="btn btn-xs btn-success" style="font-weight:600; padding: 4px 10px;">
                            <i class="fa fa-file-pdf-o"></i> Bulk PDF (All Staff Slips)
                        </a>
                        <a href="<?= base_url('admin/payroll/download_sample_salary_csv?month=' . (!empty($payment_month) ? $payment_month : date('Y-m'))) ?>" class="btn btn-xs btn-info" style="font-weight:600; padding: 4px 10px;">
                            <i class="fa fa-download"></i> Sample CSV
                        </a>
                    </div>
                </div>

                <div class="panel-body" style="padding: 15px 20px;">
                    <table class="table table-striped table-hover" id="datatable_action" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th class="col-sm-1"><?= lang('emp_id') ?></th>
                            <th><?= lang('name') ?></th>
                            <th><?= lang('salary_grade') ?></th>
                            <th><?= lang('basic_salary') ?></th>
                            <th><?= lang('net_salary') ?></th>
                            <th class="text-center"><?= lang('details') ?></th>
                            <th class="text-center"><?= lang('status') ?></th>
                            <th class="text-right"><?= lang('action') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $curency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
                        $currency_symbol = !empty($curency->symbol) ? $curency->symbol : '₹';

                        if (!empty($employee_info)): foreach ($employee_info as $v_emp_info):
                            if (!empty($v_emp_info)): foreach ($v_emp_info as $v_employee):
                                $salary_info = $this->payroll_model->check_by(array('user_id' => $v_employee->user_id, 'payment_month' => $payment_month), 'tbl_salary_payment');
                                $set_salary = empty($v_employee->salary_template_id);
                                ?>
                                <tr>
                                    <td>
                                        <span class="text-primary text-bold"><?php echo html_escape($v_employee->employment_id); ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">
                                            <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id) { ?>
                                                <a href="<?php echo base_url() ?>admin/payroll/salary_payment_details/<?php echo $salary_info->salary_payment_id ?>"
                                                   class="text-info" title="View Payment Details" data-toggle="modal" data-target="#myModal_lg">
                                                    <?php echo html_escape($v_employee->fullname); ?>
                                                </a>
                                            <?php } else { ?>
                                                <a href="<?php echo base_url() ?>admin/payroll/view_payment_details/<?php echo $v_employee->user_id . '/' . $payment_month ?>"
                                                   class="text-info" title="View Calculation Details" data-toggle="modal" data-target="#myModal_lg">
                                                    <?php echo html_escape($v_employee->fullname); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b;">
                                            <?php echo html_escape($v_employee->designations); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($v_employee->salary_grade)) { ?>
                                            <span class="label label-primary"><?php echo html_escape($v_employee->salary_grade); ?></span>
                                        <?php } else { ?>
                                            <span class="label label-warning"><?= lang('did_not_set_salary_yet') ?></span>
                                        <?php } ?>
                                    </td>
                                    <td style="font-weight: 500;">
                                        <?php
                                        if (!empty($v_employee->basic_salary)) {
                                            echo display_money($v_employee->basic_salary, $currency_symbol);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td style="font-weight: 700; color: #166534;">
                                        <?php
                                        if (!empty($v_employee->basic_salary)) {
                                            $total_allowance = 0;
                                            if (!empty($allowance_info[$v_employee->user_id])) {
                                                $total_allowance = floatval($allowance_info[$v_employee->user_id]);
                                            }
                                            $total_deduction = 0;
                                            if (!empty($deduction_info[$v_employee->user_id])) {
                                                $total_deduction = floatval($deduction_info[$v_employee->user_id]);
                                            }
                                            $total_advance = 0;
                                            if (!empty($advance_salary[$v_employee->user_id]['advance_amount'])) {
                                                $total_advance = floatval($advance_salary[$v_employee->user_id]['advance_amount']);
                                            }

                                            $net_calc = floatval($v_employee->basic_salary) + $total_allowance - $total_deduction - $total_advance;
                                            echo display_money($net_calc, $currency_symbol);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id) { ?>
                                            <a href="<?php echo base_url() ?>admin/payroll/salary_payment_details/<?php echo $salary_info->salary_payment_id ?>"
                                               class="btn btn-info btn-xs" title="View Payment Details" data-toggle="modal" data-target="#myModal_lg">
                                                <i class="fa fa-list-alt"></i>
                                            </a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url() ?>admin/payroll/view_payment_details/<?php echo $v_employee->user_id . '/' . $payment_month ?>"
                                               class="btn btn-info btn-xs" title="View Calculation Details" data-toggle="modal" data-target="#myModal_lg">
                                                <i class="fa fa-list-alt"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id) { ?>
                                            <?php if (isset($salary_info->payment_status) && $salary_info->payment_status == 'hold'): ?>
                                                <span class="label label-warning" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-pause-circle"></i> Hold</span>
                                            <?php elseif (isset($salary_info->payment_status) && $salary_info->payment_status == 'unpaid'): ?>
                                                <span class="label label-danger" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-clock-o"></i> <?= lang('unpaid') ?></span>
                                            <?php else: ?>
                                                <span class="label label-success" style="padding: 4px 8px; font-weight: 600;"><i class="fa fa-check"></i> <?= lang('paid') ?></span>
                                            <?php endif; ?>
                                        <?php } else { ?>
                                            <?php if (empty($set_salary)) { ?>
                                                <span class="label label-danger" style="padding: 4px 8px; font-weight: 600;"><?= lang('unpaid') ?></span>
                                            <?php } else { ?>
                                                <span class="label label-warning" style="padding: 4px 8px;">Not Set</span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td class="text-right" style="white-space: nowrap;">
                                        <?php if (!empty($salary_info) && $salary_info->user_id == $v_employee->user_id) { ?>
                                            <a class="btn btn-xs btn-success" target="_blank"
                                               href="<?php echo base_url() ?>admin/payroll/receive_generated/<?php echo $salary_info->salary_payment_id; ?>" style="font-weight:600;">
                                                <i class="fa fa-check-circle"></i> <?= lang('payslip') ?>
                                            </a>
                                        <?php } else { ?>
                                            <?php if (!empty($set_salary)) { ?>
                                                <a class="btn btn-xs btn-warning" target="_blank"
                                                   href="<?php echo base_url() ?>admin/payroll/create_salarytemplate?user_id=<?php echo $v_employee->user_id; ?>" style="font-weight:600;">
                                                    <i class="fa fa-cog"></i> <?= lang('set_slary') ?>
                                                </a>
                                            <?php } else { ?>
                                                <a class="btn btn-xs btn-danger"
                                                   href="<?php echo base_url() ?>admin/payroll/make_payment/0/<?php echo $v_employee->departments_id . '/' . $payment_month; ?>" style="font-weight:600;">
                                                    <i class="fa fa-credit-card"></i> <?= lang('make_payment') ?>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>

                                        <a class="btn btn-xs btn-primary" target="_blank"
                                           href="<?= base_url('admin/payroll/high_creation_payslip_pdf/' . $v_employee->user_id . '?month=' . $payment_month) ?>"
                                           title="Download High Creation Salary Slip (PDF)" style="font-weight:600; margin-left: 4px;">
                                            <i class="fa fa-file-pdf-o"></i> Slip PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
