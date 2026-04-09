<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
$type = $_GET['type'] ?? 'apar';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Code required']);
    exit;
}

// Unified check in MASTER table
$query = "SELECT COUNT(*) as count FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE asset_code = ? AND asset_type = ?";
$params = array($code, strtoupper($type));
$stmt = sqlsrv_query($koneksi, $query, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$exists = ($row['count'] > 0);

echo json_encode(['success' => true, 'exists' => $exists]);
?>