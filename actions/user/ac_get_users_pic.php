<?php
include(__DIR__ . '/../../config/db_koneksi.php');

header('Content-Type: application/json');

$query = "
    SELECT 
        u.EMPID as empid, 
        ISNULL(e.EmployeeName, u.REALNAME) as name,
        (SELECT STRING_AGG(location_name, ',') FROM [dbo].[user_pic_locations] WHERE EMPID = u.EMPID AND device_type = 'apar') as apar_locations,
        (SELECT STRING_AGG(location_name, ',') FROM [dbo].[user_pic_locations] WHERE EMPID = u.EMPID AND device_type = 'hydrant') as hydrant_locations
    FROM [apar].[Users].[UserTable] u
    LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON u.EMPID = e.EmpID
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
