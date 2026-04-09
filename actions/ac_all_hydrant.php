<?php
include(__DIR__ . '/../config/db_koneksi.php');

$area = isset($_GET['area']) ? $_GET['area'] : 'All Areas';
$search = isset($_GET['q']) ? $_GET['q'] : '';

$where = [];
$params = [];

if ($area !== 'All Areas') {
    $where[] = "h.area = ?";
    $params[] = $area;
}

$where[] = "h.asset_type = 'Hydrant'";

if ($search !== "") {
    $where[] = "(h.asset_code LIKE ? OR h.location LIKE ? OR h.model_type LIKE ? OR h.status LIKE ?)";
    $search_param = "%$search%";
    for ($i = 0; $i < 4; $i++)
        $params[] = $search_param;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT 
            id,
            asset_code as code,
            location,
            area,
            h.status,
            h.model_type as type,
            h.last_inspection_date,
            h.is_active,
            ISNULL(e.EmployeeName, u.REALNAME) as pic_name
        FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] h
        LEFT JOIN [ATI].[dbo].[HRD_EMPLOYEE_TABLE] e ON h.pic_empid = e.EmpID
        LEFT JOIN [ATI].[Users].[UserTable] u ON h.pic_empid = u.EMPID
        $where_clause
        ORDER BY h.is_active DESC, h.area ASC, h.asset_code ASC";

$result = sqlsrv_query($koneksi, $sql, $params);
$hydrant_data = [];

if ($result !== false) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        if ($row['last_inspection_date'] instanceof DateTime) {
            $row['last_inspection_date'] = $row['last_inspection_date']->format('d M Y');
        } else {
            $row['last_inspection_date'] = '-';
        }

        // Clean values
        $row['type'] = $row['type'] ?: 'N/A';

        $hydrant_data[] = $row;
    }
}

// Support both include and direct AJAX
if (basename($_SERVER['PHP_SELF']) == 'ac_all_hydrant.php' || isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($hydrant_data);
    exit;
}
?>