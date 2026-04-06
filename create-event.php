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
$title = sanitize_input($data['title'] ?? '');
$description = sanitize_input($data['description'] ?? '');
$location = sanitize_input($data['location'] ?? '');
$event_date = sanitize_input($data['event_date'] ?? '');
$event_time = sanitize_input($data['event_time'] ?? '');

if (empty($title) || empty($location) || empty($event_date) || empty($event_time)) {
    send_error_response("Missing required fields", 400);
}

// 4. Input Length Limits
if (strlen($title) > 100)
    send_error_response("Title is too long.", 400);
if (strlen($description) > 1000)
    send_error_response("Description is too long.", 400);

try {
    $stmt = $conn->prepare("INSERT INTO events (title, description, location, event_date, event_time, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $description, $location, $event_date, $event_time, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Event created"]);
    } else {
        throw new Exception("Insert failed");
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Create Event Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>