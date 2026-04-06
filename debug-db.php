<?php
require_once 'db.php';
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

try {
    $result = $conn->query("DESCRIBE users");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }

    $settingsTable = $conn->query("SHOW TABLES LIKE 'system_settings'");
    $settingsExists = $settingsTable->num_rows > 0;

    echo json_encode([
        "status" => "success",
        "users_table" => $columns,
        "system_settings_exists" => $settingsExists
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>