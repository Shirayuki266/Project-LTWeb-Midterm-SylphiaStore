<?php
require_once 'db.php';
header('Content-Type: application/json');

$id = (int)$_GET['id'];
$date = $_GET['date'] . " 23:59:59";

// 1. Lấy tồn hiện tại
$p = $conn->query("SELECT stock FROM products WHERE id = $id")->fetch_assoc();
$current = $p['stock'];

// 2. Tính lượng đã BÁN RA kể từ ngày đó đến nay (Dựa trên đơn hàng thành công)
$stmt = $conn->prepare("
    SELECT SUM(oi.quantity) as sold_since 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    WHERE oi.product_id = ? AND o.created_at > ? AND o.status != 'cancelled'
");
$stmt->bind_param("is", $id, $date);
$stmt->execute();
$sold_since = $stmt->get_result()->fetch_assoc()['sold_since'] ?? 0;

// CÔNG THỨC: Tồn quá khứ = Tồn hiện tại + Lượng đã bán đi (vì lúc đó chưa bán nên hàng vẫn còn trong kho)
// (Lưu ý: Nếu bạn có hệ thống phiếu nhập hàng, cần trừ thêm lượng đã nhập vào sau ngày đó)
$stock_at_time = $current + $sold_since;

echo json_encode(['success' => true, 'stock' => $stock_at_time]);