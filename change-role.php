<?php
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

/*
|--------------------------------------------------------------------------
| AUTHORIZATION CHECK
|--------------------------------------------------------------------------
| ONLY SUPERADMIN CAN CHANGE ROLES
*/

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Only superadmin can change roles."]);
    exit();
}

/*
|--------------------------------------------------------------------------
| VALIDATE INPUT
|--------------------------------------------------------------------------
*/

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['user_id']) || 
    !is_numeric($data['user_id']) ||
    !isset($data['role'])
) {
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
    exit();
}

$userId = intval($data['user_id']);
$newRole = $data['role'];
$currentUserId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| ALLOWED ROLES
|--------------------------------------------------------------------------
*/

$allowedRoles = ['admin', 'student'];

if (!in_array($newRole, $allowedRoles)) {
    echo json_encode(["status" => "error", "message" => "Invalid role specified."]);
    exit();
}

/*
|--------------------------------------------------------------------------
| PREVENT CHANGING YOUR OWN ROLE
|--------------------------------------------------------------------------
*/

if ($userId === $currentUserId) {
    echo json_encode([
        "status" => "error",
        "message" => "You cannot change your own role."
    ]);
    exit();
}

/*
|--------------------------------------------------------------------------
| GET TARGET USER DETAILS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found."
    ]);
    exit();
}

$targetUser = $result->fetch_assoc();
$currentRoleOfTarget = $targetUser['role'];

$stmt->close();

/*
|--------------------------------------------------------------------------
| PREVENT MODIFYING SUPERADMIN
|--------------------------------------------------------------------------
*/

if ($currentRoleOfTarget === 'superadmin') {
    echo json_encode([
        "status" => "error",
        "message" => "Superadmin role cannot be modified."
    ]);
    exit();
}

/*
|--------------------------------------------------------------------------
| PREVENT REMOVING LAST ADMIN
|--------------------------------------------------------------------------
*/

if ($currentRoleOfTarget === 'admin' && $newRole === 'student') {

    $countAdmins = $conn->query("
        SELECT COUNT(*) as total 
        FROM users 
        WHERE role='admin' AND status='active'
    ");

    $row = $countAdmins->fetch_assoc();

    if ($row['total'] <= 1) {
        echo json_encode([
            "status" => "error",
            "message" => "Cannot remove the last active admin."
        ]);
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE ROLE
|--------------------------------------------------------------------------
*/

$updateStmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
$updateStmt->bind_param("si", $newRole, $userId);

if ($updateStmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Role updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update role."
    ]);
}

$updateStmt->close();
$conn->close();
?>