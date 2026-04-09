<?php
include(__DIR__ . '/../config/db_koneksi.php');

header('Content-Type: application/json');

$ids_raw = isset($_POST['ids']) ? $_POST['ids'] : '';
$status = isset($_POST['status']) ? (int)$_POST['status'] : 0; 

if (empty($ids_raw)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit;
}

$ids_array = is_array($ids_raw) ? $ids_raw : explode(',', $ids_raw);
$ids = array_map('intval', array_filter($ids_array));

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No valid IDs.']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

// All asset types live in SE_FIRE_PROTECTION_MASTER now
$sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] SET is_active = ?, updated_at = GETDATE() WHERE id IN ($placeholders)";
$params = array_merge([$status], $ids);

$stmt = sqlsrv_query($koneksi, $sql, $params);

if ($stmt) {
    echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)]);
}
?>
