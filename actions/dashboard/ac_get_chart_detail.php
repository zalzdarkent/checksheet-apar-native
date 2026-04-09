<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$type_req = isset($_GET['type']) ? $_GET['type'] : 'all'; // 'apar', 'hydrant', 'all'
$status_req = isset($_GET['status']) ? $_GET['status'] : '';

if (!$status_req) {
    echo json_encode(['error' => 'Parameter status tidak lengkap']);
    exit;
}

$data_detail = [];
$now_m = date('m');
$now_y = date('Y');

$asset_types = ($type_req === 'all') ? ['APAR', 'HYDRANT'] : [strtoupper($type_req)];

foreach ($asset_types as $at) {
    $q = "";
    if ($status_req === 'Proses') {
        $q = "SELECT m.asset_code as kode, m.model_type as jenis, m.location as lokasi, m.area, 'Proses' as status_badge, 
                     'Belum inspeksi bulan ini' as keterangan, NULL as foto, m.x_coordinate, m.y_coordinate
              FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
              WHERE m.asset_type = '$at' AND m.is_active = 1
              AND NOT EXISTS (
                  SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t
                  WHERE t.asset_id = m.id AND MONTH(t.inspection_date) = $now_m AND YEAR(t.inspection_date) = $now_y
              )";
    } elseif ($status_req === 'OK') {
        $q = "SELECT m.asset_code as kode, m.model_type as jenis, m.location as lokasi, m.area, 'OK' as status_badge, 
                     'Sudah diinspeksi' as keterangan, NULL as foto, m.x_coordinate, m.y_coordinate
              FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
              WHERE m.asset_type = '$at' AND m.is_active = 1 AND m.status = 'OK'
              AND EXISTS (
                  SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t
                  WHERE t.asset_id = m.id AND MONTH(t.inspection_date) = $now_m AND YEAR(t.inspection_date) = $now_y
              )";
    } elseif ($status_req === 'Abnormal') {
        $q = "SELECT m.asset_code as kode, m.model_type as jenis, m.location as lokasi, m.area, 'Abnormal' as status_badge, 
                     ISNULL(l.finding_desc, 'NG / Expired') as keterangan, l.photo_repair as foto, m.x_coordinate, m.y_coordinate
              FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
              LEFT JOIN [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l ON m.id = l.asset_id AND l.repair_status != 'Verified'
              WHERE m.asset_type = '$at' AND m.is_active = 1 AND m.status = 'NG'";
    }

    if ($q) {
        $res = sqlsrv_query($koneksi, $q);
        if ($res !== false) {
            while ($r = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) $data_detail[] = $r;
        }
    }
}

echo json_encode($data_detail);
?>
