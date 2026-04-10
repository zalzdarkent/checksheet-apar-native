<?php
include(__DIR__ . '/../config/db_koneksi.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $apar = null;
} else {
    // 1. Get APAR Info from unified MASTER table
    $sql_apar = "SELECT 
                    id, 
                    asset_code as code, 
                    asset_type,
                    area, 
                    location, 
                    status, 
                    model_type as type, 
                    weight, 
                    expired_date, 
                    last_inspection_date,
                    is_active,
                    pic_empid
                 FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] 
                 WHERE id = ? AND asset_type = 'APAR'";
    $stmt_apar = sqlsrv_query($koneksi, $sql_apar, [$id]);
    $apar = sqlsrv_fetch_array($stmt_apar, SQLSRV_FETCH_ASSOC);

    if ($apar) {
        $apar['id'] = (int) $apar['id'];

        // Format dates
        if ($apar['expired_date'] instanceof DateTime) {
            $apar['expired_date_fmt'] = $apar['expired_date']->format('d M Y');
        } else {
            $apar['expired_date_fmt'] = '-';
        }

        if ($apar['last_inspection_date'] instanceof DateTime) {
            $apar['last_inspection_fmt'] = $apar['last_inspection_date']->format('d M Y');
        } else {
            $apar['last_inspection_fmt'] = '-';
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
            'exp_date' => 'Expired Date',
            'pressure' => 'Pressure',
            'weight_co2' => 'Weight CO2',
            'tube' => 'Tube',
            'hose' => 'Hose',
            'bracket' => 'Bracket',
            'wi' => 'WI',
            'form_kejadian' => 'Form Kejadian',
            'sign_box' => 'Sign Kotak',
            'sign_triangle' => 'Sign Segitiga',
            'marking_tiger' => 'Marking Tiger',
            'marking_beam' => 'Marking Tiang',
            'sr_apar' => 'SR Apar',
            'kocok_apar' => 'Kocok Apar',
            'label' => 'Label'
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

        $apar['history'] = $history;
        $apar['cases'] = $cases;

        // Expiration Logic
        if ($apar['expired_date'] instanceof DateTime) {
            $today = new DateTime('today');
            $expDate = clone $apar['expired_date'];
            $expDate->setTime(0, 0, 0);
            if ($expDate <= $today) {
                $apar['status'] = 'Expired';
                $apar['is_expired'] = true;
            } else {
                $apar['is_expired'] = false;
            }
        } else {
            $apar['is_expired'] = false;
        }
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($apar);
    exit;
}
?>