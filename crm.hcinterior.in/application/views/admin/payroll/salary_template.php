<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('94', 'created');
$edited = can_action('94', 'edited');
$deleted = can_action('94', 'deleted');
if (!empty($created) || !empty($edited)) {
?>
<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                href="<?= base_url('admin/payroll/salary_template') ?>"><i class="fa fa-list"></i> <?= lang('salary_template_list') ?></a>
        </li>
        <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                href="<?= base_url('admin/payroll/create_salarytemplate') ?>"><i class="fa fa-money"></i> Set Employee Salary</a></li>
    </ul>
    <div class="tab-content bg-white" style="border: 1px solid #e2e8f0; border-top: none; padding: 20px;">
        <!-- ************** general *************-->
        <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
            <?php } else { ?>
            <div class="panel panel-custom">
                <header class="panel-heading ">
                    <div class="panel-title"><strong><i class="fa fa-list"></i> <?= lang('salary_template_list') ?></strong></div>
                </header>
                <div class="panel-body">
            <?php } ?>
                <table class="table table-striped table-hover" id="DataTables" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="col-sm-1"><?= lang('sl') ?></th>
                            <th><?= lang('salary_grade') ?></th>
                            <th><?= lang('basic_salary') ?></th>
                            <th><?= lang('gross_salary') ?></th>
                            <th><?= lang('net_salary') ?></th>
                            <th class="col-sm-2 text-right"><?= lang('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <script type="text/javascript">
                        $(document).ready(function() {
                            list = base_url + "admin/payroll/salary_templateList";
                        });
                        </script>
                    </tbody>
                </table>
            <?php if (empty($created) && empty($edited)) { ?>
                </div>
            </div>
            <?php } else { ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>