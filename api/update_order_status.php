<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền admin']);
    exit;
}

$order_id = $_POST['order_id'] ?? 0;
$status = $_POST['status'] ?? '';

if ($order_id > 0 && $status != '') {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        // CHỈ TÍNH TOÁN VIP KHI ĐƠN HÀNG LÀ 'delivered'
        if ($status === 'delivered') {
            $resUser = $conn->query("SELECT user_id FROM orders WHERE id = $order_id");
            $orderData = $resUser->fetch_assoc();
            
            if ($orderData) {
                $userId = $orderData['user_id'];

                // Tính tổng tiền các đơn đã giao THÀNH CÔNG
                $resTotal = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE user_id = $userId AND status = 'delivered'");
                $totalData = $resTotal->fetch_assoc();
                $totalSpent = $totalData['total_spent'] ?? 0;

                $newVip = 'none';
                if ($totalSpent >= 50000000) $newVip = 'vàng';
                elseif ($totalSpent >= 10000000) $newVip = 'bạc';
                elseif ($totalSpent >= 2000000) $newVip = 'đồng';

                // Cập nhật Database
                $stmtVip = $conn->prepare("UPDATE users SET vip_level = ? WHERE id = ?");
                $stmtVip->bind_param("si", $newVip, $userId);
                $stmtVip->execute();
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}