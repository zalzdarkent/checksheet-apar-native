<?php
include(__DIR__ . '/../../config/db_koneksi.php');

// start apar
function get_total_apar()
{
    global $koneksi;

    $getTotalAPAR = sqlsrv_query($koneksi, "SELECT COUNT(*) AS total_apar FROM [apar].[dbo].[apars]");
    if ($getTotalAPAR === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalAPAR, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_apar'];
    }
    return 0;
}
function get_total_apar_proses()
{
    global $koneksi;

    $sql = "SELECT COUNT(*) AS total_proses 
        FROM [apar].[dbo].[apars] a
        WHERE (a.expired_date IS NULL OR a.expired_date > GETDATE())
        AND NOT EXISTS (
            SELECT 1 
            FROM [apar].[dbo].[bimonthly_inspections] bi
            WHERE bi.apar_unit_id = a.id
                AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                AND YEAR(bi.inspection_date) = YEAR(GETDATE())
        );";

    $getTotalAPARProses = sqlsrv_query($koneksi, $sql);
    if ($getTotalAPARProses === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalAPARProses, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_proses'];
    }
    return 0;
}
function get_total_apar_ok()
{
    global $koneksi;

    $sql = "
    SELECT COUNT(*) AS totalOK FROM [apar].[dbo].[apars] a
    WHERE a.status = 'OK'
    AND (a.expired_date IS NULL OR a.expired_date > GETDATE())
    AND EXISTS (
        SELECT 1 
        FROM [apar].[dbo].[bimonthly_inspections] bi
        WHERE bi.apar_unit_id = a.id
            AND MONTH(bi.inspection_date) = MONTH(GETDATE())
            AND YEAR(bi.inspection_date) = YEAR(GETDATE())
    );";

    $getTotalAPAROK = sqlsrv_query($koneksi, $sql);
    if ($getTotalAPAROK === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalAPAROK, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['totalOK'];
    }
    return 0;
}
function get_total_apar_abnormal()
{
    global $koneksi;

    $sql = "SELECT COUNT(*) AS total_abnormal
            FROM [apar].[dbo].[apars] a
            WHERE a.expired_date <= GETDATE()
            OR (a.status <> 'OK'
            AND EXISTS (
                SELECT 1 
                FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.apar_unit_id = a.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            ));";

    $getTotalAPARAbnormal = sqlsrv_query($koneksi, $sql);
    if ($getTotalAPARAbnormal === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalAPARAbnormal, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_abnormal'];
    }
    return 0;
}
function get_apar_abnormal_cases()
{
    global $koneksi;

    $sql = "SELECT 
            aac.id,
            aac.apar_id,
            aac.abnormal_case,
            aac.countermeasure,
            aac.due_date,
            aac.repair_photo,
            aac.status,
            aac.created_at,
            a.code,
            a.location,
            a.area,
            aac.pic_id,
            u.name as pic_name,
            u.photo as pic_photo
            FROM [apar].[dbo].[apar_abnormal_cases] aac
            LEFT JOIN [apar].[dbo].[apars] a ON aac.apar_id = a.id
            LEFT JOIN [apar].[dbo].[users] u ON aac.pic_id = u.id
            ORDER BY CASE WHEN aac.status='Verified' THEN 1 ELSE 0 END ASC, aac.created_at DESC";

    $result = sqlsrv_query($koneksi, $sql);
    $data = [];
    
    if ($result !== false) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    
    return $data;
}
// end apar

function get_total_hydrant()
{
    global $koneksi;

    $getTotalAPAR = sqlsrv_query($koneksi, "SELECT COUNT(*) AS total_hydrant FROM [apar].[dbo].[hydrants]");
    if ($getTotalAPAR === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalAPAR, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_hydrant'];
    }
    return 0;
}
function get_total_hydrant_proses()
{
    global $koneksi;

    $sql = "SELECT COUNT(*) AS total_hydrant_proses
            FROM [apar].[dbo].[hydrants] h
            WHERE NOT EXISTS (
                SELECT 1 
                FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.hydrant_unit_id = h.id
                AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            );";

    $getTotalHydrantProses = sqlsrv_query($koneksi, $sql);
    if ($getTotalHydrantProses === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalHydrantProses, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_hydrant_proses'];
    }
    return 0;
}
function get_total_hydrant_ok()
{
    global $koneksi;

    $sql = "SELECT COUNT(*) AS total_hydrant_ok
            FROM [apar].[dbo].[hydrants] h
            WHERE h.status = 'Good'
            AND EXISTS (
                SELECT 1 
                FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.hydrant_unit_id = h.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            );";

    $getTotalHydrantOK = sqlsrv_query($koneksi, $sql);
    if ($getTotalHydrantOK === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalHydrantOK, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_hydrant_ok'];
    }
    return 0;
}
function get_total_hydrant_abnormal()
{
    global $koneksi;

    $sql = "SELECT COUNT(*) AS total_hydrant_abnormal
            FROM [apar].[dbo].[hydrants] h
            WHERE h.status <> 'Good'
            AND EXISTS (
                SELECT 1 
                FROM [apar].[dbo].[bimonthly_inspections] bi
                WHERE bi.hydrant_id = h.id
                    AND MONTH(bi.inspection_date) = MONTH(GETDATE())
                    AND YEAR(bi.inspection_date) = YEAR(GETDATE())
            );";

    $getTotalHydrantAbnormal = sqlsrv_query($koneksi, $sql);
    if ($getTotalHydrantAbnormal === false) {
        return 0;
    }
    $stmt = sqlsrv_fetch_array($getTotalHydrantAbnormal, SQLSRV_FETCH_ASSOC);
    if ($stmt) {
        return $stmt['total_hydrant_abnormal'];
    }
    return 0;
}
function get_hydrant_abnormal_cases()
{
    global $koneksi;

    $sql = "SELECT 
            hac.id,
            hac.hydrant_id,
            hac.abnormal_case,
            hac.countermeasure,
            hac.due_date,
            hac.repair_photo,
            hac.status,
            hac.created_at,
            h.code,
            h.location,
            h.area,
            hac.pic_id,
            u.name as pic_name,
            u.photo as pic_photo
            FROM [apar].[dbo].[hydrant_abnormal_cases] hac
            LEFT JOIN [apar].[dbo].[hydrants] h ON hac.hydrant_id = h.id
            LEFT JOIN [apar].[dbo].[users] u ON hac.pic_id = u.id
            ORDER BY CASE WHEN hac.status='Verified' THEN 1 ELSE 0 END ASC, hac.created_at DESC";

    $result = sqlsrv_query($koneksi, $sql);
    $data = [];
    
    if ($result !== false) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    
    return $data;
}

?>