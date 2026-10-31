<?php echo message_box('success'); ?>
<?php echo message_box('error');

$edited = can_action('98', 'edited');
$created = can_action('98', 'created');
$deleted = can_action('98', 'deleted');
$currency = default_currency();
?>

<?php if (empty($switch)) { ?>
<!-- Top Filter & Actions Bar -->
<div class="panel panel-custom" style="margin-bottom: 20px;">
    <div class="panel-body" style="padding: 15px 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <form action="<?php echo base_url() ?>admin/payroll/advance_salary" method="post" class="form-inline" style="margin: 0;">
                    <label style="margin-right: 8px; font-weight: 600; color: #334155;">
                        <i class="fa fa-calendar"></i> <?= lang('year') ?>:
                    </label>
                    <div class="input-group" style="width: 140px;">
                        <input type="text" name="year" class="form-control years" value="<?php echo !empty($year) ? $year : date('Y'); ?>" data-format="yyyy">
                        <span class="input-group-btn">
                            <button type="submit" data-toggle="tooltip" title="Search" class="btn btn-primary" style="color: #ffffff !important;">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </form>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <?php if (!empty($edited) || !empty($created)) { ?>
                    <a href="<?= base_url() ?>admin/payroll/add_advance_salary" class="btn btn-success" data-toggle="modal" data-target="#myModal" style="font-weight: 600; color: #ffffff !important;">
                        <i class="fa fa-plus-circle"></i>
                        <?php echo ($this->session->userdata('user_type') == 1 ? lang('new') : lang('apply')) . ' ' . lang('advance_salary'); ?>
                    </a>
                <?php } ?>
                <a href="<?= base_url() ?>admin/payroll/advance_salary/true" class="btn btn-info" data-toggle="tooltip" title="<?= lang('switch_to_details') ?>" style="font-weight: 600; color: #ffffff !important;">
                    <i class="fa fa-exchange"></i> <?= lang('switch') ?> View
                </a>
            </div>
        </div>
    </div>
</div>

<div id="advance_salary">
    <div class="show_print" style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 20px; display: none;">
        <table style="width: 100%; vertical-align: middle;">
            <tr>
                <td style="width: 50px; border: 0px;">
                    <img style="width: 50px; height: 50px; margin-bottom: 5px;"
                         src="<?= base_url() . config_item('company_logo') ?>" alt="" class="img-circle" />
                </td>
                <td style="border: 0px;">
                    <p style="margin-left: 10px; font-weight: bold; font-size: 16px;"><?= config_item('company_name') ?></p>
                    <p style="margin-left: 10px; font-size: 12px; color: #555;">Advance Salary Statement - Year: <?= html_escape($year) ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="row">
        <!-- Month Selector Pills -->
        <div class="col-md-2 hidden-print">
            <ul class="nav nav-pills nav-stacked" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <?php
                foreach ($advance_salary_info as $key => $v_advance_salary) :
                    $month_name = date('F', strtotime($year . '-' . $key));
                    $item_count = count($v_advance_salary);
                    $isActive = ($current_month == $key);
                ?>
                <li class="<?= $isActive ? 'active' : ''; ?>" style="margin-bottom: 4px;">
                    <a data-toggle="tab" href="#<?= $month_name ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-radius: 6px; font-weight: <?= $isActive ? '700' : '500'; ?>;">
                        <span><i class="fa fa-calendar-o"></i> <?= $month_name; ?></span>
                        <?php if ($item_count > 0): ?>
                            <span class="badge" style="background: <?= $isActive ? '#ffffff' : '#e2e8f0'; ?>; color: <?= $isActive ? '#0284c7' : '#475569'; ?>;"><?= $item_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Month Tab Contents -->
        <div class="col-md-10">
            <div class="tab-content pl0">
                <?php
                foreach ($advance_salary_info as $key => $v_advance_salary) :
                    $month_name = date('F', strtotime($year . '-' . $key));
                ?>
                <div id="<?= $month_name ?>" class="tab-pane <?= ($current_month == $key) ? 'active' : ''; ?>">
                    <div class="panel panel-custom">
                        <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="panel-title" style="margin: 0;">
                                <strong><i class="fa fa-calendar text-primary"></i> <?= $month_name . ' ' . $year; ?></strong>
                            </div>
                            <div class="pull-right hidden-print">
                                <span class="hidden-print"><?php echo btn_pdf('admin/payroll/advance_salary_pdf/' . $year . '/' . $key); ?></span>
                            </div>
                        </div>

                        <div class="panel-body" style="padding: 0;">
                            <table class="table table-striped table-hover" style="margin-bottom: 0;">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th class="col-sm-1"><?= lang('emp_id') ?></th>
                                        <th><?= lang('name') ?></th>
                                        <th><?= lang('amount') ?></th>
                                        <th><?= lang('deduct_month') ?></th>
                                        <th><?= lang('request_date') ?></th>
                                        <th class="text-center"><?= lang('status') ?></th>
                                        <?php if ($this->session->userdata('user_type') == 1) { ?>
                                        <th class="text-right"><?= lang('action') ?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_amount = 0;
                                    if (!empty($v_advance_salary)) : foreach ($v_advance_salary as $advance_salary) :
                                        $total_amount += floatval($advance_salary->advance_amount);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="text-primary text-bold"><?php echo html_escape($advance_salary->employment_id); ?></span>
                                        </td>
                                        <td style="font-weight: 600; color: #0f172a;">
                                            <?php echo html_escape($advance_salary->fullname); ?>
                                        </td>
                                        <td style="font-weight: 700; color: #0284c7;">
                                            <?php echo display_money($advance_salary->advance_amount, $currency); ?>
                                        </td>
                                        <td>
                                            <i class="fa fa-calendar-o text-muted"></i> <?php echo date('F Y', strtotime($advance_salary->deduct_month)); ?>
                                        </td>
                                        <td style="color: #64748b;">
                                            <?= strftime(config_item('date_format'), strtotime($advance_salary->request_date)) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            if ($advance_salary->status == '0') {
                                                echo '<span class="label label-warning" style="padding: 4px 8px;"><i class="fa fa-clock-o"></i> ' . lang('pending') . '</span>';
                                            } elseif ($advance_salary->status == '1') {
                                                echo '<span class="label label-success" style="padding: 4px 8px;"><i class="fa fa-check"></i> ' . lang('accepted') . '</span>';
                                            } elseif ($advance_salary->status == '2') {
                                                echo '<span class="label label-danger" style="padding: 4px 8px;"><i class="fa fa-times"></i> ' . lang('rejected') . '</span>';
                                            } else {
                                                echo '<span class="label label-info" style="padding: 4px 8px;"><i class="fa fa-check-circle"></i> ' . lang('paid') . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <?php if ($this->session->userdata('user_type') == 1) { ?>
                                        <td class="text-right">
                                            <a href="<?= base_url() ?>admin/payroll/advance_salary_details/<?= $advance_salary->advance_salary_id ?>"
                                                class="btn btn-info btn-xs" title="<?= lang('view') ?>" data-toggle="modal"
                                                data-target="#myModal">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr style="background: #f1f5f9; font-weight: 700;">
                                        <td colspan="2" class="text-right" style="color: #334155;">
                                            <?= lang('total') . ' ' . lang('advance_salary') ?>:
                                        </td>
                                        <td colspan="<?php echo ($this->session->userdata('user_type') == 1) ? '5' : '4'; ?>" style="color: #0284c7; font-size: 15px;">
                                            <?php echo display_money($total_amount, $currency); ?>
                                        </td>
                                    </tr>
                                    <?php else : ?>
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding: 35px; color: #94a3b8;">
                                            <i class="fa fa-info-circle fa-2x" style="opacity: 0.5; margin-bottom: 8px;"></i>
                                            <div>No advance salary requests for <?= $month_name . ' ' . $year ?></div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php if (!empty($switch)) { ?>
<!-- Switch Details View -->
<div class="panel panel-custom" style="margin-bottom: 20px;">
    <div class="panel-body" style="padding: 15px 20px;">
        <div class="row" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <div class="col-sm-6">
                <?php if (!empty($edited) || !empty($created)) { ?>
                <a href="<?= base_url() ?>admin/payroll/add_advance_salary/true" class="btn btn-success" data-toggle="modal" data-target="#myModal" style="font-weight: 600;">
                    <i class="fa fa-plus-circle"></i>
                    <?php echo ($this->session->userdata('user_type') == 1 ? lang('new') : lang('apply')) . ' ' . lang('advance_salary'); ?>
                </a>
                <?php } ?>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= base_url() ?>admin/payroll/advance_salary" class="btn btn-purple" style="font-weight: 600;">
                    <i class="fa fa-undo"></i> <?= lang('switch_to_previous') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-3">
        <ul class="nav nav-pills nav-stacked navbar-custom-nav" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;">
            <li class="<?= $active == 1 ? 'active' : '' ?>">
                <a href="#leave_report" data-toggle="tab"><i class="fa fa-bar-chart"></i> <?= lang('advance_salary_report') ?></a>
            </li>
            <li class="my_advance_salary <?= $active == 2 ? 'active' : '' ?>">
                <a href="#my_leave" data-toggle="tab"><i class="fa fa-user"></i> <?= lang('advance_salary') ?></a>
            </li>
            <?php if ($this->session->userdata('user_type') == 1) { ?>
            <li class="all_advance_salary <?= $active == 2 ? 'active' : '' ?>">
                <a href="#all_leave" data-toggle="tab"><i class="fa fa-users"></i> <?= lang('all_advance_salary') ?></a>
            </li>
            <?php } ?>
        </ul>
    </div>
    <div class="col-sm-9">
        <div class="tab-content" style="border: 0; padding: 0;">
            <!-- My Advance Salary Tab -->
            <div class="tab-pane <?= $active == 2 ? 'active' : '' ?>" id="my_leave">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                        <h3 class="panel-title" style="margin: 0; font-weight: 600;"><i class="fa fa-user"></i> <?= lang('advance_salary') ?></h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped table-hover" id="DataTables" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><?= lang('emp_id') ?></th>
                                    <th><?= lang('name') ?></th>
                                    <th><?= lang('amount') ?></th>
                                    <th><?= lang('request_date') ?></th>
                                    <th><?= lang('deduct_month') ?></th>
                                    <th><?= lang('status') ?></th>
                                    <?php if ($this->session->userdata('user_type') == 1) { ?>
                                    <th><?= lang('action') ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Advance Salary Report Tab -->
            <div class="tab-pane <?= $active == 1 ? 'active' : '' ?>" id="leave_report">
                <div class="panel panel-custom">
                    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="panel-title" style="margin: 0; font-weight: 600;"><i class="fa fa-bar-chart"></i> <?= lang('advance_salary_report') ?></h3>
                        <form class="form-inline" action="<?php echo base_url() ?>admin/payroll/advance_salary" method="post">
                            <label style="margin-right: 6px;"><?= lang('year') ?>:</label>
                            <input type="text" name="year" class="form-control input-sm years" style="width: 80px;" value="<?php echo !empty($year) ? $year : date('Y'); ?>" data-format="yyyy">
                            <button type="submit" class="btn btn-sm btn-purple"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                    <div class="panel-body">
                        <?php if ($this->session->userdata('user_type') == 1) { ?>
                        <div class="mb-lg">
                            <h4 style="font-weight: 600; color: #334155; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;"><?= lang('all') . ' ' . lang('advance_salary_report') ?></h4>
                            <div id="morris_line_all" style="height: 250px;"></div>
                        </div>
                        <?php } ?>
                        <div class="mt-lg">
                            <h4 style="font-weight: 600; color: #334155; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;"><?= lang('my_report') ?></h4>
                            <div id="morris_line_my" style="height: 250px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    list = base_url + "admin/payroll/my_advance_salaryList";
    <?php if (admin_head()) { ?>
    $('.all_advance_salary').on('click', function() {
        table_url(base_url + "admin/payroll/all_advance_salaryList");
    });
    <?php } ?>
    $('.my_advance_salary').on('click', function() {
        table_url(base_url + "admin/payroll/my_advance_salaryList");
    });
});
</script>

<?php if ($this->session->userdata('user_type') == 1) { ?>
<script type="text/javascript">
$(function() {
    if (typeof Morris === 'undefined') return;
    var chartdata = [
        <?php foreach ($advance_salary_info as $key => $v_advance_salary) {
            $month_name = date('F', strtotime($year . '-' . $key));
            $total_amount = 0;
            foreach ($v_advance_salary as $adv_item) {
                $total_amount += floatval($adv_item->advance_amount);
            }
        ?> {
            y: "<?= $month_name ?>",
            all_report: <?= $total_amount ?>,
        },
        <?php } ?>
    ];
    new Morris.Line({
        element: 'morris_line_all',
        data: chartdata,
        xkey: 'y',
        ykeys: ["all_report"],
        labels: ["<?= lang('advance_salary') ?>"],
        lineColors: ["#7266ba"],
        parseTime: false,
        resize: true
    });
});
</script>
<?php } ?>

<script type="text/javascript">
$(function() {
    if (typeof Morris === 'undefined') return;
    var my_chartdata = [
        <?php foreach ($advance_salary_info as $mkey => $my_adv_list) {
            $my_month_name = date('F', strtotime($year . '-' . $mkey));
            $my_total = 0;
            foreach ($my_adv_list as $my_item) {
                if ($my_item->user_id == $this->session->userdata('user_id')) {
                    $my_total += floatval($my_item->advance_amount);
                }
            }
        ?> {
            y: "<?= $my_month_name ?>",
            my_report: <?= $my_total ?>,
        },
        <?php } ?>
    ];
    new Morris.Line({
        element: 'morris_line_my',
        data: my_chartdata,
        xkey: 'y',
        ykeys: ["my_report"],
        labels: ["<?= lang('my_advance_salary') ?>"],
        lineColors: ["#23b7e5"],
        parseTime: false,
        resize: true
    });
});
</script>
<?php } ?>