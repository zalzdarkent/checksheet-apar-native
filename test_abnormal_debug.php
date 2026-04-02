<?php
include 'config/db_koneksi.php';

// Check APAR 79 status
echo "=== APAR 79 Current Status ===\n";
$sql = "SELECT id, code, status FROM [apar].[dbo].[apars] WHERE id = 79";
$result = sqlsrv_query($koneksi, $sql);
if ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Code: {$row['code']} | Status: {$row['status']}\n";
}

// Check inspection data for APAR 79
echo "\n=== Inspection Items (bimonthly_apar_inspections) for APAR 79 ===\n";
$sql = "SELECT TOP 1 
    exp_date_ok, pressure_ok, weight_co2_ok, tube_ok, hose_ok, bracket_ok, 
    wi_ok, form_kejadian_ok, sign_box_ok, sign_triangle_ok, marking_tiger_ok, 
    marking_beam_ok, sr_apar_ok, kocok_apar_ok, label_ok,
    created_at
FROM [apar].[dbo].[bimonthly_apar_inspections] 
WHERE apar_id = 79 
ORDER BY created_at DESC";
$result = sqlsrv_query($koneksi, $sql);
if ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    echo "Latest Inspection (Created: " . $row['created_at']->format('Y-m-d H:i:s') . "):\n";
    foreach ($row as $key => $val) {
        if (strpos($key, '_ok') !== false) {
            echo "  " . str_replace('_ok', '', $key) . ": " . $val . "\n";
        }
    }
}

// Check abnormal cases
echo "\n=== Abnormal Cases for APAR 79 ===\n";
$sql = "SELECT id, abnormal_case, status, created_at FROM [apar].[dbo].[abnormal_cases] WHERE apar_id = 79 ORDER BY created_at DESC";
$result = sqlsrv_query($koneksi, $sql);
$count = 0;
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $count++;
    $created = $row['created_at'] instanceof DateTime ? $row['created_at']->format('Y-m-d H:i:s') : $row['created_at'];
    echo "Case #{$count}: {$row['abnormal_case']} (Status: {$row['status']}, Created: $created)\n";
}
if ($count == 0) {
    echo "No abnormal cases found\n";
}
?>
