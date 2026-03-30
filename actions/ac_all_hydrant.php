<?php
include(__DIR__ . '/../config/db_koneksi.php');

$area = isset($_GET['area']) ? $_GET['area'] : 'All Areas';
$search = isset($_GET['q']) ? $_GET['q'] : '';

$where = [];
$params = [];

if ($area !== 'All Areas') {
    $where[] = "a.area = ?";
    $params[] = $area;
}

if ($search !== "") {
    $where[] = "(a.code LIKE ? OR a.location LIKE ? OR a.type LIKE ? OR a.status LIKE ? OR a.weight LIKE ?)";
    $search_param = "%$search%";
    for ($i = 0; $i < 5; $i++) $params[] = $search_param;
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT 
            id,
            code,
            location,
            area,
            weight,
            expired_date,
            status,
            type,
            last_inspection_date
        FROM [apar].[dbo].[hydrants] a
        $where_clause
        ORDER BY a.area ASC, a.code ASC";

$result = sqlsrv_query($koneksi, $sql, $params);
$apar_data = [];

if ($result !== false) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        if ($row['expired_date'] instanceof DateTime) {
            $row['expired_date'] = $row['expired_date']->format('Y-m-d H:i:s');
        } else {
            $row['expired_date'] = '-';
        }
        
        if ($row['last_inspection_date'] instanceof DateTime) {
            $row['last_inspection_date'] = $row['last_inspection_date']->format('d M Y');
        } else {
            $row['last_inspection_date'] = '-';
        }

        // Clean values
        $row['type'] = $row['type'] ?: 'N/A';
        $row['weight'] = $row['weight'] ?: '-';
        
        $apar_data[] = $row;
    }
}

// Support both include and direct AJAX
if (basename($_SERVER['PHP_SELF']) == 'ac_all_hydrant.php' || isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($apar_data);
    exit;
}
?>
