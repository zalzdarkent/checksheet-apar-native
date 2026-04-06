<?php

$hostname = gethostname();
// echo $hostname;
// die;
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
// var_dump($koneksi);
// die;

//  $sql = "SELECT id, item_code, name, location_id, qty, created_at, updated_at FROM item_table";
//     $stmt = sqlsrv_query($koneksi, $sql);
//     $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

//     var_dump($row);die();


if (!$koneksi) {
    die(print_r(sqlsrv_errors(), true));
}

// Auto-update status APAR menjadi 'NG' sekali sehari (Berlaku untuk semua Area)
$daily_check_file = __DIR__ . '/../storage/last_expired_check.txt';
$today = date('Y-m-d');
$last_check = file_exists($daily_check_file) ? file_get_contents($daily_check_file) : '';

if ($last_check !== $today) {
    // 1. Buatkan record problem baru (Action Required) bagi APAR yg baru saja expired hari ini
    $insert_cases_sql = "INSERT INTO [apar].[dbo].[apar_abnormal_cases] 
                         (apar_id, abnormal_case, status, created_at)
                         SELECT id, 'Unit Expired (Auto-Status NG)', 'Open', GETDATE()
                         FROM [apar].[dbo].[apars]
                         WHERE expired_date <= CAST(GETDATE() AS DATE) 
                         AND status <> 'NG'
                         AND id NOT IN (
                             SELECT apar_id FROM [apar].[dbo].[apar_abnormal_cases] 
                             WHERE abnormal_case LIKE 'Unit Expired%' 
                             AND status IN ('Open', 'On Progress')
                         )";
    sqlsrv_query($koneksi, $insert_cases_sql);

    // 2. Baru kemudian tandai unit master APAR tersebut ke status NG
    $update_expired_sql = "UPDATE [apar].[dbo].[apars] 
                           SET status = 'NG' 
                           WHERE expired_date <= CAST(GETDATE() AS DATE) 
                           AND status <> 'NG'";
    sqlsrv_query($koneksi, $update_expired_sql);
    
    // Simpan tanggal hari ini agar tidak dijalankan rute berkali-kali
    if (!is_dir(__DIR__ . '/../storage')) {
        mkdir(__DIR__ . '/../storage', 0777, true);
    }
    file_put_contents($daily_check_file, $today);
} 
