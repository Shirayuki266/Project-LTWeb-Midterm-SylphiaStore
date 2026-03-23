<?php
header('Content-Type: application/json');
require_once 'db.php';

$po_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($po_id > 0) {
    // Truy vấn kết hợp bảng chi tiết và bảng sản phẩm để lấy tên sản phẩm
    $sql = "SELECT pod.*, p.name as product_name 
            FROM purchase_order_details pod 
            JOIN products p ON pod.product_id = p.id 
            WHERE pod.purchase_order_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($details);
} else {
    echo json_encode([]);
}