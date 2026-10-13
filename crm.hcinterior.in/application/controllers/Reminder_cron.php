<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reminder_cron extends MY_Controller
{
    // In-memory caches to avoid N+1 query loops
    private $user_account_cache = [];
    private $user_login_cache   = [];
    private $user_fcm_cache     = [];
    private $lead_cache         = [];

    public function __construct()
    {
        parent::__construct();

        // Prevent timeouts or memory exhaustion during large cron processing
        @ini_set('memory_limit', '256M');
        @set_time_limit(300);

        // login check bypass
        $this->session->set_userdata('logged_in', TRUE);
        if (!$this->session->userdata('user_id')) {
            $this->session->set_userdata('user_id', 5);
        }

        $this->load->database();
        $this->load->model('settings_model');
        $this->load->model('user_model');
        $this->load->library('notification_lib');

        date_default_timezone_set('Asia/Kolkata');
    }

    public function index()
    {
        $this->check_timeline_all_reminders();
    }

    public function test_mail()
    {
        $params = [];
        $params['subject'] = 'SMTP Setup Testing';
        $params['message'] = 'This is test SMTP email from Cron <br>' . date('Y-m-d H:i:s');
        $params['recipient'] = 'recallinfotech@gmail.com';
        $params['resourceed_file'] = '';

        if ($this->settings_model->send_email($params, true)) {
            echo "Mail Sent Successfully";
        } else {
            echo "Mail Failed";
        }
    }

    /**
     * Send reminder emails for overdue reminders (30+ mins past)
     */
    public function send_reminder_emails()
    {
        echo date('Y-m-d H:i:s') . "<br>";

        // Step 1: Optimized query using index on `date` without wrapping column in SQL functions
        $target_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        $reminders = $this->db
            ->where('date <=', $target_time)
            ->where('(notified != "Yes" OR notified IS NULL)', NULL, FALSE)
            ->get('tbl_reminders')
            ->result();

        if (empty($reminders)) {
            echo "Checked and processed reminders. (0 pending)";
            return;
        }

        foreach ($reminders as $reminder) {
            $user = $this->get_account_user($reminder->created_by);

            if ($user) {
                $lead = $this->get_lead_info($reminder, $user->user_id);

                $emails = [];
                if (!empty($user->user_id)) {
                    $own = $this->get_user_login_row($user->user_id);
                    if ($own && !empty($own->email)) {
                        $emails[] = $own->email;
                    }
                }

                // Step 4: Send email & FCM notification if emails exist
                if (!empty($emails)) {
                    $lead_name   = $lead->contact_name ?? 'N/A';
                    $lead_phone  = $lead->phone ?? 'N/A';
                    $description = $reminder->description ?? 'No description';
                    $reminder_id = $reminder->reminder_id ?? 'N/A';

                    echo "Reminder ID: {$reminder_id}<br>";
                    echo '<hr>';

                    $subject = "Followup Reminder Missed by {$user->fullname} ";
                    $message = "User: {$user->fullname} missed a reminder:<br><strong>{$description}</strong><br>Date: {$reminder->date} of {$lead_name} ({$lead_phone})";

                    foreach ($emails as $email) {
                        $params = [
                            'subject'         => $subject,
                            'message'         => $message,
                            'resourceed_file' => '',
                            'recipient'       => $email
                        ];
                        $this->settings_model->send_email($params, true);
                        echo "{$email} <br>Mail Sent<br>";

                        $user_info = $this->get_user_fcm_info($email);
                        if ($user_info && !empty($user_info->fcm_token)) {
                            $this->notification_lib->send_app_notification(
                                $subject,
                                " ",
                                "https://hcinterior.in/static-images/promo.jpg",
                                $user_info->fcm_token,
                                $user_info->device_type // android | ios
                            );
                        }
                    }
                }

                // Step 5: Mark reminder as notified
                $this->db->where('reminder_id', $reminder->reminder_id);
                $this->db->update('tbl_reminders', ['notified' => 'Yes']);
            }
        }

        echo "Checked and processed reminders.";
    }

    /**
     * Send reminder emails after 90 minutes overdue
     */
    public function send_reminder_emails_after_90_minutes()
    {
        echo date('Y-m-d H:i:s') . "<br>";

        // Step 1: Optimized query using index on `date`
        $target_time = date('Y-m-d H:i:s', strtotime('-90 minutes'));
        $reminders = $this->db
            ->where('date <=', $target_time)
            ->where('(after_notify != "Yes" OR after_notify IS NULL)', NULL, FALSE)
            ->get('tbl_reminders')
            ->result();

        if (empty($reminders)) {
            echo "Checked and processed reminders. (0 pending)";
            return;
        }

        foreach ($reminders as $reminder) {
            $user = $this->get_account_user($reminder->created_by);

            if ($user) {
                $lead = $this->get_lead_info($reminder, $user->user_id);

                $emails = [];
                if (!empty($user->regional_manager_id)) {
                    $rm = $this->get_user_login_row($user->regional_manager_id);
                    if ($rm && !empty($rm->email)) {
                        $emails[] = $rm->email;
                    }
                }

                if (!empty($user->team_leader_id)) {
                    $tl = $this->get_user_login_row($user->team_leader_id);
                    if ($tl && !empty($tl->email)) {
                        $emails[] = $tl->email;
                    }
                }

                if (!empty($user->user_id)) {
                    $own = $this->get_user_login_row($user->user_id);
                    if ($own && !empty($own->email)) {
                        $emails[] = $own->email;
                    }
                }

                $emails[] = "vinitasingh@hcinterior.in";
                $emails = array_unique(array_filter($emails));

                // Step 4: Send email & FCM notification if emails exist
                if (!empty($emails)) {
                    $lead_name   = $lead->contact_name ?? 'N/A';
                    $lead_phone  = $lead->phone ?? 'N/A';
                    $description = $reminder->description ?? 'No description';
                    $reminder_id = $reminder->reminder_id ?? 'N/A';

                    echo "Reminder ID: {$reminder_id}<br>";
                    echo '<hr>';

                    $subject = "Followup Reminder Missed by {$user->fullname} ";
                    $message = "User: {$user->fullname} missed a reminder:<br><strong>{$description}</strong><br>Date: {$reminder->date} of {$lead_name} ({$lead_phone})";

                    foreach ($emails as $email) {
                        $params = [
                            'subject'         => $subject,
                            'message'         => $message,
                            'resourceed_file' => '',
                            'recipient'       => $email
                        ];
                        $this->settings_model->send_email($params, true);
                        echo "{$email} <br>Mail Sent<br>";

                        $user_info = $this->get_user_fcm_info($email);
                        if ($user_info && !empty($user_info->fcm_token)) {
                            $this->notification_lib->send_app_notification(
                                $subject,
                                " ",
                                "https://hcinterior.in/static-images/promo.jpg",
                                $user_info->fcm_token,
                                $user_info->device_type
                            );
                        }
                    }
                }

                // Step 5: Mark reminder as notified
                $this->db->where('reminder_id', $reminder->reminder_id);
                $this->db->update('tbl_reminders', ['after_notify' => 'Yes']);
            }
        }

        echo "Checked and processed reminders.";
    }

    /**
     * Send reminder emails before 15 minutes
     */
    public function send_reminder_emails_before_15_minutes()
    {
        echo date('Y-m-d H:i:s') . "<br>";

        // Step 1: Optimized query using index on `date`
        $target_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $reminders = $this->db
            ->where('date <=', $target_time)
            ->where('(before_notify != "Yes" OR before_notify IS NULL)', NULL, FALSE)
            ->get('tbl_reminders')
            ->result();

        if (!empty($reminders)) {
            foreach ($reminders as $reminder) {
                $user = $this->get_account_user($reminder->created_by);

                if ($user) {
                    $lead = $this->get_lead_info($reminder, $user->user_id);

                    $emails = [];
                    if (!empty($user->user_id)) {
                        $own = $this->get_user_login_row($user->user_id);
                        if ($own && !empty($own->email)) {
                            $emails[] = $own->email;
                        }
                    }

                    if (!empty($emails)) {
                        $lead_name   = $lead->contact_name ?? 'N/A';
                        $lead_phone  = $lead->phone ?? 'N/A';
                        $description = $reminder->description ?? 'No description';
                        $reminder_id = $reminder->reminder_id ?? 'N/A';

                        echo "Reminder ID: {$reminder_id}<br>";
                        echo '<hr>';

                        $subject = "Followup Reminder by {$reminder->date} ";

                        foreach ($emails as $email) {
                            $user_info = $this->get_user_fcm_info($email);
                            if ($user_info && !empty($user_info->fcm_token)) {
                                $this->notification_lib->send_app_notification(
                                    $subject,
                                    " ",
                                    "https://hcinterior.in/static-images/promo.jpg",
                                    $user_info->fcm_token,
                                    $user_info->device_type
                                );
                                echo "{$email} <br>Push Notification Sent<br>";
                            }
                        }
                    }

                    // Step 5: Mark reminder as notified
                    $this->db->where('reminder_id', $reminder->reminder_id);
                    $this->db->update('tbl_reminders', ['before_notify' => 'Yes']);
                }
            }
        }

        echo "Checked and processed reminders.<br>";
        $this->check_timeline_all_reminders();
    }

    /**
     * Timeline Reminders for TODAY
     */
    public function check_timeline_today_reminders()
    {
        $this->run_timeline_reminders('today');
    }

    /**
     * Timeline Reminders for UPCOMING tasks (next 2 days)
     */
    public function check_timeline_upcoming_reminders()
    {
        $this->run_timeline_reminders('upcoming');
    }

    /**
     * Timeline Reminders for MISSED / OVERDUE tasks
     */
    public function check_timeline_missed_reminders()
    {
        $this->run_timeline_reminders('missed');
    }

    /**
     * Run ALL 3 categories: Upcoming, Today, Missed
     */
    public function check_timeline_all_reminders()
    {
        $this->run_timeline_reminders('all');
    }

    /**
     * Alias for backward compatibility
     */
    public function check_timeline_overdue_reminders()
    {
        $this->run_timeline_reminders('all');
    }

    /**
     * Highly Optimized Core Processor for Timeline Reminders:
     * - Eliminates 20,000+ N+1 queries by preloading all relations in 5 bulk queries
     * - In-memory O(1) hash maps for 'Done' tasks, assigned teams, and today's notifications
     * - Batch inserts all notifications in chunks
     */
    private function run_timeline_reminders($mode = 'all')
    {
        $today          = date('Y-m-d');
        $upcoming_limit = date('Y-m-d', strtotime('+2 days'));
        $now_datetime   = date('Y-m-d H:i:s');

        echo "<h3>Timeline Reminders Check [" . strtoupper($mode) . "] - " . $now_datetime . "</h3>";

        $task_fields = [
            'designer_site_visit_date' => 'Designer Site Visit Date',
            'site_start_date'          => 'Site Work Start Date',
            'factory_start_date'       => 'Factory Work Start Date',
            'design_presentation'      => 'Design Presentation (PPT)',
            'design_finalization'      => 'Design Finalization',
            'wall_design'              => 'Wall Design',
            'material_selection'       => 'Material Selection',
            'three_d_design'           => '3D Design',
            'site_validation'          => 'Site Validation',
            'washroom_plumbing'        => 'Washroom Plumbing & Drawings',
            'correction_drawing'       => 'Correction Drawing',
            'production_drawing'       => 'Production Drawing',
            'dispatch_date'            => 'Dispatch Date',
            'mom_checklist'            => 'MOM Checklist',
            'wall_electrical_drawing'  => 'Wall Electrical Drawing'
        ];

        // 1. Bulk Fetch: `timeline_manual_dates` with `tbl_client` JOINed (1 single query instead of N queries)
        $manual_dates = $this->db
            ->select('tmd.*, c.name AS client_name, c.client_sr_id')
            ->from('timeline_manual_dates tmd')
            ->join('tbl_client c', 'c.client_id = tmd.client_id', 'left')
            ->get()
            ->result();

        if (empty($manual_dates)) {
            echo "<hr><p><strong>Summary:</strong> No timeline records found.</p>";
            return;
        }

        // 2. Bulk Preload: All 'Done' task statuses (1 single query instead of thousands)
        $done_statuses = $this->db
            ->select('client_id, task_name')
            ->from('timeline_task_status')
            ->where('status', 'Done')
            ->get()
            ->result();

        $done_tasks_map = [];
        foreach ($done_statuses as $st) {
            $done_tasks_map[$st->client_id . '_' . $st->task_name] = true;
        }
        unset($done_statuses);

        // 3. Bulk Preload: All team member assignments (1 single query instead of thousands)
        $team_rows = $this->db
            ->select('client_id, user_id')
            ->from('tbl_client_assign_team')
            ->where('user_id >', 0)
            ->get()
            ->result();

        $client_team_map = [];
        foreach ($team_rows as $tr) {
            $cid = (int)$tr->client_id;
            $uid = (int)$tr->user_id;
            $client_team_map[$cid][$uid] = $uid;
        }
        unset($team_rows);

        // 4. Bulk Preload: Notifications sent today for timeline keys (1 indexed query instead of thousands)
        $today_start = $today . ' 00:00:00';
        $today_end   = $today . ' 23:59:59';
        $notif_rows  = $this->db
            ->select('to_user_id, value')
            ->from('tbl_notifications')
            ->where('date >=', $today_start)
            ->where('date <=', $today_end)
            ->like('value', 'timeline_', 'after')
            ->get()
            ->result();

        $notified_today_map = [];
        foreach ($notif_rows as $nr) {
            $notified_today_map[$nr->to_user_id . '_' . $nr->value] = true;
        }
        unset($notif_rows);

        // 5. Bulk Preload: Active users list to avoid querying tbl_users inside loops
        $active_users = $this->db
            ->select('user_id')
            ->from('tbl_users')
            ->where('activated', 1)
            ->get()
            ->result();

        $active_user_map = [];
        foreach ($active_users as $au) {
            $active_user_map[(int)$au->user_id] = true;
        }
        unset($active_users);

        // 6. Preload sender fullname for Admin (user_id 5)
        $admin_account = $this->db
            ->select('fullname')
            ->where('user_id', 5)
            ->get('tbl_account_details')
            ->row();
        $sender_name = !empty($admin_account->fullname) ? $admin_account->fullname : 'System';

        $total_notifs_sent = 0;
        $stats = [
            'upcoming' => 0,
            'today'    => 0,
            'missed'   => 0
        ];
        $new_notifications = [];

        // 7. Fast In-Memory Processing
        foreach ($manual_dates as $row) {
            $client_id = (int)$row->client_id;
            if (empty($client_id)) {
                continue;
            }

            $client_name  = !empty($row->client_name) ? $row->client_name : 'Client';
            $client_sr_id = !empty($row->client_sr_id) ? $row->client_sr_id : 'ID:' . $client_id;

            // Recipient user IDs: Team members + Admin (user_id 5)
            $assigned_members = isset($client_team_map[$client_id]) ? array_values($client_team_map[$client_id]) : [];
            $notify_user_ids = $assigned_members;
            if (!in_array(5, $notify_user_ids)) {
                $notify_user_ids[] = 5;
            }

            foreach ($task_fields as $field => $title) {
                if (empty($row->$field)) {
                    continue;
                }

                $task_date_raw = trim($row->$field);
                if (empty($task_date_raw) || strcasecmp($task_date_raw, 'na') === 0) {
                    continue;
                }

                // If range like "27-05-2026 - 29-05-2026", pick the last date
                if (preg_match_all('/\b\d{1,4}[-\/]\d{1,2}[-\/]\d{1,4}\b/', $task_date_raw, $matches) && !empty($matches[0])) {
                    $target_date_str = end($matches[0]);
                } else {
                    $target_date_str = $task_date_raw;
                }

                $ts_target = strtotime($target_date_str);
                if ($ts_target === false || $ts_target <= 0 || (int)date('Y', $ts_target) <= 2000) {
                    continue; // invalid or 1970 date
                }

                $task_target_date    = date('Y-m-d', $ts_target);
                $display_target_date = date('d-m-Y', $ts_target);

                // Determine Category: upcoming / today / missed
                $category = null;
                if ($task_target_date == $today) {
                    $category = 'today';
                } elseif ($task_target_date < $today) {
                    $category = 'missed';
                } elseif ($task_target_date > $today && $task_target_date <= $upcoming_limit) {
                    $category = 'upcoming';
                } else {
                    continue; // Future date beyond 2 days
                }

                // Check mode filter
                if ($mode !== 'all' && $mode !== $category) {
                    continue;
                }

                // Fast O(1) in-memory check if already "Done"
                if (isset($done_tasks_map[$client_id . '_' . $field])) {
                    continue;
                }

                $stats[$category]++;

                $link = 'admin/client/details/' . $client_id . '/timelines';

                // English messages based on Category
                if ($category == 'today') {
                    $notif_value_key = 'timeline_today_' . $client_id . '_' . $field;
                    $message = "Timeline Alert [Today]: Task '{$title}' is due TODAY ({$display_target_date}) for Client {$client_sr_id} ({$client_name}). Please ensure completion today.";
                } elseif ($category == 'upcoming') {
                    $days_left = (int) round((strtotime($task_target_date) - strtotime($today)) / 86400);
                    $when_text = ($days_left == 1) ? "TOMORROW ({$display_target_date})" : "in {$days_left} days ({$display_target_date})";
                    $notif_value_key = 'timeline_upcoming_' . $client_id . '_' . $field;
                    $message = "Timeline Alert [Upcoming]: Task '{$title}' is scheduled for {$when_text} for Client {$client_sr_id} ({$client_name}). Please prepare for completion.";
                } else { // missed
                    $days_overdue = (int) round((strtotime($today) - strtotime($task_target_date)) / 86400);
                    $notif_value_key = 'timeline_missed_' . $client_id . '_' . $field;
                    $message = "Timeline Alert [Missed]: Task '{$title}' is OVERDUE by {$days_overdue} day(s) for Client {$client_sr_id} ({$client_name}). Due Date was {$display_target_date}. Please take immediate action.";
                }

                $sent_count_for_task = 0;
                foreach ($notify_user_ids as $u_id) {
                    // Skip inactive or non-existent users
                    if (!isset($active_user_map[$u_id])) {
                        continue;
                    }

                    // Aaj ke din duplicate notification na jaye (Fast O(1) lookup)
                    if (isset($notified_today_map[$u_id . '_' . $notif_value_key])) {
                        continue;
                    }

                    // Mark as sent in memory map to prevent same-run duplicate
                    $notified_today_map[$u_id . '_' . $notif_value_key] = true;

                    $new_notifications[] = [
                        'to_user_id'   => $u_id,
                        'from_user_id' => 5,
                        'name'         => $sender_name,
                        'description'  => $message,
                        'link'         => $link,
                        'value'        => $notif_value_key,
                        'date'         => $now_datetime,
                        'read'         => 0,
                        'read_inline'  => 0,
                    ];

                    $sent_count_for_task++;
                    $total_notifs_sent++;
                }

                // If running specifically for missed timeline reminders, send email to darakshan@hcinterior.in
                if ($mode === 'missed' && $category === 'missed' && $sent_count_for_task > 0) {
                    $email_recipient = 'darakshan@hcinterior.in';
                    $email_subject   = "Timeline Missed Task Alert: {$title} - Client {$client_sr_id} ({$client_name})";
                    $email_message   = "Hello,<br><br>"
                        . "A timeline task is <strong>OVERDUE / MISSED</strong>:<br><br>"
                        . "<strong>Client:</strong> {$client_name} ({$client_sr_id})<br>"
                        . "<strong>Task:</strong> {$title}<br>"
                        . "<strong>Due Date:</strong> {$display_target_date}<br>"
                        . "<strong>Days Overdue:</strong> {$days_overdue} day(s)<br>"
                        . "<strong>Timeline URL:</strong> <a href='" . base_url($link) . "'>" . base_url($link) . "</a><br><br>"
                        . "Please review and take necessary action.";

                    $mail_params = [
                        'subject'         => $email_subject,
                        'message'         => $email_message,
                        'resourceed_file' => '',
                        'recipient'       => $email_recipient
                    ];

                    $this->settings_model->send_email($mail_params, true);
                    echo "<p style='color:darkred;'>[Missed Email] Sent to {$email_recipient} for Task '{$title}' (Client {$client_sr_id}).</p>";
                }

                $catColor = ($category == 'today') ? 'blue' : (($category == 'upcoming') ? 'purple' : 'green');
                echo "<p style='color:{$catColor};'>[{$category}] Client {$client_sr_id} ({$client_name}) - Task '{$title}' (Due: {$display_target_date}): Notification sent to {$sent_count_for_task} member(s).</p>";
            }
        }

        // 8. Batch Insert all generated notifications (Chunked by 200)
        if (!empty($new_notifications)) {
            foreach (array_chunk($new_notifications, 200) as $chunk) {
                $this->db->insert_batch('tbl_notifications', $chunk);
            }
        }

        echo "<hr><p><strong>Summary:</strong> Upcoming: {$stats['upcoming']} | Today: {$stats['today']} | Missed: {$stats['missed']}. Total notifications sent today: {$total_notifs_sent}.</p>";
    }

    // ==========================================
    // Caching Helpers for High-Performance Crons
    // ==========================================

    private function get_account_user($user_id)
    {
        $uid = (int)$user_id;
        if ($uid <= 0) {
            return null;
        }

        if (!array_key_exists($uid, $this->user_account_cache)) {
            $this->user_account_cache[$uid] = $this->db
                ->where('user_id', $uid)
                ->get('tbl_account_details')
                ->row();
        }

        return $this->user_account_cache[$uid];
    }

    private function get_user_login_row($user_id)
    {
        $uid = (int)$user_id;
        if ($uid <= 0) {
            return null;
        }

        if (!array_key_exists($uid, $this->user_login_cache)) {
            $this->user_login_cache[$uid] = $this->db
                ->where('user_id', $uid)
                ->get('tbl_users')
                ->row();
        }

        return $this->user_login_cache[$uid];
    }

    private function get_user_fcm_info($email)
    {
        $email = trim($email);
        if (empty($email)) {
            return null;
        }

        if (!array_key_exists($email, $this->user_fcm_cache)) {
            $this->user_fcm_cache[$email] = $this->db
                ->select('tbl_users.fcm_token, tbl_users.device_type, tbl_account_details.fullname')
                ->from('tbl_users')
                ->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left')
                ->where('tbl_users.email', $email)
                ->get()
                ->row();
        }

        return $this->user_fcm_cache[$email];
    }

    private function get_lead_info($reminder, $user_id)
    {
        // If reminder is linked to leads module directly
        if (!empty($reminder->module) && $reminder->module === 'leads' && !empty($reminder->module_id)) {
            $cache_key = 'module_' . $reminder->module_id;
            if (!array_key_exists($cache_key, $this->lead_cache)) {
                $this->lead_cache[$cache_key] = $this->db
                    ->select('phone, contact_name')
                    ->from('tbl_leads')
                    ->where('leads_id', $reminder->module_id)
                    ->get()
                    ->row();
            }
            return $this->lead_cache[$cache_key];
        }

        // Fallback: permission lookup by user_id
        $uid = (int)$user_id;
        $cache_key = 'user_' . $uid;
        if (!array_key_exists($cache_key, $this->lead_cache)) {
            $this->lead_cache[$cache_key] = $this->db
                ->select('phone, contact_name')
                ->from('tbl_leads')
                ->like('permission', '"' . $uid . '"')
                ->get()
                ->row();
        }

        return $this->lead_cache[$cache_key];
    }
}
