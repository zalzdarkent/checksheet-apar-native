<?php
$serverName = "DESKTOP-1V8I9K6\SQLEXPRESS";
$connectionInfo = array("Database" => "PRD", "UID" => "sa", "PWD" => "Saaccountalif123");
$koneksi = sqlsrv_connect($serverName, $connectionInfo);
if (!$koneksi) {
    echo "Connection to PRD failed:\n";
    print_r(sqlsrv_errors());
} else {
    echo "Connection to PRD SUCCEEDED.\n";
}

$connectionInfo = array("Database" => "ATI", "UID" => "sa", "PWD" => "Saaccountalif123");
$koneksi = sqlsrv_connect($serverName, $connectionInfo);
if (!$koneksi) {
    echo "\nConnection to ATI failed:\n";
    print_r(sqlsrv_errors());
} else {
    echo "Connection to ATI SUCCEEDED.\n";
}
?>