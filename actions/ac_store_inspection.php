<?php
// Start output buffering to catch all output
ob_start();

include(__DIR__ . '/../config/db_koneksi.php');

header('Content-Type: application/json');

// Error handler untuk convert semua errors ke JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Clear any buffered output
    ob_end_clean();
    error_log("PHP ERROR [$errno]: $errstr in $errfile:$errline");
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Server Error: ' . $errstr,
        'error_file' => $errfile,
        'error_line' => $errline
    ]);
    exit;
});

// Exception handler
set_exception_handler(function($e) {
    ob_end_clean();
    error_log("EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Exception: ' . $e->getMessage(),
        'error_trace' => $e->getTraceAsString()
    ]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'] ?? null;
$equipment_id = (int)($_POST['equipment_id'] ?? 0);
$inspection_date_raw = $_POST['inspection_date'] ?? date('Y-m-d H:i');

// Convert datetime-local format (2026-04-01T14:30) to SQL format (2026-04-01 14:30:00)
$inspection_date_formatted = str_replace('T', ' ', $inspection_date_raw) . ':00';
$inspection_date = $inspection_date_formatted;

$general_notes = $_POST['general_notes'] ?? '';

if (!in_array($type, ['apar', 'hydrant']) || $equipment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    $photo_dir = __DIR__ . '/../storage/inspections/';
    if (!is_dir($photo_dir)) {
        mkdir($photo_dir, 0755, true);
    }
    
    $debug_info = [];
    
    // Log all POST data for debugging
    error_log("DEBUG: POST data received:");
    error_log(print_r($_POST, true));

    if ($type === 'apar') {
        $table = 'bimonthly_apar_inspections';
        $items = ['exp_date', 'pressure', 'weight_co2', 'tube', 'hose', 'bracket', 'wi', 'form_kejadian', 'sign_box', 'sign_triangle', 'marking_tiger', 'marking_beam', 'sr_apar', 'kocok_apar', 'label'];
        
        $cols = ['apar_id', 'user_id', 'inspection_date', 'notes', 'created_at', 'updated_at'];
        $placeholders = ['?', '?', '?', '?', 'GETDATE()', 'GETDATE()'];
        $params = [$equipment_id, $user_id, $inspection_date, $general_notes];
        
        foreach ($items as $item) {
            $ok = (int)($_POST[$item . '_ok'] ?? 0);
            $foto_path = '';
            
            if (isset($_FILES[$item . '_foto']) && $_FILES[$item . '_foto']['size'] > 0) {
                $file = $_FILES[$item . '_foto'];
                $filename = 'apar_' . $equipment_id . '_' . $item . '_' . time() . '.jpg';
                $filepath = $photo_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $foto_path = 'storage/inspections/' . $filename;
                }
            }
            
            $cols[] = $item . '_ok';
            $cols[] = $item . '_foto';
            $placeholders[] = '?';
            $placeholders[] = '?';
            $params[] = $ok;
            $params[] = $foto_path;
            
            // Hanya item exp_date dan kocok_apar yang punya kolom keterangan
            $items_with_notes = ['exp_date', 'kocok_apar'];
            if (in_array($item, $items_with_notes) && isset($_POST[$item . '_keterangan'])) {
                $cols[] = $item . '_keterangan';
                $placeholders[] = '?';
                $params[] = $_POST[$item . '_keterangan'] ?? '';
            }
        }
        
        $sql = "INSERT INTO [apar].[dbo].[" . $table . "] (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . "); SELECT SCOPE_IDENTITY() AS last_id;";
        $stmt = sqlsrv_query($koneksi, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception('Insert inspection failed: ' . print_r(sqlsrv_errors(), true));
        }
        
        // Advance to the Second Result Set to get SCOPE_IDENTITY()
        sqlsrv_next_result($stmt);
        $id_row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $last_inspection_id = $id_row['last_id'] ?? null;
        
        error_log("DEBUG: Captured last_inspection_id=" . ($last_inspection_id ?? 'NULL'));

        sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[apars] SET last_inspection_date = ? WHERE id = ?", [$inspection_date, $equipment_id]);
        
        // Auto-detect abnormal items and update status
        $abnormal_items = [];
        $required_items = ['exp_date', 'pressure', 'weight_co2'];
        $item_labels = [
            'exp_date' => 'Exp. Date',
            'pressure' => 'Pressure',
            'weight_co2' => 'Weight CO2',
            'tube' => 'Tube',
            'hose' => 'Hose',
            'bracket' => 'Bracket',
            'wi' => 'WI',
            'form_kejadian' => 'Form Kejadian',
            'sign_box' => 'SIGN Kotak',
            'sign_triangle' => 'SIGN Segitiga',
            'marking_tiger' => 'Marking Tiger',
            'marking_beam' => 'Marking Beam',
            'sr_apar' => '5R APAR',
            'kocok_apar' => 'Kocok APAR',
            'label' => 'Label'
        ];
        
        foreach ($items as $item) {
            $ok_value = (int)($_POST[$item . '_ok'] ?? 1);
            $post_val = $_POST[$item . '_ok'] ?? 'NULL';
            error_log("DEBUG: Item=" . $item . " | POST_VALUE=" . $post_val . " | ok_value=" . $ok_value);
            if ($ok_value === 0) {
                $abnormal_items[] = $item_labels[$item] ?? $item;
            }
        }
        
        $debug_info['abnormal_items_found'] = $abnormal_items;
        $debug_info['post_keys'] = array_keys($_POST);
        error_log("DEBUG: Total abnormal items found: " . count($abnormal_items));
        error_log("DEBUG: All POST keys: " . implode(", ", array_keys($_POST)));
        
        if (!empty($abnormal_items)) {
            error_log("DEBUG: Abnormal items: " . implode(", ", $abnormal_items));
            // Update device status to NG
            $update_sql = "UPDATE [apar].[dbo].[apars] SET status = 'NG' WHERE id = ?";
            $result = sqlsrv_query($koneksi, $update_sql, [$equipment_id]);
            error_log("DEBUG: UPDATE apars result=" . ($result ? 'SUCCESS' : 'FAILED'));
            $debug_info['status_updated'] = ($result ? true : false);
            
            // Capture PIC identity (EMPID) with Fallback to Area PIC
            $info_sql = "SELECT pic_empid, area FROM [apar].[dbo].[apars] WHERE id = ?";
            $info_stmt = sqlsrv_query($koneksi, $info_sql, [$equipment_id]);
            $pic_id = null;
            if ($info_stmt && $row = sqlsrv_fetch_array($info_stmt, SQLSRV_FETCH_ASSOC)) {
                $p_empid = trim($row['pic_empid'] ?? '');
                $p_area = trim($row['area'] ?? '');

                // 1. Use unit's designated PIC
                if (!empty($p_empid)) {
                    $pic_id = $p_empid;
                }

                // 2. Fallback to Area PIC if unit's PIC is empty
                if (empty($pic_id) && !empty($p_area)) {
                    $f_sql = "SELECT TOP 1 EMPID FROM [apar].[dbo].[user_pic_locations] WHERE location_name = ? AND device_type = 'apar'";
                    $f_stmt = sqlsrv_query($koneksi, $f_sql, [$p_area]);
                    if ($f_stmt && $f_row = sqlsrv_fetch_array($f_stmt, SQLSRV_FETCH_ASSOC)) {
                        $pic_id = $f_row['EMPID'];
                    }
                }
            }
            error_log("DEBUG: Storing PIC EMPID [" . ($pic_id ?? 'NULL') . "] in abnormal case.");

            // Create abnormal case record
            $abnormal_case_text = implode(', ', $abnormal_items);
            $abnormal_sql = "INSERT INTO [apar].[dbo].[apar_abnormal_cases] (apar_id, inspection_id, abnormal_case, countermeasure, pic_id, created_at, status) VALUES (?, ?, ?, '-', ?, GETDATE(), 'Open')";
            $result = sqlsrv_query($koneksi, $abnormal_sql, [$equipment_id, $last_inspection_id, $abnormal_case_text, $pic_id]);
            
            error_log("DEBUG: INSERT apar_abnormal_cases result=" . ($result ? 'SUCCESS' : 'FAILED'));
            $debug_info['case_created'] = ($result ? true : false);
            
            if (!$result) {
                $errors = sqlsrv_errors();
                error_log("DEBUG: SQL Error: " . print_r($errors, true));
                throw new Exception('Gagal menyimpan data kasus abnormal APAR: ' . print_r($errors, true));
            }
        }
        
    } else {
        $table = 'bimonthly_hydrant_inspections';
        $items = ['body_hydrant', 'selang', 'couple_join', 'nozzle', 'check_sheet', 'valve_kran', 'lampu', 'cover_lampu', 'kunci_pilar_hydrant', 'pilar_hydrant', 'marking', 'sign_larangan', 'nomor_hydrant', 'wi_hydrant'];
        $jenis = $_POST['jenis_hydrant'] ?? 'Unknown';
        
        $cols = ['hydrant_id', 'user_id', 'inspection_date', 'notes', 'jenis_hydrant', 'created_at', 'updated_at'];
        $placeholders = ['?', '?', '?', '?', '?', 'GETDATE()', 'GETDATE()'];
        $params = [$equipment_id, $user_id, $inspection_date, $general_notes, $jenis];
        
        foreach ($items as $item) {
            $ok = isset($_POST[$item . '_ok']) ? (int)$_POST[$item . '_ok'] : 1;
            $foto_path = '';
            
            if (isset($_FILES[$item . '_foto']) && $_FILES[$item . '_foto']['size'] > 0) {
                $file = $_FILES[$item . '_foto'];
                $filename = 'hydrant_' . $equipment_id . '_' . $item . '_' . time() . '.jpg';
                $filepath = $photo_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $foto_path = 'storage/inspections/' . $filename;
                }
            }
            
            $cols[] = $item . '_ok';
            $cols[] = $item . '_foto';
            $placeholders[] = '?';
            $placeholders[] = '?';
            $params[] = $ok;
            $params[] = $foto_path;
        }
        
        $sql = "INSERT INTO [apar].[dbo].[" . $table . "] (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . "); SELECT SCOPE_IDENTITY() AS last_id;";
        $stmt = sqlsrv_query($koneksi, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception('Insert hydrant inspection failed: ' . print_r(sqlsrv_errors(), true));
        }
        
        // Advance to the Second Result Set to get SCOPE_IDENTITY()
        sqlsrv_next_result($stmt);
        $id_row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $last_inspection_id = $id_row['last_id'] ?? null;
        
        error_log("DEBUG: Captured hydrant last_inspection_id=" . ($last_inspection_id ?? 'NULL'));
        
        sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[hydrants] SET last_inspection_date = ? WHERE id = ?", [$inspection_date, $equipment_id]);
        
        // Auto-detect abnormal items and update status
        $abnormal_items = [];
        $item_labels = [
            'body_hydrant' => 'Body Hydrant',
            'selang' => 'Selang',
            'couple_join' => 'Couple Join',
            'nozzle' => 'Nozzle',
            'check_sheet' => 'Check Sheet',
            'valve_kran' => 'Valve Kran',
            'lampu' => 'Lampu',
            'cover_lampu' => 'Cover Lampu',
            'kunci_pilar_hydrant' => 'Kunci Pilar Hydrant',
            'pilar_hydrant' => 'Pilar Hydrant',
            'marking' => 'Marking',
            'sign_larangan' => 'Sign Larangan',
            'nomor_hydrant' => 'Nomor Hydrant',
            'wi_hydrant' => 'WI Hydrant'
        ];
        
        foreach ($items as $item) {
            $ok_value = (int)($_POST[$item . '_ok'] ?? 1);
            error_log("DEBUG: Hydrant Item=" . $item . " | ok_value=" . $ok_value);
            if ($ok_value === 0) {
                $abnormal_items[] = $item_labels[$item] ?? $item;
            }
        }
        
        $debug_info['abnormal_items_found'] = $abnormal_items;
        error_log("DEBUG: Hydrant Total abnormal items found: " . count($abnormal_items));
        
        if (!empty($abnormal_items)) {
            error_log("DEBUG: Hydrant Abnormal items: " . implode(", ", $abnormal_items));
            // Update device status to NG
            $update_sql = "UPDATE [apar].[dbo].[hydrants] SET status = 'NG' WHERE id = ?";
            $result = sqlsrv_query($koneksi, $update_sql, [$equipment_id]);
            error_log("DEBUG: UPDATE hydrants result=" . ($result ? 'SUCCESS' : 'FAILED'));
            $debug_info['status_updated'] = ($result ? true : false);
            
            // Capture PIC identity (EMPID) with Fallback to Area PIC
            $info_sql = "SELECT pic_empid, area FROM [apar].[dbo].[hydrants] WHERE id = ?";
            $info_stmt = sqlsrv_query($koneksi, $info_sql, [$equipment_id]);
            $pic_id = null;
            if ($info_stmt && $row = sqlsrv_fetch_array($info_stmt, SQLSRV_FETCH_ASSOC)) {
                $p_empid = trim($row['pic_empid'] ?? '');
                $p_area = trim($row['area'] ?? '');

                // 1. Use unit's designated PIC
                if (!empty($p_empid)) {
                    $pic_id = $p_empid;
                }

                // 2. Fallback to Area PIC if unit's PIC is empty
                if (empty($pic_id) && !empty($p_area)) {
                    $f_sql = "SELECT TOP 1 EMPID FROM [apar].[dbo].[user_pic_locations] WHERE location_name = ? AND device_type = 'hydrant'";
                    $f_stmt = sqlsrv_query($koneksi, $f_sql, [$p_area]);
                    if ($f_stmt && $f_row = sqlsrv_fetch_array($f_stmt, SQLSRV_FETCH_ASSOC)) {
                        $pic_id = $f_row['EMPID'];
                    }
                }
            }
            error_log("DEBUG: Storing Hydrant PIC EMPID [" . ($pic_id ?? 'NULL') . "] in abnormal case.");

            // Create abnormal case record
            $abnormal_case_text = implode(', ', $abnormal_items);
            $abnormal_sql = "INSERT INTO [apar].[dbo].[hydrant_abnormal_cases] (hydrant_id, inspection_id, abnormal_case, countermeasure, pic_id, created_at, status) VALUES (?, ?, ?, '-', ?, GETDATE(), 'Open')";
            $result = sqlsrv_query($koneksi, $abnormal_sql, [$equipment_id, $last_inspection_id, $abnormal_case_text, $pic_id]);
            
            error_log("DEBUG: INSERT hydrant_abnormal_cases result=" . ($result ? 'SUCCESS' : 'FAILED'));
            $debug_info['case_created'] = ($result ? true : false);
            
            if (!$result) {
                $errors = sqlsrv_errors();
                error_log("DEBUG: SQL Error: " . print_r($errors, true));
                throw new Exception('Gagal menyimpan data kasus abnormal Hydrant: ' . print_r($errors, true));
            }
        }
    }
    
    // Clear buffer and send clean JSON response
    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Inspection saved successfully',
        'redirect' => '?page=' . $type . '-detail&id=' . $equipment_id,
        'debug' => $debug_info
    ]);
    exit;
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("EXCEPTION: " . $e->getMessage());
    error_log("TRACE: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString()
    ]);
    exit;
}
?>
