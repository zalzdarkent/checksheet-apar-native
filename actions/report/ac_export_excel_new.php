<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

// Check for ZipArchive
if (!class_exists('ZipArchive')) {
    die("Error: ZipArchive extension PHP tidak aktif. Silakan aktifkan di php.ini");
}

$type = $_GET['type'] ?? 'apar';
$year = date('Y');

if ($type === 'apar') {
    $query = "SELECT 
                a.id as apar_id_master, a.code, a.area, a.location,
                ISNULL(a.expired_date, '') as exp_date,
                bi.id as inspection_id,
                bi.inspection_date, bi.user_id,
                bi.exp_date_ok, bi.pressure_ok, bi.weight_co2_ok,
                bi.tube_ok, bi.hose_ok, bi.bracket_ok, bi.wi_ok, bi.form_kejadian_ok,
                bi.sign_box_ok, bi.sign_triangle_ok, bi.marking_tiger_ok, bi.marking_beam_ok,
                bi.sr_apar_ok, bi.kocok_apar_ok, bi.label_ok, bi.notes,
                u.name
              FROM [apar].[dbo].[apars] a
              INNER JOIN [apar].[dbo].[bimonthly_apar_inspections] bi ON a.id = bi.apar_id
              LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
              WHERE a.is_active = 1 AND YEAR(bi.inspection_date) = $year
              ORDER BY bi.inspection_date DESC";
    $filename = "DATA_INSPEKSI_APAR_" . $year;
} else {
    $query = "SELECT 
                h.id as hydrant_id_master, h.code, h.area, h.location,
                bi.id as inspection_id,
                bi.inspection_date, bi.user_id,
                bi.body_hydrant_ok, bi.selang_ok, bi.couple_join_ok,
                bi.nozzle_ok, bi.check_sheet_ok, bi.valve_kran_ok, bi.lampu_ok,
                bi.cover_lampu_ok, bi.box_display_ok, bi.konsul_hydrant_ok, bi.jr_ok,
                bi.marking_ok, bi.label_ok, bi.notes,
                u.name
              FROM [apar].[dbo].[hydrants] h
              INNER JOIN [apar].[dbo].[bimonthly_hydrant_inspections] bi ON h.id = bi.hydrant_id
              LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
              WHERE h.is_active = 1 AND YEAR(bi.inspection_date) = $year
              ORDER BY bi.inspection_date DESC";
    $filename = "DATA_INSPEKSI_HYDRANT_" . $year;
}

$stmt = sqlsrv_query($koneksi, $query);
$rows_data = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Hapus filter ini agar semua data muncul
        $rows_data[] = $row;
    }
}

// Generate TRUE XLSX (OpenXML)
function generateXLSX($filename, $sheetName, $data) {
    $temp = sys_get_temp_dir() . '/' . uniqid('xlsx');
    mkdir($temp); mkdir($temp.'/_rels'); mkdir($temp.'/xl'); mkdir($temp.'/xl/_rels'); mkdir($temp.'/xl/worksheets');
    
    $strings = array(); $s_map = array();
    foreach($data as $r) {
        foreach($r as $v) {
            $s = (string)$v;
            if(!isset($s_map[$s])) { $s_map[$s] = count($strings); $strings[] = $s; }
        }
    }

    $xml_strings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">';
    foreach($strings as $s) $xml_strings .= '<si><t>'.htmlspecialchars($s).'</t></si>';
    $xml_strings .= '</sst>';
    
    $xml_ws = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
    $xml_ws .= '<mergeCells count="2"><mergeCell ref="A1:L1"/><mergeCell ref="A2:L2"/></mergeCells>';
    $xml_ws .= '<sheetData>';
    foreach($data as $ri => $rv) {
        $xml_ws .= '<row r="'.($ri+1).'">';
        foreach($rv as $ci => $cv) {
            $col = ""; $n = $ci; while($n >= 0) { $col = chr($n % 26 + 65) . $col; $n = floor($n/26)-1; }
            $xml_ws .= '<c r="'.$col.($ri+1).'" t="s"><v>'.$s_map[(string)$cv].'</v></c>';
        }
        $xml_ws .= '</row>';
    }
    $xml_ws .= '</sheetData></worksheet>';

    file_put_contents($temp.'/[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
    file_put_contents($temp.'/_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    file_put_contents($temp.'/xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.htmlspecialchars($sheetName).'" sheetId="1" r:id="rId1"/></sheets></workbook>');
    file_put_contents($temp.'/xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');
    file_put_contents($temp.'/xl/sharedStrings.xml', $xml_strings);
    file_put_contents($temp.'/xl/worksheets/sheet1.xml', $xml_ws);

    $zip = new ZipArchive();
    $zip_path = $temp.'.xlsx';
    if($zip->open($zip_path, ZipArchive::CREATE)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(realpath($temp)) + 1);
            $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
        }
        $zip->close();
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
    readfile($zip_path);
    unlink($zip_path);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) ($file->isDir()) ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    rmdir($temp);
    exit;
}

$monthName = date('F');
if ($type === 'apar') {
    $final_rows[] = array('LAPORAN INSPEKSI BIMONTHLY APAR', '', '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('TAHUN ' . $year . ' - BULAN ' . $monthName, '', '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('No', 'Tanggal', 'Kode APAR', 'Area', 'Lokasi', 'Exp. Date', 'Pressure', 'Selang', 'Status Akhir', 'Inspektor', 'Catatan');
} else {
    $final_rows[] = array('LAPORAN INSPEKSI BIMONTHLY HYDRANT', '', '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('TAHUN ' . $year . ' - BULAN ' . $monthName, '', '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('No', 'Tanggal', 'Kode Hydrant', 'Area', 'Lokasi', 'Body', 'Selang', 'Nozzle', 'Status Akhir', 'Inspektor', 'Catatan');
}

$no = 1;
foreach ($rows_data as $row) {
    if ($type === 'apar') {
        // Map according to your screenshot columns: No | Tanggal | Kode | Area | Lokasi | Exp. Date | Pressure | Selang | Status Akhir | Inspektor | Catatan
        $d_row = array($no++);
        $d_row[] = ($row['inspection_date'] instanceof DateTime) ? $row['inspection_date']->format('d/m/Y H:i') : ($row['inspection_date'] ?? '-');
        $d_row[] = $row['code'];
        $d_row[] = $row['area'];
        $d_row[] = $row['location'];
        $d_row[] = ($row['exp_date_ok'] == 1) ? 'OK' : 'ABNORMAL';
        $d_row[] = ($row['pressure_ok'] == 1) ? 'OK' : 'ABNORMAL';
        $d_row[] = ($row['hose_ok'] == 1) ? 'OK' : 'ABNORMAL';
        
        // Status Akhir logic
        $all_ok = ($row['exp_date_ok'] == 1 && $row['pressure_ok'] == 1 && $row['hose_ok'] == 1 && $row['tube_ok'] == 1 && $row['bracket_ok'] == 1); // simplified for layout
        $d_row[] = $all_ok ? 'OK' : 'ABNORMAL';
        
        $d_row[] = $row['name'] ?? '-';
        $d_row[] = $row['notes'] ?? '-';
    } else {
        $d_row = array($no++);
        $d_row[] = ($row['inspection_date'] instanceof DateTime) ? $row['inspection_date']->format('d/m/Y H:i') : ($row['inspection_date'] ?? '-');
        $d_row[] = $row['code'];
        $d_row[] = $row['area'];
        $d_row[] = $row['location'];
        $d_row[] = ($row['body_hydrant_ok'] == 1) ? 'OK' : 'ABNORMAL';
        $d_row[] = ($row['selang_ok'] == 1) ? 'OK' : 'ABNORMAL';
        $d_row[] = ($row['nozzle_ok'] == 1) ? 'OK' : 'ABNORMAL';
        
        $all_ok = ($row['body_hydrant_ok'] == 1 && $row['selang_ok'] == 1 && $row['nozzle_ok'] == 1);
        $d_row[] = $all_ok ? 'OK' : 'ABNORMAL';
        
        $d_row[] = $row['name'] ?? '-';
        $d_row[] = $row['notes'] ?? '-';
    }
    $final_rows[] = $d_row;
}

generateXLSX($filename, "Report", $final_rows);
