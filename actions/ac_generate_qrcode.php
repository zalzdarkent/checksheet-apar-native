<?php
require_once __DIR__ . '/../assets/vendor/phpqrcode/qrlib.php';

// Get data to encode from URL parameter
$data = isset($_GET['data']) ? $_GET['data'] : 'https://google.com';

// Clean the output buffer to avoid any extra characters
if (ob_get_length()) ob_clean();

// Set content type header
header('Content-Type: image/png');

// Use QRcode::png to output the image directly to the browser
// Parameters: data, filename (false to output to browser), EC level, pixel size, frame size
QRcode::png($data, false, QR_ECLEVEL_M, 10, 2);
exit;
?>
