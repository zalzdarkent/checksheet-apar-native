<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$data_all = [];

// Fetch Unified Markers (APAR & Hydrant)
$sql = "
    SELECT 
        m.id,
        m.asset_code as kode, 
        m.model_type as jenis, 
        m.location as lokasi, 
        m.area, 
        m.x_coordinate, 
        m.y_coordinate,
        LOWER(m.asset_type) as device_type,
        (SELECT TOP 1 finding_desc 
         FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] 
         WHERE asset_id = m.id AND repair_status <> 'Verified' 
         ORDER BY created_at DESC) as issue,
        CASE 
            WHEN m.status = 'OK' THEN 'OK'
            WHEN m.status = 'On Progress' THEN 'Proses'
            ELSE 'Abnormal'
        END as status_badge
    FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
    WHERE m.x_coordinate IS NOT NULL AND m.y_coordinate IS NOT NULL AND m.is_active = 1
";

$res = sqlsrv_query($koneksi, $sql);
if ($res !== false) {
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $row['issue'] = $row['issue'] ?? '';
        $data_all[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data_all);
?>
