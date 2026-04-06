<?php
    
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
$host = 'sql312.infinityfree.com';
$username = 'if0_41558629';
$password = '78lmGoyC89ylg';
$database = 'if0_41558629_camp_nav';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Attempt connection on default MySQL port
    $conn = new mysqli($host, $username, $password, $database, 3306);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Fallback: Some portable XAMPP setups use 3307
    try {
        $conn = new mysqli($host, $username, $password, $database, 3307);
        $conn->set_charset("utf8mb4");
    } catch (mysqli_sql_exception $e2) {
        error_log("Database Connection Error: " . $e2->getMessage());
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "status" => "error",
            "message" => "Database connection failed. Ensure MySQL is running and credentials match."
        ]);
        exit();
    }
}