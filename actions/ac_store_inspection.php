<?php
include(__DIR__ . '/../config/db_koneksi.php');

header('Content-Type: application/json');

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
        
        $sql = "INSERT INTO [apar].[dbo].[" . $table . "] (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = sqlsrv_query($koneksi, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception('Insert failed: ' . print_r(sqlsrv_errors(), true));
        }
        
        sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[apars] SET last_inspection_date = ? WHERE id = ?", [$inspection_date, $equipment_id]);
        
    } else {
        $table = 'bimonthly_hydrant_inspections';
        $items = ['body_hydrant', 'selang', 'couple_join', 'nozzle', 'check_sheet', 'valve_kran', 'lampu', 'cover_lampu', 'box_display', 'konsul_hydrant', 'jr', 'marking', 'label'];
        $jenis = $_POST['jenis_hydrant'] ?? 'Unknown';
        
        $cols = ['hydrant_id', 'user_id', 'inspection_date', 'notes', 'jenis_hydrant', 'created_at', 'updated_at'];
        $placeholders = ['?', '?', '?', '?', '?', 'GETDATE()', 'GETDATE()'];
        $params = [$equipment_id, $user_id, $inspection_date, $general_notes, $jenis];
        
        foreach ($items as $item) {
            $ok = (int)($_POST[$item . '_ok'] ?? 0);
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
        
        $sql = "INSERT INTO [apar].[dbo].[" . $table . "] (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = sqlsrv_query($koneksi, $sql, $params);
        
        if ($stmt === false) {
            throw new Exception('Insert failed: ' . print_r(sqlsrv_errors(), true));
        }
        
        sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[hydrants] SET last_inspection_date = ? WHERE id = ?", [$inspection_date, $equipment_id]);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Inspection saved successfully',
        'redirect' => '?page=' . $type . '-detail&id=' . $equipment_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
