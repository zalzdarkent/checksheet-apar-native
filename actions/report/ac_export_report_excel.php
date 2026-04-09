<?php
require_once __DIR__ . '/../../config/db_koneksi.php';
require_once __DIR__ . '/../../helper/XlsxWriter.php';

$type = $_GET['type'] ?? 'apar';
$bulan = date('m');
$tahun = date('Y');
$asset_type = strtoupper($type);

$query = "SELECT TOP 100
            bi.id, bi.inspection_date, m.asset_code as code, m.area, m.location, m.expired_date,
            bi.*, ISNULL(e.EmployeeName, u.REALNAME) as inspector
          FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
          INNER JOIN [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
          LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON bi.user_id = e.EmpID
          LEFT JOIN [apar].[Users].[UserTable] u ON bi.user_id = u.EMPID
          WHERE MONTH(bi.inspection_date) = $bulan AND YEAR(bi.inspection_date) = $tahun
          AND m.asset_type = ? AND m.is_active = 1
          ORDER BY bi.inspection_date DESC";

$stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
$data = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $data[] = $row;
}

$writer = new XlsxWriter();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Report_' . strtoupper($type) . '_' . date('Y-m-d') . '.xlsx"');

$writer->writeSheetHeader('Report', array('No' => 'integer', 'Tanggal' => 'string', 'Kode' => 'string', 'Area' => 'string', 'Lokasi' => 'string', 'Exp. Date' => 'string', 'Status' => 'string', 'Inspector' => 'string', 'Catatan' => 'string'));

$no = 1;
foreach ($data as $row) {
    $check_items = ($type === 'apar') ? 
        ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
        ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
    
    $all_ok = true;
    foreach ($check_items as $ci) { if (isset($row[$ci]) && $row[$ci] != 1) { $all_ok = false; break; } }
    
    $writer->writeSheetRow('Report', array(
        $no++,
        ($row['inspection_date'] instanceof DateTime ? $row['inspection_date']->format('d/m/Y H:i') : '-'),
        (string)$row['code'], (string)$row['area'], (string)$row['location'],
        ($row['expired_date'] instanceof DateTime ? $row['expired_date']->format('d/m/Y') : '-'),
        ($all_ok ? 'OK' : 'ABNORMAL'),
        $row['inspector'] ?? '-',
        $row['notes'] ?? '-'
    ));
}
$writer->writeToStdOut();
exit;
