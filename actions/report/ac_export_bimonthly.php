<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

$type = $_GET['type'] ?? 'apar'; // 'apar' or 'hydrant'
$format = $_GET['format'] ?? 'excel';
$year = date('Y');

$periods = array(
    1 => array('name' => 'I (Jan-Feb)', 'months' => array(1, 2)),
    2 => array('name' => 'II (Mar-Apr)', 'months' => array(3, 4)),
    3 => array('name' => 'III (Mei-Jun)', 'months' => array(5, 6)),
    4 => array('name' => 'IV (Jul-Agt)', 'months' => array(7, 8)),
    5 => array('name' => 'V (Sep-Okt)', 'months' => array(9, 10)),
    6 => array('name' => 'VI (Nov-Des)', 'months' => array(11, 12))
);

try {
    $asset_type = strtoupper($type);

    // Unified query from Master + Trans
    $query = "SELECT 
                m.id, m.asset_code as code, m.area, m.location, m.model_type as type,
                bi.inspection_date, bi.*
              FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m
              LEFT JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] bi 
                ON m.id = bi.asset_id AND YEAR(bi.inspection_date) = $year
              WHERE m.asset_type = ? AND m.is_active = 1
              ORDER BY m.area, m.location, m.asset_code";

    $stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
    if ($stmt === false)
        throw new Exception("Database Error: " . print_r(sqlsrv_errors(), true));

    $title = "LAPORAN PEMERIKSAAN " . strtoupper($type) . " (PER 2 BULAN) TAHUN $year";
    $isApar = ($type === 'apar');

    // Fetch all equipment data
    $equipment_raw = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $equipment_raw[] = $row;
    }

    if ($format === 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$title.csv\"");
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $header = array('No', 'Kode', 'Area', 'Lokasi', 'Type');
        foreach ($periods as $period)
            $header[] = $period['name'];
        fputcsv($output, $header);

        $no = 1;
        $equipment_data = array();

        foreach ($equipment_raw as $item) {
            $key = $item['id'];
            if (!isset($equipment_data[$key])) {
                $equipment_data[$key] = array(
                    'no' => $no++,
                    'code' => $item['code'],
                    'area' => $item['area'],
                    'location' => $item['location'],
                    'type' => $item['type'] ?? '-',
                    'periods' => array_fill_keys(array_keys($periods), '-')
                );
            }

            if (!empty($item['inspection_date'])) {
                $month = $item['inspection_date']->format('n');
                foreach ($periods as $idx => $period) {
                    if (in_array($month, $period['months'])) {
                        $check_items = $isApar ?
                            ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
                            ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];

                        $all_ok = true;
                        foreach ($check_items as $ci) {
                            if (isset($item[$ci]) && $item[$ci] != 1) {
                                $all_ok = false;
                                break;
                            }
                        }
                        $equipment_data[$key]['periods'][$idx] = $all_ok ? 'OK' : 'ABNORMAL';
                        break;
                    }
                }
            }
        }

        foreach ($equipment_data as $row_data) {
            $row = array($row_data['no'], $row_data['code'], $row_data['area'], $row_data['location'], $row_data['type']);
            foreach ($periods as $idx => $period)
                $row[] = $row_data['periods'][$idx];
            fputcsv($output, $row);
        }
        fclose($output);

    } else if ($format === 'pdf') {
        require_once __DIR__ . '/../../assets/vendor/phpqrcode/bindings/tcpdf/tcpdf.php';

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, "TAHUN $year", 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(0, 102, 204);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(8, 7, 'No', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Kode', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Area', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Lokasi', 1, 0, 'L', true);
        foreach ($periods as $p)
            $pdf->Cell(25, 7, $p['name'], 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetTextColor(0, 0, 0);
        $no = 1;
        $equipment_data = array();
        foreach ($equipment_raw as $item) {
            $key = $item['id'];
            if (!isset($equipment_data[$key])) {
                $equipment_data[$key] = array(
                    'no' => $no++,
                    'code' => $item['code'],
                    'area' => $item['area'],
                    'location' => $item['location'],
                    'periods' => array_fill_keys(array_keys($periods), '-')
                );
            }
            if (!empty($item['inspection_date'])) {
                $month = $item['inspection_date']->format('n');
                foreach ($periods as $idx => $period) {
                    if (in_array($month, $period['months'])) {
                        $check_items = $isApar ?
                            ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
                            ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
                        $all_ok = true;
                        foreach ($check_items as $ci) {
                            if (isset($item[$ci]) && $item[$ci] != 1) {
                                $all_ok = false;
                                break;
                            }
                        }
                        $equipment_data[$key]['periods'][$idx] = $all_ok ? 'OK' : 'ABN';
                        break;
                    }
                }
            }
        }

        foreach ($equipment_data as $item) {
            $fill = ($item['no'] % 2 == 0);
            if ($fill)
                $pdf->SetFillColor(240, 240, 240);
            else
                $pdf->SetFillColor(255, 255, 255);

            $pdf->Cell(8, 6, $item['no'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $item['code'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, substr($item['area'], 0, 10), 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, substr($item['location'], 0, 25), 1, 0, 'L', $fill);
            foreach ($periods as $idx => $period)
                $pdf->Cell(25, 6, $item['periods'][$idx], 1, 0, 'C', $fill);
            $pdf->Ln();
        }
        $pdf->Output($title . '.pdf', 'D');
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>