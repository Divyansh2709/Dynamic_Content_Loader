<?php
include "db.php";

try {

// Pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 6;
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Category filter
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build query
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(title LIKE ? OR content LIKE ? OR author LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if ($category !== '') {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get total count
$countSql = "SELECT COUNT(*) AS total FROM posts {$whereClause}";
$countStmt = $conn->prepare($countSql);
if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

// Fetch posts
$sql = "SELECT id, title, content, author, category, created_at FROM posts {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

$paramsCopy = $params;
$paramsCopy[] = $limit;
$paramsCopy[] = $offset;
$fullTypes = $types . 'ii';

$stmt->bind_param($fullTypes, ...$paramsCopy);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();

// Get categories for filter
$catResult = $conn->query("SELECT DISTINCT category FROM posts ORDER BY category");
$categories = [];
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Return JSON response
echo json_encode([
    "posts" => $posts,
    "pagination" => [
        "page" => $page,
        "limit" => $limit,
        "total" => (int)$total,
        "totalPages" => ceil($total / $limit)
    ],
    "categories" => $categories
]);

$conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
