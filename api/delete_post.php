<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

requireMethod('POST');
$user = requireAuth();
$data = getRequestData();

$id = (int)($data['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(['error' => 'Valid post id is required.'], 422);
}

$conn = getDbConnection();
$deleteStmt = $conn->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
$userId = (int)$user['id'];
$deleteStmt->bind_param('ii', $id, $userId);
$deleteStmt->execute();
$affected = $deleteStmt->affected_rows;
$deleteStmt->close();

if ($affected === 0) {
    jsonResponse(['error' => 'Post not found or not owned by you.'], 404);
}

jsonResponse([
    'success' => true,
    'message' => 'Post deleted successfully.',
]);
