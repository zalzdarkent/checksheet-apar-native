<?php
include(__DIR__ . '/../../config/db_koneksi.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $apar = null;
} else {
    // 1. Get APAR Info
    $sql_apar = "SELECT * FROM [apar].[dbo].[apars] WHERE id = ?";
    $stmt_apar = sqlsrv_query($koneksi, $sql_apar, [$id]);
    $apar = sqlsrv_fetch_array($stmt_apar, SQLSRV_FETCH_ASSOC);

    if ($apar) {
        // Format dates
        if ($apar['expired_date'] instanceof DateTime) {
            $apar['expired_date_fmt'] = $apar['expired_date']->format('d M Y');
        } else {
            $apar['expired_date_fmt'] = '-';
        }

        if ($apar['last_inspection_date'] instanceof DateTime) {
            $apar['last_inspection_fmt'] = $apar['last_inspection_date']->format('d M Y');
        } else {
            $apar['last_inspection_fmt'] = '-';
        }

        // 2. Get Inspection History (Join with users for inspector name)
        $sql_history = "SELECT h.*, u.username as inspector_name 
                        FROM [apar].[dbo].[bimonthly_apar_inspections] h
                        LEFT JOIN [apar].[dbo].[users] u ON h.user_id = u.id
                        WHERE h.apar_id = ? 
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
        $sql_cases = "SELECT * FROM [apar].[dbo].[apar_abnormal_cases] 
                      WHERE apar_id = ? 
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

        $apar['history'] = $history;
        $apar['cases'] = $cases;
    }
}

// If this is an AJAX call, output JSON.
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($apar);
    exit;
}
?>
