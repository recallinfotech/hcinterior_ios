<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reminder_cron extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        // login check bypass
        $this->session->set_userdata('logged_in', TRUE);
        if (!$this->session->userdata('user_id')) {
            $this->session->set_userdata('user_id', 5);
        }

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

    public function send_reminder_emails()
    {

        // Load necessary models
        $this->load->database();
        echo date('Y-m-d H:i:s');
        // Step 1: Get overdue reminders (30 mins past and not notified)
        $this->db->where('notified !=', 'Yes'); // <- important line
        $this->db->where('DATE_ADD(date, INTERVAL 30 MINUTE) <=', date('Y-m-d H:i:s'));
        $reminders = $this->db->get('tbl_reminders')->result();

        foreach ($reminders as $reminder) {
            // Step 2: Get user details from tbl_account_details
            $this->db->where('user_id', $reminder->created_by);
            $user = $this->db->get('tbl_account_details')->row();


            if ($user) {

                $this->db->select('phone, contact_name');
                $this->db->from('tbl_leads');
                $this->db->like('permission', '"' . $user->user_id . '"');
                $lead = $this->db->get()->row();


                // Step 3: Get regional_manager and team_leader emails from tbl_users
                $emails = [];

                // if ($user->regional_manager_id) {
                // $this->db->where('user_id', $user->regional_manager_id);
                // $rm = $this->db->get('tbl_users')->row();
                // if ($rm) $emails[] = $rm->email;
                // }

                // if ($user->team_leader_id) {
                // $this->db->where('user_id', $user->team_leader_id);
                // $tl = $this->db->get('tbl_users')->row();
                // if ($tl) $emails[] = $tl->email;
                // }

                if ($user->user_id) {
                    $this->db->where('user_id', $user->user_id);
                    $own = $this->db->get('tbl_users')->row();
                    if ($own) $emails[] = $own->email;
                }


                // Step 4: Send email if emails exist
                if (!empty($emails)) {

                    $lead_name = $lead->contact_name ?? 'N/A';
                    $lead_phone = $lead->phone ?? 'N/A';
                    $description = $reminder->description ?? 'No description';
                    $reminder_id = $reminder->reminder_id ?? 'No description';
                    print_r($reminder_id);
                    echo '<hr>';

                    $subject = "Followup Reminder Missed by {$user->fullname} ";
                    $message = "User: {$user->fullname} missed a reminder:<br><strong>{$description}</strong><br>Date: {$reminder->date} of {$lead_name} ({$lead_phone})";

                    foreach ($emails as $email) {
                        $params['subject'] = $subject;
                        $params['message'] = $message;
                        $params['resourceed_file'] = '';
                        $params['recipient'] = $email;
                        // $this->email->set_mailtype("html"); // Ensure HTML email
                        $this->settings_model->send_email($params, true);
                        echo $email;
                        echo '<br>';
                        echo 'Mail Sent';


                        $user_info = $this->db->select('tbl_users.fcm_token, tbl_users.device_type, tbl_account_details.fullname')

                            ->from('tbl_users')
                            ->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left')
                            ->where('tbl_users.email', $email)
                            ->get()
                            ->row();

                        $response = $this->notification_lib->send_app_notification(
                            "$subject",
                            " ",
                            "https://hcinterior.in/static-images/promo.jpg",
                            $user_info->fcm_token,
                            $user_info->device_type   // android | ios
                        );
                    }
                }

                // Step 5: Mark reminder as notified
                $this->db->where('reminder_id', $reminder->reminder_id);
                $this->db->update('tbl_reminders', ['notified' => 'Yes']);
            }
        }

        echo "Checked and processed reminders.";
    }


    public function send_reminder_emails_after_90_minutes()
    {

        // Load necessary models
        $this->load->database();
        echo date('Y-m-d H:i:s');
        // Step 1: Get overdue reminders (30 mins past and not notified)
        $this->db->where('after_notify !=', 'Yes'); // <- important line
        $this->db->where('DATE_ADD(date, INTERVAL 90 MINUTE) <=', date('Y-m-d H:i:s'));
        $reminders = $this->db->get('tbl_reminders')->result();

        foreach ($reminders as $reminder) {
            // Step 2: Get user details from tbl_account_details
            $this->db->where('user_id', $reminder->created_by);
            $user = $this->db->get('tbl_account_details')->row();


            if ($user) {

                $this->db->select('phone, contact_name');
                $this->db->from('tbl_leads');
                $this->db->like('permission', '"' . $user->user_id . '"');
                $lead = $this->db->get()->row();


                // Step 3: Get regional_manager and team_leader emails from tbl_users
                $emails = [];

                if ($user->regional_manager_id) {
                    $this->db->where('user_id', $user->regional_manager_id);
                    $rm = $this->db->get('tbl_users')->row();
                    if ($rm) $emails[] = $rm->email;
                }

                if ($user->team_leader_id) {
                    $this->db->where('user_id', $user->team_leader_id);
                    $tl = $this->db->get('tbl_users')->row();
                    if ($tl) $emails[] = $tl->email;
                }

                if ($user->user_id) {
                    $this->db->where('user_id', $user->user_id);
                    $own = $this->db->get('tbl_users')->row();

                    if ($own) $emails[] = $own->email;
                }

                $emails[] = "vinitasingh@hcinterior.in";
                // Step 4: Send email if emails exist
                if (!empty($emails)) {

                    $lead_name = $lead->contact_name ?? 'N/A';
                    $lead_phone = $lead->phone ?? 'N/A';
                    $description = $reminder->description ?? 'No description';
                    $reminder_id = $reminder->reminder_id ?? 'No description';
                    print_r($reminder_id);
                    echo '<hr>';

                    $subject = "Followup Reminder Missed by {$user->fullname} ";
                    $message = "User: {$user->fullname} missed a reminder:<br><strong>{$description}</strong><br>Date: {$reminder->date} of {$lead_name} ({$lead_phone})";

                    foreach ($emails as $email) {
                        $params['subject'] = $subject;
                        $params['message'] = $message;
                        $params['resourceed_file'] = '';
                        $params['recipient'] = $email;
                        //$this->email->set_mailtype("html"); // Ensure HTML email
                        $this->settings_model->send_email($params, true);
                        echo $email;
                        echo '<br>';
                        echo 'Mail Sent';

                        $user_info = $this->db->select('tbl_users.fcm_token, tbl_users.device_type, tbl_account_details.fullname')
                            ->from('tbl_users')
                            ->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left')
                            ->where('tbl_users.email', $email)
                            ->get()
                            ->row();

                        $response = $this->notification_lib->send_app_notification(
                            "$subject",
                            " ",
                            "https://hcinterior.in/static-images/promo.jpg",
                            $user_info->fcm_token,
                            $user_info->device_type   // android | ios
                        );
                    }
                }

                // Step 5: Mark reminder as notified
                $this->db->where('reminder_id', $reminder->reminder_id);
                $this->db->update('tbl_reminders', ['after_notify' => 'Yes']);
            }
        }

        echo "Checked and processed reminders.";
    }

    public function send_reminder_emails_before_15_minutes()
    {

        // Load necessary models
        $this->load->database();
        echo date('Y-m-d H:i:s');
        // Step 1: Get overdue reminders (30 mins past and not notified)
        $this->db->where('before_notify !=', 'Yes'); // <- important line
        $this->db->where('DATE_SUB(date, INTERVAL 15 MINUTE) <=', date('Y-m-d H:i:s'));
        $reminders = $this->db->get('tbl_reminders')->result();


        print_r($reminders);
        foreach ($reminders as $reminder) {

            // Step 2: Get user details from tbl_account_details
            $this->db->where('user_id', $reminder->created_by);
            $user = $this->db->get('tbl_account_details')->row();

            if ($user) {

                $this->db->select('phone, contact_name');
                $this->db->from('tbl_leads');
                $this->db->like('permission', '"' . $user->user_id . '"');
                $lead = $this->db->get()->row();



                // Step 3: Get regional_manager and team_leader emails from tbl_users
                $emails = [];

                // if ($user->regional_manager_id) {
                // $this->db->where('user_id', $user->regional_manager_id);
                // $rm = $this->db->get('tbl_users')->row();
                // if ($rm) $emails[] = $rm->email;
                // }

                // if ($user->team_leader_id) {
                // $this->db->where('user_id', $user->team_leader_id);
                // $tl = $this->db->get('tbl_users')->row();
                // if ($tl) $emails[] = $tl->email;
                // }

                if ($user->user_id) {
                    $this->db->where('user_id', $user->user_id);
                    $own = $this->db->get('tbl_users')->row();

                    if ($own) $emails[] = $own->email;
                }


                // Step 4: Send email if emails exist
                if (!empty($emails)) {

                    $lead_name = $lead->contact_name ?? 'N/A';
                    $lead_phone = $lead->phone ?? 'N/A';
                    $description = $reminder->description ?? 'No description';
                    $reminder_id = $reminder->reminder_id ?? 'No description';
                    print_r($reminder_id);
                    echo '<hr>';

                    $subject = "Followup Reminder by  {$reminder->date} ";
                    $message = "User: {$user->fullname} have a reminder: <br><strong>{$description}</strong><br>Date: {$reminder->date} of {$lead_name} ({$lead_phone})";

                    foreach ($emails as $email) {
                        $params['subject'] = $subject;
                        $params['message'] = $message;
                        $params['resourceed_file'] = '';
                        $params['recipient'] = $email;
                        //  $this->email->set_mailtype("html"); // Ensure HTML email
                        //  $this->settings_model->send_email($params, true);
                        echo $email;
                        echo '<br>';
                        echo 'Mail Sent';


                        $user_info = $this->db->select('tbl_users.fcm_token, tbl_users.device_type, tbl_account_details.fullname')
                            ->from('tbl_users')
                            ->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left')
                            ->where('tbl_users.email', $email)
                            ->get()
                            ->row();

                        $response = $this->notification_lib->send_app_notification(
                            "$subject",
                            " ",
                            "https://hcinterior.in/static-images/promo.jpg",
                            $user_info->fcm_token,
                            $user_info->device_type   // android | ios
                        );
                    }
                }

                // Step 5: Mark reminder as notified
                $this->db->where('reminder_id', $reminder->reminder_id);
                $this->db->update('tbl_reminders', ['before_notify' => 'Yes']);
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
     * Core processor for Timeline Reminders:
     * - 'today'    : Tasks due today
     * - 'upcoming' : Tasks due within next 2 days
     * - 'missed'   : Tasks overdue (past dates)
     * - 'all'      : Process all three categories
     */
    private function run_timeline_reminders($mode = 'all')
    {
        $this->load->database();
        if (!$this->session->userdata('user_id')) {
            $this->session->set_userdata('user_id', 5);
        }

        $today = date('Y-m-d');
        $upcoming_limit = date('Y-m-d', strtotime('+2 days'));

        echo "<h3>Timeline Reminders Check [" . strtoupper($mode) . "] - " . date('Y-m-d H:i:s') . "</h3>";

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

        $manual_dates = $this->db->get('timeline_manual_dates')->result();
        $total_notifs_sent = 0;
        $stats = [
            'upcoming' => 0,
            'today'    => 0,
            'missed'   => 0
        ];

        foreach ($manual_dates as $row) {
            $client_id = (int)$row->client_id;
            if (empty($client_id)) {
                continue;
            }

            // Fetch Client Info
            $client = $this->db->select('name, client_sr_id')->where('client_id', $client_id)->get('tbl_client')->row();
            $client_name = !empty($client->name) ? $client->name : 'Client';
            $client_sr_id = !empty($client->client_sr_id) ? $client->client_sr_id : 'ID:' . $client_id;

            foreach ($task_fields as $field => $title) {
                if (empty($row->$field)) {
                    continue;
                }

                $task_date_raw = trim($row->$field);
                if (empty($task_date_raw) || strtolower($task_date_raw) == 'na') {
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

                $task_target_date = date('Y-m-d', $ts_target);
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
                    // Future date beyond 2 days
                    continue;
                }

                // Check mode filter
                if ($mode !== 'all' && $mode !== $category) {
                    continue;
                }

                // Check if already "Done" in timeline_task_status
                $is_done = $this->db
                    ->where('client_id', $client_id)
                    ->where('task_name', $field)
                    ->where('status', 'Done')
                    ->count_all_results('timeline_task_status') > 0;

                if ($is_done) {
                    continue; // Done ho chuka hai, skip
                }

                $stats[$category]++;

                // Get all assigned users from tbl_client_assign_team
                $assigned_members = $this->db
                    ->select('DISTINCT(user_id) as user_id')
                    ->where('client_id', $client_id)
                    ->where('user_id >', 0)
                    ->get('tbl_client_assign_team')
                    ->result();

                // Recipient user IDs: Team members + Admin (user_id 5)
                $notify_user_ids = [];
                if (!empty($assigned_members)) {
                    foreach ($assigned_members as $member) {
                        $uid = (int)$member->user_id;
                        if ($uid > 0 && !in_array($uid, $notify_user_ids)) {
                            $notify_user_ids[] = $uid;
                        }
                    }
                }

                // Admin (user_id 5) ko bhi notification zaroor jana chahiye
                if (!in_array(5, $notify_user_ids)) {
                    $notify_user_ids[] = 5;
                }

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
                    // Aaj ke din duplicate notification na jaye
                    $already_notified = $this->db
                        ->where('to_user_id', $u_id)
                        ->where('value', $notif_value_key)
                        ->where('DATE(date)', $today)
                        ->count_all_results('tbl_notifications') > 0;

                    if ($already_notified) {
                        continue;
                    }

                    $sent = add_notification([
                        'to_user_id'   => $u_id,
                        'from_user_id' => 5, // Nishant Singh / Admin
                        'description'  => $message,
                        'link'         => $link,
                        'value'        => $notif_value_key,
                    ]);

                    if ($sent) {
                        $sent_count_for_task++;
                        $total_notifs_sent++;
                    }
                }

                $catColor = ($category == 'today') ? 'blue' : (($category == 'upcoming') ? 'purple' : 'green');
                echo "<p style='color:{$catColor};'>[{$category}] Client {$client_sr_id} ({$client_name}) - Task '{$title}' (Due: {$display_target_date}): Notification sent to {$sent_count_for_task} member(s).</p>";
            }
        }

        echo "<hr><p><strong>Summary:</strong> Upcoming: {$stats['upcoming']} | Today: {$stats['today']} | Missed: {$stats['missed']}. Total notifications sent today: {$total_notifs_sent}.</p>";
    }
}
