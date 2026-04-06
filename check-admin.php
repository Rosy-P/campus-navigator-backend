<?php

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

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    send_error_response("Not logged in", 401);
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT status, role, name FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['status'] === 'suspended') {
            session_destroy();
            send_error_response("Account suspended", 403);
        }
        // Sync role and name in session
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
    } else {
        send_error_response("User not found", 404);
    }

    if (!in_array($_SESSION['role'], ["admin", "superadmin"])) {
        send_error_response("Access denied", 403);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Admin verified",
        "user" => [
            "id" => $userId,
            "name" => $_SESSION['user_name'],
            "role" => $_SESSION['role']
        ]
    ]);

} catch (Exception $e) {
    error_log("Check Admin Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>