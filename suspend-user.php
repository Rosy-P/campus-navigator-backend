<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');

session_start();

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

/*
|--------------------------------------------------------------------------
| AUTHORIZATION CHECK
|--------------------------------------------------------------------------
| Allow admin OR superadmin
*/

if (!isset($_SESSION['user_id']) || 
   !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

/*
|--------------------------------------------------------------------------
| VALIDATE INPUT
|--------------------------------------------------------------------------
*/

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
    exit();
}

$userId = intval($data['user_id']);
$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];

/*
|--------------------------------------------------------------------------
| PREVENT SELF SUSPENSION
|--------------------------------------------------------------------------
*/

if ($userId === $currentUserId) {
    echo json_encode([
        "status" => "error",
        "message" => "You cannot suspend your own account."
    ]);
    exit();
}

/*
|--------------------------------------------------------------------------
| GET TARGET USER DETAILS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT role, status FROM users WHERE id = ?");
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
$targetRole = $targetUser['role'];
$targetStatus = $targetUser['status'];

$stmt->close();

/*
|--------------------------------------------------------------------------
| PREVENT SUSPENDING SUPERADMIN
|--------------------------------------------------------------------------
*/

if ($targetRole === 'superadmin') {
    echo json_encode([
        "status" => "error",
        "message" => "Superadmin account cannot be suspended."
    ]);
    exit();
}

/*
|--------------------------------------------------------------------------
| PREVENT LAST ACTIVE ADMIN FROM BEING SUSPENDED
|--------------------------------------------------------------------------
*/

if ($targetRole === 'admin' && $targetStatus === 'active') {

    $countAdmins = $conn->query("
        SELECT COUNT(*) as total 
        FROM users 
        WHERE role='admin' AND status='active'
    ");

    $row = $countAdmins->fetch_assoc();

    if ($row['total'] <= 1) {
        echo json_encode([
            "status" => "error",
            "message" => "Cannot suspend the last active admin."
        ]);
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| TOGGLE STATUS
|--------------------------------------------------------------------------
*/

$newStatus = ($targetStatus === 'active') ? 'suspended' : 'active';

$updateStmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$updateStmt->bind_param("si", $newStatus, $userId);

if ($updateStmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "User status updated successfully.",
        "new_status" => $newStatus
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update status."
    ]);
}

$updateStmt->close();
$conn->close();
?>