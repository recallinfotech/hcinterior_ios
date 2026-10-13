<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use Google\Client as Google_Client;

class Notification_lib {

    protected $CI;
    private $serviceAccountKeyPath;
    private $projectId;
    private $iosBundleId;
    private static $cachedBearerToken = null;
    private static $tokenExpiresAt = 0;

    public function __construct() {
        $this->CI =& get_instance();

        // 🔑 Firebase service account
        $this->serviceAccountKeyPath = APPPATH . 'third_party/firebase-key.json';

        // 🔑 Firebase project ID
        $this->projectId = 'hc-interior';

        // 🔑 iOS Bundle Identifier (MUST MATCH APP)
        $this->iosBundleId = 'com.HCIP.HCIP';
    }

    private function get_oauth_token() {
        if (!empty(self::$cachedBearerToken) && time() < self::$tokenExpiresAt - 60) {
            return self::$cachedBearerToken;
        }

        $client = new Google_Client();
        $client->setAuthConfig($this->serviceAccountKeyPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $accessToken = $client->fetchAccessTokenWithAssertion();

        if (isset($accessToken['access_token'])) {
            self::$cachedBearerToken = $accessToken['access_token'];
            $expiresIn = isset($accessToken['expires_in']) ? (int)$accessToken['expires_in'] : 3500;
            self::$tokenExpiresAt = time() + $expiresIn;
            return self::$cachedBearerToken;
        }

        throw new Exception(isset($accessToken['error_description']) ? $accessToken['error_description'] : 'Unable to acquire OAuth token');
    }

    public function send_app_notification(
        $title,
        $body,
        $image_url = null,
        $token = null,
        $device_type = null // android | ios | null
    ) {
        if (empty($token)) {
            return ['status' => 'error', 'message' => 'FCM token missing'];
        }

        $image_url = $image_url ?? "https://hcinterior.in/static-images/promo.jpg";

        // 🔐 Get OAuth access token
        try {
            $bearerToken = $this->get_oauth_token();
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Auth Failed: ' . $e->getMessage()];
        }

        // 🔔 Base message
        $message = [
            'token' => $token,

            'notification' => [
                'title' => $title,
                'body'  => $body
            ],

            'data' => [
                'type' => 'REMINDER',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ]
        ];

        // ===============================
        // ✅ ANDROID CONFIG
        // ===============================
        if ($device_type === 'android' || empty($device_type)) {
            $message['android'] = [
                'priority' => 'HIGH',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'reminders'
                ]
            ];
        }

        // ===============================
        // ✅ iOS CONFIG (DEV + PROD SAFE)
        // ===============================
       if ($device_type === 'ios' || empty($device_type)) {
    $message['apns'] = [
        'headers' => [
            'apns-priority'  => '10',
            'apns-push-type' => 'alert',
            'apns-topic'     => $this->iosBundleId
        ],
        'payload' => [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body'  => $body
                ],
                'sound' => 'default',
                'badge' => 1
            ]
        ],
        // ✅ IMAGE MUST BE HERE FOR iOS
        'fcm_options' => [
            'image' => $image_url
        ]
    ];
}

        $payload = ['message' => $message];

        // 📡 Send request to FCM
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$bearerToken}",
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return ['status' => 'error', 'message' => $curl_error];
        }

        $decoded = json_decode($response, true);

        return isset($decoded['name'])
            ? ['status' => 'success', 'message' => 'Notification sent']
            : ['status' => 'error', 'message' => 'FCM error', 'response' => $decoded];
    }
	
	
	
	public function send_app_notification_target(
    $title,
    $body,
    $image_url = null,
    $token = null,
    $device_type = null, // android | ios | null
    $extra_data = []     // 👈 NEW: Deep link data parameter
) {
    if (empty($token)) {
        return ['status' => 'error', 'message' => 'FCM token missing'];
    }
    $image_url = $image_url ?? "https://hcinterior.in/static-images/promo.jpg";
    // 🔐 Get OAuth access token
    try {
        $bearerToken = $this->get_oauth_token();
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Auth Failed: ' . $e->getMessage()];
    }
    // 💥 Prepare FCM Data Payload (Values MUST be Strings in FCM v1)
    $data_payload = [
        'type' => isset($extra_data['type']) ? (string)$extra_data['type'] : 'REMINDER',
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
    ];
    if (!empty($extra_data) && is_array($extra_data)) {
        foreach ($extra_data as $k => $v) {
            $data_payload[(string)$k] = (string)$v;
        }
    }
    // 🔔 Base message
    $message = [
        'token' => $token,
        'notification' => [
            'title' => $title,
            'body'  => $body
        ],
        'data' => $data_payload
    ];


        // ===============================
        // ✅ ANDROID CONFIG
        // ===============================
        if ($device_type === 'android' || empty($device_type)) {
            $message['android'] = [
                'priority' => 'HIGH',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'reminders'
                ]
            ];
        }

        // ===============================
        // ✅ iOS CONFIG (DEV + PROD SAFE)
        // ===============================
       if ($device_type === 'ios' || empty($device_type)) {
    $message['apns'] = [
        'headers' => [
            'apns-priority'  => '10',
            'apns-push-type' => 'alert',
            'apns-topic'     => $this->iosBundleId
        ],
        'payload' => [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body'  => $body
                ],
                'sound' => 'default',
                'badge' => 1
            ]
        ],
        // ✅ IMAGE MUST BE HERE FOR iOS
        'fcm_options' => [
            'image' => $image_url
        ]
    ];
}

        $payload = ['message' => $message];

        // 📡 Send request to FCM
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$bearerToken}",
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return ['status' => 'error', 'message' => $curl_error];
        }

        $decoded = json_decode($response, true);

        return isset($decoded['name'])
            ? ['status' => 'success', 'message' => 'Notification sent', 'Detail' => $extra_data]
            : ['status' => 'error', 'message' => 'FCM error', 'response' => $decoded];
    }
	
	
	
}