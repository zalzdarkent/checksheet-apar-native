<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'apar';
$month = $_GET['month'] ?? 'all';
$asset_type = strtoupper($type);

$dateFilter = "";
if ($month !== 'all') {
    $monthNum = intval($month);
    $year = date('Y');
    $dateFilter = "AND MONTH(bi.inspection_date) = $monthNum AND YEAR(bi.inspection_date) = $year";
}

try {
    $query = "SELECT TOP 5
                bi.id, bi.inspection_date, m.asset_code as code, m.area, m.location, bi.*
              FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
              INNER JOIN [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
              WHERE m.asset_type = ? AND m.is_active = 1 $dateFilter
              ORDER BY bi.inspection_date DESC";

    $stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
    $data = array();

    if ($stmt === false) throw new Exception("Query failed: " . print_r(sqlsrv_errors(), true));

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $check_items = ($type === 'apar') ? 
            ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
            ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];

        $all_ok = true;
        foreach ($check_items as $ci) { if (isset($row[$ci]) && $row[$ci] != 1) { $all_ok = false; break; } }

        $data[] = array(
            'id' => (int)$row['id'],
            'inspection_date' => ($row['inspection_date'] instanceof DateTime) ? $row['inspection_date']->format('d-m-Y') : '-',
            'code' => (string)$row['code'],
            'area' => (string)$row['area'],
            'location' => (string)$row['location'],
            'status' => $all_ok ? 'OK' : 'Abnormal'
        );
    }
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}
?>
