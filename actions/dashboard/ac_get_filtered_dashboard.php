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
    // Determine if filtering by date
    $hasDateFilter = !empty($startDate) || !empty($endDate);
    
    if ($hasDateFilter) {
        // ===== WITH DATE FILTER: Only get devices with abnormal cases in date range =====
        
        // APAR STATS with date filter
        $sqlApar = "SELECT 
            COUNT(DISTINCT a.id) as total,
            SUM(CASE WHEN a.status = 'OK' THEN 1 ELSE 0 END) as ok,
            SUM(CASE WHEN a.status = 'On Inspection' THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN a.status = 'Abnormal' OR a.status = 'Expired' THEN 1 ELSE 0 END) as abnormal
        FROM [apar].[dbo].[apars] a
        INNER JOIN [apar].[dbo].[abnormal_cases] ac ON a.id = ac.apar_id
        WHERE CAST(ac.created_at AS DATE) >= CAST('" . $startDate . "' AS DATE)
        AND CAST(ac.created_at AS DATE) <= CAST('" . $endDate . "' AS DATE)";
        
        $resApar = sqlsrv_query($koneksi, $sqlApar);
        if ($resApar && $row = sqlsrv_fetch_array($resApar, SQLSRV_FETCH_ASSOC)) {
            $response['apar'] = [
                'total' => (int)($row['total'] ?? 0),
                'ok' => (int)($row['ok'] ?? 0),
                'proses' => (int)($row['proses'] ?? 0),
                'abnormal' => (int)($row['abnormal'] ?? 0)
            ];
        }

        // HYDRANT STATS with date filter
        $sqlHydrant = "SELECT 
            COUNT(DISTINCT h.id) as total,
            SUM(CASE WHEN h.status = 'Good' THEN 1 ELSE 0 END) as ok,
            SUM(CASE WHEN h.status = 'On Inspection' THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN h.status = 'Abnormal' OR h.status = 'Expired' THEN 1 ELSE 0 END) as abnormal
        FROM [apar].[dbo].[hydrants] h
        INNER JOIN [apar].[dbo].[abnormal_cases] ac ON h.id = ac.hydrant_id
        WHERE CAST(ac.created_at AS DATE) >= CAST('" . $startDate . "' AS DATE)
        AND CAST(ac.created_at AS DATE) <= CAST('" . $endDate . "' AS DATE)";
        
        $resHydrant = sqlsrv_query($koneksi, $sqlHydrant);
        if ($resHydrant && $row = sqlsrv_fetch_array($resHydrant, SQLSRV_FETCH_ASSOC)) {
            $response['hydrant'] = [
                'total' => (int)($row['total'] ?? 0),
                'ok' => (int)($row['ok'] ?? 0),
                'proses' => (int)($row['proses'] ?? 0),
                'abnormal' => (int)($row['abnormal'] ?? 0)
            ];
        }

        // MARKERS with date filter
        $sqlMarkers = "SELECT DISTINCT
            a.id, a.code, a.status, 'APAR' as jenis, 'apar' as device_type,
            a.x_coordinate, a.y_coordinate,
            CASE WHEN a.status = 'OK' THEN 'OK'
                 WHEN a.status = 'On Inspection' THEN 'Proses'
                 ELSE 'Abnormal' END as status_badge
        FROM [apar].[dbo].[apars] a
        INNER JOIN [apar].[dbo].[abnormal_cases] ac ON a.id = ac.apar_id
        WHERE a.x_coordinate IS NOT NULL AND a.y_coordinate IS NOT NULL
        AND CAST(ac.created_at AS DATE) >= CAST('" . $startDate . "' AS DATE)
        AND CAST(ac.created_at AS DATE) <= CAST('" . $endDate . "' AS DATE)
        
        UNION ALL
        
        SELECT DISTINCT
            h.id, h.code, h.status, 'Hydrant' as jenis, 'hydrant' as device_type,
            h.x_coordinate, h.y_coordinate,
            CASE WHEN h.status = 'Good' THEN 'OK'
                 WHEN h.status = 'On Inspection' THEN 'Proses'
                 ELSE 'Abnormal' END as status_badge
        FROM [apar].[dbo].[hydrants] h
        INNER JOIN [apar].[dbo].[abnormal_cases] ac ON h.id = ac.hydrant_id
        WHERE h.x_coordinate IS NOT NULL AND h.y_coordinate IS NOT NULL
        AND CAST(ac.created_at AS DATE) >= CAST('" . $startDate . "' AS DATE)
        AND CAST(ac.created_at AS DATE) <= CAST('" . $endDate . "' AS DATE)";
        
    } else {
        // ===== NO DATE FILTER: Get ALL devices with their current status =====
        
        // APAR STATS (all)
        $sqlApar = "SELECT 
            COUNT(a.id) as total,
            SUM(CASE WHEN a.status = 'OK' THEN 1 ELSE 0 END) as ok,
            SUM(CASE WHEN a.status = 'On Inspection' THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN a.status = 'Abnormal' OR a.status = 'Expired' THEN 1 ELSE 0 END) as abnormal
        FROM [apar].[dbo].[apars] a";
        
        $resApar = sqlsrv_query($koneksi, $sqlApar);
        if ($resApar) {
            $row = sqlsrv_fetch_array($resApar, SQLSRV_FETCH_ASSOC);
            if ($row) {
                $response['apar'] = [
                    'total' => (int)$row['total'],
                    'ok' => (int)$row['ok'],
                    'proses' => (int)$row['proses'],
                    'abnormal' => (int)$row['abnormal']
                ];
            }
        } else {
            $response['error_apar'] = sqlsrv_errors();
        }

        // HYDRANT STATS (all)
        $sqlHydrant = "SELECT 
            COUNT(h.id) as total,
            SUM(CASE WHEN h.status = 'Good' THEN 1 ELSE 0 END) as ok,
            SUM(CASE WHEN h.status = 'On Inspection' THEN 1 ELSE 0 END) as proses,
            SUM(CASE WHEN h.status = 'Abnormal' OR h.status = 'Expired' THEN 1 ELSE 0 END) as abnormal
        FROM [apar].[dbo].[hydrants] h";
        
        $resHydrant = sqlsrv_query($koneksi, $sqlHydrant);
        if ($resHydrant) {
            $row = sqlsrv_fetch_array($resHydrant, SQLSRV_FETCH_ASSOC);
            if ($row) {
                $response['hydrant'] = [
                    'total' => (int)$row['total'],
                    'ok' => (int)$row['ok'],
                    'proses' => (int)$row['proses'],
                    'abnormal' => (int)$row['abnormal']
                ];
            }
        } else {
            $response['error_hydrant'] = sqlsrv_errors();
        }

        // MARKERS (all)
        $sqlMarkers = "SELECT 
            a.id, a.code, a.status, 'APAR' as jenis, 'apar' as device_type,
            a.x_coordinate, a.y_coordinate,
            CASE WHEN a.status = 'OK' THEN 'OK'
                 WHEN a.status = 'On Inspection' THEN 'Proses'
                 ELSE 'Abnormal' END as status_badge
        FROM [apar].[dbo].[apars] a
        WHERE a.x_coordinate IS NOT NULL AND a.y_coordinate IS NOT NULL
        
        UNION ALL
        
        SELECT 
            h.id, h.code, h.status, 'Hydrant' as jenis, 'hydrant' as device_type,
            h.x_coordinate, h.y_coordinate,
            CASE WHEN h.status = 'Good' THEN 'OK'
                 WHEN h.status = 'On Inspection' THEN 'Proses'
                 ELSE 'Abnormal' END as status_badge
        FROM [apar].[dbo].[hydrants] h
        WHERE h.x_coordinate IS NOT NULL AND h.y_coordinate IS NOT NULL";
    }

    // Execute marker query
    $resMarkers = sqlsrv_query($koneksi, $sqlMarkers);
    if ($resMarkers) {
        while ($row = sqlsrv_fetch_array($resMarkers, SQLSRV_FETCH_ASSOC)) {
            $response['markers'][] = [
                'kode' => $row['code'],
                'status_badge' => $row['status_badge'],
                'jenis' => $row['jenis'],
                'device_type' => $row['device_type'],
                'x_coordinate' => $row['x_coordinate'],
                'y_coordinate' => $row['y_coordinate']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
