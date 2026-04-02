<?php
include("config/db_koneksi.php");

echo "=== TEST COUNT OK STATUS ===\n\n";

// Test APAR OK count
$sqlAparOK = "SELECT COUNT(*) as total_ok FROM [apar].[dbo].[apars] WHERE status = 'OK'";
$resAparOK = sqlsrv_query($koneksi, $sqlAparOK);
if ($resAparOK) {
    $row = sqlsrv_fetch_array($resAparOK, SQLSRV_FETCH_ASSOC);
    echo "APAR dengan status = 'OK': " . $row['total_ok'] . "\n";
}

// Test HYDRANT Good count
$sqlHydrantGood = "SELECT COUNT(*) as total_good FROM [apar].[dbo].[hydrants] WHERE status = 'Good'";
$resHydrantGood = sqlsrv_query($koneksi, $sqlHydrantGood);
if ($resHydrantGood) {
    $row = sqlsrv_fetch_array($resHydrantGood, SQLSRV_FETCH_ASSOC);
    echo "HYDRANT dengan status = 'Good': " . $row['total_good'] . "\n";
}

echo "\n=== BREAKDOWN STATUS APAR ===\n";
$sqlAparStatus = "SELECT status, COUNT(*) as count FROM [apar].[dbo].[apars] GROUP BY status ORDER BY count DESC";
$resAparStatus = sqlsrv_query($koneksi, $sqlAparStatus);
if ($resAparStatus) {
    while ($row = sqlsrv_fetch_array($resAparStatus, SQLSRV_FETCH_ASSOC)) {
        echo "Status: " . $row['status'] . " => Count: " . $row['count'] . "\n";
    }
}

echo "\n=== BREAKDOWN STATUS HYDRANT ===\n";
$sqlHydrantStatus = "SELECT status, COUNT(*) as count FROM [apar].[dbo].[hydrants] GROUP BY status ORDER BY count DESC";
$resHydrantStatus = sqlsrv_query($koneksi, $sqlHydrantStatus);
if ($resHydrantStatus) {
    while ($row = sqlsrv_fetch_array($resHydrantStatus, SQLSRV_FETCH_ASSOC)) {
        echo "Status: " . $row['status'] . " => Count: " . $row['count'] . "\n";
    }
}

echo "\n=== ALL APAR SAMPLE ===\n";
$sqlSampleApar = "SELECT TOP 10 id, code, status FROM [apar].[dbo].[apars]";
$resSampleApar = sqlsrv_query($koneksi, $sqlSampleApar);
if ($resSampleApar) {
    while ($row = sqlsrv_fetch_array($resSampleApar, SQLSRV_FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | Code: " . $row['code'] . " | Status: " . $row['status'] . "\n";
    }
}

echo "\n=== ALL HYDRANT SAMPLE ===\n";
$sqlSampleHydrant = "SELECT TOP 10 id, code, status FROM [apar].[dbo].[hydrants]";
$resSampleHydrant = sqlsrv_query($koneksi, $sqlSampleHydrant);
if ($resSampleHydrant) {
    while ($row = sqlsrv_fetch_array($resSampleHydrant, SQLSRV_FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | Code: " . $row['code'] . " | Status: " . $row['status'] . "\n";
    }
}

?>
