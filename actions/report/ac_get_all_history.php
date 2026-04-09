<?php
include(__DIR__ . '/../../config/db_koneksi.php');

header('Content-Type: application/json');

try {
    $data = [];

    // Unified Fetch from LINES table
    $sql = "
        SELECT 
            l.id,
            l.asset_id as unit_id,
            LOWER(m.asset_type) as type,
            m.asset_code as code,
            m.area,
            l.finding_desc as abnormal_case,
            l.countermeasure,
            l.repair_status as status,
            l.created_at,
            l.due_date,
            l.verified_at,
            COALESCE(u_sys.REALNAME, e_pic.EmployeeName, u_emp.REALNAME) as pic_name
        FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES] l
        LEFT JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON l.asset_id = m.id
        LEFT JOIN [ATI].[Users].[UserTable] u_sys ON LTRIM(RTRIM(l.pic_empid)) = LTRIM(RTRIM(u_sys.EMPID))
        LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e_pic ON LTRIM(RTRIM(l.pic_empid)) = LTRIM(RTRIM(e_pic.EmpID))
        LEFT JOIN [ATI].[Users].[UserTable] u_emp ON LTRIM(RTRIM(l.pic_empid)) = LTRIM(RTRIM(u_emp.EMPID))
        ORDER BY l.created_at DESC
    ";

    $stmt = sqlsrv_query($koneksi, $sql);
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row['created_at_fmt'] = $row['created_at'] instanceof DateTime ? $row['created_at']->format('d/m/Y H:i') : '-';
            $row['verified_at_fmt'] = $row['verified_at'] instanceof DateTime ? $row['verified_at']->format('d/m/Y H:i') : '-';
            $row['due_date_fmt'] = $row['due_date'] instanceof DateTime ? $row['due_date']->format('d/m/Y') : ($row['due_date'] ?: '-');
            $data[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>