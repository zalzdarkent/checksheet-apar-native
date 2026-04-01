<?php
include 'config/db_koneksi.php';
$tables = ['apars', 'hydrants'];
foreach ($tables as $table) {
    echo "Columns for $table:\n";
    $sql = "SELECT TOP 1 * FROM [apar].[dbo].[$table]";
    $q = sqlsrv_query($koneksi, $sql);
    if ($q) {
        $r = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC);
        if ($r) {
            print_r(array_keys($r));
        } else {
            echo "No data in $table\n";
        }
    } else {
        print_r(sqlsrv_errors());
    }
}
?>
