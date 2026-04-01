<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? $_GET['q'] : '';

$where_search = "";
$params = [];

if ($search !== "") {
    $where_search = " AND (
        h.code LIKE ? OR 
        h.location LIKE ? OR 
        h.type LIKE ? OR 
        CAST(h.last_inspection_date AS VARCHAR) LIKE ?
    )";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

$params[] = $offset;
$params[] = $limit;

$sql = "SELECT 
            id,
            code,
            location,
            type,
            status,
            last_inspection_date as last_inspection,
            is_active
        FROM [apar].[dbo].[hydrants] h
        WHERE h.area = 'Office' $where_search
        ORDER BY h.is_active DESC, h.code ASC
        OFFSET ? ROWS
        FETCH NEXT ? ROWS ONLY";

$result = sqlsrv_query($koneksi, $sql, $params);
$hydrant_data = [];

if ($result !== false) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        if ($row['last_inspection'] instanceof DateTime) {
            $row['last_inspection'] = $row['last_inspection']->format('d M Y');
        } else {
            $row['last_inspection'] = '-';
        }
        $hydrant_data[] = $row;
    }
}

// Only echo JSON if hit directly
if (basename($_SERVER['PHP_SELF']) == 'ac_get_data_hydrant.php') {
    header('Content-Type: application/json');
    echo json_encode($hydrant_data);
}
?>