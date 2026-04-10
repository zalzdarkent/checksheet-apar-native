<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $hydrant = null;
} else {
    // 1. Get Hydrant Info from unified MASTER table
    $sql_hydrant = "SELECT 
                        id, 
                        asset_code as code, 
                        asset_type,
                        area, 
                        location, 
                        status, 
                        model_type as type, 
                        last_inspection_date,
                        is_active,
                        pic_empid
                     FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] 
                     WHERE id = ? AND asset_type = 'Hydrant'";
    $stmt_hydrant = sqlsrv_query($koneksi, $sql_hydrant, [$id]);
    $hydrant = sqlsrv_fetch_array($stmt_hydrant, SQLSRV_FETCH_ASSOC);

    if ($hydrant) {
        $hydrant['id'] = (int) $hydrant['id'];

        // Format dates
        if ($hydrant['last_inspection_date'] instanceof DateTime) {
            $hydrant['last_inspection_fmt'] = $hydrant['last_inspection_date']->format('d M Y');
        } else {
            $hydrant['last_inspection_fmt'] = '-';
        }

        // 2. Get Inspection History from unified TRANS table
        $sql_history = "SELECT h.*, ISNULL(e.EmployeeName, u.REALNAME) as inspector_name 
                        FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] h
                        LEFT JOIN [ATI].[Users].[UserTable] u ON h.user_id = u.EMPID
                        LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e ON h.user_id = e.EmpID
                        WHERE h.asset_id = ? 
                        ORDER BY h.inspection_date DESC";
        $stmt_history = sqlsrv_query($koneksi, $sql_history, [$id]);
        $history = [];

        $items = [
            'body_hydrant' => 'Body Hydrant',
            'selang' => 'Selang',
            'couple_join' => 'Couple Join',
            'nozzle' => 'Nozzle',
            'check_sheet' => 'Check Sheet',
            'valve_kran' => 'Valve/Kran',
            'lampu' => 'Lampu',
            'cover_lampu' => 'Cover Lampu',
            'box_display' => 'Box Display',
            'konsul_hydrant' => 'Konsul Hydrant',
            'jr' => 'Kunci Rachet (JR)',
            'marking' => 'Marking'
        ];

        if ($stmt_history !== false) {
            while ($row = sqlsrv_fetch_array($stmt_history, SQLSRV_FETCH_ASSOC)) {
                if ($row['inspection_date'] instanceof DateTime) {
                    $row['inspection_date_fmt'] = $row['inspection_date']->format('d M Y H:i');
                } else {
                    $row['inspection_date_fmt'] = '-';
                }

                $is_ng = false;
                $ng_details = [];
                $ng_items_list = [];
                foreach ($items as $item_key => $item_label) {
                    $ok_val = isset($row[$item_key . '_ok']) ? (int) $row[$item_key . '_ok'] : 1;
                    if ($ok_val === 0) {
                        $is_ng = true;
                        $ng_details[] = $item_label;
                    }
                    $ng_items_list[] = [
                        'key' => $item_key,
                        'label' => $item_label,
                        'ok' => $ok_val,
                        'photo' => $row[$item_key . '_foto'] ?? null
                    ];
                }
                $row['insp_status'] = $is_ng ? 'NG' : 'OK';
                $row['ng_text'] = implode(', ', $ng_details);
                $row['full_items'] = $ng_items_list;
                $history[] = $row;
            }
        }

        // 3. Get Abnormal History from LINES — dikelompokkan per trans_id (per sesi inspeksi)
        //    Satu inspeksi dengan >1 item NG ditampilkan sebagai 1 baris,
        //    issue digabung dengan STRING_AGG, status terburuk dipakai sebagai representatif.
        $sql_cases = "
            WITH grouped AS (
                SELECT
                    l.trans_id,
                    l.asset_id,
                    STRING_AGG(l.finding_desc, ' | ')
                        WITHIN GROUP (ORDER BY l.id)        AS abnormal_case,
                    STRING_AGG(l.repair_photo, ',')
                        WITHIN GROUP (ORDER BY l.id)        AS repair_photos,
                    CASE
                        WHEN SUM(CASE WHEN l.repair_status = 'Open'        THEN 1 ELSE 0 END) > 0 THEN 'Open'
                        WHEN SUM(CASE WHEN l.repair_status = 'On Progress' THEN 1 ELSE 0 END) > 0 THEN 'On Progress'
                        WHEN SUM(CASE WHEN l.repair_status = 'Closed'      THEN 1 ELSE 0 END) > 0 THEN 'Closed'
                        ELSE 'Verified'
                    END                                     AS repair_status,
                    MIN(l.countermeasure)                   AS countermeasure,
                    MIN(l.due_date)                         AS due_date,
                    MIN(l.pic_empid)                        AS pic_empid,
                    MIN(l.verified_by)                      AS verified_by,
                    MIN(l.verified_at)                      AS verified_at,
                    MAX(l.created_at)                       AS created_at,
                    COUNT(l.id)                             AS ng_count
                FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES] l
                WHERE l.asset_id = ?
                GROUP BY l.trans_id, l.asset_id
            )
            SELECT
                g.*,
                ISNULL(e_ver.EmployeeName, u_ver.REALNAME) AS verified_by_name,
                ISNULL(e_pic.EmployeeName, u_pic.REALNAME) AS pic_name
            FROM grouped g
            LEFT JOIN [ATI].[Users].[UserTable] u_ver   ON g.verified_by = u_ver.EMPID
            LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e_ver ON g.verified_by = e_ver.EmpID
            LEFT JOIN [ATI].[Users].[UserTable] u_pic   ON g.pic_empid = u_pic.EMPID
            LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e_pic ON g.pic_empid = e_pic.EmpID
            ORDER BY g.created_at DESC";

        $stmt_cases = sqlsrv_query($koneksi, $sql_cases, [$id]);
        $cases = [];
        if ($stmt_cases !== false) {
            while ($row = sqlsrv_fetch_array($stmt_cases, SQLSRV_FETCH_ASSOC)) {
                $row['status'] = $row['repair_status'];

                if ($row['created_at'] instanceof DateTime) {
                    $row['created_at_fmt'] = $row['created_at']->format('d M Y');
                } else {
                    $row['created_at_fmt'] = '-';
                }

                if (isset($row['due_date']) && $row['due_date'] instanceof DateTime) {
                    $row['due_date_fmt'] = $row['due_date']->format('d M Y');
                } else {
                    $row['due_date_fmt'] = '-';
                }

                // Foto: ambil foto pertama yang ada dari repair_photos
                $photos = array_filter(explode(',', $row['repair_photos'] ?? ''));
                $row['repair_photo'] = !empty($photos) ? trim($photos[0]) : null;

                $cases[] = $row;
            }
        }

        $hydrant['history'] = $history;
        $hydrant['cases'] = $cases;
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($hydrant);
    exit;
}
?>