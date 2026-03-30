<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $hydrant = null;
} else {
    // 1. Get Hydrant Info
    $sql_hydrant = "SELECT * FROM [apar].[dbo].[hydrants] WHERE id = ?";
    $stmt_hydrant = sqlsrv_query($koneksi, $sql_hydrant, [$id]);
    $hydrant = sqlsrv_fetch_array($stmt_hydrant, SQLSRV_FETCH_ASSOC);

    if ($hydrant) {
        // Format dates
        if ($hydrant['last_inspection_date'] instanceof DateTime) {
            $hydrant['last_inspection_fmt'] = $hydrant['last_inspection_date']->format('d M Y');
        } else {
            $hydrant['last_inspection_fmt'] = '-';
        }

        // 2. Get Inspection History (Join with users for inspector name)
        // Note: For hydrants, we use hydrant_unit_id in bimonthly_inspections
        $sql_history = "SELECT h.*, u.username as inspector_name 
                        FROM [apar].[dbo].[bimonthly_inspections] h
                        LEFT JOIN [apar].[dbo].[users] u ON h.user_id = u.id
                        WHERE h.hydrant_unit_id = ? 
                        ORDER BY h.inspection_date DESC";
        $stmt_history = sqlsrv_query($koneksi, $sql_history, [$id]);
        $history = [];
        if ($stmt_history !== false) {
            while ($row = sqlsrv_fetch_array($stmt_history, SQLSRV_FETCH_ASSOC)) {
                if ($row['inspection_date'] instanceof DateTime) {
                    $row['inspection_date_fmt'] = $row['inspection_date']->format('d M Y H:i');
                } else {
                    $row['inspection_date_fmt'] = '-';
                }
                $history[] = $row;
            }
        }

        // 3. Get Abnormal Cases
        $sql_cases = "SELECT * FROM [apar].[dbo].[hydrant_abnormal_cases] 
                      WHERE hydrant_id = ? 
                      ORDER BY created_at DESC";
        $stmt_cases = sqlsrv_query($koneksi, $sql_cases, [$id]);
        $cases = [];
        if ($stmt_cases !== false) {
            while ($row = sqlsrv_fetch_array($stmt_cases, SQLSRV_FETCH_ASSOC)) {
                if ($row['created_at'] instanceof DateTime) {
                    $row['created_at_fmt'] = $row['created_at']->format('d M Y');
                } else {
                    $row['created_at_fmt'] = '-';
                }
                $cases[] = $row;
            }
        }

        $hydrant['history'] = $history;
        $hydrant['cases'] = $cases;
    }
}

// If this is an AJAX call, output JSON.
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($hydrant);
    exit;
}
?>
