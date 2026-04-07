<?php
include(__DIR__ . '/../config/db_koneksi.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$creator_user_id = $_SESSION['user_id'];
$device_type = $_POST['device_type'] ?? '';

sqlsrv_begin_transaction($koneksi);

try {
    $now = date('Y-m-d H:i:s');

    // 1. Prepare Device Data
    $area = $_POST['area'] ?? '';
    $code = $_POST['code'] ?? '';
    $location = $_POST['location'] ?? '';
    $type = $_POST['type'] ?? '';
    $pic_empid = $_POST['pic_empid'] ?? '';
    $x = $_POST['x_coordinate'] ?? 0;
    $y = $_POST['y_coordinate'] ?? 0;
    
    // Validate Basic Info
    if (empty($area) || empty($code) || empty($location)) {
        throw new Exception("Area, Code, dan Lokasi wajib diisi!");
    }

    // Check if code exists
    $table_devices = ($device_type === 'apar') ? '[dbo].[apars]' : '[dbo].[hydrants]';
    $check_sql = "SELECT COUNT(*) as count FROM $table_devices WHERE code = ?";
    $check_stmt = sqlsrv_query($koneksi, $check_sql, [$code]);
    $check_row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
    if ($check_row['count'] > 0) {
        throw new Exception("Kode $code sudah terdaftar!");
    }

    // 2. Insert Device
    if ($device_type === 'apar') {
        $weight = $_POST['weight'] ?? 0;
        $expired_date = $_POST['expired_date'] ?? null;
        $sql_device = "INSERT INTO $table_devices 
                        (code, area, location, type, weight, expired_date, pic_empid, x_coordinate, y_coordinate, status, is_active, created_at, updated_at) 
                        OUTPUT INSERTED.id
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'OK', 1, ?, ?)";
        $params_device = [$code, $area, $location, $type, $weight, $expired_date, $pic_empid, $x, $y, $now, $now];
    } else {
        $sql_device = "INSERT INTO $table_devices 
                        (code, area, location, type, pic_empid, x_coordinate, y_coordinate, status, is_active, created_at, updated_at) 
                        OUTPUT INSERTED.id
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'OK', 1, ?, ?)";
        $params_device = [$code, $area, $location, $type, $pic_empid, $x, $y, $now, $now];
    }

    $stmt_device = sqlsrv_query($koneksi, $sql_device, $params_device);
    if ($stmt_device === false) {
        throw new Exception("Gagal menyimpan data alat: " . json_encode(sqlsrv_errors()));
    }

    // Get New ID from OUTPUT INSERTED
    $id_row = sqlsrv_fetch_array($stmt_device, SQLSRV_FETCH_ASSOC);
    $new_device_id = $id_row['id'] ?? null;

    if (!$new_device_id) {
        throw new Exception("Gagal mendapatkan ID alat baru.");
    }

    // 3. Handle Initial Inspection
    $photo_dir = __DIR__ . '/../storage/inspections/';
    if (!is_dir($photo_dir)) mkdir($photo_dir, 0755, true);

    $general_notes = $_POST['general_notes'] ?? 'Inspeksi Awal Registrasi';
    $inspection_date = $now;

    // Handle Unit Photo (General)
    $unit_photo_path = '';
    if (isset($_FILES['unit_photo']) && $_FILES['unit_photo']['size'] > 0) {
        $file = $_FILES['unit_photo'];
        $filename = 'unit_' . $device_type . '_' . $new_device_id . '_' . time() . '.jpg';
        if (move_uploaded_file($file['tmp_name'], $photo_dir . $filename)) {
            $unit_photo_path = 'storage/inspections/' . $filename;
        }
    }

    if ($device_type === 'apar') {
        $table_inspect = '[dbo].[bimonthly_apar_inspections]';
        $items = ['exp_date', 'pressure', 'weight_co2', 'tube', 'hose', 'bracket', 'wi', 'form_kejadian', 'sign_box', 'sign_triangle', 'marking_tiger', 'marking_beam', 'sr_apar', 'kocok_apar', 'label'];
        $item_labels = [
            'exp_date' => 'Exp. Date', 'pressure' => 'Pressure', 'weight_co2' => 'Weight CO2', 
            'tube' => 'Tube', 'hose' => 'Hose', 'bracket' => 'Bracket', 'wi' => 'WI', 
            'form_kejadian' => 'Form Kejadian', 'sign_box' => 'SIGN Kotak', 'sign_triangle' => 'SIGN Segitiga', 
            'marking_tiger' => 'Marking Tiger', 'marking_beam' => 'Marking Beam', 'sr_apar' => '5R APAR', 
            'kocok_apar' => 'Kocok APAR', 'label' => 'Label'
        ];
        
        $cols = ['apar_id', 'user_id', 'inspection_date', 'notes', 'photo', 'created_at', 'updated_at'];
        $placeholders = ['?', '?', '?', '?', '?', '?', '?'];
        $params = [$new_device_id, $creator_user_id, $inspection_date, $general_notes, $unit_photo_path, $now, $now];
    } else {
        $table_inspect = '[dbo].[bimonthly_hydrant_inspections]';
        $items = ['body_hydrant', 'selang', 'couple_join', 'nozzle', 'check_sheet', 'valve_kran', 'lampu', 'cover_lampu', 'kunci_pilar_hydrant', 'pilar_hydrant', 'marking', 'sign_larangan', 'nomor_hydrant', 'wi_hydrant'];
        $item_labels = [
            'body_hydrant' => 'Body Hydrant', 'selang' => 'Selang', 'couple_join' => 'Couple Join', 
            'nozzle' => 'Nozzle', 'check_sheet' => 'Check Sheet', 'valve_kran' => 'Valve Kran', 
            'lampu' => 'Lampu', 'cover_lampu' => 'Cover Lampu', 'kunci_pilar_hydrant' => 'Kunci Pilar Hydrant', 
            'pilar_hydrant' => 'Pilar Hydrant', 'marking' => 'Marking', 'sign_larangan' => 'Sign Larangan', 
            'nomor_hydrant' => 'Nomor Hydrant', 'wi_hydrant' => 'WI Hydrant'
        ];
        
        $cols = ['hydrant_id', 'user_id', 'inspection_date', 'notes', 'jenis_hydrant', 'photo', 'created_at', 'updated_at'];
        $placeholders = ['?', '?', '?', '?', '?', '?', '?', '?'];
        $params = [$new_device_id, $creator_user_id, $inspection_date, $general_notes, $type, $unit_photo_path, $now, $now];
    }

    $abnormal_items = [];
    foreach ($items as $item) {
        $ok_val = (int)($_POST[$item . '_ok'] ?? 1);
        if ($ok_val === 0) $abnormal_items[] = $item_labels[$item] ?? $item;
        
        $foto_path = '';
        if (isset($_FILES[$item . '_foto']) && $_FILES[$item . '_foto']['size'] > 0) {
            $file = $_FILES[$item . '_foto'];
            $filename = $device_type . '_' . $new_device_id . '_' . $item . '_' . time() . '.jpg';
            if (move_uploaded_file($file['tmp_name'], $photo_dir . $filename)) {
                $foto_path = 'storage/inspections/' . $filename;
            }
        }
        
        $cols[] = $item . '_ok';
        $cols[] = $item . '_foto';
        $placeholders[] = '?';
        $placeholders[] = '?';
        $params[] = $ok_val;
        $params[] = $foto_path;
    }

    // Get Last Inspection ID from OUTPUT INSERTED
    $sql_inspect = "INSERT INTO $table_inspect (" . implode(',', $cols) . ") 
                    OUTPUT INSERTED.id
                    VALUES (" . implode(',', $placeholders) . ")";
    $stmt_inspect = sqlsrv_query($koneksi, $sql_inspect, $params);
    
    if ($stmt_inspect === false) {
        throw new Exception("Gagal menyimpan inspeksi: " . json_encode(sqlsrv_errors()));
    }

    $id_row2 = sqlsrv_fetch_array($stmt_inspect, SQLSRV_FETCH_ASSOC);
    $last_inspection_id = $id_row2['id'] ?? null;

    // Update Device Last Inspection Date
    $update_sql = "UPDATE $table_devices SET last_inspection_date = ? WHERE id = ?";
    sqlsrv_query($koneksi, $update_sql, [$inspection_date, $new_device_id]);

    // 4. Handle Abnormal Cases
    if (!empty($abnormal_items)) {
        // Update device status to NG
        sqlsrv_query($koneksi, "UPDATE $table_devices SET status = 'NG' WHERE id = ?", [$new_device_id]);
        
        $case_text = implode(', ', $abnormal_items);
        if ($device_type === 'apar') {
            $abnormal_sql = "INSERT INTO [dbo].[apar_abnormal_cases] (apar_id, inspection_id, abnormal_case, countermeasure, created_at, status) VALUES (?, ?, ?, '-', GETDATE(), 'Open')";
        } else {
            $abnormal_sql = "INSERT INTO [dbo].[hydrant_abnormal_cases] (hydrant_id, inspection_id, abnormal_case, countermeasure, created_at, status) VALUES (?, ?, ?, '-', GETDATE(), 'Open')";
        }
        sqlsrv_query($koneksi, $abnormal_sql, [$new_device_id, $last_inspection_id, $case_text]);
    }

    sqlsrv_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Alat baru dan inspeksi awal berhasil disimpan.']);

} catch (Exception $e) {
    if ($koneksi) sqlsrv_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
