<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

require_once __DIR__ . '/../../assets/vendor/dompdf/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$type = $_GET['type'] ?? 'apar';
$year = date('Y');

// Bimonthly periods
$periods = array(
    1 => array('name' => 'I (Jan-Feb)', 'months' => array(1, 2)),
    2 => array('name' => 'II (Mar-Apr)', 'months' => array(3, 4)),
    3 => array('name' => 'III (Mei-Jun)', 'months' => array(5, 6)),
    4 => array('name' => 'IV (Jul-Agt)', 'months' => array(7, 8)),
    5 => array('name' => 'V (Sep-Okt)', 'months' => array(9, 10)),
    6 => array('name' => 'VI (Nov-Des)', 'months' => array(11, 12))
);

if ($type === 'apar') {
    $query = "SELECT DISTINCT
                a.id, a.code, a.area, a.location,
                bi.inspection_date, bi.exp_date_ok, bi.pressure_ok, bi.weight_co2_ok,
                bi.tube_ok, bi.hose_ok, bi.bracket_ok, bi.wi_ok, bi.form_kejadian_ok,
                bi.sign_box_ok, bi.sign_triangle_ok, bi.marking_tiger_ok, bi.marking_beam_ok,
                bi.sr_apar_ok, bi.kocok_apar_ok, bi.label_ok
              FROM [apar].[dbo].[apars] a
              LEFT JOIN [apar].[dbo].[bimonthly_apar_inspections] bi 
                ON a.id = bi.apar_id AND YEAR(bi.inspection_date) = $year
              WHERE a.is_active = 1
              ORDER BY a.area, a.location, a.code";
    $title = "LAPORAN_PEMERIKSAAN_APAR_BIMONTHLY_$year";
} else {
    $query = "SELECT DISTINCT
                h.id, h.code, h.area, h.location,
                bi.inspection_date, bi.body_hydrant_ok, bi.selang_ok, bi.couple_join_ok,
                bi.nozzle_ok, bi.check_sheet_ok, bi.valve_kran_ok, bi.lampu_ok,
                bi.cover_lampu_ok, bi.box_display_ok, bi.konsul_hydrant_ok, bi.jr_ok,
                bi.marking_ok, bi.label_ok
              FROM [apar].[dbo].[hydrants] h
              LEFT JOIN [apar].[dbo].[bimonthly_hydrant_inspections] bi 
                ON h.id = bi.hydrant_id AND YEAR(bi.inspection_date) = $year
              WHERE h.is_active = 1
              ORDER BY h.area, h.location, h.code";
    $title = "LAPORAN_PEMERIKSAAN_HYDRANT_BIMONTHLY_$year";
}

$stmt = sqlsrv_query($koneksi, $query);
$equipment = array();

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $equipment[] = $row;
    }
}

// Generate HTML untuk PDF
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $title . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 10px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
        .header p { font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr th { 
            background-color: #e6e6e6; 
            color: black; 
            padding: 8px 5px; 
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
        }
        tbody td { 
            padding: 8px 4px; 
            border: 1px solid #000; 
            font-size: 10px;
            text-align: center;
            height: 25px;
        }
        tbody td:first-child { text-align: center; width: 4%; }
        tbody td:nth-child(2) { text-align: left; width: 12%; }
        tbody td:nth-child(3) { text-align: left; width: 12%; }
        tbody td:nth-child(4) { text-align: left; width: 20%; }
        .status-ok { 
            color: #28a745; 
            font-weight: bold;
        }
        .status-abnormal { 
            color: #dc3545; 
            font-weight: bold;
        }
        .empty { 
            color: #999;
        }
        @page { 
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . ($type === 'apar' ? 'LAPORAN PEMERIKSAAN APAR (PER 2 BULAN)' : 'LAPORAN PEMERIKSAAN HYDRANT (PER 2 BULAN)') . '</h1>
        <p>Tahun: ' . $year . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:4%;">No</th>
                <th rowspan="2" style="width:12%;">Kode ' . strtoupper($type) . '</th>
                <th rowspan="2" style="width:12%;">Area</th>
                <th rowspan="2" style="width:20%;">Lokasi</th>
                <th colspan="6">Periode Pemeriksaan</th>
            </tr>
            <tr>
                <th style="width:8%;">I (Jan-Feb)</th>
                <th style="width:8%;">II (Mar-Apr)</th>
                <th style="width:8%;">III (Mei-Jun)</th>
                <th style="width:8%;">IV (Jul-Agt)</th>
                <th style="width:8%;">V (Sep-Okt)</th>
                <th style="width:8%;">VI (Nov-Des)</th>
            </tr>
        </thead>
        <tbody>';

// Organize equipment data
$no = 1;
$equipment_data = array();

foreach ($equipment as $item) {
    $key = $item['id'];
    if (!isset($equipment_data[$key])) {
        $equipment_data[$key] = array(
            'code' => $item['code'],
            'area' => $item['area'],
            'location' => $item['location'],
            'periods' => array_fill_keys(array_keys($periods), '')
        );
    }
    
    if (!empty($item['inspection_date'])) {
        $month = $item['inspection_date'] instanceof DateTime ? 
                 $item['inspection_date']->format('n') : 
                 date('n', strtotime($item['inspection_date']));
        
        foreach ($periods as $idx => $period) {
            if (in_array($month, $period['months'])) {
                if ($type === 'apar') {
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
                
                $status = $all_ok ? 'OK' : 'ABNORMAL';
                $equipment_data[$key]['periods'][$idx] = $status;
                break;
            }
        }
    }
}

// Sort equipment data to put ones with inspections first
uasort($equipment_data, function($a, $b) {
    $a_has_data = count(array_filter($a['periods'])) > 0 ? 1 : 0;
    $b_has_data = count(array_filter($b['periods'])) > 0 ? 1 : 0;
    
    if ($a_has_data != $b_has_data) {
        return $b_has_data - $a_has_data;
    }
    
    $cmp = strcmp($a['area'], $b['area']);
    if ($cmp == 0) {
        $cmp = strcmp($a['location'], $b['location']);
        if ($cmp == 0) {
            $cmp = strcmp($a['code'], $b['code']);
        }
    }
    return $cmp;
});

// Generate table rows
foreach ($equipment_data as $item) {
    $html .= '<tr>';
    $html .= '<td>' . $no++ . '</td>';
    $html .= '<td>' . htmlspecialchars($item['code']) . '</td>';
    $html .= '<td>' . htmlspecialchars($item['area']) . '</td>';
    $html .= '<td>' . htmlspecialchars($item['location']) . '</td>';
    
    foreach ($periods as $idx => $period) {
        $status = $item['periods'][$idx];
        $class = '';
        if ($status === 'OK') {
            $class = 'status-ok';
        } elseif ($status === 'ABNORMAL') {
            $class = 'status-abnormal';
        } else {
            $class = 'empty';
        }
        $html .= '<td class="' . $class . '">' . htmlspecialchars($status) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Generate PDF using DOMPDF
try {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    // Output PDF
    $filename = ($type === 'apar' ? 'LAPORAN_PEMERIKSAAN_APAR_BIMONTHLY' : 'LAPORAN_PEMERIKSAAN_HYDRANT_BIMONTHLY') . '_' . $year . '.pdf';
    $dompdf->stream($filename);
} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h2>Error generating PDF:</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Make sure DOMPDF is installed: <code>composer require dompdf/dompdf</code></p>';
}
exit;
