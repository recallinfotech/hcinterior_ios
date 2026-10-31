<?php include_once 'asset/admin-ajax.php';
$created = can_action('72', 'created');
$edited = can_action('72', 'edited');
$deleted = can_action('72', 'deleted');
$office_hours = config_item('office_hours');
$current_month_name = date('F Y');

$cl_cat_id = 1;
$el_cat_id = 2;
$sl_cat_id = 3;
if (!empty($all_categories)) {
    foreach ($all_categories as $ac) {
        $ac_code = strtoupper(trim($ac->leave_category));
        if ($ac_code === 'CL') $cl_cat_id = $ac->leave_category_id;
        if ($ac_code === 'EL') $el_cat_id = $ac->leave_category_id;
        if ($ac_code === 'SL') $sl_cat_id = $ac->leave_category_id;
    }
}
?>

<style>
.leave-stat-card {
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 20px;
    color: #fff;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.leave-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12);
}
.leave-stat-card .stat-info h3 {
    margin: 0 0 5px 0;
    font-size: 26px;
    font-weight: 700;
}
.leave-stat-card .stat-info span {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 600;
    opacity: 0.95;
}
.leave-stat-card .stat-icon {
    font-size: 34px;
    opacity: 0.35;
}
.bg-grad-staff { background: linear-gradient(135deg, #1f4037, #99f2c8); color: #fff; }
.bg-grad-el { background: linear-gradient(135deg, #11998e, #38ef7d); color: #fff; }
.bg-grad-sl { background: linear-gradient(135deg, #8a2387, #e94057); color: #fff; }
.bg-grad-tot { background: linear-gradient(135deg, #2b5876, #4e4376); color: #fff; }

.badge-el {
    background-color: #27ae60;
    color: #fff;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
}
.badge-sl {
    background-color: #9b59b6;
    color: #fff;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
}
.badge-cl {
    background-color: #3498db;
    color: #fff;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
}
.leave-balance-chip {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.chip-positive {
    background: #e8f8f5;
    color: #117a65;
    border: 1px solid #a3e4d7;
}
.chip-zero {
    background: #fef9e7;
    color: #b7950b;
    border: 1px solid #f9e79f;
}
.chip-negative {
    background: #fdedec;
    color: #922b21;
    border: 1px solid #f5b7b1;
}
.sub-tabs-pills > li.active > a, 
.sub-tabs-pills > li.active > a:focus, 
.sub-tabs-pills > li.active > a:hover {
    background-color: #5d9cec !important;
    color: #fff !important;
    font-weight: 600;
}
.sub-tabs-pills > li > a {
    border-radius: 4px;
    padding: 8px 18px;
    color: #555;
    background: #f4f5f7;
    margin-right: 6px;
    font-weight: 500;
}
.sub-tabs-pills > li > a:hover {
    background: #e9eaec;
}
</style>

<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="mt-lg">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="pending_approval <?= $active == 1 ? 'active' : '' ?>">
                <a href="<?= base_url('admin/leave_management') ?>"><?= lang('pending') . ' ' . lang('approval') ?></a>
            </li>

            <li class="my_leave <?= $active == 2 ? 'active' : '' ?>">
                <a href="<?= base_url('admin/leave_management/my_leave') ?>"><?= lang('my_leave') ?></a>
            </li>

            <?php if (!empty(admin_head())) { ?>
            <li class="all_leave <?= $active == 3 ? 'active' : '' ?>">
                <a href="<?= base_url('admin/leave_management/all_leave') ?>"><?= lang('all_leave') ?></a>
            </li>
            <?php } ?>
            
            <li class="<?= $active == 4 ? 'active' : '' ?>">
                <a href="<?= base_url('admin/leave_management/leave_report') ?>"><?= lang('leave_report') ?></a>
            </li>

            <?php if (!empty(admin_head())) { ?>
            <li class="active">
                <a href="<?= base_url('admin/leave_management/leave_credits') ?>" style="border-top: 3px solid #27ae60; font-weight: 600;">
                    <i class="fa fa-calendar-plus-o text-success"></i> Earn &amp; Sick Leaves (Monthly Credit)
                </a>
            </li>
            <?php } ?>

            <li class="pull-right">
                <a href="<?= base_url() ?>admin/leave_management/apply_leave" class="bg-info" data-toggle="modal"
                    data-placement="top" data-target="#myModal_extra_lg">
                    <i class="fa fa-plus "></i> <?= lang('apply') . ' ' . lang('leave') ?>
                </a>
            </li>
        </ul>

        <div class="tab-content" style="border: 0; padding: 20px 15px; background: #fff;">
            
            <!-- TOP KPI STATS CARDS -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="leave-stat-card bg-grad-staff">
                        <div class="stat-info">
                            <h3><?= $total_staff ?></h3>
                            <span>Active Staff</span>
                        </div>
                        <div class="stat-icon"><i class="fa fa-users"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="leave-stat-card bg-grad-el">
                        <div class="stat-info">
                            <h3><?= number_format($cur_month_el_credited, 2) ?> <small style="color:#fff;font-size:13px;">Days</small></h3>
                            <span>Earn Leave (<?= $current_month_name ?>)</span>
                        </div>
                        <div class="stat-icon"><i class="fa fa-calendar-check-o"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="leave-stat-card bg-grad-sl">
                        <div class="stat-info">
                            <h3><?= number_format($cur_month_sl_credited, 2) ?> <small style="color:#fff;font-size:13px;">Days</small></h3>
                            <span>Sick Leave (<?= $current_month_name ?>)</span>
                        </div>
                        <div class="stat-icon"><i class="fa fa-heartbeat"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="leave-stat-card bg-grad-tot">
                        <div class="stat-info">
                            <h3><?= number_format($ytd_el_credited + $ytd_sl_credited, 2) ?> <small style="color:#fff;font-size:13px;">Days</small></h3>
                            <span>Total YTD Credits (<?= date('Y') ?>)</span>
                        </div>
                        <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR & SUB-TABS -->
            <div class="row" style="margin-bottom: 18px; margin-top: 5px;">
                <div class="col-md-7 col-sm-12">
                    <ul class="nav nav-pills sub-tabs-pills">
                        <li class="active">
                            <a href="#subtab_balances" data-toggle="tab">
                                <i class="fa fa-list"></i> Employee Leave Balances
                            </a>
                        </li>
                        <li>
                            <a href="#subtab_batch_setup" data-toggle="tab">
                                <i class="fa fa-sliders text-warning"></i> ⚡ Mid-Year Batch Setup (All Staff)
                            </a>
                        </li>
                        <li>
                            <a href="#subtab_history" data-toggle="tab">
                                <i class="fa fa-history"></i> Leave Credit History Logs
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-5 col-sm-12 text-right" style="margin-top: 5px;">
                    <?php if (!empty(admin_head())) { ?>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalSetBalance" style="margin-right: 5px; font-weight:600;">
                        <i class="fa fa-sliders"></i> Set Employee Balance (Mid-Year)
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalSingleCredit" style="margin-right: 5px;">
                        <i class="fa fa-plus-circle"></i> Add Credit
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalBulkCredit">
                        <i class="fa fa-bolt"></i> Run Monthly Accrual
                    </button>
                    <?php } ?>
                </div>
            </div>

            <!-- SUB-TAB CONTENTS -->
            <div class="tab-content" style="border: 0; padding: 0;">
                
                <!-- SUB-TAB 1: EMPLOYEE LEAVE BALANCES -->
                <div class="tab-pane active" id="subtab_balances">
                    <div class="panel panel-default" style="border: 1px solid #ebebeb; border-radius: 6px;">
                        <div class="panel-heading" style="background:#fafafa; border-bottom:1px solid #ebebeb; padding: 12px 15px;">
                            <h4 class="panel-title" style="font-weight: 600; color:#333;">
                                <i class="fa fa-users text-primary"></i> Current Employee Leave Balances (Year <?= date('Y') ?>)
                                <small class="text-muted" style="margin-left: 10px;">Monthly added Earn Leaves (EL) &amp; Sick Leaves (SL) update here automatically.</small>
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table_employee_leave_balances" width="100%">
                                    <thead>
                                        <tr style="background:#f9f9f9;">
                                            <th style="width: 250px;">Employee</th>
                                            <th>Designation / Dept</th>
                                            <th style="text-align:center;">Earn Leave (EL)<br><small class="text-muted">Credited | Taken | <strong>Balance</strong></small></th>
                                            <th style="text-align:center;">Sick Leave (SL)<br><small class="text-muted">Credited | Taken | <strong>Balance</strong></small></th>
                                            <th style="text-align:center;">Casual Leave (CL)<br><small class="text-muted">Quota | Taken | <strong>Balance</strong></small></th>
                                            <th style="text-align:center;">Total Available Balance</th>
                                            <?php if (!empty(admin_head())) { ?>
                                            <th style="text-align:center; width: 180px;">Actions</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($employees)) {
                                            foreach ($employees as $emp) {
                                                $el_info = !empty($emp->balances['EL']) ? $emp->balances['EL'] : array('credited' => 0, 'taken' => 0, 'balance' => 0, 'is_configured' => false);
                                                $sl_info = !empty($emp->balances['SL']) ? $emp->balances['SL'] : array('credited' => 0, 'taken' => 0, 'balance' => 0, 'is_configured' => false);
                                                $cl_info = !empty($emp->balances['CL']) ? $emp->balances['CL'] : array('allocated' => 12, 'taken' => 0, 'balance' => 12, 'is_configured' => false);
                                                
                                                $el_bal = floatval($el_info['balance']);
                                                $sl_bal = floatval($sl_info['balance']);
                                                $cl_bal = floatval($cl_info['balance']);
                                                $total_bal = $el_bal + $sl_bal + $cl_bal;

                                                $el_chip_class = ($el_bal > 0) ? 'chip-positive' : (($el_bal == 0) ? 'chip-zero' : 'chip-negative');
                                                $sl_chip_class = ($sl_bal > 0) ? 'chip-positive' : (($sl_bal == 0) ? 'chip-zero' : 'chip-negative');
                                                $cl_chip_class = ($cl_bal > 0) ? 'chip-positive' : (($cl_bal == 0) ? 'chip-zero' : 'chip-negative');
                                                $tot_chip_class = ($total_bal > 0) ? 'chip-positive' : (($total_bal == 0) ? 'chip-zero' : 'chip-negative');
                                        ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex; align-items:center;">
                                                    <img src="<?= !empty($emp->avatar) ? base_url($emp->avatar) : base_url('uploads/default_avatar.jpg') ?>" 
                                                         alt="" style="width:36px; height:36px; border-radius:50%; margin-right:12px; object-fit:cover; border:1px solid #ddd;">
                                                    <div>
                                                        <strong><?= htmlspecialchars($emp->fullname) ?></strong>
                                                        <div style="font-size: 11px; color: #888;">
                                                            Code: <strong><?= !empty($emp->emp_code) ? $emp->emp_code : (!empty($emp->employment_id) ? $emp->employment_id : 'EMP-' . $emp->user_id) ?></strong>
                                                            <?php if (!empty($emp->joining_date)) : ?>
                                                                &bull; Joined: <?= date('d M Y', strtotime($emp->joining_date)) ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div><strong><?= !empty($emp->designations) ? $emp->designations : 'Staff' ?></strong></div>
                                                <small class="text-muted"><?= !empty($emp->deptname) ? $emp->deptname : '-' ?></small>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="badge-el" style="margin-right: 4px;">EL</span>
                                                <small><?= number_format($el_info['credited'], 1) ?> cr / <?= number_format($el_info['taken'], 1) ?> taken</small>
                                                <?php if (!empty($el_info['is_configured'])) : ?>
                                                    <span class="label label-success" style="font-size:9px;" title="Manually Set / Configured"><i class="fa fa-check"></i> Set</span>
                                                <?php else : ?>
                                                    <span class="label label-default" style="font-size:9px;" title="Default / Unconfigured Quota">Default</span>
                                                <?php endif; ?>
                                                <br>
                                                <span class="leave-balance-chip <?= $el_chip_class ?>" style="margin-top: 4px;">
                                                    <?= number_format($el_bal, 2) ?> Days Bal
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="badge-sl" style="margin-right: 4px;">SL</span>
                                                <small><?= number_format($sl_info['credited'], 1) ?> cr / <?= number_format($sl_info['taken'], 1) ?> taken</small>
                                                <?php if (!empty($sl_info['is_configured'])) : ?>
                                                    <span class="label label-success" style="font-size:9px;" title="Manually Set / Configured"><i class="fa fa-check"></i> Set</span>
                                                <?php else : ?>
                                                    <span class="label label-default" style="font-size:9px;" title="Default / Unconfigured Quota">Default</span>
                                                <?php endif; ?>
                                                <br>
                                                <span class="leave-balance-chip <?= $sl_chip_class ?>" style="margin-top: 4px;">
                                                    <?= number_format($sl_bal, 2) ?> Days Bal
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <span class="badge-cl" style="margin-right: 4px;">CL</span>
                                                <small><?= number_format($cl_info['allocated'], 1) ?> qta / <?= number_format($cl_info['taken'], 1) ?> taken</small>
                                                <?php if (!empty($cl_info['is_configured'])) : ?>
                                                    <span class="label label-success" style="font-size:9px;" title="Manually Set / Configured"><i class="fa fa-check"></i> Set</span>
                                                <?php else : ?>
                                                    <span class="label label-default" style="font-size:9px;" title="Default / Unconfigured Quota">Default</span>
                                                <?php endif; ?>
                                                <br>
                                                <span class="leave-balance-chip <?= $cl_chip_class ?>" style="margin-top: 4px;">
                                                    <?= number_format($cl_bal, 2) ?> Days Bal
                                                </span>
                                            </td>
                                            <td style="text-align:center;">
                                                <strong class="leave-balance-chip <?= $tot_chip_class ?>" style="font-size: 13px; padding: 5px 12px;">
                                                    <?= number_format($total_bal, 2) ?> Days
                                                </strong>
                                            </td>
                                            <?php if (!empty(admin_head())) { ?>
                                            <td style="text-align:center; white-space:nowrap;">
                                                <button type="button" class="btn btn-xs btn-warning btn-set-balance" 
                                                        data-user-id="<?= $emp->user_id ?>" 
                                                        data-user-name="<?= htmlspecialchars($emp->fullname) ?>"
                                                        data-joining="<?= !empty($emp->joining_date) ? $emp->joining_date : '' ?>"
                                                        data-cl="<?= $cl_bal ?>"
                                                        data-sl="<?= $sl_bal ?>"
                                                        data-el="<?= $el_bal ?>"
                                                        title="Set Remaining / Mid-Year Balance">
                                                    <i class="fa fa-sliders"></i> Set Balance
                                                </button>
                                                <button type="button" class="btn btn-xs btn-primary btn-credit-user" 
                                                        data-user-id="<?= $emp->user_id ?>" 
                                                        data-user-name="<?= htmlspecialchars($emp->fullname) ?>"
                                                        title="Credit Leave to this user">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                                <a href="<?= base_url('admin/attendance/time_history?user_id=' . $emp->user_id) ?>" 
                                                   class="btn btn-xs btn-default" title="View Time History & Attendance Calendar">
                                                    <i class="fa fa-calendar"></i>
                                                </a>
                                            </td>
                                            <?php } ?>
                                        </tr>
                                        <?php } 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUB-TAB 2: MID-YEAR & NEW JOINING BATCH SETUP -->
                <div class="tab-pane" id="subtab_batch_setup">
                    <div class="panel panel-default" style="border: 1px solid #ebebeb; border-radius: 6px;">
                        <div class="panel-heading" style="background:#fafafa; border-bottom:1px solid #ebebeb; padding: 12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <div>
                                <h4 class="panel-title" style="font-weight: 700; color:#1e293b; margin:0;">
                                    <i class="fa fa-sliders text-warning"></i> ⚡ Mid-Year &amp; New Joining Leave Setup Sheet (<?= date('Y') ?>)
                                </h4>
                                <small class="text-muted">Directly enter or adjust remaining leave days for all staff. Click 'Save All Balances' to commit.</small>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button type="button" class="btn btn-default btn-sm" id="btn_prorate_all_unconfigured" title="Auto Pro-Rate leaves for all staff whose balance is not yet set">
                                    <i class="fa fa-magic text-warning"></i> Auto Pro-Rate Unset Staff
                                </button>
                                <button type="submit" form="form_batch_balances" id="btn_submit_batch_balances" class="btn btn-success btn-sm" style="font-weight: 700; padding: 6px 16px;">
                                    <i class="fa fa-save"></i> Save All Balances
                                </button>
                            </div>
                        </div>
                        <div class="panel-body" style="padding: 15px;">
                            <form id="form_batch_balances" action="<?= base_url('admin/leave_management/save_batch_leave_balances') ?>" method="post">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="table_batch_leave_setup" width="100%">
                                        <thead>
                                            <tr style="background:#f1f5f9; font-size: 12px; text-transform: uppercase;">
                                                <th style="width: 40px; text-align:center;">#</th>
                                                <th style="min-width: 220px;">Employee</th>
                                                <th style="min-width: 120px;">Joining Date</th>
                                                <th style="text-align:center; min-width: 110px;">
                                                    <span class="badge-cl">CL</span> Casual Leave
                                                    <br><small class="text-muted">Remaining Days</small>
                                                </th>
                                                <th style="text-align:center; min-width: 110px;">
                                                    <span class="badge-sl">SL</span> Sick Leave
                                                    <br><small class="text-muted">Remaining Days</small>
                                                </th>
                                                <th style="text-align:center; min-width: 110px;">
                                                    <span class="badge-el">EL</span> Earn Leave
                                                    <br><small class="text-muted">Remaining Days</small>
                                                </th>
                                                <th style="text-align:center; min-width: 90px;">Status</th>
                                                <th style="text-align:center; min-width: 130px;">Quick Row Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($employees)) {
                                                $b_idx = 1;
                                                foreach ($employees as $emp) {
                                                    $el_info = !empty($emp->balances['EL']) ? $emp->balances['EL'] : array('credited' => 0, 'taken' => 0, 'balance' => 0, 'is_configured' => false);
                                                    $sl_info = !empty($emp->balances['SL']) ? $emp->balances['SL'] : array('credited' => 0, 'taken' => 0, 'balance' => 0, 'is_configured' => false);
                                                    $cl_info = !empty($emp->balances['CL']) ? $emp->balances['CL'] : array('allocated' => 12, 'taken' => 0, 'balance' => 12, 'is_configured' => false);
                                                    
                                                    $is_conf = (!empty($cl_info['is_configured']) || !empty($el_info['is_configured']) || !empty($sl_info['is_configured']));
                                                    $j_date = !empty($emp->joining_date) ? $emp->joining_date : '';
                                            ?>
                                            <tr id="batch_row_<?= $emp->user_id ?>" data-joining="<?= $j_date ?>" data-configured="<?= $is_conf ? '1' : '0' ?>" data-user-name="<?= htmlspecialchars($emp->fullname) ?>">
                                                <td style="text-align:center; vertical-align:middle;"><?= $b_idx++ ?></td>
                                                <td style="vertical-align:middle;">
                                                    <strong><?= htmlspecialchars($emp->fullname) ?></strong>
                                                    <div style="font-size: 11px; color: #64748b;">
                                                        <?= !empty($emp->emp_code) ? $emp->emp_code : (!empty($emp->employment_id) ? $emp->employment_id : 'EMP-' . $emp->user_id) ?> &bull; <?= !empty($emp->designations) ? $emp->designations : 'Staff' ?>
                                                    </div>
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    <?php if (!empty($j_date)) : ?>
                                                        <span style="font-size: 12px; font-weight:600; color:#334155;"><i class="fa fa-calendar-o text-muted"></i> <?= date('d M Y', strtotime($j_date)) ?></span>
                                                    <?php else : ?>
                                                        <span class="text-muted" style="font-size: 11px;">--</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    <input type="number" step="0.5" min="0" max="45" 
                                                           name="batch_balances[<?= $emp->user_id ?>][<?= $cl_cat_id ?>]" 
                                                           id="batch_cl_<?= $emp->user_id ?>"
                                                           class="form-control input-sm text-center batch-input" 
                                                           value="<?= floatval($cl_info['balance']) ?>" 
                                                           style="width: 80px; display:inline-block; font-weight: 700; color:#2980b9;">
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    <input type="number" step="0.5" min="0" max="45" 
                                                           name="batch_balances[<?= $emp->user_id ?>][<?= $sl_cat_id ?>]" 
                                                           id="batch_sl_<?= $emp->user_id ?>"
                                                           class="form-control input-sm text-center batch-input" 
                                                           value="<?= floatval($sl_info['balance']) ?>" 
                                                           style="width: 80px; display:inline-block; font-weight: 700; color:#8e44ad;">
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    <input type="number" step="0.5" min="0" max="45" 
                                                           name="batch_balances[<?= $emp->user_id ?>][<?= $el_cat_id ?>]" 
                                                           id="batch_el_<?= $emp->user_id ?>"
                                                           class="form-control input-sm text-center batch-input" 
                                                           value="<?= floatval($el_info['balance']) ?>" 
                                                           style="width: 80px; display:inline-block; font-weight: 700; color:#27ae60;">
                                                </td>
                                                <td style="text-align:center; vertical-align:middle;">
                                                    <?php if ($is_conf) : ?>
                                                        <span class="label label-success" style="font-size: 10px; font-weight:600;"><i class="fa fa-check"></i> Set</span>
                                                    <?php else : ?>
                                                        <span class="label label-warning" style="font-size: 10px; font-weight:600;"><i class="fa fa-clock-o"></i> Default</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center; vertical-align:middle; white-space:nowrap;">
                                                    <button type="button" class="btn btn-xs btn-info btn-prorate-row" 
                                                            data-user-id="<?= $emp->user_id ?>" 
                                                            data-joining="<?= $j_date ?>" 
                                                            title="Auto Pro-Rate leaves for remaining months based on joining date">
                                                        <i class="fa fa-calculator"></i> Pro-Rate
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-success btn-save-row" 
                                                            data-user-id="<?= $emp->user_id ?>" 
                                                            title="Save only this employee's balance">
                                                        <i class="fa fa-check"></i> Save
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- SUB-TAB 3: CREDIT HISTORY LOGS -->
                <div class="tab-pane" id="subtab_history">
                    <div class="panel panel-default" style="border: 1px solid #ebebeb; border-radius: 6px;">
                        <div class="panel-heading" style="background:#fafafa; border-bottom:1px solid #ebebeb; padding: 12px 15px;">
                            <h4 class="panel-title" style="font-weight: 600; color:#333;">
                                <i class="fa fa-history text-info"></i> Leave Accrual &amp; Credit Transaction History
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table_credit_history" width="100%">
                                    <thead>
                                        <tr style="background:#f9f9f9;">
                                            <th># ID</th>
                                            <th>Date &amp; Time</th>
                                            <th>Employee</th>
                                            <th>Leave Type</th>
                                            <th>For Month</th>
                                            <th>Credits Added</th>
                                            <th>Accrual Method</th>
                                            <th>Added By</th>
                                            <th>Remarks</th>
                                            <?php if (!empty(admin_head())) { ?>
                                            <th style="width: 70px; text-align:center;">Action</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($credit_history)) {
                                            foreach ($credit_history as $cr) {
                                                $badge_class = ($cr->leave_type == 'EL') ? 'badge-el' : (($cr->leave_type == 'SL') ? 'badge-sl' : 'badge-cl');
                                                $type_label = ($cr->added_type == 'monthly_auto') ? '<span class="label label-success">Monthly Bulk</span>' : (($cr->added_type == 'monthly_manual') ? '<span class="label label-info">Manual Add</span>' : '<span class="label label-warning">Adjustment</span>');
                                        ?>
                                        <tr id="row_credit_<?= $cr->leave_credit_id ?>">
                                            <td>#<?= $cr->leave_credit_id ?></td>
                                            <td><?= date('d M Y, h:i A', strtotime($cr->created_at)) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($cr->employee_name) ?></strong>
                                                <?php if (!empty($cr->emp_code)) { ?>
                                                <small class="text-muted">(<?= $cr->emp_code ?>)</small>
                                                <?php } ?>
                                            </td>
                                            <td><span class="<?= $badge_class ?>"><?= $cr->leave_type ?> (<?= ($cr->leave_type == 'EL') ? 'Earn Leave' : (($cr->leave_type == 'SL') ? 'Sick Leave' : $cr->leave_type) ?>)</span></td>
                                            <td><strong><?= date('F Y', strtotime($cr->month_year . '-01')) ?></strong></td>
                                            <td><strong style="color: #27ae60; font-size: 13px;">+<?= number_format($cr->credits, 2) ?> Days</strong></td>
                                            <td><?= $type_label ?></td>
                                            <td><?= !empty($cr->added_by_name) ? htmlspecialchars($cr->added_by_name) : 'Admin' ?></td>
                                            <td><small><?= !empty($cr->remarks) ? htmlspecialchars($cr->remarks) : '-' ?></small></td>
                                            <?php if (!empty(admin_head())) { ?>
                                            <td style="text-align:center;">
                                                <button type="button" class="btn btn-xs btn-danger btn-delete-credit" 
                                                        data-id="<?= $cr->leave_credit_id ?>" 
                                                        title="Revoke / Delete Credit">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            </td>
                                            <?php } ?>
                                        </tr>
                                        <?php }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- ================= MODAL: SET EMPLOYEE LEAVE BALANCE (MID-YEAR SETUP) ================= -->
<div class="modal fade" id="modalSetBalance" tabindex="-1" role="dialog" aria-labelledby="modalSetBalanceLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 6px;">
            <form id="form_set_balance" action="<?= base_url('admin/leave_management/save_employee_leave_balances') ?>" method="post" class="form-horizontal">
                <div class="modal-header" style="background:#f39c12; color:#fff; border-radius: 6px 6px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.9;">&times;</button>
                    <h4 class="modal-title" id="modalSetBalanceLabel">
                        <i class="fa fa-sliders"></i> Set Employee Leave Balance (Mid-Year / New Joining)
                    </h4>
                </div>
                <div class="modal-body" style="padding: 20px 22px;">
                    
                    <div class="alert alert-warning" style="font-size: 12px; margin-bottom: 18px; padding: 10px 14px;">
                        <i class="fa fa-info-circle"></i> <strong>Mid-Term Leave Setup:</strong> Specify the exact remaining leaves for this employee for <?= date('Y') ?>. These values will become their available balance in the CRM and Mobile App.
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Employee <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="user_id" id="set_balance_user_id" class="form-control select_box" style="width: 100%;" required>
                                <option value="">-- Choose Employee --</option>
                                <?php if (!empty($employees)) {
                                    foreach ($employees as $emp) { 
                                        $el_b = !empty($emp->balances['EL']) ? $emp->balances['EL']['balance'] : 0;
                                        $sl_b = !empty($emp->balances['SL']) ? $emp->balances['SL']['balance'] : 0;
                                        $cl_b = !empty($emp->balances['CL']) ? $emp->balances['CL']['balance'] : 12;
                                    ?>
                                    <option value="<?= $emp->user_id ?>" 
                                            data-name="<?= htmlspecialchars($emp->fullname) ?>"
                                            data-joining="<?= !empty($emp->joining_date) ? $emp->joining_date : '' ?>"
                                            data-cl="<?= $cl_b ?>"
                                            data-sl="<?= $sl_b ?>"
                                            data-el="<?= $el_b ?>">
                                        <?= htmlspecialchars($emp->fullname) ?> <?= !empty($emp->emp_code) ? '(' . $emp->emp_code . ')' : '' ?> - <?= !empty($emp->designations) ? $emp->designations : 'Staff' ?>
                                    </option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="emp_joining_info_wrap" style="display:none;">
                        <label class="col-sm-4 control-label">Joining Date</label>
                        <div class="col-sm-8" style="padding-top:7px;">
                            <span id="emp_joining_display" class="label label-info" style="font-size:12px;"></span>
                        </div>
                    </div>

                    <!-- Quick Helper: New Joiner / Mid-Year Pro-Rate Tool -->
                    <div class="well well-sm" style="background:#f8fafc; border:1px dashed #cbd5e1; margin-left:15px; margin-right:15px; padding:10px 14px; border-radius:4px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <span style="font-size:12px; font-weight:600; color:#334155;">
                                <i class="fa fa-calculator text-primary"></i> Auto Pro-Rate Calculator (New Joiner / Mid-Year):
                            </span>
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <select id="calc_month_select" class="form-control input-sm" style="width:130px; font-size:12px;">
                                    <?php 
                                    $curr_m = (int)date('m');
                                    for ($m = 1; $m <= 12; $m++) { 
                                        $m_name = date('F', mktime(0, 0, 0, $m, 1));
                                        $rem = 12 - $m + 1;
                                        $sel = ($m == $curr_m) ? 'selected' : '';
                                        echo "<option value='$m' data-remaining='$rem' $sel>$m_name ($rem mos)</option>";
                                    } ?>
                                </select>
                                <button type="button" id="btn_apply_prorate" class="btn btn-sm btn-info" style="font-size:11px; font-weight:600;">
                                    Calculate
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Casual Leave (CL) -->
                    <div class="form-group">
                        <label class="col-sm-4 control-label">
                            <span class="badge-cl">CL</span> Casual Leave <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" max="45" name="balances[<?= $cl_cat_id ?>]" id="set_cl_balance" class="form-control" value="0" required>
                                <span class="input-group-addon">Days Remaining</span>
                            </div>
                            <small class="text-muted">Rate: 1.0 day / month (12 / year)</small>
                        </div>
                    </div>

                    <!-- Sick Leave (SL) -->
                    <div class="form-group">
                        <label class="col-sm-4 control-label">
                            <span class="badge-sl">SL</span> Sick Leave <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" max="45" name="balances[<?= $sl_cat_id ?>]" id="set_sl_balance" class="form-control" value="0" required>
                                <span class="input-group-addon">Days Remaining</span>
                            </div>
                            <small class="text-muted">Rate: 1.0 day / month (12 / year)</small>
                        </div>
                    </div>

                    <!-- Earn Leave (EL) -->
                    <div class="form-group">
                        <label class="col-sm-4 control-label">
                            <span class="badge-el">EL</span> Earn Leave <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" max="45" name="balances[<?= $el_cat_id ?>]" id="set_el_balance" class="form-control" value="0" required>
                                <span class="input-group-addon">Days Remaining</span>
                            </div>
                            <small class="text-muted">Rate: 1.25 days / month (15 / year)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Setup Reason / Type</label>
                        <div class="col-sm-8">
                            <select name="balance_type" id="set_balance_type" class="form-control">
                                <option value="opening_balance">Mid-Year Opening Balance (Remaining Leaves for <?= date('Y') ?>)</option>
                                <option value="new_joining">New Joining Pro-Rated Allocation</option>
                                <option value="adjustment">Manual Balance Adjustment</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Remarks / Note</label>
                        <div class="col-sm-8">
                            <textarea name="remarks" id="set_balance_remarks" class="form-control" rows="2" placeholder="e.g. Mid-year balance setup as of <?= date('M Y') ?>"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="btn_submit_set_balance" style="font-weight:700;">
                        <i class="fa fa-save"></i> Save Employee Balance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL: CREDIT SINGLE EMPLOYEE ================= -->
<div class="modal fade" id="modalSingleCredit" tabindex="-1" role="dialog" aria-labelledby="modalSingleCreditLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 6px;">
            <form id="form_single_credit" action="<?= base_url('admin/leave_management/save_leave_credit') ?>" method="post" class="form-horizontal">
                <input type="hidden" name="credit_scope" value="single">
                <div class="modal-header" style="background:#5d9cec; color:#fff; border-radius: 6px 6px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.9;">&times;</button>
                    <h4 class="modal-title" id="modalSingleCreditLabel"><i class="fa fa-plus-circle"></i> Add / Credit Leave to Employee</h4>
                </div>
                <div class="modal-body" style="padding: 25px 20px;">
                    
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Select Employee <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="user_id" id="single_user_id" class="form-control select_box" style="width: 100%;" required>
                                <option value="">-- Choose Employee --</option>
                                <?php if (!empty($employees)) {
                                    foreach ($employees as $emp) { ?>
                                    <option value="<?= $emp->user_id ?>">
                                        <?= htmlspecialchars($emp->fullname) ?> <?= !empty($emp->emp_code) ? '(' . $emp->emp_code . ')' : '' ?> - <?= !empty($emp->designations) ? $emp->designations : 'Staff' ?>
                                    </option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Leave Type <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <select name="leave_type" id="single_leave_type" class="form-control" required>
                                    <?php if (!empty($all_categories)) {
                                        foreach ($all_categories as $v_cat) {
                                            $c_code = strtoupper(trim($v_cat->leave_category));
                                            $c_label = $c_code;
                                            if ($c_code == 'EL') $c_label = 'Earn Leave (EL)';
                                            elseif ($c_code == 'SL') $c_label = 'Sick Leave (SL)';
                                            elseif ($c_code == 'CL') $c_label = 'Casual Leave (CL)';
                                    ?>
                                        <option value="<?= $c_code ?>" data-quota="<?= $v_cat->leave_quota ?>"><?= $c_label ?></option>
                                    <?php }
                                    } ?>
                                </select>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default text-primary" data-toggle="modal" data-target="#modalAddLeaveType" title="Create / Save New Leave Type" style="font-weight:600;">
                                        <i class="fa fa-plus-circle"></i> + Add Type
                                    </button>
                                </span>
                            </div>
                            <small class="text-muted" id="single_leave_type_hint">Standard monthly accrual: 1.25 days for EL, 1.00 day for SL.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">For Month &amp; Year <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="month" name="month_year" id="single_month_year" class="form-control" value="<?= date('Y-m') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Days to Credit <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.25" min="0.25" max="30" name="credits" id="single_credits" class="form-control" value="1.25" required>
                            <small class="text-muted">e.g. 1.25, 1.0, 2.5</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Remarks / Reason</label>
                        <div class="col-sm-8">
                            <textarea name="remarks" id="single_remarks" class="form-control" rows="2" placeholder="Optional notes (e.g. Monthly EL accrual for <?= date('F Y') ?>)"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_single_credit">
                        <i class="fa fa-save"></i> Save Leave Credit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL: ADD NEW LEAVE TYPE ================= -->
<div class="modal fade" id="modalAddLeaveType" tabindex="-1" role="dialog" aria-labelledby="modalAddLeaveTypeLabel" style="z-index: 1060;">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius: 6px;">
            <form id="form_add_leave_type" action="<?= base_url('admin/leave_management/save_leave_category_ajax') ?>" method="post">
                <div class="modal-header" style="background:#5d9cec; color:#fff; border-radius: 6px 6px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title" id="modalAddLeaveTypeLabel"><i class="fa fa-plus-circle"></i> Add New Leave Type</h4>
                </div>
                <div class="modal-body" style="padding: 20px 15px;">
                    <div class="form-group">
                        <label class="control-label">Leave Code / Short Name <span class="text-danger">*</span></label>
                        <input type="text" name="leave_category" id="new_cat_code" class="form-control" placeholder="e.g. ML, PL, CO" style="text-transform:uppercase;" required>
                        <small class="text-muted">Short code like EL, SL, CL, ML, etc.</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Full Name / Description</label>
                        <input type="text" name="category_name" id="new_cat_name" class="form-control" placeholder="e.g. Maternity Leave, Compensatory Off">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Annual Quota (Days)</label>
                        <input type="number" min="1" max="100" name="leave_quota" id="new_cat_quota" class="form-control" value="12" required>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn_save_leave_type">
                        <i class="fa fa-save"></i> Save Leave Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL: RUN MONTHLY BULK ACCRUAL ================= -->
<div class="modal fade" id="modalBulkCredit" tabindex="-1" role="dialog" aria-labelledby="modalBulkCreditLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 6px;">
            <form id="form_bulk_credit" action="<?= base_url('admin/leave_management/save_leave_credit') ?>" method="post" class="form-horizontal">
                <input type="hidden" name="credit_scope" value="bulk">
                <div class="modal-header" style="background:#27ae60; color:#fff; border-radius: 6px 6px 0 0;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.9;">&times;</button>
                    <h4 class="modal-title" id="modalBulkCreditLabel"><i class="fa fa-bolt"></i> Run Monthly Leave Accrual (All Active Staff)</h4>
                </div>
                <div class="modal-body" style="padding: 25px 20px;">
                    
                    <div class="alert alert-info" style="font-size: 13px; margin-bottom: 20px;">
                        <i class="fa fa-info-circle"></i> This automated tool will credit <strong>Earn Leave (EL)</strong> and <strong>Sick Leave (SL)</strong> to all <strong><?= $total_staff ?></strong> active staff members for the selected month.
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Accrual Month <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="month" name="month_year" id="bulk_month_year" class="form-control" value="<?= date('Y-m') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Earn Leave (EL) Days <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar-check-o text-success"></i></span>
                                <input type="number" step="0.25" min="0" max="10" name="el_credits" id="bulk_el_credits" class="form-control" value="1.25" required>
                                <span class="input-group-addon">Days / Month</span>
                            </div>
                            <small class="text-muted">Standard rate: 1.25 days per month (15 days/year)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Sick Leave (SL) Days <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-heartbeat text-danger"></i></span>
                                <input type="number" step="0.25" min="0" max="10" name="sl_credits" id="bulk_sl_credits" class="form-control" value="1.00" required>
                                <span class="input-group-addon">Days / Month</span>
                            </div>
                            <small class="text-muted">Standard rate: 1.00 day per month (12 days/year)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Duplicate Protection</label>
                        <div class="col-sm-8">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="skip_existing" value="1" checked>
                                    <strong>Skip staff who already have credits for this month</strong>
                                </label>
                            </div>
                            <small class="text-muted">Uncheck only if you want to overwrite existing credits for this month.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Remarks</label>
                        <div class="col-sm-8">
                            <textarea name="remarks" id="bulk_remarks" class="form-control" rows="2" placeholder="e.g. Monthly automated leave accrual"></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8f9fa;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_bulk_credit">
                        <i class="fa fa-bolt"></i> Run Accrual Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTables
    if ($('#table_employee_leave_balances').length) {
        $('#table_employee_leave_balances').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']]
        });
    }

    if ($('#table_batch_leave_setup').length) {
        $('#table_batch_leave_setup').DataTable({
            responsive: true,
            pageLength: 50,
            order: [[0, 'asc']]
        });
    }

    if ($('#table_credit_history').length) {
        $('#table_credit_history').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }

    // Mid-Year & New Joining: Open Set Balance Modal for specific user
    $(document).on('click', '.btn-set-balance', function() {
        var uid = $(this).data('user-id');
        var uname = $(this).data('user-name');
        var jdate = $(this).data('joining');
        var cl = $(this).data('cl');
        var sl = $(this).data('sl');
        var el = $(this).data('el');

        $('#set_balance_user_id').val(uid).trigger('change');
        $('#set_cl_balance').val(cl !== undefined ? cl : 0);
        $('#set_sl_balance').val(sl !== undefined ? sl : 0);
        $('#set_el_balance').val(el !== undefined ? el : 0);

        if (jdate) {
            $('#emp_joining_display').text(jdate);
            $('#emp_joining_info_wrap').show();
            var jYear = new Date(jdate).getFullYear();
            var currentYear = new Date().getFullYear();
            if (jYear === currentYear) {
                var jMonth = new Date(jdate).getMonth() + 1;
                $('#calc_month_select').val(jMonth);
            }
        } else {
            $('#emp_joining_info_wrap').hide();
        }

        $('#modalSetBalance').modal('show');
    });

    // When employee selection changes inside Set Balance modal
    $('#set_balance_user_id').on('change', function() {
        var opt = $(this).find('option:selected');
        if (!opt.val()) return;
        var jdate = opt.data('joining');
        var cl = opt.data('cl');
        var sl = opt.data('sl');
        var el = opt.data('el');

        if (cl !== undefined) $('#set_cl_balance').val(cl);
        if (sl !== undefined) $('#set_sl_balance').val(sl);
        if (el !== undefined) $('#set_el_balance').val(el);

        if (jdate) {
            $('#emp_joining_display').text(jdate);
            $('#emp_joining_info_wrap').show();
            var jYear = new Date(jdate).getFullYear();
            var currentYear = new Date().getFullYear();
            if (jYear === currentYear) {
                var jMonth = new Date(jdate).getMonth() + 1;
                $('#calc_month_select').val(jMonth);
            }
        } else {
            $('#emp_joining_info_wrap').hide();
        }
    });

    // Auto Pro-rate Calculator button in Set Balance modal
    $('#btn_apply_prorate').on('click', function() {
        var m = parseInt($('#calc_month_select').val()) || (new Date().getMonth() + 1);
        var rem = 12 - m + 1;
        if (rem < 0) rem = 0;
        if (rem > 12) rem = 12;

        var cl = (rem * 1.0).toFixed(1);
        var sl = (rem * 1.0).toFixed(1);
        var el = (rem * 1.25).toFixed(2);

        $('#set_cl_balance').val(cl);
        $('#set_sl_balance').val(sl);
        $('#set_el_balance').val(el);
        $('#set_balance_type').val('new_joining');

        var mName = $('#calc_month_select option:selected').text();
        $('#set_balance_remarks').val('Pro-rated allocation from ' + mName + ' (' + rem + ' months remaining in ' + new Date().getFullYear() + ')');

        if (typeof toastr !== 'undefined') {
            toastr.info('Pro-rated calculated for ' + rem + ' months: CL ' + cl + ', SL ' + sl + ', EL ' + el);
        }
    });

    // AJAX submit for Set Employee Leave Balance
    $('#form_set_balance').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btn_submit_set_balance');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Employee Balance');
                if (res.status === 'success') {
                    $('#modalSetBalance').modal('hide');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    setTimeout(function() { window.location.reload(); }, 900);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error saving balance');
                    } else {
                        alert(res.message || 'Error saving balance');
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Employee Balance');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Server error: ' + error);
                } else {
                    alert('Server error: ' + error);
                }
            }
        });
    });

    // Quick Action in Batch Setup: Pro-rate single row
    $(document).on('click', '.btn-prorate-row', function() {
        var btn = $(this);
        var uid = btn.data('user-id');
        var jdate = btn.data('joining');
        var row = $('#batch_row_' + uid);

        var currentYear = new Date().getFullYear();
        var currentMonth = new Date().getMonth() + 1;
        var calcMonth = currentMonth;

        if (jdate) {
            var jYear = new Date(jdate).getFullYear();
            if (jYear === currentYear) {
                calcMonth = new Date(jdate).getMonth() + 1;
            }
        }

        var rem = 12 - calcMonth + 1;
        if (rem < 0) rem = 0;
        if (rem > 12) rem = 12;

        var cl = (rem * 1.0).toFixed(1);
        var sl = (rem * 1.0).toFixed(1);
        var el = (rem * 1.25).toFixed(2);

        row.find('#batch_cl_' + uid).val(cl);
        row.find('#batch_sl_' + uid).val(sl);
        row.find('#batch_el_' + uid).val(el);

        row.css('background-color', '#fffbeb');
        setTimeout(function() { row.css('background-color', ''); }, 1500);

        if (typeof toastr !== 'undefined') {
            toastr.info('Pro-rated (' + rem + ' mos): CL=' + cl + ', SL=' + sl + ', EL=' + el + '. Click Save to persist.');
        }
    });

    // Quick Action in Batch Setup: Save single row
    $(document).on('click', '.btn-save-row', function() {
        var btn = $(this);
        var uid = btn.data('user-id');
        var row = $('#batch_row_' + uid);
        var cl = row.find('#batch_cl_' + uid).val();
        var sl = row.find('#batch_sl_' + uid).val();
        var el = row.find('#batch_el_' + uid).val();
        var clCatId = '<?= $cl_cat_id ?>';
        var slCatId = '<?= $sl_cat_id ?>';
        var elCatId = '<?= $el_cat_id ?>';

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        var postData = {
            user_id: uid,
            balance_type: 'opening_balance',
            remarks: 'Direct row update from Leave Setup Sheet',
            balances: {}
        };
        postData.balances[clCatId] = cl;
        postData.balances[slCatId] = sl;
        postData.balances[elCatId] = el;

        $.ajax({
            url: '<?= base_url("admin/leave_management/save_employee_leave_balances") ?>',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
                if (res.status === 'success') {
                    row.find('td:nth-child(7)').html('<span class="label label-success" style="font-size:10px;"><i class="fa fa-check"></i> Set</span>');
                    row.attr('data-configured', '1');
                    row.css('background-color', '#f0fdf4');
                    setTimeout(function() { row.css('background-color', ''); }, 2000);
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error saving');
                    } else {
                        alert(res.message || 'Error saving');
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
                alert('Server error: ' + error);
            }
        });
    });

    // Auto Pro-Rate all unconfigured staff in Batch Setup
    $('#btn_prorate_all_unconfigured').on('click', function() {
        var count = 0;
        var currentYear = new Date().getFullYear();
        var currentMonth = new Date().getMonth() + 1;

        var rows;
        if ($.fn.DataTable.isDataTable('#table_batch_leave_setup')) {
            var dt = $('#table_batch_leave_setup').DataTable();
            rows = dt.$('tr');
        } else {
            rows = $('#table_batch_leave_setup tbody tr');
        }

        rows.each(function() {
            var tr = $(this);
            var isConf = tr.data('configured');
            if (isConf == '0' || isConf === 0) {
                var jdate = tr.data('joining');
                var calcMonth = currentMonth;
                if (jdate) {
                    var jYear = new Date(jdate).getFullYear();
                    if (jYear === currentYear) {
                        calcMonth = new Date(jdate).getMonth() + 1;
                    }
                }
                var rem = 12 - calcMonth + 1;
                if (rem < 0) rem = 0;
                if (rem > 12) rem = 12;

                var cl = (rem * 1.0).toFixed(1);
                var sl = (rem * 1.0).toFixed(1);
                var el = (rem * 1.25).toFixed(2);

                tr.find('input[name*="[' + '<?= $cl_cat_id ?>' + ']"]').val(cl);
                tr.find('input[name*="[' + '<?= $sl_cat_id ?>' + ']"]').val(sl);
                tr.find('input[name*="[' + '<?= $el_cat_id ?>' + ']"]').val(el);
                count++;
            }
        });

        if (typeof toastr !== 'undefined') {
            toastr.success('Auto-calculated pro-rated leaves for ' + count + ' unset staff. Click "Save All Balances" to persist.');
        } else {
            alert('Auto-calculated pro-rated leaves for ' + count + ' unset staff. Click "Save All Balances" to persist.');
        }
    });

    // AJAX submit for Batch Leave Balances (All Staff)
    $('#form_batch_balances').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btn_submit_batch_balances');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving All...');

        var formData;
        if ($.fn.DataTable.isDataTable('#table_batch_leave_setup')) {
            var dt = $('#table_batch_leave_setup').DataTable();
            formData = dt.$('input, select, textarea').serialize();
        } else {
            formData = form.serialize();
        }

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Balances');
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    setTimeout(function() { window.location.reload(); }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error saving batch balances');
                    } else {
                        alert(res.message || 'Error saving batch balances');
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Balances');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Server error: ' + error);
                } else {
                    alert('Server error: ' + error);
                }
            }
        });
    });

    // Single credit leave type change default days
    $('#single_leave_type').on('change', function() {
        var val = $(this).val();
        if (val === 'EL') {
            $('#single_credits').val('1.25');
            $('#single_leave_type_hint').text('Standard monthly accrual: 1.25 days for EL.');
        } else if (val === 'SL') {
            $('#single_credits').val('1.00');
            $('#single_leave_type_hint').text('Standard monthly accrual: 1.00 day for SL.');
        } else if (val === 'CL') {
            $('#single_credits').val('1.00');
            $('#single_leave_type_hint').text('Casual leave monthly credit.');
        } else {
            $('#single_credits').val('1.00');
            $('#single_leave_type_hint').text('Leave type: ' + val);
        }
    });

    // AJAX submit for creating new leave type dynamically
    $('#form_add_leave_type').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btn_save_leave_type');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Leave Type');
                if (res.status === 'success') {
                    $('#modalAddLeaveType').modal('hide');
                    form[0].reset();
                    // Append and select the new category in dropdown
                    var newOpt = $('<option>', {
                        value: res.category.leave_category,
                        text: res.category.label,
                        selected: true
                    });
                    $('#single_leave_type').append(newOpt).trigger('change');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                } else {
                    alert(res.message || 'Error saving leave type');
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Leave Type');
                alert('Server error: ' + error);
            }
        });
    });

    // Button in table to credit specific user
    $('.btn-credit-user').on('click', function() {
        var uid = $(this).data('user-id');
        var uname = $(this).data('user-name');
        $('#single_user_id').val(uid).trigger('change');
        $('#modalSingleCredit').modal('show');
    });

    // AJAX submit for single credit
    $('#form_single_credit').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btn_submit_single_credit');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Leave Credit');
                if (res.status === 'success') {
                    $('#modalSingleCredit').modal('hide');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    setTimeout(function() { window.location.reload(); }, 900);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error crediting leave');
                    } else {
                        alert(res.message || 'Error crediting leave');
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Leave Credit');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Server error: ' + error);
                } else {
                    alert('Server error: ' + error);
                }
            }
        });
    });

    // AJAX submit for bulk credit
    $('#form_bulk_credit').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btn_submit_bulk_credit');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing Accrual...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> Run Accrual Now');
                if (res.status === 'success') {
                    $('#modalBulkCredit').modal('hide');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    setTimeout(function() { window.location.reload(); }, 1200);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Error processing monthly accrual');
                    } else {
                        alert(res.message || 'Error processing monthly accrual');
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="fa fa-bolt"></i> Run Accrual Now');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Server error: ' + error);
                } else {
                    alert('Server error: ' + error);
                }
            }
        });
    });

    // Delete / revoke credit
    $(document).on('click', '.btn-delete-credit', function() {
        var id = $(this).data('id');
        if (!confirm('Are you sure you want to revoke and delete this leave credit record?')) {
            return false;
        }

        var row = $('#row_credit_' + id);
        $.ajax({
            url: '<?= base_url("admin/leave_management/delete_leave_credit") ?>',
            type: 'POST',
            data: { leave_credit_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    row.fadeOut(300, function() { $(this).remove(); });
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                } else {
                    alert(res.message || 'Failed to delete record');
                }
            },
            error: function(xhr, status, error) {
                alert('Server error: ' + error);
            }
        });
    });
});
</script>
