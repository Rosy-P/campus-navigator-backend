<?php
// resetPassword.php
error_reporting(0);
ini_set('display_errors', 0);
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

require_once 'db.php';

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

$token = $data['token'] ?? '';
$new_password = $data['password'] ?? '';

if (empty($token) || empty($new_password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Token and new password are required"]);
    exit();
}

// 1. Authenticate token matching and lifespan
$current_time = date("Y-m-d H:i:s");
$sql = "SELECT id FROM users WHERE reset_token = ? AND token_expiry > ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // 2. Hash New pass with native highly-secure algorithm BCRYPT
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // 3. Complete Action -> Save and Flush Token lifecycle
        $update_sql = "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Password securely updated."]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update profile constraint"]);
        }
        $update_stmt->close();
    } else {
        http_response_code(400);
        // Do not describe specifically whether it is expired OR invalid for better security masking
        echo json_encode(["status" => "error", "message" => "This link is either invalid or expired."]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal database error"]);
}

if (isset($conn)) {
    $conn->close();
}
?>
