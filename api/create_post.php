<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');
$user = requireAuth();
$data = getRequestData();

$title = cleanText($data['title'] ?? '');
$content = cleanText($data['content'] ?? '');
$category = cleanText($data['category'] ?? '');

if ($title === '' || $content === '' || $category === '') {
    jsonResponse(['error' => 'Title, content, and category are required.'], 422);
}

if (strlen($title) > 255) {
    jsonResponse(['error' => 'Title must be 255 characters or fewer.'], 422);
}

$conn = getDbConnection();
$stmt = $conn->prepare('INSERT INTO posts (title, content, author, category, user_id) VALUES (?, ?, ?, ?, ?)');
$author = (string)$user['name'];
$userId = (int)$user['id'];
$stmt->bind_param('ssssi', $title, $content, $author, $category, $userId);
$stmt->execute();
$postId = $stmt->insert_id;
$stmt->close();

$getStmt = $conn->prepare('SELECT id, title, content, author, category, created_at, user_id FROM posts WHERE id = ? LIMIT 1');
$getStmt->bind_param('i', $postId);
$getStmt->execute();
$post = $getStmt->get_result()->fetch_assoc();
$getStmt->close();

jsonResponse([
    'success' => true,
    'message' => 'Post created successfully.',
    'post' => [
        'id' => (int)$post['id'],
        'title' => (string)$post['title'],
        'content' => (string)$post['content'],
        'author' => (string)$post['author'],
        'category' => (string)$post['category'],
        'created_at' => $post['created_at'],
        'user_id' => (int)$post['user_id'],
        'can_manage' => true,
    ],
], 201);
