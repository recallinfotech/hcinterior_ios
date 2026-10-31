<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<?php
$curency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
$currency_symbol = !empty($curency->symbol) ? $curency->symbol : '₹';
?>

<!-- Top Filter Bar -->
<div class="panel panel-custom" style="margin-bottom: 20px;">
    <div class="panel-body" style="padding: 15px 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <form id="existing_customer" action="<?php echo base_url() ?>admin/payroll/provident_fund" method="post" class="form-inline" style="margin: 0;">
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
            <div>
                <button class="btn btn-primary" onclick="printDiv('provident_fund_area')" style="font-weight: 600; color: #ffffff !important;">
                    <i class="fa fa-print"></i> Print Statement
                </button>
            </div>
        </div>
    </div>
</div>

<div id="provident_fund_area">
    <div class="show_print" style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 20px; display: none;">
        <table style="width: 100%; vertical-align: middle;">
            <tr>
                <td style="width: 50px; border: 0px;">
                    <img style="width: 50px; height: 50px; margin-bottom: 5px;"
                         src="<?= base_url() . config_item('company_logo') ?>" alt="" class="img-circle"/>
                </td>
                <td style="border: 0px;">
                    <p style="margin-left: 10px; font-weight: bold; font-size: 16px;"><?= config_item('company_name') ?></p>
                    <p style="margin-left: 10px; font-size: 12px; color: #555;">Provident Fund Statement - Year: <?= html_escape($year) ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="row">
        <!-- Month Selector Pills -->
        <div class="col-md-2 hidden-print">
            <ul class="nav nav-pills nav-stacked" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <?php
                foreach ($provident_fund_info as $key => $v_provident_fund):
                    $month_name = date('F', strtotime($year . '-' . $key));
                    $item_count = count($v_provident_fund);
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
                foreach ($provident_fund_info as $key => $v_provident_fund):
                    $month_name = date('F', strtotime($year . '-' . $key));
                ?>
                    <div id="<?= $month_name ?>" class="tab-pane <?= ($current_month == $key) ? 'active' : ''; ?>">
                        <div class="panel panel-custom">
                            <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="panel-title" style="margin: 0;">
                                    <strong><i class="fa fa-shield text-primary"></i> <?= lang('provident_fund') ?> &mdash; <?= $month_name . ' ' . $year; ?></strong>
                                </div>
                                <div class="pull-right hidden-print">
                                    <span class="hidden-print"><?php echo btn_pdf('admin/payroll/provident_fund_pdf/' . $year . '/' . $key); ?></span>
                                </div>
                            </div>

                            <div class="panel-body" style="padding: 0;">
                                <table class="table table-striped table-hover" style="margin-bottom: 0;">
                                    <thead style="background: #f8fafc;">
                                    <tr>
                                        <th class="col-sm-1"><?= lang('emp_id') ?></th>
                                        <th><?= lang('name') ?></th>
                                        <th><?= lang('payment_date') ?></th>
                                        <th class="text-right"><?= lang('amount') ?> (PF Deduction)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $total_amount = 0;
                                    if (!empty($v_provident_fund)): foreach ($v_provident_fund as $provident_fund) :
                                        $total_amount += floatval($provident_fund->salary_payment_deduction_value);
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="text-primary text-bold"><?php echo html_escape($provident_fund->employment_id); ?></span>
                                            </td>
                                            <td style="font-weight: 600; color: #0f172a;">
                                                <i class="fa fa-user-circle text-muted" style="margin-right: 6px;"></i>
                                                <?php echo html_escape($provident_fund->fullname); ?>
                                            </td>
                                            <td style="color: #64748b;">
                                                <?= strftime(config_item('date_format'), strtotime($provident_fund->paid_date)) ?>
                                            </td>
                                            <td class="text-right" style="font-weight: 700; color: #0284c7;">
                                                <?php echo display_money($provident_fund->salary_payment_deduction_value, $currency_symbol); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                        <tr style="background: #f1f5f9; font-weight: 700;">
                                            <td colspan="3" class="text-right" style="color: #334155;">
                                                <?= lang('total') . ' ' . lang('provident_fund') ?>:
                                            </td>
                                            <td class="text-right" style="color: #0284c7; font-size: 15px;">
                                                <?php echo display_money($total_amount, $currency_symbol); ?>
                                            </td>
                                        </tr>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="4" class="text-center" style="padding: 35px; color: #94a3b8;">
                                                <i class="fa fa-info-circle fa-2x" style="opacity: 0.5; margin-bottom: 8px;"></i>
                                                <div>No Provident Fund records found for <?= $month_name . ' ' . $year ?></div>
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

<script type="text/javascript">
    function printDiv(divId) {
        var printContents = document.getElementById(divId).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
