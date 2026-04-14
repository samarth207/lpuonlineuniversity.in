<?php
// Form submission handler — single endpoint for all forms
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once 'db_config.php';

// Get POST data
$form_type     = trim($_POST['form_type'] ?? '');
$name          = trim($_POST['name'] ?? '');
$email         = trim($_POST['email'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$program       = trim($_POST['program'] ?? '');
$qualification = trim($_POST['qualification'] ?? '');
$message       = trim($_POST['message'] ?? '');
$page_source   = trim($_POST['page_source'] ?? '');

// Validate required fields
if (empty($name) || empty($email) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name, email, and phone are required.']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Validate form_type
$allowed_types = ['enquire', 'apply', 'hero_apply', 'scholarship_apply', 'blog_lead'];
if (!in_array($form_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid form type.']);
    exit;
}

// Get visitor info
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $stmt = $pdo->prepare("INSERT INTO enquiries (form_type, name, email, phone, program, qualification, message, page_source, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $form_type,
        $name,
        $email,
        $phone,
        $program ?: null,
        $qualification ?: null,
        $message ?: null,
        $page_source ?: null,
        $ip_address,
        $user_agent
    ]);

    echo json_encode(['success' => true, 'message' => 'Form submitted successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
