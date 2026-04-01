<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
$type = $_GET['type'] ?? 'apar';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Code required']);
    exit;
}

$table = ($type === 'hydrant') ? '[apar].[dbo].[hydrants]' : '[apar].[dbo].[apars]';

$query = "SELECT COUNT(*) as count FROM $table WHERE code = ?";
$params = array($code);
$stmt = sqlsrv_query($koneksi, $query, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$exists = ($row['count'] > 0);

echo json_encode(['success' => true, 'exists' => $exists]);
?>
