<?php
// ✅ EXACT origin (NOT *)
// ✅ CORS headers
header("Access-Control-Allow-Origin: https://navigator-tau-three.vercel.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';
require_once 'security.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_GET['user_id']) ? intval($_GET['user_id']) : 0);
$name = isset($_GET['name']) ? $_GET['name'] : '';
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;

if ($user_id <= 0 || (empty($name) && ($lat == 0 || $lng == 0))) {
    echo json_encode(["is_saved" => false, "error" => "Invalid parameters"]);
    exit;
}

// Check by name and user_id primarily
$stmt = $conn->prepare("SELECT id FROM saved_locations WHERE user_id = ? AND name = ? LIMIT 1");
$stmt->bind_param("is", $user_id, $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["is_saved" => true]);
} else {
    // Fallback: Check by coordinates with a small tolerance
    $stmt = $conn->prepare("SELECT id FROM saved_locations WHERE user_id = ? AND ABS(latitude - ?) < 0.0001 AND ABS(longitude - ?) < 0.0001 LIMIT 1");
    $stmt->bind_param("idd", $user_id, $lat, $lng);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(["is_saved" => true]);
    } else {
        echo json_encode(["is_saved" => false]);
    }
}

$stmt->close();
$conn->close();
?>
