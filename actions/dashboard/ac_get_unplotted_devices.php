<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$unplotted = [];

// Get unplotted APARs
$sql_apar = "SELECT code, type as jenis, location as lokasi, area 
             FROM [apar].[dbo].[apars] 
             WHERE x_coordinate IS NULL OR y_coordinate IS NULL";
$res_apar = sqlsrv_query($koneksi, $sql_apar);
if ($res_apar !== false) {
    while ($row = sqlsrv_fetch_array($res_apar, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = 'apar';
        $unplotted[] = $row;    
    }
}

// Get unplotted Hydrants
$sql_hydrant = "SELECT code, type as jenis, location as lokasi, area 
                FROM [apar].[dbo].[hydrants] 
                WHERE x_coordinate IS NULL OR y_coordinate IS NULL";
$res_hydrant = sqlsrv_query($koneksi, $sql_hydrant);
if ($res_hydrant !== false) {
    while ($row = sqlsrv_fetch_array($res_hydrant, SQLSRV_FETCH_ASSOC)) {
        $row['device_type'] = 'hydrant';
        $unplotted[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($unplotted);
?>
