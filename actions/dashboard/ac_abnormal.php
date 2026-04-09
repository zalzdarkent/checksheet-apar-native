<?php
session_start();
require_once __DIR__ . '/../../config/db_koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? ''; // This is the ID from SE_FIRE_PROTECTION_LINES
$type = $_POST['type'] ?? '';

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: No ID provided']);
    exit;
}

header('Content-Type: application/json');

try {
    if ($action === 'get_inspection_detail') {
        // Fetch from unified LINES table joined with ALL TRANS columns for photo retrieval
        $fetch_sql = "SELECT l.*, t.*, t.notes as inspector_notes 
                      FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l
                      LEFT JOIN [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t ON l.trans_id = t.id
                      WHERE l.id = ?";
        $fetch_stmt = sqlsrv_query($koneksi, $fetch_sql, [$id]);
        $case_row = sqlsrv_fetch_array($fetch_stmt, SQLSRV_FETCH_ASSOC);

        if (!$case_row) throw new Exception('Case not found');

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

        $ng_items = [];
        $aliases = explode(',', $case_row['check_item_alias'] ?? '');
        
        foreach ($aliases as $alias) {
            $alias = trim($alias);
            if (!$alias) continue;
            
            // For consolidated legacy data, pull specific photos from the TRANS row columns (e.g. tube_foto)
            // If it's a new separate row, photo_evidence might already be enough, but pulling from trans is safer/consistent
            $photo = $case_row['photo_evidence'];
            $photo_col = $alias . '_foto';
            if (isset($case_row[$photo_col]) && !empty($case_row[$photo_col])) {
                $photo = $case_row[$photo_col];
            }

            $ng_items[] = [
                'label' => $item_labels[$alias] ?? $case_row['finding_desc'],
                'photo' => $photo ? $photo : null,
                'keterangan' => (count($aliases) > 1) ? '' : ($case_row['finding_desc'] != ($item_labels[$alias] ?? '') ? $case_row['finding_desc'] : '')
            ];
        }

        // If for some reason aliases were empty, fallback to the single finding_desc
        if (empty($ng_items)) {
            $ng_items[] = [
                'label' => $case_row['finding_desc'],
                'photo' => $case_row['photo_evidence'],
                'keterangan' => ''
            ];
        }

        $case_info = [
            'countermeasure' => $case_row['countermeasure'] ?? '-',
            'due_date' => $case_row['due_date'] instanceof DateTime ? $case_row['due_date']->format('d/m/Y') : ($case_row['due_date'] ?? '-'),
            'repair_photo' => $case_row['repair_photo'] ? 'storage/' . $case_row['repair_photo'] : null
        ];

        echo json_encode([
            'status' => 'success',
            'inspection_date' => $case_row['inspection_date'] instanceof DateTime ? $case_row['inspection_date']->format('d/m/Y H:i') : '-',
            'inspector_notes' => $case_row['inspector_notes'] ?: '-',
            'ng_items' => $ng_items,
            'case_info' => $case_info
        ]);
        exit;
    }

    if ($action === 'start_progress') {
        $countermeasure = $_POST['countermeasure'] ?? '';
        $due_date = $_POST['due_date'] ?: null;

        $sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_LINES] 
                SET repair_status = 'On Progress', countermeasure = ?, due_date = ?, updated_at = GETDATE() 
                WHERE id = ?";
        $stmt = sqlsrv_query($koneksi, $sql, [$countermeasure, $due_date, $id]);
        if ($stmt === false) throw new Exception('Gagal memulai proses.');
        
        echo json_encode(['status' => 'success', 'message' => 'Proses perbaikan dimulai.']);
        exit;
    }

    if ($action === 'update_detail') {
        $finding_desc = $_POST['abnormal_case'] ?? '';
        $countermeasure = $_POST['countermeasure'] ?? '';
        $due_date = $_POST['due_date'] ?: null;
        $pic_id = $_POST['pic_id'] ?: null;

        $sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_LINES] SET 
                finding_desc = ?, 
                countermeasure = ?, 
                due_date = ?, 
                verified_by = ?, 
                updated_at = GETDATE()
                WHERE id = ?";
        
        $stmt = sqlsrv_query($koneksi, $sql, [$finding_desc, $countermeasure, $due_date, $pic_id, $id]);
        if ($stmt === false) throw new Exception('Gagal mengupdate detail kasus.');
        
        echo json_encode(['status' => 'success', 'message' => 'Detail kasus berhasil diupdate.']);
        exit;
    }

    if ($action === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        $photoPath = null;

        if ($new_status === 'Closed') {
            if (isset($_FILES['repair_photo']) && $_FILES['repair_photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../storage/repairs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = pathinfo($_FILES['repair_photo']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('repair_') . '.' . $ext;
                if (move_uploaded_file($_FILES['repair_photo']['tmp_name'], $uploadDir . $filename)) {
                    $photoPath = 'repairs/' . $filename;
                }
            }
        }

        $sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_LINES] SET repair_status = ?, updated_at = GETDATE()";
        $params = [$new_status];
        if ($photoPath) { $sql .= ", repair_photo = ?"; $params[] = $photoPath; }
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = sqlsrv_query($koneksi, $sql, $params);
        if ($stmt === false) throw new Exception('Gagal mengupdate status.');
        
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diupdate.']);
        exit;
    }

    if ($action === 'verify_case') {
        if (strtolower($_SESSION['user_role'] ?? '') !== 'admin') throw new Exception('Only admin can verify.');

        sqlsrv_begin_transaction($koneksi);
        
        // 1. Get Asset ID
        $get_asset = sqlsrv_query($koneksi, "SELECT asset_id FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] WHERE id = ?", [$id]);
        $asset_row = sqlsrv_fetch_array($get_asset, SQLSRV_FETCH_ASSOC);
        $asset_id = $asset_row['asset_id'];

        // 2. Update line status
        $sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_LINES] SET 
                repair_status = 'Verified', 
                verified_at = GETDATE(), 
                verified_by = ?, 
                updated_at = GETDATE() 
                WHERE id = ?";
        sqlsrv_query($koneksi, $sql, [$_SESSION['user_id'], $id]);
        
        // 3. Check if all other issues for this asset are verified
        $check_others = sqlsrv_query($koneksi, "SELECT COUNT(*) as open_issues FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] WHERE asset_id = ? AND repair_status <> 'Verified'", [$asset_id]);
        $others_row = sqlsrv_fetch_array($check_others, SQLSRV_FETCH_ASSOC);
        
        if ($others_row['open_issues'] == 0) {
            // All verified, unit is OK
            sqlsrv_query($koneksi, "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] SET status = 'OK' WHERE id = ?", [$asset_id]);
        }
        
        sqlsrv_commit($koneksi);
        echo json_encode(['status' => 'success', 'message' => 'Kasus berhasil diverifikasi.']);
        exit;
    }

} catch (Exception $e) {
    if ($koneksi) sqlsrv_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
