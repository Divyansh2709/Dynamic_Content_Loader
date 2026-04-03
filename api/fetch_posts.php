<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

function bindDynamicParams(mysqli_stmt $stmt, string $types, array &$values): void
{
    if ($types === '' || count($values) === 0) {
        return;
    }

    $bindArgs = [$types];
    foreach ($values as $index => &$value) {
        $bindArgs[] = &$values[$index];
    }

    if (!call_user_func_array([$stmt, 'bind_param'], $bindArgs)) {
        throw new RuntimeException('Failed to bind statement parameters.');
    }
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

try {
    $conn = getDbConnection();

    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 6;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $search = isset($_GET['search']) ? cleanText($_GET['search']) : '';
    $category = isset($_GET['category']) ? cleanText($_GET['category']) : '';
    $sort = isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'ASC' : 'DESC';
    $mine = isset($_GET['mine']) && $_GET['mine'] === '1';

    $where = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $where[] = '(title LIKE ? OR content LIKE ? OR author LIKE ?)';
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    if ($category !== '') {
        $where[] = 'category = ?';
        $params[] = $category;
        $types .= 's';
    }

    if ($mine) {
        $user = requireAuth();
        $where[] = 'user_id = ?';
        $params[] = (int)$user['id'];
        $types .= 'i';
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    $countSql = "SELECT COUNT(*) AS total FROM posts {$whereClause}";
    $countStmt = $conn->prepare($countSql);
    $countParams = $params;
    bindDynamicParams($countStmt, $types, $countParams);
    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();

    $sql = "SELECT id, title, content, author, category, created_at, user_id FROM posts {$whereClause} ORDER BY created_at {$sort}, id {$sort} LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $queryParams = $params;
    $queryParams[] = $limit;
    $queryParams[] = $offset;
    $queryTypes = $types . 'ii';
    bindDynamicParams($stmt, $queryTypes, $queryParams);
    $stmt->execute();

    $viewer = currentUser();
    $viewerId = $viewer !== null ? (int)$viewer['id'] : 0;

    $posts = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ownerId = isset($row['user_id']) ? (int)$row['user_id'] : 0;
        $posts[] = [
            'id' => (int)$row['id'],
            'title' => (string)$row['title'],
            'content' => (string)$row['content'],
            'author' => (string)$row['author'],
            'category' => (string)$row['category'],
            'created_at' => $row['created_at'],
            'user_id' => $ownerId,
            'can_manage' => $viewerId > 0 && $ownerId > 0 && $ownerId === $viewerId,
        ];
    }
    $stmt->close();

    $categories = [];
    $catResult = $conn->query('SELECT DISTINCT category FROM posts ORDER BY category');
    if ($catResult) {
        while ($row = $catResult->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }

    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 0;
    $currentPage = $limit > 0 ? (int)floor($offset / $limit) + 1 : 1;

    jsonResponse([
        'posts' => $posts,
        'limit' => $limit,
        'offset' => $offset,
        'total' => $total,
        'hasMore' => ($offset + count($posts)) < $total,
        'pagination' => [
            'page' => max(1, $currentPage),
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $totalPages,
        ],
        'categories' => $categories,
    ]);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Failed to fetch posts.'], 500);
}
