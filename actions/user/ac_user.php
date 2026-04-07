<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
include(__DIR__ . '/../../config/db_koneksi.php');

function get_all_users()
{
    global $koneksi;

    $kueri = "
        SELECT 
            u.EMPID as npk, 
            ISNULL(e.EmployeeName, u.REALNAME) as name, 
            u.GROUPUSER as role, 
            u.CF_Active as is_active, 
            u.PicFile as photo,
            u.EMPID as id,
            (SELECT STRING_AGG(location_name, ',') FROM [dbo].[user_pic_locations] WHERE EMPID = u.EMPID AND device_type = 'apar') as pic_apar_location,
            (SELECT STRING_AGG(location_name, ',') FROM [dbo].[user_pic_locations] WHERE EMPID = u.EMPID AND device_type = 'hydrant') as pic_hydrant_location
        FROM [apar].[Users].[UserTable] u
        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON u.EMPID = e.EmpID
    ";
    $hasil = sqlsrv_query($koneksi, $kueri);
    $data = [];
    if ($hasil !== false) {
        while ($row = sqlsrv_fetch_array($hasil, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    return $data;
}

function get_user_by_id($id)
{
    global $koneksi;

    $kueri = "
        SELECT 
            u.USERID as npk, 
            ISNULL(e.EmployeeName, u.REALNAME) as name, 
            u.GROUPUSER as role, 
            u.CF_Active as is_active, 
            u.PicFile as photo,
            u.EMPID as id,
            (SELECT STRING_AGG(location_name, ',') FROM [dbo].[user_pic_locations] WHERE EMPID = u.EMPID AND device_type = 'apar') as pic_apar_location,
            (SELECT STRING_AGG(location_name, ',') FROM [dbo].[user_pic_locations] WHERE EMPID = u.EMPID AND device_type = 'hydrant') as pic_hydrant_location
        FROM [apar].[Users].[UserTable] u
        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON u.EMPID = e.EmpID
        WHERE u.EMPID = ?
    ";
    $params = array($id);
    $hasil = sqlsrv_query($koneksi, $kueri, $params);
    if ($hasil === false) {
        return null;
    }
    $data = sqlsrv_fetch_array($hasil, SQLSRV_FETCH_ASSOC);
    return $data;
}

function update_user($id)
{
    global $koneksi;

    $name = $_POST['name'];
    $npk = $_POST['npk'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $old_user = get_user_by_id($id);
    $photo = $old_user['photo'];

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $_SESSION['error_password'] = "Password tidak cocok!";
            header("Location: ../../index.php?page=edit-user&id=" . $id);
            exit;
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_query = "password = ?, ";
        $password_param = $password_hash;
    } else {
        $password_query = "";
        $password_param = null;
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['png', 'jpg', 'jpeg'];
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['photo']['size'];

        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['error_photo'] = "Format file tidak didukung! Gunakan PNG, JPG, atau JPEG.";
            header("Location: ../../index.php?page=edit-user&id=" . $id);
            exit;
        }

        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION['error_photo'] = "Ukuran file terlalu besar! Maksimal 5MB.";
            header("Location: ../../index.php?page=edit-user&id=" . $id);
            exit;
        }

        $dir = __DIR__ . '/../../storage/users/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // PENGHAPUSAN FOTO LAMA DIPINDAHKAN KE AKHIR (SETELAH QUERY SUKSES)
        // Agar jika query fail, file lama masih ada (tidak jadi placeholder)

        $photo = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $dir . $photo;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $_SESSION['error_photo'] = "Gagal mengupload foto!";
            header("Location: ../../index.php?page=edit-user&id=" . $id);
            exit;
        }
    }

    $updated_at = date('Y-m-d H:i:s');

    // Fix query syntax: ensure commas are correct regardless of password update
    $kueri = "UPDATE [apar].[dbo].[users] SET name = ?, npk = ?, role = ?, ";
    if (!empty($password_query)) {
        $kueri .= "password = ?, ";
    }
    $kueri .= "photo = ?, updated_at = ? WHERE id = ?";

    $params = array($name, $npk, $role);
    if (!empty($password_query)) {
        $params[] = $password_param;
    }
    $params[] = $photo;
    $params[] = $updated_at;
    $params[] = (int) $id; // Force integer for ID

    $hasil = sqlsrv_query($koneksi, $kueri, $params);
    if ($hasil !== false) {
        // Hapus foto lama hanya jika update DB sukses dan ada foto baru yang diupload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__ . '/../../storage/users/';
            if (!empty($old_user['photo']) && file_exists($dir . $old_user['photo'])) {
                unlink($dir . $old_user['photo']);
            }
        }

        // Update session if editing own profile
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_photo'] = $photo;
        }

        $_SESSION['success'] = "User berhasil diupdate!";
        header("Location: ../../index.php?page=user-management");
        exit;
    } else {
        // Log error for debugging - make it more readable
        $errors = sqlsrv_errors();
        $error_msg = "";
        foreach ($errors as $error) {
            $error_msg .= "SQLSTATE: " . $error['SQLSTATE'] . ", Code: " . $error['code'] . ", Message: " . $error['message'] . " | ";
        }
        $_SESSION['error'] = "Gagal mengupdate user! Detail: " . $error_msg;
        header("Location: ../../index.php?page=edit-user&id=" . $id);
        exit;
    }
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

    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['png', 'jpg', 'jpeg'];
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['photo']['size'];

        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['error_photo'] = "Format file tidak didukung! Gunakan PNG, JPG, atau JPEG.";
            header("Location: ../../index.php?page=add-user");
            exit;
        }

        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION['error_photo'] = "Ukuran file terlalu besar! Maksimal 5MB.";
            header("Location: ../../index.php?page=add-user");
            exit;
        }

        $dir = __DIR__ . '/../../storage/users/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $photo = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $dir . $photo;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $_SESSION['error_photo'] = "Gagal mengupload foto!";
            header("Location: ../../index.php?page=add-user");
            exit;
        }
    }

    $remember_token = null;
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    $kueri = "INSERT INTO [apar].[dbo].[users] (name, npk, role, password, is_active, photo, remember_token, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?)";
    $params = array($name, $npk, $role, $password_hash, $photo, $remember_token, $created_at, $updated_at);
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

function set_pic_locations($id)
{
    global $koneksi;
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $hasil = true;
    // 1. Delete existing for this user
    $del = sqlsrv_query($koneksi, "DELETE FROM [dbo].[user_pic_locations] WHERE EMPID = ?", [$id]);
    if ($del === false) $hasil = false;

    // 2. Insert new APAR locations
    if (isset($_POST['pic_apar_location']) && is_array($_POST['pic_apar_location'])) {
        foreach ($_POST['pic_apar_location'] as $loc) {
            $ins = sqlsrv_query($koneksi, "INSERT INTO [dbo].[user_pic_locations] (EMPID, device_type, location_name) VALUES (?, 'apar', ?)", [$id, $loc]);
            if ($ins === false) $hasil = false;
        }
    }

    // 3. Insert new Hydrant locations
    if (isset($_POST['pic_hydrant_location']) && is_array($_POST['pic_hydrant_location'])) {
        foreach ($_POST['pic_hydrant_location'] as $loc) {
            $ins = sqlsrv_query($koneksi, "INSERT INTO [dbo].[user_pic_locations] (EMPID, device_type, location_name) VALUES (?, 'hydrant', ?)", [$id, $loc]);
            if ($ins === false) $hasil = false;
        }
    }

    if ($hasil) {
        $_SESSION['success'] = "PIC Lokasi berhasil diperbarui!";
    } else {
        $errors = sqlsrv_errors();
        $_SESSION['error'] = "Gagal memperbarui! " . (isset($errors[0]['message']) ? $errors[0]['message'] : 'Database Error');
    }
    header("Location: ../../index.php?page=user-management");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['_method'] ?? 'POST';

    if ($method === 'PUT' && isset($_POST['id'])) {
        update_user($_POST['id']);
    } else if (isset($_POST['action']) && $_POST['action'] === 'set_pic') {
        set_pic_locations($_POST['id']);
    } else if (isset($_POST['action']) && $_POST['action'] === 'set_status') {
        // Redundant due to centralization but kept for safety logic or removed if desired
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
        // delete_user($_GET['id']);
    }
}