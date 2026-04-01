<?php
include(__DIR__ . '/config/db_koneksi.php');

$type = isset($_GET['type']) ? $_GET['type'] : 'apar';
$ids_str = isset($_GET['ids']) ? $_GET['ids'] : '';
$ids_array = array_filter(explode(',', $ids_str));

if (empty($ids_array)) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>No IDs provided for printing.</h2><p>Please select items to print.</p></div>");
}

// Convert to integers to prevent SQL injection
$ids = array_map('intval', $ids_array);

$table = ($type === 'hydrant') ? '[apar].[dbo].[hydrants]' : '[apar].[dbo].[apars]';
$data = [];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("print_qr.php", "", $_SERVER['PHP_SELF']);

$placeholders = implode(',', array_fill(0, count($ids), '?'));
// Select different columns based on type
if ($type === 'hydrant') {
    $sql = "SELECT id, code, location, type FROM $table WHERE id IN ($placeholders)";
} else {
    $sql = "SELECT id, code, location, type, weight FROM $table WHERE id IN ($placeholders)";
}

$stmt = sqlsrv_query($koneksi, $sql, $ids);

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
} else {
    die("Database Error: " . print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Codes - <?php echo ucfirst($type); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        @media print {
            .no-print { display: none !important; }
            @page { 
                margin: 0; 
                size: portrait;
            }
            body { 
                margin: 0; 
                background: white !important;
            }
            .qr-container {
                padding: 10mm !important;
                box-shadow: none !important;
                background: white !important;
                justify-content: center !important;
            }
            .qr-item {
                break-inside: avoid;
                border: 1px solid #ddd !important;
            }
        }
        
        body {
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
        }

        .qr-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            width: 100%;
            max-width: 1100px;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }

        .qr-item {
            border: 1px solid #e0e0e0;
            background: #fff;
            padding: 30px;
            text-align: left;
            position: relative;
            min-height: 380px;
            width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.2s;
        }

        .qr-title {
            font-size: 2rem;
            font-weight: 800;
            color: #000;
            margin-bottom: 25px;
            width: 100%;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .qr-info-section {
            width: 100%;
            margin-bottom: 25px;
            align-self: flex-start;
        }

        .info-row {
            font-size: 1.15rem;
            margin-bottom: 10px;
            line-height: 1.4;
            display: flex;
            gap: 8px;
        }

        .info-row strong {
            font-weight: 700;
            min-width: 75px;
            display: inline-block;
        }

        .qr-image-wrapper {
            margin-top: auto;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .qr-image {
            width: 180px;
            height: 180px;
            image-rendering: pixelated;
        }

        .btn-print-fixed {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1000;
            padding: 14px 30px;
            border-radius: 50px;
            background: #2563eb;
            color: white;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .btn-print-fixed:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .header-info {
            text-align: center;
            margin-bottom: 40px;
            width: 100%;
            max-width: 800px;
        }

        .header-info h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #111827;
        }

        .header-info p {
            color: #6b7280;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <button class="btn-print-fixed no-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Print QR Labels
    </button>

    <div class="header-info no-print">
        <h2><?php echo count($data); ?> Labels Ready</h2>
        <p>Previewing <?php echo ucfirst($type); ?> QR codes in official format.</p>
    </div>

    <div class="qr-container">
        <?php foreach ($data as $item): ?>
            <div class="qr-item">
                <div class="qr-title"><?php echo $item['code']; ?></div>
                
                <div class="qr-info-section">
                    <div class="info-row">
                        <strong>Loc:</strong> 
                        <span><?php echo $item['location']; ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Type:</strong> 
                        <span><?php echo $item['type'] ?: 'N/A'; ?></span>
                    </div>
                    <?php if ($type === 'apar'): ?>
                        <div class="info-row">
                            <strong>Weight:</strong> 
                            <span><?php echo $item['weight'] ?: '-'; ?> Kg</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="qr-image-wrapper">
                    <?php 
                        $detail_page = ($type === 'hydrant') ? 'hydrant-detail' : 'apar-detail';
                        $qr_url = $base_url . "index.php?page=" . $detail_page . "&id=" . $item['id'];
                    ?>
                    <img src="actions/ac_generate_qrcode.php?data=<?php echo urlencode($qr_url); ?>" alt="QR" class="qr-image">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Auto trigger print dialogue on load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
