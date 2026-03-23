<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['id'];
    $quantity = (int)$_POST['quantity'];
    $admin_id = $_SESSION['admin_id'] ?? 1; // ID admin đang thực hiện

    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'error' => 'Số lượng nhập phải lớn hơn 0']);
        exit;
    }

    // Bắt đầu Transaction để tránh lỗi dữ liệu không đồng bộ
    $conn->begin_transaction();

    try {
        // BƯỚC 1: Tạo Phiếu nhập (import_receipts)
        // Lưu ý: Kiểm tra lại tên cột trong bảng của bạn (ví dụ: admin_id hay created_by)
        $stmt1 = $conn->prepare("INSERT INTO import_receipts (admin_id, created_at) VALUES (?, NOW())");
        $stmt1->bind_param("i", $admin_id);
        $stmt1->execute();
        $receipt_id = $conn->insert_id;

        // BƯỚC 2: Thêm Chi tiết phiếu nhập (import_items)
        // Giả sử bảng import_items có các cột: import_receipt_id, product_id, quantity
        $stmt2 = $conn->prepare("INSERT INTO import_items (import_receipt_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt2->bind_param("iii", $receipt_id, $product_id, $quantity);
        $stmt2->execute();

        // BƯỚC 3: Cập nhật tồn kho thực tế của sản phẩm
        $stmt3 = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt3->bind_param("ii", $quantity, $product_id);
        $stmt3->execute();

        $conn->commit(); // Xác nhận lưu mọi thay đổi
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback(); // Nếu có lỗi, hủy toàn bộ các bước trên
        echo json_encode(['success' => false, 'error' => "Lỗi hệ thống: " . $e->getMessage()]);
    }
    exit;
}