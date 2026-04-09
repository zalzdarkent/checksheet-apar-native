<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

$area = $_GET['area'] ?? '';
$type = $_GET['type'] ?? 'apar'; // 'apar' or 'hydrant'

if (empty($area)) {
    echo json_encode(['success' => false, 'message' => 'Area required']);
    exit;
}

$prefix = "";
switch ($area) {
    case 'Disa':
        $prefix = "D-1-";
        break;
    case 'Machining':
        $prefix = "M-1-";
        break;
    case 'Ace':
        $prefix = "A-1-";
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid area']);
        exit;
}

// Unified query from MASTER table using asset_code
$query = "SELECT MAX(asset_code) as max_code FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE asset_code LIKE ? AND asset_type = ?";
$params = array($prefix . '%', strtoupper($type));
$stmt = sqlsrv_query($koneksi, $query, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$next_code = "";

if ($row['max_code']) {
    $parts = explode('-', $row['max_code']);
    $last_num = end($parts);

    if (is_numeric($last_num)) {
        $next_num = intval($last_num) + 1;
        $next_num_padded = str_pad($next_num, 3, "0", STR_PAD_LEFT);
        $next_code = $prefix . $next_num_padded;
    } else {
        $next_code = $prefix . "001";
    }
} else {
    $next_code = $prefix . "001";
}

echo json_encode(['success' => true, 'next_code' => $next_code]);
?>