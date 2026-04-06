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

// Include security and database
require_once 'security.php';
require_once 'db.php';

// Get input data
$data = json_decode(file_get_contents("php://input"));

// Extract variables
$email = $data->email ?? $_POST['email'] ?? null;
$password = $data->password ?? $_POST['password'] ?? null;

// 1. Basic Validation
if (empty($email) || empty($password)) {
    send_error_response("Incomplete data. Please provide email and password.", 400);
}

// 2. Email Validation
if (!is_valid_email($email)) {
    send_error_response("Invalid email format.", 400);
}

// 3. Rate Limiting Check
if (!check_rate_limit($email)) {
    send_error_response("Too many login attempts. Please try again after 1 minute.", 429);
}

// 4. Sanitize inputs
$email = sanitize_input($email);

try {
    // Check user credentials
    $query = "SELECT id, name, password, role, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 5. Account Suspension Check
        if (isset($user['status']) && $user['status'] === 'suspended') {
            send_error_response("Your account has been suspended. Please contact support.", 403);
        }

        // 6. Verify password
        if (password_verify($password, $user['password'])) {

            // Success: Regenerate session ID
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = $user['role'];

            // Clear login attempts on success
            unset($_SESSION['login_attempts']);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login successful.",
                "user" => [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $email,
                    "role" => $user['role']
                ],
                "csrf_token" => generate_csrf_token() // Provide token for subsequent stateful requests
            ]);

        } else {
            send_error_response("Invalid credentials.", 401);
        }
    } else {
        send_error_response("Invalid credentials.", 401); // Avoid revealing if account exists
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>