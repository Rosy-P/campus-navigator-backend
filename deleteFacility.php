<?php
error_reporting(0);
ini_set('display_errors', 0);
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
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id'])) {
    $id = (int)$data['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM facilities WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "status" => "success",
                "message" => "Facility successfully deleted."
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
        "message" => "Missing facility ID."
    ]);
}

if (isset($conn)) {
    $conn->close();
}
