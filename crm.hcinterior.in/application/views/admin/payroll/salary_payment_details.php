<?php
$curency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
$currency_symbol = !empty($curency->symbol) ? $curency->symbol : '₹';

// Calculate Basic Salary & Salary Grade from details info
$salary_grade = '';
$basic_salary = 0;
if (!empty($salary_payment_details_info)) {
    foreach ($salary_payment_details_info as $v_det) {
        if ($v_det->salary_payment_details_label == 'Salary Grades' || $v_det->salary_payment_details_label == lang('salary_grade')) {
            $salary_grade = $v_det->salary_payment_details_value;
        }
        if ($v_det->salary_payment_details_label == 'Basic Salary' || $v_det->salary_payment_details_label == lang('basic_salary')) {
            $basic_salary = floatval($v_det->salary_payment_details_value);
        }
    }
}

// Calculate Allowances
$total_allowance = 0;
if (!empty($allowance_info)) {
    foreach ($allowance_info as $v_all) {
        $total_allowance += floatval($v_all->salary_payment_allowance_value);
    }
}
$gross_salary = $basic_salary + $total_allowance;

// Calculate Deductions
$total_deduction = 0;
if (!empty($deduction_info)) {
    foreach ($deduction_info as $v_ded) {
        $total_deduction += floatval($v_ded->salary_payment_deduction_value);
    }
}
$net_salary = $gross_salary - $total_deduction;

$fine_deduction = !empty($salary_payment_info->fine_deduction) ? floatval($salary_payment_info->fine_deduction) : 0;
$paid_amount = $net_salary - $fine_deduction;

// Payment Method name safe resolution
$method_name = '';
$payment_type_id = !empty($salary_payment_info->payment_type) ? $salary_payment_info->payment_type : (!empty($salary_payment_info->payment_method) ? $salary_payment_info->payment_method : null);
if (!empty($payment_type_id)) {
    if (is_numeric($payment_type_id)) {
        $pm = get_row('tbl_payment_methods', array('payment_methods_id' => $payment_type_id));
        if (!empty($pm->method_name)) {
            $method_name = $pm->method_name;
        }
    } else {
        $method_name = $payment_type_id;
    }
}
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
            <i class="fa fa-file-text-o" style="margin-right: 8px; color: #38bdf8;"></i>
            <?= lang('salary_payment_details') ?> &mdash; <span style="color: #93c5fd;"><?= html_escape($salary_payment_info->fullname) ?></span>
        </h4>
        <div style="display: flex; gap: 6px; align-items: center;">
            <a href="<?= base_url() ?>admin/payroll/send_payslip/<?= $salary_payment_info->salary_payment_id ?>"
               class="btn btn-success btn-xs" data-toggle="tooltip" title="<?= lang('send_email') ?>">
                <i class="fa fa-envelope-o"></i> Email
            </a>
            <span><?php echo btn_pdf('admin/payroll/salary_payment_details_pdf/' . $salary_payment_info->salary_payment_id); ?></span>
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
                        <p style="margin: 2px 0 0; font-size: 12px; color: #555;">Payment Receipt: <?= html_escape($salary_payment_info->fullname) ?> &mdash; <?= date('F Y', strtotime($salary_payment_info->payment_month)) ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Employee Info Card -->
        <div class="emp-profile-card">
            <?php if (!empty($salary_payment_info->avatar)): ?>
                <img src="<?php echo base_url() . $salary_payment_info->avatar; ?>" class="emp-profile-avatar" alt="Avatar">
            <?php else: ?>
                <img src="<?php echo base_url() ?>/img/user.png" class="emp-profile-avatar" alt="User">
            <?php endif; ?>

            <div style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #0f172a;">
                        <?= html_escape($salary_payment_info->fullname) ?>
                    </h3>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <?php if (!empty($salary_grade)): ?>
                            <span class="label label-primary" style="font-size: 11px; padding: 4px 8px;">
                                <?= html_escape($salary_grade) ?>
                            </span>
                        <?php endif; ?>
                        <span class="label label-info" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fa fa-calendar"></i> <?= date('F Y', strtotime($salary_payment_info->payment_month)) ?>
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 13px; color: #475569; margin-top: 6px;">
                    <div><strong>Emp ID:</strong> <span class="text-primary"><?= html_escape($salary_payment_info->employment_id) ?></span></div>
                    <div><strong>Dept:</strong> <?= html_escape($salary_payment_info->deptname) ?></div>
                    <div><strong>Designation:</strong> <?= html_escape($salary_payment_info->designations) ?></div>
                    <?php if (!empty($salary_payment_info->paid_date)): ?>
                        <div><strong>Paid Date:</strong> <?= date('d M Y, h:i A', strtotime($salary_payment_info->paid_date)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Overview Row -->
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
                    <div class="salary-stat-label" style="color: #166534;"><?= lang('paid_amount') ?></div>
                    <div class="salary-stat-value" style="color: #15803d;"><?= display_money($paid_amount, $currency_symbol) ?></div>
                </div>
            </div>
        </div>

        <!-- Breakdown Columns (Allowances & Deductions) -->
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
                                        <?= html_escape($v_all->salary_payment_allowance_label) ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #0f172a;">
                                        <?= display_money($v_all->salary_payment_allowance_value, $currency_symbol) ?>
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
                        <span class="badge" style="background: #dc2626; color: #fff;"><?= count(!empty($deduction_info) ? $deduction_info : []) ?></span>
                    </div>
                    <?php if (!empty($deduction_info)) : ?>
                        <table class="breakdown-table">
                            <tbody>
                            <?php foreach ($deduction_info as $v_ded) : ?>
                                <tr>
                                    <td style="color: #334155; font-weight: 500;">
                                        <i class="fa fa-minus-circle text-danger" style="font-size: 11px; margin-right: 6px;"></i>
                                        <?= html_escape($v_ded->salary_payment_deduction_label) ?>
                                    </td>
                                    <td class="text-right" style="font-weight: 600; color: #0f172a;">
                                        <?= display_money($v_ded->salary_payment_deduction_value, $currency_symbol) ?>
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
                            <i class="fa fa-smile-o" style="color: #10b981; font-size: 24px; opacity: 0.8; margin-bottom: 6px; display: block;"></i>
                            <div style="font-size: 13px; font-weight: 500; color: #475569;">No Deductions Applicable</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary & Net Paid Banner -->
        <div class="summary-banner">
            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                <div class="col-sm-3 summary-item text-center">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('gross_salary') ?>
                    </div>
                    <div style="font-size: 18px; font-weight: 700;">
                        <?= display_money($gross_salary, $currency_symbol) ?>
                    </div>
                </div>
                <div class="col-sm-3 summary-item text-center">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('total_deduction') ?>
                    </div>
                    <div style="font-size: 18px; font-weight: 700; color: #fecaca;">
                        <?= display_money($total_deduction, $currency_symbol) ?>
                    </div>
                </div>
                <?php if ($fine_deduction > 0): ?>
                    <div class="col-sm-2 summary-item text-center">
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                            <?= lang('fine_deduction') ?>
                        </div>
                        <div style="font-size: 18px; font-weight: 700; color: #fef08a;">
                            <?= display_money($fine_deduction, $currency_symbol) ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="<?php echo ($fine_deduction > 0) ? 'col-sm-4' : 'col-sm-6'; ?> summary-item text-center">
                    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 3px;">
                        <?= lang('paid_amount') ?> (Net Credited)
                    </div>
                    <div style="font-size: 24px; font-weight: 800; color: #a7f3d0;">
                        <?= display_money($paid_amount, $currency_symbol) ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($method_name) || !empty($salary_payment_info->comments)): ?>
                <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.2); font-size: 12px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <?php if (!empty($method_name)): ?>
                        <div>
                            <strong><?= lang('payment_method') ?>:</strong>
                            <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff;"><?= html_escape($method_name) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($salary_payment_info->comments)): ?>
                        <div>
                            <strong><?= lang('comments') ?>:</strong> <?= html_escape($salary_payment_info->comments) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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