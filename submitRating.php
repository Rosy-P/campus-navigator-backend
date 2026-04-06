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

require_once 'db.php';

// Create table if not exists
$tableName = 'facility_ratings';
$createTableSql = "CREATE TABLE IF NOT EXISTS `$tableName` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `facility_id` INT NOT NULL,
    `rating` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
)";
$conn->query($createTableSql);

// Handle POST request
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->facility_id) && !empty($data->rating)) {
    $fid = (int)$data->facility_id;
    $rating = (int)$data->rating;

    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Rating must be between 1 and 5."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO facility_ratings (facility_id, rating) VALUES (?, ?)");
    $stmt->bind_param("ii", $fid, $rating);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Rating submitted successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Submit failed: " . $stmt->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Please provide facility_id and rating."]);
}

$conn->close();
?>
