<?php
require_once __DIR__ . '/../config/db_koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$area = $_POST['area'] ?? '';
$code = $_POST['code'] ?? '';
$location = $_POST['location'] ?? '';
$weight = $_POST['weight'] ?? '';
$type = $_POST['type'] ?? '';
$expired_date = $_POST['expired_date'] ?? '';
$status = $_POST['status'] ?? '';

// Validation
if (empty($area) || empty($code) || empty($location) || empty($weight) || empty($type) || empty($expired_date) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi!']);
    exit;
}

// Double check if code already exists
$check_query = "SELECT COUNT(*) as count FROM [apar].[dbo].[apars] WHERE code = ?";
$check_stmt = sqlsrv_query($koneksi, $check_query, [$code]);
$check_row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

if ($check_row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Kode APAR sudah digunakan!']);
    exit;
}

// Prepare Insert
$query = "INSERT INTO [apar].[dbo].[apars] 
          (code, location, area, weight, type, expired_date, status, is_active, created_at, updated_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, 1, GETDATE(), GETDATE())";

$params = array($code, $location, $area, $weight, $type, $expired_date, $status);
$stmt = sqlsrv_query($koneksi, $query, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $errors[0]['message']]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Data APAR berhasil ditambahkan']);
?>
