<?php

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$script_path = str_replace(['index.php', 'print_qr.php', 'login.php'], '', $script_name);

$base_url = $protocol . "://" . $host . $script_path;

if (!defined('BASE_URL')) {
    define('BASE_URL', $base_url);
}
?>
