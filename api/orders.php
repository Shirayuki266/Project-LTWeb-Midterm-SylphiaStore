<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_GET['action'] === 'list') {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $conn->prepare("
        SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN orders_items oi ON o.id = oi.donhang_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Total pages
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    $pages = ceil($total / $limit);
    
    echo json_encode([
        'orders' => $orders,
        'pagination' => ['page' => $page, 'pages' => $pages, 'total' => $total]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>