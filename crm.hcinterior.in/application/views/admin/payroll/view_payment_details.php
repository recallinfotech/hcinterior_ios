<?php
$curency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
$currency_symbol = !empty($curency->symbol) ? $curency->symbol : '₹';

$basic_salary = !empty($employee_info->basic_salary) ? floatval($employee_info->basic_salary) : 0;

$total_allowance = 0;
if (!empty($allowance_info)) {
    foreach ($allowance_info as $v_all) {
        $total_allowance += floatval($v_all->allowance_value);
    }
}
$gross_salary = $basic_salary + $total_allowance;

$total_deduction = 0;
if (!empty($deduction_info)) {
    foreach ($deduction_info as $v_ded) {
        $total_deduction += floatval($v_ded->deduction_value);
    }
}

// Absent deduction
$total_working_days = !empty($total_working_days) ? $total_working_days : (!empty(config_item('salary_count_days')) ? floatval(config_item('salary_count_days')) : 26);
$total_absent = !empty($total_absent) ? floatval($total_absent) : 0;
$per_day_salary = ($total_working_days > 0) ? ($gross_salary / $total_working_days) : 0;
$total_absent_salary = round($per_day_salary * $total_absent, 2);
$total_deduction += $total_absent_salary;

// Advance salary
$advance_amount = 0;
if (!empty($advance_salary['advance_amount'])) {
    $advance_amount = floatval($advance_salary['advance_amount']);
    $total_deduction += $advance_amount;
}

$net_salary = $gross_salary - $total_deduction;
$fine_deduction = !empty($salary_payment_info->fine_deduction) ? floatval($salary_payment_info->fine_deduction) : 0;
$paid_amount = $net_salary - $fine_deduction;
?>
<div id="printableArea">
    <style>
        .payroll-modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #ffffff;
            padding: 16px 22px;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .payroll-modal-header h4 {
            color: #ffffff;
            margin: 0;
            font-size: 17px;
            font-weight: 600;
        }
        .emp-profile-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .emp-profile-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .salary-stat-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .salary-stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .salary-stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        .breakdown-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            height: 100%;
        }
        .breakdown-card-header {
            padding: 12px 16px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .header-allowances {
            background: #f0fdf4;
            color: #166534;
        }
        .header-deductions {
            background: #fef2f2;
            color: #991b1b;
        }
        .breakdown-table {
            width: 100%;
            margin-bottom: 0;
        }
        .breakdown-table td {
            padding: 10px 16px !important;
            border-top: 1px solid #f1f5f9 !important;
            font-size: 13px;
        }
        .breakdown-table tr:first-child td {
            border-top: none !important;
        }
        .breakdown-empty-state {
            padding: 30px 15px;
            text-align: center;
            color: #94a3b8;
        }
        .summary-banner {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-radius: 8px;
            padding: 18px 24px;
            margin-top: 5px;
        }
        .summary-banner .summary-item {
            border-right: 1px solid rgba(255,255,255,0.2);
            padding: 0 15px;
        }
        .summary-banner .summary-item:last-child {
            border-right: none;
        }
        @media print {
            .hidden-print { display: none !important; }
            .show_print { display: block !important; }
        }
    </style>

    <div class="payroll-modal-header hidden-print">
        <h4 id="myModalLabel">
            <i class="fa fa-calculator" style="margin-right: 8px; color: #38bdf8;"></i>
            <?= lang('payment_details') ?> &mdash; <span style="color: #93c5fd;"><?= html_escape($employee_info->fullname) ?></span>
        </h4>
        <div style="display: flex; gap: 6px; align-items: center;">
            <button class="btn btn-xs btn-danger" type="button" data-toggle="tooltip" title="<?= lang('print') ?>"
                    onclick="printDiv('printableArea')"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>

    <div class="modal-body wrap-modal wrap" style="padding: 20px;">
        <div class="show_print" style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 20px; display: none;">
            <table style="width: 100%; vertical-align: middle;">
                <tr>
                    <td style="width: 55px; border: 0px;">
                        <img style="width: 50px; height: 50px; margin-bottom: 5px;"
                             src="<?= base_url() . config_item('company_logo') ?>" alt="" class="img-circle" />
                    </td>
                    <td style="border: 0px;">
                        <h4 style="margin: 0; font-weight: bold;"><?= config_item('company_name') ?></h4>
                        <p style="margin: 2px 0 0; font-size: 12px; color: #555;">Payment Calculation: <?= html_escape($employee_info->fullname) ?> &mdash; <?= date('F Y', strtotime($payment_month)) ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Employee Profile Header -->
        <div class="emp-profile-card">
            <?php if (!empty($employee_info->avatar)): ?>
                <img src="<?php echo base_url() . $employee_info->avatar; ?>" class="emp-profile-avatar" alt="Avatar">
            <?php else: ?>
                <img src="<?php echo base_url() ?>/img/user.png" class="emp-profile-avatar" alt="User">
            <?php endif; ?>

            <div style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #0f172a;">
                        <?= html_escape($employee_info->fullname) ?>
                    </h3>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <?php if (!empty($employee_info->salary_grade)): ?>
                            <span class="label label-primary" style="font-size: 11px; padding: 4px 8px;">
                                <?= html_escape($employee_info->salary_grade) ?>
                            </span>
                        <?php endif; ?>
                        <span class="label label-info" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fa fa-calendar"></i> <?= date('F Y', strtotime($payment_month)) ?>
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 13px; color: #475569; margin-top: 6px;">
                    <div><strong>Emp ID:</strong> <span class="text-primary"><?= html_escape($employee_info->employment_id) ?></span></div>
                    <div><strong>Dept:</strong> <?= html_escape($employee_info->deptname) ?></div>
                    <div><strong>Designation:</strong> <?= html_escape($employee_info->designations) ?></div>
                    <div><strong>Present Days:</strong> <span class="badge" style="background: #16a34a;"><?= !empty($total_present) ? $total_present : 0 ?></span></div>
                    <?php if (!empty($total_absent)): ?>
                        <div><strong>Absent Days:</strong> <span class="badge" style="background: #dc2626;"><?= $total_absent ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Overview Stat Cards -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-sm-3 col-xs-6">
                <div class="salary-stat-box">
                    <div class="salary-stat-label"><?= lang('basic_salary') ?></div>
                    <div class="salary-stat-value"><?= display_money($basic_salary, $currency_symbol) ?></div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="salary-stat-box">
                    <div class="salary-stat-label"><?= lang('total_allowance') ?></div>
                    <div class="salary-stat-value" style="color: #16a34a;"><?= display_money($total_allowance, $currency_symbol) ?></div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="salary-stat-box">
                    <div class="salary-stat-label"><?= lang('total_deduction') ?></div>
                    <div class="salary-stat-value" style="color: #dc2626;"><?= display_money($total_deduction, $currency_symbol) ?></div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="salary-stat-box" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <div class="salary-stat-label" style="color: #166534;"><?= lang('net_salary') ?></div>
                    <div class="salary-stat-value" style="color: #15803d;"><?= display_money($net_salary, $currency_symbol) ?></div>
                </div>
            </div>
        </div>

        <!-- Two Columns Breakdown -->
        <div class="row">
            <!-- Allowances Column -->
            <div class="col-sm-6">
                <div class="breakdown-card">
                    <div class="breakdown-card-header header-allowances">
                        <span><i class="fa fa-plus-circle"></i> <?= lang('allowances') ?> (Earnings)</span>
                        <span class="badge" style="background: #16a34a; color: #fff;"><?= count(!empty($allowance_info) ? $allowance_info : []) ?></span>
                    </div>
                    <?php if (!empty($allowance_info)) : ?>
                        <table class="breakdown-table">
                            <tbody>
                            <?php foreach ($allowance_info as $v_all) : ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-check-circle text-success" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= html_escape($v_all->allowance_label) ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #0f172a;">
                                        <?= display_money($v_all->allowance_value, $currency_symbol) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="background: #f8fafc; font-weight: 700;">
                                <td style="color: #1e293b;">Total Allowances</td>
                                <td class="text-right" style="color: #16a34a; font-size: 14px;">
                                    <?= display_money($total_allowance, $currency_symbol) ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="breakdown-empty-state">
                            <i class="fa fa-folder-open-o" style="font-size: 24px; opacity: 0.5; margin-bottom: 6px; display: block;"></i>
                            <div style="font-size: 12px;"><?= lang('nothing_to_display') ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deductions Column -->
            <div class="col-sm-6">
                <div class="breakdown-card">
                    <div class="breakdown-card-header header-deductions">
                        <span><i class="fa fa-minus-circle"></i> <?= lang('deductions') ?></span>
                    </div>
                    <?php if (!empty($deduction_info) || !empty($total_absent_salary) || !empty($advance_amount)) : ?>
                        <table class="breakdown-table">
                            <tbody>
                            <?php if (!empty($deduction_info)): foreach ($deduction_info as $v_ded) : ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-minus-circle text-danger" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= html_escape($v_ded->deduction_label) ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #0f172a;">
                                        <?= display_money($v_ded->deduction_value, $currency_symbol) ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            <?php if (!empty($total_absent_salary)): ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-user-times text-danger" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= lang('absent') . ' ' . lang('deduction') ?> (<?= $total_absent ?> days)
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #dc2626;">
                                        <?= display_money($total_absent_salary, $currency_symbol) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($advance_amount)): ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-credit-card text-danger" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= lang('advance_salary') ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #dc2626;">
                                        <?= display_money($advance_amount, $currency_symbol) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr style="background: #f8fafc; font-weight: 700;">
                                <td style="color: #1e293b;">Total Deductions</td>
                                <td class="text-right" style="color: #dc2626; font-size: 14px;">
                                    <?= display_money($total_deduction, $currency_symbol) ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="breakdown-empty-state">
                            <i class="fa fa-smile-o" style="color: #10b981; font-size: 24px; opacity: 0.8; margin-bottom: 6px; display: block;"></i>
                            <div style="font-size: 13px; font-weight: 500; color: #475569;">No Deductions Applicable</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary Banner -->
        <div class="summary-banner">
            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                <div class="col-sm-4 summary-item text-center">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('gross_salary') ?>
                    </div>
                    <div style="font-size: 18px; font-weight: 700;">
                        <?= display_money($gross_salary, $currency_symbol) ?>
                    </div>
                </div>
                <div class="col-sm-4 summary-item text-center">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('total_deduction') ?>
                    </div>
                    <div style="font-size: 18px; font-weight: 700; color: #fecaca;">
                        <?= display_money($total_deduction, $currency_symbol) ?>
                    </div>
                </div>
                <div class="col-sm-4 summary-item text-center">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('net_salary') ?> (Payable)
                    </div>
                    <div style="font-size: 24px; font-weight: 800; color: #a7f3d0;">
                        <?= display_money($net_salary, $currency_symbol) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer hidden-print" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
    <button type="button" class="btn btn-default" data-dismiss="modal" style="padding: 6px 18px; font-weight: 500;">
        <i class="fa fa-times"></i> <?= lang('close') ?>
    </button>
</div>

<script type="text/javascript">
function printDiv(printableArea) {
    var printContents = document.getElementById(printableArea).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
