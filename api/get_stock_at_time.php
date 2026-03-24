<?php
require_once 'db.php';
header('Content-Type: application/json');

$id = (int)$_GET['id'];
$date = $_GET['date'] . " 23:59:59"; // Tính đến hết ngày được chọn

// 1. Lấy tồn hiện tại trong bảng products
$p = $conn->query("SELECT stock FROM products WHERE id = $id")->fetch_assoc();
if (!$p) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}
$current_stock = (int)$p['stock'];

// 2. Tính lượng đã BÁN RA (Xuất kho) từ sau ngày X đến nay
$stmt_sold = $conn->prepare("
    SELECT IFNULL(SUM(oi.quantity), 0) as total 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    WHERE oi.product_id = ? AND o.created_at > ? AND o.status != 'cancelled'
");
$stmt_sold->bind_param("is", $id, $date);
$stmt_sold->execute();
$sold_since = $stmt_sold->get_result()->fetch_assoc()['total'];

// 3. Tính lượng đã NHẬP VÀO từ sau ngày X đến nay (Dựa trên phiếu nhập)
$stmt_import = $conn->prepare("
    SELECT IFNULL(SUM(ii.quantity), 0) as total 
    FROM import_items ii 
    JOIN import_receipts ir ON ii.import_receipt_id = ir.id 
    WHERE ii.product_id = ? AND ir.created_at > ?
");
$stmt_import->bind_param("is", $id, $date);
$stmt_import->execute();
$imported_since = $stmt_import->get_result()->fetch_assoc()['total'];

// 4. Áp dụng công thức ngược
$stock_at_time = $current_stock + $sold_since - $imported_since;

echo json_encode([
    'success' => true, 
    'stock' => max(0, $stock_at_time), // Đảm bảo không hiện số âm
    'details' => [
        'current' => $current_stock,
        'sold_after' => $sold_since,
        'imported_after' => $imported_since
    ]
]);