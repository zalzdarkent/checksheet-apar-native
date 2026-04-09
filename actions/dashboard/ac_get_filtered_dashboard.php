<?php
include("../../config/db_koneksi.php");

$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';

$response = [
    'apar' => ['total' => 0, 'ok' => 0, 'proses' => 0, 'abnormal' => 0],
    'hydrant' => ['total' => 0, 'ok' => 0, 'proses' => 0, 'abnormal' => 0],
    'markers' => []
];

try {
    $hasDateFilter = !empty($startDate) && !empty($endDate);
    
    // 1. Get Totals from MASTER
    $sql_totals = "SELECT asset_type, COUNT(*) as total FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE is_active = 1 GROUP BY asset_type";
    $res_totals = sqlsrv_query($koneksi, $sql_totals);
    while ($row = sqlsrv_fetch_array($res_totals, SQLSRV_FETCH_ASSOC)) {
        $type = strtolower($row['asset_type']);
        if (isset($response[$type])) $response[$type]['total'] = (int)$row['total'];
    }

    // Inspection check items
    $apar_items = ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'];
    $apar_ok_check = "(" . implode(" = 1 AND ", $apar_items) . " = 1)";
    
    $hydrant_items = ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
    $hydrant_ok_check = "(" . implode(" = 1 AND ", $hydrant_items) . " = 1)";

    // 2. Determine Stats based on Period
    if ($hasDateFilter) {
        // Stats in date range:
        // OK = Assets with at least one transition in range that is ALL OK
        // Abnormal = Assets with at least one transition in range that HAS NG, or an active abnormality in LINES
        $sql_stats = "SELECT m.id, m.asset_type,
                        CASE 
                            WHEN EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND t.inspection_date >= ? AND t.inspection_date <= ? AND " . ($at == 'APAR' ? $apar_ok_check : $hydrant_ok_check) . ") THEN 'OK'
                            WHEN EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND t.inspection_date >= ? AND t.inspection_date <= ? AND NOT " . ($at == 'APAR' ? $apar_ok_check : $hydrant_ok_check) . ") THEN 'Abnormal'
                            WHEN EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l WHERE l.asset_id = m.id AND l.created_at <= ? AND (l.verified_at IS NULL OR l.verified_at >= ?)) THEN 'Abnormal'
                            ELSE 'Proses'
                        END as status_period
                      FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m WHERE m.is_active = 1";
        
        // This is complex for a single query. Let's simplify and use 3 passes for the stats.
        
        // pass: Abnormal in range
        $sql_abn = "SELECT m.asset_type, COUNT(DISTINCT m.id) as count
                    FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
                    WHERE m.is_active = 1 AND (
                        EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND CAST(t.inspection_date AS DATE) BETWEEN ? AND ? AND (
                            (m.asset_type = 'APAR' AND NOT $apar_ok_check) OR (m.asset_type = 'HYDRANT' AND NOT $hydrant_ok_check)
                        )) OR
                        EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l WHERE l.asset_id = m.id AND CAST(l.created_at AS DATE) <= ? AND (l.verified_at IS NULL OR CAST(l.verified_at AS DATE) >= ?))
                    ) GROUP BY m.asset_type";
        $res_abn = sqlsrv_query($koneksi, $sql_abn, [$startDate, $endDate, $endDate, $startDate]);
        while ($row = sqlsrv_fetch_array($res_abn, SQLSRV_FETCH_ASSOC)) {
            $response[strtolower($row['asset_type'])]['abnormal'] = (int)$row['count'];
        }

        // pass: OK in range (excluding ones that were abnormal)
        $sql_ok = "SELECT m.asset_type, COUNT(DISTINCT m.id) as count
                   FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
                   WHERE m.is_active = 1 AND 
                         EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND CAST(t.inspection_date AS DATE) BETWEEN ? AND ? AND (
                            (m.asset_type = 'APAR' AND $apar_ok_check) OR (m.asset_type = 'HYDRANT' AND $hydrant_ok_check)
                         )) 
                         AND NOT (
                            EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND CAST(t.inspection_date AS DATE) BETWEEN ? AND ? AND (
                                (m.asset_type = 'APAR' AND NOT $apar_ok_check) OR (m.asset_type = 'HYDRANT' AND NOT $hydrant_ok_check)
                            )) OR
                            EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l WHERE l.asset_id = m.id AND CAST(l.created_at AS DATE) <= ? AND (l.verified_at IS NULL OR CAST(l.verified_at AS DATE) >= ?))
                         ) GROUP BY m.asset_type";
        $res_ok = sqlsrv_query($koneksi, $sql_ok, [$startDate, $endDate, $startDate, $endDate, $endDate, $startDate]);
        while ($row = sqlsrv_fetch_array($res_ok, SQLSRV_FETCH_ASSOC)) {
            $response[strtolower($row['asset_type'])]['ok'] = (int)$row['count'];
        }

        foreach (['apar', 'hydrant'] as $t) {
            $response[$t]['proses'] = $response[$t]['total'] - $response[$t]['ok'] - $response[$t]['abnormal'];
        }

        // Markers: status in range
        $sqlMarkers = "SELECT 
                m.id, m.asset_code as code, m.asset_type as jenis, x_coordinate, y_coordinate, m.area,
                CASE 
                    WHEN EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND CAST(t.inspection_date AS DATE) BETWEEN ? AND ? AND (
                        (m.asset_type = 'APAR' AND NOT $apar_ok_check) OR (m.asset_type = 'HYDRANT' AND NOT $hydrant_ok_check)
                    )) OR EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l WHERE l.asset_id = m.id AND CAST(l.created_at AS DATE) <= ? AND (l.verified_at IS NULL OR CAST(l.verified_at AS DATE) >= ?)) THEN 'Abnormal'
                    WHEN EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id = m.id AND CAST(t.inspection_date AS DATE) BETWEEN ? AND ? AND (
                        (m.asset_type = 'APAR' AND $apar_ok_check) OR (m.asset_type = 'HYDRANT' AND $hydrant_ok_check)
                    )) THEN 'OK'
                    ELSE 'Proses'
                END as status_badge
            FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
            WHERE m.is_active = 1 AND x_coordinate IS NOT NULL AND y_coordinate IS NOT NULL";
        $params_markers = [$startDate, $endDate, $endDate, $startDate, $startDate, $endDate];

    } else {
        // Current Status (No Filter)
        foreach (['apar', 'hydrant'] as $t) {
            $ut = strtoupper($t);
            $response[$t]['ok'] = (int)sqlsrv_fetch_array(sqlsrv_query($koneksi, "SELECT COUNT(*) FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE asset_type='$ut' AND status='OK' AND is_active=1"))[0];
            $response[$t]['abnormal'] = (int)sqlsrv_fetch_array(sqlsrv_query($koneksi, "SELECT COUNT(*) FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE asset_type='$ut' AND status='NG' AND is_active=1"))[0];
            
            $now_m = date('m'); $now_y = date('Y');
            $response[$t]['proses'] = (int)sqlsrv_fetch_array(sqlsrv_query($koneksi, "SELECT COUNT(*) FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m WHERE asset_type='$ut' AND is_active=1 AND NOT EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id=m.id AND MONTH(t.inspection_date)=$now_m AND YEAR(t.inspection_date)=$now_y)"))[0];
        }

        $sqlMarkers = "SELECT id, asset_code as code, asset_type as jenis, x_coordinate, y_coordinate, area,
                        CASE 
                            WHEN status = 'NG' THEN 'Abnormal'
                            WHEN EXISTS (SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t WHERE t.asset_id=m.id AND MONTH(t.inspection_date)=MONTH(GETDATE()) AND YEAR(t.inspection_date)=YEAR(GETDATE())) THEN 'OK'
                            ELSE 'Proses'
                        END as status_badge
                       FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
                       WHERE is_active = 1 AND x_coordinate IS NOT NULL AND y_coordinate IS NOT NULL";
        $params_markers = [];
    }

    $resMarkers = sqlsrv_query($koneksi, $sqlMarkers, $params_markers);
    if ($resMarkers) {
        while ($row = sqlsrv_fetch_array($resMarkers, SQLSRV_FETCH_ASSOC)) {
            $response['markers'][] = [
                'kode' => $row['code'],
                'status_badge' => $row['status_badge'],
                'jenis' => $row['jenis'],
                'area' => $row['area'] ?? '-',
                'device_type' => strtolower($row['jenis']),
                'x_coordinate' => $row['x_coordinate'],
                'y_coordinate' => $row['y_coordinate']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
