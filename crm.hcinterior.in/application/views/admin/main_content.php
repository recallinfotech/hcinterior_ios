<?php
echo message_box('success');
echo message_box('error');
?>

<div class="dashboard">

    <?php
    $CI = &get_instance();
    $CI->load->database();

    $user_id = $CI->session->userdata('user_id');
    $is_admin = ($CI->session->userdata('user_type') == 1);

    // -------------------------------------------------------------
    // 1. OPTIMIZED TOP 5 LEADS QUERY (Single JOIN Query)
    // -------------------------------------------------------------
    $CI->db->select('l.contact_name, l.phone, l.city, l.created_time, s.lead_type');
    $CI->db->from('tbl_leads l');
    $CI->db->join('tbl_lead_status s', 's.lead_status_id = l.lead_status_id', 'left');

    if (!$is_admin) {
        $CI->db->like('l.permission', '"' . $user_id . '"');
    }

    $CI->db->order_by('l.leads_id', 'DESC');
    $CI->db->limit(5);
    $latest_leads = $CI->db->get()->result();

    // -------------------------------------------------------------
    // 2. OPTIMIZED LATEST REMINDERS QUERY (Single JOIN Query)
    // -------------------------------------------------------------
    $CI->db->select('r.reminder_id, r.description, r.date, r.notified, r.user_id, u.username as assigned_user');
    $CI->db->from('tbl_reminders r');
    $CI->db->join('tbl_users u', 'u.user_id = r.user_id', 'left');
    $CI->db->where('r.module', 'leads');

    if (!$is_admin) {
        $CI->db->where('r.user_id', $user_id);
    }

    $CI->db->order_by('r.reminder_id', 'DESC');
    $CI->db->limit(5);
    $reminders = $CI->db->get()->result();

    // -------------------------------------------------------------
    // 3. OPTIMIZED METRICS COUNT QUERY (Single Aggregation Query)
    // -------------------------------------------------------------
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
    ?>

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

        <!-- REMINDER LATEST REMINDER BOX -->
        <div class="col-md-6 mt-lg">
            <div class="panel panel-custom menu">
                <header class="panel-heading mb0">
                    <h3 class="panel-title">
                        Reminder | <a class="text-sm" target="_blank" href="#">Latest Reminder</a>
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
                            <?php if (!empty($reminders)) {
                                foreach ($reminders as $reminder) { ?>
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

    <!-- BOTTOM METRICS STATS BAR -->
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