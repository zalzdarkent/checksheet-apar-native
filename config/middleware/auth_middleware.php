<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
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
