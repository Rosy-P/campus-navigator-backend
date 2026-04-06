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

require_once 'security.php';
require_once 'db.php';

try {
    $query = "
        SELECT 
            e.id,
            e.title,
            e.description,
            e.location,
            e.event_date,
            e.event_time,
            e.latitude,
            e.longitude,
            e.created_by,
            u.name AS creator_name
        FROM events e
        LEFT JOIN users u ON e.created_by = u.id
        WHERE e.created_at >= NOW() - INTERVAL 1 DAY
        ORDER BY e.event_date ASC, e.event_time ASC
    ";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed");
    }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            "id" => (int) $row["id"],
            "title" => sanitize_input($row["title"]),
            "description" => sanitize_input($row["description"]),
            "location" => sanitize_input($row["location"]),
            "event_date" => $row["event_date"],
            "event_time" => $row["event_time"],
            "latitude" => (float) $row["latitude"],
            "longitude" => (float) $row["longitude"],
            "created_by" => (int) $row["created_by"],
            "creator_name" => sanitize_input($row["creator_name"] ?? "Unknown")
        ];
    }

    echo json_encode([
        "status" => "success",
        "events" => $events
    ]);

} catch (Exception $e) {
    error_log("Get Events Error: " . $e->getMessage());
    send_error_response();
}

$conn->close();
?>