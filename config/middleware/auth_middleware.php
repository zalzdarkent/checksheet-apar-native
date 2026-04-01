<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $current_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $encoded_url = urlencode($current_url);
        header("Location: login.php?redirect_to=" . $encoded_url);
        exit;
    }
}

function check_admin() {
    $role = strtolower($_SESSION['user_role'] ?? '');
    if ($role !== 'admin') {
        $_SESSION['error'] = "Halaman ini hanya untuk Admin!";
        header("Location: index.php?page=dashboard");
        exit;
    }
}
?>
