<?php
echo "=== TEST ENDPOINT ac_get_filtered_dashboard.php ===\n\n";

$url = "http://localhost/apar/actions/dashboard/ac_get_filtered_dashboard.php";

$response = file_get_contents($url);
$data = json_decode($response, true);

echo "Response:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>
