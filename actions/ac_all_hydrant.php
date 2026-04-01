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

if ($search !== "") {
    $where[] = "(h.code LIKE ? OR h.location LIKE ? OR h.type LIKE ? OR h.status LIKE ?)";
    $search_param = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $search_param;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT 
            id,
            code,
            location,
            area,
            status,
            type,
            last_inspection_date,
            is_active
FROM [apar].[dbo].[hydrants] h
        $where_clause
        ORDER BY h.is_active DESC, h.area ASC, h.code ASC";

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
