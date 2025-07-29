<?php
// Handle contact form submission and save to contact_messages table
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($name === '' || $email === '' || $message === '') {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // Insert into contact_messages, allow user_id to be null
    $sql = "INSERT INTO contact_messages (user_id, name, email, status, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$user_id, $name, $email, $status, $message]);
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Thank you for contacting us! We will get back with email to you soon.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
    }
    exit;
}
// If not POST, show error
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
