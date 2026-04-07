<?php
header("Content-Type: application/json");
$host = 'maglev.proxy.rlwy.net';
$username = 'root';
$password = 'BZxOqkRtcTIFlFAUdSrZARaDbHDjppUQ';
$database = 'railway';
$port = 40980;

try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    $conn->set_charset("utf8mb4");
    echo json_encode(["status" => "connected", "host" => $host, "port" => $port]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
