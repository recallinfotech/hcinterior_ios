<?php
$user_id   = $this->session->userdata('user_id');
$user_type = $this->session->userdata('user_type');

$all_tabs_config = [
    ['active_id' => 1,  'label' => lang('client_list'),                    'url' => base_url('admin/client/manage_client'),         'permission' => true],
    ['active_id' => 2,  'label' => lang('Esclation'),                      'url' => base_url('admin/client/esclation'),             'permission' => ($user_type == 1 || check_permission('tab_escalation'))],
    ['active_id' => 3,  'label' => lang('Pending Esclation'),              'url' => base_url('admin/client/esclation_pending'),     'permission' => ($user_type == 1 || check_permission('tab_pending_escalation'))],
    ['active_id' => 4,  'label' => lang('QC Design'),                      'url' => base_url('admin/client/qc_design'),             'permission' => ($user_type == 1 || check_permission('tab_qc_design'))],
    ['active_id' => 5,  'label' => lang('Final Validation Design'),        'url' => base_url('admin/client/final_validation_design'), 'permission' => ($user_type == 1 || check_permission('tab_final_validation_design'))],
    ['active_id' => 6,  'label' => lang('MOM / Checklist List'),           'url' => base_url('admin/client/mom_checklist'),         'permission' => ($user_type == 1 || check_permission('tab_mom_checklist_list'))],
    ['active_id' => 7,  'label' => lang('Civil Drawing'),                  'url' => base_url('admin/client/civil_drawing_list'),    'permission' => ($user_type == 1 || check_permission('tab_civil_drawing'))],
    ['active_id' => 8,  'label' => lang('KT Request List'),                'url' => base_url('admin/client/kt_request_list'),        'permission' => ($user_type == 1 || check_permission('tab_kt_request_list'))],
    ['active_id' => 9,  'label' => lang('3D Design List'),                 'url' => base_url('admin/client/three_d_design_list'),    'permission' => ($user_type == 1 || check_permission('tab_design_3d_list'))],
    ['active_id' => 10, 'label' => lang('BOM / Cutting List'),           'url' => base_url('admin/client/bom_list'),               'permission' => ($user_type == 1 || check_permission('tab_bom_checklist_list'))],
    ['active_id' => 11, 'label' => lang('Purchase Request List'),          'url' => base_url('admin/client/purchase_request_list'),  'permission' => ($user_type == 1 || check_permission('tab_purchase_request_list'))],
    ['active_id' => 12, 'label' => lang('On Site Purchase Request List'),  'url' => base_url('admin/client/on_site_purchase_list'),  'permission' => ($user_type == 1 || check_permission('tab_onsite_purchase_request_list'))],
    ['active_id' => 13, 'label' => lang('Dispatch Items & Photos'),        'url' => base_url('admin/client/dispatch_list'),          'permission' => ($user_type == 1 || check_permission('tab_dispatch_items_photos'))],
    ['active_id' => 14, 'label' => lang('Loose Furniture List'),         'url' => base_url('admin/client/loose_furniture_list'),   'permission' => ($user_type == 1 || check_permission('tab_loose_furniture_list'))],
    ['active_id' => 15, 'label' => 'Monthly Payment Plan',                 'url' => base_url('admin/client/monthly_payment_plan'),   'permission' => ($user_type == 1 || check_permission('tab_monthly_payment_plan'))],
    ['active_id' => 16, 'label' => 'Payment Section',                      'url' => base_url('admin/client/payment_section'),        'permission' => ($user_type == 1 || check_permission('tab_payment_section'))],
];

$allowed_tabs = array_values(array_filter($all_tabs_config, function ($t) {
    return !empty($t['permission']);
}));

$total_tabs = count($allowed_tabs);
$limit = 7;
$visible_tabs = [];
$more_tabs = [];

if ($total_tabs > $limit) {
    $visible_tabs = array_slice($allowed_tabs, 0, $limit);
    $more_tabs    = array_slice($allowed_tabs, $limit);
} else {
    $visible_tabs = $allowed_tabs;
}

$active = 16;
$is_more_active = false;
$active_more_label = '';
if (!empty($more_tabs)) {
    foreach ($more_tabs as $mtab) {
        if ($active == $mtab['active_id']) {
            $is_more_active = true;
            $active_more_label = $mtab['label'];
            break;
        }
    }
}
?>

<!-- Tab Navigation Bar -->
<div class="nav-tabs-custom" style="margin-bottom: 15px;">
    <ul class="nav nav-tabs">
        <?php foreach ($visible_tabs as $tab): ?>
            <li class="<?= ($active == $tab['active_id']) ? 'active' : '' ?>">
                <a href="<?= $tab['url'] ?>"><?= $tab['label'] ?></a>
            </li>
        <?php endforeach; ?>

        <?php if (!empty($more_tabs)): ?>
            <li class="dropdown <?= $is_more_active ? 'active' : '' ?>">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false" style="font-weight: 600;">
                    More <?= $is_more_active ? '(' . $active_more_label . ')' : '' ?> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <?php foreach ($more_tabs as $tab): ?>
                        <li class="<?= ($active == $tab['active_id']) ? 'active' : '' ?>">
                            <a href="<?= $tab['url'] ?>"><?= $tab['label'] ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Main Panel -->
<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>Payment Section (Client Payment Overview)</strong>
        </div>
    </div>

    <div class="panel-body">
        
        <!-- Filter Bar -->
        <form method="get" action="" class="form-horizontal mb-lg">
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-4">
                    <label>Search Client</label>
                    <input type="text" name="search" class="form-control" placeholder="Search Client Name or Client SR ID" value="<?= html_escape($search ?? '') ?>">
                </div>
                <div class="col-md-3" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filter</button>
                    <a href="<?= base_url('admin/client/payment_section') ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                </div>
            </div>
        </form>

        <!-- Payment Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered DataTables" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th style="width: 20%;">Client ID</th>
                        <th style="width: 25%;">Freeze BOQ Amount</th>
                        <th style="width: 25%;">Payment Received (%)</th>
                        <th style="width: 15%;">Generate Demand</th>
                        <th style="width: 15%;">Send Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary"><?= !empty($client->client_sr_id) ? html_escape($client->client_sr_id) : 'N/A' ?></strong>
                                    <?php if (!empty($client->name)): ?>
                                        <br><a href="<?= base_url('admin/client/details/' . $client->client_id) ?>" target="_blank" style="color: #333; font-weight: 500;">
                                            <?= html_escape($client->name) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($client->freeze_numeric > 0): ?>
                                        <strong style="color: #27ae60;">₹ <?= number_format($client->freeze_numeric, 2) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted"><?= !empty($client->freeze_boq_amount) ? html_escape($client->freeze_boq_amount) : '₹ 0.00' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $pct = $client->payment_percentage;
                                    $badge_class = ($pct >= 100) ? 'success' : (($pct >= 50) ? 'info' : (($pct > 0) ? 'warning' : 'danger'));
                                    ?>
                                    <span class="label label-<?= $badge_class ?>" style="font-size: 13px; padding: 5px 10px; display: inline-block;">
                                        <?= number_format($pct, 2) ?>%
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Received: <strong>₹ <?= number_format($client->total_paid, 2) ?></strong>
                                    </small>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-primary" title="Generate Demand">
                                        <i class="fa fa-file-text-o"></i> Generate Demand
                                    </a>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-info" title="Send Email">
                                        <i class="fa fa-envelope"></i> Send Email
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No active client records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
