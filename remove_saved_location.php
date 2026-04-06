<?php
/**
 * remove_saved_location.php
 * Endpoint to remove a saved location.
 * POST JSON: { id, user_id }
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
require_once 'security.php';

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

$id = isset($data['id']) ? (int)$data['id'] : null;
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($data['user_id']) ? (int)$data['user_id'] : null);

if (!$id || !$user_id) {
    echo json_encode(["status" => "error", "message" => "Missing required fields: id or user_id"]);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM saved_locations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Location not found or unauthorized"]);
        }
    } else {
        throw new Exception("Delete failed: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
