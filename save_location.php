<?php
// htdocs/campus-navigator-backend/save_location.php

// CORS Headers
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
require_once 'security.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

// Read and decode JSON input
$inputJson = file_get_contents('php://input');
$data = json_decode($inputJson, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit();
}

// Required fields
$name = isset($data['name']) ? $data['name'] : null;
$block = isset($data['block']) ? $data['block'] : null;
$floor = isset($data['floor']) ? $data['floor'] : null;
$latitude = isset($data['latitude']) ? (double)$data['latitude'] : null;
$longitude = isset($data['longitude']) ? (double)$data['longitude'] : null;
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if (!$name || $latitude === null || $longitude === null || !$user_id) {
    echo json_encode(["status" => "error", "message" => "Missing required fields or not logged in"]);
    exit();
}

try {
    // Check for duplicate
    $checkStmt = $conn->prepare("SELECT id FROM saved_locations WHERE user_id = ? AND name = ? AND block = ? AND floor = ?");
    $checkStmt->bind_param("isss", $user_id, $name, $block, $floor);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Location already saved"]);
        exit();
    }

    // Insert new record
    $insertStmt = $conn->prepare("INSERT INTO saved_locations (user_id, name, block, floor, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("isssdd", $user_id, $name, $block, $floor, $latitude, $longitude);
    
    if ($insertStmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Location saved successfully"]);
    } else {
        throw new Exception("Execute failed: " . $insertStmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
