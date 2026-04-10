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
            SELECT 1 FROM [PRD].[dbo].[SE_FIRE_PROTECTION_TRANS] t
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
    $sql = "SELECT COUNT(*) AS total FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] a $where_clause";

    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return $row['total'];
    }
    return 0;
}

// ----------------- APAR Summaries -----------------
function get_total_apar()
{
    return get_stats('APAR');
}
function get_total_apar_proses()
{
    $total = get_total_apar();
    $inspected = get_stats('APAR', null, true);
    return max(0, $total - $inspected);
}
function get_total_apar_ok()
{
    return get_stats('APAR', 'OK', true);
}
function get_total_apar_abnormal()
{
    return get_stats('APAR', null, false, true);
}

// ----------------- Hydrant Summaries -----------------
function get_total_hydrant()
{
    return get_stats('Hydrant');
}
function get_total_hydrant_proses()
{
    $total = get_total_hydrant();
    $inspected = get_stats('Hydrant', null, true);
    return max(0, $total - $inspected);
}
function get_total_hydrant_ok()
{
    return get_stats('Hydrant', 'OK', true);
}
function get_total_hydrant_abnormal()
{
    return get_stats('Hydrant', null, false, true);
}

// ----------------- Abnormal Cases (Grouped per Asset) -----------------
// Setiap asset (APAR/Hydrant) yang punya >1 item NG digabung menjadi 1 baris.
// Status ditentukan dari kondisi terburuk: Open > On Progress > Closed
function get_all_abnormal_cases($type)
{
    global $koneksi;

    $sql = "
        WITH grouped AS (
            SELECT
                m.id                                                            AS id,
                m.asset_code                                                    AS code,
                m.area,
                m.location,
                STRING_AGG(l.finding_desc, ' | ')
                    WITHIN GROUP (ORDER BY l.id)                                AS abnormal_case,
                STRING_AGG(CAST(l.id AS NVARCHAR(20)), ',')
                    WITHIN GROUP (ORDER BY l.id)                                AS line_ids,
                CASE
                    WHEN SUM(CASE WHEN l.repair_status = 'Open'        THEN 1 ELSE 0 END) > 0 THEN 'Open'
                    WHEN SUM(CASE WHEN l.repair_status = 'On Progress' THEN 1 ELSE 0 END) > 0 THEN 'On Progress'
                    WHEN SUM(CASE WHEN l.repair_status = 'Revision'    THEN 1 ELSE 0 END) > 0 THEN 'Revision'
                    WHEN SUM(CASE WHEN l.repair_status = 'Closed'      THEN 1 ELSE 0 END) > 0 THEN 'Closed'
                    ELSE 'Verified'
                END                                                             AS status,
                MIN(l.due_date)                                                 AS due_date,
                MAX(l.created_at)                                               AS created_at,
                MIN(l.countermeasure)                                           AS countermeasure,
                MIN(l.pic_empid)                                                AS pic_id,
                COUNT(l.id)                                                     AS ng_count
            FROM [PRD].[dbo].[SE_FIRE_PROTECTION_LINES] l
            INNER JOIN [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON l.asset_id = m.id
            WHERE m.asset_type = ?
              AND l.repair_status IN ('Open', 'On Progress', 'Closed', 'Revision')
            GROUP BY m.id, m.asset_code, m.area, m.location
        )
        SELECT
            g.*,
            COALESCE(u_sys.REALNAME, e_pic.EmployeeName) AS pic_name,
            u_sys.PicFile                                 AS pic_photo
        FROM grouped g
        LEFT JOIN [ATI].[Users].[UserTable] u_sys
            ON LTRIM(RTRIM(g.pic_id)) = LTRIM(RTRIM(u_sys.EMPID))
        LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e_pic
            ON LTRIM(RTRIM(g.pic_id)) = LTRIM(RTRIM(e_pic.EmpID))
        ORDER BY
            CASE g.status
                WHEN 'Open'        THEN 1
                WHEN 'On Progress' THEN 2
                WHEN 'Revision'    THEN 3
                WHEN 'Closed'      THEN 4
                ELSE 5
            END ASC,
            g.created_at DESC";

    $result = sqlsrv_query($koneksi, $sql, [$type]);
    $data = [];
    if ($result !== false) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    return $data;
}

function get_apar_abnormal_cases()
{
    return get_all_abnormal_cases('APAR');
}
function get_hydrant_abnormal_cases()
{
    return get_all_abnormal_cases('Hydrant');
}
?>