<?php
$serverName = "DESKTOP-1V8I9K6\SQLEXPRESS";
$connectionInfo = array("UID" => "sa", "PWD" => "Saaccountalif123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo "Testing Databases...\n";
$dbs = ['ATI', 'PRD', 'apar'];
foreach ($dbs as $db) {
    $res = sqlsrv_query($conn, "SELECT name FROM sys.databases WHERE name = '$db'");
    if ($res && sqlsrv_fetch_array($res)) {
        echo "- Database '$db' EXISTS.\n";

        // List tables
        $tables = sqlsrv_query($conn, "SELECT SCHEMA_NAME(schema_id) as s, name FROM [$db].sys.tables");
        if ($tables) {
            echo "  Tables in $db:\n";
            while ($t = sqlsrv_fetch_array($tables, SQLSRV_FETCH_ASSOC)) {
                echo "    * " . $t['s'] . "." . $t['name'] . "\n";
            }
        }
    } else {
        echo "- Database '$db' NOT FOUND.\n";
    }
}

// Fetch users from ATI
$sql = "SELECT EMPID, EmployeeName FROM [ATI].[Users].[UserTable]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    echo "\nUsers in [ATI].[Users].[UserTable]:\n";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo " - " . $row['EMPID'] . ": " . $row['EmployeeName'] . "\n";
    }
}

// Check HRD_EMPLOYEE_TABLE
$sql = "SELECT EmpID, EmployeeName FROM [ATI].[dbo].[HRD_EMPLOYEE_TABLE]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    echo "\nEmployees in [ATI].[dbo].[HRD_EMPLOYEE_TABLE]:\n";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo " - " . $row['EmpID'] . ": " . $row['EmployeeName'] . "\n";
    }
}
?>