<?php
// forgotPassword.php
error_reporting(0);
ini_set('display_errors', 0);
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

require_once 'db.php';

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);
$email = $data['email'] ?? '';

// Validate email format
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "A valid email is required"]);
    exit();
}

// 1. Generate Secure Token & Expiry time safely using random_bytes
$token = bin2hex(random_bytes(16));

// Expiry set exactly 15 minutes from current server time
$expiry = date("Y-m-d H:i:s", strtotime('+15 minutes'));

// 2. Perform DB update (Safely binding parameters)
$sql = "UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?";
try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $token, $expiry, $email);
    $stmt->execute();
    
    // SECURITY: Whether a row was affected or not DO NOT reveal to frontend
    // to prevent email enumeration attacks.

    // DEMO: Return demo link directly in JSON response body
    // In production, instantiate an SMTP call here (e.g. PHPMailer) instead.
    $resetLink = "https://navigator-tau-three.vercel.app/reset-password?token=" . $token;
    
    echo json_encode([
        "status" => "success", 
        "message" => "If the email is valid, a secure link was sent.",
        "demo_link" => $resetLink // To be removed in production later
    ]);
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Internal database error"]);
}

if (isset($conn)) {
    $conn->close();
}
?>
