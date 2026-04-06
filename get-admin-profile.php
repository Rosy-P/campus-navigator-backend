<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();
// ✅ CORS headers (fixed)
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
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Session missing. Please login again."
    ]);
    exit();
}
require_once 'db.php';
$userId = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo json_encode([
            "status" => "success",
            "admin" => $user
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Admin user not found for ID: " . $userId
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
$conn->close();
?>