<?php
include(__DIR__ . '/../config/db_koneksi.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$creator_user_id = $_SESSION['user_id'];
$device_type = $_POST['device_type'] ?? ''; // 'apar' or 'hydrant'

sqlsrv_begin_transaction($koneksi);

try {
    $now = date('Y-m-d H:i:s');
    $area = $_POST['area'] ?? '';
    $code = $_POST['code'] ?? '';
    $location = $_POST['location'] ?? '';
    $model_type = $_POST['type'] ?? '';
    $pic_empid = $_POST['pic_empid'] ?? '';
    $x = $_POST['x_coordinate'] ?? 0;
    $y = $_POST['y_coordinate'] ?? 0;
    
    if (empty($area) || empty($code) || empty($location)) {
        throw new Exception("Area, Code, dan Lokasi wajib diisi!");
    }

    // 1. Check if asset_code exists in Master
    $check_sql = "SELECT COUNT(*) as count FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE asset_code = ?";
    $check_stmt = sqlsrv_query($koneksi, $check_sql, [$code]);
    $check_row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);
    if ($check_row['count'] > 0) throw new Exception("Kode $code sudah terdaftar!");

    // 2. Insert into MASTER
    $weight = ($device_type === 'apar') ? ($_POST['weight'] ?? 0) : null;
    $expired_date = ($device_type === 'apar') ? ($_POST['expired_date'] ?? null) : null;
    
    $sql_master = "INSERT INTO [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] 
                    (asset_code, asset_type, area, location, model_type, weight, expired_date, pic_empid, x_coordinate, y_coordinate, status, is_active, created_at, updated_at) 
                    OUTPUT INSERTED.id
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'OK', 1, ?, ?)";
    $params_master = [$code, strtoupper($device_type), $area, $location, $model_type, $weight, $expired_date, $pic_empid, $x, $y, $now, $now];
    
    $stmt_master = sqlsrv_query($koneksi, $sql_master, $params_master);
    if ($stmt_master === false) throw new Exception("Gagal simpan master: " . print_r(sqlsrv_errors(), true));
    
    $id_row = sqlsrv_fetch_array($stmt_master, SQLSRV_FETCH_ASSOC);
    $new_asset_id = $id_row['id'];

    // 3. Initial Inspection (TRANS)
    $photo_dir = __DIR__ . '/../storage/inspections/';
    if (!is_dir($photo_dir)) mkdir($photo_dir, 0755, true);

    $cols = ['asset_id', 'user_id', 'inspection_date', 'notes'];
    $placeholders = ['?', '?', '?', '?'];
    $params_trans = [$new_asset_id, $creator_user_id, $now, 'Registrasi Awal'];

    if ($device_type === 'apar') {
        $check_items = ['exp_date', 'pressure', 'weight_co2', 'tube', 'hose', 'bracket', 'wi', 'form_kejadian', 'sign_box', 'sign_triangle', 'marking_tiger', 'marking_beam', 'sr_apar', 'kocok_apar', 'label'];
    } else {
        $check_items = ['body_hydrant', 'selang', 'couple_join', 'nozzle', 'check_sheet', 'valve_kran', 'lampu', 'cover_lampu', 'kunci_pilar_hydrant', 'pilar_hydrant', 'marking', 'sign_larangan', 'nomor_hydrant', 'wi_hydrant'];
    }

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

    $abnormal_items = [];
    foreach ($check_items as $item) {
        $ok = (int)($_POST[$item . '_ok'] ?? 1);
        $foto_path = '';
        if (isset($_FILES[$item . '_foto']) && $_FILES[$item . '_foto']['size'] > 0) {
            $filename = 'initial_' . $device_type . '_' . $new_asset_id . '_' . $item . '_' . time() . '.jpg';
            if (move_uploaded_file($_FILES[$item . '_foto']['tmp_name'], $photo_dir . $filename)) {
                $foto_path = 'storage/inspections/' . $filename;
            }
        }
        $cols[] = $item . '_ok'; $cols[] = $item . '_foto';
        $placeholders[] = '?'; $placeholders[] = '?';
        $params_trans[] = $ok; $params_trans[] = $foto_path;

        if ($ok === 0) {
            $abnormal_items[] = [
                'alias' => $item,
                'label' => $item_labels[$item],
                'photo' => $foto_path
            ];
        }
    }

    $sql_trans = "INSERT INTO [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] (" . implode(',', $cols) . ") 
                 OUTPUT INSERTED.id VALUES (" . implode(',', $placeholders) . ")";
    $stmt_trans = sqlsrv_query($koneksi, $sql_trans, $params_trans);
    if ($stmt_trans === false) throw new Exception("Gagal simpan inspeksi: " . print_r(sqlsrv_errors(), true));
    
    $trans_row = sqlsrv_fetch_array($stmt_trans, SQLSRV_FETCH_ASSOC);
    $trans_id = $trans_row['id'];

    sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] SET last_inspection_date = ? WHERE id = ?", [$now, $new_asset_id]);

    // 4. Abnormal Cases (LINES)
    if (!empty($abnormal_items)) {
        sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] SET status = 'NG' WHERE id = ?", [$new_asset_id]);
        
        $labels = array_column($abnormal_items, 'label');
        $aliases = array_column($abnormal_items, 'alias');
        $finding_desc = implode(', ', $labels);
        $alias_summary = implode(',', $aliases);
        $first_photo = $abnormal_items[0]['photo'] ?? '';

        $sql_line = "INSERT INTO [apar].[dbo].[SE_FIRE_PROTECTION_LINES] 
                     (trans_id, asset_id, check_item_alias, finding_desc, repair_status, pic_empid, photo_evidence, created_at) 
                     VALUES (?, ?, ?, ?, 'Open', ?, ?, GETDATE())";
        sqlsrv_query($koneksi, $sql_line, [$trans_id, $new_asset_id, $alias_summary, $finding_desc, $pic_empid, $first_photo]);
    }

    sqlsrv_commit($koneksi);
    echo json_encode(['success' => true, 'message' => 'Alat baru berhasil didaftarkan.']);

} catch (Exception $e) {
    if ($koneksi) sqlsrv_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
