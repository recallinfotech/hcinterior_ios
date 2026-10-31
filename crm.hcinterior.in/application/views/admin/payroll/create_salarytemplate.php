<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('94', 'created');
$edited = can_action('94', 'edited');
$deleted = can_action('94', 'deleted');
$is_admin = ($this->session->userdata('user_type') == 1);
if (!empty($created) || !empty($edited) || $is_admin) {
?>

<style>
    .salary-template-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        margin-bottom: 25px;
        overflow: hidden;
    }
    .salary-template-card .card-header-custom {
        padding: 16px 22px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .salary-template-card .card-header-custom h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.3px;
        color: #ffffff;
    }
    .salary-template-card .card-body-custom {
        padding: 24px;
    }
    .component-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .component-panel:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }
    .component-panel .panel-heading-earnings {
        background: #ecfdf5;
        border-bottom: 1px solid #a7f3d0;
        color: #065f46;
        font-weight: 700;
        padding: 12px 18px;
        border-radius: 9px 9px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .component-panel .panel-heading-deductions {
        background: #fef2f2;
        border-bottom: 1px solid #fecaca;
        color: #991b1b;
        font-weight: 700;
        padding: 12px 18px;
        border-radius: 9px 9px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .component-panel .panel-body {
        padding: 18px;
    }
    .component-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: background 0.15s, border-color 0.15s;
    }
    .component-row:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .component-row .form-control {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
    }
    .component-row .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .btn-remove-row {
        color: #ef4444;
        background: #fee2e2;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-remove-row:hover {
        background: #dc2626;
        color: #ffffff;
    }
    .btn-add-row {
        font-weight: 600;
        border-radius: 6px;
        padding: 7px 16px;
        font-size: 12.5px;
        transition: all 0.2s;
    }
    .summary-card {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px 22px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .summary-card .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed #cbd5e1;
    }
    .summary-card .summary-item:last-child {
        border-bottom: none;
        padding-top: 14px;
    }
    .summary-card .summary-label {
        font-size: 13.5px;
        font-weight: 600;
        color: #475569;
    }
    .summary-card .summary-val {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }
    .summary-card .net-payable-row {
        background: #10b981;
        color: #ffffff;
        margin: 10px -22px -18px -22px;
        padding: 16px 22px;
        border-radius: 0 0 8px 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .summary-card .net-payable-row .net-label {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .summary-card .net-payable-row .net-val {
        font-size: 22px;
        font-weight: 800;
        font-family: inherit;
    }
    .quick-helper-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .quick-helper-box .helper-title {
        font-weight: 700;
        color: #1e40af;
        font-size: 13px;
        margin-bottom: 2px;
    }
    .quick-helper-box .helper-sub {
        font-size: 11.5px;
        color: #3b82f6;
    }
    .emp-info-pill {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 14px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12.5px;
    }
    .emp-info-pill .emp-meta {
        color: #334155;
    }
    .emp-info-pill .emp-meta strong {
        color: #0f172a;
    }
</style>

<div class="salary-template-card">
    <div class="card-header-custom">
        <div>
            <h3>
                <i class="fa fa-user-circle text-warning"></i>
                <span id="header_title_text">
                    Set Employee Salary Structure<?= !empty($selected_employee) ? ' &mdash; <span style="color:#38bdf8;">' . htmlspecialchars($selected_employee->fullname) . '</span>' : '' ?>
                </span>
            </h3>
            <small style="color: #94a3b8;">Set one-time monthly salary structure for an employee. Monthly payslips &amp; calculations will automatically factor in attendance, leaves, and deductions.</small>
        </div>
        <div>
            <a href="<?= base_url('admin/payroll/employee_salary_list') ?>" class="btn btn-sm btn-default" style="font-weight:600;">
                <i class="fa fa-users"></i> Back to Employee List
            </a>
        </div>
    </div>

    <div class="card-body-custom">
        <form data-parsley-validate="" novalidate="" role="form" enctype="multipart/form-data"
              action="<?php echo base_url() ?>admin/payroll/set_salary_details/<?php
              if (!empty($salary_template_info->salary_template_id)) {
                  echo $salary_template_info->salary_template_id;
              }
              ?>" method="post" id="form_salary_template">

            <!-- Hidden overtime_salary (default 0 as overtime is disabled) -->
            <input type="hidden" name="overtime_salary" value="<?= !empty($salary_template_info->overtime_salary) ? htmlspecialchars($salary_template_info->overtime_salary) : '0' ?>">

            <!-- 1. EMPLOYEE SELECTOR & BASIC SALARY -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                            <i class="fa fa-user text-primary"></i> Select Employee <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" id="select_employee_id" class="form-control select_box" required style="width: 100%;">
                            <?php if (!empty($selected_employee)) : ?>
                                <option value="<?= $selected_employee->user_id ?>"
                                    data-fullname="<?= htmlspecialchars($selected_employee->fullname) ?>"
                                    data-empid="<?= htmlspecialchars($selected_employee->employment_id ?: '') ?>"
                                    data-dept="<?= htmlspecialchars($selected_employee->deptname ?: 'General') ?>"
                                    data-desig="<?= htmlspecialchars($selected_employee->designations ?: 'Staff') ?>"
                                    data-hassalary="<?= !empty($salary_template_info->salary_template_id) ? '1' : '0' ?>"
                                    data-basicsalary="<?= !empty($salary_template_info->basic_salary) ? htmlspecialchars($salary_template_info->basic_salary) : '0' ?>"
                                    selected>
                                    <?= htmlspecialchars($selected_employee->fullname) ?> (<?= htmlspecialchars($selected_employee->employment_id ?: 'No ID') ?> - <?= htmlspecialchars($selected_employee->designations ?: 'Staff') ?>)
                                </option>
                            <?php else : ?>
                                <option value="">-- Choose Employee to Set Salary --</option>
                                <?php if (!empty($all_employees)) : foreach ($all_employees as $dept => $emp_list) : ?>
                                    <optgroup label="<?= htmlspecialchars($dept) ?>">
                                        <?php foreach ($emp_list as $emp) : ?>
                                            <option value="<?= $emp->user_id ?>"
                                                data-fullname="<?= htmlspecialchars($emp->fullname) ?>"
                                                data-empid="<?= htmlspecialchars($emp->employment_id ?: '') ?>"
                                                data-dept="<?= htmlspecialchars($emp->deptname ?: 'General') ?>"
                                                data-desig="<?= htmlspecialchars($emp->designations ?: 'Staff') ?>"
                                                data-hassalary="<?= !empty($emp->salary_template_id) ? '1' : '0' ?>"
                                                data-basicsalary="<?= !empty($emp->basic_salary) ? htmlspecialchars($emp->basic_salary) : '0' ?>"
                                                <?= (!empty($selected_user_id) && $selected_user_id == $emp->user_id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($emp->fullname) ?> (<?= htmlspecialchars($emp->employment_id ?: 'No ID') ?> - <?= htmlspecialchars($emp->designations ?: 'Staff') ?>) <?= !empty($emp->salary_template_id) ? '✓' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; endif; ?>
                            <?php endif; ?>
                        </select>

                        <!-- Employee Info Pill Badge -->
                        <div id="emp_info_pill" class="emp-info-pill" style="display: <?= !empty($selected_employee) ? 'flex' : 'none' ?>;">
                            <div class="emp-meta">
                                <strong><span id="pill_emp_name"><?= !empty($selected_employee) ? htmlspecialchars($selected_employee->fullname) : '' ?></span></strong>
                                (<span id="pill_emp_code"><?= !empty($selected_employee->employment_id) ? htmlspecialchars($selected_employee->employment_id) : 'HC' ?></span>) &bull;
                                <span id="pill_emp_dept"><?= !empty($selected_employee->deptname) ? htmlspecialchars($selected_employee->deptname) : 'General' ?></span> &bull;
                                <span id="pill_emp_desig"><?= !empty($selected_employee->designations) ? htmlspecialchars($selected_employee->designations) : 'Staff' ?></span>
                            </div>
                            <div id="pill_status_badge">
                                <?php if (!empty($salary_template_info->salary_template_id)) : ?>
                                    <span class="label label-success" style="font-size: 11px; padding: 4px 8px;">
                                        <i class="fa fa-check-circle"></i> Salary Configured
                                    </span>
                                <?php else : ?>
                                    <span class="label label-warning" style="font-size: 11px; padding: 4px 8px;">
                                        <i class="fa fa-info-circle"></i> New Salary Setup
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label style="font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                            <i class="fa fa-money text-success"></i> <?= lang('basic_salary') ?> (Monthly) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-addon" style="background:#f1f5f9; font-weight:bold;">₹</span>
                            <input type="number" step="0.01" data-parsley-type="number" name="basic_salary" id="main_basic_salary" value="<?php
                            if (!empty($salary_template_info->basic_salary)) {
                                echo htmlspecialchars($salary_template_info->basic_salary);
                            }
                            ?>" class="salary form-control" required style="height: 42px; font-size: 15px; font-weight: 700; color: #0f172a;"
                                   placeholder="e.g. 10000.00">
                        </div>
                        <small class="text-muted">Basic Salary is typically 50% of the total monthly CTC.</small>
                    </div>
                </div>
            </div>

            <!-- 2. HIGH CREATION FORMULA ASSISTANT -->
            <div class="quick-helper-box">
                <div>
                    <div class="helper-title">
                        <i class="fa fa-magic"></i> High Creation Standard CTC Formula Assistant
                    </div>
                    <div class="helper-sub">
                        Enter Monthly CTC to auto-fill Basic (50%), Variable (12%), HRA (40%), Conveyance (₹1000), Medical (₹1250), SPA balance &amp; EPF (12%)!
                    </div>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <div class="input-group" style="width: 180px;">
                        <span class="input-group-addon" style="padding: 4px 8px; font-weight:bold;">CTC ₹</span>
                        <input type="number" id="quick_ctc_input" placeholder="e.g. 20000" class="form-control input-sm" style="font-weight: 700;">
                    </div>
                    <button type="button" id="btn_apply_hc_formula" class="btn btn-sm btn-primary" style="font-weight: 700;">
                        <i class="fa fa-bolt"></i> Auto-Fill Formula
                    </button>
                </div>
            </div>

            <!-- 3. ALLOWANCES & DEDUCTIONS GRIDS -->
            <div class="row">
                <!-- ALLOWANCES (EARNINGS) -->
                <div class="col-md-6">
                    <div class="component-panel">
                        <div class="panel-heading-earnings">
                            <span>
                                <i class="fa fa-plus-circle text-success"></i> <?= lang('allowances') ?> (Earnings)
                            </span>
                            <span class="badge" id="badge_earnings_count" style="background: #059669;">0</span>
                        </div>
                        <div class="panel-body">
                            <div id="allowances_container">
                                <?php
                                $total_salary = 0;
                                if (!empty($salary_allowance_info)) : 
                                    foreach ($salary_allowance_info as $v_allowance_info) : 
                                ?>
                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="allowance_label[]"
                                                   value="<?php echo htmlspecialchars($v_allowance_info->allowance_label); ?>"
                                                   placeholder="Allowance Name (e.g. Variable, H.R.A.)" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]"
                                                       value="<?php echo htmlspecialchars($v_allowance_info->allowance_value); ?>"
                                                       class="salary form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                            <input type="hidden" name="salary_allowance_id[]" value="<?php echo htmlspecialchars($v_allowance_info->salary_allowance_id); ?>">
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF" title="Remove allowance">
                                                 <i class="fa fa-trash-o"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php $total_salary += floatval($v_allowance_info->allowance_value); ?>
                                <?php endforeach; ?>
                                <?php else : ?>
                                    <!-- Default High Creation Starter Allowances -->
                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="allowance_label[]" value="Variable" placeholder="Allowance Name" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="0.00" class="salary form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>

                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="allowance_label[]" value="H.R.A." placeholder="Allowance Name" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="0.00" class="salary form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>

                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="allowance_label[]" value="Conveyance Allowances" placeholder="Allowance Name" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="1000.00" class="salary form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>

                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="allowance_label[]" value="Medical Allowances" placeholder="Allowance Name" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="1250.00" class="salary form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>

                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="allowance_label[]" value="SPA" placeholder="Allowance Name" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="0.00" class="salary form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="button" id="btn_add_allowance" class="btn btn-default btn-add-row" style="color: #059669; border-color: #a7f3d0; background: #f0fdf4;">
                                <i class="fa fa-plus-circle"></i> Add Allowance Head
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DEDUCTIONS -->
                <div class="col-md-6">
                    <div class="component-panel">
                        <div class="panel-heading-deductions">
                            <span>
                                <i class="fa fa-minus-circle text-danger"></i> <?= lang('deductions') ?>
                            </span>
                            <span class="badge" id="badge_deductions_count" style="background: #dc2626;">0</span>
                        </div>
                        <div class="panel-body">
                            <div id="deductions_container">
                                <?php
                                $total_deduction = 0;
                                if (!empty($salary_deduction_info)) : 
                                    foreach ($salary_deduction_info as $v_deduction_info) : 
                                ?>
                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="deduction_label[]"
                                                   value="<?php echo htmlspecialchars($v_deduction_info->deduction_label); ?>"
                                                   placeholder="Deduction Name (e.g. EPF Employee)" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="deduction_value[]"
                                                       value="<?php echo htmlspecialchars($v_deduction_info->deduction_value); ?>"
                                                       class="deduction form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                            <input type="hidden" name="salary_deduction_id[]" value="<?php echo htmlspecialchars($v_deduction_info->salary_deduction_id); ?>">
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF_deduc" title="Remove deduction">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php $total_deduction += floatval($v_deduction_info->deduction_value); ?>
                                <?php endforeach; ?>
                                <?php else : ?>
                                    <!-- Default EPF Deduction Head -->
                                    <div class="component-row">
                                        <div style="flex: 2;">
                                            <input type="text" class="form-control" name="deduction_label[]" value="EPF Employee" placeholder="Deduction Name" required>
                                        </div>
                                        <div style="flex: 1.5;">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="padding: 4px 8px;">₹</span>
                                                <input type="number" step="0.01" data-parsley-type="number" name="deduction_value[]" value="0.00" class="deduction form-control" placeholder="Amount" required style="font-weight: 600;">
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn-remove-row remCF_deduc" title="Remove deduction"><i class="fa fa-trash-o"></i></button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="button" id="btn_add_deduction" class="btn btn-default btn-add-row" style="color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                                <i class="fa fa-plus-circle"></i> Add Deduction Head
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. REAL-TIME TOTAL SUMMARY CARD -->
            <div class="row" style="margin-top: 10px;">
                <div class="col-md-6 col-md-offset-6">
                    <div class="summary-card">
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa fa-money text-muted"></i> <?= lang('basic_salary') ?>:
                            </span>
                            <span class="summary-val" id="summary_display_basic">₹0.00</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa fa-gift text-muted"></i> Total <?= lang('allowances') ?>:
                            </span>
                            <span class="summary-val" style="color: #059669;" id="summary_display_allowance">+₹0.00</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa fa-calculator text-muted"></i> <?= lang('gross_salary') ?> (CTC):
                            </span>
                            <span class="summary-val" id="summary_display_gross">₹0.00</span>
                            <input type="hidden" name="" id="total" value="0">
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa fa-minus text-muted"></i> Total <?= lang('deductions') ?>:
                            </span>
                            <span class="summary-val" style="color: #dc2626;" id="summary_display_deduc">-₹0.00</span>
                            <input type="hidden" name="" id="deduc" value="0">
                        </div>
                        <div class="net-payable-row">
                            <span class="net-label">
                                <i class="fa fa-check-circle"></i> <?= lang('net_salary') ?> (In Hand):
                            </span>
                            <span class="net-val" id="summary_display_net">₹0.00</span>
                            <input type="hidden" name="" id="net_salary" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. ACTIONS TOOLBAR -->
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
                <a href="<?= base_url('admin/payroll/employee_salary_list') ?>" class="btn btn-default" style="font-weight: 600; padding: 8px 20px;">
                    <?= lang('cancel') ?>
                </a>
                <button type="submit" class="btn btn-primary" id="btn_save_salary" style="font-weight: 700; padding: 8px 25px; font-size: 14px; box-shadow: 0 4px 10px rgba(37,99,235,0.25);">
                    <i class="fa fa-check"></i> Save Employee Salary
                </button>
            </div>

        </form>
    </div>
</div>

<?php } else { ?>
    <div class="alert alert-danger">
        <i class="fa fa-ban"></i> You do not have permission to view or set employee salaries.
    </div>
<?php } ?>

<script type="text/javascript">
$(document).ready(function () {

    // 1. Employee Dropdown Change Event
    $("#select_employee_id").on("change", function () {
        var userId = $(this).val();
        if (!userId) {
            $("#emp_info_pill").hide();
            $("#header_title_text").text("Set Employee Salary Structure");
            return;
        }

        var opt = $(this).find("option:selected");
        var fullname = opt.data("fullname") || opt.text();
        var empid = opt.data("empid") || "HC";
        var dept = opt.data("dept") || "General";
        var desig = opt.data("desig") || "Staff";

        $("#pill_emp_name").text(fullname);
        $("#pill_emp_code").text(empid);
        $("#pill_emp_dept").text(dept);
        $("#pill_emp_desig").text(desig);
        $("#header_title_text").text("Set Salary for: " + fullname);
        $("#emp_info_pill").css("display", "flex");

        // AJAX Fetch Employee's existing salary details
        $.ajax({
            url: "<?= base_url('admin/payroll/get_employee_salary_ajax/') ?>" + userId,
            type: "GET",
            dataType: "json",
            beforeSend: function () {
                $("#pill_status_badge").html('<span class="label label-info"><i class="fa fa-spinner fa-spin"></i> Loading...</span>');
            },
            success: function (res) {
                if (res && res.success) {
                    if (res.has_salary) {
                        $("#pill_status_badge").html('<span class="label label-success" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-check-circle"></i> Salary Configured (Editing)</span>');
                    } else {
                        $("#pill_status_badge").html('<span class="label label-warning" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-info-circle"></i> New Salary Setup</span>');
                    }

                    // Populate Basic Salary
                    $("#main_basic_salary").val(res.basic_salary);

                    // Populate Quick CTC
                    if (res.ctc) {
                        $("#quick_ctc_input").val(res.ctc);
                    } else {
                        $("#quick_ctc_input").val('');
                    }

                    // Populate Allowances
                    if (res.allowances && res.allowances.length > 0) {
                        $("#allowances_container").empty();
                        $.each(res.allowances, function (i, item) {
                            var row = $(
                                '<div class="component-row">\n' +
                                '    <div style="flex: 2;">\n' +
                                '        <input type="text" class="form-control" name="allowance_label[]" value="' + item.label + '" placeholder="Allowance Name" required>\n' +
                                '    </div>\n' +
                                '    <div style="flex: 1.5;">\n' +
                                '        <div class="input-group">\n' +
                                '            <span class="input-group-addon" style="padding: 4px 8px;">₹</span>\n' +
                                '            <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="' + item.value + '" class="salary form-control" required style="font-weight: 600;">\n' +
                                '        </div>\n' +
                                (item.id ? '        <input type="hidden" name="salary_allowance_id[]" value="' + item.id + '">\n' : '') +
                                '    </div>\n' +
                                '    <div>\n' +
                                '        <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>\n' +
                                '    </div>\n' +
                                '</div>'
                            );
                            $("#allowances_container").append(row);
                        });
                    }

                    // Populate Deductions
                    if (res.deductions && res.deductions.length > 0) {
                        $("#deductions_container").empty();
                        $.each(res.deductions, function (i, item) {
                            var row = $(
                                '<div class="component-row">\n' +
                                '    <div style="flex: 2;">\n' +
                                '        <input type="text" class="form-control" name="deduction_label[]" value="' + item.label + '" placeholder="Deduction Name" required>\n' +
                                '    </div>\n' +
                                '    <div style="flex: 1.5;">\n' +
                                '        <div class="input-group">\n' +
                                '            <span class="input-group-addon" style="padding: 4px 8px;">₹</span>\n' +
                                '            <input type="number" step="0.01" data-parsley-type="number" name="deduction_value[]" value="' + item.value + '" class="deduction form-control" required style="font-weight: 600;">\n' +
                                '        </div>\n' +
                                (item.id ? '        <input type="hidden" name="salary_deduction_id[]" value="' + item.id + '">\n' : '') +
                                '    </div>\n' +
                                '    <div>\n' +
                                '        <button type="button" class="btn-remove-row remCF_deduc" title="Remove deduction"><i class="fa fa-trash-o"></i></button>\n' +
                                '    </div>\n' +
                                '</div>'
                            );
                            $("#deductions_container").append(row);
                        });
                    }

                    // Update URL without reload
                    if (window.history && window.history.pushState) {
                        var newUrl = "<?= base_url('admin/payroll/create_salarytemplate') ?>?user_id=" + userId;
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    }

                    calculate_payroll();
                }
            },
            error: function () {
                $("#pill_status_badge").html('<span class="label label-danger">Error Loading</span>');
            }
        });
    });

    // 2. Add Allowance Row
    $("#btn_add_allowance").on("click", function () {
        var new_row = $(
            '<div class="component-row">\n' +
            '    <div style="flex: 2;">\n' +
            '        <input type="text" class="form-control" name="allowance_label[]" placeholder="Allowance Name (e.g. Variable, HRA)" required>\n' +
            '    </div>\n' +
            '    <div style="flex: 1.5;">\n' +
            '        <div class="input-group">\n' +
            '            <span class="input-group-addon" style="padding: 4px 8px;">₹</span>\n' +
            '            <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="0.00" class="salary form-control" placeholder="Amount" required style="font-weight: 600;">\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div>\n' +
            '        <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>\n' +
            '    </div>\n' +
            '</div>'
        );
        $("#allowances_container").append(new_row);
        calculate_payroll();
    });

    // Remove Allowance Row
    $("#allowances_container").on('click', '.remCF', function () {
        $(this).closest('.component-row').remove();
        calculate_payroll();
    });

    // 3. Add Deduction Row
    $("#btn_add_deduction").on("click", function () {
        var new_row = $(
            '<div class="component-row">\n' +
            '    <div style="flex: 2;">\n' +
            '        <input type="text" class="form-control" name="deduction_label[]" placeholder="Deduction Name (e.g. EPF, TDS)" required>\n' +
            '    </div>\n' +
            '    <div style="flex: 1.5;">\n' +
            '        <div class="input-group">\n' +
            '            <span class="input-group-addon" style="padding: 4px 8px;">₹</span>\n' +
            '            <input type="number" step="0.01" data-parsley-type="number" name="deduction_value[]" value="0.00" class="deduction form-control" placeholder="Amount" required style="font-weight: 600;">\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div>\n' +
            '        <button type="button" class="btn-remove-row remCF_deduc" title="Remove deduction"><i class="fa fa-trash-o"></i></button>\n' +
            '    </div>\n' +
            '</div>'
        );
        $("#deductions_container").append(new_row);
        calculate_payroll();
    });

    // Remove Deduction Row
    $("#deductions_container").on('click', '.remCF_deduc', function () {
        $(this).closest('.component-row').remove();
        calculate_payroll();
    });

    // 4. Auto-Fill High Creation Formula Assistant
    $("#btn_apply_hc_formula").on("click", function () {
        var ctc = parseFloat($("#quick_ctc_input").val());
        if (!ctc || ctc <= 0) {
            alert("Please enter a valid CTC amount (e.g. 20000 or 43600).");
            $("#quick_ctc_input").focus();
            return;
        }

        // Standard HC Interior formulas:
        var basic = Math.round(ctc * 0.50 * 100) / 100;
        var variable = Math.round(ctc * 0.12 * 100) / 100;
        var hra = Math.round(basic * 0.40 * 100) / 100;
        var conveyance = 1000.00;
        var medical = 1250.00;
        var spa = Math.max(0, Math.round((ctc - (basic + variable + hra + conveyance + medical)) * 100) / 100);
        var epf = Math.min(1800, Math.round(basic * 0.12 * 100) / 100);

        // Set Basic Salary input
        $("#main_basic_salary").val(basic.toFixed(2));

        // Re-populate allowances container
        var hc_allowances = [
            { label: 'Variable', value: variable.toFixed(2) },
            { label: 'H.R.A.', value: hra.toFixed(2) },
            { label: 'Conveyance Allowances', value: conveyance.toFixed(2) },
            { label: 'Medical Allowances', value: medical.toFixed(2) },
            { label: 'SPA', value: spa.toFixed(2) }
        ];

        $("#allowances_container").empty();
        $.each(hc_allowances, function (i, item) {
            var row = $(
                '<div class="component-row">\n' +
                '    <div style="flex: 2;">\n' +
                '        <input type="text" class="form-control" name="allowance_label[]" value="' + item.label + '" required>\n' +
                '    </div>\n' +
                '    <div style="flex: 1.5;">\n' +
                '        <div class="input-group">\n' +
                '            <span class="input-group-addon" style="padding: 4px 8px;">₹</span>\n' +
                '            <input type="number" step="0.01" data-parsley-type="number" name="allowance_value[]" value="' + item.value + '" class="salary form-control" required style="font-weight: 600;">\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <div>\n' +
                '        <button type="button" class="btn-remove-row remCF" title="Remove allowance"><i class="fa fa-trash-o"></i></button>\n' +
                '    </div>\n' +
                '</div>'
            );
            $("#allowances_container").append(row);
        });

        // Set EPF in Deductions container
        var epf_found = false;
        $("#deductions_container .component-row").each(function () {
            var lbl = $(this).find('input[name="deduction_label[]"]').val();
            if (lbl && lbl.toUpperCase().indexOf('EPF') !== -1) {
                $(this).find('input[name="deduction_value[]"]').val(epf.toFixed(2));
                epf_found = true;
            }
        });
        if (!epf_found) {
            var epf_row = $(
                '<div class="component-row">\n' +
                '    <div style="flex: 2;">\n' +
                '        <input type="text" class="form-control" name="deduction_label[]" value="EPF Employee" required>\n' +
                '    </div>\n' +
                '    <div style="flex: 1.5;">\n' +
                '        <div class="input-group">\n' +
                '            <span class="input-group-addon" style="padding: 4px 8px;">₹</span>\n' +
                '            <input type="number" step="0.01" data-parsley-type="number" name="deduction_value[]" value="' + epf.toFixed(2) + '" class="deduction form-control" required style="font-weight: 600;">\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <div>\n' +
                '        <button type="button" class="btn-remove-row remCF_deduc" title="Remove deduction"><i class="fa fa-trash-o"></i></button>\n' +
                '    </div>\n' +
                '</div>'
            );
            $("#deductions_container").append(epf_row);
        }

        calculate_payroll();
    });

    // 5. Real-time Calculation Event Listeners
    $(document).on("input change blur", ".salary, .deduction, #main_basic_salary", function () {
        calculate_payroll();
    });

    // Form validation before submit: ensure employee selected
    $("#form_salary_template").on("submit", function (e) {
        var uid = $("#select_employee_id").val();
        if (!uid) {
            e.preventDefault();
            alert("Please select an employee before saving the salary structure.");
            $("#select_employee_id").focus();
            return false;
        }
    });

    // Initial calculation on page load
    calculate_payroll();
});

function calculate_payroll() {
    var basic = parseFloat($("#main_basic_salary").val()) || 0;
    var allowances_sum = 0;
    var deductions_sum = 0;

    $(".salary").each(function () {
        if ($(this).attr('id') !== 'main_basic_salary') {
            allowances_sum += parseFloat($(this).val()) || 0;
        }
    });

    $(".deduction").each(function () {
        deductions_sum += parseFloat($(this).val()) || 0;
    });

    var gross = basic + allowances_sum;
    var net = Math.max(0, gross - deductions_sum);

    // Update hidden inputs
    $("#total").val(gross.toFixed(2));
    $("#deduc").val(deductions_sum.toFixed(2));
    $("#net_salary").val(net.toFixed(2));

    // Update formatted UI labels
    $("#summary_display_basic").text('₹' + basic.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $("#summary_display_allowance").text('₹' + allowances_sum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $("#summary_display_gross").text('₹' + gross.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $("#summary_display_deduc").text('-₹' + deductions_sum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $("#summary_display_net").text('₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    // Update badges count
    var count_allow = $("#allowances_container .component-row").length;
    var count_deduc = $("#deductions_container .component-row").length;
    $("#badge_earnings_count").text(count_allow);
    $("#badge_deductions_count").text(count_deduc);
}
</script>