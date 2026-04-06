<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();
// ✅ CORS headers (fixed)
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
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit();
}
require_once 'db.php';
try {
    // 1. Ensure settings table exists and has default row
    $conn->query("CREATE TABLE IF NOT EXISTS system_settings (
        id INT PRIMARY KEY DEFAULT 1,
        default_location VARCHAR(100) DEFAULT 'Main Gate',
        default_zoom INT DEFAULT 17,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $checkRow = $conn->query("SELECT id FROM system_settings WHERE id = 1");
    if ($checkRow->num_rows === 0) {
        $conn->query("INSERT INTO system_settings (id, default_location, default_zoom) VALUES (1, 'Main Gate', 17)");
    }
    // 2. Fetch settings
    $settingsRes = $conn->query("SELECT * FROM system_settings WHERE id = 1");
    $settings = $settingsRes->fetch_assoc();
    // 3. Fetch statistics
    $userCountRes = $conn->query("SELECT COUNT(*) as count FROM users");
    $eventCountRes = $conn->query("SELECT COUNT(*) as count FROM events");
    $locationCountRes = $conn->query("SELECT COUNT(*) as count FROM facilities");
    $stats = [
        "users" => $userCountRes->fetch_assoc()['count'],
        "events" => $eventCountRes->fetch_assoc()['count'],
        "locations" => $locationCountRes->fetch_assoc()['count']
    ];
    echo json_encode([
        "status" => "success",
        "settings" => $settings,
        "stats" => $stats
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
$conn->close();
?>