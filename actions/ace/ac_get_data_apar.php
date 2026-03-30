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
        a.code LIKE ? OR 
        a.location LIKE ? OR 
        a.type LIKE ? OR 
        CAST(a.last_inspection_date AS VARCHAR) LIKE ?
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
            expired_date,
            last_inspection_date as last_inspection
        FROM [apar].[dbo].[apars] a
        WHERE a.area = 'Ace' $where_search
        ORDER BY a.code ASC
        OFFSET ? ROWS
        FETCH NEXT ? ROWS ONLY";

$result = sqlsrv_query($koneksi, $sql, $params);
$apar_data = [];

if ($result !== false) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        if ($row['last_inspection'] instanceof DateTime) {
            $row['last_inspection'] = $row['last_inspection']->format('d M Y');
        } else {
            $row['last_inspection'] = '-';
        }
        $apar_data[] = $row;
    }
}

// Only echo JSON if hit directly
if (basename($_SERVER['PHP_SELF']) == 'ac_get_data.php') {
    header('Content-Type: application/json');
    echo json_encode($apar_data);
}
?>