<?php
$curency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
$currency_symbol = !empty($curency->symbol) ? $curency->symbol : '₹';
?>
<div class="panel panel-custom" style="margin-bottom: 0;">
    <div class="panel-heading" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fff; padding: 14px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="panel-title" style="margin: 0; font-size: 16px; font-weight: 600;">
            <i class="fa fa-credit-card" style="margin-right: 8px; color: #38bdf8;"></i>
            <?= lang('advance_salary_details') ?>
        </h4>
    </div>
    <div class="panel-body form-horizontal" style="padding: 24px;">
        <table class="table table-bordered" style="margin-bottom: 20px;">
            <tbody>
                <tr>
                    <td style="width: 35%; background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('employee') ?></td>
                    <td style="font-weight: 700; color: #0f172a;">
                        <i class="fa fa-user-circle text-muted" style="margin-right: 6px;"></i>
                        <?php echo html_escape($advance_salary_info->fullname); ?>
                        <?php if (!empty($advance_salary_info->employment_id)): ?>
                            <span class="label label-default" style="margin-left: 8px; font-weight: normal;"><?php echo html_escape($advance_salary_info->employment_id); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('advance_amount') ?></td>
                    <td style="font-size: 18px; font-weight: 800; color: #0284c7;">
                        <?php echo display_money($advance_salary_info->advance_amount, $currency_symbol); ?>
                    </td>
                </tr>
                <tr>
                    <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('deduct_month') ?></td>
                    <td style="font-weight: 600; color: #334155;">
                        <i class="fa fa-calendar-o text-muted" style="margin-right: 6px;"></i>
                        <?php echo date('F Y', strtotime($advance_salary_info->deduct_month)); ?>
                    </td>
                </tr>
                <tr>
                    <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('apply_on') ?></td>
                    <td style="color: #64748b;">
                        <?= strftime(config_item('date_format'), strtotime($advance_salary_info->request_date)) ?>
                    </td>
                </tr>
                <?php if (!empty($advance_salary_info->approve_by) && $advance_salary_info->approve_by != 0) {
                    $approver = $this->db->where('user_id', $advance_salary_info->approve_by)->get('tbl_account_details')->row();
                    if (!empty($approver)) {
                    ?>
                    <tr>
                        <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('action_by') ?></td>
                        <td><?= html_escape($approver->fullname); ?></td>
                    </tr>
                <?php } } ?>
                <tr>
                    <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('current_status') ?></td>
                    <td>
                        <?php
                        if ($advance_salary_info->status == '0') {
                            echo '<span class="label label-warning" style="padding: 4px 10px; font-size: 12px;"><i class="fa fa-clock-o"></i> ' . lang('pending') . '</span>';
                        } elseif ($advance_salary_info->status == '1') {
                            echo '<span class="label label-success" style="padding: 4px 10px; font-size: 12px;"><i class="fa fa-check"></i> ' . lang('accepted') . '</span>';
                        } elseif ($advance_salary_info->status == '2') {
                            echo '<span class="label label-danger" style="padding: 4px 10px; font-size: 12px;"><i class="fa fa-times"></i> ' . lang('rejected') . '</span>';
                        } else {
                            echo '<span class="label label-info" style="padding: 4px 10px; font-size: 12px;"><i class="fa fa-check-circle"></i> ' . lang('paid') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php if (!empty($advance_salary_info->reason)): ?>
                <tr>
                    <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('reason') ?></td>
                    <td style="font-size: 13px; color: #334155;"><?= nl2br(html_escape($advance_salary_info->reason)); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($advance_salary_info->comments)): ?>
                <tr>
                    <td style="background: #f8fafc; font-weight: 600; color: #475569;"><?= lang('comments') ?></td>
                    <td style="font-size: 13px; color: #334155;"><?= nl2br(html_escape($advance_salary_info->comments)); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($this->session->userdata('user_type') == 1 && ($advance_salary_info->status == '0' || $advance_salary_info->status == '2')) { ?>
            <div style="background: #f1f5f9; border-radius: 6px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <span style="font-weight: 600; color: #334155;"><?= lang('change') . ' ' . lang('status') ?>:</span>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="<?= base_url() ?>admin/payroll/set_salary_status/1/<?= $advance_salary_info->advance_salary_id; ?>"
                       class="btn btn-sm btn-success" style="font-weight: 600;">
                        <i class="fa fa-check"></i> <?= lang('approved') ?>
                    </a>
                    <a href="<?= base_url() ?>admin/payroll/set_salary_status/2/<?= $advance_salary_info->advance_salary_id; ?>"
                       class="btn btn-sm btn-danger" style="font-weight: 600;">
                        <i class="fa fa-times"></i> <?= lang('reject') ?>
                    </a>
                    <a href="<?= base_url() ?>admin/payroll/set_salary_status/3/<?= $advance_salary_info->advance_salary_id; ?>"
                       class="btn btn-sm btn-info" style="font-weight: 600;">
                        <i class="fa fa-money"></i> <?= lang('paid') ?>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fa fa-times"></i> <?= lang('close') ?>
        </button>
    </div>
</div>
