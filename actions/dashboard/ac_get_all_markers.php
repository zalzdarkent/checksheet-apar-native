<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$data_all = [];

// Fetch APAR Markers
$sql_apar = "
    SELECT a.code as kode, a.type as jenis, a.location as lokasi, a.area, a.x_coordinate, a.y_coordinate,
    CASE 
        WHEN a.expired_date <= GETDATE() OR (a.status <> 'OK' AND EXISTS (
            SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi 
            WHERE bi.apar_unit_id = a.id AND MONTH(bi.inspection_date) = MONTH(GETDATE()) AND YEAR(bi.inspection_date) = YEAR(GETDATE())
        )) THEN 'Abnormal'
        WHEN EXISTS (
            SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi 
            WHERE bi.apar_unit_id = a.id AND MONTH(bi.inspection_date) = MONTH(GETDATE()) AND YEAR(bi.inspection_date) = YEAR(GETDATE())
        ) AND a.status = 'OK' THEN 'OK'
        ELSE 'Proses'
    END as status_badge
    FROM [apar].[dbo].[apars] a
    WHERE a.x_coordinate IS NOT NULL AND a.y_coordinate IS NOT NULL
";

$res_apar = sqlsrv_query($koneksi, $sql_apar);
if ($res_apar !== false) {
    while ($row = sqlsrv_fetch_array($res_apar, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = 'apar';
        $data_all[] = $row;
    }
}

// Fetch Hydrant Markers
$sql_hydrant = "
    SELECT h.code as kode, h.type as jenis, h.location as lokasi, h.area, h.x_coordinate, h.y_coordinate,
    CASE 
        WHEN h.status <> 'Good' AND EXISTS (
            SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi 
            WHERE bi.hydrant_unit_id = h.id AND MONTH(bi.inspection_date) = MONTH(GETDATE()) AND YEAR(bi.inspection_date) = YEAR(GETDATE())
        ) THEN 'Abnormal'
        WHEN EXISTS (
            SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi 
            WHERE bi.hydrant_unit_id = h.id AND MONTH(bi.inspection_date) = MONTH(GETDATE()) AND YEAR(bi.inspection_date) = YEAR(GETDATE())
        ) AND h.status = 'Good' THEN 'OK'
        ELSE 'Proses'
    END as status_badge
    FROM [apar].[dbo].[hydrants] h
    WHERE h.x_coordinate IS NOT NULL AND h.y_coordinate IS NOT NULL
";

$res_hydrant = sqlsrv_query($koneksi, $sql_hydrant);
if ($res_hydrant !== false) {
    while ($row = sqlsrv_fetch_array($res_hydrant, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = 'hydrant';
        $data_all[] = $row;
    }
}

echo json_encode($data_all);
?>
