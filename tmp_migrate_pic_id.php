<?php
include(__DIR__ . '/config/db_koneksi.php');

$queries = [
    // 1. Drop constraints from apar_abnormal_cases
    "ALTER TABLE [dbo].[apar_abnormal_cases] DROP CONSTRAINT [apar_abnormal_cases_pic_id_foreign]",
    // 2. Drop constraints from hydrant_abnormal_cases
    "ALTER TABLE [dbo].[hydrant_abnormal_cases] DROP CONSTRAINT [hydrant_abnormal_cases_pic_id_foreign]",
    // 3. Alter columns to NVARCHAR(20)
    "ALTER TABLE [dbo].[apar_abnormal_cases] ALTER COLUMN [pic_id] NVARCHAR(20) NULL",
    "ALTER TABLE [dbo].[hydrant_abnormal_cases] ALTER COLUMN [pic_id] NVARCHAR(20) NULL"
];

foreach ($queries as $sql) {
    echo "Executing: $sql\n";
    $stmt = sqlsrv_query($koneksi, $sql);
    if ($stmt === false) {
        echo "FAILED: " . print_r(sqlsrv_errors(), true) . "\n";
    } else {
        echo "SUCCESS.\n";
    }
}
?>
