<?php
include(__DIR__ . '/config/db_koneksi.php');

$type = isset($_GET['type']) ? $_GET['type'] : 'apar';
$ids_str = isset($_GET['ids']) ? $_GET['ids'] : '';
$ids_array = array_filter(explode(',', $ids_str));

if (empty($ids_array)) {
    die("<div style='text-align:center; padding:50px;'><h2>No IDs provided for printing.</h2></div>");
}

$ids = array_map('intval', $ids_array);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$asset_type = strtoupper($type);

// Unified query from MASTER
$sql = "SELECT id, asset_code as code, location, model_type as type, weight, asset_type 
        FROM [PRD].[dbo].[SE_FIRE_PROTECTION_MASTER] 
        WHERE id IN ($placeholders) AND asset_type = ?";

$params = array_merge($ids, [$asset_type]);
$stmt = sqlsrv_query($koneksi, $sql, $params);

$data = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
        $data[] = $row;
} else {
    die("Database Error: " . print_r(sqlsrv_errors(), true));
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("print_qr.php", "", $_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print QR Labels</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap');
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                margin: 0;
                size: portrait;
            }

            body {
                background: white !important;
            }
        }

        body {
            background: #f0f2f5;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .qr-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            background: white;
            padding: 30px;
            border-radius: 12px;
        }

        .qr-item {
            border: 1px solid #ddd;
            padding: 20px;
            width: 280px;
            text-align: center;
            border-radius: 8px;
        }

        .qr-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .info-row {
            font-size: 1rem;
            margin-bottom: 5px;
            text-align: left;
        }

        .qr-image {
            width: 160px;
            height: 160px;
            margin-top: 15px;
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <button class="btn-print no-print" onclick="window.print()">Print Labels</button>
    <div class="qr-container">
        <?php foreach ($data as $item): ?>
            <div class="qr-item">
                <div class="qr-title"><?php echo $item['code']; ?></div>
                <div class="info-row"><strong>Loc:</strong> <?php echo $item['location']; ?></div>
                <div class="info-row"><strong>Type:</strong> <?php echo $item['type'] ?: 'N/A'; ?></div>
                <?php if ($item['asset_type'] === 'APAR'): ?>
                    <div class="info-row"><strong>Weight:</strong> <?php echo $item['weight'] ?: '-'; ?> Kg</div>
                <?php endif; ?>
                <?php
                $page = ($item['asset_type'] === 'HYDRANT') ? 'hydrant-detail' : 'apar-detail';
                $qr_url = $base_url . "index.php?page=" . $page . "&id=" . $item['id'];
                ?>
                <img src="actions/ac_generate_qrcode.php?data=<?php echo urlencode($qr_url); ?>" alt="QR" class="qr-image">
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>