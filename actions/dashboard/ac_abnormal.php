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

$apar_mapping = [
    'Exp. Date' => 'exp_date',
    'Pressure' => 'pressure',
    'Weight CO2' => 'weight_co2',
    'Tube' => 'tube',
    'Hose' => 'hose',
    'Bracket' => 'bracket',
    'WI' => 'wi',
    'Form Kejadian' => 'form_kejadian',
    'SIGN Kotak' => 'sign_box',
    'SIGN Segitiga' => 'sign_triangle',
    'Marking Tiger' => 'marking_tiger',
    'Marking Beam' => 'marking_beam',
    '5R APAR' => 'sr_apar',
    'Kocok APAR' => 'kocok_apar',
    'Label' => 'label'
];

$hydrant_mapping = [
    'Body Hydrant' => 'body_hydrant',
    'Selang' => 'selang',
    'Couple Join' => 'couple_join',
    'Nozzle' => 'nozzle',
    'Check Sheet' => 'check_sheet',
    'Valve Kran' => 'valve_kran',
    'Lampu' => 'lampu',
    'Cover Lampu' => 'cover_lampu',
    'Kunci Pilar Hydrant' => 'kunci_pilar_hydrant',
    'Pilar Hydrant' => 'pilar_hydrant',
    'Marking' => 'marking',
    'Sign Larangan' => 'sign_larangan',
    'Nomor Hydrant' => 'nomor_hydrant',
    'WI Hydrant' => 'wi_hydrant'
];

if ($action === 'get_inspection_detail') {
    $fetch_sql = "SELECT * FROM $table WHERE id = ?";
    $fetch_stmt = sqlsrv_query($koneksi, $fetch_sql, [$id]);
    $case_row = sqlsrv_fetch_array($fetch_stmt, SQLSRV_FETCH_ASSOC);

    if (!$case_row) {
        echo json_encode(['status' => 'error', 'message' => 'Case not found']);
        exit;
    }

    $inspection_id = $case_row['inspection_id'];
    if (!$inspection_id) {
        echo json_encode(['status' => 'error', 'message' => 'No inspection data linked to this case']);
        exit;
    }

    $inspection_table = ($type === 'apar') ? '[apar].[dbo].[bimonthly_apar_inspections]' : '[apar].[dbo].[bimonthly_hydrant_inspections]';
    
    $ins_sql = "SELECT * FROM $inspection_table WHERE id = ?";
    $ins_stmt = sqlsrv_query($koneksi, $ins_sql, [$inspection_id]);
    $ins_row = sqlsrv_fetch_array($ins_stmt, SQLSRV_FETCH_ASSOC);

    if (!$ins_row) {
        echo json_encode(['status' => 'error', 'message' => 'Inspection detail not found']);
        exit;
    }

    $ng_items = [];
    $mapping = ($type === 'apar') ? $apar_mapping : $hydrant_mapping;
    foreach ($mapping as $label => $col_prefix) {
        $col_ok = $col_prefix . '_ok';
        $col_foto = $col_prefix . '_foto';
        $col_desc = $col_prefix . '_keterangan'; // used for some apar fields
        
        if (isset($ins_row[$col_ok]) && $ins_row[$col_ok] === 0) {
            $ng_items[] = [
                'label' => $label,
                'photo' => $ins_row[$col_foto] ?? null,
                'keterangan' => $ins_row[$col_desc] ?? ''
            ];
        }
    }

    $case_info = [
        'countermeasure' => $case_row['countermeasure'] ?? '-',
        'due_date' => isset($case_row['due_date']) && is_object($case_row['due_date']) ? $case_row['due_date']->format('d/m/Y') : ($case_row['due_date'] ?? '-'),
        'repair_photo' => isset($case_row['repair_photo']) && $case_row['repair_photo'] ? 'storage/' . $case_row['repair_photo'] : null
    ];

    echo json_encode([
        'status' => 'success',
        'inspection_date' => $ins_row['inspection_date'] ? $ins_row['inspection_date']->format('d/m/Y H:i') : '-',
        'inspector_notes' => $ins_row['notes'] ?: '-',
        'ng_items' => $ng_items,
        'case_info' => $case_info
    ]);
    exit;
}

if ($action === 'start_progress') {
    $countermeasure = $_POST['countermeasure'] ?? '';
    $due_date = $_POST['due_date'] ?? null;
    if ($due_date === '') $due_date = null;

    $sql = "UPDATE $table SET status = 'On Progress', countermeasure = ?, due_date = ?, updated_at = GETDATE() WHERE id = ?";
    $stmt = sqlsrv_query($koneksi, $sql, [$countermeasure, $due_date, $id]);
    
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memulai proses.']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Proses perbaikan dimulai.']);
    }
    exit;
}

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
    
    $fetch_sql = "SELECT * FROM $table WHERE id = ?";
    $fetch_stmt = sqlsrv_query($koneksi, $fetch_sql, [$id]);
    $case_row = sqlsrv_fetch_array($fetch_stmt, SQLSRV_FETCH_ASSOC);

    if ($case_row) {
        $inspection_id = $case_row['inspection_id'];
        $abnormal_items_text = $case_row['abnormal_case']; 
        $device_id = $case_row[$fk_field];

        sqlsrv_begin_transaction($koneksi);
        
        // 1. Update case status
        $sql = "UPDATE $table SET 
                status = 'Verified', 
                verified_at = GETDATE(), 
                verified_by = ?, 
                updated_at = GETDATE() 
                WHERE id = ?";
        $stmt = sqlsrv_query($koneksi, $sql, [$user_id, $id]);
        
        // 2. Update Master Table Status
        $updMasterSql = "UPDATE $master_table SET status = 'OK' WHERE id = ?";
        $updMasterStmt = sqlsrv_query($koneksi, $updMasterSql, [$device_id]);
        
        // 3. (Removed Sync to keep original history as NG)
        $updInspStmt = true;
        
        if ($stmt === false || $updMasterStmt === false || $updInspStmt === false) {
            sqlsrv_rollback($koneksi);
            echo json_encode(['status' => 'error', 'message' => 'Gagal memverifikasi kasus.', 'errors' => sqlsrv_errors()]);
        } else {
            sqlsrv_commit($koneksi);
            echo json_encode(['status' => 'success', 'message' => 'Kasus berhasil diverifikasi. Status unit normal, histori NG tersimpan.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data kasus tidak ditemukan.']);
    }
    
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali.']);
