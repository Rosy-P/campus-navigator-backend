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
// For GET/POST requests without body, we check header
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!validate_csrf_token($csrf_token)) {
    send_error_response("Invalid CSRF token", 403);
}

try {
    $query = "DELETE FROM events WHERE created_at < NOW() - INTERVAL 1 DAY";

    if ($conn->query($query)) {
        $deleted_count = $conn->affected_rows;
        echo json_encode([
            "status" => "success",
            "message" => "Cleanup successful",
            "deleted_count" => $deleted_count
        ]);
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    error_log("Cleanup Events Error: " . $e->getMessage());
    send_error_response("Cleanup failed");
}

$conn->close();
?>