<?php
include("config/db_koneksi.php");

echo "=== DIRECT QUERY DEBUG ===\n\n";

// Test exact query dari backend
$sqlApar = "SELECT 
    COUNT(a.id) as total,
    SUM(CASE WHEN a.status = 'OK' THEN 1 ELSE 0 END) as ok,
    SUM(CASE WHEN a.status = 'On Inspection' THEN 1 ELSE 0 END) as proses,
    SUM(CASE WHEN a.status = 'Abnormal' OR a.status = 'Expired' THEN 1 ELSE 0 END) as abnormal
FROM [apar].[dbo].[apars] a";

echo "Query APAR:\n$sqlApar\n\n";

$resApar = sqlsrv_query($koneksi, $sqlApar);

if ($resApar === false) {
    echo "ERROR APAR - sqlsrv_errors():\n";
    print_r(sqlsrv_errors());
} else {
    $row = sqlsrv_fetch_array($resApar, SQLSRV_FETCH_ASSOC);
    echo "Result APAR:\n";
    print_r($row);
}

echo "\n---\n\n";

$sqlHydrant = "SELECT 
    COUNT(h.id) as total,
    SUM(CASE WHEN h.status = 'Good' THEN 1 ELSE 0 END) as ok,
    SUM(CASE WHEN h.status = 'On Inspection' THEN 1 ELSE 0 END) as proses,
    SUM(CASE WHEN h.status = 'Abnormal' OR h.status = 'Expired' THEN 1 ELSE 0 END) as abnormal
FROM [apar].[dbo].[hydrants] h";

echo "Query HYDRANT:\n$sqlHydrant\n\n";

$resHydrant = sqlsrv_query($koneksi, $sqlHydrant);

if ($resHydrant === false) {
    echo "ERROR HYDRANT - sqlsrv_errors():\n";
    print_r(sqlsrv_errors());
} else {
    $row = sqlsrv_fetch_array($resHydrant, SQLSRV_FETCH_ASSOC);
    echo "Result HYDRANT:\n";
    print_r($row);
}

?>
