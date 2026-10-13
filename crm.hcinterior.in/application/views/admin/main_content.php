<?php
echo message_box('success');
echo message_box('error');
?>

<div class="dashboard">

    <?php
    $CI = &get_instance();
    $CI->load->database();

    $user_id        = $CI->session->userdata('user_id');
    $is_admin       = ($CI->session->userdata('user_type') == 1);
    $designation_id = $CI->session->userdata('designations_id');

    // -------------------------------------------------------------
    // PERMISSION CHECK (Assigned via Department Details tbl_user_role)
    // -------------------------------------------------------------
    $has_leads_perm  = $is_admin || can_action('55', 'view') || can_action_by_label('leads', 'view') || can_do('55');
    $has_client_perm = $is_admin || can_action('4', 'view') || can_action_by_label('client', 'view') || can_do('4');

    // Default view selection
    $active_view = 'leads';
    if (!$has_leads_perm && $has_client_perm) {
        $active_view = 'client';
    } elseif ($has_leads_perm && !$has_client_perm) {
        $active_view = 'leads';
    } else {
        $active_view = (isset($_GET['view']) && $_GET['view'] === 'client') ? 'client' : 'leads';
    }

    // -------------------------------------------------------------
    // 1. LEADS DATA (Only fetched if user has Leads permission)
    // -------------------------------------------------------------
    $latest_leads = [];
    $leads_reminders = [];
    $total_leads = $dead_leads = $forward_new = $fresh = $vendor = $meeting_fixed = 0;

    if ($has_leads_perm) {
        // Optimized Top 5 Leads
        $CI->db->select('l.contact_name, l.phone, l.city, l.created_time, s.lead_type');
        $CI->db->from('tbl_leads l');
        $CI->db->join('tbl_lead_status s', 's.lead_status_id = l.lead_status_id', 'left');

        if (!$is_admin) {
            $CI->db->like('l.permission', '"' . $user_id . '"');
        }

        $CI->db->order_by('l.leads_id', 'DESC');
        $CI->db->limit(5);
        $latest_leads = $CI->db->get()->result();

        // Optimized Latest Reminders (Leads)
        $CI->db->select('r.reminder_id, r.description, r.date, r.notified, r.user_id, u.username as assigned_user');
        $CI->db->from('tbl_reminders r');
        $CI->db->join('tbl_users u', 'u.user_id = r.user_id', 'left');
        $CI->db->where('r.module', 'leads');

        if (!$is_admin) {
            $CI->db->where('r.user_id', $user_id);
        }

        $CI->db->order_by('r.reminder_id', 'DESC');
        $CI->db->limit(5);
        $leads_reminders = $CI->db->get()->result();

        // Leads Metrics
        $where_perm = "";
        if (!$is_admin) {
            $where_perm = " WHERE permission LIKE '%\"" . $user_id . "\"%'";
        }

        $metrics = $CI->db->query("
            SELECT 
                COUNT(*) as total_leads,
                COALESCE(SUM(CASE WHEN lead_status_id = '5' THEN 1 ELSE 0 END), 0) as dead_leads,
                COALESCE(SUM(CASE WHEN lead_status_id = '4' THEN 1 ELSE 0 END), 0) as forward_new,
                COALESCE(SUM(CASE WHEN lead_status_id = '8' THEN 1 ELSE 0 END), 0) as fresh,
                COALESCE(SUM(CASE WHEN lead_status_id = '9' THEN 1 ELSE 0 END), 0) as vendor,
                COALESCE(SUM(CASE WHEN lead_status_id = '3' THEN 1 ELSE 0 END), 0) as meeting_fixed
            FROM tbl_leads
            {$where_perm}
        ")->row();

        $total_leads   = $metrics->total_leads ?? 0;
        $dead_leads    = $metrics->dead_leads ?? 0;
        $forward_new   = $metrics->forward_new ?? 0;
        $fresh         = $metrics->fresh ?? 0;
        $vendor        = $metrics->vendor ?? 0;
        $meeting_fixed = $metrics->meeting_fixed ?? 0;

        // Leads Monthly Trend (Last 6 Months)
        $where_perm_created = " WHERE created_time IS NOT NULL AND created_time != '0000-00-00 00:00:00' ";
        if (!$is_admin) {
            $where_perm_created .= " AND permission LIKE '%\"" . $user_id . "\"%' ";
        }
        $leads_m_res = $CI->db->query("
            SELECT DATE_FORMAT(created_time, '%b %Y') as m_label, COUNT(*) as m_count
            FROM tbl_leads
            {$where_perm_created}
            GROUP BY YEAR(created_time), MONTH(created_time)
            ORDER BY created_time DESC
            LIMIT 6
        ")->result_array();
        $leads_monthly_data = !empty($leads_m_res) ? array_reverse($leads_m_res) : [];
    }

    // -------------------------------------------------------------
    // 2. CLIENT DATA (Only fetched if user has Client permission)
    // -------------------------------------------------------------
    $latest_clients = [];
    $latest_handover_requests = [];
    $total_clients = $unhold_clients = $hold_clients = $handover_approved = $handover_pending = $assigned_clients = 0;

    if ($has_client_perm) {
        $CI->load->helper('permission_helper');
        $has_all_client_perm    = check_permission('all_client_list', $user_id);
        $has_region_client_perm = check_permission('region_wise_client', $user_id);
        $has_only_assigned_perm = check_permission('only_assigned_client', $user_id);

        $all_client_designations = [45, 39, 76, 71, 72, 77, 128, 150];

        $can_view_all_clients = (
            $is_admin ||
            $has_all_client_perm ||
            (!$has_region_client_perm && !$has_only_assigned_perm && in_array((int)$designation_id, $all_client_designations, true))
        );

        $client_ids_filter = null;
        if (!$can_view_all_clients) {
            if ($has_region_client_perm) {
                $user_acc_row = $CI->db->select('user_region')->where('user_id', $user_id)->get('tbl_account_details')->row();
                $user_region_str = !empty($user_acc_row->user_region) ? trim($user_acc_row->user_region) : '';
                $u_regs = array_filter(array_map('trim', explode(',', $user_region_str)));
                if (!empty($u_regs)) {
                    $CI->db->select('tbl_client.client_id');
                    $CI->db->from('tbl_client');
                    $CI->db->join('tbl_leads', 'tbl_leads.leads_id = tbl_client.leads_id', 'left');
                    $CI->db->group_start();
                        $CI->db->where_in('tbl_leads.region', $u_regs);
                        $CI->db->or_where_in('tbl_client.lead_region', $u_regs);
                    $CI->db->group_end();
                    $r_clients = $CI->db->get()->result_array();
                    $client_ids_filter = !empty($r_clients) ? array_unique(array_column($r_clients, 'client_id')) : [0];
                } else {
                    $client_ids_filter = [0];
                }
            } else {
                $assign_query = $CI->db->select('client_id')->from('tbl_client_assign_team')->where('user_id', $user_id)->get()->result_array();
                $assigned_cids = !empty($assign_query) ? array_column($assign_query, 'client_id') : [];

                $perm_leads = $CI->db->select('leads_id')->like('permission', '"' . $user_id . '"')->get('tbl_leads')->result_array();
                $perm_lids = !empty($perm_leads) ? array_column($perm_leads, 'leads_id') : [];

                $CI->db->select('client_id')->from('tbl_client');
                $CI->db->group_start();
                $has_cond = false;
                if (!empty($assigned_cids)) {
                    $CI->db->where_in('client_id', $assigned_cids);
                    $has_cond = true;
                }
                if (!empty($perm_lids)) {
                    if ($has_cond) {
                        $CI->db->or_where_in('leads_id', $perm_lids);
                    } else {
                        $CI->db->where_in('leads_id', $perm_lids);
                        $has_cond = true;
                    }
                }
                if ($has_cond) {
                    $CI->db->or_where('primary_contact', $user_id);
                } else {
                    $CI->db->where('primary_contact', $user_id);
                }
                $CI->db->group_end();
                $c_res = $CI->db->get()->result_array();
                $client_ids_filter = !empty($c_res) ? array_unique(array_column($c_res, 'client_id')) : [0];
            }
        }

        // Top 5 Clients
        $CI->db->select('client_id, client_sr_id, name, phone, lead_region, city, operation_status, date_added');
        $CI->db->from('tbl_client');
        if ($client_ids_filter !== null) {
            $CI->db->where_in('client_id', !empty($client_ids_filter) ? $client_ids_filter : [0]);
        }
        $CI->db->order_by('client_id', 'DESC');
        $CI->db->limit(5);
        $latest_clients = $CI->db->get()->result();

        // Top 5 Handover Requests
        $CI->db->select('client_id, client_sr_id, name, hand_over_request_status, hand_over_request_date, approved_at, hand_over_remark');
        $CI->db->from('tbl_client');
        if ($client_ids_filter !== null) {
            $CI->db->where_in('client_id', !empty($client_ids_filter) ? $client_ids_filter : [0]);
        }
        $CI->db->where('hand_over_request_status IS NOT NULL', null, false);
        $CI->db->where("TRIM(hand_over_request_status) !=", "");
        $CI->db->order_by('client_id', 'DESC');
        $CI->db->limit(5);
        $latest_handover_requests = $CI->db->get()->result();

        // Client Metrics (including Handover Approved & Pending)
        $c_where = "";
        if ($client_ids_filter !== null) {
            $cid_list = !empty($client_ids_filter) ? implode(',', array_map('intval', $client_ids_filter)) : '0';
            $c_where = " WHERE client_id IN ($cid_list)";
        }
        $c_metrics = $CI->db->query("
            SELECT 
                COUNT(*) as total_clients,
                COALESCE(SUM(CASE WHEN operation_status = 'Unhold' THEN 1 ELSE 0 END), 0) as unhold_clients,
                COALESCE(SUM(CASE WHEN operation_status = 'Hold' THEN 1 ELSE 0 END), 0) as hold_clients,
                COALESCE(SUM(CASE WHEN hand_over_request_status IN ('Approved', 'Approve') THEN 1 ELSE 0 END), 0) as handover_approved,
                COALESCE(SUM(CASE WHEN hand_over_request_status = 'Pending' THEN 1 ELSE 0 END), 0) as handover_pending,
                COALESCE(SUM(CASE WHEN hand_over_request_status = 'Reject' THEN 1 ELSE 0 END), 0) as handover_rejected
            FROM tbl_client
            {$c_where}
        ")->row();

        $total_clients     = $c_metrics->total_clients ?? 0;
        $unhold_clients    = $c_metrics->unhold_clients ?? 0;
        $hold_clients      = $c_metrics->hold_clients ?? 0;
        $handover_approved = $c_metrics->handover_approved ?? 0;
        $handover_pending  = $c_metrics->handover_pending ?? 0;
        $handover_rejected = $c_metrics->handover_rejected ?? 0;
        $assigned_clients  = $CI->db->where('user_id', $user_id)->count_all_results('tbl_client_assign_team');

        // Client Monthly Trend (Last 6 Months)
        $c_date_where = " WHERE date_added IS NOT NULL AND date_added != '0000-00-00 00:00:00' ";
        if ($client_ids_filter !== null) {
            $cid_list = !empty($client_ids_filter) ? implode(',', array_map('intval', $client_ids_filter)) : '0';
            $c_date_where .= " AND client_id IN ($cid_list) ";
        }
        $client_m_res = $CI->db->query("
            SELECT DATE_FORMAT(date_added, '%b %Y') as m_label, COUNT(*) as m_count
            FROM tbl_client
            {$c_date_where}
            GROUP BY YEAR(date_added), MONTH(date_added)
            ORDER BY date_added DESC
            LIMIT 6
        ")->result_array();
        $client_monthly_data = !empty($client_m_res) ? array_reverse($client_m_res) : [];
    }
    ?>

    <?php if ($has_leads_perm && $has_client_perm): ?>
        <!-- Tab Switcher for Admin / Users with both Leads & Client Permissions -->
        <div class="row m-b-md" style="margin-bottom: 15px;">
            <div class="col-sm-12">
                <div class="btn-group" role="group">
                    <button type="button" class="btn <?= ($active_view === 'leads') ? 'btn-primary active' : 'btn-default' ?> tab-switch-btn" data-target="#section_leads_dashboard">
                        <i class="fa fa-bullhorn"></i> Leads Dashboard
                    </button>
                    <button type="button" class="btn <?= ($active_view === 'client') ? 'btn-primary active' : 'btn-default' ?> tab-switch-btn" data-target="#section_client_dashboard">
                        <i class="fa fa-users"></i> Client Dashboard
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ============================================================= -->
    <!-- LEADS DASHBOARD SECTION -->
    <!-- ============================================================= -->
    <?php if ($has_leads_perm): ?>
    <div id="section_leads_dashboard" class="dashboard-section" style="<?= ($active_view !== 'leads') ? 'display:none;' : '' ?>">
        <!-- LEADS VISUAL CHARTS -->
        <div class="row">
            <!-- LEADS STATUS DONUT CHART -->
            <div class="col-md-5 mt-lg">
                <div class="panel panel-custom">
                    <header class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-pie-chart text-info"></i> Leads Pipeline Breakdown
                            <span class="pull-right badge" style="background: rgba(255,255,255,0.2); font-weight: normal;">
                                Total: <?= number_format($total_leads) ?>
                            </span>
                        </h3>
                    </header>
                    <div class="panel-body" style="padding: 15px;">
                        <div id="morris-leads-status" style="height: 220px;"></div>
                        <div class="row text-center m-t-sm" style="font-size: 11px; margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                            <div class="col-xs-4" style="padding: 2px;">
                                <span style="color:#23b7e5; font-weight:600;"><i class="fa fa-circle"></i> Fresh</span>
                                <div class="font-bold h5 m0"><?= number_format($fresh) ?></div>
                            </div>
                            <div class="col-xs-4" style="padding: 2px;">
                                <span style="color:#27c24c; font-weight:600;"><i class="fa fa-circle"></i> Meeting</span>
                                <div class="font-bold h5 m0"><?= number_format($meeting_fixed) ?></div>
                            </div>
                            <div class="col-xs-4" style="padding: 2px;">
                                <span style="color:#7266ba; font-weight:600;"><i class="fa fa-circle"></i> Forward</span>
                                <div class="font-bold h5 m0"><?= number_format($forward_new) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LEADS MONTHLY BAR CHART -->
            <div class="col-md-7 mt-lg">
                <div class="panel panel-custom">
                    <header class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-bar-chart text-primary"></i> Monthly Leads Trend (Last 6 Months)
                        </h3>
                    </header>
                    <div class="panel-body" style="padding: 15px;">
                        <div id="morris-leads-monthly" style="height: 260px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- LEADS TOP 5 BOX -->
            <div class="col-md-6 mt-lg">
                <div class="panel panel-custom menu">
                    <header class="panel-heading mb0">
                        <h3 class="panel-title">
                            Leads | <a class="text-sm" target="_blank" href="<?= base_url('admin/leads') ?>">Top 5 Leads</a>
                        </h3>
                    </header>
                    <div class="table-responsive">
                        <table class="table todo-preview table-striped m-b-none text-sm items">
                            <thead>
                                <tr>
                                    <th>Contact Name</th>
                                    <th>Phone</th>
                                    <th>City</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($latest_leads)) {
                                    foreach ($latest_leads as $lead) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($lead->contact_name) ?></td>
                                            <td><?= htmlspecialchars($lead->phone) ?></td>
                                            <td><?= htmlspecialchars($lead->city) ?></td>
                                            <td><?= !empty($lead->lead_type) ? htmlspecialchars($lead->lead_type) : 'N/A' ?></td>
                                            <td><?= !empty($lead->created_time) && $lead->created_time != '0000-00-00 00:00:00' ? display_datetime($lead->created_time) : 'NA' ?></td>
                                        </tr>
                                <?php }
                                } else { ?>
                                    <tr><td colspan="5" class="text-center">No leads found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- REMINDER LATEST REMINDER BOX (FOR LEADS USERS) -->
            <div class="col-md-6 mt-lg">
                <div class="panel panel-custom menu">
                    <header class="panel-heading mb0">
                        <h3 class="panel-title">
                            Reminder | <a class="text-sm" target="_blank" href="<?= base_url('admin/leads/reminder_list') ?>">Latest Reminder</a>
                        </h3>
                    </header>
                    <div class="table-responsive">
                        <table class="table todo-preview table-striped m-b-none text-sm items">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Remind</th>
                                    <th>Notified</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leads_reminders)) {
                                    foreach ($leads_reminders as $reminder) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reminder->description) ?></td>
                                            <td><?= display_datetime($reminder->date) ?></td>
                                            <td><?= !empty($reminder->assigned_user) ? htmlspecialchars($reminder->assigned_user) : '-' ?></td>
                                            <td>
                                                <?= ($reminder->notified == 1) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>' ?>
                                            </td>
                                        </tr>
                                <?php }
                                } else { ?>
                                    <tr><td colspan="4" class="text-center">No reminders found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM METRICS STATS BAR (LEADS) -->
        <div class="clearfix visible-sm-block"></div>
        <section class="panel mt-lg">
            <footer class="panel-footer bg-white no-padder">
                <div class="row text-center no-gutter">
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $total_leads ?></span>
                        <small class="text-muted m-b block">Total Leads</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $dead_leads ?></span>
                        <small class="text-muted m-b block">Dead</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $forward_new ?></span>
                        <small class="text-muted m-b block">Forword</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $fresh ?></span>
                        <small class="text-muted m-b block">Fresh</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $vendor ?></span>
                        <small class="text-muted m-b block">Vendor</small>
                    </div>
                    <div class="col-xs-2">
                        <span class="h4 font-bold m-t block"><?= $meeting_fixed ?></span>
                        <small class="text-muted m-b block">Meeting Fixed</small>
                    </div>
                </div>
            </footer>
        </section>
    </div>
    <?php endif; ?>

    <!-- ============================================================= -->
    <!-- CLIENT DASHBOARD SECTION -->
    <!-- ============================================================= -->
    <?php if ($has_client_perm): ?>
    <div id="section_client_dashboard" class="dashboard-section" style="<?= ($active_view !== 'client') ? 'display:none;' : '' ?>">
        <!-- CLIENT VISUAL CHARTS -->
        <div class="row">
            <!-- HANDOVER REQUEST DONUT CHART -->
            <div class="col-md-5 mt-lg">
                <div class="panel panel-custom">
                    <header class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-pie-chart text-success"></i> Handover Requests Status
                            <span class="pull-right badge" style="background: rgba(255,255,255,0.2); font-weight: normal;">
                                Total: <?= ($handover_approved + $handover_pending + $handover_rejected) ?>
                            </span>
                        </h3>
                    </header>
                    <div class="panel-body" style="padding: 15px;">
                        <div id="morris-client-handover" style="height: 220px;"></div>
                        <div class="row text-center m-t-sm" style="font-size: 11px; margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                            <div class="col-xs-4" style="padding: 2px;">
                                <span style="color:#27c24c; font-weight:600;"><i class="fa fa-circle"></i> Approved</span>
                                <div class="font-bold h5 m0"><?= number_format($handover_approved) ?></div>
                            </div>
                            <div class="col-xs-4" style="padding: 2px;">
                                <span style="color:#ff902b; font-weight:600;"><i class="fa fa-circle"></i> Pending</span>
                                <div class="font-bold h5 m0"><?= number_format($handover_pending) ?></div>
                            </div>
                            <div class="col-xs-4" style="padding: 2px;">
                                <span style="color:#f05050; font-weight:600;"><i class="fa fa-circle"></i> Rejected</span>
                                <div class="font-bold h5 m0"><?= number_format($handover_rejected) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW CLIENTS MONTHLY BAR CHART -->
            <div class="col-md-7 mt-lg">
                <div class="panel panel-custom">
                    <header class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-bar-chart text-info"></i> New Clients Onboarded (Last 6 Months)
                            <span class="pull-right badge" style="background: rgba(255,255,255,0.2); font-weight: normal;">
                                Total: <?= number_format($total_clients) ?>
                            </span>
                        </h3>
                    </header>
                    <div class="panel-body" style="padding: 15px;">
                        <div id="morris-client-monthly" style="height: 260px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- CLIENT TOP 5 BOX -->
            <div class="col-md-6 mt-lg">
                <div class="panel panel-custom menu">
                    <header class="panel-heading mb0">
                        <h3 class="panel-title">
                            Client | <a class="text-sm" target="_blank" href="<?= base_url('admin/client/manage_client') ?>">Top 5 Clients</a>
                        </h3>
                    </header>
                    <div class="table-responsive">
                        <table class="table todo-preview table-striped m-b-none text-sm items">
                            <thead>
                                <tr>
                                    <th>Client ID</th>
                                    <th>Client Name</th>
                                    <th>City / Region</th>
                                    <th>Operation Status</th>
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($latest_clients)) {
                                    foreach ($latest_clients as $client) { 
                                        $region_or_city = !empty($client->lead_region) ? $client->lead_region : (!empty($client->city) ? $client->city : 'N/A');
                                        $status_badge = '<span class="label label-default">Active</span>';
                                        if ($client->operation_status === 'Hold') {
                                            $status_badge = '<span class="label label-danger">Hold</span>';
                                        } elseif ($client->operation_status === 'Unhold') {
                                            $status_badge = '<span class="label label-success">Unhold</span>';
                                        } elseif ($client->operation_status === 'Completed') {
                                            $status_badge = '<span class="label label-primary">Completed</span>';
                                        } elseif ($client->operation_status === 'Cancelled') {
                                            $status_badge = '<span class="label label-warning">Cancelled</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('admin/client/details/' . $client->client_id) ?>" target="_blank" class="text-info font-bold">
                                                    <?= !empty($client->client_sr_id) ? htmlspecialchars($client->client_sr_id) : '#' . $client->client_id ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($client->name) ?></td>
                                            <td><?= htmlspecialchars($region_or_city) ?></td>
                                            <td><?= $status_badge ?></td>
                                            <td><?= !empty($client->date_added) ? date('d M Y', strtotime($client->date_added)) : 'N/A' ?></td>
                                        </tr>
                                <?php }
                                } else { ?>
                                    <tr><td colspan="5" class="text-center">No clients found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- HANDOVER REQUEST BOX (IN PLACE OF REMINDER FOR CLIENT SECTION) -->
            <div class="col-md-6 mt-lg">
                <div class="panel panel-custom menu">
                    <header class="panel-heading mb0">
                        <h3 class="panel-title">
                            Handover Request | <a class="text-sm" target="_blank" href="<?= base_url('admin/client/manage_client') ?>">Latest Requests</a>
                            <span class="pull-right">
                                <span class="badge" style="background:#27c24c; font-size:11px; margin-right:4px;">Approved: <?= $handover_approved ?></span>
                                <span class="badge" style="background:#ff902b; font-size:11px;">Pending: <?= $handover_pending ?></span>
                            </span>
                        </h3>
                    </header>
                    <div class="table-responsive">
                        <table class="table todo-preview table-striped m-b-none text-sm items">
                            <thead>
                                <tr>
                                    <th>Client ID</th>
                                    <th>Client Name</th>
                                    <th>Handover Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($latest_handover_requests)) {
                                    foreach ($latest_handover_requests as $ho) { 
                                        $ho_status = trim($ho->hand_over_request_status ?? '');
                                        if ($ho_status === 'Approved' || $ho_status === 'Approve') {
                                            $ho_badge = '<span class="label label-success">Approved</span>';
                                        } elseif ($ho_status === 'Reject') {
                                            $ho_badge = '<span class="label label-danger">Reject</span>';
                                        } else {
                                            $ho_badge = '<span class="label label-warning">Pending</span>';
                                        }
                                        $ho_date = (!empty($ho->hand_over_request_date) && $ho->hand_over_request_date !== '0000-00-00')
                                            ? date('d M Y', strtotime($ho->hand_over_request_date))
                                            : 'NA';
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('admin/client/details/' . $ho->client_id) ?>" target="_blank" class="text-info font-bold">
                                                    <?= !empty($ho->client_sr_id) ? htmlspecialchars($ho->client_sr_id) : '#' . $ho->client_id ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($ho->name) ?></td>
                                            <td><?= $ho_date ?></td>
                                            <td><?= $ho_badge ?></td>
                                        </tr>
                                <?php }
                                } else { ?>
                                    <tr><td colspan="4" class="text-center">No Handover Requests found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM METRICS STATS BAR (CLIENT WITH HANDOVER APPROVED & PENDING) -->
        <div class="clearfix visible-sm-block"></div>
        <section class="panel mt-lg">
            <footer class="panel-footer bg-white no-padder">
                <div class="row text-center no-gutter">
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $total_clients ?></span>
                        <small class="text-muted m-b block">Total Clients</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $assigned_clients ?></span>
                        <small class="text-muted m-b block">My Assigned</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block" style="color:#27c24c;"><?= $handover_approved ?></span>
                        <small class="text-muted m-b block">Handover Approved</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block" style="color:#ff902b;"><?= $handover_pending ?></span>
                        <small class="text-muted m-b block">Handover Pending</small>
                    </div>
                    <div class="col-xs-2 b-r b-light">
                        <span class="h4 font-bold m-t block"><?= $unhold_clients ?></span>
                        <small class="text-muted m-b block">Active (Unhold)</small>
                    </div>
                    <div class="col-xs-2">
                        <span class="h4 font-bold m-t block"><?= $hold_clients ?></span>
                        <small class="text-muted m-b block">Hold</small>
                    </div>
                </div>
            </footer>
        </section>
    </div>
    <?php endif; ?>

    <?php if (!$has_leads_perm && !$has_client_perm): ?>
        <div class="alert alert-info text-center" style="margin-top: 30px;">
            <i class="fa fa-info-circle fa-2x m-b-sm"></i>
            <p><strong>Welcome to HC Interior Portal</strong></p>
            <p class="text-muted">You do not have access to Leads or Client dashboard metrics. Please contact Administrator for permissions.</p>
        </div>
    <?php endif; ?>

</div>

<!-- Chart Dependencies -->
<script src="<?= base_url() ?>assets/plugins/raphael/raphael.min.js"></script>
<script src="<?= base_url() ?>assets/plugins/morris/morris.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    var clientChartsInit = false;
    var leadsChartsInit = false;

    function initVisibleCharts() {
        <?php if ($has_client_perm): ?>
        var $cSec = $('#section_client_dashboard');
        if ($cSec.is(':visible')) {
            if (!clientChartsInit) {
                // 1. Handover Donut Chart
                var hoData = [
                    <?php if (($handover_approved + $handover_pending + $handover_rejected) > 0): ?>
                        { label: 'Approved', value: <?= (int)$handover_approved ?> },
                        { label: 'Pending', value: <?= (int)$handover_pending ?> },
                        { label: 'Rejected', value: <?= (int)$handover_rejected ?> }
                    <?php else: ?>
                        { label: 'No Requests', value: 1 }
                    <?php endif; ?>
                ];
                var hoColors = [
                    <?php if (($handover_approved + $handover_pending + $handover_rejected) > 0): ?>
                        '#27c24c', '#ff902b', '#f05050'
                    <?php else: ?>
                        '#cbd5e1'
                    <?php endif; ?>
                ];

                if ($('#morris-client-handover').length) {
                    window.clientHoChart = new Morris.Donut({
                        element: 'morris-client-handover',
                        data: hoData,
                        colors: hoColors,
                        resize: true,
                        formatter: function(y, data) {
                            return y;
                        }
                    });
                }

                // 2. Client Monthly Bar Chart
                var clientMonthlyData = <?= json_encode(!empty($client_monthly_data) ? array_map(function($r) {
                    return ['period' => $r['m_label'], 'clients' => (int)$r['m_count']];
                }, $client_monthly_data) : [['period' => date('M Y'), 'clients' => 0]]) ?>;

                if ($('#morris-client-monthly').length) {
                    window.clientMonthlyChart = new Morris.Bar({
                        element: 'morris-client-monthly',
                        data: clientMonthlyData,
                        xkey: 'period',
                        ykeys: ['clients'],
                        labels: ['Clients Added'],
                        barColors: ['#23b7e5'],
                        resize: true,
                        hideHover: 'auto',
                        gridTextFamily: 'Inter, sans-serif',
                        gridTextColor: '#64748b'
                    });
                }
                clientChartsInit = true;
            } else {
                if (window.clientHoChart && typeof window.clientHoChart.redraw === 'function') {
                    window.clientHoChart.redraw();
                }
                if (window.clientMonthlyChart && typeof window.clientMonthlyChart.redraw === 'function') {
                    window.clientMonthlyChart.redraw();
                }
            }
        }
        <?php endif; ?>

        <?php if ($has_leads_perm): ?>
        var $lSec = $('#section_leads_dashboard');
        if ($lSec.is(':visible')) {
            if (!leadsChartsInit) {
                // 1. Leads Status Donut Chart
                var leadsData = [
                    <?php if (($fresh + $meeting_fixed + $forward_new + $dead_leads + $vendor) > 0): ?>
                        { label: 'Fresh', value: <?= (int)$fresh ?> },
                        { label: 'Meeting Fixed', value: <?= (int)$meeting_fixed ?> },
                        { label: 'Forward', value: <?= (int)$forward_new ?> },
                        { label: 'Dead', value: <?= (int)$dead_leads ?> },
                        { label: 'Vendor', value: <?= (int)$vendor ?> }
                    <?php else: ?>
                        { label: 'No Leads', value: 1 }
                    <?php endif; ?>
                ];
                var leadsColors = [
                    <?php if (($fresh + $meeting_fixed + $forward_new + $dead_leads + $vendor) > 0): ?>
                        '#23b7e5', '#27c24c', '#7266ba', '#f05050', '#ff902b'
                    <?php else: ?>
                        '#cbd5e1'
                    <?php endif; ?>
                ];

                if ($('#morris-leads-status').length) {
                    window.leadsStatusChart = new Morris.Donut({
                        element: 'morris-leads-status',
                        data: leadsData,
                        colors: leadsColors,
                        resize: true,
                        formatter: function(y, data) {
                            return y;
                        }
                    });
                }

                // 2. Leads Monthly Bar Chart
                var leadsMonthlyData = <?= json_encode(!empty($leads_monthly_data) ? array_map(function($r) {
                    return ['period' => $r['m_label'], 'leads' => (int)$r['m_count']];
                }, $leads_monthly_data) : [['period' => date('M Y'), 'leads' => 0]]) ?>;

                if ($('#morris-leads-monthly').length) {
                    window.leadsMonthlyChart = new Morris.Bar({
                        element: 'morris-leads-monthly',
                        data: leadsMonthlyData,
                        xkey: 'period',
                        ykeys: ['leads'],
                        labels: ['Total Leads'],
                        barColors: ['#5d9cec'],
                        resize: true,
                        hideHover: 'auto',
                        gridTextFamily: 'Inter, sans-serif',
                        gridTextColor: '#64748b'
                    });
                }
                leadsChartsInit = true;
            } else {
                if (window.leadsStatusChart && typeof window.leadsStatusChart.redraw === 'function') {
                    window.leadsStatusChart.redraw();
                }
                if (window.leadsMonthlyChart && typeof window.leadsMonthlyChart.redraw === 'function') {
                    window.leadsMonthlyChart.redraw();
                }
            }
        }
        <?php endif; ?>
    }

    // Tab Switcher with Chart Redraw
    $('.tab-switch-btn').click(function() {
        $('.tab-switch-btn').removeClass('btn-primary active').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('btn-primary active');
        var targetSection = $(this).data('target');
        $('.dashboard-section').hide();
        $(targetSection).fadeIn(150, function() {
            setTimeout(function() {
                initVisibleCharts();
                $(window).trigger('resize');
            }, 50);
        });
    });

    // Initialize charts for currently active section
    setTimeout(function() {
        initVisibleCharts();
        $(window).trigger('resize');
    }, 100);
});
</script>