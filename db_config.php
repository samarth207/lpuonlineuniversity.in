<?php
// Database Configuration for Hostinger
// Update these values with your Hostinger MySQL credentials
$db_host = 'localhost';          // Usually 'localhost' on Hostinger
$db_name = 'u261758575_lpu';       // Your database name
$db_user = 'u261758575_lpu';       // Your database username
$db_pass = 'm3@G$HxmAr?C';                   // Your database password — FILL THIS IN

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
