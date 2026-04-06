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

if (isset($_SESSION['user_id'])) {
    require_once 'db.php';

    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['status'] === 'suspended') {
            session_destroy();
            echo json_encode(["authenticated" => false, "message" => "Account suspended"]);
            exit();
        }
    }

    echo json_encode([
        "authenticated" => true,
        "user" => [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email'],
            "role" => $_SESSION['role']
        ],
        "csrf_token" => generate_csrf_token()
    ]);
} else {
    echo json_encode(["authenticated" => false]);
}
?>