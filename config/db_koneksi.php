<?php

$hostname = gethostname();
// echo $hostname;
// die;
$serverName = "DESKTOP-1V8I9K6\SQLEXPRESS"; 

$database = "apar";
$username = "sa";
$password = "Saaccountalif123";

$connectionInfo = array(
    "Database" => $database,
    "UID" => $username,
    "PWD" => $password,
    "CharacterSet" => "UTF-8",
);


$koneksi = sqlsrv_connect($serverName, $connectionInfo);
// var_dump($koneksi);
// die;

//  $sql = "SELECT id, item_code, name, location_id, qty, created_at, updated_at FROM item_table";
//     $stmt = sqlsrv_query($koneksi, $sql);
//     $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

//     var_dump($row);die();


if (!$koneksi) {
    die(print_r(sqlsrv_errors(), true));
} 
