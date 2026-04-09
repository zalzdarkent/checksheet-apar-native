<?php

$hostname = gethostname();
$serverName = "DESKTOP-1V8I9K6\SQLEXPRESS"; 

$database = "apar";
$username = "sa";
$password = "Saaccountalif123";

$connectionInfo = array(
    "Database" => $database,
    "UID" => $username,
    "PWD" => $password,
    "CharacterSet" => "UTF-8",
);

$koneksi = sqlsrv_connect($serverName, $connectionInfo);

if (!$koneksi) {
    die(print_r(sqlsrv_errors(), true));
}

// Auto-update status for expired units once a day
$daily_check_file = __DIR__ . '/../storage/last_expired_check.txt';
$today = date('Y-m-d');
$last_check = file_exists($daily_check_file) ? file_get_contents($daily_check_file) : '';

if ($last_check !== $today) {
    // 1. Create audit trail entries for newly expired units
    $insert_cases_sql = "INSERT INTO [apar].[dbo].[SE_FIRE_PROTECTION_LINES] 
                         (asset_id, finding_desc, repair_status, created_at)
                         SELECT id, 'Unit Expired (Auto-Status NG)', 'Open', GETDATE()
                         FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER]
                         WHERE expired_date <= CAST(GETDATE() AS DATE) 
                         AND status <> 'NG'
                         AND id NOT IN (
                             SELECT asset_id FROM [apar].[dbo].[SE_FIRE_PROTECTION_LINES] 
                             WHERE finding_desc LIKE 'Unit Expired%' 
                             AND repair_status IN ('Open', 'On Progress')
                         )";
    sqlsrv_query($koneksi, $insert_cases_sql);

    // 2. Mark master unit status to NG
    $update_expired_sql = "UPDATE [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] 
                           SET status = 'NG' 
                           WHERE expired_date <= CAST(GETDATE() AS DATE) 
                           AND status <> 'NG'";
    sqlsrv_query($koneksi, $update_expired_sql);
    
    if (!is_dir(__DIR__ . '/../storage')) {
        mkdir(__DIR__ . '/../storage', 0777, true);
    }
    file_put_contents($daily_check_file, $today);
} 
?>
