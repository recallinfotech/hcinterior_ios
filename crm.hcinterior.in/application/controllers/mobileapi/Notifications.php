<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CI_Controller {

    public function __construct() {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit(0);
        }
    }

    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function getGoogleAccessToken() {
        $clientEmail = 'firebase-adminsdk-fbsvc@hc-interior.iam.gserviceaccount.com';
        $privateKey  = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDW1Tjv2vlVxJep
FfcdORKoPXEfz+4Axf0eAn2T4xrcWAXq/1UTiqNwEubxpuhb3a57Hdy4w7S5b1kY
DsUXXgwzUbZ0AnMXiEC+r2ZmA13wU83/rHnTJYbTNLDXlHzUMdKhnKVTfZPqlX5J
IftA/5rFOupAh6RC9E+uudc2oE9If6Cd+GgWDjMdlWV/BBuoHi10crxhtQNFVlwt
17WJWaIsCtdA8MRTBjQIdeB1oMGoc2Hw2XBf5nuv2ZWX2pXt9hOVMz4Ai79BYndl
A/l9Kd6bMNT+QKj0C5+tp9c8GFOxwFEmz4X2Vp4soawhGycIuk56WwrL/mwWPmNt
kS6D9/6tAgMBAAECggEAAIISl2aiLlS/sxOtOPF4nNNAYD3ipzfQqHCDEYV4+8pt
gSfHcLtkNZCl4S5u4EC+9+WTLxCvbbzYrhji2HJNfxWTY+TZltWoqYrKqDUb1MSt
+QvUYe51yiwWuvvL68iOYfl33qGuZuChotSkvnucRxkjkrF/bvUa9yPTYXqxlU9O
2pGqz0nVbMt5X8JvS8J1iKb3nIZC15lUyDQi2P6vo5ghUGermNl3tzDThGlMmkdL
PgZKHYfeW1bdPCBLVhj5/SCVGHHX1vgqUHUpqHJlJShNq/vJdaxM1K1bkgTZXLFO
/k67GdNxzMqP9i+t0vto6ZaEIEMvfo+AnhHl/WEJoQKBgQD3HFheVNa7hOCVanS4
kt3ZfAOz4sn8sWH++2h8P6eHWNtBq3drVythTpyYSTqZW2C2vIuxLkp4Eys0pCbn
+2ZCbaeJ34+U17cW23krslYwFB6pwZlsc2oWpYX16CNpwmFnlKkglk/uwQEiTDOv
aC4iuceijklaJNVhJfWUaLpImQKBgQDej6EQbzmXlziptRUTfA5Lh0vp9GSjtloi
gdjXoFmFt31ci5cSwfEDL964FzsxQa62PVXGGNyc3mF0yYmgFmFt4uLfBqkdgm9z
U+caaUtFkD1kkceJXlgIPlMFCpfLT7VQmt0M64MsjbZq3cF2iQneIVAp2HHBbvAl
q4ET3hYPNQKBgQChRDxfg5qH0lYG7OyzsBVcY9S+xtjvMowzrbsoqxoX+GNO3ioX
QVIsNPN5ZwD5KGtblnzL6tvqtQfTWPWTG7xGye02y1GW35i7MAxJ+h7JTbgdLR2F
a2Tm1qswKolB8ftDb/9YJwCPXiHxUi3A4YqKWxfv/E/epn8i4XT9n8NCsQKBgQCM
/lS9AplrrYNaD7vQYjD0LDwtdIQlKqqjXbsvrwfHrFygulX0riSvLi5cVWtMYx35
mZWzL6DjSAZZZCvp3QPQB4JOY/vTFATi/O5VTws+gIhEJA5Sug/u+PzDHtjXFiH5
dIJBYxnwqb48qUucemhj7prIR7SZJFzCoInfOjyjWQKBgQDu5CR4/WW02l7R/ei0
dnhIsgYDhzZhfd7m4V+EmHq+kLYmM4AcfZR7RvDXlVIkIpMMdhtR2Prl30ZuO9jW
0a9qJvIzO2X1GMuA5VYgCxERFJgCpDln3pRAhUt40aaibevcad5JU+jLJt0mY2A4
tFBRE26j3QYIJec4AhRx0tACgg==
-----END PRIVATE KEY-----
EOD;

        $now = time();
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'iss'   => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now
        ]);

        $base64Header  = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);

        $signature = '';
        openssl_sign($base64Header . '.' . $base64Payload, $signature, $privateKey, 'SHA256');
        $base64Signature = $this->base64UrlEncode($signature);

        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt
        ]));

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        return isset($json['access_token']) ? $json['access_token'] : null;
    }

    private function convertApnsToFcmToken($apnsToken, $bundleId = 'com.HCIP.HCSales') {
        if (empty($apnsToken) || strlen($apnsToken) < 32 || strlen($apnsToken) > 64 || !ctype_xdigit($apnsToken)) {
            return $apnsToken;
        }

        $accessToken = $this->getGoogleAccessToken();
        if (!$accessToken) return $apnsToken;

        $ch = curl_init('https://iid.googleapis.com/iid/v1:batchImport');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'access_token_auth: true'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'application' => $bundleId,
            'sandbox'     => false,
            'apns_tokens' => [$apnsToken]
        ]));

        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (isset($data['results'][0]['registration_token'])) {
            return $data['results'][0]['registration_token'];
        }
        return $apnsToken;
    }

    public function send() {
        header('Content-Type: application/json');

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        $token = isset($data['token']) ? trim($data['token']) : trim($this->input->post('token'));
        $title = isset($data['title']) ? $data['title'] : $this->input->post('title');
        $body  = isset($data['body'])  ? $data['body']  : $this->input->post('body');
        $bundleId = !empty($data['bundle_id']) ? $data['bundle_id'] : (!empty($data['topic']) ? $data['topic'] : 'com.HCIP.HCSales');

        if (empty($title) || empty($body)) {
            echo json_encode(['status' => false, 'message' => 'Title and Body are required.']);
            return;
        }

        $effectiveToken = $this->convertApnsToFcmToken($token, $bundleId);

        $accessToken = $this->getGoogleAccessToken();
        if (!$accessToken) {
            echo json_encode(['status' => false, 'message' => 'Failed to obtain OAuth2 Access Token.']);
            return;
        }

        $fcmUrl = "https://fcm.googleapis.com/v1/projects/hc-interior/messages:send";

        $messagePayload = [
            'message' => [
                'token' => $effectiveToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority'  => '10',
                        'apns-push-type' => 'alert',
                        'apns-topic'     => $bundleId
                    ],
                    'payload' => [   
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body'  => $body,
                            ],
                            'sound'             => 'default',
                            'badge'             => 1,
                            'content-available' => 1
                        ]
                    ]
                ],
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'title'     => $title,
                        'body'      => $body,
                        'sound'     => 'default',
                        'channel_id'=> 'default',
                    ]
                ],
                'data' => [
                    'title'     => (string)$title,
                    'body'      => (string)$body,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]
        ];

        $ch = curl_init($fcmUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messagePayload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            echo json_encode([
                'status'          => true,
                'message'         => 'FCM v1 Notification sent successfully!',
                'effective_token' => $effectiveToken,
                'fcm_response'    => json_decode($response, true)
            ]);
        } else {
            echo json_encode([
                'status'          => false,
                'message'         => "FCM v1 API Error (HTTP {$httpCode})",
                'effective_token' => $effectiveToken,
                'fcm_error'       => json_decode($response, true) ? json_decode($response, true) : $response
            ]);
        }
    }

    public function check_token() {
        header('Content-Type: application/json');
        $token = $this->input->get('token') ? trim($this->input->get('token')) : trim($this->input->post('token'));
        if (empty($token)) {
            echo json_encode(['status' => false, 'message' => 'Token parameter is required']);
            return;
        }

        $accessToken = $this->getGoogleAccessToken();
        $ch = curl_init("https://iid.googleapis.com/iid/v1/info/{$token}?details=true");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'access_token_auth: true'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
    }
}
