<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

$type = $_GET['type'] ?? 'apar';
$format = $_GET['format'] ?? 'excel';
$month = $_GET['month'] ?? 'all';
$asset_type = strtoupper($type);

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
    $query = "SELECT 
                bi.inspection_date, m.asset_code as code, m.area, m.location, bi.*,
                ISNULL(e.EmployeeName, u.REALNAME) as inspector
              FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
              INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
              LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e ON bi.user_id = e.EmpID
              LEFT JOIN [ATI].[Users].[UserTable] u ON bi.user_id = u.EMPID
              WHERE m.asset_type = ? $dateFilter
              ORDER BY bi.inspection_date DESC";

    $stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
    if ($stmt === false)
        throw new Exception("Database Error: " . print_r(sqlsrv_errors(), true));

    $title = strtoupper($type) . " Inspections Report - $monthTitle";
    $filename = "Report_" . strtoupper($type) . "_" . date('Y-m-d');

    if ($format === 'excel') {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Tanggal', 'Kode', 'Area', 'Lokasi', 'Inspector', 'Status'], ',');

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $check_items = ($type === 'apar') ?
                ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
                ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];

            $all_ok = true;
            foreach ($check_items as $ci) {
                if (isset($row[$ci]) && $row[$ci] != 1) {
                    $all_ok = false;
                    break;
                }
            }
            $date = $row['inspection_date'] ? $row['inspection_date']->format('d-m-Y') : '-';
            fputcsv($output, [$date, $row['code'], $row['area'], $row['location'], $row['inspector'] ?? '-', $all_ok ? 'OK' : 'ABNORMAL'], ',');
        }
        fclose($output);

    } else if ($format === 'pdf') {
        require_once __DIR__ . '/../../assets/vendor/phpqrcode/bindings/tcpdf/tcpdf.php';
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(0, 102, 204);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(25, 7, 'Tanggal', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Kode', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Area', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Lokasi', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Inspector', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Status', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $no = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $check_items = ($type === 'apar') ?
                ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
                ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
            $all_ok = true;
            foreach ($check_items as $ci) {
                if (isset($row[$ci]) && $row[$ci] != 1) {
                    $all_ok = false;
                    break;
                }
            }

            $pdf->SetFillColor(($no % 2 == 0) ? 240 : 255, ($no % 2 == 0) ? 240 : 255, ($no % 2 == 0) ? 240 : 255);
            $pdf->Cell(25, 6, $row['inspection_date'] ? $row['inspection_date']->format('d-m-Y') : '-', 1, 0, 'C', true);
            $pdf->Cell(25, 6, $row['code'], 1, 0, 'C', true);
            $pdf->Cell(30, 6, substr($row['area'], 0, 15), 1, 0, 'L', true);
            $pdf->Cell(50, 6, substr($row['location'], 0, 25), 1, 0, 'L', true);
            $pdf->Cell(30, 6, substr($row['inspector'] ?? '-', 0, 20), 1, 0, 'C', true);
            $pdf->Cell(20, 6, $all_ok ? 'OK' : 'ABN', 1, 1, 'C', true);
            $no++;
        }
        $pdf->Output($filename . '.pdf', 'D');
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>