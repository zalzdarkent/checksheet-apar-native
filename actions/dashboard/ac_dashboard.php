<?php
include(__DIR__ . '/../../config/db_koneksi.php');

// Helper for Master Stats
function get_stats($type, $status = null, $only_inspected_this_month = false, $only_abnormal = false)
{
    global $koneksi;
    
    $where = ["a.asset_type = ?"];
    $params = [$type];
    
    if ($status) {
        $where[] = "a.status = ?";
        $params[] = $status;
    }

    if ($only_inspected_this_month) {
        $where[] = "EXISTS (
            SELECT 1 FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] t
            WHERE t.asset_id = a.id
            AND MONTH(t.inspection_date) = MONTH(GETDATE())
            AND YEAR(t.inspection_date) = YEAR(GETDATE())
        )";
    }

    if ($only_abnormal) {
        // Assets are abnormal if status != OK OR expired
        $where[] = "(a.status <> 'OK' OR (a.expired_date IS NOT NULL AND a.expired_date <= CAST(GETDATE() AS DATE)))";
    }

    $where_clause = "WHERE " . implode(" AND ", $where);
    $sql = "SELECT COUNT(*) AS total FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] a $where_clause";
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return $row['total'];
    }
    return 0;
}

// ----------------- APAR Summaries -----------------
function get_total_apar() { return get_stats('APAR'); }
function get_total_apar_proses() { 
    global $koneksi;
    // Total APAR minus those inspected this month
    $total = get_total_apar();
    $inspected = get_stats('APAR', null, true);
    return max(0, $total - $inspected);
}
function get_total_apar_ok() { return get_stats('APAR', 'OK', true); }
function get_total_apar_abnormal() { return get_stats('APAR', null, false, true); }

// ----------------- Hydrant Summaries -----------------
function get_total_hydrant() { return get_stats('Hydrant'); }
function get_total_hydrant_proses() { 
    $total = get_total_hydrant();
    $inspected = get_stats('Hydrant', null, true);
    return max(0, $total - $inspected);
}
function get_total_hydrant_ok() { return get_stats('Hydrant', 'OK', true); }
function get_total_hydrant_abnormal() { return get_stats('Hydrant', null, false, true); }

// ----------------- Abnormal Cases (Consolidated) -----------------
function get_all_abnormal_cases($type)
{
    global $koneksi;

    $sql = "SELECT 
                l.id,
                l.asset_id,
                l.finding_desc as abnormal_case,
                l.countermeasure,
                l.due_date,
                l.repair_photo,
                l.repair_status as status,
                l.created_at,
                m.asset_code as code,
                m.location,
                m.area,
                l.pic_empid as pic_id,
                l.verified_by as verifier_id,
                COALESCE(u_sys.name, e_pic.EmployeeName, u_emp.REALNAME) as pic_name,
                u_sys.photo as pic_photo
            FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l
            INNER JOIN [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON l.asset_id = m.id
            LEFT JOIN [apar].[dbo].[users] u_sys ON LTRIM(RTRIM(l.pic_empid)) = LTRIM(RTRIM(u_sys.npk))
            LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_pic ON LTRIM(RTRIM(l.pic_empid)) = LTRIM(RTRIM(e_pic.EmpID))
            LEFT JOIN [apar].[Users].[UserTable] u_emp ON LTRIM(RTRIM(l.pic_empid)) = LTRIM(RTRIM(u_emp.EMPID))
            WHERE m.asset_type = ? AND l.repair_status IN ('Open', 'On Progress', 'Closed')
            ORDER BY CASE WHEN l.repair_status='Closed' THEN 1 ELSE 0 END ASC, l.created_at DESC";

    $result = sqlsrv_query($koneksi, $sql, [$type]);
    $data = [];
    if ($result !== false) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    return $data;
}

function get_apar_abnormal_cases() { return get_all_abnormal_cases('APAR'); }
function get_hydrant_abnormal_cases() { return get_all_abnormal_cases('Hydrant'); }
?>
