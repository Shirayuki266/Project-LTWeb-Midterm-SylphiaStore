<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $id = (int)($_GET['id'] ?? 0);
    $rawDate = trim($_GET['date'] ?? '');

    if ($id <= 0) {
        throw new Exception('Thiếu mã sản phẩm hợp lệ.');
    }

    $selectedDate = DateTime::createFromFormat('Y-m-d', $rawDate);
    if (!$selectedDate || $selectedDate->format('Y-m-d') !== $rawDate) {
        throw new Exception('Ngày tra cứu không hợp lệ.');
    }

    $date = $selectedDate->format('Y-m-d') . ' 23:59:59';

    $stmtProduct = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmtProduct->bind_param('i', $id);
    $stmtProduct->execute();
    $product = $stmtProduct->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại.');
    }

    $currentStock = (int)$product['stock'];

    $stmtSold = $conn->prepare("
        SELECT IFNULL(SUM(oi.quantity), 0) AS total
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = ?
          AND o.created_at > ?
          AND o.status != 'cancelled'
    ");
    if (!$stmtSold) {
        throw new Exception('Không thể chuẩn bị truy vấn lịch sử xuất kho.');
    }
    $stmtSold->bind_param('is', $id, $date);
    $stmtSold->execute();
    $soldSince = (int)($stmtSold->get_result()->fetch_assoc()['total'] ?? 0);

    $stmtImport = $conn->prepare("
        SELECT IFNULL(SUM(pod.quantity), 0) AS total
        FROM purchase_order_details pod
        JOIN purchase_orders po ON pod.purchase_order_id = po.id
        WHERE pod.product_id = ?
          AND po.created_at > ?
          AND po.status = 'completed'
    ");
    if (!$stmtImport) {
        throw new Exception('Không thể chuẩn bị truy vấn lịch sử nhập kho.');
    }
    $stmtImport->bind_param('is', $id, $date);
    $stmtImport->execute();
    $importedSince = (int)($stmtImport->get_result()->fetch_assoc()['total'] ?? 0);

    $stockAtTime = $currentStock + $soldSince - $importedSince;

    echo json_encode([
        'success' => true,
        'stock' => max(0, $stockAtTime),
        'details' => [
            'current' => $currentStock,
            'sold_after' => $soldSince,
            'imported_after' => $importedSince
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}