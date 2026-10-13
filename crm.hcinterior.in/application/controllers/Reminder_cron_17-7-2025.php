<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reminder_cron extends MY_Controller {

     public function __construct()
    {
        
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('email');
      
    
    }

    public function send_reminder_emails()
    {

       
     
        // Load necessary models
        $this->load->database();

        // Step 1: Get overdue reminders (30 mins past and not notified)
       // $this->db->where('notified', 'Yes');
    //    $this->db->where('DATE_ADD(date, INTERVAL 30 MINUTE) <=', date('Y-m-d H:i:s'));
        $reminders = $this->db->get('tbl_reminders')->result();

        foreach ($reminders as $reminder) {
            // Step 2: Get user details from tbl_account_details
            $this->db->where('user_id', $reminder->user_id);
            $user = $this->db->get('tbl_account_details')->row();
          
 print_r($reminder->user_id);
//  die;
            if ($user) {
               
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

                // Step 4: Send email if emails exist
                if (!empty($emails)) {

  print_r($emails);
  
                    $subject = 'Reminder Missed by User';
                    $message = "User ID: {$reminder->user_id} missed a reminder: <br><strong>{$reminder->description}</strong><br>Date: {$reminder->date}";

                    $this->load->library('email');
                    foreach ($emails as $email) {

                                 
                 
                         // $this->email->from('your_email@gmail.com', 'Reminder System');
                        // // $this->email->to($email);
                        // // $this->email->subject($subject);
                        // // $this->email->message($message);
                        // // $this->email->send();

        $params['subject'] = $subject;
        $params['message'] = $message;
        $params['resourceed_file'] = '';
        $params['recipient'] = $email;
        
        $this->user_model->send_email($params);
        print_r($params);
        echo ' Mail Sent';
                       
                    }
                }

                // Step 5: Mark reminder as notified
                // $this->db->where('reminder_id', $reminder->reminder_id);
                // $this->db->update('tbl_reminders', ['notified' => 1]);
            }
        }

        echo "Checked and processed reminders.";
    }
}
