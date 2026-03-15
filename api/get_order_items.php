<?php
require_once 'db.php';
header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id > 0) {
    // CHÚ Ý: Dùng order_items (đã sửa lỗi Table doesn't exist)
    $stmt = $conn->prepare("
        SELECT oi.*, p.name AS product_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
} else {
    echo json_encode([]);
}