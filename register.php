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
$name = $data->name ?? $_POST['name'] ?? null;
$email = $data->email ?? $_POST['email'] ?? null;
$password = $data->password ?? $_POST['password'] ?? null;

// 1. Basic Validation
if (empty($name) || empty($email) || empty($password)) {
    send_error_response("Incomplete data. Please provide name, email, and password.", 400);
}

// 2. Input Length Limits
if (strlen($name) > 50)
    send_error_response("Name is too long (max 50).", 400);
if (strlen($email) > 100)
    send_error_response("Email is too long (max 100).", 400);

// 3. Email Validation
if (!is_valid_email($email)) {
    send_error_response("Invalid email format.", 400);
}

// 4. Password Strength Validation
if (!is_strong_password($password)) {
    send_error_response("Password must be at least 8 characters long and contain at least one letter and one number.", 400);
}

// 5. Sanitize remaining inputs
$name = sanitize_input($name);
$email = sanitize_input($email);

try {
    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        send_error_response("Email already exists.", 409);
    }
    $stmt->close();

    // 6. Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insert_query = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')";
    $stmt = $conn->prepare($insert_query);

    // Bind parameters
    $stmt->bind_param("sss", $name, $email, $password_hash);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "User registered successfully."]);
    } else {
        throw new Exception("Insert failed");
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>