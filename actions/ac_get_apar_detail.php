<?php
include(__DIR__ . '/../config/db_koneksi.php');

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
        $sql_history = "SELECT h.*, ISNULL(e.EmployeeName, u.REALNAME) as inspector_name 
                        FROM [apar].[dbo].[bimonthly_apar_inspections] h
                        LEFT JOIN [apar].[Users].[UserTable] u ON h.user_id = u.EMPID
                        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON h.user_id = e.EmpID
                        WHERE h.apar_id = ? 
                        ORDER BY h.inspection_date DESC";
        $stmt_history = sqlsrv_query($koneksi, $sql_history, [$id]);
        $history = [];
        
        $items = [
            'exp_date' => 'Expired Date', 'pressure' => 'Pressure', 
            'weight_co2' => 'Weight CO2', 'tube' => 'Tube', 
            'hose' => 'Hose', 'bracket' => 'Bracket', 'wi' => 'WI', 
            'form_kejadian' => 'Form Kejadian', 'sign_box' => 'Sign Kotak', 
            'sign_triangle' => 'Sign Segitiga', 'marking_tiger' => 'Marking Tiger', 
            'marking_beam' => 'Marking Tiang', 'sr_apar' => 'SR Apar', 
            'kocok_apar' => 'Kocok Apar', 'label' => 'Label'
        ];
        
        if ($stmt_history !== false) {
            while ($row = sqlsrv_fetch_array($stmt_history, SQLSRV_FETCH_ASSOC)) {
                if ($row['inspection_date'] instanceof DateTime) {
                    $row['inspection_date_fmt'] = $row['inspection_date']->format('d M Y H:i');
                } else {
                    $row['inspection_date_fmt'] = '-';
                }
                
                $is_ng = false;
                $ng_details = [];
                $ng_items_list = [];
                foreach ($items as $item_key => $item_label) {
                     if (isset($row[$item_key . '_ok']) && $row[$item_key . '_ok'] === 0) {
                         $is_ng = true;
                         $ng_details[] = $item_label;
                     }
                     // Attach all item info for the modal later
                     $ng_items_list[] = [
                         'key' => $item_key,
                         'label' => $item_label,
                         'ok' => isset($row[$item_key . '_ok']) ? $row[$item_key . '_ok'] : 1,
                         'photo' => isset($row[$item_key . '_foto']) ? $row[$item_key . '_foto'] : null,
                         'keterangan' => isset($row[$item_key . '_keterangan']) ? $row[$item_key . '_keterangan'] : null
                     ];
                }
                $row['insp_status'] = $is_ng ? 'NG' : 'OK';
                $row['ng_text'] = implode(', ', $ng_details);
                $row['full_items'] = $ng_items_list;

                $history[] = $row;
            }
        }

        // 3. Get Abnormal Cases with PIC name
        $sql_cases = "SELECT c.*, ISNULL(e_pic.EmployeeName, u_pic.REALNAME) as pic_name, ISNULL(e_ver.EmployeeName, u_ver.REALNAME) as verified_by_name
                      FROM [apar].[dbo].[apar_abnormal_cases] c
                      LEFT JOIN [apar].[Users].[UserTable] u_pic ON c.pic_id = u_pic.EMPID
                      LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_pic ON c.pic_id = e_pic.EmpID
                      LEFT JOIN [apar].[Users].[UserTable] u_ver ON c.verified_by = u_ver.EMPID
                      LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_ver ON c.verified_by = e_ver.EmpID
                      WHERE c.apar_id = ? 
                      ORDER BY c.created_at DESC";
        $stmt_cases = sqlsrv_query($koneksi, $sql_cases, [$id]);
        $cases = [];
        if ($stmt_cases !== false) {
            while ($row = sqlsrv_fetch_array($stmt_cases, SQLSRV_FETCH_ASSOC)) {
                if ($row['created_at'] instanceof DateTime) {
                    $row['created_at_fmt'] = $row['created_at']->format('d M Y');
                } else {
                    $row['created_at_fmt'] = '-';
                }
                // Format due_date if it's DateTime
                if (isset($row['due_date'])) {
                    if ($row['due_date'] instanceof DateTime) {
                        $row['due_date_fmt'] = $row['due_date']->format('d M Y');
                    } else {
                        $row['due_date_fmt'] = $row['due_date'] ?: '-';
                    }
                } else {
                    $row['due_date_fmt'] = '-';
                }
                $cases[] = $row;
            }
        }

        $apar['history'] = $history;
        $apar['cases'] = $cases;
        
        // Check if APAR is expired and update status accordingly
        if ($apar['expired_date'] instanceof DateTime) {
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $expDate = clone $apar['expired_date'];
            $expDate->setTime(0, 0, 0);
            if ($expDate <= $today) {
                $apar['status'] = 'Expired';
                $apar['is_expired'] = true;
            } else {
                $apar['is_expired'] = false;
            }
        } else {
            $apar['is_expired'] = false;
        }
    }
}

// If this is an AJAX call, output JSON.
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($apar);
    exit;
}
?>
