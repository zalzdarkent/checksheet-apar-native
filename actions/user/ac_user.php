<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
include(__DIR__ . '/../../config/db_koneksi.php');

function get_all_users()
{
    global $koneksi;

    $kueri = "SELECT * FROM [apar].[dbo].[users]";
    $hasil = sqlsrv_query($koneksi, $kueri);
    $data = [];
    if ($hasil !== false) {
        while ($row = sqlsrv_fetch_array($hasil, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    return $data;
}

function store_user()
{
    global $koneksi;

    $name = $_POST['name'];
    $npk = $_POST['npk'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error_password'] = "Password tidak cocok!";
        header("Location: ../../index.php?page=add-user");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $kueri = "INSERT INTO [apar].[dbo].[users] (name, npk, role, password, is_active) 
              VALUES (?, ?, ?, ?, 1)";
    $params = array($name, $npk, $role, $password_hash);
    $hasil = sqlsrv_query($koneksi, $kueri, $params);
    if ($hasil !== false) {
        $_SESSION['success'] = "User berhasil ditambahkan!";
        header("Location: ../../index.php?page=user-management");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan user!";
        header("Location: ../../index.php?page=add-user");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'])) {
        store_user();
    }
}
?>