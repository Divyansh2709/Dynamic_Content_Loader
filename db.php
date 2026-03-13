<?php
// Suppress PHP HTML error output — all errors returned as JSON
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");

$conn = @new mysqli("localhost", "root", "", "demo_db");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");
?>
