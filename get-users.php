<?php
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

require_once 'security.php';
require_once 'db.php';

// 1. Role Check
require_role(['admin', 'superadmin']);

try {
    $query = "SELECT id, name, email, role, status, created_at FROM users ORDER BY name ASC";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed");
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            "id" => (int) $row["id"],
            "name" => sanitize_input($row["name"]),
            "email" => sanitize_input($row["email"]),
            "role" => sanitize_input($row["role"]),
            "status" => sanitize_input($row["status"]),
            "created_at" => $row["created_at"]
        ];
    }

    echo json_encode([
        "status" => "success",
        "users" => $users
    ]);

} catch (Exception $e) {
    error_log("Get Users Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>