<?php
// ✅ Session config (MUST be before session_start)
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

// ✅ Start session
session_start();

// ✅ CORS headers
header("Access-Control-Allow-Origin: https://navigator-tau-three.vercel.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ (Optional) Hide errors in production
error_reporting(0);
ini_set('display_errors', 0);

require_once 'db.php';

// ✅ Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['name']) && !empty($data['category'])) {

    $name = $conn->real_escape_string(trim($data['name']));
    $category = $conn->real_escape_string(trim($data['category']));
    $description = $conn->real_escape_string(trim($data['description'] ?? ''));
    $status = $conn->real_escape_string(trim($data['status'] ?? 'Open'));
    $image_url = $conn->real_escape_string(trim($data['image'] ?? ''));
    $latitude = (float)($data['latitude'] ?? 0);
    $longitude = (float)($data['longitude'] ?? 0);
    $hours = $conn->real_escape_string(trim($data['hours'] ?? '09:00 - 17:00'));
    $phone = $conn->real_escape_string(trim($data['phone'] ?? 'N/A'));

    $sql = "INSERT INTO facilities 
            (name, category, description, status, image_url, latitude, longitude, hours, phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssddss", $name, $category, $description, $status, $image_url, $latitude, $longitude, $hours, $phone);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "status" => "success",
                "id" => $conn->insert_id,
                "message" => "Facility added successfully."
            ]);
        } else {
            throw new Exception($stmt->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "status" => "error",
        "message" => "Incomplete data. Name and category required."
    ]);
}

$conn->close();