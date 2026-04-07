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
            $_SESSION['error'] = "User ID dan Password wajib diisi!";
            header("Location: ../../login.php");
            exit;
        }

        $query = "
            SELECT 
                u.USERID, 
                u.PASSWORD, 
                u.PASSWD,
                u.EMPID, 
                u.GROUPUSER, 
                u.PicFile,
                u.REALNAME,
                e.EmployeeName
            FROM [apar].[Users].[UserTable] u
            LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON u.EMPID = e.EmpID
            WHERE (u.USERID = ? OR u.EMPID = ?) AND u.CF_Active = 1
        ";
        $params = array($npk, $npk);
        $stmt = sqlsrv_query($koneksi, $query, $params);

        if ($stmt === false) {
            $_SESSION['error'] = "Terjadi kesalahan sistem database!";
            header("Location: ../../login.php");
            exit;
        }

        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Validasi menggunakan MD5
        $input_md5 = md5($password);
        if ($user && ($input_md5 === $user['PASSWORD'] || $input_md5 === $user['PASSWD'] || $password === $user['PASSWORD'])) {
            // Success Login
            $_SESSION['user_id'] = $user['EMPID'];
            $_SESSION['user_npk'] = $user['USERID'];
            $_SESSION['user_name'] = !empty($user['EmployeeName']) ? $user['EmployeeName'] : (!empty($user['REALNAME']) ? $user['REALNAME'] : $user['USERID']);
            $_SESSION['user_role'] = !empty($user['GROUPUSER']) ? $user['GROUPUSER'] : 'user';
            $_SESSION['user_photo'] = !empty($user['PicFile']) ? $user['PicFile'] : 'profile.jpg';
            
            $_SESSION['success'] = "Selamat datang, " . $_SESSION['user_name'] . "!";
            
            if (!empty($_POST['redirect_to'])) {
                header("Location: " . $_POST['redirect_to']);
            } else {
                header("Location: ../../index.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "User ID atau Password salah!";
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
