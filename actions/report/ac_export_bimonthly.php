<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

$type = $_GET['type'] ?? 'apar';
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
    if ($type === 'apar') {
        $query = "SELECT DISTINCT
                    a.id, a.code, a.area, a.location, a.type,
                    bi.inspection_date, bi.exp_date_ok, bi.pressure_ok, bi.weight_co2_ok,
                    bi.tube_ok, bi.hose_ok, bi.bracket_ok, bi.wi_ok, bi.form_kejadian_ok,
                    bi.sign_box_ok, bi.sign_triangle_ok, bi.marking_tiger_ok, bi.marking_beam_ok,
                    bi.sr_apar_ok, bi.kocok_apar_ok, bi.label_ok
                  FROM [apar].[dbo].[apars] a
                  LEFT JOIN [apar].[dbo].[bimonthly_apar_inspections] bi 
                    ON a.id = bi.apar_id AND YEAR(bi.inspection_date) = $year
                  WHERE a.is_active = 1
                  ORDER BY a.area, a.location, a.code";
        
        $title = "LAPORAN PEMERIKSAAN APAR (PER 2 BULAN) TAHUN $year";
        $isApar = true;
    } else {
        $query = "SELECT DISTINCT
                    h.id, h.code, h.area, h.location, h.type,
                    bi.inspection_date, bi.body_hydrant_ok, bi.selang_ok, bi.couple_join_ok,
                    bi.nozzle_ok, bi.check_sheet_ok, bi.valve_kran_ok, bi.lampu_ok,
                    bi.cover_lampu_ok, bi.box_display_ok, bi.konsul_hydrant_ok, bi.jr_ok,
                    bi.marking_ok, bi.label_ok
                  FROM [apar].[dbo].[hydrants] h
                  LEFT JOIN [apar].[dbo].[bimonthly_hydrant_inspections] bi 
                    ON h.id = bi.hydrant_id AND YEAR(bi.inspection_date) = $year
                  WHERE h.is_active = 1
                  ORDER BY h.area, h.location, h.code";
        
        $title = "LAPORAN PEMERIKSAAN HYDRANT (PER 2 BULAN) TAHUN $year";
        $isApar = false;
    }

    $stmt = sqlsrv_query($koneksi, $query);
    
    // Fetch all equipment data
    $equipment = array();
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $equipment[] = $row;
        }
    }

    if ($format === 'excel') {
        // CSV Export
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$title.csv\"");
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        $header = array('No', 'Kode', 'Area', 'Lokasi', 'Type');
        foreach ($periods as $period) {
            $header[] = $period['name'];
        }
        fputcsv($output, $header);
        
        // Data
        $no = 1;
        $equipment_data = array();
        
        foreach ($equipment as $item) {
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
            
            // Check inspection for this period
            if (!empty($item['inspection_date'])) {
                $month = $item['inspection_date']->format('n');
                
                foreach ($periods as $idx => $period) {
                    if (in_array($month, $period['months'])) {
                        if ($isApar) {
                            $params = array(
                                $item['exp_date_ok'], $item['pressure_ok'], $item['weight_co2_ok'],
                                $item['tube_ok'], $item['hose_ok'], $item['bracket_ok'], $item['wi_ok'],
                                $item['form_kejadian_ok'], $item['sign_box_ok'], $item['sign_triangle_ok'],
                                $item['marking_tiger_ok'], $item['marking_beam_ok'], $item['sr_apar_ok'],
                                $item['kocok_apar_ok'], $item['label_ok']
                            );
                        } else {
                            $params = array(
                                $item['body_hydrant_ok'], $item['selang_ok'], $item['couple_join_ok'],
                                $item['nozzle_ok'], $item['check_sheet_ok'], $item['valve_kran_ok'], $item['lampu_ok'],
                                $item['cover_lampu_ok'], $item['box_display_ok'], $item['konsul_hydrant_ok'],
                                $item['jr_ok'], $item['marking_ok'], $item['label_ok']
                            );
                        }
                        
                        $all_ok = true;
                        foreach ($params as $p) {
                            if ($p != 1) {
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
        
        foreach ($equipment_data as $item) {
            $row = array($item['no'], $item['code'], $item['area'], $item['location'], $item['type']);
            foreach ($periods as $idx => $period) {
                $row[] = $item['periods'][$idx];
            }
            fputcsv($output, $row);
        }
        fclose($output);

    } else if ($format === 'pdf') {
        // PDF Export
        require_once __DIR__ . '/../../assets/vendor/phpqrcode/bindings/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, "TAHUN $year", 0, 1, 'C');
        $pdf->Ln(3);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(0, 102, 204);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(8, 7, 'No', 1, 0, 'C', true);
        $pdf->Cell(15, 7, 'Kode', 1, 0, 'C', true);
        $pdf->Cell(18, 7, 'Area', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Lokasi', 1, 0, 'L', true);
        foreach ($periods as $p) {
            $pdf->Cell(20, 7, $p['name'], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Table data
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetTextColor(0, 0, 0);
        $no = 1;
        
        $equipment_data = array();
        foreach ($equipment as $item) {
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
                        if ($isApar) {
                            $params = array(
                                $item['exp_date_ok'], $item['pressure_ok'], $item['weight_co2_ok'],
                                $item['tube_ok'], $item['hose_ok'], $item['bracket_ok'], $item['wi_ok'],
                                $item['form_kejadian_ok'], $item['sign_box_ok'], $item['sign_triangle_ok'],
                                $item['marking_tiger_ok'], $item['marking_beam_ok'], $item['sr_apar_ok'],
                                $item['kocok_apar_ok'], $item['label_ok']
                            );
                        } else {
                            $params = array(
                                $item['body_hydrant_ok'], $item['selang_ok'], $item['couple_join_ok'],
                                $item['nozzle_ok'], $item['check_sheet_ok'], $item['valve_kran_ok'], $item['lampu_ok'],
                                $item['cover_lampu_ok'], $item['box_display_ok'], $item['konsul_hydrant_ok'],
                                $item['jr_ok'], $item['marking_ok'], $item['label_ok']
                            );
                        }
                        
                        $all_ok = true;
                        foreach ($params as $p) {
                            if ($p != 1) {
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
            if ($item['no'] % 2 == 0) {
                $pdf->SetFillColor(240, 240, 240);
                $fill = true;
            } else {
                $pdf->SetFillColor(255, 255, 255);
                $fill = false;
            }
            
            $pdf->Cell(8, 6, $item['no'], 1, 0, 'C', $fill);
            $pdf->Cell(15, 6, $item['code'], 1, 0, 'C', $fill);
            $pdf->Cell(18, 6, substr($item['area'], 0, 8), 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, substr($item['location'], 0, 18), 1, 0, 'L', $fill);
            
            foreach ($periods as $idx => $period) {
                $pdf->Cell(20, 6, $item['periods'][$idx], 1, 0, 'C', $fill);
            }
            
            $pdf->Ln();
        }
        
        $pdf->Output($title . '.pdf', 'D');
    }

} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Error: ' . $e->getMessage();
}
?>
