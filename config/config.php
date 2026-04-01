<?php
// Centralized Configuration
// This file handles the base URL detection for the whole application.

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Detect the script path and remove common filenames to get the root directory
$script_name = $_SERVER['SCRIPT_NAME'];
$script_path = str_replace(['index.php', 'print_qr.php', 'login.php'], '', $script_name);

// BASE_URL for references (QR codes, deep-links, assets)
$base_url = $protocol . "://" . $host . $script_path;

// To force a specific URL (like Ngrok), uncomment the line below:
// $base_url = "https://your-ngrok-id.ngrok-free.app/apar/";

if (!defined('BASE_URL')) {
    define('BASE_URL', $base_url);
}
?>
