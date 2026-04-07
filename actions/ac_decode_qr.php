<?php
// Autoloader manual untuk Libern dan Zxing
spl_autoload_register(function ($class) {
    if (strpos($class, 'Libern\QRCodeReader\\') === 0) {
        $file = __DIR__ . '/../assets/vendor/libern/qr-code-reader/src/' . str_replace('\\', '/', substr($class, 20)) . '.php';
        if (file_exists($file)) require $file;
    } elseif (strpos($class, 'Zxing\\') === 0) {
        $file = __DIR__ . '/../assets/vendor/libern/qr-code-reader/src/lib/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) require $file;
    }
});

// Load file common function PHP
$commonFunctions = __DIR__ . '/../assets/vendor/libern/qr-code-reader/src/lib/common/customFunctions.php';
if (file_exists($commonFunctions)) {
    require_once $commonFunctions;
}

use Libern\QRCodeReader\QRCodeReader;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qr_image'])) {
    $file = $_FILES['qr_image'];
    $tmp_name = $file['tmp_name'];

    // Cek format image
    if ($file['error'] === UPLOAD_ERR_OK) {
        try {
            $QRCodeReader = new QRCodeReader();
            $qrcode_text = $QRCodeReader->decode($tmp_name);
            
            if ($qrcode_text) {
                echo json_encode(['success' => true, 'text' => $qrcode_text]);
            } else {
                echo json_encode(['success' => false, 'message' => 'QR Code tidak ditemukan pada gambar. Pastikan gambar jelas dan fokus.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $file['error']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No image provided']);
}
