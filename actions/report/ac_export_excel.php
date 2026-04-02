<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

$type = $_GET['type'] ?? 'apar';
$year = date('Y');

if ($type === 'apar') {
    $query = "SELECT 
                a.id, a.code, a.area, a.location,
                ISNULL(a.exp_date, '') as exp_date,
                bi.inspection_date, bi.user_id,
                bi.exp_date_ok, bi.pressure_ok, bi.weight_co2_ok,
                bi.tube_ok, bi.hose_ok, bi.bracket_ok, bi.wi_ok, bi.form_kejadian_ok,
                bi.sign_box_ok, bi.sign_triangle_ok, bi.marking_tiger_ok, bi.marking_beam_ok,
                bi.sr_apar_ok, bi.kocok_apar_ok, bi.label_ok, bi.notes,
                u.full_name
              FROM [apar].[dbo].[apars] a
              LEFT JOIN [apar].[dbo].[bimonthly_apar_inspections] bi 
                ON a.id = bi.apar_id AND YEAR(bi.inspection_date) = $year
              LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
              WHERE a.is_active = 1
              ORDER BY a.area, a.location, a.code, bi.inspection_date";
    $title = "DATA_INSPEKSI_APAR_" . $year;
} else {
    $query = "SELECT 
                h.id, h.code, h.area, h.location,
                bi.inspection_date, bi.user_id,
                bi.body_hydrant_ok, bi.selang_ok, bi.couple_join_ok,
                bi.nozzle_ok, bi.check_sheet_ok, bi.valve_kran_ok, bi.lampu_ok,
                bi.cover_lampu_ok, bi.box_display_ok, bi.konsul_hydrant_ok, bi.jr_ok,
                bi.marking_ok, bi.label_ok, bi.notes,
                u.full_name
              FROM [apar].[dbo].[hydrants] h
              LEFT JOIN [apar].[dbo].[bimonthly_hydrant_inspections] bi 
                ON h.id = bi.hydrant_id AND YEAR(bi.inspection_date) = $year
              LEFT JOIN [apar].[dbo].[users] u ON bi.user_id = u.id
              WHERE h.is_active = 1
              ORDER BY h.area, h.location, h.code, bi.inspection_date";
    $title = "DATA_INSPEKSI_HYDRANT_" . $year;
}

$stmt = sqlsrv_query($koneksi, $query);
$data = array();

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
}

// Create XLSX file
class SimpleXLSX {
    private $sheets = array();
    private $strings = array();
    private $string_map = array();
    
    public function addSheet($name, $data) {
        $this->sheets[] = array('name' => $name, 'data' => $data);
    }
    
    private function addString($s) {
        if (!isset($this->string_map[$s])) {
            $this->string_map[$s] = count($this->strings);
            $this->strings[] = $s;
        }
        return $this->string_map[$s];
    }
    
    private function createSharedStringsXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($this->strings) . '" uniqueCount="' . count($this->strings) . '">' . "\n";
        foreach ($this->strings as $str) {
            $xml .= '<si><t>' . htmlspecialchars($str) . '</t></si>' . "\n";
        }
        $xml .= '</sst>';
        return $xml;
    }
    
    private function createWorksheetXml($sheetIndex, $data) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '<sheetData>' . "\n";
        
        $row_num = 1;
        foreach ($data as $row_data) {
            $xml .= '<row r="' . $row_num . '">' . "\n";
            $col_letter = 'A';
            
            foreach ($row_data as $cell_value) {
                $cell_ref = $col_letter . $row_num;
                
                if (is_null($cell_value) || $cell_value === '') {
                    $xml .= '<c r="' . $cell_ref . '"/>' . "\n";
                } elseif (is_numeric($cell_value) && strpos($cell_value, '.') === false) {
                    $xml .= '<c r="' . $cell_ref . '" t="n"><v>' . (int)$cell_value . '</v></c>' . "\n";
                } else {
                    $string_idx = $this->addString($cell_value);
                    $xml .= '<c r="' . $cell_ref . '" t="s"><v>' . $string_idx . '</v></c>' . "\n";
                }
                
                $col_letter++;
            }
            
            $xml .= '</row>' . "\n";
            $row_num++;
        }
        
        $xml .= '</sheetData>' . "\n";
        $xml .= '</worksheet>';
        return $xml;
    }
    
    private function createContentTypesXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n";
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n";
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>' . "\n";
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n";
        
        foreach ($this->sheets as $i => $sheet) {
            $xml .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        }
        
        $xml .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' . "\n";
        $xml .= '</Types>';
        return $xml;
    }
    
    private function createWorkbookXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '<sheets>' . "\n";
        
        foreach ($this->sheets as $i => $sheet) {
            $xml .= '<sheet name="' . htmlspecialchars($sheet['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>' . "\n";
        }
        
        $xml .= '</sheets>' . "\n";
        $xml .= '</workbook>';
        return $xml;
    }
    
    private function createWorkbookRelsXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        
        foreach ($this->sheets as $i => $sheet) {
            $xml .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>' . "\n";
        }
        
        $xml .= '<Relationship Id="rId' . (count($this->sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' . "\n";
        $xml .= '</Relationships>';
        return $xml;
    }
    
    private function createRootRelsXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        $xml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n";
        $xml .= '</Relationships>';
        return $xml;
    }
    
    public function output($filename) {
        $temp_dir = sys_get_temp_dir() . '/' . uniqid('xlsx_');
        mkdir($temp_dir);
        mkdir($temp_dir . '/_rels');
        mkdir($temp_dir . '/xl');
        mkdir($temp_dir . '/xl/worksheets');
        
        // Create files
        file_put_contents($temp_dir . '/[Content_Types].xml', $this->createContentTypesXml());
        file_put_contents($temp_dir . '/_rels/.rels', $this->createRootRelsXml());
        file_put_contents($temp_dir . '/xl/workbook.xml', $this->createWorkbookXml());
        file_put_contents($temp_dir . '/xl/_rels/workbook.xml.rels', $this->createWorkbookRelsXml());
        file_put_contents($temp_dir . '/xl/sharedStrings.xml', $this->createSharedStringsXml());
        
        foreach ($this->sheets as $i => $sheet) {
            file_put_contents($temp_dir . '/xl/worksheets/sheet' . ($i + 1) . '.xml', $this->createWorksheetXml($i, $sheet['data']));
        }
        
        // Create ZIP
        $zip = new ZipArchive();
        $zip_file = sys_get_temp_dir() . '/' . $filename . '.xlsx';
        
        if ($zip->open($zip_file, ZipArchive::CREATE) === true) {
            $this->addFilesFromDir($zip, $temp_dir, '');
            $zip->close();
        }
        
        // Clean temp
        $this->rmdir($temp_dir);
        
        return $zip_file;
    }
    
    private function addFilesFromDir(&$zip, $dir, $path) {
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($dir . '/' . $file)) {
                    $this->addFilesFromDir($zip, $dir . '/' . $file, $path . $file . '/');
                } else {
                    $zip->addFile($dir . '/' . $file, $path . $file);
                }
            }
        }
        closedir($handle);
    }
    
    private function rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->rmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}

// Prepare data
$sheet_data = array();

// Headers
if ($type === 'apar') {
    $headers = array('No', 'Tanggal', 'Kode APAR', 'Area', 'Lokasi', 'Exp. Date', 
                     'Exp Date OK', 'Pressure OK', 'Weight CO2 OK', 'Tube OK', 'Hose OK', 
                     'Bracket OK', 'WI OK', 'Form Kejadian OK', 'Sign Box OK', 'Sign Triangle OK',
                     'Marking Tiger OK', 'Marking Beam OK', 'SR APAR OK', 'Kocok APAR OK', 'Label OK',
                     'Status', 'Inspector', 'Notes');
} else {
    $headers = array('No', 'Tanggal', 'Kode Hydrant', 'Area', 'Lokasi',
                     'Body Hydrant OK', 'Selang OK', 'Couple/Join OK', 'Nozzle OK', 'Check Sheet OK',
                     'Valve/Kran OK', 'Lampu OK', 'Cover Lampu OK', 'Box Display OK', 'Konsul Hydrant OK',
                     'JR OK', 'Marking OK', 'Label OK', 'Status', 'Inspector', 'Notes');
}

$sheet_data[] = $headers;

// Data rows
$no = 1;
foreach ($data as $row) {
    $row_data = array();
    $row_data[] = $no++;
    
    $inspection_date = !empty($row['inspection_date']) ? 
        ($row['inspection_date'] instanceof DateTime ? 
            $row['inspection_date']->format('Y-m-d') : 
            date('Y-m-d', strtotime($row['inspection_date']))) : '';
    $row_data[] = $inspection_date;
    
    $row_data[] = $row['code'];
    $row_data[] = $row['area'];
    $row_data[] = $row['location'];
    
    if ($type === 'apar') {
        $row_data[] = $row['exp_date'] ?? '';
        $row_data[] = $row['exp_date_ok'] ?? '';
        $row_data[] = $row['pressure_ok'] ?? '';
        $row_data[] = $row['weight_co2_ok'] ?? '';
        $row_data[] = $row['tube_ok'] ?? '';
        $row_data[] = $row['hose_ok'] ?? '';
        $row_data[] = $row['bracket_ok'] ?? '';
        $row_data[] = $row['wi_ok'] ?? '';
        $row_data[] = $row['form_kejadian_ok'] ?? '';
        $row_data[] = $row['sign_box_ok'] ?? '';
        $row_data[] = $row['sign_triangle_ok'] ?? '';
        $row_data[] = $row['marking_tiger_ok'] ?? '';
        $row_data[] = $row['marking_beam_ok'] ?? '';
        $row_data[] = $row['sr_apar_ok'] ?? '';
        $row_data[] = $row['kocok_apar_ok'] ?? '';
        $row_data[] = $row['label_ok'] ?? '';
        
        $params = array(
            $row['exp_date_ok'], $row['pressure_ok'], $row['weight_co2_ok'],
            $row['tube_ok'], $row['hose_ok'], $row['bracket_ok'], $row['wi_ok'],
            $row['form_kejadian_ok'], $row['sign_box_ok'], $row['sign_triangle_ok'],
            $row['marking_tiger_ok'], $row['marking_beam_ok'], $row['sr_apar_ok'],
            $row['kocok_apar_ok'], $row['label_ok']
        );
    } else {
        $row_data[] = $row['body_hydrant_ok'] ?? '';
        $row_data[] = $row['selang_ok'] ?? '';
        $row_data[] = $row['couple_join_ok'] ?? '';
        $row_data[] = $row['nozzle_ok'] ?? '';
        $row_data[] = $row['check_sheet_ok'] ?? '';
        $row_data[] = $row['valve_kran_ok'] ?? '';
        $row_data[] = $row['lampu_ok'] ?? '';
        $row_data[] = $row['cover_lampu_ok'] ?? '';
        $row_data[] = $row['box_display_ok'] ?? '';
        $row_data[] = $row['konsul_hydrant_ok'] ?? '';
        $row_data[] = $row['jr_ok'] ?? '';
        $row_data[] = $row['marking_ok'] ?? '';
        $row_data[] = $row['label_ok'] ?? '';
        
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
    
    $row_data[] = $all_ok ? 'OK' : 'ABNORMAL';
    $row_data[] = $row['full_name'] ?? '';
    $row_data[] = $row['notes'] ?? '';
    
    $sheet_data[] = $row_data;
}

// Generate XLSX
$xlsx = new SimpleXLSX();
$xlsx->addSheet('Inspeksi ' . strtoupper($type), $sheet_data);

$xlsx_file = $xlsx->output($title);

// Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $title . '.xlsx"');
header('Content-Length: ' . filesize($xlsx_file));

readfile($xlsx_file);
unlink($xlsx_file);
exit;
