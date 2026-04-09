<?php
include(__DIR__ . '/../config/db_koneksi.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $apar = null;
} else {
    // 1. Get APAR Info from unified MASTER table
    $sql_apar = "SELECT 
                    id, 
                    asset_code as code, 
                    asset_type,
                    area, 
                    location, 
                    status, 
                    model_type as type, 
                    weight, 
                    expired_date, 
                    last_inspection_date,
                    is_active,
                    pic_empid
                 FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] 
                 WHERE id = ? AND asset_type = 'APAR'";
    $stmt_apar = sqlsrv_query($koneksi, $sql_apar, [$id]);
    $apar = sqlsrv_fetch_array($stmt_apar, SQLSRV_FETCH_ASSOC);

    if ($apar) {
        $apar['id'] = (int)$apar['id'];
        
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

        // 2. Get Inspection History from unified TRANS table
        $sql_history = "SELECT h.*, ISNULL(e.EmployeeName, u.REALNAME) as inspector_name 
                        FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] h
                        LEFT JOIN [apar].[Users].[UserTable] u ON h.user_id = u.EMPID
                        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON h.user_id = e.EmpID
                        WHERE h.asset_id = ? 
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
                     $ok_val = isset($row[$item_key . '_ok']) ? (int)$row[$item_key . '_ok'] : 1;
                     if ($ok_val === 0) {
                         $is_ng = true;
                         $ng_details[] = $item_label;
                     }
                     $ng_items_list[] = [
                         'key' => $item_key,
                         'label' => $item_label,
                         'ok' => $ok_val,
                         'photo' => $row[$item_key . '_foto'] ?? null
                     ];
                }
                $row['insp_status'] = $is_ng ? 'NG' : 'OK';
                $row['ng_text'] = implode(', ', $ng_details);
                $row['full_items'] = $ng_items_list;
                $history[] = $row;
            }
        }

        // 3. Get Abnormal History from unified LINES table
        $sql_cases = "SELECT l.*, 
                           ISNULL(e_ver.EmployeeName, u_ver.REALNAME) as verified_by_name,
                           ISNULL(e_pic.EmployeeName, u_pic.REALNAME) as pic_name
                       FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] l
                       LEFT JOIN [apar].[Users].[UserTable] u_ver ON l.verified_by = u_ver.EMPID
                       LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_ver ON l.verified_by = e_ver.EmpID
                       LEFT JOIN [apar].[Users].[UserTable] u_pic ON l.pic_empid = u_pic.EMPID
                       LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_pic ON l.pic_empid = e_pic.EmpID
                       WHERE l.asset_id = ? 
                       ORDER BY l.created_at DESC";
        $stmt_cases = sqlsrv_query($koneksi, $sql_cases, [$id]);
        $cases = [];
        if ($stmt_cases !== false) {
            while ($row = sqlsrv_fetch_array($stmt_cases, SQLSRV_FETCH_ASSOC)) {
                $row['id'] = (int)$row['id'];
                $row['abnormal_case'] = $row['finding_desc']; // legacy-naming map
                $row['status'] = $row['repair_status']; // legacy-naming map
                
                if ($row['created_at'] instanceof DateTime) {
                    $row['created_at_fmt'] = $row['created_at']->format('d M Y');
                } else {
                    $row['created_at_fmt'] = '-';
                }
                
                if (isset($row['due_date']) && $row['due_date'] instanceof DateTime) {
                    $row['due_date_fmt'] = $row['due_date']->format('d M Y');
                } else {
                    $row['due_date_fmt'] = '-';
                }
                $cases[] = $row;
            }
        }

        $apar['history'] = $history;
        $apar['cases'] = $cases;
        
        // Expiration Logic
        if ($apar['expired_date'] instanceof DateTime) {
            $today = new DateTime('today');
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

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($apar);
    exit;
}
?>
