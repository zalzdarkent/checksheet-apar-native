<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db_koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $npk = $_POST['npk'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($npk) || empty($password)) {
            $_SESSION['error'] = "NPK dan Password wajib diisi!";
            header("Location: ../../login.php");
            exit;
        }

        $query = "SELECT * FROM [apar].[dbo].[users] WHERE npk = ? AND is_active = 1";
        $params = array($npk);
        $stmt = sqlsrv_query($koneksi, $query, $params);

        if ($stmt === false) {
            $_SESSION['error'] = "Terjadi kesalahan sistem!";
            header("Location: ../../login.php");
            exit;
        }

        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Success Login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_npk'] = $user['npk'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_photo'] = $user['photo'];
            
            $_SESSION['success'] = "Selamat datang, " . $user['name'] . "!";
            
            if (!empty($_POST['redirect_to'])) {
                header("Location: " . $_POST['redirect_to']);
            } else {
                header("Location: ../../index.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "NPK atau Password salah!";
            header("Location: ../../login.php");
            exit;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    session_start();
    $_SESSION['success'] = "Berhasil logout!";
    header("Location: ../../login.php");
    exit;
}
?>
