<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$unplotted = [];

// Unified query from MASTER for both types
$sql = "SELECT asset_code as code, model_type as jenis, location as lokasi, area, asset_type as device_type
        FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] 
        WHERE (x_coordinate IS NULL OR y_coordinate IS NULL OR x_coordinate = 0 OR y_coordinate = 0)
        AND is_active = 1";

$res = sqlsrv_query($koneksi, $sql);
if ($res !== false) {
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = strtolower($row['device_type']);
        $unplotted[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($unplotted);
?>