<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 12;
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? $_GET['q'] : '';

$where_search = "";
$params = [];

if ($search !== "") {
    $where_search = " AND (
        a.asset_code LIKE ? OR 
        a.location LIKE ? OR 
        a.model_type LIKE ? OR 
        CAST(a.last_inspection_date AS VARCHAR) LIKE ?
    )";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

$params[] = $offset;
$params[] = $limit;

$sql = "SELECT 
            id,
            asset_code as code,
            location,
            model_type as type,
            status,
            last_inspection_date as last_inspection,
            is_active
        FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] a
        WHERE a.area = 'Disa' AND a.asset_type = 'HYDRANT' $where_search
        ORDER BY a.is_active DESC, a.asset_code ASC
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
if (basename($_SERVER['PHP_SELF']) == 'ac_get_data_hydrants.php') {
    header('Content-Type: application/json');
    echo json_encode($hydrant_data);
}
?>