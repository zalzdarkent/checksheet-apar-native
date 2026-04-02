<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

$month = $_GET['month'] ?? 'all';
$year = date('Y');

// Determine date range
if ($month === 'all') {
    $dateCondition = "1=1";
    $monthTitle = "All Time";
} else {
    $monthNum = intval($month);
    // Use safer method instead of strftime for Windows compatibility
    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $monthTitle = ($monthNum >= 1 && $monthNum <= 12) ? $monthNames[$monthNum - 1] . " " . $year : "Unknown";
    $dateCondition = "MONTH(inspection_date) = $monthNum AND YEAR(inspection_date) = $year";
}

try {
    // ========== TOTAL APAR ==========
    $query = "SELECT COUNT(*) as total FROM [apar].[dbo].[apars] WHERE is_active = 1";
    $stmt = sqlsrv_query($koneksi, $query);
    if ($stmt === false) {
        throw new Exception("Error: " . implode(", ", sqlsrv_errors()));
    }
    $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $total_apar = $result['total'] ?? 0;

    // ========== TOTAL HYDRANT ==========
    $query = "SELECT COUNT(*) as total FROM [apar].[dbo].[hydrants] WHERE is_active = 1";
    $stmt = sqlsrv_query($koneksi, $query);
    if ($stmt === false) {
        throw new Exception("Error: " . implode(", ", sqlsrv_errors()));
    }
    $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $total_hydrant = $result['total'] ?? 0;

    // ========== APAR ABNORMAL CASES ==========
    $query = "SELECT COUNT(*) as total FROM [apar].[dbo].[bimonthly_apar_inspections]
              WHERE $dateCondition
              AND (exp_date_ok != 1 OR pressure_ok != 1 OR weight_co2_ok != 1 
                   OR tube_ok != 1 OR hose_ok != 1 OR bracket_ok != 1 
                   OR wi_ok != 1 OR form_kejadian_ok != 1 OR sign_box_ok != 1 
                   OR sign_triangle_ok != 1 OR marking_tiger_ok != 1 OR marking_beam_ok != 1 
                   OR sr_apar_ok != 1 OR kocok_apar_ok != 1 OR label_ok != 1)";
    $stmt = sqlsrv_query($koneksi, $query);
    $apar_abnormal = 0;
    if ($stmt !== false) {
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $apar_abnormal = $result['total'] ?? 0;
    }

    // ========== HYDRANT ABNORMAL CASES ==========
    $query = "SELECT COUNT(*) as total FROM [apar].[dbo].[bimonthly_hydrant_inspections]
              WHERE $dateCondition
              AND (body_hydrant_ok != 1 OR selang_ok != 1 OR couple_join_ok != 1 
                   OR nozzle_ok != 1 OR check_sheet_ok != 1 OR valve_kran_ok != 1 
                   OR lampu_ok != 1 OR cover_lampu_ok != 1 OR box_display_ok != 1 
                   OR konsul_hydrant_ok != 1 OR jr_ok != 1 OR marking_ok != 1 
                   OR label_ok != 1)";
    $stmt = sqlsrv_query($koneksi, $query);
    $hydrant_abnormal = 0;
    if ($stmt !== false) {
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $hydrant_abnormal = $result['total'] ?? 0;
    }

    // ========== APAR INSPECTION PROGRESS (Current Month) ==========
    $current_month = date('m');
    $current_year = date('Y');
    
    $query = "SELECT COUNT(*) as total FROM [apar].[dbo].[bimonthly_apar_inspections] 
              WHERE MONTH(inspection_date) = $current_month AND YEAR(inspection_date) = $current_year";
    $stmt = sqlsrv_query($koneksi, $query);
    $apar_inspected_month = 0;
    if ($stmt !== false) {
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $apar_inspected_month = $result['total'] ?? 0;
    }
    $apar_progress = $total_apar > 0 ? round(($apar_inspected_month / $total_apar) * 100) : 0;

    // ========== HYDRANT INSPECTION PROGRESS (Current Month) ==========
    $query = "SELECT COUNT(*) as total FROM [apar].[dbo].[bimonthly_hydrant_inspections] 
              WHERE MONTH(inspection_date) = $current_month AND YEAR(inspection_date) = $current_year";
    $stmt = sqlsrv_query($koneksi, $query);
    $hydrant_inspected_month = 0;
    if ($stmt !== false) {
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $hydrant_inspected_month = $result['total'] ?? 0;
    }
    $hydrant_progress = $total_hydrant > 0 ? round(($hydrant_inspected_month / $total_hydrant) * 100) : 0;

    echo json_encode([
        'success' => true,
        'month_title' => $monthTitle,
        'apar' => [
            'total' => (int)$total_apar,
            'abnormal' => (int)$apar_abnormal,
            'inspected_month' => (int)$apar_inspected_month,
            'progress' => (int)$apar_progress
        ],
        'hydrant' => [
            'total' => (int)$total_hydrant,
            'abnormal' => (int)$hydrant_abnormal,
            'inspected_month' => (int)$hydrant_inspected_month,
            'progress' => (int)$hydrant_progress
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

