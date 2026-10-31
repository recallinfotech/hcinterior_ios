<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="panel panel-custom">
    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div class="panel-title" style="margin: 0;">
            <strong><i class="fa fa-cogs"></i> <?= lang('manage_salary_details') ?></strong>
        </div>
        <div>
            <a href="<?= base_url('admin/payroll/employee_salary_list') ?>" class="btn btn-xs btn-primary" style="font-weight: 600; color: #ffffff !important;">
                <i class="fa fa-users"></i> <?= lang('employee_salary_list') ?>
            </a>
            <a href="<?= base_url('admin/payroll/salary_template') ?>" class="btn btn-xs btn-info" style="font-weight: 600; margin-left: 5px; color: #ffffff !important;">
                <i class="fa fa-list"></i> <?= lang('salary_template') ?>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="panel-body" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 20px;">
        <form id="form" role="form" enctype="multipart/form-data"
              action="<?php echo base_url() ?>admin/payroll/manage_salary_details" method="post"
              class="form-horizontal">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="field-1" class="col-sm-3 control-label" style="font-weight: 600; font-size: 13px;">
                    <?= lang('select_department') ?> <span class="text-danger">*</span>
                </label>
                <div class="col-sm-5">
                    <select name="departments_id" class="form-control select_box" required style="width: 100%;">
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
                <div class="col-sm-2">
                    <button type="submit" id="sbtn" value="1" name="flag" class="btn btn-primary btn-block" style="font-weight: 600;">
                        <i class="fa fa-arrow-right"></i> <?= lang('go') ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Employee Salary Assignment Table -->
    <?php if (!empty($flag)): ?>
        <form id="form_validation" role="form" enctype="multipart/form-data"
              action="<?php echo base_url() ?>admin/payroll/save_salary_details" method="post">
            <div class="panel-body" style="padding: 20px;">
                <table class="table table-striped table-hover" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                    <thead style="background: #f1f5f9;">
                    <tr>
                        <th style="padding: 12px 15px; font-weight: 600; color: #334155; width: 35%;">
                            <?= lang('employee') . ' ' . lang('name') ?>
                        </th>
                        <th style="padding: 12px 15px; font-weight: 600; color: #334155; width: 25%;">
                            <?= lang('designation') ?>
                        </th>
                        <th style="padding: 12px 15px; font-weight: 600; color: #334155; width: 40%;">
                            <?= lang('monthly') . ' ' . lang('salary_grade') ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $hh = '';
                    if (!empty($employee_info)): foreach ($employee_info as $key => $v_emp_info):
                        if (!empty($v_emp_info)): foreach ($v_emp_info as $v_employee):
                            $hh = true;
                            ?>
                            <tr>
                                <td style="padding: 12px 15px; vertical-align: middle;">
                                    <input type="hidden" name="user_id[]" value="<?php echo $v_employee->user_id ?>">
                                    <div style="font-weight: 600; color: #0f172a;">
                                        <i class="fa fa-user-circle text-muted" style="margin-right: 6px;"></i>
                                        <?php echo html_escape($v_employee->fullname); ?>
                                    </div>
                                    <?php if (!empty($v_employee->employment_id)): ?>
                                        <div style="font-size: 11px; color: #64748b; margin-left: 20px;">
                                            ID: <span class="text-primary"><?php echo html_escape($v_employee->employment_id); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 15px; vertical-align: middle;">
                                    <span class="label label-default" style="font-size: 11px; padding: 4px 8px;">
                                        <?php echo html_escape($v_employee->designations); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 15px; vertical-align: middle;">
                                    <input type="hidden" name="monthly_status[]" value="<?php echo $v_employee->user_id ?>">
                                    <select name="salary_template_id[]" class="form-control select_box" style="width: 100%;">
                                        <option value=""><?= lang('select_monthly_grade') ?>...</option>
                                        <?php if (!empty($salary_grade)) : foreach ($salary_grade as $v_salary_info) : ?>
                                            <option value="<?php echo $v_salary_info->salary_template_id ?>"
                                                <?php
                                                if (!empty($salary_grade_info)) {
                                                    foreach ($salary_grade_info as $v_grade_salary_info) {
                                                        foreach ($v_grade_salary_info as $v_gsalary_info) {
                                                            if (!empty($v_gsalary_info) && $v_employee->user_id == $v_gsalary_info->user_id) {
                                                                echo $v_salary_info->salary_template_id == $v_gsalary_info->salary_template_id ? 'selected ' : '';
                                                            }
                                                        }
                                                    }
                                                }
                                                ?> >
                                                <?php echo html_escape($v_salary_info->salary_grade); ?> (Basic: ₹<?php echo number_format($v_salary_info->basic_salary, 2); ?>)
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; endif; endforeach; endif; ?>

                    <?php if (empty($hh)) { ?>
                        <tr>
                            <td colspan="3" class="text-center" style="padding: 30px; color: #94a3b8;">
                                <i class="fa fa-info-circle fa-2x" style="opacity: 0.5; margin-bottom: 8px;"></i>
                                <div><?= lang('nothing_to_display') ?></div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <?php if (!empty($hh)) { ?>
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-sm-8"></div>
                        <div class="col-sm-4 text-right">
                            <button id="salery_btn" type="submit" class="btn btn-primary btn-block" style="padding: 10px; font-weight: 600; font-size: 14px;">
                                <i class="fa fa-check-circle"></i> <?= lang('update') ?> Salary Assignment
                            </button>
                        </div>
                    </div>
                <?php } ?>

                <!-- Hidden values when update -->
                <input type="hidden" name="departments_id" value="<?php echo $departments_id ?>"/>
                <?php
                if (!empty($salary_grade_info)) {
                    foreach ($salary_grade_info as $v_grade_salary_info) {
                        foreach ($v_grade_salary_info as $v_gsalary_info) {
                            if (!empty($v_gsalary_info)) {
                                ?>
                                <input type="hidden" name="payroll_id[]" value="<?php echo $v_gsalary_info->payroll_id ?>"/>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </div>
        </form>
    <?php endif; ?>
</div>
