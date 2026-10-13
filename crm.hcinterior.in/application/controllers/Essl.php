<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Essl extends MY_Controller  {

public function __construct()
{
    parent::__construct();

    // login check bypass
    $this->session->set_userdata('logged_in', TRUE);

    $this->load->model('settings_model');
    $this->load->model('user_model');

    $this->load->library('Notification_lib_hygine');
    

    date_default_timezone_set('Asia/Kolkata');
}

 
  public function getAttendance()
{
    echo "API HIT<br>";

    $url = "http://115.245.195.27:83/iclock/WebAPIService.asmx";

    // 🔥 Dynamic date (आज का data)
    $from = date('Y-m-d 00:00');
    $to   = date('Y-m-d 23:59');
    print_r($from);
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Body>
        <GetTransactionsLog xmlns="http://tempuri.org/">
          <FromDateTime>'.$from.'</FromDateTime>
          <ToDateTime>'.$to.'</ToDateTime>
          <SerialNumber>NFZ8262001293</SerialNumber>
          <UserName>apiuser</UserName>
          <UserPassword>Hc@12345</UserPassword>
          <strDataList></strDataList>
        </GetTransactionsLog>
      </soap:Body>
    </soap:Envelope>';

    $headers = [
        "Content-Type: text/xml; charset=utf-8",
        "SOAPAction: \"http://tempuri.org/GetTransactionsLog\""
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);

    $response = curl_exec($ch);

    if ($response === false) {
        echo "❌ CURL ERROR: " . curl_error($ch);
        return;
    }

    curl_close($ch);

    // 🔥 SOAP XML parsing (correct way)
    $xmlObj = simplexml_load_string($response);

    $namespaces = $xmlObj->getNamespaces(true);
    $body = $xmlObj->children($namespaces['soap'])->Body;

    $result = $body->children()->GetTransactionsLogResponse
        ->GetTransactionsLogResult;

    $logString = (string)$result;

    // 🧪 Debug (पहली बार check करो)
    if (empty($logString)) {
        echo "❌ No data found<br>";
        return;
    }

   echo "Saving data...<br>";

$responseNode = $body->children()->GetTransactionsLogResponse;
$logString = (string)$responseNode->strDataList;

if (empty($logString)) {
    echo "❌ No data found";
    return;
}

$lines = explode("\n", $logString);
$inserted = 0;

foreach ($lines as $line) {

    $line = trim($line);
    if (empty($line)) continue;

    // remove tab
    $line = str_replace("\t", " ", $line);
    $line = preg_replace('/\s+/', ' ', $line);

    $parts = explode(' ', $line);

    if (count($parts) >= 3) {

        $emp_code = $parts[0];
        $punch_time = $parts[1] . ' ' . $parts[2];

        // 🔥 validate datetime
        if (!strtotime($punch_time)) continue;

        $exists = $this->db
            ->where('emp_code', $emp_code)
            ->where('punch_time', $punch_time)
            ->get('attendance_logs')
            ->row();

        if (!$exists) {
            $this->db->insert('attendance_logs', [
                'emp_code' => $emp_code,
                'punch_time' => $punch_time
            ]);
            $inserted++;
        }
    }
}

 
echo "✅ Inserted: " . $inserted;  

 }
 
  /**
   * Fetch attendance logs by custom date range
   * 
   * Usage examples:
   * 1. URL params:   base_url/Essl/getAttendanceByDateRange/2026-03-01/2026-03-25
   * 2. Single date:  base_url/Essl/getAttendanceByDateRange/2026-03-25
   * 3. GET query:    base_url/Essl/getAttendanceByDateRange?from_date=2026-03-01&to_date=2026-03-25
   * 4. POST payload: from_date=2026-03-01 & to_date=2026-03-25
   */
  public function getAttendanceByDateRange($from_param = null, $to_param = null)
  {
      echo "API HIT (Date Range)<br>";

      // Fetch input from URL args, GET query, or POST
      $from_input = $from_param ?: ($this->input->get('from_date') ?: ($this->input->get('from') ?: ($this->input->post('from_date') ?: ($this->input->post('from') ?: null))));
      $to_input   = $to_param   ?: ($this->input->get('to_date')   ?: ($this->input->get('to')   ?: ($this->input->post('to_date')   ?: ($this->input->post('to')   ?: null))));

      $from_time = $from_input ? strtotime($from_input) : false;
      $to_time   = $to_input ? strtotime($to_input) : false;

      // Format From Date
      if ($from_time !== false && !empty($from_input)) {
          $from = (strlen(trim($from_input)) <= 10) ? date('Y-m-d 00:00', $from_time) : date('Y-m-d H:i', $from_time);
      } else {
          $from = date('Y-m-d 00:00');
      }

      // Format To Date (defaults to end of from_date if only from_date is provided)
      if ($to_time !== false && !empty($to_input)) {
          $to = (strlen(trim($to_input)) <= 10) ? date('Y-m-d 23:59', $to_time) : date('Y-m-d H:i', $to_time);
      } else {
          $to = ($from_time !== false && !empty($from_input)) ? date('Y-m-d 23:59', $from_time) : date('Y-m-d 23:59');
      }

      echo "<b>From Date:</b> " . htmlspecialchars($from) . "<br>";
      echo "<b>To Date:</b> " . htmlspecialchars($to) . "<br><br>";

      $url = "http://115.245.195.27:83/iclock/WebAPIService.asmx";

      $xml = '<?xml version="1.0" encoding="utf-8"?>
      <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
          xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
          xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
          <GetTransactionsLog xmlns="http://tempuri.org/">
            <FromDateTime>'.$from.'</FromDateTime>
            <ToDateTime>'.$to.'</ToDateTime>
            <SerialNumber>NFZ8262001293</SerialNumber>
            <UserName>apiuser</UserName>
            <UserPassword>Hc@12345</UserPassword>
            <strDataList></strDataList>
          </GetTransactionsLog>
        </soap:Body>
      </soap:Envelope>';

      $headers = [
          "Content-Type: text/xml; charset=utf-8",
          "SOAPAction: \"http://tempuri.org/GetTransactionsLog\""
      ];

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_TIMEOUT, 120);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

      $response = curl_exec($ch);

      if ($response === false) {
          echo "❌ CURL ERROR: " . curl_error($ch);
          return;
      }

      curl_close($ch);

      // SOAP XML parsing
      $xmlObj = @simplexml_load_string($response);
      if (!$xmlObj) {
          echo "❌ Failed to parse XML response.<br>";
          return;
      }

      $namespaces = $xmlObj->getNamespaces(true);
      if (!isset($namespaces['soap'])) {
          echo "❌ Invalid SOAP response structure.<br>";
          return;
      }

      $body = $xmlObj->children($namespaces['soap'])->Body;
      $responseNode = $body->children()->GetTransactionsLogResponse;

      $logString = '';
      if (isset($responseNode->strDataList) && !empty((string)$responseNode->strDataList)) {
          $logString = (string)$responseNode->strDataList;
      } elseif (isset($responseNode->GetTransactionsLogResult) && !empty((string)$responseNode->GetTransactionsLogResult)) {
          $logString = (string)$responseNode->GetTransactionsLogResult;
      }

      if (empty($logString)) {
          echo "❌ No data found for the date range: " . htmlspecialchars($from) . " to " . htmlspecialchars($to) . "<br>";
          return;
      }

      echo "Saving data...<br>";

      $lines = explode("\n", $logString);
      $inserted = 0;
      $skipped = 0;
      $total = 0;

      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) continue;

          // remove tab and extra spaces
          $line = str_replace("\t", " ", $line);
          $line = preg_replace('/\s+/', ' ', $line);

          $parts = explode(' ', $line);

          if (count($parts) >= 3) {
              $total++;
              $emp_code = $parts[0];
              $punch_time = $parts[1] . ' ' . $parts[2];

              // validate datetime
              if (!strtotime($punch_time)) continue;

              $exists = $this->db
                  ->where('emp_code', $emp_code)
                  ->where('punch_time', $punch_time)
                  ->get('attendance_logs')
                  ->row();

              if (!$exists) {
                  $this->db->insert('attendance_logs', [
                      'emp_code' => $emp_code,
                      'punch_time' => $punch_time
                  ]);
                  $inserted++;
              } else {
                  $skipped++;
              }
          }
      }

      echo "✅ Total Records Processed: " . $total . "<br>";
      echo "✅ Inserted: " . $inserted . "<br>";
      echo "ℹ️ Already Existed (Skipped): " . $skipped . "<br>";
  }
 
 
   public function sendPushNotification()
    {
 		 
    $serverKey = "BB-hBybA1MKMv83d7yCeAXEMdgWQtpbR68CEP7lqEfZbENgXK5bEfxvvrmTOdL_2AuS4fDm3PWH7JSVL0tA7xgg";

    $deviceToken = "etJZCzruQjGpFV7kX8Pj3e:APA91bEm_S9lMzv0PTJr18noS86aaLu2MFNVIf-Z8pLvEg3bXMspIOZq-jXddnbq66eS4R9EehafvdeTTdW0kXi0lRSkzNxZvggP3XZuE2T_eHkoIWEsYqw";

      $data = [
        "to" => $deviceToken,
        "notification" => [
            "title" => "Test Notification",
            "body" => "Ye CodeIgniter se aaya hai 🔥",
            "sound" => "default"
        ],
        "data" => [
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            "custom_data" => "value123"
        ]
    ];

    $headers = [
        "Authorization: key=" . $serverKey,
        "Content-Type: application/json"
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if ($response === FALSE) {
        echo json_encode([
            'status' => false,
            'error' => curl_error($ch)
        ]);
        return;
    }

    curl_close($ch);

    echo json_encode([
        'status' => true,
        'response' => json_decode($response)
    ]);

 
	}
	
	
	
	
    public function test_push()
    {
    
           $response = $this->notification_lib_hygine->send_app_notification(
                            "hello this is test msg",
                            "this is body msg",
                            "https://hygiair.ai/public/logo.png",
                           "etJZCzruQjGpFV7kX8Pj3e:APA91bEm_S9lMzv0PTJr18noS86aaLu2MFNVIf-Z8pLvEg3bXMspIOZq-jXddnbq66eS4R9EehafvdeTTdW0kXi0lRSkzNxZvggP3XZuE2T_eHkoIWEsYqw",
                            "android"   // android | ios
                        );

        print_r($response);
    }



 
}