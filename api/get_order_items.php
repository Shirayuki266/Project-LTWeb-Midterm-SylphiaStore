<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$order_id = $_GET['order_id'] ?? 0;

if ($order_id > 0) {
    // JOIN với bảng products để lấy tên sản phẩm
    $sql = "SELECT oi.*, p.name as product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($items);
} else {
    echo json_encode([]);
}