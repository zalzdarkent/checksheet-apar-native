<?php
include(__DIR__ . '/../../config/db_koneksi.php');

header('Content-Type: application/json');

$query = "
    SELECT 
        u.EMPID as empid, 
        ISNULL(e.EmployeeName, u.REALNAME) as name,
        (SELECT STRING_AGG(area_name, ',') FROM [PRD].[dbo].[SE_FIRE_PROTECTION_AREA] WHERE empid = u.EMPID AND asset_type = 'apar') as apar_locations,
        (SELECT STRING_AGG(area_name, ',') FROM [PRD].[dbo].[SE_FIRE_PROTECTION_AREA] WHERE empid = u.EMPID AND asset_type = 'hydrant') as hydrant_locations
    FROM [ATI].[Users].[UserTable] u
    LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e ON u.EMPID = e.EmpID
    WHERE u.CF_Active = 1
";

$stmt = sqlsrv_query($koneksi, $query);
$users = [];

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $users[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $users]);
?>