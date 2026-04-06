<?php
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

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id'])) {
    $id = (int)$data['id'];
    $name = $conn->real_escape_string(trim($data['name'] ?? ''));
    $category = $conn->real_escape_string(trim($data['category'] ?? ''));
    $description = $conn->real_escape_string(trim($data['description'] ?? ''));
    $status = $conn->real_escape_string(trim($data['status'] ?? 'Open'));
    $image_url = $conn->real_escape_string(trim($data['image'] ?? ''));
    $latitude = (float)($data['latitude'] ?? 0);
    $longitude = (float)($data['longitude'] ?? 0);
    $hours = $conn->real_escape_string(trim($data['hours'] ?? ''));
    $phone = $conn->real_escape_string(trim($data['phone'] ?? ''));

    $sql = "UPDATE facilities SET name=?, category=?, description=?, status=?, image_url=?, latitude=?, longitude=?, hours=?, phone=? WHERE id=?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssddssi", $name, $category, $description, $status, $image_url, $latitude, $longitude, $hours, $phone, $id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "status" => "success",
                "message" => "Facility updated successfully."
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
        "message" => "Missing facility ID parameter."
    ]);
}

if (isset($conn)) {
    $conn->close();
}
