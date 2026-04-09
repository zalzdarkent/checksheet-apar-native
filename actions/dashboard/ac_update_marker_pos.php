<?php
include(__DIR__ . '/../../config/db_koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $device_type = strtoupper($_POST['device_type'] ?? '');
    $x = $_POST['x_coordinate'];
    $y = $_POST['y_coordinate'];

    if (empty($code) || empty($device_type) || $x === null || $y === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    $sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] 
            SET x_coordinate = ?, y_coordinate = ?, updated_at = GETDATE() 
            WHERE asset_code = ? AND asset_type = ?";
    $params = [$x, $y, $code, $device_type];
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt) {
        echo json_encode(['status' => 'success', 'message' => 'Marker position updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
