<?php
$curency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
$currency_symbol = !empty($curency->symbol) ? $curency->symbol : '₹';

$total_allowance = 0;
if (!empty($salary_allowance_info)) {
    foreach ($salary_allowance_info as $v_allowance) {
        $total_allowance += floatval($v_allowance->allowance_value);
    }
}
$basic_salary = floatval($emp_salary_info->basic_salary);
$gross_salary = $basic_salary + $total_allowance;

$total_deduction = 0;
if (!empty($salary_deduction_info)) {
    foreach ($salary_deduction_info as $v_deduction) {
        $total_deduction += floatval($v_deduction->deduction_value);
    }
}
$net_salary = $gross_salary - $total_deduction;
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
        .breakdown-empty-state i {
            font-size: 24px;
            margin-bottom: 8px;
            opacity: 0.6;
            display: block;
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
            <i class="fa fa-user" style="margin-right: 8px; color: #38bdf8;"></i>
            <?= lang('employee_salary_details') ?> &mdash; <span style="color: #93c5fd;"><?= html_escape($emp_salary_info->fullname) ?></span>
        </h4>
        <div style="display: flex; gap: 6px; align-items: center;">
            <span><?php echo btn_pdf('admin/payroll/make_pdf/' . $emp_salary_info->user_id); ?></span>
            <button class="btn btn-xs btn-danger" type="button" data-toggle="tooltip" title="Print"
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
                        <p style="margin: 2px 0 0; font-size: 12px; color: #555;">Employee Salary Breakdown: <?= html_escape($emp_salary_info->fullname) ?> (<?= html_escape($emp_salary_info->employment_id) ?>)</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Employee Info Card -->
        <div class="emp-profile-card">
            <?php if (!empty($emp_salary_info->avatar)): ?>
                <img src="<?php echo base_url() . $emp_salary_info->avatar; ?>" class="emp-profile-avatar" alt="Avatar">
            <?php else: ?>
                <img src="<?php echo base_url() ?>/img/user.png" class="emp-profile-avatar" alt="User">
            <?php endif; ?>

            <div style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <h3 style="margin: 0 0 6px; font-size: 20px; font-weight: 700; color: #0f172a;">
                        <?= html_escape($emp_salary_info->fullname) ?>
                    </h3>
                    <span class="label label-primary" style="font-size: 12px; padding: 4px 10px;">
                        <?= html_escape($emp_salary_info->salary_grade) ?>
                    </span>
                </div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 13px; color: #475569; margin-top: 6px;">
                    <div><strong>Emp ID:</strong> <span class="text-primary"><?= html_escape($emp_salary_info->employment_id) ?></span></div>
                    <div><strong>Dept:</strong> <?= html_escape($emp_salary_info->deptname) ?></div>
                    <div><strong>Designation:</strong> <?= html_escape($emp_salary_info->designations) ?></div>
                    <?php if (!empty($emp_salary_info->joining_date)) : ?>
                        <div><strong>Joining:</strong> <?= date('d M Y', strtotime($emp_salary_info->joining_date)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Overview Stats -->
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

        <!-- Allowances & Deductions Breakdown -->
        <div class="row">
            <!-- Allowances Column -->
            <div class="col-sm-6">
                <div class="breakdown-card">
                    <div class="breakdown-card-header header-allowances">
                        <span><i class="fa fa-plus-circle"></i> <?= lang('allowances') ?> (Earnings)</span>
                        <span class="badge" style="background: #16a34a; color: #fff;"><?= count(!empty($salary_allowance_info) ? $salary_allowance_info : []) ?></span>
                    </div>
                    <?php if (!empty($salary_allowance_info)) : ?>
                        <table class="breakdown-table">
                            <tbody>
                            <?php foreach ($salary_allowance_info as $v_allowance) : ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-check-circle text-success" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= html_escape($v_allowance->allowance_label) ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #0f172a;">
                                        <?= display_money($v_allowance->allowance_value, $currency_symbol) ?>
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
                            <i class="fa fa-folder-open-o"></i>
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
                        <span class="badge" style="background: #dc2626; color: #fff;"><?= count(!empty($salary_deduction_info) ? $salary_deduction_info : []) ?></span>
                    </div>
                    <?php if (!empty($salary_deduction_info)) : ?>
                        <table class="breakdown-table">
                            <tbody>
                            <?php foreach ($salary_deduction_info as $v_deduction) : ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-minus-circle text-danger" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= html_escape($v_deduction->deduction_label) ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #0f172a;">
                                        <?= display_money($v_deduction->deduction_value, $currency_symbol) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
                            <i class="fa fa-smile-o" style="color: #10b981; opacity: 0.8;"></i>
                            <div style="font-size: 13px; font-weight: 500; color: #475569;">No Deductions Applicable</div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">Full gross salary credited without deductions</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Total Salary Summary Banner -->
        <div class="summary-banner">
            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                <div class="col-sm-4 summary-item text-center">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('gross_salary') ?>
                    </div>
                    <div style="font-size: 20px; font-weight: 700;">
                        <?= display_money($gross_salary, $currency_symbol) ?>
                    </div>
                </div>
                <div class="col-sm-4 summary-item text-center">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('total_deduction') ?>
                    </div>
                    <div style="font-size: 20px; font-weight: 700; color: #fecaca;">
                        <?= display_money($total_deduction, $currency_symbol) ?>
                    </div>
                </div>
                <div class="col-sm-4 summary-item text-center">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('net_salary') ?> (Take Home)
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
