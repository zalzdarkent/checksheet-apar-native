<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

// Header untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Laporan_Inspeksi_APAR_' . date('Y-m-d') . '.xlsx"');

$type = $_GET['type'] ?? 'apar';
$bulan = date('m');
$tahun = date('Y');
$monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulanText = $monthNames[$bulan - 1];

// Get data from database
if ($type === 'apar') {
    $query = "SELECT TOP 100
                bi.id,
                bi.inspection_date,
                a.code,
                a.area,
                a.location,
                a.expired_date,
                bi.exp_date_ok, bi.pressure_ok, bi.weight_co2_ok, bi.tube_ok, 
                bi.hose_ok, bi.bracket_ok, bi.wi_ok, bi.form_kejadian_ok,
                bi.sign_box_ok, bi.sign_triangle_ok, bi.marking_tiger_ok, bi.marking_beam_ok,
                bi.sr_apar_ok, bi.kocok_apar_ok, bi.label_ok,
                u.name as inspector,
                bi.notes
              FROM [apar].[dbo].[bimonthly_apar_inspections] bi
              LEFT JOIN [apar].[dbo].[apars] a ON bi.apar_id = a.id
              LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
              WHERE MONTH(bi.inspection_date) = $bulan AND YEAR(bi.inspection_date) = $tahun
              ORDER BY bi.inspection_date DESC";
    $title = "LAPORAN INSPEKSI BIMONTHLY APAR";
} else {
    $query = "SELECT TOP 100
                bi.id,
                bi.inspection_date,
                h.code,
                h.area,
                h.location,
                h.expired_date,
                bi.body_hydrant_ok, bi.selang_ok, bi.couple_join_ok, bi.nozzle_ok,
                bi.check_sheet_ok, bi.valve_kran_ok, bi.lampu_ok, bi.cover_lampu_ok,
                bi.box_display_ok, bi.konsul_hydrant_ok, bi.jr_ok, bi.marking_ok, bi.label_ok,
                u.name as inspector,
                bi.notes
              FROM [apar].[dbo].[bimonthly_hydrant_inspections] bi
              LEFT JOIN [apar].[dbo].[hydrants] h ON bi.hydrant_id = h.id
              LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
              WHERE MONTH(bi.inspection_date) = $bulan AND YEAR(bi.inspection_date) = $tahun
              ORDER BY bi.inspection_date DESC";
    $title = "LAPORAN INSPEKSI BIMONTHLY HYDRANT";
}

$stmt = sqlsrv_query($koneksi, $query);
$data = array();

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
}

// Generate XLSX menggunakan format XML sederhana
require_once __DIR__ . '/../../helper/XlsxWriter.php';
$writer = new XlsxWriter();

// Sheet 1: Data
$writer->writeSheetHeader('Report', array(
    'No' => 'integer',
    'Tanggal' => 'string',
    'Kode' => 'string',
    'Area' => 'string',
    'Lokasi' => 'string',
    'Exp. Date' => 'string',
    'Status' => 'string',
    'Inspector' => 'string',
    'Catatan' => 'string'
));

$no = 1;
foreach ($data as $row) {
    // Tentukan status
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
    foreach ($params as $p) {
        if ($p != 1) {
            $all_ok = false;
            break;
        }
    }
    
    $status = $all_ok ? 'OK' : 'ABNORMAL';
    
    // Format tanggal
    $tanggal = '-';
    if ($row['inspection_date'] !== null) {
        if ($row['inspection_date'] instanceof DateTime) {
            $tanggal = $row['inspection_date']->format('d/m/Y H:i');
        } else {
            $tanggal = (string)$row['inspection_date'];
        }
    }
    
    $exp_date = '-';
    if ($row['expired_date'] !== null) {
        if ($row['expired_date'] instanceof DateTime) {
            $exp_date = $row['expired_date']->format('d/m/Y');
        } else {
            $exp_date = (string)$row['expired_date'];
        }
    }
    
    try {
        $writer->writeSheetRow('Report', array(
            $no++,
            $tanggal,
            (string)$row['code'],
            (string)$row['area'],
            (string)$row['location'],
            $exp_date,
            $status,
            $row['inspector'] ?? '-',
            $row['notes'] ?? '-'
        ));
    } catch (Exception $e) {
        // Continue if row write fails
    }
}

$writer->writeToStdOut();
exit;
?>
