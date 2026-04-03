<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');
$user = requireAuth();
$data = getRequestData();

$id = (int)($data['id'] ?? 0);
$title = cleanText($data['title'] ?? '');
$content = cleanText($data['content'] ?? '');
$category = cleanText($data['category'] ?? '');

if ($id <= 0 || $title === '' || $content === '' || $category === '') {
    jsonResponse(['error' => 'Valid id, title, content, and category are required.'], 422);
}

if (strlen($title) > 255) {
    jsonResponse(['error' => 'Title must be 255 characters or fewer.'], 422);
}

$conn = getDbConnection();
$ownerCheck = $conn->prepare('SELECT user_id FROM posts WHERE id = ? LIMIT 1');
$ownerCheck->bind_param('i', $id);
$ownerCheck->execute();
$row = $ownerCheck->get_result()->fetch_assoc();
$ownerCheck->close();

if (!$row) {
    jsonResponse(['error' => 'Post not found.'], 404);
}

if ((int)$row['user_id'] !== (int)$user['id']) {
    jsonResponse(['error' => 'You can only edit your own posts.'], 403);
}

$updateStmt = $conn->prepare('UPDATE posts SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?');
$userId = (int)$user['id'];
$updateStmt->bind_param('sssii', $title, $content, $category, $id, $userId);
$updateStmt->execute();
$updateStmt->close();

$getStmt = $conn->prepare('SELECT id, title, content, author, category, created_at, user_id FROM posts WHERE id = ? LIMIT 1');
$getStmt->bind_param('i', $id);
$getStmt->execute();
$post = $getStmt->get_result()->fetch_assoc();
$getStmt->close();

jsonResponse([
    'success' => true,
    'message' => 'Post updated successfully.',
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
]);
