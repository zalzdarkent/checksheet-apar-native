<?php
include(__DIR__ . '/../../config/db_koneksi.php');

header('Content-Type: application/json');

try {
    $data = [];

    // 1. Fetch ALL APAR Abnormal Cases
    $sql_apar = "
        SELECT 
            c.id,
            c.apar_id as unit_id,
            'apar' as type,
            a.code,
            a.area,
            c.abnormal_case,
            c.countermeasure,
            c.status,
            c.created_at,
            c.due_date,
            c.verified_at,
            ISNULL(e.EmployeeName, u.REALNAME) as pic_name
        FROM [apar].[dbo].[apar_abnormal_cases] c
        LEFT JOIN [apar].[dbo].[apars] a ON c.apar_id = a.id
        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON c.pic_id = e.EmpID
        LEFT JOIN [apar].[Users].[UserTable] u ON c.pic_id = u.EMPID
        ORDER BY c.created_at DESC
    ";
    
    $stmt_apar = sqlsrv_query($koneksi, $sql_apar);
    if ($stmt_apar !== false) {
        while ($row = sqlsrv_fetch_array($stmt_apar, SQLSRV_FETCH_ASSOC)) {
            $row['created_at_fmt'] = $row['created_at'] instanceof DateTime ? $row['created_at']->format('d/m/Y H:i') : '-';
            $row['verified_at_fmt'] = $row['verified_at'] instanceof DateTime ? $row['verified_at']->format('d/m/Y H:i') : '-';
            $row['due_date_fmt'] = $row['due_date'] instanceof DateTime ? $row['due_date']->format('d/m/Y') : ($row['due_date'] ?: '-');
            $data[] = $row;
        }
    }

    // 2. Fetch ALL Hydrant Abnormal Cases
    $sql_hydrant = "
        SELECT 
            c.id,
            c.hydrant_id as unit_id,
            'hydrant' as type,
            h.code,
            h.area,
            c.abnormal_case,
            c.countermeasure,
            c.status,
            c.created_at,
            c.due_date,
            c.verified_at,
            ISNULL(e.EmployeeName, u.REALNAME) as pic_name
        FROM [apar].[dbo].[hydrant_abnormal_cases] c
        LEFT JOIN [apar].[dbo].[hydrants] h ON c.hydrant_id = h.id
        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON c.pic_id = e.EmpID
        LEFT JOIN [apar].[Users].[UserTable] u ON c.pic_id = u.EMPID
        ORDER BY c.created_at DESC
    ";
    
    $stmt_hydrant = sqlsrv_query($koneksi, $sql_hydrant);
    if ($stmt_hydrant !== false) {
        while ($row = sqlsrv_fetch_array($stmt_hydrant, SQLSRV_FETCH_ASSOC)) {
            $row['created_at_fmt'] = $row['created_at'] instanceof DateTime ? $row['created_at']->format('d/m/Y H:i') : '-';
            $row['verified_at_fmt'] = $row['verified_at'] instanceof DateTime ? $row['verified_at']->format('d/m/Y H:i') : '-';
            $row['due_date_fmt'] = $row['due_date'] instanceof DateTime ? $row['due_date']->format('d/m/Y') : ($row['due_date'] ?: '-');
            $data[] = $row;
        }
    }

    // Sort all by date descending
    usort($data, function($a, $b) {
        return strtotime($b['created_at_fmt']) - strtotime($a['created_at_fmt']);
    });

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
