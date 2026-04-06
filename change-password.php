<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();
// ✅ CORS headers
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
// ... rest of your code
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}
require_once 'db.php';
$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"));
$oldPassword = $data->oldPassword ?? null;
$newPassword = $data->newPassword ?? null;
if (empty($oldPassword) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Old and new passwords are required"]);
    exit();
}
try {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) {
        throw new Exception("User not found");
    }
    $user = $result->fetch_assoc();
    if (!password_verify($oldPassword, $user['password'])) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Incorrect old password"]);
        exit();
    }
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newHash, $userId);
    if ($updateStmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully"]);
    } else {
        throw new Exception("Update failed: " . $updateStmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
?>