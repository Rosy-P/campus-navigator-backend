<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();
// ✅ CORS headers (fixed for production)
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
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit();
}
require_once 'db.php';
$userId = $_SESSION['user_id'];
// ✅ Get input (JSON or form-data)
$data = json_decode(file_get_contents("php://input"));
$name = $data->name ?? $_POST['name'] ?? null;
$email = $data->email ?? $_POST['email'] ?? null;
if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Name and email are required"
    ]);
    exit();
}
try {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $userId);
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Profile updated successfully"
        ]);
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
$conn->close();
?>