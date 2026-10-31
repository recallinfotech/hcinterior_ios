<?php echo message_box('success'); ?>
<?php echo message_box('error');

$edited = can_action('91', 'edited');
$deleted = can_action('91', 'deleted');
?>

<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title" style="margin: 0;">
            <strong><i class="fa fa-users"></i> <?= lang('employee_salary_details') ?></strong>
        </div>
    </div>
    <div class="panel-body">
        <!-- Table -->
        <table class="table table-striped table-hover DataTables" id="DataTables" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th class="col-sm-1"><?= lang('emp_id') ?></th>
                    <th><?= lang('name') ?></th>
                    <th><?= lang('department') ?> / <?= lang('designation') ?></th>
                    <th><?= lang('salary_grade') ?></th>
                    <th><?= lang('basic_salary') ?></th>
                    <th class="col-sm-2 text-right"><?= lang('action') ?></th>
                </tr>
            </thead>
            <tbody>
                <script type="text/javascript">
                $(document).ready(function() {
                    list = base_url + "admin/payroll/employee_salaryList";
                });
                </script>
            </tbody>
        </table>
    </div>
</div>