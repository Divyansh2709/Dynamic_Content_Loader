<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');

$data = getRequestData();
$name = cleanText($data['name'] ?? '');
$email = strtolower(cleanText($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    jsonResponse(['error' => 'Name, email, and password are required.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['error' => 'Please provide a valid email address.'], 422);
}

if (strlen($password) < 6) {
    jsonResponse(['error' => 'Password must be at least 6 characters long.'], 422);
}

$conn = getDbConnection();

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($existing) {
    jsonResponse(['error' => 'Email is already registered.'], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$insertStmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$insertStmt->bind_param('sss', $name, $email, $hash);
$insertStmt->execute();
$userId = $insertStmt->insert_id;
$insertStmt->close();

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => $userId,
    'name' => $name,
    'email' => $email,
];

jsonResponse([
    'success' => true,
    'message' => 'Registration successful.',
    'user' => $_SESSION['user'],
], 201);
