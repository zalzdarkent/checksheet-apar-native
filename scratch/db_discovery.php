<?php
$serverName = "DESKTOP-1V8I9K6\SQLEXPRESS";
$connectionInfo = array("Database" => "master", "UID" => "sa", "PWD" => "Saaccountalif123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo "Databases:\n";
$sql = "SELECT name FROM sys.databases";
$stmt = sqlsrv_query($conn, $sql);
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "- " . $row['name'] . "\n";
}

echo "\nTables in ATI:\n";
$sql = "SELECT SCHEMA_NAME(schema_id) as SchemaName, name FROM ATI.sys.tables";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "- " . $row['SchemaName'] . "." . $row['name'] . "\n";
    }
}

echo "\nTables in PRD:\n";
$sql = "SELECT SCHEMA_NAME(schema_id) as SchemaName, name FROM PRD.sys.tables";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "- " . $row['SchemaName'] . "." . $row['name'] . "\n";
    }
}
?>