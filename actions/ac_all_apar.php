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
            a.status,
            a.type,
            a.last_inspection_date,
            a.is_active,
            ISNULL(e.EmployeeName, u.REALNAME) as pic_name
        FROM [apar].[dbo].[apars] a
        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON a.pic_empid = e.EmpID
        LEFT JOIN [apar].[Users].[UserTable] u ON a.pic_empid = u.EMPID
        $where_clause
        ORDER BY a.is_active DESC, a.area ASC, a.code ASC";

$result = sqlsrv_query($koneksi, $sql, $params);
$apar_data = [];

if ($result !== false) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $is_expired = false;
        
        if ($row['expired_date'] instanceof DateTime) {
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $expDate = clone $row['expired_date'];
            $expDate->setTime(0, 0, 0);
            $is_expired = $expDate <= $today;
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
        $row['is_expired'] = $is_expired;
        
        // If expired, override status to 'Expired'
        if ($is_expired) {
            $row['status'] = 'Expired';
        }
        
        $apar_data[] = $row;
    }
}

// Support both include and direct AJAX
if (basename($_SERVER['PHP_SELF']) == 'ac_all_apar.php' || isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($apar_data);
    exit;
}
?>
