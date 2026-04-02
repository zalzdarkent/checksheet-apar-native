<?php
session_start();
require_once __DIR__ . '/../../config/db_koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$type = $_POST['type'] ?? '';

if (!$id || !$type || !in_array($type, ['apar', 'hydrant'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$table = ($type === 'apar') ? '[apar].[dbo].[apar_abnormal_cases]' : '[apar].[dbo].[hydrant_abnormal_cases]';
$master_table = ($type === 'apar') ? '[apar].[dbo].[apars]' : '[apar].[dbo].[hydrants]';
$fk_field = ($type === 'apar') ? 'apar_id' : 'hydrant_id';

if ($action === 'update_detail') {
    $abnormal_case = $_POST['abnormal_case'] ?? '';
    $countermeasure = $_POST['countermeasure'] ?? '';
    $due_date = $_POST['due_date'] ?? null;
    $pic_id = $_POST['pic_id'] ?? null;

    if ($pic_id === '') $pic_id = null;
    if ($due_date === '') $due_date = null;

    $sql = "UPDATE $table SET 
            abnormal_case = ?, 
            countermeasure = ?, 
            due_date = ?, 
            pic_id = ?, 
            updated_at = GETDATE()
            WHERE id = ?";
    
    $params = [$abnormal_case, $countermeasure, $due_date, $pic_id, $id];
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate detail kasus.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Detail kasus berhasil diupdate.']);
    }
    exit;
}

if ($action === 'update_status') {
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['Open', 'On Progress', 'Closed'])) {
        echo json_encode(['status' => 'error', 'message' => 'Status tidak valid.']);
        exit;
    }

    $photoPath = null;
    $new_expired_date = $_POST['new_expired_date'] ?? null;
    if ($new_expired_date === '') $new_expired_date = null;

    if ($status === 'Closed') {
        if (isset($_FILES['repair_photo']) && $_FILES['repair_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../storage/repairs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileInfo = pathinfo($_FILES['repair_photo']['name']);
            $ext = strtolower($fileInfo['extension']);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                echo json_encode(['status' => 'error', 'message' => 'Format file foto tidak valid.']);
                exit;
            }
            
            $filename = uniqid('repair_') . '.' . $ext;
            if (move_uploaded_file($_FILES['repair_photo']['tmp_name'], $uploadDir . $filename)) {
                $photoPath = 'repairs/' . $filename;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload foto.']);
                exit;
            }
        } else {
            // Need to check if it already has a photo
            $chkSql = "SELECT repair_photo FROM $table WHERE id = ?";
            $chkStmt = sqlsrv_query($koneksi, $chkSql, [$id]);
            $chkRow = sqlsrv_fetch_array($chkStmt, SQLSRV_FETCH_ASSOC);
            if (!$chkRow || empty($chkRow['repair_photo'])) {
                echo json_encode(['status' => 'error', 'message' => 'Foto bukti perbaikan wajib diupload saat menutup kasus!']);
                exit;
            }
        }
    }

    $sql = "UPDATE $table SET status = ?, updated_at = GETDATE()";
    $params = [$status];

    if ($photoPath !== null) {
        $sql .= ", repair_photo = ?";
        $params[] = $photoPath;
    }

    if ($new_expired_date !== null) {
        $sql .= ", new_expired_date = ?";
        $params[] = $new_expired_date;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    sqlsrv_begin_transaction($koneksi);

    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt === false) {
        sqlsrv_rollback($koneksi);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status.']);
        exit;
    }

    // Update master table if new expired date is provided and status is Closed
    if ($status === 'Closed' && $new_expired_date !== null) {
        $fetchSql = "SELECT $fk_field FROM $table WHERE id = ?";
        $fetchStmt = sqlsrv_query($koneksi, $fetchSql, [$id]);
        $row = sqlsrv_fetch_array($fetchStmt, SQLSRV_FETCH_ASSOC);
        if ($row && isset($row[$fk_field])) {
            $master_id = $row[$fk_field];
            $updMasterSql = "UPDATE $master_table SET expired_date = ?, status = 'OK' WHERE id = ?";
            $updMasterStmt = sqlsrv_query($koneksi, $updMasterSql, [$new_expired_date, $master_id]);
            if ($updMasterStmt === false) {
                sqlsrv_rollback($koneksi);
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate master expired date.']);
                exit;
            }
        }
    }

    sqlsrv_commit($koneksi);
    echo json_encode(['status' => 'success', 'message' => 'Status berhasil diupdate.']);
    exit;
}

if ($action === 'verify_case') {
    if (strtolower($_SESSION['user_role']) !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Hanya admin yang dapat memverifikasi kasus.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE $table SET 
            status = 'Verified', 
            verified_at = GETDATE(), 
            verified_by = ?, 
            updated_at = GETDATE() 
            WHERE id = ?";
    
    $stmt = sqlsrv_query($koneksi, $sql, [$user_id, $id]);
    
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memverifikasi kasus.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Kasus berhasil diverifikasi.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali.']);
