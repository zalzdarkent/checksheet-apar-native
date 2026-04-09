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
        $sql_history = "SELECT h.*, ISNULL(e.EmployeeName, u.REALNAME) as inspector_name 
                        FROM [apar].[dbo].[bimonthly_hydrant_inspections] h
                        LEFT JOIN [apar].[Users].[UserTable] u ON h.user_id = u.EMPID
                        LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e ON h.user_id = e.EmpID
                        WHERE h.hydrant_id = ? 
                        ORDER BY h.inspection_date DESC";
        $stmt_history = sqlsrv_query($koneksi, $sql_history, [$id]);
        $history = [];
        
        $items = [
            'body_hydrant' => 'Body Hydrant',
            'selang' => 'Selang',
            'couple_join' => 'Couple Join',
            'nozzle' => 'Nozzle',
            'check_sheet' => 'Check Sheet',
            'valve_kran' => 'Valve/Kran',
            'lampu' => 'Lampu',
            'cover_lampu' => 'Cover Lampu',
            'box_display' => 'Box Display',
            'konsul_hydrant' => 'Konsul Hydrant',
            'jr' => 'Kunci Rachet (JR)',
            'marking' => 'Marking',
            'label' => 'Label'
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
                      FROM [apar].[dbo].[hydrant_abnormal_cases] c
                      LEFT JOIN [apar].[Users].[UserTable] u_pic ON c.pic_id = u_pic.EMPID
                      LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_pic ON c.pic_id = e_pic.EmpID
                      LEFT JOIN [apar].[Users].[UserTable] u_ver ON c.verified_by = u_ver.EMPID
                      LEFT JOIN [apar].[dbo].[HRD_EMPLOYEE_TABLE] e_ver ON c.verified_by = e_ver.EmpID
                      WHERE c.hydrant_id = ? 
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
