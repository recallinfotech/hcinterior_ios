<?php
// Test admin user in clientList
$cookie_file = __DIR__ . '/admin_cookie.txt';
if (file_exists($cookie_file)) unlink($cookie_file);

// 1. GET login page
$ch = curl_init('http://localhost/hc_interior_live/crm.hcinterior.in/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_exec($ch);
curl_close($ch);

// Set session for admin user 57 in CodeIgniter or login
// Let's check if we can simulate session directly or test query
define('BASEPATH', 'c:/wamp64/www/hc_interior_live/crm.hcinterior.in/system/');
require_once 'c:/wamp64/www/hc_interior_live/scratch/test_stats.php';
