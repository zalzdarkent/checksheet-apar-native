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
    $monthNames = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $monthTitle = ($monthNum >= 1 && $monthNum <= 12) ? $monthNames[$monthNum - 1] . " " . $year : "Unknown";
    $dateCondition = "MONTH(t.inspection_date) = $monthNum AND YEAR(t.inspection_date) = $year";
}

try {
    // ========== TOTAL COUNTS ==========
    $query_totals = "SELECT 
                        SUM(CASE WHEN asset_type = 'APAR' THEN 1 ELSE 0 END) as total_apar,
                        SUM(CASE WHEN asset_type = 'HYDRANT' THEN 1 ELSE 0 END) as total_hydrant
                    FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE is_active = 1";
    $stmt_totals = sqlsrv_query($koneksi, $query_totals);
    $row_totals = sqlsrv_fetch_array($stmt_totals, SQLSRV_FETCH_ASSOC);
    $total_apar = $row_totals['total_apar'] ?? 0;
    $total_hydrant = $row_totals['total_hydrant'] ?? 0;

    // ========== ABNORMAL FINDINGS (at least 1 NG in session) ==========

    // APAR items list
    $apar_items = ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'];
    $apar_ng_check = implode(" = 0 OR ", $apar_items) . " = 0";

    // HYDRANT items list
    $hydrant_items = ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok'];
    $hydrant_ng_check = implode(" = 0 OR ", $hydrant_items) . " = 0";

    $query_apar_ab = "SELECT COUNT(*) as total FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] t
                      INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON t.asset_id = m.id
                      WHERE $dateCondition AND m.asset_type = 'APAR' AND ($apar_ng_check)";
    $stmt_apar_ab = sqlsrv_query($koneksi, $query_apar_ab);
    $apar_abnormal = sqlsrv_fetch_array($stmt_apar_ab, SQLSRV_FETCH_ASSOC)['total'] ?? 0;

    $query_hydrant_ab = "SELECT COUNT(*) as total FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] t
                         INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON t.asset_id = m.id
                         WHERE $dateCondition AND m.asset_type = 'HYDRANT' AND ($hydrant_ng_check)";
    $stmt_hydrant_ab = sqlsrv_query($koneksi, $query_hydrant_ab);
    $hydrant_abnormal = sqlsrv_fetch_array($stmt_hydrant_ab, SQLSRV_FETCH_ASSOC)['total'] ?? 0;

    // ========== INSPECTION PROGRESS (Current Month) ==========
    $current_month = date('m');
    $current_year = date('Y');

    $query_prog = "SELECT 
                     SUM(CASE WHEN m.asset_type = 'APAR' THEN 1 ELSE 0 END) as apar_inspected,
                     SUM(CASE WHEN m.asset_type = 'HYDRANT' THEN 1 ELSE 0 END) as hydrant_inspected
                   FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] t
                   INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON t.asset_id = m.id
                   WHERE MONTH(t.inspection_date) = $current_month AND YEAR(t.inspection_date) = $current_year";
    $stmt_prog = sqlsrv_query($koneksi, $query_prog);
    $row_prog = sqlsrv_fetch_array($stmt_prog, SQLSRV_FETCH_ASSOC);

    $apar_inspected_month = $row_prog['apar_inspected'] ?? 0;
    $hydrant_inspected_month = $row_prog['hydrant_inspected'] ?? 0;

    $apar_progress = $total_apar > 0 ? round(($apar_inspected_month / $total_apar) * 100) : 0;
    $hydrant_progress = $total_hydrant > 0 ? round(($hydrant_inspected_month / $total_hydrant) * 100) : 0;

    echo json_encode([
        'success' => true,
        'month_title' => $monthTitle,
        'apar' => [
            'total' => (int) $total_apar,
            'abnormal' => (int) $apar_abnormal,
            'inspected_month' => (int) $apar_inspected_month,
            'progress' => (int) $apar_progress
        ],
        'hydrant' => [
            'total' => (int) $total_hydrant,
            'abnormal' => (int) $hydrant_abnormal,
            'inspected_month' => (int) $hydrant_inspected_month,
            'progress' => (int) $hydrant_progress
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