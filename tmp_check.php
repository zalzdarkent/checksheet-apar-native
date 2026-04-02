<?php
require 'c:\laragon\www\apar\config\db_koneksi.php';
$year = date('Y');
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

$stmt = sqlsrv_query($koneksi, $query);
if ($stmt === false) {
    die("ERROR: " . print_r(sqlsrv_errors(), true));
}

$rows = 0;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rows++;
}
echo "Total hydrant rows: $rows\n";
