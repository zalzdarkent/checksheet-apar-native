<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$data_all = [];

// Fetch APAR Markers
$sql_apar = "
    SELECT a.code as kode, a.type as jenis, a.location as lokasi, a.area, a.x_coordinate, a.y_coordinate,
    (SELECT TOP 1 abnormal_case FROM [apar].[dbo].[apar_abnormal_cases] WHERE apar_id = a.id AND status <> 'Verified' ORDER BY created_at DESC) as issue,
    CASE 
        WHEN a.status = 'OK' THEN 'OK'
        WHEN a.status = 'On Inspection' THEN 'Proses'
        ELSE 'Abnormal'
    END as status_badge
    FROM [apar].[dbo].[apars] a
    WHERE a.x_coordinate IS NOT NULL AND a.y_coordinate IS NOT NULL
";

$res_apar = sqlsrv_query($koneksi, $sql_apar);
if ($res_apar !== false) {
    while ($row = sqlsrv_fetch_array($res_apar, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = 'apar';
        $row['issue'] = $row['issue'] ?? '';
        $data_all[] = $row;
    }
}

// Fetch Hydrant Markers
$sql_hydrant = "
    SELECT h.code as kode, h.type as jenis, h.location as lokasi, h.area, h.x_coordinate, h.y_coordinate,
    (SELECT TOP 1 abnormal_case FROM [apar].[dbo].[hydrant_abnormal_cases] WHERE hydrant_id = h.id AND status <> 'Verified' ORDER BY created_at DESC) as issue,
    CASE 
        WHEN h.status = 'Good' THEN 'OK'
        WHEN h.status = 'On Inspection' THEN 'Proses'
        ELSE 'Abnormal'
    END as status_badge
    FROM [apar].[dbo].[hydrants] h
    WHERE h.x_coordinate IS NOT NULL AND h.y_coordinate IS NOT NULL
";

$res_hydrant = sqlsrv_query($koneksi, $sql_hydrant);
if ($res_hydrant !== false) {
    while ($row = sqlsrv_fetch_array($res_hydrant, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = 'hydrant';
        $row['issue'] = $row['issue'] ?? '';
        $data_all[] = $row;
    }
}

echo json_encode($data_all);
?>
