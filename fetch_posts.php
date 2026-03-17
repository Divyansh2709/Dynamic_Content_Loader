<?php
include "db.php";

function bindDynamicParams(mysqli_stmt $stmt, string $types, array &$values): void {
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

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 6;
    $offset = ($page - 1) * $limit;

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $sort = isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'ASC' : 'DESC';

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

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    $countSql = "SELECT COUNT(*) AS total FROM posts {$whereClause}";
    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) {
        throw new RuntimeException('Failed to prepare count query.');
    }

    $countParams = $params;
    bindDynamicParams($countStmt, $types, $countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = (int)$countResult->fetch_assoc()['total'];
    $countStmt->close();

    $sql = "SELECT id, title, content, author, category, created_at FROM posts {$whereClause} ORDER BY created_at {$sort}, id {$sort} LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare posts query.');
    }

    $queryParams = $params;
    $queryParams[] = $limit;
    $queryParams[] = $offset;
    $queryTypes = $types . 'ii';

    bindDynamicParams($stmt, $queryTypes, $queryParams);
    $stmt->execute();

    $result = $stmt->get_result();
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();

    $categories = [];
    $catResult = $conn->query('SELECT DISTINCT category FROM posts ORDER BY category');
    if ($catResult) {
        while ($row = $catResult->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }

    echo json_encode([
        'posts' => $posts,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int)ceil($total / $limit) : 0
        ],
        'categories' => $categories
    ]);

    $conn->close();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>