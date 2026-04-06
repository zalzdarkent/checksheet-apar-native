<?php
// Simulate inspection submission untuk debug
include 'config/db_koneksi.php';

$apar_id = 78;
$user_id = 1;

// Mock POST data - semua items OK
$_POST = [
    'type' => 'apar',
    'equipment_id' => $apar_id,
    'inspection_date' => '2026-04-02 10:00',
    'general_notes' => 'Test inspection',
    'exp_date_ok' => '0', // Abnormal
    'pressure_ok' => '1', // OK
    'weight_co2_ok' => '1', // OK
    'tube_ok' => '1',
    'hose_ok' => '1',
    'bracket_ok' => '1',
    'wi_ok' => '1',
    'form_kejadian_ok' => '1',
    'sign_box_ok' => '1',
    'sign_triangle_ok' => '1',
    'marking_tiger_ok' => '1',
    'marking_beam_ok' => '1',
    'sr_apar_ok' => '1',
    'kocok_apar_ok' => '1',
    'label_ok' => '1',
];

echo "=== TEST: Inserting inspection for APAR $apar_id ===\n";

$table = 'bimonthly_apar_inspections';
$items = ['exp_date', 'pressure', 'weight_co2', 'tube', 'hose', 'bracket', 'wi', 'form_kejadian', 'sign_box', 'sign_triangle', 'marking_tiger', 'marking_beam', 'sr_apar', 'kocok_apar', 'label'];

$cols = ['apar_id', 'user_id', 'inspection_date', 'notes', 'created_at', 'updated_at'];
$placeholders = ['?', '?', '?', '?', 'GETDATE()', 'GETDATE()'];
$params = [$apar_id, $user_id, $_POST['inspection_date'], $_POST['general_notes']];

foreach ($items as $item) {
    $ok = (int)($_POST[$item . '_ok'] ?? 0);
    
    $cols[] = $item . '_ok';
    $cols[] = $item . '_foto';
    $placeholders[] = '?';
    $placeholders[] = '?';
    $params[] = $ok;
    $params[] = ''; // empty foto path
}

// Build SQL
$sql = "INSERT INTO [apar].[dbo].[" . $table . "] (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";

echo "SQL: " . $sql . "\n";
echo "Cols count: " . count($cols) . ", Params count: " . count($params) . "\n";

// Try insert
$stmt = sqlsrv_query($koneksi, $sql, $params);

if ($stmt === false) {
    echo "INSERT FAILED!\n";
    echo "Error: " . print_r(sqlsrv_errors(), true);
} else {
    echo "✓ INSERT SUCCESS\n";
    
    // Now test abnormal detection
    echo "\n=== TEST: Abnormal Detection ===\n";
    
    $abnormal_items = [];
    $item_labels = [
        'exp_date' => 'Exp. Date',
        'pressure' => 'Pressure',
        'weight_co2' => 'Weight CO2',
        'tube' => 'Tube',
        'hose' => 'Hose',
        'bracket' => 'Bracket',
        'wi' => 'WI',
        'form_kejadian' => 'Form Kejadian',
        'sign_box' => 'SIGN Kotak',
        'sign_triangle' => 'SIGN Segitiga',
        'marking_tiger' => 'Marking Tiger',
        'marking_beam' => 'Marking Beam',
        'sr_apar' => '5R APAR',
        'kocok_apar' => 'Kocok APAR',
        'label' => 'Label'
    ];
    
    foreach ($items as $item) {
        $ok_value = (int)($_POST[$item . '_ok'] ?? 1);
        echo "Checking $item: value=$ok_value ... ";
        if ($ok_value === 0) {
            $abnormal_items[] = $item_labels[$item] ?? $item;
            echo "ABNORMAL\n";
        } else {
            echo "OK\n";
        }
    }
    
    echo "\nTotal abnormal: " . count($abnormal_items) . "\n";
    if (!empty($abnormal_items)) {
        echo "Abnormal items: " . implode(", ", $abnormal_items) . "\n";
        
        // Update status
        $update_sql = "UPDATE [apar].[dbo].[apars] SET status = 'Abnormal' WHERE id = ?";
        $result = sqlsrv_query($koneksi, $update_sql, [$apar_id]);
        
        if ($result) {
            echo "✓ Status updated to 'Abnormal'\n";
        } else {
            echo "✗ Status update FAILED: " . print_r(sqlsrv_errors(), true) . "\n";
        }
        
        // Create abnormal case
        $abnormal_case_text = implode(', ', $abnormal_items);
        $abnormal_sql = "INSERT INTO [apar].[dbo].[abnormal_cases] (apar_id, abnormal_case, created_at, status, user_id) VALUES (?, ?, GETDATE(), 'Open', ?)";
        $result = sqlsrv_query($koneksi, $abnormal_sql, [$apar_id, $abnormal_case_text, $user_id]);
        
        if ($result) {
            echo "✓ Abnormal case created\n";
        } else {
            echo "✗ Case creation FAILED: " . print_r(sqlsrv_errors(), true) . "\n";
        }
    }
}

// Check final status
echo "\n=== VERIFY: Current APAR Status ===\n";
$check_sql = "SELECT id, code, status FROM [apar].[dbo].[apars] WHERE id = ?";
$result = sqlsrv_query($koneksi, $check_sql, [$apar_id]);
if ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    echo "APAR {$row['id']} ({$row['code']}): Status = {$row['status']}\n";
}
?>
