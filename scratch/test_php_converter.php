<?php

function get_google_fcm_token_from_apns($apns_token) {
    if (empty($apns_token) || strlen($apns_token) != 64) {
        return $apns_token;
    }

    $client_email = 'firebase-adminsdk-fbsvc@hc-interior.iam.gserviceaccount.com';
    $private_key = "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDW1Tjv2vlVxJep\nFfcdORKoPXEfz+4Axf0eAn2T4xrcWAXq/1UTiqNwEubxpuhb3a57Hdy4w7S5b1kY\nDsUXXgwzUbZ0AnMXiEC+r2ZmA13wU83/rHnTJYbTNLDXlHzUMdKhnKVTfZPqlX5J\nIftA/5rFOupAh6RC9E+uudc2oE9If6Cd+GgWDjMdlWV/BBuoHi10crxhtQNFVlwt\n17WJWaIsCtdA8MRTBjQIdeB1oMGoc2Hw2XBf5nuv2ZWX2pXt9hOVMz4Ai79BYndl\nA/l9Kd6bMNT+QKj0C5+tp9c8GFOxwFEmz4X2Vp4soawhGycIuk56WwrL/mwWPmNt\nkS6D9/6tAgMBAAECggEAAIISl2aiLlS/sxOtOPF4nNNAYD3ipzfQqHCDEYV4+8pt\ngSfHcLtkNZCl4S5u4EC+9+WTLxCvbbzYrhji2HJNfxWTY+TZltWoqYrKqDUb1MSt\n+QvUYe51yiwWuvvL68iOYfl33qGuZuChotSkvnucRxkjkrF/bvUa9yPTYXqxlU9O\n2pGqz0nVbMt5X8JvS8J1iKb3nIZC15lUyDQi2P6vo5ghUGermNl3tzDThGlMmkdL\nPgZKHYfeW1bdPCBLVhj5/SCVGHHX1vgqUHUpqHJlJShNq/vJdaxM1K1bkgTZXLFO\n/k67GdNxzMqP9i+t0vto6ZaEIEMvfo+AnhHl/WEJoQKBgQD3HFheVNa7hOCVanS4\nkt3ZfAOz4sn8sWH++2h8P6eHWNtBq3drVythTpyYSTqZW2C2vIuxLkp4Eys0pCbn\n+2ZCbaeJ34+U17cW23krslYwFB6pwZlsc2oWpYX16CNpwmFnlKkglk/uwQEiTDOv\naC4iuceijklaJNVhJfWUaLpImQKBgQDej6EQbzmXlziptRUTfA5Lh0vp9GSjtloi\ngdjXoFmFt31ci5cSwfEDL964FzsxQa62PVXGGNyc3mF0yYmgFmFt4uLfBqkdgm9z\nU+caaUtFkD1kkceJXlgIPlMFCpfLT7VQmt0M64MsjbZq3cF2iQneIVAp2HHBbvAl\nq4ET3hYPNQKBgQChRDxfg5qH0lYG7OyzsBVcY9S+xtjvMowzrbsoqxoX+GNO3ioX\nQVIsNPN5ZwD5KGtblnzL6tvqtQfTWPWTG7xGye02y1GW35i7MAxJ+h7JTbgdLR2F\na2Tm1qswKolB8ftDb/9YJwCPXiHxUi3A4YqKWxfv/E/epn8i4XT9n8NCsQKBgQCM\n/lS9AplrrYNaD7vQYjD0LDwtdIQlKqqjXbsvrwfHrFygulX0riSvLi5cVWtMYx35\nmZWzL6DjSAZZZCvp3QPQB4JOY/vTFATi/O5VTws+gIhEJA5Sug/u+PzDHtjXFiH5\ndIJBYxnwqb48qUucemhj7prIR7SZJFzCoInfOjyjWQKBgQDu5CR4/WW02l7R/ei0\ndnhIsgYDhzZhfd7m4V+EmHq+kLYmM4AcfZR7RvDXlVIkIpMMdhtR2Prl30ZuO9jW\n0a9qJvIzO2X1GMuA5VYgCxERFJgCpDln3pRAhUt40aaibevcad5JU+jLJt0mY2A4\ntFBRE26j3QYIJec4AhRx0tACgg==\n-----END PRIVATE KEY-----";

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $claim = json_encode([
        'iss' => $client_email,
        'scope' => 'https://www.googleapis.com/auth/cloud-platform',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlClaim  = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claim));

    $signature = '';
    openssl_sign($base64UrlHeader . "." . $base64UrlClaim, $signature, $private_key, 'SHA256');
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $jwt = $base64UrlHeader . "." . $base64UrlClaim . "." . $base64UrlSignature;

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ]));
    $res = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($res, true);
    $access_token = $tokenData['access_token'] ?? '';
    if (empty($access_token)) {
        echo "Failed to get access token: " . $res . "\n";
        return $apns_token;
    }

    $ch = curl_init('https://iid.googleapis.com/iid/v1:batchImport');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'application' => 'com.HCIP.HCOperation',
        'sandbox' => false,
        'apns_tokens' => [$apns_token]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
        'access_token_auth: true'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    if (isset($data['results'][0]['registration_token'])) {
        return $data['results'][0]['registration_token'];
    }
    echo "batchImport response: " . $res . "\n";
    return $apns_token;
}

$apnsToken = '828E708E4378B4C624F5E0ADDE90AACAE788845F16113383B6D7DF1F96B66FD6';
echo "Converting APNs Token via PHP OAuth 2.0...\n";
$fcmToken = get_google_fcm_token_from_apns($apnsToken);
echo "\nRESULTING GOOGLE FCM TOKEN:\n" . $fcmToken . "\n";
