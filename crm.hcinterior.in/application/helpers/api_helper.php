<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Generate a new API token for a user
 * 
 * @param int $user_id
 * @param int $expiry_in_hours Optional, default 24 hours
 * @return string $token
 */
function generate_api_token($user_id, $expiry_in_hours = 24)
{
    $CI =& get_instance();

    $token = bin2hex(random_bytes(32)); // 64-char random string
    $expiry = date('Y-m-d H:i:s', strtotime("+$expiry_in_hours hours"));

    $CI->db->where('user_id', $user_id);
    $CI->db->update('tbl_users', [
        'api_token'       => $token,
        'token_expiry'    => $expiry
    ]);

    return $token;
}

/**
 * Validate the token from request headers
 * 
 * @return object $user
 */
function validate_api_token()
{
    $CI =& get_instance();

    $headers = $CI->input->request_headers();
    $token_raw = '';

    if (!empty($headers['Authorization'])) {
        $token_raw = $headers['Authorization'];
    } else if (!empty($headers['authorization'])) {
        $token_raw = $headers['authorization'];
    } else if (!empty($headers['token'])) {
        $token_raw = $headers['token'];
    } else if (!empty($headers['X-API-TOKEN'])) {
        $token_raw = $headers['X-API-TOKEN'];
    } else if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $token_raw = $_SERVER['HTTP_AUTHORIZATION'];
    } else if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $token_raw = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } else {
        $token_raw = $CI->input->get_post('token', true) ?: $CI->input->get_post('api_token', true);
        if (empty($token_raw)) {
            $raw_json = json_decode(file_get_contents('php://input'), true);
            if (is_array($raw_json)) {
                $token_raw = $raw_json['token'] ?? ($raw_json['api_token'] ?? '');
            }
        }
    }

    $token = trim(str_replace('Bearer ', '', (string)$token_raw));

    if (empty($token)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Authorization token missing or empty']);
        exit;
    }

    $user = $CI->db->where('api_token', $token)
                   ->where('api_token !=', '')
                   ->where('api_token IS NOT NULL', null, false)
                   ->get('tbl_users')
                   ->row();

    if (!$user || empty($user->user_id)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Invalid API token']);
        exit;
    }

    // Check token expiry
    if (!empty($user->token_expiry) && strtotime($user->token_expiry) < time()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Token expired']);
        exit;
    }

    return $user;
}

/**
 * Revoke a token (logout)
 * 
 * @param int $user_id
 */
function revoke_api_token($user_id)
{
    $CI =& get_instance();
    $CI->db->where('user_id', $user_id);
    $CI->db->update('tbl_users', [
        'api_token'    => null,
        'token_expiry' => null
    ]);
}
