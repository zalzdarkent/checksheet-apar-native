<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$jenis_perangkat = isset($_GET['type']) ? $_GET['type'] : '';
$status_pencarian = isset($_GET['status']) ? $_GET['status'] : '';

if (!$jenis_perangkat || !$status_pencarian) {
    echo json_encode(['error' => 'Parameter tidak lengkap']);
    exit;
}

$data_detail = [];

if ($jenis_perangkat === 'apar' || $jenis_perangkat === 'all') {
    // Queries for APAR
    $q_proses = "SELECT a.code as kode, a.type as jenis, a.location as lokasi, a.area, 'Proses' as status_badge, 
            'Belum inspeksi bulan ini' as keterangan, NULL as foto, a.x_coordinate, a.y_coordinate
            FROM [apar].[dbo].[apars] a
            WHERE (a.expired_date IS NULL OR a.expired_date > GETDATE())
            AND NOT EXISTS (
                SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.apar_unit_id = a.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            )";
            
    $q_ok = "SELECT a.code as kode, a.type as jenis, a.location as lokasi, a.area, 'OK' as status_badge, 
            'Sudah diinspeksi' as keterangan, NULL as foto, a.x_coordinate, a.y_coordinate
            FROM [apar].[dbo].[apars] a
            WHERE a.status = 'OK'
            AND (a.expired_date IS NULL OR a.expired_date > GETDATE())
            AND EXISTS (
                SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.apar_unit_id = a.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            )";
            
    $q_abn = "SELECT a.code as kode, a.type as jenis, a.location as lokasi, a.area, 'Abnormal' as status_badge, 
            COALESCE(aac.abnormal_case, 'Expired / Rusak') as keterangan, aac.repair_photo as foto, a.x_coordinate, a.y_coordinate
            FROM [apar].[dbo].[apars] a
            LEFT JOIN [apar].[dbo].[apar_abnormal_cases] aac ON a.id = aac.apar_id AND aac.status != 'Verified'
            WHERE a.expired_date <= GETDATE() OR (a.status <> 'OK'
            AND EXISTS (
                SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.apar_unit_id = a.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            ))";

    $queries = [];
    if ($status_pencarian === 'Proses') $queries[] = $q_proses;
    elseif ($status_pencarian === 'OK') $queries[] = $q_ok;
    elseif ($status_pencarian === 'Abnormal') $queries[] = $q_abn;
    elseif ($status_pencarian === 'Semua Status') $queries = [$q_proses, $q_ok, $q_abn];

    foreach ($queries as $q) {
        $res = sqlsrv_query($koneksi, $q);
        if ($res !== false) {
            while ($r = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
                $data_detail[] = $r;
            }
        }
    }
}

if ($jenis_perangkat === 'hydrant' || $jenis_perangkat === 'all') {
    // Queries for HYDRANT
    $q_proses = "SELECT h.code as kode, h.type as jenis, h.location as lokasi, h.area, 'Proses' as status_badge, 
            'Belum inspeksi bulan ini' as keterangan, NULL as foto, h.x_coordinate, h.y_coordinate
            FROM [apar].[dbo].[hydrants] h
            WHERE NOT EXISTS (
                SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.hydrant_unit_id = h.id
                AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            )";
            
    $q_ok = "SELECT h.code as kode, h.type as jenis, h.location as lokasi, h.area, 'OK' as status_badge, 
            'Sudah diinspeksi' as keterangan, NULL as foto, h.x_coordinate, h.y_coordinate
            FROM [apar].[dbo].[hydrants] h
            WHERE h.status = 'Good'
            AND EXISTS (
                SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.hydrant_unit_id = h.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            )";
            
    $q_abn = "SELECT h.code as kode, h.type as jenis, h.location as lokasi, h.area, 'Abnormal' as status_badge, 
            COALESCE(hac.abnormal_case, 'Rusak / Temuan') as keterangan, hac.repair_photo as foto, h.x_coordinate, h.y_coordinate
            FROM [apar].[dbo].[hydrants] h
            LEFT JOIN [apar].[dbo].[hydrant_abnormal_cases] hac ON h.id = hac.hydrant_id AND hac.status != 'Verified'
            WHERE h.status <> 'Good'
            AND EXISTS (
                SELECT 1 FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.hydrant_id = h.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            )";

    $queries = [];
    if ($status_pencarian === 'Proses') $queries[] = $q_proses;
    elseif ($status_pencarian === 'OK') $queries[] = $q_ok;
    elseif ($status_pencarian === 'Abnormal') $queries[] = $q_abn;
    elseif ($status_pencarian === 'Semua Status') $queries = [$q_proses, $q_ok, $q_abn];

    foreach ($queries as $q) {
        $res = sqlsrv_query($koneksi, $q);
        if ($res !== false) {
            while ($r = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
                $data_detail[] = $r;
            }
        }
    }
}

echo json_encode($data_detail);
?>
