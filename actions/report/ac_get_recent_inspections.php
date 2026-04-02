<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'apar';
$month = $_GET['month'] ?? 'all';

if ($month === 'all') {
    $dateFilter = "";
} else {
    $monthNum = intval($month);
    $year = date('Y');
    $dateFilter = "AND MONTH(bi.inspection_date) = $monthNum AND YEAR(bi.inspection_date) = $year";
}

try {
    if ($type === 'apar') {
        $query = "SELECT TOP 5
                    bi.id,
                    bi.inspection_date,
                    a.code,
                    a.area,
                    a.location,
                    bi.exp_date_ok,
                    bi.pressure_ok,
                    bi.weight_co2_ok,
                    bi.tube_ok,
                    bi.hose_ok,
                    bi.bracket_ok,
                    bi.wi_ok,
                    bi.form_kejadian_ok,
                    bi.sign_box_ok,
                    bi.sign_triangle_ok,
                    bi.marking_tiger_ok,
                    bi.marking_beam_ok,
                    bi.sr_apar_ok,
                    bi.kocok_apar_ok,
                    bi.label_ok
                  FROM [apar].[dbo].[bimonthly_apar_inspections] bi
                  INNER JOIN [apar].[dbo].[apars] a ON bi.apar_id = a.id
                  WHERE 1=1 $dateFilter
                  ORDER BY bi.inspection_date DESC";
    } else {
        $query = "SELECT TOP 5
                    bi.id,
                    bi.inspection_date,
                    h.code,
                    h.area,
                    h.location,
                    bi.body_hydrant_ok,
                    bi.selang_ok,
                    bi.couple_join_ok,
                    bi.nozzle_ok,
                    bi.check_sheet_ok,
                    bi.valve_kran_ok,
                    bi.lampu_ok,
                    bi.cover_lampu_ok,
                    bi.box_display_ok,
                    bi.konsul_hydrant_ok,
                    bi.jr_ok,
                    bi.marking_ok,
                    bi.label_ok
                  FROM [apar].[dbo].[bimonthly_hydrant_inspections] bi
                  INNER JOIN [apar].[dbo].[hydrants] h ON bi.hydrant_id = h.id
                  WHERE 1=1 $dateFilter
                  ORDER BY bi.inspection_date DESC";
    }

    $stmt = sqlsrv_query($koneksi, $query);
    $data = array();

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(array('error' => 'Query failed', 'details' => sqlsrv_errors()));
        exit;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ATTR_ASSOC)) {
        try {
            if ($type === 'apar') {
                $params = array(
                    $row['exp_date_ok'], $row['pressure_ok'], $row['weight_co2_ok'],
                    $row['tube_ok'], $row['hose_ok'], $row['bracket_ok'], $row['wi_ok'],
                    $row['form_kejadian_ok'], $row['sign_box_ok'], $row['sign_triangle_ok'],
                    $row['marking_tiger_ok'], $row['marking_beam_ok'], $row['sr_apar_ok'],
                    $row['kocok_apar_ok'], $row['label_ok']
                );
            } else {
                $params = array(
                    $row['body_hydrant_ok'], $row['selang_ok'], $row['couple_join_ok'],
                    $row['nozzle_ok'], $row['check_sheet_ok'], $row['valve_kran_ok'], $row['lampu_ok'],
                    $row['cover_lampu_ok'], $row['box_display_ok'], $row['konsul_hydrant_ok'],
                    $row['jr_ok'], $row['marking_ok'], $row['label_ok']
                );
            }

            $all_ok = true;
            foreach ($params as $param) {
                if ($param != 1) {
                    $all_ok = false;
                    break;
                }
            }

            $status = $all_ok ? 'OK' : 'Abnormal';
            
            // Format date properly from DateTime object
            if ($row['inspection_date'] instanceof DateTime) {
                $formatted_date = $row['inspection_date']->format('d-m-Y');
            } else {
                $formatted_date = (string)$row['inspection_date'];
            }

            $data[] = array(
                'id' => (int)$row['id'],
                'inspection_date' => $formatted_date,
                'code' => (string)$row['code'],
                'area' => (string)$row['area'],
                'location' => (string)$row['location'],
                'status' => $status
            );
        } catch (Exception $e) {
            continue;
        }
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode(array());
}
?>
