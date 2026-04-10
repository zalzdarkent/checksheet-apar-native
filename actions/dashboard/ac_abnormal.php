<?php
session_start();
require_once __DIR__ . '/../../config/db_koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$id     = $_POST['id'] ?? ''; // Sekarang = asset_id (id dari SE_FIRE_PROTECTION_MASTER)
$type   = $_POST['type'] ?? '';

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: No ID provided']);
    exit;
}

header('Content-Type: application/json');

// Label mapping untuk setiap check item alias
$item_labels = [
    'exp_date'            => 'Exp. Date',
    'pressure'            => 'Pressure',
    'weight_co2'          => 'Weight CO2',
    'tube'                => 'Tube',
    'hose'                => 'Hose',
    'bracket'             => 'Bracket',
    'wi'                  => 'WI',
    'form_kejadian'       => 'Form Kejadian',
    'sign_box'            => 'SIGN Kotak',
    'sign_triangle'       => 'SIGN Segitiga',
    'marking_tiger'       => 'Marking Tiger',
    'marking_beam'        => 'Marking Beam',
    'sr_apar'             => '5R APAR',
    'kocok_apar'          => 'Kocok APAR',
    'label'               => 'Label',
    'body_hydrant'        => 'Body Hydrant',
    'selang'              => 'Selang',
    'couple_join'         => 'Couple Join',
    'nozzle'              => 'Nozzle',
    'check_sheet'         => 'Check Sheet',
    'valve_kran'          => 'Valve Kran',
    'lampu'               => 'Lampu',
    'cover_lampu'         => 'Cover Lampu',
    'box_display'         => 'Box Display',
    'konsul_hydrant'      => 'Konsul Hydrant',
    'jr'                  => 'JR',
    'kunci_pilar_hydrant' => 'Kunci Pilar Hydrant',
    'pilar_hydrant'       => 'Pilar Hydrant',
    'marking'             => 'Marking',
    'sign_larangan'       => 'Sign Larangan',
    'nomor_hydrant'       => 'Nomor Hydrant',
    'wi_hydrant'          => 'WI Hydrant',
];

try {

    // =========================================================
    // ACTION: Detail semua item NG aktif untuk 1 asset
    // $id = asset_id
    // =========================================================
    if ($action === 'get_inspection_detail') {
        $fetch_sql = "
            SELECT l.*, t.notes AS inspector_notes, t.inspection_date,
                   m.asset_code, m.area, m.location, m.asset_type
            FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES] l
            LEFT JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] t ON l.trans_id = t.id
            INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON l.asset_id = m.id
            WHERE l.asset_id = ?
              AND l.repair_status IN ('Open', 'On Progress', 'Closed', 'Revision')
            ORDER BY l.id ASC";

        $fetch_stmt = sqlsrv_query($koneksi, $fetch_sql, [$id]);
        $rows = [];
        while ($row = sqlsrv_fetch_array($fetch_stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
        if (empty($rows)) throw new Exception('Case not found');

        $first    = $rows[0];
        $ng_items = [];

        foreach ($rows as $case_row) {
            $aliases = explode(',', $case_row['check_item_alias'] ?? '');
            foreach ($aliases as $alias) {
                $alias = trim($alias);
                if (!$alias) continue;

                // Coba ambil foto temuan dari kolom TRANS (e.g. pressure_foto)
                $photo     = $case_row['photo_evidence'] ?? null;
                $photo_col = $alias . '_foto';
                if (isset($case_row[$photo_col]) && !empty($case_row[$photo_col])) {
                    $photo = $case_row[$photo_col];
                }

                $ng_items[] = [
                    'line_id'         => $case_row['id'],
                    'label'           => $item_labels[$alias] ?? $case_row['finding_desc'],
                    'keterangan'      => $case_row['finding_desc'],
                    'photo'           => $photo ? 'storage/inspections/' . $photo : null,
                    'repair_photo'    => $case_row['repair_photo'] ? 'storage/' . $case_row['repair_photo'] : null,
                    'status'          => $case_row['repair_status'],
                    'revision_count'  => (int)($case_row['revision_count'] ?? 0),
                    'revision_notes'  => $case_row['revision_notes'] ?? null,
                ];
            }
        }

        // Fallback jika aliases kosong
        if (empty($ng_items)) {
            foreach ($rows as $case_row) {
                $ng_items[] = [
                    'line_id'         => $case_row['id'],
                    'label'           => $case_row['finding_desc'],
                    'keterangan'      => '',
                    'photo'           => $case_row['photo_evidence'] ? 'storage/inspections/' . $case_row['photo_evidence'] : null,
                    'repair_photo'    => $case_row['repair_photo'] ? 'storage/' . $case_row['repair_photo'] : null,
                    'status'          => $case_row['repair_status'],
                    'revision_count'  => (int)($case_row['revision_count'] ?? 0),
                    'revision_notes'  => $case_row['revision_notes'] ?? null,
                ];
            }
        }

        // Ambil info perbaikan dari row pertama yang punya countermeasure
        $case_info = null;
        foreach ($rows as $r) {
            if (!empty($r['countermeasure']) || !empty($r['due_date'])) {
                $case_info = [
                    'countermeasure' => $r['countermeasure'] ?? '-',
                    'due_date'       => $r['due_date'] instanceof DateTime
                        ? $r['due_date']->format('d/m/Y')
                        : ($r['due_date'] ?? '-'),
                ];
                break;
            }
        }

        echo json_encode([
            'status'          => 'success',
            'inspection_date' => $first['inspection_date'] instanceof DateTime
                ? $first['inspection_date']->format('d/m/Y H:i') : '-',
            'inspector_notes' => $first['inspector_notes'] ?: '-',
            'asset_info'      => [
                'code'     => $first['asset_code'],
                'area'     => $first['area'],
                'location' => $first['location'],
                'type'     => $first['asset_type']
            ],
            'ng_items'        => $ng_items,
            'case_info'       => $case_info,
        ]);
        exit;
    }

    // =========================================================
    // ACTION: Ambil semua item NG "On Progress" untuk modal close
    //         Dipakai untuk populate 1 file input per item NG
    // $id = asset_id
    // =========================================================
    if ($action === 'get_ng_items_for_close') {
        $sql = "
            SELECT l.id, l.check_item_alias, l.finding_desc
            FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES] l
            WHERE l.asset_id = ? AND l.repair_status = 'On Progress'
            ORDER BY l.id ASC";
        $stmt = sqlsrv_query($koneksi, $sql, [$id]);

        $items       = [];
        $has_expired = false;

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $aliases     = explode(',', $row['check_item_alias'] ?? '');
            $first_alias = trim($aliases[0]);

            // Cek apakah ada item terkait expired date
            if (
                stripos($first_alias, 'exp_date') !== false ||
                stripos($row['finding_desc'], 'expired') !== false ||
                stripos($row['finding_desc'], 'kadaluarsa') !== false
            ) {
                $has_expired = true;
            }

            $items[] = [
                'line_id' => $row['id'],
                'alias'   => $first_alias,
                'label'   => $item_labels[$first_alias] ?? $row['finding_desc'],
                'finding' => $row['finding_desc'],
            ];
        }

        echo json_encode(['status' => 'success', 'items' => $items, 'has_expired' => $has_expired]);
        exit;
    }

    // =========================================================
    // ACTION: Mulai proses perbaikan (Open → On Progress)
    //         Update SEMUA baris 'Open' milik asset ini
    // $id = asset_id
    // =========================================================
    if ($action === 'start_progress') {
        $countermeasure = $_POST['countermeasure'] ?? '';
        $due_date       = $_POST['due_date'] ?: null;

        $sql = "
            UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
            SET repair_status = 'On Progress',
                countermeasure = ?,
                due_date       = ?,
                updated_at     = GETDATE()
            WHERE asset_id = ? AND repair_status = 'Open'";

        $stmt = sqlsrv_query($koneksi, $sql, [$countermeasure, $due_date, $id]);
        if ($stmt === false) throw new Exception('Gagal memulai proses.');

        echo json_encode(['status' => 'success', 'message' => 'Proses perbaikan dimulai.']);
        exit;
    }

    // =========================================================
    // ACTION: Update detail kasus (masih per-line untuk keperluan admin)
    // $id = line_id
    // =========================================================
    if ($action === 'update_detail') {
        $finding_desc   = $_POST['abnormal_case'] ?? '';
        $countermeasure = $_POST['countermeasure'] ?? '';
        $due_date       = $_POST['due_date'] ?: null;
        $pic_id         = $_POST['pic_id'] ?: null;

        $sql = "
            UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
            SET finding_desc   = ?,
                countermeasure = ?,
                due_date       = ?,
                pic_empid      = ?,
                updated_at     = GETDATE()
            WHERE id = ?";
        $stmt = sqlsrv_query($koneksi, $sql, [$finding_desc, $countermeasure, $due_date, $pic_id, $id]);
        if ($stmt === false) throw new Exception('Gagal mengupdate detail kasus.');

        echo json_encode(['status' => 'success', 'message' => 'Detail kasus berhasil diupdate.']);
        exit;
    }

    // =========================================================
    // ACTION: Selesaikan perbaikan (On Progress → Closed)
    //         Upload 1 foto per item NG. File key: repair_photo_{line_id}
    // $id = asset_id
    // =========================================================
    if ($action === 'update_status') {
        $new_status = $_POST['status'] ?? '';

        if ($new_status === 'Closed') {
            $uploadDir = __DIR__ . '/../../storage/repairs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            sqlsrv_begin_transaction($koneksi);

            // Ambil semua 'On Progress' lines untuk asset ini
            $lines_sql  = "
                SELECT id FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
                WHERE asset_id = ? AND repair_status = 'On Progress'";
            $lines_stmt = sqlsrv_query($koneksi, $lines_sql, [$id]);
            if ($lines_stmt === false) throw new Exception('Gagal mengambil data lines.');

            $updated = 0;
            while ($line = sqlsrv_fetch_array($lines_stmt, SQLSRV_FETCH_ASSOC)) {
                $line_id   = $line['id'];
                $photoPath = null;
                $file_key  = 'repair_photo_' . $line_id;

                // Upload foto untuk item NG ini (1 foto per line)
                if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $ext      = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('repair_') . '.' . $ext;
                    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $uploadDir . $filename)) {
                        $photoPath = 'repairs/' . $filename;
                    }
                }

                $upd_sql    = "UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
                               SET repair_status = 'Closed', updated_at = GETDATE()";
                $upd_params = [];
                if ($photoPath) {
                    $upd_sql .= ", repair_photo = ?";
                    $upd_params[] = $photoPath;
                }
                $upd_sql .= " WHERE id = ?";
                $upd_params[] = $line_id;

                $upd_stmt = sqlsrv_query($koneksi, $upd_sql, $upd_params);
                if ($upd_stmt === false) throw new Exception('Gagal update line ' . $line_id);
                $updated++;
            }

            // Update expired date pada master jika ada
            $new_expired = $_POST['new_expired_date'] ?? '';
            if ($new_expired) {
                sqlsrv_query($koneksi,
                    "UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER]
                     SET expired_date = ?, updated_at = GETDATE()
                     WHERE id = ?",
                    [$new_expired, $id]
                );
            }

            sqlsrv_commit($koneksi);
            echo json_encode([
                'status'  => 'success',
                'message' => $updated . ' item perbaikan berhasil dikonfirmasi & ditutup.',
            ]);
            exit;
        }

        throw new Exception('Status tidak dikenali.');
    }

    // =========================================================
    // ACTION: Verifikasi (Closed → Verified) — Admin only
    //         Update SEMUA baris 'Closed' milik asset ini
    // $id = asset_id
    // =========================================================
    if ($action === 'verify_case') {
        if (strtolower($_SESSION['user_role'] ?? '') !== 'admin')
            throw new Exception('Only admin can verify.');

        sqlsrv_begin_transaction($koneksi);

        // Update semua Closed lines → Verified
        $sql = "
            UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
            SET repair_status = 'Verified',
                verified_at   = GETDATE(),
                verified_by   = ?,
                updated_at    = GETDATE()
            WHERE asset_id = ? AND repair_status = 'Closed'";
        sqlsrv_query($koneksi, $sql, [$_SESSION['user_id'], $id]);

        // Cek apakah semua NG untuk asset ini sudah Verified
        $check     = sqlsrv_query($koneksi,
            "SELECT COUNT(*) AS open_count
             FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
             WHERE asset_id = ? AND repair_status <> 'Verified'",
            [$id]
        );
        $check_row = sqlsrv_fetch_array($check, SQLSRV_FETCH_ASSOC);

        if ($check_row['open_count'] == 0) {
            // Semua NG sudah beres → kembalikan status unit ke OK
            sqlsrv_query($koneksi,
                "UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER]
                 SET status = 'OK', updated_at = GETDATE()
                 WHERE id = ?",
                [$id]
            );
        }

        sqlsrv_commit($koneksi);
        echo json_encode(['status' => 'success', 'message' => 'Semua item perbaikan berhasil diverifikasi.']);
        exit;
    }

    // =========================================================
    // ACTION: submit_decision — Admin submit keputusan per-item:
    //         item dipilih  → Revision
    //         item lainnya  → Verified
    //         Jika tidak ada yang dipilih → semua Verified
    // $id = asset_id
    // =========================================================
    if ($action === 'submit_decision') {
        if (strtolower($_SESSION['user_role'] ?? '') !== 'admin')
            throw new Exception('Hanya admin yang bisa melakukan verifikasi.');

        $revision_line_ids = json_decode($_POST['revision_line_ids'] ?? '[]', true) ?: [];
        $revision_notes    = trim($_POST['revision_notes'] ?? '');
        $admin_id          = $_SESSION['user_id'];

        sqlsrv_begin_transaction($koneksi);

        // Ambil semua Closed lines untuk asset ini
        $lines_sql  = "SELECT id, repair_photo, revision_count
                       FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
                       WHERE asset_id = ? AND repair_status = 'Closed'";
        $lines_stmt = sqlsrv_query($koneksi, $lines_sql, [$id]);
        if ($lines_stmt === false) throw new Exception('Gagal mengambil data lines.');

        $verified_count = 0;
        $revision_count_saved = 0;

        while ($line = sqlsrv_fetch_array($lines_stmt, SQLSRV_FETCH_ASSOC)) {
            $line_id = $line['id'];

            if (in_array((string)$line_id, array_map('strval', $revision_line_ids))) {
                // → Revision
                $new_rev_count = ((int)($line['revision_count'] ?? 0)) + 1;
                $upd = sqlsrv_query($koneksi,
                    "UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
                     SET repair_status   = 'Revision',
                         revision_notes  = ?,
                         revision_at     = GETDATE(),
                         revision_by     = ?,
                         revision_count  = ?,
                         updated_at      = GETDATE()
                     WHERE id = ?",
                    [$revision_notes ?: null, $admin_id, $new_rev_count, $line_id]
                );
                if ($upd === false) throw new Exception('Gagal update Revision untuk line ' . $line_id);

                // Insert ke revision log
                sqlsrv_query($koneksi,
                    "INSERT INTO [PRD].[dbo].[SE_FIRE_PROTECTION_REVISION_LOG]
                         (line_id, revision_cycle, revision_notes, revised_by, rejected_repair_photo)
                     VALUES (?, ?, ?, ?, ?)",
                    [$line_id, $new_rev_count, $revision_notes ?: '-', $admin_id, $line['repair_photo']]
                );
                $revision_count_saved++;

            } else {
                // → Verified
                $upd = sqlsrv_query($koneksi,
                    "UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
                     SET repair_status = 'Verified',
                         verified_at   = GETDATE(),
                         verified_by   = ?,
                         updated_at    = GETDATE()
                     WHERE id = ?",
                    [$admin_id, $line_id]
                );
                if ($upd === false) throw new Exception('Gagal update Verified untuk line ' . $line_id);
                $verified_count++;
            }
        }

        // Cek apakah semua NG untuk asset ini sudah Verified
        $check = sqlsrv_query($koneksi,
            "SELECT COUNT(*) AS open_count
             FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES]
             WHERE asset_id = ? AND repair_status NOT IN ('Verified')",
            [$id]
        );
        $check_row = sqlsrv_fetch_array($check, SQLSRV_FETCH_ASSOC);
        if ($check_row['open_count'] == 0) {
            sqlsrv_query($koneksi,
                "UPDATE [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER]
                 SET status = 'OK', updated_at = GETDATE() WHERE id = ?",
                [$id]
            );
        }

        sqlsrv_commit($koneksi);

        $msg = $verified_count . ' item diverifikasi';
        if ($revision_count_saved > 0) $msg .= ', ' . $revision_count_saved . ' item perlu perbaikan ulang (Revisi)';
        echo json_encode(['status' => 'success', 'message' => $msg . '.']);
        exit;
    }

} catch (Exception $e) {
    if ($koneksi) sqlsrv_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>