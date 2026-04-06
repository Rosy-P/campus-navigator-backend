<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();
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
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}
require_once 'db.php';
$data = json_decode(file_get_contents("php://input"));
$location = $data->default_location ?? null;
$zoom = $data->default_zoom ?? null;
if (empty($location) || $zoom === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Location and zoom are required"]);
    exit();
}
try {
    $stmt = $conn->prepare("UPDATE system_settings SET default_location = ?, default_zoom = ? WHERE id = 1");
    $stmt->bind_param("si", $location, $zoom);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "System settings updated successfully"]);
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
$conn->close();
?>