<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

$type = $_GET['type'] ?? 'apar';
$year = date('Y');
$asset_type = strtoupper($type);

$query = "SELECT 
            m.id, m.asset_code as code, m.area, m.location, m.weight, m.expired_date as master_exp_date,
            bi.inspection_date, bi.user_id, bi.notes, bi.*,
            ISNULL(e.EmployeeName, u.REALNAME) as inspector_name
          FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m
          LEFT JOIN [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi 
            ON m.id = bi.asset_id AND YEAR(bi.inspection_date) = $year
          LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON bi.user_id = e.EmpID
          LEFT JOIN [apar].[Users].[UserTable] u ON bi.user_id = u.EMPID
          WHERE m.asset_type = ? AND m.is_active = 1
          ORDER BY m.area, m.location, m.asset_code, bi.inspection_date";

$title = "DATA_INSPEKSI_" . strtoupper($type) . "_" . $year;
$stmt = sqlsrv_query($koneksi, $query, [$asset_type]);
$data = array();
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) $data[] = $row;
}

// Minimal SimpleXLSX class
class SimpleXLSX {
    private $sheets = array();
    private $strings = array();
    private $string_map = array();
    public function addSheet($name, $data) { $this->sheets[] = array('name' => $name, 'data' => $data); }
    private function addString($s) {
        if (!isset($this->string_map[$s])) {
            $this->string_map[$s] = count($this->strings);
            $this->strings[] = $s;
        }
        return $this->string_map[$s];
    }
    private function createSharedStringsXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($this->strings) . '" uniqueCount="' . count($this->strings) . '">';
        foreach ($this->strings as $str) $xml .= '<si><t>' . htmlspecialchars($str) . '</t></si>';
        return $xml . '</sst>';
    }
    private function createWorksheetXml($sheetIndex, $data) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $row_num = 1;
        foreach ($data as $row_data) {
            $xml .= '<row r="' . $row_num . '">';
            $col_idx = 0;
            foreach ($row_data as $cell_value) {
                $ref = $this->num2alpha($col_idx++) . $row_num;
                if (is_null($cell_value) || $cell_value === '') $xml .= '<c r="' . $ref . '"/>';
                elseif (is_numeric($cell_value) && !is_string($cell_value)) $xml .= '<c r="' . $ref . '" t="n"><v>' . $cell_value . '</v></c>';
                else $xml .= '<c r="' . $ref . '" t="s"><v>' . $this->addString((string)$cell_value) . '</v></c>';
            }
            $xml .= '</row>'; $row_num++;
        }
        return $xml . '</sheetData></worksheet>';
    }
    private function num2alpha($n) {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) $r = chr($n % 26 + 0x41) . $r;
        return $r;
    }
    public function output($filename) {
        $temp_dir = sys_get_temp_dir() . '/' . uniqid('xlsx_');
        mkdir($temp_dir); mkdir($temp_dir . '/_rels'); mkdir($temp_dir . '/xl'); mkdir($temp_dir . '/xl/worksheets'); mkdir($temp_dir . '/xl/_rels');
        file_put_contents($temp_dir . '/[Content_Types].xml', $this->ctXml());
        file_put_contents($temp_dir . '/_rels/.rels', $this->relXml());
        file_put_contents($temp_dir . '/xl/workbook.xml', $this->wbXml());
        file_put_contents($temp_dir . '/xl/_rels/workbook.xml.rels', $this->wbrXml());
        file_put_contents($temp_dir . '/xl/sharedStrings.xml', $this->createSharedStringsXml());
        foreach ($this->sheets as $i => $sheet) file_put_contents($temp_dir . '/xl/worksheets/sheet' . ($i + 1) . '.xml', $this->createWorksheetXml($i, $sheet['data']));
        $zip = new ZipArchive(); $zip_file = sys_get_temp_dir() . '/' . $filename . '.xlsx';
        if ($zip->open($zip_file, ZipArchive::CREATE) === true) { $this->addDir($zip, $temp_dir, ''); $zip->close(); }
        $this->rmdir($temp_dir); return $zip_file;
    }
    private function ctXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        foreach ($this->sheets as $i => $s) $xml .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        return $xml . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>';
    }
    private function wbXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        foreach ($this->sheets as $i => $s) $xml .= '<sheet name="' . htmlspecialchars($s['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        return $xml . '</sheets></workbook>';
    }
    private function wbrXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->sheets as $i => $s) $xml .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        return $xml . '<Relationship Id="rId' . (count($this->sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>';
    }
    private function relXml() { return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>'; }
    private function addDir(&$zip, $dir, $path) {
        foreach (scandir($dir) as $f) { if ($f != '.' && $f != '..') { if (is_dir($dir . '/' . $f)) $this->addDir($zip, $dir . '/' . $f, $path . $f . '/'); else $zip->addFile($dir . '/' . $f, $path . $f); } }
    }
    private function rmdir($dir) {
        foreach (scandir($dir) as $f) { if ($f != '.' && $f != '..') { if (is_dir($dir . '/' . $f)) $this->rmdir($dir . '/' . $f); else unlink($dir . '/' . $f); } }
        rmdir($dir);
    }
}

// Data Preparation
$sheet_data = [];
if ($type === 'apar') {
    $sheet_data[] = ['No', 'Tanggal', 'Kode APAR', 'Area', 'Lokasi', 'Exp. Date', 'Exp OK', 'Pres OK', 'Wght OK', 'Tube OK', 'Hose OK', 'Brkt OK', 'WI OK', 'Form OK', 'SgnB OK', 'SgnT OK', 'MrkT OK', 'MrkB OK', '5R OK', 'Kock OK', 'Lbl OK', 'Status', 'Inspector', 'Notes'];
} else {
    $sheet_data[] = ['No', 'Tanggal', 'Kode Hydrant', 'Area', 'Lokasi', 'Body OK', 'Selang OK', 'Cpl OK', 'Nozl OK', 'Chks OK', 'Vlv OK', 'Lmp OK', 'CvrL OK', 'Box OK', 'Kon OK', 'JR OK', 'Mrk OK', 'Lbl OK', 'Status', 'Inspector', 'Notes'];
}

$no = 1;
foreach ($data as $row) {
    $row_out = [$no++, ($row['inspection_date'] instanceof DateTime ? $row['inspection_date']->format('Y-m-d') : ''), $row['code'], $row['area'], $row['location']];
    if ($type === 'apar') {
        $row_out[] = ($row['master_exp_date'] instanceof DateTime ? $row['master_exp_date']->format('Y-m-d') : '');
        $items = ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'];
    } else {
        $items = ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
    }
    $all_ok = true;
    foreach ($items as $it) {
        $val = $row[$it] ?? '';
        $row_out[] = ($val === 1 ? 'OK' : ($val === 0 ? 'NG' : '-'));
        if ($val === 0) $all_ok = false;
    }
    $row_out[] = (!isset($row['inspection_date']) ? '-' : ($all_ok ? 'OK' : 'ABNORMAL'));
    $row_out[] = $row['inspector_name'] ?? '-';
    $row_out[] = $row['notes'] ?? '';
    $sheet_data[] = $row_out;
}

$xlsx = new SimpleXLSX();
$xlsx->addSheet('Inspeksi', $sheet_data);
$xlsx_file = $xlsx->output($title);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $title . '.xlsx"');
header('Content-Length: ' . filesize($xlsx_file));
readfile($xlsx_file); unlink($xlsx_file); exit;
