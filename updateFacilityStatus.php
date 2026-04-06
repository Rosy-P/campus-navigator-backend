<?php
require_once 'db.php';
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
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$status = $data['status']; // Open / Closed / Crowded

$stmt = $conn->prepare("UPDATE facilities SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed"]);
}
?>