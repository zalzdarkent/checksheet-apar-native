<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

if (!class_exists('ZipArchive')) {
    die("Error: ZipArchive extension PHP tidak aktif.");
}

$type = $_GET['type'] ?? 'apar';
$year = date('Y');
$asset_type = strtoupper($type);

// Unified Query
$query = "SELECT 
            m.id as asset_id, m.asset_code as code, m.area, m.location,
            bi.inspection_date, bi.user_id, bi.notes, bi.*,
            ISNULL(e.EmployeeName, u.REALNAME) as inspector_name
          FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
          INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
          LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e ON bi.user_id = e.EmpID
          LEFT JOIN [ATI].[Users].[UserTable] u ON bi.user_id = u.EMPID
          WHERE m.asset_type = ? AND m.is_active = 1 AND YEAR(bi.inspection_date) = $year
          ORDER BY bi.inspection_date DESC";

$stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
$rows_data = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows_data[] = $row;
    }
}

$filename = "DATA_INSPEKSI_" . strtoupper($type) . "_" . $year;

// True XLSX helper
function generateXLSX($filename, $sheetName, $data)
{
    $temp = sys_get_temp_dir() . '/' . uniqid('xlsx');
    mkdir($temp);
    mkdir($temp . '/_rels');
    mkdir($temp . '/xl');
    mkdir($temp . '/xl/_rels');
    mkdir($temp . '/xl/worksheets');

    $strings = array();
    $s_map = array();
    foreach ($data as $r) {
        foreach ($r as $v) {
            $s = (string) $v;
            if (!isset($s_map[$s])) {
                $s_map[$s] = count($strings);
                $strings[] = $s;
            }
        }
    }

    $xml_strings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
    foreach ($strings as $s)
        $xml_strings .= '<si><t>' . htmlspecialchars($s) . '</t></si>';
    $xml_strings .= '</sst>';

    $xml_ws = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
    $xml_ws .= '<sheetData>';
    foreach ($data as $ri => $rv) {
        $xml_ws .= '<row r="' . ($ri + 1) . '">';
        foreach ($rv as $ci => $cv) {
            $col = "";
            $n = $ci;
            while ($n >= 0) {
                $col = chr($n % 26 + 65) . $col;
                $n = floor($n / 26) - 1;
            }
            $xml_ws .= '<c r="' . $col . ($ri + 1) . '" t="s"><v>' . $s_map[(string) $cv] . '</v></c>';
        }
        $xml_ws .= '</row>';
    }
    $xml_ws .= '</sheetData></worksheet>';

    file_put_contents($temp . '/[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
    file_put_contents($temp . '/_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    file_put_contents($temp . '/xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . htmlspecialchars($sheetName) . '" sheetId="1" r:id="rId1"/></sheets></workbook>');
    file_put_contents($temp . '/xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');
    file_put_contents($temp . '/xl/sharedStrings.xml', $xml_strings);
    file_put_contents($temp . '/xl/worksheets/sheet1.xml', $xml_ws);

    $zip = new ZipArchive();
    $zip_path = $temp . '.xlsx';
    if ($zip->open($zip_path, ZipArchive::CREATE)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $zip->addFile($filePath, substr($filePath, strlen(realpath($temp)) + 1));
        }
        $zip->close();
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    readfile($zip_path);
    unlink($zip_path);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file)
        ($file->isDir()) ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    rmdir($temp);
    exit;
}

$monthName = date('F');
$final_rows = array();
if ($type === 'apar') {
    $final_rows[] = array('LAPORAN INSPEKSI ' . strtoupper($type), '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('TAHUN ' . $year, '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('No', 'Tanggal', 'Kode', 'Area', 'Lokasi', 'Exp', 'Pressure', 'Hose', 'Status', 'Inspector', 'Catatan');
} else {
    $final_rows[] = array('LAPORAN INSPEKSI ' . strtoupper($type), '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('TAHUN ' . $year, '', '', '', '', '', '', '', '', '', '');
    $final_rows[] = array('No', 'Tanggal', 'Kode', 'Area', 'Lokasi', 'Body', 'Hose', 'Nozzle', 'Status', 'Inspector', 'Catatan');
}

$no = 1;
foreach ($rows_data as $row) {
    $d_row = array($no++);
    $d_row[] = ($row['inspection_date'] instanceof DateTime) ? $row['inspection_date']->format('d/m/Y H:i') : ($row['inspection_date'] ?? '-');
    $d_row[] = $row['code'];
    $d_row[] = $row['area'];
    $d_row[] = $row['location'];

    if ($type === 'apar') {
        $d_row[] = ($row['exp_date_ok'] == 1) ? 'OK' : 'NG';
        $d_row[] = ($row['pressure_ok'] == 1) ? 'OK' : 'NG';
        $d_row[] = ($row['hose_ok'] == 1) ? 'OK' : 'NG';
        $check_items = ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'];
    } else {
        $d_row[] = ($row['body_hydrant_ok'] == 1) ? 'OK' : 'NG';
        $d_row[] = ($row['selang_ok'] == 1) ? 'OK' : 'NG';
        $d_row[] = ($row['nozzle_ok'] == 1) ? 'OK' : 'NG';
        $check_items = ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
    }

    $all_ok = true;
    foreach ($check_items as $ci) {
        if (isset($row[$ci]) && $row[$ci] != 1) {
            $all_ok = false;
            break;
        }
    }
    $d_row[] = $all_ok ? 'OK' : 'ABNORMAL';
    $d_row[] = $row['inspector_name'] ?? '-';
    $d_row[] = $row['notes'] ?? '-';
    $final_rows[] = $d_row;
}

generateXLSX($filename, "Report", $final_rows);
