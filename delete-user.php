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

require_once 'security.php';
require_once 'db.php';

// 1. Role Check
require_role(['admin', 'superadmin']);

// 2. CSRF Validation
$data = json_decode(file_get_contents("php://input"), true);
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $data['csrf_token'] ?? '';

if (!validate_csrf_token($csrf_token)) {
    send_error_response("Invalid CSRF token", 403);
}

// 3. Extract and Validate Input
$userId = isset($data['user_id']) ? intval($data['user_id']) : 0;

if ($userId === 0) {
    send_error_response("Invalid user ID", 400);
}

// Prevent self-deletion
if ($userId === intval($_SESSION['user_id'])) {
    send_error_response("You cannot delete your own account.", 400);
}

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User deleted"]);
    } else {
        throw new Exception("Delete failed");
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Delete User Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>