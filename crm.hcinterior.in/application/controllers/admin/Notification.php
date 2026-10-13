<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php'; 
use Google\Client as Google_Client;

class Notification extends Admin_Controller {

    // 🔹 Firebase service key path और project_id
    private $serviceAccountKeyPath = APPPATH . 'third_party/firebase-key.json'; 
    private $projectId = 'hc-interior'; 

    public function __construct() {
        parent::__construct();
    }

    // Default route
    public function index() {
        echo 'Firebase Notification API Working ✅';
    }

    // 🔹 1️⃣ सभी devices को (Topic-based)
    public function send_static_topic() {
        $title = "🔥 HC Interior Offer!";
        $body  = "New Diwali collection out now. Visit hcinterior.in!";
        $image_url = "https://hcinterior.in/images/new_hc_logo.png"; // optional image

        $result = $this->send_fcm_message([
            'type' => 'topic',
            'target' => 'allDevices',
            'title' => $title,
            'body'  => $body,
            'image' => $image_url
        ]);

        return $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    // 🔹 2️⃣ किसी एक fixed token वाले device को भेजने के लिए
    public function send_app_notification($title = 'hello', $body = 'hello', $image_url = NULL) {
        // $title = "👋 Hello from HC Interior!";
        // $body  = "Your personalized offer is waiting inside the app.";
         $image_url = "https://hcinterior.in/static-images/promo.jpg";

        // 👇 यहाँ अपना FCM token डालो (test device का)
        $token = "f92VvkevRUukfMp4asptuG:APA91bEUkktEItJlhfFF0fgm0NtRu5vhzkfeegyRL25xQ-QycBGAT941znaGhK9TVZyyfuNjPBHa5iJCekVcmYUU1vsNncYjKCu76Tbz21IHjOduXnWJ9Zo";

        $result = $this->send_fcm_message([
            'type' => 'token',
            'target' => $token,
            'title' => $title,
            'body'  => $body,
            'image' => $image_url
        ]);

        return $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    // 🔹 3️⃣ Common reusable function for FCM send
    private function send_fcm_message($params) {
        // ✅ Firebase JSON key check
        if (!file_exists($this->serviceAccountKeyPath)) {
            return ['status' => 'error', 'message' => 'Firebase key not found.'];
        }

        // ✅ Google Auth setup
        try {
            $client = new Google_Client();
            $client->setAuthConfig($this->serviceAccountKeyPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $accessToken = $client->fetchAccessTokenWithAssertion();
            $bearerToken = $accessToken['access_token'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Auth Failed: ' . $e->getMessage()];
        }

        // ✅ Build FCM Message Payload
        $targetKey = ($params['type'] === 'topic') ? 'topic' : 'token';

        $messagePayload = [
            'message' => [
                $targetKey => $params['target'],
                'notification' => [
                    'title' => $params['title'],
                    'body'  => $params['body'],
                    'image' => $params['image'] ?? null,
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',
                    'title' => $params['title'],
                    'body' => $params['body'],
                    'image' => $params['image'] ?? ''
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'high_importance_channel'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'alert' => [
                                'title' => $params['title'],
                                'body'  => $params['body']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // ✅ Send Request to Firebase
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($messagePayload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $bearerToken",
                'Content-Type: application/json'
            ],
        ]);

        $response_fcm = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return ['status' => 'error', 'message' => 'cURL Error: ' . $curl_error];
        }

        $decoded = json_decode($response_fcm, true);

        if (isset($decoded['name'])) {
            return [
                'status' => 'success',
                'message' => 'Notification sent successfully ✅',
                'target' => $params['target'],
                'fcm_response' => $decoded
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Unexpected FCM response',
                'target' => $params['target'],
                'fcm_response' => $decoded
            ];
        }
    }
}
