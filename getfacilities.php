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

try {
    $category = $_GET['category'] ?? '';
    $openOnly = isset($_GET['open']) && $_GET['open'] === 'true';
    $search = $_GET['search'] ?? '';

    $sql = "SELECT * FROM facilities WHERE 1=1";

    if (!empty($category) && $category !== 'All') {
        $sql .= " AND category = '" . $conn->real_escape_string($category) . "'";
    }

    if (!empty($search)) {
        $sql .= " AND (name LIKE '%" . $conn->real_escape_string($search) . "%' 
                  OR description LIKE '%" . $conn->real_escape_string($search) . "%')";
    }

    $result = $conn->query($sql);
    $facilities = [];
    date_default_timezone_set("Asia/Kolkata");

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fid = $row['id'];

            // Dynamic Rating Join
            $ratingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM facility_ratings WHERE facility_id = $fid";
            $ratingResult = $conn->query($ratingQuery);
            $ratingData = ($ratingResult && $ratingResult->num_rows > 0) ? $ratingResult->fetch_assoc() : ['avg_rating' => 0, 'total' => 0];

            $row['rating'] = isset($ratingData['avg_rating']) ? round((float) $ratingData['avg_rating'], 1) : 0;
            $row['total_ratings'] = isset($ratingData['total']) ? (int) $ratingData['total'] : 0;

            if ($openOnly && strtolower($row['status']) !== "open") {
                continue;
            }

            $row['image'] = $row['image_url'];
            unset($row['image_url']);
            $row['status'] = ucfirst($row['status']);

            $facilities[] = $row;
        }
    }

    echo json_encode([
        "success" => true,
        "status" => "success",
        "data" => $facilities
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "status" => "error",
        "message" => "Failed to fetch facilities: " . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}