<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$area     = $_POST['area']     ?? '';
$code     = $_POST['code']     ?? '';
$location = $_POST['location'] ?? '';
$type     = $_POST['type']     ?? '';
$status   = $_POST['status']   ?? '';

// Validation
if (empty($area) || empty($code) || empty($location) || empty($type) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']);
    exit;
}

// Check if code already exists
$check_query = "SELECT COUNT(*) as count FROM [apar].[dbo].[hydrants] WHERE code = ?";
$check_stmt  = sqlsrv_query($koneksi, $check_query, [$code]);
$check_row   = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

if ($check_row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Kode Hydrant sudah digunakan!']);
    exit;
}

// Insert
$query = "INSERT INTO [apar].[dbo].[hydrants]
          (code, location, area, type, status, is_active, created_at, updated_at)
          VALUES (?, ?, ?, ?, ?, 1, GETDATE(), GETDATE())";

$params = [$code, $location, $area, $type, $status];
$stmt   = sqlsrv_query($koneksi, $query, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $errors[0]['message']]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Data Hydrant berhasil ditambahkan']);
?>
