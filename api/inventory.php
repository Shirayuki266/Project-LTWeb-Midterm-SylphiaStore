<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Phương thức không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$product_id = (int)($_POST['id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Sản phẩm không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'error' => 'Số lượng nhập phải lớn hơn 0.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn->begin_transaction();

try {
    // Lấy giá vốn hiện tại để lưu vào chi tiết phiếu nhập nhanh
    $stmtProduct = $conn->prepare("SELECT cost_price FROM products WHERE id = ?");
    $stmtProduct->bind_param('i', $product_id);
    $stmtProduct->execute();
    $product = $stmtProduct->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm cần nhập kho.');
    }

    $import_price = (float)($product['cost_price'] ?? 0);

    // Ghi nhận 1 phiếu nhập hoàn thành để đồng bộ lịch sử với toàn hệ thống
    $supplier_name = 'Nhập kho nhanh (QLKho)';
    $total_amount = $import_price * $quantity;
    $status = 'completed';

    $stmtOrder = $conn->prepare("INSERT INTO purchase_orders (supplier_name, total_amount, status, created_at) VALUES (?, ?, ?, NOW())");
    $stmtOrder->bind_param('sds', $supplier_name, $total_amount, $status);
    $stmtOrder->execute();
    $order_id = $conn->insert_id;

    $stmtDetail = $conn->prepare("INSERT INTO purchase_order_details (purchase_order_id, product_id, quantity, import_price) VALUES (?, ?, ?, ?)");
    $stmtDetail->bind_param('iiid', $order_id, $product_id, $quantity, $import_price);
    $stmtDetail->execute();

    // Tăng tồn kho trực tiếp
    $stmtStock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    $stmtStock->bind_param('ii', $quantity, $product_id);
    $stmtStock->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Đã nhập thêm hàng thành công.',
        'purchase_order_id' => $order_id
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => 'Lỗi hệ thống: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}