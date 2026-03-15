<?php
session_start();
header('Content-Type: application/json');

// 1. Kiểm tra đăng nhập Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Bạn không có quyền truy cập']);
    exit();
}

require_once 'db.php'; // Đảm bảo đường dẫn này đúng tới file kết nối database của bạn

// 2. Kiểm tra dữ liệu đầu vào
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $quantity_to_add = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if ($product_id <= 0 || $quantity_to_add <= 0) {
        echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ (ID hoặc số lượng phải lớn hơn 0)']);
        exit();
    }

    try {
    // Sử dụng INSERT ... ON DUPLICATE KEY UPDATE 
    // Nếu sản phẩm chưa có trong bảng inventory thì thêm mới, nếu có rồi thì cộng dồn stock
    $sql = "INSERT INTO inventory (product_id, stock) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE stock = stock + ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $product_id, $quantity_to_add, $quantity_to_add);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Lỗi SQL: ' . $conn->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
} else {
    echo json_encode(['success' => false, 'error' => 'Phương thức yêu cầu không hợp lệ']);
}