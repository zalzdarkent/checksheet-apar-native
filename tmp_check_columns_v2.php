<?php
include 'config/db_koneksi.php';
$tables = ['apars', 'hydrants'];
foreach ($tables as $table) {
    echo "Columns for $table: ";
    $sql = "SELECT TOP 0 * FROM [apar].[dbo].[$table]";
    $res = sqlsrv_query($koneksi, $sql);
    $cols = [];
    foreach(sqlsrv_field_metadata($res) as $field) {
        $cols[] = $field['Name'];
    }
    echo implode(', ', $cols) . "\n\n";
}
?>
