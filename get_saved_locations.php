<?php
/**
 * get_saved_locations.php
 * Fetch saved locations for a user
 */
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();
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
require_once 'security.php';
// ✅ Get user_id from session OR query param
$user_id = isset($_SESSION['user_id']) 
    ? (int)$_SESSION['user_id'] 
    : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : null);
if (!$user_id) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing parameter: user_id"
    ]);
    exit();
}
try {
    $sql = "SELECT 
                sl.id, 
                sl.name, 
                sl.block, 
                sl.floor, 
                sl.latitude, 
                sl.longitude,
                f.image_url,
                f.category,
                f.status,
                sl.created_at
            FROM saved_locations sl
            LEFT JOIN facilities f ON 
                (ABS(sl.latitude - f.latitude) < 0.0001 AND 
                 ABS(sl.longitude - f.longitude) < 0.0001)
            WHERE sl.user_id = ?
            ORDER BY sl.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $saved_locations = [];
    while ($row = $result->fetch_assoc()) {
        $saved_locations[] = $row;
    }
    echo json_encode([
        "status" => "success",
        "data" => $saved_locations
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
$conn->close();
?>