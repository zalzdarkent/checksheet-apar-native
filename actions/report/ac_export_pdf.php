<?php
require_once __DIR__ . '/../../config/db_koneksi.php';
require_once __DIR__ . '/../../assets/vendor/dompdf/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$type = $_GET['type'] ?? 'apar';
$year = date('Y');
$asset_type = strtoupper($type);

$periods = array(
    1 => array('name' => 'I (Jan-Feb)', 'months' => array(1, 2)),
    2 => array('name' => 'II (Mar-Apr)', 'months' => array(3, 4)),
    3 => array('name' => 'III (Mei-Jun)', 'months' => array(5, 6)),
    4 => array('name' => 'IV (Jul-Agt)', 'months' => array(7, 8)),
    5 => array('name' => 'V (Sep-Okt)', 'months' => array(9, 10)),
    6 => array('name' => 'VI (Nov-Des)', 'months' => array(11, 12))
);

$query = "SELECT DISTINCT
            m.id, m.asset_code as code, m.area, m.location,
            bi.inspection_date, bi.*
          FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
          LEFT JOIN [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi 
            ON m.id = bi.asset_id AND YEAR(bi.inspection_date) = $year
          WHERE m.asset_type = ? AND m.is_active = 1
          ORDER BY m.area, m.location, m.asset_code";

$stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
$equipment_raw = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $equipment_raw[] = $row;
}

$title = "LAPORAN_PEMERIKSAAN_" . strtoupper($type) . "_BIMONTHLY_$year";
$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body { font-family: Arial, sans-serif; margin: 10px; font-size: 10px; }
    .header { text-align: center; margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; }
    th { background-color: #f2f2f2; padding: 5px; border: 1px solid #000; font-size: 9px; }
    td { padding: 5px; border: 1px solid #000; text-align: center; }
    .status-ok { color: green; font-weight: bold; }
    .status-abnormal { color: red; font-weight: bold; }
</style></head><body>
<div class="header">
    <h2>LAPORAN PEMERIKSAAN ' . strtoupper($type) . ' (PER 2 BULAN)</h2>
    <p>Tahun: ' . $year . '</p>
</div>
<table><thead><tr>
    <th rowspan="2" style="width:40px;">No</th>
    <th rowspan="2">Kode</th>
    <th rowspan="2">Area</th>
    <th rowspan="2">Lokasi</th>
    <th colspan="6">Periode Pemeriksaan</th>
</tr><tr>';
foreach ($periods as $p) $html .= "<th>{$p['name']}</th>";
$html .= '</tr></thead><tbody>';

$no = 1;
$equipment_data = array();
foreach ($equipment_raw as $item) {
    if (!isset($equipment_data[$item['id']])) {
        $equipment_data[$item['id']] = [
            'code' => $item['code'], 'area' => $item['area'], 'location' => $item['location'],
            'periods' => array_fill_keys(array_keys($periods), '')
        ];
    }
    if (!empty($item['inspection_date'])) {
        $month = $item['inspection_date']->format('n');
        foreach ($periods as $idx => $period) {
            if (in_array($month, $period['months'])) {
                $check_items = ($type === 'apar') ? 
                    ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
                    ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
                $all_ok = true;
                foreach ($check_items as $ci) { if (isset($item[$ci]) && $item[$ci] != 1) { $all_ok = false; break; } }
                $equipment_data[$item['id']]['periods'][$idx] = $all_ok ? 'OK' : 'NG';
                break;
            }
        }
    }
}

foreach ($equipment_data as $item) {
    $html .= "<tr><td>" . $no++ . "</td><td>{$item['code']}</td><td>{$item['area']}</td><td>{$item['location']}</td>";
    foreach ($periods as $idx => $p) {
        $st = $item['periods'][$idx];
        $cls = ($st === 'OK' ? 'status-ok' : ($st === 'NG' ? 'status-abnormal' : ''));
        $html .= "<td class='$cls'>$st</td>";
    }
    $html .= "</tr>";
}
$html .= '</tbody></table></body></html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream($title . ".pdf");
exit;
