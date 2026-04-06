<?php
require_once 'security.php';
require_once 'db.php';

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

// 1. Role Check
require_role(['admin', 'superadmin']);

// 2. CSRF Validation
$data = json_decode(file_get_contents("php://input"), true);
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $data['csrf_token'] ?? '';

if (!validate_csrf_token($csrf_token)) {
    send_error_response("Invalid CSRF token", 403);
}

$id = (int)($data['id'] ?? 0);

if (!$id) {
    send_error_response("Invalid event ID", 400);
}

try {
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Event deleted"]);
    } else {
        throw new Exception("Delete failed");
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Delete Event Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>