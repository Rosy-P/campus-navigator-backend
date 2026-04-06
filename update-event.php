<?php
require_once 'security.php';
require_once 'db.php';
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
// 1. Role Check
require_role(['admin', 'superadmin']);

// 2. CSRF Validation
$data = json_decode(file_get_contents("php://input"), true);
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $data['csrf_token'] ?? '';

if (!validate_csrf_token($csrf_token)) {
    send_error_response("Invalid CSRF token", 403);
}

$id = (int)($data['id'] ?? 0);
$title = sanitize_input($data['title'] ?? '');
$description = sanitize_input($data['description'] ?? '');
$location = sanitize_input($data['location'] ?? '');
$event_date = sanitize_input($data['event_date'] ?? '');
$event_time = sanitize_input($data['event_time'] ?? '');

if (!$id || empty($title) || empty($location)) {
    send_error_response("Missing required fields", 400);
}

try {
    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, location=?, event_date=?, event_time=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $description, $location, $event_date, $event_time, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Event updated"]);
    } else {
        throw new Exception("Update failed");
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Update Event Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>