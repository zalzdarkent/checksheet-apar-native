<?php
include(__DIR__ . '/../../config/db_koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $device_type = $_POST['device_type'];
    $x = $_POST['x_coordinate'];
    $y = $_POST['y_coordinate'];

    if (empty($code) || empty($device_type) || $x === null || $y === null) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    $table = ($device_type === 'apar') ? '[apar].[dbo].[apars]' : '[apar].[dbo].[hydrants]';
    
    $sql = "UPDATE $table SET x_coordinate = ?, y_coordinate = ? WHERE code = ?";
    $params = [$x, $y, $code];
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt) {
        echo json_encode(['status' => 'success', 'message' => 'Marker position updated']);
    } else {
        $errors = sqlsrv_errors();
        echo json_encode(['status' => 'error', 'message' => $errors[0]['message']]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
