<?php
/**
 * save_locations.php
 * Endpoint to save a location for a specific user.
 * POST JSON: { user_id, name, block, floor, latitude, longitude }
 */
// ✅ EXACT origin (NOT *)
header("Access-Control-Allow-Origin: https://navigator-tau-three.vercel.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit();
}

$inputJson = file_get_contents('php://input');
$data = json_decode($inputJson, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON input"]);
    exit();
}

$user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
$name = isset($data['name']) ? trim($data['name']) : null;
$block = isset($data['block']) ? trim($data['block']) : "";
$floor = isset($data['floor']) ? trim($data['floor']) : "";
$latitude = isset($data['latitude']) ? (double)$data['latitude'] : null;
$longitude = isset($data['longitude']) ? (double)$data['longitude'] : null;

if (!$user_id || !$name || $latitude === null || $longitude === null) {
    echo json_encode(["success" => false, "error" => "Missing required fields: user_id, name, latitude, or longitude"]);
    exit();
}

try {
    // Check for duplicate for this user
    $checkStmt = $conn->prepare("SELECT id FROM saved_locations WHERE user_id = ? AND name = ? AND block = ? AND floor = ?");
    $checkStmt->bind_param("isss", $user_id, $name, $block, $floor);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Location already saved"]);
        exit();
    }

    // Insert new record
    $insertStmt = $conn->prepare("INSERT INTO saved_locations (user_id, name, block, floor, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("isssdd", $user_id, $name, $block, $floor, $latitude, $longitude);
    
    if ($insertStmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        throw new Exception("Insert failed: " . $insertStmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
