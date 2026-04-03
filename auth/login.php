<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');

$data = getRequestData();
$email = strtolower(cleanText($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    jsonResponse(['error' => 'Email and password are required.'], 422);
}

$conn = getDbConnection();
$stmt = $conn->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    jsonResponse(['error' => 'Invalid email or password.'], 401);
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
];

jsonResponse([
    'success' => true,
    'message' => 'Login successful.',
    'user' => $_SESSION['user'],
]);
