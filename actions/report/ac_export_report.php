<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

$type = $_GET['type'] ?? 'apar'; // apar or hydrant
$format = $_GET['format'] ?? 'excel'; // excel or pdf
$month = $_GET['month'] ?? 'all';

// Determine date filter
if ($month === 'all') {
    $dateFilter = "";
    $monthTitle = "All Time";
} else {
    $monthNum = intval($month);
    $year = date('Y');
    $dateFilter = "AND MONTH(bi.inspection_date) = $monthNum AND YEAR(bi.inspection_date) = $year";
    $monthTitle = date('F Y', mktime(0, 0, 0, $monthNum, 1, $year));
}

try {
    if ($type === 'apar') {
        // Get APAR inspections data
        $query = "SELECT
                    bi.inspection_date,
                    a.code,
                    a.area,
                    a.location,
                    u.name as inspector,
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
                  JOIN [apar].[dbo].[apars] a ON bi.apar_id = a.id
                  LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
                  WHERE 1=1 $dateFilter
                  ORDER BY bi.inspection_date DESC";
        
        $title = "APAR Inspections Report - $monthTitle";
        $filename = "Report_APAR_" . date('Y-m-d');
    } else {
        // Get Hydrant inspections data
        $query = "SELECT
                    bi.inspection_date,
                    h.code,
                    h.area,
                    h.location,
                    u.name as inspector,
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
                  JOIN [apar].[dbo].[hydrants] h ON bi.hydrant_id = h.id
                  LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
                  WHERE 1=1 $dateFilter
                  ORDER BY bi.inspection_date DESC";
        
        $title = "Hydrant Inspections Report - $monthTitle";
        $filename = "Report_Hydrant_" . date('Y-m-d');
    }

    $stmt = sqlsrv_query($koneksi, $query);
    
    if ($format === 'excel') {
        // Export as CSV (Excel compatible)
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header
        if ($type === 'apar') {
            fputcsv($output, ['Tanggal', 'Kode', 'Area', 'Lokasi', 'Inspector', 'Status'], ',');
        } else {
            fputcsv($output, ['Tanggal', 'Kode', 'Area', 'Lokasi', 'Inspector', 'Status'], ',');
        }
        
        // Write data rows
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Determine status
            if ($type === 'apar') {
                $params = [
                    $row['exp_date_ok'], $row['pressure_ok'], $row['weight_co2_ok'],
                    $row['tube_ok'], $row['hose_ok'], $row['bracket_ok'], $row['wi_ok'],
                    $row['form_kejadian_ok'], $row['sign_box_ok'], $row['sign_triangle_ok'],
                    $row['marking_tiger_ok'], $row['marking_beam_ok'], $row['sr_apar_ok'],
                    $row['kocok_apar_ok'], $row['label_ok']
                ];
            } else {
                $params = [
                    $row['body_hydrant_ok'], $row['selang_ok'], $row['couple_join_ok'],
                    $row['nozzle_ok'], $row['check_sheet_ok'], $row['valve_kran_ok'],
                    $row['lampu_ok'], $row['cover_lampu_ok'], $row['box_display_ok'],
                    $row['konsul_hydrant_ok'], $row['jr_ok'], $row['marking_ok'], $row['label_ok']
                ];
            }
            
            $all_ok = true;
            foreach ($params as $param) {
                if ($param != 1) {
                    $all_ok = false;
                    break;
                }
            }
            
            $status = $all_ok ? 'OK' : 'Abnormal';
            $date = $row['inspection_date'] ? $row['inspection_date']->format('d-m-Y') : '-';
            
            fputcsv($output, [
                $date,
                $row['code'],
                $row['area'],
                $row['location'],
                $row['inspector'] ?? '-',
                $status
            ], ',');
        }
        
        fclose($output);
        
    } else if ($format === 'pdf') {
        // Export as PDF using TCPDF
        require_once __DIR__ . '/../../assets/vendor/phpqrcode/bindings/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 10, 15);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(0, 102, 204);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(25, 7, 'Tanggal', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Kode', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Area', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Lokasi', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Inspector', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Status', 1, 1, 'C', true);
        
        // Table data
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        $rowNum = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Determine status
            if ($type === 'apar') {
                $params = [
                    $row['exp_date_ok'], $row['pressure_ok'], $row['weight_co2_ok'],
                    $row['tube_ok'], $row['hose_ok'], $row['bracket_ok'], $row['wi_ok'],
                    $row['form_kejadian_ok'], $row['sign_box_ok'], $row['sign_triangle_ok'],
                    $row['marking_tiger_ok'], $row['marking_beam_ok'], $row['sr_apar_ok'],
                    $row['kocok_apar_ok'], $row['label_ok']
                ];
            } else {
                $params = [
                    $row['body_hydrant_ok'], $row['selang_ok'], $row['couple_join_ok'],
                    $row['nozzle_ok'], $row['check_sheet_ok'], $row['valve_kran_ok'],
                    $row['lampu_ok'], $row['cover_lampu_ok'], $row['box_display_ok'],
                    $row['konsul_hydrant_ok'], $row['jr_ok'], $row['marking_ok'], $row['label_ok']
                ];
            }
            
            $all_ok = true;
            foreach ($params as $param) {
                if ($param != 1) {
                    $all_ok = false;
                    break;
                }
            }
            
            $status = $all_ok ? 'OK' : 'Abnormal';
            $date = $row['inspection_date'] ? $row['inspection_date']->format('d-m-Y') : '-';
            
            // Alternating row colors
            if ($rowNum % 2 == 0) {
                $pdf->SetFillColor(240, 240, 240);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }
            
            $pdf->Cell(25, 6, $date, 1, 0, 'C', true);
            $pdf->Cell(25, 6, $row['code'], 1, 0, 'C', true);
            $pdf->Cell(30, 6, substr($row['area'], 0, 10), 1, 0, 'L', true);
            $pdf->Cell(50, 6, substr($row['location'], 0, 20), 1, 0, 'L', true);
            $pdf->Cell(25, 6, substr($row['inspector'] ?? '-', 0, 15), 1, 0, 'C', true);
            $pdf->Cell(20, 6, $status, 1, 1, 'C', true);
            
            $rowNum++;
        }
        
        $pdf->Output($filename . '.pdf', 'D');
    }

} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Error: ' . $e->getMessage();
}
?>
