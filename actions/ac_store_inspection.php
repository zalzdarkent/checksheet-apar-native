<?php
ob_start();
include(__DIR__ . '/../config/db_koneksi.php');
header('Content-Type: application/json');

// Error and Exception Handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'PHP Error: ' . $errstr]);
    exit;
});

set_exception_handler(function($e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'] ?? null;
$equipment_id = (int)($_POST['equipment_id'] ?? 0);
$inspection_date_raw = $_POST['inspection_date'] ?? date('Y-m-d H:i');
$inspection_date = str_replace('T', ' ', $inspection_date_raw) . ':00';
$general_notes = $_POST['general_notes'] ?? '';

if (!in_array($type, ['apar', 'hydrant']) || $equipment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    $photo_dir = __DIR__ . '/../storage/inspections/';
    if (!is_dir($photo_dir)) mkdir($photo_dir, 0755, true);
    
    // 1. Prepare Columns for FLAT TRANS Table
    $cols = ['asset_id', 'user_id', 'inspection_date', 'notes'];
    $placeholders = ['?', '?', '?', '?'];
    $params = [$equipment_id, $user_id, $inspection_date, $general_notes];
    
    $check_items = [];
    if ($type === 'apar') {
        $check_items = ['exp_date', 'pressure', 'weight_co2', 'tube', 'hose', 'bracket', 'wi', 'form_kejadian', 'sign_box', 'sign_triangle', 'marking_tiger', 'marking_beam', 'sr_apar', 'kocok_apar', 'label'];
    } else {
        $check_items = ['body_hydrant', 'selang', 'couple_join', 'nozzle', 'check_sheet', 'valve_kran', 'lampu', 'cover_lampu', 'kunci_pilar_hydrant', 'pilar_hydrant', 'marking', 'sign_larangan', 'nomor_hydrant', 'wi_hydrant'];
    }

    $abnormal_items = [];
    $item_labels = [
        'exp_date' => 'Exp. Date', 'pressure' => 'Pressure', 'weight_co2' => 'Weight CO2', 'tube' => 'Tube', 'hose' => 'Hose', 
        'bracket' => 'Bracket', 'wi' => 'WI', 'form_kejadian' => 'Form Kejadian', 'sign_box' => 'SIGN Kotak', 
        'sign_triangle' => 'SIGN Segitiga', 'marking_tiger' => 'Marking Tiger', 'marking_beam' => 'Marking Beam', 
        'sr_apar' => '5R APAR', 'kocok_apar' => 'Kocok APAR', 'label' => 'Label',
        'body_hydrant' => 'Body Hydrant', 'selang' => 'Selang', 'couple_join' => 'Couple Join', 'nozzle' => 'Nozzle',
        'check_sheet' => 'Check Sheet', 'valve_kran' => 'Valve Kran', 'lampu' => 'Lampu', 'cover_lampu' => 'Cover Lampu',
        'kunci_pilar_hydrant' => 'Kunci Pilar Hydrant', 'pilar_hydrant' => 'Pilar Hydrant', 'marking' => 'Marking',
        'sign_larangan' => 'Sign Larangan', 'nomor_hydrant' => 'Nomor Hydrant', 'wi_hydrant' => 'WI Hydrant'
    ];

    foreach ($check_items as $item) {
        $ok = (int)($_POST[$item . '_ok'] ?? 1);
        $foto_path = '';
        
        if (isset($_FILES[$item . '_foto']) && $_FILES[$item . '_foto']['size'] > 0) {
            $file = $_FILES[$item . '_foto'];
            $filename = $type . '_' . $equipment_id . '_' . $item . '_' . time() . '.jpg';
            if (move_uploaded_file($file['tmp_name'], $photo_dir . $filename)) {
                $foto_path = 'storage/inspections/' . $filename;
            }
        }
        
        $cols[] = $item . '_ok';
        $cols[] = $item . '_foto';
        $placeholders[] = '?';
        $placeholders[] = '?';
        $params[] = $ok;
        $params[] = $foto_path;

        if ($ok === 0) {
            $abnormal_items[] = [
                'alias' => $item,
                'label' => $item_labels[$item] ?? $item,
                'photo' => $foto_path
            ];
        }
    }

    // 2. Insert into SE_FIRE_PROTECTION_TRANS
    $sql_trans = "INSERT INTO [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . "); SELECT SCOPE_IDENTITY() AS last_id;";
    $stmt = sqlsrv_query($koneksi, $sql_trans, $params);
    if ($stmt === false) throw new Exception(print_r(sqlsrv_errors(), true));
    
    sqlsrv_next_result($stmt);
    $id_row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $trans_id = $id_row['last_id'];

    // 3. Update Master Last Inspection
    sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] SET last_inspection_date = ? WHERE id = ?", [$inspection_date, $equipment_id]);

    // 4. Handle Abnormal Findings (LINES)
    if (!empty($abnormal_items)) {
        sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] SET status = 'NG' WHERE id = ?", [$equipment_id]);
        
        // Fetch PIC EMPID from Master
        $sql_pic = "SELECT pic_empid FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE id = ?";
        $res_pic = sqlsrv_query($koneksi, $sql_pic, [$equipment_id]);
        $pic_empid = null;
        if ($res_pic && $row_pic = sqlsrv_fetch_array($res_pic, SQLSRV_FETCH_ASSOC)) {
            $pic_empid = $row_pic['pic_empid'];
        }

        // INSERT separate rows for each abnormal finding (No longer consolidated)
        foreach ($abnormal_items as $ab_item) {
            $sql_line = "INSERT INTO [apar].[dbo].[SE_FIRE_PROTECTION_LINES] 
                         (trans_id, asset_id, check_item_alias, finding_desc, repair_status, pic_empid, photo_evidence, created_at) 
                         VALUES (?, ?, ?, ?, 'Open', ?, ?, GETDATE())";
            sqlsrv_query($koneksi, $sql_line, [
                $trans_id, 
                $equipment_id, 
                $ab_item['alias'], 
                $ab_item['label'], 
                $pic_empid, 
                $ab_item['photo']
            ]);
        }
    }

    ob_end_clean();
    echo json_encode([
        'status' => 'success',
        'message' => 'Inspection saved successfully',
        'redirect' => '?page=' . $type . '-detail&id=' . $equipment_id
    ]);

} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
