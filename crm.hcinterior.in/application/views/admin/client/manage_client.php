<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>


<?php
$user_id = $this->session->userdata('user_id');
$user_type          = $this->session->userdata('user_type');
$designation_id     = $this->session->userdata('designations_id');
//    echo  $designation_id ;
?>


<?php
$all_customer_group = $this->db->where('type', 'client')->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();
$curency = $this->client_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
$user_id = $this->session->userdata('user_id');
$assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();

$id = $this->uri->segment(5);
$search_by = $this->uri->segment(4);
$created = can_action('4', 'created');
$edited = can_action('4', 'edited');
$deleted = can_action('4', 'deleted');
?>
<div class="row">
    <div class="col-sm-12">
        <?php
        $is_department_head = is_department_head();
        $this->load->helper('permission_helper');
        $can_view_kpi = ($user_type == 1 || check_permission('client_dashboard_kpi', $user_id));
        $can_view_filters = ($user_type == 1 || check_permission('client_filters', $user_id));
        $is_finance_manager = is_finance_manager($user_id);
        $can_export_client = can_export_client_data($user_id);

        if (!empty($can_view_filters)) { ?>
            <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
                data-title="<?php echo lang('filter_by'); ?>">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-left group" style="width:300px;">
                    <li class="filter_by"><a href="#"><?php echo lang('all'); ?></a></li>
                    <li class="divider"></li>
                    <?php if (count(array($all_customer_group)) > 0) { ?>
                        <?php foreach ($all_customer_group as $group) {
                        ?>
                            <li class="filter_by" id="<?= $group->customer_group_id ?>">
                                <a href="#"><?php echo $group->customer_group; ?></a>
                            </li>
                        <?php }
                        ?>
                        <div class="clearfix"></div>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <?php if (!empty($can_view_kpi)) { ?>
            <!-- CLIENT ANALYTICS & QUICK FILTER CARDS -->
            <style>
                .client-kpi-grid {
                    display: grid;
                    grid-template-columns: repeat(6, 1fr);
                    gap: 12px;
                    margin-bottom: 14px;
                }
                @media (max-width: 1200px) {
                    .client-kpi-grid {
                        grid-template-columns: repeat(3, 1fr);
                    }
                }
                @media (max-width: 768px) {
                    .client-kpi-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }
                .kpi-stat-card {
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    padding: 12px 14px;
                    cursor: pointer;
                    position: relative;
                    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
                    user-select: none;
                }
                .kpi-stat-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
                }
                .kpi-stat-card.active-kpi {
                    border-width: 2px !important;
                    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
                }
                .kpi-stat-card .card-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 6px;
                }
                .kpi-stat-card .card-icon {
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                }
                .kpi-stat-card .card-count {
                    font-size: 22px;
                    font-weight: 700;
                    color: #0f172a;
                    line-height: 1.1;
                }
                .kpi-stat-card .card-label {
                    font-size: 11px;
                    font-weight: 600;
                    color: #64748b;
                    text-transform: uppercase;
                    letter-spacing: 0.4px;
                    margin-top: 4px;
                    display: block;
                }
                .kpi-stat-card .card-subtext {
                    font-size: 10px;
                    color: #94a3b8;
                    margin-top: 2px;
                }
                
                /* Color variations */
                .kpi-card-total { border-left: 4px solid #3b82f6; }
                .kpi-card-total .card-icon { background: #eff6ff; color: #2563eb; }
                .kpi-card-total.active-kpi { border-color: #2563eb; background: #f8faff; }

                .kpi-card-active { border-left: 4px solid #10b981; }
                .kpi-card-active .card-icon { background: #ecfdf5; color: #059669; }
                .kpi-card-active.active-kpi { border-color: #059669; background: #f0fdf4; }

                .kpi-card-hold { border-left: 4px solid #f59e0b; }
                .kpi-card-hold .card-icon { background: #fffbeb; color: #d97706; }
                .kpi-card-hold.active-kpi { border-color: #d97706; background: #fffdf5; }

                .kpi-card-closed { border-left: 4px solid #8b5cf6; }
                .kpi-card-closed .card-icon { background: #f5f3ff; color: #7c3aed; }
                .kpi-card-closed.active-kpi { border-color: #7c3aed; background: #faf5ff; }

                .kpi-card-month { border-left: 4px solid #0284c7; }
                .kpi-card-month .card-icon { background: #f0f9ff; color: #0284c7; }
                .kpi-card-month.active-kpi { border-color: #0284c7; background: #f0f9ff; }

                .kpi-card-week { border-left: 4px solid #0d9488; }
                .kpi-card-week .card-icon { background: #f0fdfa; color: #0d9488; }
                .kpi-card-week.active-kpi { border-color: #0d9488; background: #f0fdfa; }

                .onboard-date-bar {
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    padding: 10px 14px;
                    margin-bottom: 15px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
                }
                .quick-date-btn.active-btn {
                    background-color: #3b82f6 !important;
                    color: #ffffff !important;
                    border-color: #2563eb !important;
                }
                .quick-date-btn.active-btn i {
                    color: #ffffff !important;
                }
            </style>

            <?php
            $stats_total = $client_stats['total_clients'] ?? 0;
            $stats_active = $client_stats['active_clients'] ?? 0;
            $stats_hold = $client_stats['hold_clients'] ?? 0;
            $stats_closed = $client_stats['closed_clients'] ?? 0;
            $stats_month = $client_stats['month_clients'] ?? 0;
            $stats_week = $client_stats['week_clients'] ?? 0;
            ?>

            <div class="client-kpi-grid">
                <!-- TOTAL CLIENTS -->
                <div class="kpi-stat-card kpi-card-total active-kpi" id="kpi_btn_total" title="Show All Clients">
                    <div class="card-head">
                        <div class="card-icon"><i class="fa fa-users"></i></div>
                        <span class="label label-default" style="font-size:9px; font-weight:600;">ALL</span>
                    </div>
                    <div class="card-count" id="stat_val_total"><?= $stats_total ?></div>
                    <span class="card-label">Total Clients</span>
                    <div class="card-subtext">All client records</div>
                </div>

                <!-- ACTIVE CLIENTS -->
                <div class="kpi-stat-card kpi-card-active" id="kpi_btn_active" title="Filter Active Clients">
                    <div class="card-head">
                        <div class="card-icon"><i class="fa fa-check-circle"></i></div>
                        <span class="label label-success" style="font-size:9px; font-weight:600;">ACTIVE</span>
                    </div>
                    <div class="card-count" id="stat_val_active" style="color:#059669;"><?= $stats_active ?></div>
                    <span class="card-label">Active Clients</span>
                    <div class="card-subtext">In-progress / ongoing</div>
                </div>

                <!-- HOLD CLIENTS -->
                <div class="kpi-stat-card kpi-card-hold" id="kpi_btn_hold" title="Filter Hold Clients">
                    <div class="card-head">
                        <div class="card-icon"><i class="fa fa-pause-circle"></i></div>
                        <span class="label label-warning" style="font-size:9px; font-weight:600;">HOLD</span>
                    </div>
                    <div class="card-count" id="stat_val_hold" style="color:#d97706;"><?= $stats_hold ?></div>
                    <span class="card-label">On Hold</span>
                    <div class="card-subtext">Operations paused</div>
                </div>

                <!-- CLOSED CLIENTS -->
                <div class="kpi-stat-card kpi-card-closed" id="kpi_btn_closed" title="Filter Closed / Completed Clients">
                    <div class="card-head">
                        <div class="card-icon"><i class="fa fa-flag-checkered"></i></div>
                        <span class="label label-primary" style="font-size:9px; font-weight:600; background-color:#8b5cf6;">CLOSED</span>
                    </div>
                    <div class="card-count" id="stat_val_closed" style="color:#7c3aed;"><?= $stats_closed ?></div>
                    <span class="card-label">Closed / Completed</span>
                    <div class="card-subtext">Finished handover</div>
                </div>

                <!-- 1 MONTH ONBOARDED -->
                <div class="kpi-stat-card kpi-card-month" id="kpi_btn_month" title="Filter Clients Onboarded This Month">
                    <div class="card-head">
                        <div class="card-icon"><i class="fa fa-calendar-check-o"></i></div>
                        <span class="label label-info" style="font-size:9px; font-weight:600; background-color:#0284c7;"><?= date('M Y') ?></span>
                    </div>
                    <div class="card-count" id="stat_val_month" style="color:#0284c7;"><?= $stats_month ?></div>
                    <span class="card-label">1 Month Onboarded</span>
                    <div class="card-subtext">Joined in <?= date('F') ?></div>
                </div>

                <!-- 1 WEEK ONBOARDED -->
                <div class="kpi-stat-card kpi-card-week" id="kpi_btn_week" title="Filter Clients Onboarded in Last 7 Days">
                    <div class="card-head">
                        <div class="card-icon"><i class="fa fa-bolt"></i></div>
                        <span class="label label-info" style="font-size:9px; font-weight:600; background-color:#0d9488;">LAST 7D</span>
                    </div>
                    <div class="card-count" id="stat_val_week" style="color:#0d9488;"><?= $stats_week ?></div>
                    <span class="card-label">1 Week Onboarded</span>
                    <div class="card-subtext">Past 7 days activity</div>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($can_view_filters)) { ?>
            <!-- ONBOARDED DATE-WISE & QUICK FILTER BAR -->
            <div class="onboard-date-bar">
                <div class="row" style="display:flex; align-items:center; flex-wrap:wrap; margin:0;">
                    <div class="col-md-5 col-sm-12" style="padding-left:0; margin-bottom:6px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; color:#1e293b; font-size:12px; white-space:nowrap;">
                                <i class="fa fa-calendar" style="color:#3b82f6;"></i> Onboarded Date:
                            </span>
                            <div class="input-group input-group-sm" style="max-width:140px;">
                                <span class="input-group-addon" style="font-size:11px; padding:3px 8px; font-weight:600;">From</span>
                                <input type="date" id="onboard_date_from" class="form-control" style="font-size:12px; height:30px;">
                            </div>
                            <div class="input-group input-group-sm" style="max-width:140px;">
                                <span class="input-group-addon" style="font-size:11px; padding:3px 8px; font-weight:600;">To</span>
                                <input type="date" id="onboard_date_to" class="form-control" style="font-size:12px; height:30px;">
                            </div>
                            <button type="button" id="apply_onboard_date_btn" class="btn btn-sm btn-primary" style="height:30px; font-weight:600; font-size:11px;">
                                <i class="fa fa-filter"></i> Apply
                            </button>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12" style="margin-bottom:6px;">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-default quick-date-btn" data-preset="1_week" style="font-weight:600; font-size:11px;">
                                <i class="fa fa-bolt" style="color:#0d9488;"></i> 1 Week
                            </button>
                            <button type="button" class="btn btn-default quick-date-btn" data-preset="this_month" style="font-weight:600; font-size:11px;">
                                <i class="fa fa-calendar-check-o text-primary"></i> 1 Month (<?= date('M') ?>)
                            </button>
                            <button type="button" class="btn btn-default quick-date-btn" data-preset="30_days" style="font-weight:600; font-size:11px;">
                                <i class="fa fa-clock-o text-purple"></i> Last 30 Days
                            </button>
                            <button type="button" class="btn btn-default" id="clear_onboard_date_btn" style="color:#64748b; font-size:11px;" title="Clear date filter">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 text-right" style="padding-right:0; margin-bottom:6px;">
                        <span id="active_filter_badge" class="label label-info" style="display:none; font-size:11px; padding:5px 8px; font-weight:600; border-radius:4px;"></span>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-bottom:15px;">

                <!-- USER FILTER BUTTON -->
                <div class="<?= !empty($can_export_client) ? 'col-md-2' : 'col-md-4' ?>">

                    <button type="button"
                        class="btn btn-default btn-block"
                        id="toggle_user_filters">

                        <i class="fa fa-users"></i>
                        User Filters

                    </button>

                </div>

                <!-- TIMELINE FILTER BUTTON -->
                <div class="<?= !empty($can_export_client) ? 'col-md-2' : 'col-md-4' ?>">

                    <button type="button"
                        class="btn btn-default btn-block"
                        id="toggle_timeline_filters">

                        <i class="fa fa-calendar"></i>
                        Timeline Filters

                    </button>

                </div>

                <!-- RESET BUTTON -->
                <div class="<?= !empty($can_export_client) ? 'col-md-2' : 'col-md-4' ?>">

                    <button type="button"
                        id="reset_filters"
                        class="btn btn-danger btn-block">

                        <i class="fa fa-times"></i>
                        Reset Filters

                    </button>

                </div>

                <?php if (!empty($can_export_client)) { ?>
                <!-- EXCEL DOWNLOAD BUTTON -->
                <div class="col-md-3">
                    <button type="button"
                        id="export_excel_btn"
                        class="btn btn-success btn-block export-excel-trigger"
                        title="Download Full Filtered Data as Excel"
                        style="font-weight:600; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                        <i class="fa fa-file-excel-o"></i> Excel Download
                    </button>
                </div>

                <!-- PDF DOWNLOAD BUTTON -->
                <div class="col-md-3">
                    <button type="button"
                        id="export_pdf_btn"
                        class="btn btn-danger btn-block export-pdf-trigger"
                        title="Download Full Filtered Data as PDF"
                        style="font-weight:600; background-color:#dc2626; border-color:#b91c1c; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                        <i class="fa fa-file-pdf-o"></i> PDF Download
                    </button>
                </div>
                <?php } ?>

            </div>





            <!-- USER FILTER BOX -->
            <div class="panel panel-default filter-box"
                id="user_filter_box"
                style="display:none; padding:15px; margin-bottom:15px;">

                <div class="row">

                    <div class="col-md-2 col-sm-4">

                        <label>Select Role</label>

                        <select id="role_filter"
                            class="form-control">

                            <option value="">All Roles</option>

                            <?php
                            $roles = $this->db
                                ->where('status', 1)
                                ->order_by('role_name', 'ASC')
                                ->get('roles')
                                ->result();

                            foreach ($roles as $role) {
                            ?>
                                <option value="<?= $role->role_name ?>">
                                    <?= $role->role_name ?>
                                </option>
                            <?php } ?>

                        </select>

                    </div>

                    <div class="col-md-3 col-sm-4">

                        <label>Select User</label>

                        <select id="assign_user_filter"
                            class="form-control">

                            <option value="">All Users</option>

                        </select>

                    </div>

                    <div class="col-md-2 col-sm-4">
                        <label>Region Filter</label>
                        <select id="region_filter" class="form-control">
                            <option value="">All Regions</option>
                            <?php if (!empty($all_regions)) {
                                foreach ($all_regions as $reg) { ?>
                                    <option value="<?= html_escape($reg) ?>" <?= (isset($_GET['region_filter']) && $_GET['region_filter'] == $reg) ? 'selected' : '' ?>>
                                        <?= html_escape($reg) ?>
                                    </option>
                            <?php } } ?>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6">
                        <label>Payment Filter</label>
                        <select id="payment_filter" class="form-control">
                            <option value="">All</option>
                            <option value="red">Late Payment Client</option>
                            <option value="green">On time Payment Client</option>
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <label>Operation Status</label>
                        <select id="operation_status_filter" class="form-control">
                            <option value="">All Operation Status</option>
                            <option value="Active" <?= (isset($_GET['operation_status_filter']) && $_GET['operation_status_filter'] == 'Active') ? 'selected' : '' ?>>Active</option>
                            <option value="Hold" <?= (isset($_GET['operation_status_filter']) && $_GET['operation_status_filter'] == 'Hold') ? 'selected' : '' ?>>Hold</option>
                            <option value="Closed" <?= (isset($_GET['operation_status_filter']) && in_array($_GET['operation_status_filter'], ['Closed', 'Completed'])) ? 'selected' : '' ?>>Closed / Completed</option>
                            <option value="Unhold" <?= (isset($_GET['operation_status_filter']) && $_GET['operation_status_filter'] == 'Unhold') ? 'selected' : '' ?>>Unhold</option>
                            <option value="Cancelled" <?= (isset($_GET['operation_status_filter']) && $_GET['operation_status_filter'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                </div>

            </div>

            <!-- TIMELINE FILTER BOX -->
            <div class="panel panel-default filter-box"
                id="timeline_filter_box"
                style="display:none; padding:20px; margin-bottom:15px;">

                <h4 style="margin-top:0; margin-bottom:20px; font-weight:600;">
                    <i class="fa fa-calendar"></i>
                    Timeline Filters
                </h4>

                <div class="row">

                    <!-- SITE VALIDATION -->
                    <div class="col-md-3">

                        <div class="timeline-filter-card">

                            <label class="filter-title">
                                Site Validation
                            </label>

                            <div class="row">

                                <div class="col-xs-6">
                                    <small class="mini-label">From</small>

                                    <input type="date"
                                        id="site_validation_from"
                                        class="form-control">
                                </div>

                                <div class="col-xs-6">
                                    <small class="mini-label">To</small>

                                    <input type="date"
                                        id="site_validation_to"
                                        class="form-control">
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- DISPATCH DATE -->
                    <div class="col-md-3">

                        <div class="timeline-filter-card">

                            <label class="filter-title">
                                Dispatch Date
                            </label>

                            <div class="row">

                                <div class="col-xs-6">
                                    <small class="mini-label">From</small>

                                    <input type="date"
                                        id="dispatch_date_from"
                                        class="form-control">
                                </div>

                                <div class="col-xs-6">
                                    <small class="mini-label">To</small>

                                    <input type="date"
                                        id="dispatch_date_to"
                                        class="form-control">
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- SITE START -->
                    <div class="col-md-3">

                        <div class="timeline-filter-card">

                            <label class="filter-title">
                                Site Start
                            </label>

                            <div class="row">

                                <div class="col-xs-6">
                                    <small class="mini-label">From</small>

                                    <input type="date"
                                        id="site_start_date_from"
                                        class="form-control">
                                </div>

                                <div class="col-xs-6">
                                    <small class="mini-label">To</small>

                                    <input type="date"
                                        id="site_start_date_to"
                                        class="form-control">
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- HAND OVER -->
                    <div class="col-md-3">

                        <div class="timeline-filter-card">

                            <label class="filter-title">
                                Hand Over Date
                            </label>

                            <div class="row">

                                <div class="col-xs-6">
                                    <small class="mini-label">From</small>

                                    <input type="date"
                                        id="hand_over_request_date_from"
                                        class="form-control">
                                </div>

                                <div class="col-xs-6">
                                    <small class="mini-label">To</small>

                                    <input type="date"
                                        id="hand_over_request_date_to"
                                        class="form-control">
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="timeline-filter-card">

                            <label class="filter-title">
                                KT Meeting
                            </label>

                            <div class="row">

                                <div class="col-xs-6">
                                    <small class="mini-label">From</small>
                                    <input type="date"
                                        id="kt_meeting_from"
                                        class="form-control">
                                </div>

                                <div class="col-xs-6">
                                    <small class="mini-label">To</small>
                                    <input type="date"
                                        id="kt_meeting_to"
                                        class="form-control">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        <?php
        }
        if (!empty($created) || !empty($edited)) { ?>
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <?php
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
                    ['active_id' => 12, 'label' => lang('On Site Purchase Request List'),  'url' => base_url('admin/client/on_site_purchase_list'),  'permission' => ($user_type == 1 || check_permission('tab_onsite_purchase_request_list') || check_permission('site_dispatch') || check_permission('field_dispatch'))],
                    ['active_id' => 13, 'label' => lang('Dispatch Items & Photos'),        'url' => base_url('admin/client/dispatch_list'),          'permission' => ($user_type == 1 || check_permission('tab_dispatch_items_photos'))],
                    ['active_id' => 14, 'label' => lang('Loose Furniture List'),         'url' => base_url('admin/client/loose_furniture_list'),   'permission' => ($user_type == 1 || check_permission('tab_loose_furniture_list'))],
                    ['active_id' => 15, 'label' => 'Monthly Payment Plan',                 'url' => base_url('admin/client/monthly_payment_plan'),   'permission' => ($user_type == 1 || check_permission('tab_monthly_payment_plan'))],
                    ['active_id' => 16, 'label' => 'Payment Section',                      'url' => base_url('admin/client/payment_section'),        'permission' => ($user_type == 1 || check_permission('tab_payment_section'))],
                ];

                // Filter allowed tabs based on permissions
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

                // Check if active tab is inside "More"
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

                <style type="text/css">
                    .custom-bulk-button {
                        display: initial;
                    }
                </style>

                <div class="tab-content bg-white">
                    <!-- Stock Category List tab Starts -->
                    <div class="tab-pane <?= $active == 1 ? 'active' : '' ?>" id="client_list" style="position: relative;">
                    <?php } else { ?>
                        <div class="panel panel-custom">
                            <header class="panel-heading ">
                                <div class="panel-title"><strong><?= lang('client_list') ?></strong></div>
                            </header>
                        <?php } ?>





                        <div class="box">
                            <div style="padding: 10px 15px 6px 15px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                <div style="font-size:13px; font-weight:700; color:#334155;">
                                    <i class="fa fa-users text-primary"></i> Client Records
                                </div>
                                <?php if (!empty($can_export_client)) { ?>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-success export-excel-trigger" title="Download Filtered Data as Excel" style="font-weight:600;">
                                        <i class="fa fa-file-excel-o"></i> Excel
                                    </button>
                                    <button type="button" class="btn btn-danger export-pdf-trigger" title="Download Filtered Data as PDF" style="font-weight:600; background-color:#dc2626; border-color:#b91c1c;">
                                        <i class="fa fa-file-pdf-o"></i> PDF
                                    </button>
                                </div>
                                <?php } ?>
                            </div>
                            <table class="table table-striped DataTables bulk_table " id="DataTables" cellspacing="0"
                                width="100%">
                                <thead>
                                    <tr>
                                        <?php if (!empty($deleted)) { ?>
                                            <th data-orderable="false">
                                                <div class="checkbox c-checkbox">
                                                    <label class="needsclick">
                                                        <input id="select_all" type="checkbox">
                                                        <span class="fa fa-check"></span></label>
                                                </div>
                                            </th>
                                        <?php } ?>
                                        <th><?= lang('Client ID') ?> </th>
                                        <th><?= lang('name') ?> </th>
                                        <th><?= lang('Date') ?></th>
                                        <?php if ($assign_team->member_profile != 'Operation Team') { ?>
                                            <?php if ($user_type == 1 || $designation_id  == 72 || $designation_id  == 45 || $designation_id  == 48  || $designation_id  == 49 || $designation_id  == 50 || $designation_id  == 54 || $designation_id  == 126 || $designation_id  == 152) {
                                            ?>
                                                <th><?= lang('Freeze BOQ Amount') ?> </th>
                                            <?php } ?>
                                            <!-- <th><?= lang('Discount Modular') ?> </th>
                                    <th><?= lang('Discount Civil') ?> </th>-->

                                            <?php if ($user_type == 1 || $designation_id  == 72 || $designation_id  == 45 || $designation_id  == 48  || $designation_id  == 49 || $designation_id  == 50 || $designation_id  == 152) { ?>
                                                <th><?= lang('Amount Recived') ?> </th>
                                            <?php } ?>

                                        <?php } ?>
                                        <?php if (empty($is_finance_manager)) { ?>
                                            <th><?= lang('Sales Manager Name') ?> </th>
                                            <th>Assign Team</th>
                                            <th>

                                                <?= lang('Hand Ove Request') ?> </th>
                                        <?php } ?>
                                        <?php $show_custom_fields = custom_form_table(12, null);
                                        if (!empty($show_custom_fields)) {
                                            foreach ($show_custom_fields as $c_label => $v_fields) {
                                                if (!empty($c_label)) {
                                        ?>
                                                    <th><?= $c_label ?> </th>
                                        <?php }
                                            }
                                        }
                                        ?>


                                        <?php if (empty($is_finance_manager)) { ?>
                                            <th class=""><?= lang('Phase') ?></th>
                                        <?php } ?>
                                        <th class="hidden-print"><?= lang('action') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <script type="text/javascript">
                                        $(document).ready(function() {
                                            list = base_url + "admin/client/clientList";
                                            bulk_url = base_url + 'admin/client/bulk_delete';
                                            $('.filtered > .dropdown-toggle').on('click', function() {
                                                if ($('.group').css('display') == 'block') {
                                                    $('.group').css('display', 'none');
                                                } else {
                                                    $('.group').css('display', 'block')
                                                }
                                            });
                                            $('.filter_by').on('click', function() {
                                                $('.filter_by').removeClass('active');
                                                $('.group').css('display', 'block');
                                                $(this).addClass('active');
                                                var filter_by = $(this).attr('id');
                                                if (filter_by) {
                                                    filter_by = filter_by;
                                                } else {
                                                    filter_by = '';
                                                }
                                                table_url(base_url + "admin/client/clientList/" +
                                                    filter_by);
                                            });
                                        });
                                    </script>

                                </tbody>
                            </table>
                        </div>
                        </div>

                    </div>
                </div>
            </div>
    </div>
</div>
<script type="text/javascript">
    $('#toggle_user_filters').click(function() {

        $('#user_filter_box').slideToggle(200);

    });

    $('#toggle_timeline_filters').click(function() {

        $('#timeline_filter_box').slideToggle(200);

    });


    function fetch_lat_long_from_google_cprofile() {
        var data = {};
        data.address = $('textarea[name="address"]').val();
        data.city = $('input[name="city"]').val();
        data.country = $('select[name="country"] option:selected').text();
        console.log(data);
        $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
        $.post('<?= base_url() ?>admin/common/fetch_address_info_gmaps', data).done(function(data) {
            data = JSON.parse(data);
            $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
            if (data.response.status == 'OK') {
                $('input[name="latitude"]').val(data.lat);
                $('input[name="longitude"]').val(data.lng);
            } else {
                if (data.response.status == 'ZERO_RESULTS') {
                    toastr.warning("<?php echo lang('g_search_address_not_found'); ?>");
                } else {
                    toastr.warning(data.response.status);
                }
            }
        });
    }
</script>
<script>
    var TODAY = '<?= date("Y-m-d") ?>';
    var ONE_WEEK_AGO = '<?= date("Y-m-d", strtotime("-7 days")) ?>';
    var THIS_MONTH_START = '<?= date("Y-m-01") ?>';
    var THIRTY_DAYS_AGO = '<?= date("Y-m-d", strtotime("-30 days")) ?>';

    function syncKpiCardHighlights() {
        var op_status = $('#operation_status_filter').val();
        var d_from = $('#onboard_date_from').val();
        var d_to   = $('#onboard_date_to').val();

        $('.kpi-stat-card').removeClass('active-kpi');
        $('.quick-date-btn').removeClass('active-btn');

        var filterDescriptions = [];

        // Status highlight
        if (op_status === 'Active') {
            $('#kpi_btn_active').addClass('active-kpi');
            filterDescriptions.push('<i class="fa fa-check-circle" style="color:#059669;"></i> Status: Active');
        } else if (op_status === 'Hold') {
            $('#kpi_btn_hold').addClass('active-kpi');
            filterDescriptions.push('<i class="fa fa-pause-circle" style="color:#d97706;"></i> Status: On Hold');
        } else if (op_status === 'Closed' || op_status === 'Completed') {
            $('#kpi_btn_closed').addClass('active-kpi');
            filterDescriptions.push('<i class="fa fa-flag-checkered" style="color:#7c3aed;"></i> Status: Closed');
        }

        // Date highlight
        if (d_from === THIS_MONTH_START && d_to === TODAY) {
            $('#kpi_btn_month').addClass('active-kpi');
            $('.quick-date-btn[data-preset="this_month"]').addClass('active-btn');
            filterDescriptions.push('<i class="fa fa-calendar-check-o text-primary"></i> Onboarded: 1 Month (<?= date("M") ?>)');
        } else if (d_from === ONE_WEEK_AGO && d_to === TODAY) {
            $('#kpi_btn_week').addClass('active-kpi');
            $('.quick-date-btn[data-preset="1_week"]').addClass('active-btn');
            filterDescriptions.push('<i class="fa fa-bolt" style="color:#0d9488;"></i> Onboarded: 1 Week');
        } else if (d_from === THIRTY_DAYS_AGO && d_to === TODAY) {
            $('.quick-date-btn[data-preset="30_days"]').addClass('active-btn');
            filterDescriptions.push('<i class="fa fa-clock-o text-purple"></i> Onboarded: Last 30 Days');
        } else if (d_from || d_to) {
            filterDescriptions.push('<i class="fa fa-calendar" style="color:#3b82f6;"></i> Date: ' + (d_from || 'Start') + ' to ' + (d_to || 'Today'));
        }

        if (!op_status && !d_from && !d_to) {
            $('#kpi_btn_total').addClass('active-kpi');
            $('#active_filter_badge').hide().html('');
        } else {
            $('#active_filter_badge').show().html('Filter: ' + filterDescriptions.join(' &nbsp;|&nbsp; ') + ' &nbsp;<a href="javascript:void(0)" id="clear_all_quick_filter" style="color:#fff; text-decoration:underline; font-weight:normal; margin-left:6px;"><i class="fa fa-times"></i> Clear</a>');
        }
    }

    function updateTableWithFilter() {

        var kt_meeting_from = $('#kt_meeting_from').val();
        var kt_meeting_to = $('#kt_meeting_to').val();

        var assign_user_id = $('#assign_user_filter').val();

        var site_validation_from = $('#site_validation_from').val();
        var site_validation_to = $('#site_validation_to').val();

        var dispatch_date_from = $('#dispatch_date_from').val();
        var dispatch_date_to = $('#dispatch_date_to').val();

        var site_start_date_from = $('#site_start_date_from').val();
        var site_start_date_to = $('#site_start_date_to').val();

        var hand_over_request_date_from = $('#hand_over_request_date_from').val();
        var hand_over_request_date_to = $('#hand_over_request_date_to').val();

        var onboard_date_from = $('#onboard_date_from').val();
        var onboard_date_to = $('#onboard_date_to').val();

        let params = [];

        var payment_filter = $('#payment_filter').val();
        var operation_status_filter = $('#operation_status_filter').val();
        var region_filter = $('#region_filter').val();

        if (payment_filter) {
            params.push("payment_filter=" + payment_filter);
        }

        if (operation_status_filter) {
            params.push("operation_status_filter=" + operation_status_filter);
        }

        if (region_filter) {
            params.push("region_filter=" + encodeURIComponent(region_filter));
        }

        if (assign_user_id) {
            params.push("assign_user_id=" + assign_user_id);
        }

        // ONBOARD DATE FILTERS
        if (onboard_date_from) {
            params.push("onboard_date_from=" + onboard_date_from);
        }
        if (onboard_date_to) {
            params.push("onboard_date_to=" + onboard_date_to);
        }

        // SITE VALIDATION
        if (site_validation_from) {
            params.push("site_validation_from=" + site_validation_from);
        }
        if (site_validation_to) {
            params.push("site_validation_to=" + site_validation_to);
        }

        // DISPATCH
        if (dispatch_date_from) {
            params.push("dispatch_date_from=" + dispatch_date_from);
        }
        if (dispatch_date_to) {
            params.push("dispatch_date_to=" + dispatch_date_to);
        }

        // SITE START
        if (site_start_date_from) {
            params.push("site_start_date_from=" + site_start_date_from);
        }
        if (site_start_date_to) {
            params.push("site_start_date_to=" + site_start_date_to);
        }

        // HANDOVER
        if (hand_over_request_date_from) {
            params.push("hand_over_request_date_from=" + hand_over_request_date_from);
        }
        if (hand_over_request_date_to) {
            params.push("hand_over_request_date_to=" + hand_over_request_date_to);
        }

        if (kt_meeting_from) {
            params.push("kt_meeting_from=" + kt_meeting_from);
        }
        if (kt_meeting_to) {
            params.push("kt_meeting_to=" + kt_meeting_to);
        }

        syncKpiCardHighlights();

        let query = params.join('&');

        // browser url
        let browser_url = base_url + "admin/client/manage_client";

        if (query) {
            browser_url += "?" + query;
        }

        window.history.replaceState({}, '', browser_url);

        // DATATABLE RELOAD
        $('#DataTables').DataTable().ajax.url(
            base_url + "admin/client/clientList?" + query
        ).load();

    }

    // Refresh KPI Counts when datatable loads data
    $(document).on('init.dt', function() {
        $('#DataTables').on('xhr.dt', function(e, settings, json, xhr) {
            if (json && json.stats) {
                if (json.stats.total_clients !== undefined) $('#stat_val_total').text(json.stats.total_clients);
                if (json.stats.active_clients !== undefined) $('#stat_val_active').text(json.stats.active_clients);
                if (json.stats.hold_clients !== undefined) $('#stat_val_hold').text(json.stats.hold_clients);
                if (json.stats.closed_clients !== undefined) $('#stat_val_closed').text(json.stats.closed_clients);
                if (json.stats.month_clients !== undefined) $('#stat_val_month').text(json.stats.month_clients);
                if (json.stats.week_clients !== undefined) $('#stat_val_week').text(json.stats.week_clients);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | KPI STAT CARDS CLICK EVENTS
    |--------------------------------------------------------------------------
    */
    $('#kpi_btn_total').click(function() {
        $('#operation_status_filter').val('');
        $('#onboard_date_from').val('');
        $('#onboard_date_to').val('');
        $('.quick-date-btn').removeClass('active-btn');
        updateTableWithFilter();
    });

    $('#kpi_btn_active').click(function() {
        if ($('#operation_status_filter').val() === 'Active') {
            $('#operation_status_filter').val('');
        } else {
            $('#operation_status_filter').val('Active');
        }
        updateTableWithFilter();
    });

    $('#kpi_btn_hold').click(function() {
        if ($('#operation_status_filter').val() === 'Hold') {
            $('#operation_status_filter').val('');
        } else {
            $('#operation_status_filter').val('Hold');
        }
        updateTableWithFilter();
    });

    $('#kpi_btn_closed').click(function() {
        if ($('#operation_status_filter').val() === 'Closed') {
            $('#operation_status_filter').val('');
        } else {
            $('#operation_status_filter').val('Closed');
        }
        updateTableWithFilter();
    });

    $('#kpi_btn_month').click(function() {
        if ($('#onboard_date_from').val() === THIS_MONTH_START && $('#onboard_date_to').val() === TODAY) {
            $('#onboard_date_from').val('');
            $('#onboard_date_to').val('');
            $('.quick-date-btn').removeClass('active-btn');
        } else {
            $('#onboard_date_from').val(THIS_MONTH_START);
            $('#onboard_date_to').val(TODAY);
            $('.quick-date-btn[data-preset="this_month"]').addClass('active-btn');
        }
        updateTableWithFilter();
    });

    $('#kpi_btn_week').click(function() {
        if ($('#onboard_date_from').val() === ONE_WEEK_AGO && $('#onboard_date_to').val() === TODAY) {
            $('#onboard_date_from').val('');
            $('#onboard_date_to').val('');
            $('.quick-date-btn').removeClass('active-btn');
        } else {
            $('#onboard_date_from').val(ONE_WEEK_AGO);
            $('#onboard_date_to').val(TODAY);
            $('.quick-date-btn[data-preset="1_week"]').addClass('active-btn');
        }
        updateTableWithFilter();
    });

    /*
    |--------------------------------------------------------------------------
    | QUICK DATE PRESET BUTTONS
    |--------------------------------------------------------------------------
    */
    $(document).on('click', '.quick-date-btn', function() {
        var preset = $(this).data('preset');
        $('.quick-date-btn').removeClass('active-btn');
        $(this).addClass('active-btn');

        if (preset === '1_week') {
            $('#onboard_date_from').val(ONE_WEEK_AGO);
            $('#onboard_date_to').val(TODAY);
        } else if (preset === 'this_month') {
            $('#onboard_date_from').val(THIS_MONTH_START);
            $('#onboard_date_to').val(TODAY);
        } else if (preset === '30_days') {
            $('#onboard_date_from').val(THIRTY_DAYS_AGO);
            $('#onboard_date_to').val(TODAY);
        }
        updateTableWithFilter();
    });

    $('#clear_onboard_date_btn').click(function() {
        $('#onboard_date_from').val('');
        $('#onboard_date_to').val('');
        $('.quick-date-btn').removeClass('active-btn');
        updateTableWithFilter();
    });

    $('#apply_onboard_date_btn').click(function() {
        $('.quick-date-btn').removeClass('active-btn');
        updateTableWithFilter();
    });

    $('#onboard_date_from, #onboard_date_to').change(function() {
        $('.quick-date-btn').removeClass('active-btn');
        updateTableWithFilter();
    });

    $(document).on('click', '#clear_all_quick_filter', function() {
        $('#reset_filters').click();
    });

    /*
    |--------------------------------------------------------------------------
    | FILTER CHANGE EVENTS
    |--------------------------------------------------------------------------
    */

    $('#site_validation_from, #site_validation_to').change(function() {
        updateTableWithFilter();
    });

    $('#dispatch_date_from, #dispatch_date_to').change(function() {
        updateTableWithFilter();
    });

    $('#site_start_date_from, #site_start_date_to').change(function() {
        updateTableWithFilter();
    });

    $('#hand_over_request_date_from, #hand_over_request_date_to').change(function() {
        updateTableWithFilter();
    });

    $('#kt_meeting_from, #kt_meeting_to').change(function() {
        updateTableWithFilter();
    });

    $('#assign_user_filter').change(function() {
        updateTableWithFilter();
    });

    $('#payment_filter, #operation_status_filter, #region_filter').change(function() {
        updateTableWithFilter();
    });

    /*
    |--------------------------------------------------------------------------
    | EXCEL & PDF EXPORT HANDLERS (FULL DATA ACCORDING TO ACTIVE FILTERS)
    |--------------------------------------------------------------------------
    */
    function getClientFilterQuery() {
        var params = [];

        var kt_meeting_from = $('#kt_meeting_from').val();
        var kt_meeting_to   = $('#kt_meeting_to').val();
        var assign_user_id  = $('#assign_user_filter').val();
        var site_validation_from = $('#site_validation_from').val();
        var site_validation_to   = $('#site_validation_to').val();
        var dispatch_date_from   = $('#dispatch_date_from').val();
        var dispatch_date_to     = $('#dispatch_date_to').val();
        var site_start_date_from = $('#site_start_date_from').val();
        var site_start_date_to   = $('#site_start_date_to').val();
        var hand_over_request_date_from = $('#hand_over_request_date_from').val();
        var hand_over_request_date_to   = $('#hand_over_request_date_to').val();
        var onboard_date_from = $('#onboard_date_from').val();
        var onboard_date_to   = $('#onboard_date_to').val();
        var payment_filter    = $('#payment_filter').val();
        var operation_status_filter = $('#operation_status_filter').val();
        var region_filter     = $('#region_filter').val();

        if (payment_filter) params.push("payment_filter=" + encodeURIComponent(payment_filter));
        if (operation_status_filter) params.push("operation_status_filter=" + encodeURIComponent(operation_status_filter));
        if (region_filter) params.push("region_filter=" + encodeURIComponent(region_filter));
        if (assign_user_id) params.push("assign_user_id=" + encodeURIComponent(assign_user_id));
        if (onboard_date_from) params.push("onboard_date_from=" + encodeURIComponent(onboard_date_from));
        if (onboard_date_to) params.push("onboard_date_to=" + encodeURIComponent(onboard_date_to));
        if (site_validation_from) params.push("site_validation_from=" + encodeURIComponent(site_validation_from));
        if (site_validation_to) params.push("site_validation_to=" + encodeURIComponent(site_validation_to));
        if (dispatch_date_from) params.push("dispatch_date_from=" + encodeURIComponent(dispatch_date_from));
        if (dispatch_date_to) params.push("dispatch_date_to=" + encodeURIComponent(dispatch_date_to));
        if (site_start_date_from) params.push("site_start_date_from=" + encodeURIComponent(site_start_date_from));
        if (site_start_date_to) params.push("site_start_date_to=" + encodeURIComponent(site_start_date_to));
        if (hand_over_request_date_from) params.push("hand_over_request_date_from=" + encodeURIComponent(hand_over_request_date_from));
        if (hand_over_request_date_to) params.push("hand_over_request_date_to=" + encodeURIComponent(hand_over_request_date_to));
        if (kt_meeting_from) params.push("kt_meeting_from=" + encodeURIComponent(kt_meeting_from));
        if (kt_meeting_to) params.push("kt_meeting_to=" + encodeURIComponent(kt_meeting_to));

        try {
            var search_val = $('#DataTables').DataTable().search();
            if (search_val) {
                params.push("search=" + encodeURIComponent(search_val));
            }
        } catch(e) {}

        return params.join('&');
    }

    $(document).on('click', '.export-excel-trigger', function(e) {
        e.preventDefault();
        var query = getClientFilterQuery();
        var export_url = base_url + "admin/client/export_excel" + (query ? "?" + query : "");
        window.location.href = export_url;
    });

    $(document).on('click', '.export-pdf-trigger', function(e) {
        e.preventDefault();
        var query = getClientFilterQuery();
        var export_url = base_url + "admin/client/export_pdf" + (query ? "?" + query : "");
        window.open(export_url, "_blank");
    });

    /*
    |--------------------------------------------------------------------------
    | RESET FILTERS
    |--------------------------------------------------------------------------
    */

    $('#reset_filters').click(function() {

        $('#payment_filter').val('');
        $('#operation_status_filter').val('');
        $('#region_filter').val('');
        $('#role_filter').val('');

        $('#onboard_date_from').val('');
        $('#onboard_date_to').val('');
        $('.quick-date-btn').removeClass('active-btn');
        $('.kpi-stat-card').removeClass('active-kpi');
        $('#kpi_btn_total').addClass('active-kpi');
        $('#active_filter_badge').hide().html('');

        $('#assign_user_filter').html(
            '<option value="">All Users</option>'
        );

        $('#site_validation_from').val('');
        $('#site_validation_to').val('');

        $('#dispatch_date_from').val('');
        $('#dispatch_date_to').val('');

        $('#site_start_date_from').val('');
        $('#site_start_date_to').val('');

        $('#hand_over_request_date_from').val('');
        $('#hand_over_request_date_to').val('');

        $('#kt_meeting_from').val('');
        $('#kt_meeting_to').val('');

        window.history.replaceState({},
            '',
            base_url + "admin/client/manage_client"
        );

        $('#DataTables').DataTable().ajax.url(
            base_url + "admin/client/clientList"
        ).load();

    });


    /*
    |--------------------------------------------------------------------------
    | PAGE LOAD RESTORE FILTERS
    |--------------------------------------------------------------------------
    */

    $(document).ready(function() {

        const params = new URLSearchParams(window.location.search);

        // region filter
        const region_filter = params.get('region_filter');

        // assign user
        const assign_user_id = params.get('assign_user_id');

        // site validation
        const site_validation_from = params.get('site_validation_from');
        const site_validation_to = params.get('site_validation_to');

        // dispatch
        const dispatch_date_from = params.get('dispatch_date_from');
        const dispatch_date_to = params.get('dispatch_date_to');

        // site start
        const site_start_date_from = params.get('site_start_date_from');
        const site_start_date_to = params.get('site_start_date_to');

        // handover
        const hand_over_request_date_from = params.get('hand_over_request_date_from');
        const hand_over_request_date_to = params.get('hand_over_request_date_to');

        const kt_meeting_from = params.get('kt_meeting_from');
        const kt_meeting_to = params.get('kt_meeting_to');

        const onboard_date_from = params.get('onboard_date_from');
        const onboard_date_to = params.get('onboard_date_to');

        /*
        |--------------------------------------------------------------------------
        | SHOW FILTER BOXES
        |--------------------------------------------------------------------------
        */

        if (assign_user_id || region_filter) {
            $('#user_filter_box').show();
        }

        if (
            site_validation_from ||
            site_validation_to ||
            dispatch_date_from ||
            dispatch_date_to ||
            site_start_date_from ||
            site_start_date_to ||
            hand_over_request_date_from ||
            hand_over_request_date_to ||
            kt_meeting_from ||
            kt_meeting_to
        ) {
            $('#timeline_filter_box').show();
        }


        /*
        |--------------------------------------------------------------------------
        | RESTORE VALUES
        |--------------------------------------------------------------------------
        */
        $('#kt_meeting_from').val(kt_meeting_from);
        $('#kt_meeting_to').val(kt_meeting_to);

        $('#site_validation_from').val(site_validation_from);
        $('#site_validation_to').val(site_validation_to);

        $('#dispatch_date_from').val(dispatch_date_from);
        $('#dispatch_date_to').val(dispatch_date_to);

        $('#site_start_date_from').val(site_start_date_from);
        $('#site_start_date_to').val(site_start_date_to);

        $('#hand_over_request_date_from').val(hand_over_request_date_from);
        $('#hand_over_request_date_to').val(hand_over_request_date_to);

        if (onboard_date_from) $('#onboard_date_from').val(onboard_date_from);
        if (onboard_date_to) $('#onboard_date_to').val(onboard_date_to);

        syncKpiCardHighlights();


        /*
        |--------------------------------------------------------------------------
        | INITIAL URL
        |--------------------------------------------------------------------------
        */

        let initial_url = base_url + "admin/client/clientList";

        let query = [];

        params.forEach(function(value, key) {
            query.push(key + "=" + value);
        });

        if (query.length > 0) {
            initial_url += "?" + query.join('&');
        }

        list = initial_url;


        /*
        |--------------------------------------------------------------------------
        | RESTORE USER
        |--------------------------------------------------------------------------
        */

        if (assign_user_id) {

            $.ajax({
                url: base_url + "admin/client/get_user_by_id",
                type: "POST",
                data: {
                    user_id: assign_user_id
                },
                success: function(response) {

                    $('#assign_user_filter').html(response);

                    $('#assign_user_filter').val(assign_user_id);
                }
            });

        }

    });


    $('#role_filter').change(function() {

        var role_name = $(this).val();

        $('#assign_user_filter').html('<option value="">Loading...</option>');

        $.ajax({
            url: base_url + "admin/client/get_role_users",
            type: "POST",
            data: {
                role_name: role_name
            },
            success: function(response) {

                $('#assign_user_filter').html(response);

                const params = new URLSearchParams(window.location.search);
                const assign_user_id = params.get('assign_user_id');

                if (assign_user_id) {
                    $('#assign_user_filter').val(assign_user_id);
                }
            }
        });

    });
</script>


<div class="modal fade" id="escalationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
                <h4 class="modal-title">
                    Escalation Management
                </h4>
            </div>

            <div class="modal-body">

                <!-- CREATE ESCALATION -->
                <div id="new_escalation_box">

                    <form action="<?= base_url('admin/client/save_escalation') ?>" method="post">

                        <input type="hidden"
                            name="client_id"
                            id="escalation_client_id">

                        <div class="row">

                            <div class="col-md-12">
                                <label>Escalate To</label>

                                <select name="assigned_to"
                                    id="escalation_user_list"
                                    class="form-control"
                                    required>
                                    <option value="">Select User</option>
                                </select>
                            </div>



                        </div>



                        <div class="form-group">
                            <label>Remark / Issue</label>

                            <textarea name="remark"
                                class="form-control"
                                rows="4"
                                required></textarea>
                        </div>

                        <button type="submit"
                            class="btn btn-primary">

                            Submit Escalation

                        </button>

                    </form>
                </div>


                <h4>
                    Escalation History
                </h4>

                <div id="escalation_history">

                    Loading...

                </div>



            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="go_ahead_Modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('Go Ahead') ?>
                </h4>
            </div>
            <form id="bulk_assign_form"
                action="<?= base_url('admin/client/go_ahead') ?>"
                method="post"
                class="form-horizontal form-groups-bordered">

                <div class="modal-body p-5">


                    <input type="hidden" name="client_id" id="client_id">



                    <div class="">
                        <label>Status</label>
                        <input type="text" name="go_ahead"
                            class="form-control" value="Done" readonly>
                    </div>



                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?= lang('close') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('Request') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                    aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('Hand Over Request') ?>
                </h4>
            </div>
            <form id="bulk_assign_form"
                action="<?= base_url('admin/client/hand_over_request_update') ?>"
                method="post"
                class="form-horizontal form-groups-bordered">

                <div class="modal-body p-5">


                    <input type="hidden" name="client_id" id="hand_over_client_id">
                    <input type="hidden" name="hand_over_request_status" id="hand_over_request_status"
                        class="form-control" value="Pending" readonly>

                    <div class="">
                        <label>Sales Manager Name</label>
                        <input type="text" name="sales_manage_name" id="sales_manage_name" class="form-control" readonly>
                        <input type="text" name="sales_manager_id" id="sales_manager_id" class="form-control" readonly>
                    </div>

                    <!-- <div class="">
                                            <label>Status</label>
                                          
                                        </div> -->

                    <div class="">
                        <label>Hand Over Request Date</label>
                        <input
                            type="date"
                            name="hand_over_request_date"
                            class="form-control"
                            required
                            min="<?= date('Y-m-d') ?>"
                            max="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?= lang('close') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('Request') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="approveHandOverModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Approve Hand Over Request</h4>
            </div>

            <form action="<?= base_url('admin/client/hand_over_request_approve') ?>"
                method="post">

                <div class="modal-body ">

                    <input type="hidden" name="client_id" id="approve_client_id">
                    <select class="form-control" name="hand_over_request_status" id="approval_status">
                        <option value="Approved">Approved</option>
                        <option value="Reject">Reject</option>
                    </select>

                    <div class="form-group" id="remark_box" style="display:none; margin-top:10px;">
                        <label>Remark (Required for Reject)</label>
                        <textarea name="hand_over_remark" class="form-control" rows="3"
                            placeholder="Enter rejection remark..."></textarea>
                    </div>
                    <p>
                        Are you sure you want to <strong>update</strong><br>
                        this hand over request?
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        Approve
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>



<script>
    $(document).on('click', '.approve_request_btn', function() {
        var clientId = $(this).data('client-id');
        $('#approve_client_id').val(clientId);
    });
</script>

<script>
    $(document).on('click', '.bulk_assign_btn', function() {

        var clientId = $(this).data('client-id');
        var salesManagerName = $(this).data('sales-manage-name');
        var sales_manager_id = $(this).data('sales_manager_id');

        $('#client_id').val(clientId);
        $('#hand_over_client_id').val(clientId);
        $('#sales_manage_name').val(salesManagerName);
        $('#sales_manager_id').val(sales_manager_id);
        $('#hand_over_request_status').val('Pending');

    });


    $(document).on('change', '#approval_status', function() {
        var status = $(this).val();

        if (status === 'Reject') {
            $('#remark_box').show();
            $('#remark_box textarea').attr('required', true);
        } else {
            $('#remark_box').hide();
            $('#remark_box textarea').val('');
            $('#remark_box textarea').removeAttr('required');
        }
    });
</script>
<?php if ($this->session->userdata('user_type') == 1) { ?>


    <style>
        .dt-buttons {
            display: none;
        }

        #role_filter,
        #assign_user_filter {
            border-radius: 6px;
            height: 34px;
        }

        .panel-default {
            border-radius: 10px;
        }

        .panel-default h5 {
            font-weight: 600;
        }

        #role_filter,
        #assign_user_filter,
        #site_validation_filter,
        #dispatch_date_filter,
        #site_start_date_filter {
            height: 38px;
            border-radius: 6px;
        }

        .filter-box {
            border-radius: 10px;
            background: #fff;
        }

        #toggle_user_filters,
        #toggle_timeline_filters {
            font-weight: 600;
        }

        #reset_filters {
            font-weight: 600;
        }
    </style>

<?php } ?>


<script>
    $(document).on('click', '.escalation_btn', function() {

        let client_id = $(this).data('client-id');

        $('#escalation_client_id').val(client_id);

        // users load
        $.post(
            base_url + 'admin/client/get_client_assign_users', {
                client_id: client_id
            },
            function(response) {
                $('#escalation_user_list').html(response);
            }
        );

        // escalation history load
        $.post(
            base_url + 'admin/client/get_escalation_history', {
                client_id: client_id
            },
            function(response) {

                response = JSON.parse(response);

                $('#escalation_history').html(
                    response.html
                );

                if (response.active_escalation_id > 0) {

                    $('#new_escalation_box').hide();

                } else {

                    $('#new_escalation_box').show();

                }

            }
        );

    });
</script>



<script>
    $(document).on('click', '.reply_escalation_btn', function() {

        let escalation_id = $(this).data('id');

        $('.reply-row').remove();

        $.post(
            base_url + 'admin/client/get_escalation_comments', {
                escalation_id: escalation_id
            },
            function(response) {

                let html = `
                <tr class="reply-row">
                    <td colspan="6">

                        <div id="reply_comments_box_${escalation_id}">
                            ${response}
                        </div>

                        <textarea
                            class="form-control reply_comment"
                            data-id="${escalation_id}"
                            rows="3"
                            placeholder="Write reply..."></textarea>

                        <br>

                        <button
                            type="button"
                            class="btn btn-success save_reply_btn"
                            data-id="${escalation_id}">
                            Submit Reply
                        </button>

                    </td>
                </tr>
            `;

                $('button.reply_escalation_btn[data-id="' + escalation_id + '"]')
                    .closest('tr')
                    .after(html);

            }
        );

    });




    $(document).on('click', '.view_escalation_btn', function() {

        let escalation_id = $(this).data('id');

        $('.reply-row').remove();

        $.post(
            base_url + 'admin/client/get_escalation_comments', {
                escalation_id: escalation_id
            },
            function(response) {

                let html = `
                <tr class="reply-row">
                    <td colspan="6">
                        ${response}
                    </td>
                </tr>
            `;

                $('button.view_escalation_btn[data-id="' + escalation_id + '"]')
                    .closest('tr')
                    .after(html);

            }
        );

    });

    $(document).on('click', '.approve_escalation_btn', function() {

        let escalation_id = $(this).data('id');

        alert('Approve Click : ' + escalation_id);

    });


    $(document).on('click', '.reopen_escalation_btn', function() {

        let escalation_id = $(this).data('id');

        alert('Reopen Click : ' + escalation_id);

    });
</script>


<script>
    $(document).on('click', '.save_reply_btn', function() {

        let escalation_id = $(this).data('id');

        let comment = $(this)
            .closest('td')
            .find('.reply_comment')
            .val();

        if (comment == '') {
            alert('Please enter reply');
            return;
        }

        $.post(
            base_url + 'admin/client/save_escalation_reply', {
                escalation_id: escalation_id,
                comment: comment
            },
            function() {

                $('.reply_escalation_btn[data-id="' + escalation_id + '"]').click();

            }
        );

    });



    $(document).on('click', '.close_escalation_btn', function() {

        let escalation_id = $(this).data('id');

        if (!confirm('Are you sure you want to close this escalation?')) {
            return;
        }

        $.post(
            base_url + 'admin/client/close_escalation', {
                escalation_id: escalation_id
            },
            function() {

                let client_id = $('#escalation_client_id').val();

                $.post(
                    base_url + 'admin/client/get_escalation_history', {
                        client_id: client_id
                    },
                    function(response) {

                        response = JSON.parse(response);

                        $('#escalation_history').html(response.html);

                    }
                );

            }
        );

    });
</script>

<!-- CLIENT INVOICE & AMOUNT MODAL -->
<div class="modal fade" id="clientInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="clientInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 860px; max-width: 95%;">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
            
            <div class="modal-header" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; padding: 14px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.85; font-size: 24px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="clientInvoiceModalLabel" style="font-weight: 600; font-size: 16px; display: flex; align-items: center;">
                    <i class="fa fa-file-text-o text-warning" style="margin-right: 8px;"></i> Client Invoices & Amount Upload
                </h4>
            </div>

            <div class="modal-body" style="padding: 18px; background: #f8fafc;">

                <!-- Client Info Card -->
                <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div class="panel-body" style="padding: 12px 18px; background: #ffffff;">
                        <h4 style="margin: 0; font-weight: 700; color: #1e293b; font-size: 16px;">
                            <span id="cim_client_name">-</span>
                            <span id="cim_client_sr_id" class="label label-primary" style="margin-left: 6px; font-size: 11px;"></span>
                        </h4>
                    </div>
                </div>

                <!-- Previous Uploaded Invoices Table -->
                <div class="panel panel-default" style="border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 18px; overflow: hidden;">
                    <div class="panel-heading" style="background: #f1f5f9; padding: 10px 16px; font-weight: 600; color: #334155; font-size: 13px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa fa-list text-primary" style="margin-right: 5px;"></i> Uploaded Invoices List</span>
                        <span class="label label-info" id="cim_total_invoiced_badge" style="font-size: 11px; padding: 4px 8px;">Total Invoiced: ₹0.00</span>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <div class="table-responsive" style="margin-bottom: 0; max-height: 190px; overflow-y: auto;">
                            <table class="table table-bordered table-striped" style="margin-bottom: 0; font-size: 12px;" id="cim_invoices_table">
                                <thead>
                                    <tr style="background: #f8fafc; color: #475569;">
                                        <th style="width: 45px; text-align: center;">#</th>
                                        <th style="width: 140px;">Amount (₹)</th>
                                        <th>Invoice File</th>
                                        <th style="width: 130px;">Uploaded By</th>
                                        <th style="width: 150px;">Uploaded Date</th>
                                        <th style="width: 60px; text-align: center;" class="cim-delete-col">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="cim_invoices_tbody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted" style="padding: 15px;">Loading invoices...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="cim_view_only_notice" class="alert alert-info" style="margin: 10px 15px; font-size: 12px; display: none;">
                            <i class="fa fa-lock"></i> <strong>View Only Access:</strong> You have permission to view and download invoices. Uploading new invoices requires <em>Client Invoice Upload</em> permission.
                        </div>
                    </div>
                </div>

                <!-- Upload New Invoices Form -->
                <div class="panel panel-default" id="cim_upload_section" style="border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 0;">
                    <div class="panel-heading" style="background: #f1f5f9; padding: 10px 16px; font-weight: 600; color: #334155; font-size: 13px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa fa-cloud-upload text-success" style="margin-right: 5px;"></i> Upload Invoices</span>
                        <button type="button" class="btn btn-xs btn-primary" id="cim_add_row_btn" style="border-radius: 4px; font-weight: 600; padding: 4px 10px;">
                            <i class="fa fa-plus"></i> Add Row
                        </button>
                    </div>
                    <div class="panel-body" style="padding: 16px; background: #ffffff;">
                        
                        <form id="cim_upload_form" enctype="multipart/form-data">
                            <input type="hidden" name="client_id" id="cim_client_id_val">

                            <div id="cim_rows_container">
                                <!-- Dynamic rows are injected here -->
                            </div>

                            <div style="margin-top: 15px; padding-top: 14px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <button type="button" class="btn btn-sm" id="cim_add_row_btn_bottom" style="border: 1px dashed #0284c7; color: #0284c7; background: #f0f9ff; border-radius: 4px; font-weight: 600;">
                                    <i class="fa fa-plus-circle"></i> + Add Another Row
                                </button>
                                <div>
                                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="margin-right: 6px;">Close</button>
                                    <button type="submit" class="btn btn-success btn-sm" id="cim_save_btn" style="min-width: 130px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <i class="fa fa-save"></i> Save Invoices
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    var cimRowIndex = 0;

    function buildInvoiceRow(index) {
        return `
        <div class="row cim-invoice-row" id="cim_row_${index}" style="margin-bottom: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; display: flex; align-items: center;">
            <div class="col-md-5 col-sm-5" style="padding-left: 8px; padding-right: 8px;">
                <label style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 3px; display: block;">
                    <i class="fa fa-inr text-muted"></i> Invoice Amount <span class="text-danger">*</span>
                </label>
                <input type="number" step="0.01" min="1" name="amounts[]" class="form-control input-sm cim-amount-input" placeholder="e.g. 50000" required style="border-radius: 4px;">
            </div>
            <div class="col-md-6 col-sm-6" style="padding-left: 8px; padding-right: 8px;">
                <label style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 3px; display: block;">
                    <i class="fa fa-paperclip text-muted"></i> Invoice File (PDF, Image, Doc) <span class="text-danger">*</span>
                </label>
                <input type="file" name="invoice_files[]" class="form-control input-sm cim-file-input" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" required style="border-radius: 4px; padding: 4px;">
            </div>
            <div class="col-md-1 col-sm-1 text-center" style="padding-left: 4px; padding-right: 4px; padding-top: 18px;">
                <button type="button" class="btn btn-sm btn-danger cim-remove-row-btn" data-row-id="${index}" title="Remove this row" style="border-radius: 4px; padding: 4px 8px;">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
        `;
    }

    function addInvoiceRow() {
        cimRowIndex++;
        $('#cim_rows_container').append(buildInvoiceRow(cimRowIndex));
        updateRemoveButtonsVisibility();
    }

    function updateRemoveButtonsVisibility() {
        var count = $('.cim-invoice-row').length;
        if (count <= 1) {
            $('.cim-remove-row-btn').attr('disabled', true).css('opacity', '0.4');
        } else {
            $('.cim-remove-row-btn').removeAttr('disabled').css('opacity', '1');
        }
    }

    // Open Modal
    $(document).on('click', '.client_invoice_modal_btn', function(e) {
        e.preventDefault();
        var clientId = $(this).data('client-id');
        var clientName = $(this).data('client-name') || '-';
        var clientSrId = $(this).data('client-sr-id') || '';

        $('#cim_client_id_val').val(clientId);
        $('#cim_client_name').text(clientName);
        $('#cim_client_sr_id').text(clientSrId ? clientSrId : 'ID: ' + clientId);

        // Reset rows container with 1 empty row
        $('#cim_rows_container').empty();
        cimRowIndex = 0;
        addInvoiceRow();

        // Load invoices list via AJAX
        loadClientInvoices(clientId);

        $('#clientInvoiceModal').modal('show');
    });

    // Add row buttons
    $(document).on('click', '#cim_add_row_btn, #cim_add_row_btn_bottom', function(e) {
        e.preventDefault();
        addInvoiceRow();
    });

    // Remove row
    $(document).on('click', '.cim-remove-row-btn', function(e) {
        e.preventDefault();
        if ($('.cim-invoice-row').length > 1) {
            $(this).closest('.cim-invoice-row').remove();
            updateRemoveButtonsVisibility();
        }
    });

    // Load Invoices Function
    function loadClientInvoices(clientId) {
        $('#cim_invoices_tbody').html('<tr><td colspan="6" class="text-center text-muted" style="padding: 15px;"><i class="fa fa-spinner fa-spin"></i> Loading invoices...</td></tr>');

        $.ajax({
            url: base_url + 'admin/client/get_client_invoices_ajax',
            type: 'POST',
            data: { client_id: clientId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var canUpload = (res.permissions && res.permissions.can_upload) ? true : false;

                    if (canUpload) {
                        $('#cim_upload_section').show();
                        $('#cim_view_only_notice').hide();
                        $('.cim-delete-col').show();
                    } else {
                        $('#cim_upload_section').hide();
                        $('#cim_view_only_notice').show();
                        $('.cim-delete-col').hide();
                    }

                    if (res.client && res.client.total_invoiced_formatted) {
                        $('#cim_total_invoiced_badge').text('Total Invoiced: ₹' + res.client.total_invoiced_formatted);
                    }

                    if (res.invoices && res.invoices.length > 0) {
                        var rowsHtml = '';
                        $.each(res.invoices, function(idx, inv) {
                            var iconClass = 'fa-file-o';
                            if (inv.file_ext === 'pdf') {
                                iconClass = 'fa-file-pdf-o text-danger';
                            } else if (['jpg', 'jpeg', 'png', 'webp'].indexOf(inv.file_ext) !== -1) {
                                iconClass = 'fa-file-image-o text-info';
                            } else if (['xls', 'xlsx'].indexOf(inv.file_ext) !== -1) {
                                iconClass = 'fa-file-excel-o text-success';
                            } else if (['doc', 'docx'].indexOf(inv.file_ext) !== -1) {
                                iconClass = 'fa-file-word-o text-primary';
                            }

                            var deleteTd = '';
                            if (canUpload) {
                                deleteTd = `
                                <td class="cim-delete-col" style="text-align: center; vertical-align: middle;">
                                    <button type="button" class="btn btn-xs btn-danger cim-delete-invoice-btn" data-id="${inv.id}" title="Delete Invoice">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>`;
                            }

                            rowsHtml += `
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">${idx + 1}</td>
                                <td style="vertical-align: middle;"><b class="text-success" style="font-size: 13px;">₹${inv.amount_formatted}</b></td>
                                <td style="vertical-align: middle;">
                                    <a href="${inv.file_url}" target="_blank" style="text-decoration: none; font-weight: 500; color: #0284c7;">
                                        <i class="fa ${iconClass}"></i> ${inv.original_name}
                                    </a>
                                </td>
                                <td style="vertical-align: middle; color: #334155; font-size: 11px;">
                                    <i class="fa fa-user-circle text-muted"></i> ${inv.uploader || '-'}
                                </td>
                                <td style="vertical-align: middle; color: #64748b; font-size: 11px;">
                                    <i class="fa fa-clock-o"></i> ${inv.created_at}
                                </td>
                                ${deleteTd}
                            </tr>
                            `;
                        });
                        $('#cim_invoices_tbody').html(rowsHtml);
                    } else {
                        var colSpan = canUpload ? 6 : 5;
                        $('#cim_invoices_tbody').html(`<tr><td colspan="${colSpan}" class="text-center text-muted" style="padding: 15px;"><i class="fa fa-info-circle"></i> No invoices uploaded yet for this client.</td></tr>`);
                    }
                } else {
                    $('#cim_invoices_tbody').html('<tr><td colspan="6" class="text-center text-danger" style="padding: 15px;">' + (res.message || 'Failed to load invoices') + '</td></tr>');
                }
            },
            error: function() {
                $('#cim_invoices_tbody').html('<tr><td colspan="6" class="text-center text-danger" style="padding: 15px;">Error loading invoices from server.</td></tr>');
            }
        });
    }

    // Delete Invoice
    $(document).on('click', '.cim-delete-invoice-btn', function(e) {
        e.preventDefault();
        var invoiceId = $(this).data('id');
        var clientId = $('#cim_client_id_val').val();

        if (!confirm('Are you sure you want to delete this invoice record? This action cannot be undone.')) {
            return;
        }

        var $btn = $(this);
        $btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: base_url + 'admin/client/delete_client_invoice_ajax',
            type: 'POST',
            data: { invoice_id: invoiceId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    loadClientInvoices(clientId);
                } else {
                    alert(res.message || 'Error deleting invoice');
                    $btn.removeAttr('disabled').html('<i class="fa fa-trash"></i>');
                }
            },
            error: function() {
                alert('Server error while deleting invoice');
                $btn.removeAttr('disabled').html('<i class="fa fa-trash"></i>');
            }
        });
    });

    // Form Submit Handler
    $(document).on('submit', '#cim_upload_form', function(e) {
        e.preventDefault();

        var clientId = $('#cim_client_id_val').val();
        if (!clientId) {
            alert('Please select a client');
            return;
        }

        // Validate rows
        var valid = true;
        var rowCount = 0;
        $('.cim-invoice-row').each(function() {
            rowCount++;
            var amt = $(this).find('.cim-amount-input').val();
            var file = $(this).find('.cim-file-input')[0].files;

            if (!amt || parseFloat(amt) <= 0) {
                alert('Please enter a valid amount greater than 0 for all rows.');
                $(this).find('.cim-amount-input').focus();
                valid = false;
                return false;
            }

            if (!file || file.length === 0) {
                alert('Please select an invoice file for all rows.');
                $(this).find('.cim-file-input').focus();
                valid = false;
                return false;
            }
        });

        if (!valid) {
            return;
        }

        var $saveBtn = $('#cim_save_btn');
        $saveBtn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

        var formData = new FormData(this);

        $.ajax({
            url: base_url + 'admin/client/save_client_invoices_ajax',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                $saveBtn.removeAttr('disabled').html('<i class="fa fa-save"></i> Save Invoices');
                if (res.success) {
                    // Reset rows to 1 fresh row
                    $('#cim_rows_container').empty();
                    cimRowIndex = 0;
                    addInvoiceRow();

                    // Reload uploaded invoices
                    loadClientInvoices(clientId);

                    // Show success
                    alert(res.message || 'Invoices saved successfully!');
                } else {
                    alert(res.message || 'Failed to save invoices');
                }
            },
            error: function() {
                $saveBtn.removeAttr('disabled').html('<i class="fa fa-save"></i> Save Invoices');
                alert('Server error while saving invoices. Please try again.');
            }
        });
    });
</script>