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
$status = $_POST['status'] ?? ''; // Trạng thái mới muốn chuyển sang

if ($order_id > 0 && $status != '') {
    // Bắt đầu Transaction để bảo vệ dữ liệu
    $conn->begin_transaction();

    try {
        // 1. Lấy trạng thái cũ của đơn hàng
        $stmtOld = $conn->prepare("SELECT status, user_id, total FROM orders WHERE id = ?");
        $stmtOld->bind_param("i", $order_id);
        $stmtOld->execute();
        $orderData = $stmtOld->get_result()->fetch_assoc();
        
        if (!$orderData) throw new Exception("Không tìm thấy đơn hàng.");
        $old_status = $orderData['status'];
        $userId = $orderData['user_id'];

        // 2. LOGIC TRỪ KHO: Khi chuyển từ 'pending' sang 'processing', 'shipping' hoặc 'delivered'
        // Mình coi các trạng thái này là đơn đã được duyệt và bắt đầu thoát kho
        $active_statuses = ['processing', 'shipping', 'delivered'];
        
        if ($old_status === 'pending' && in_array($status, $active_statuses)) {
            $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
            while ($item = $items->fetch_assoc()) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];

                // Kiểm tra tồn kho hiện tại
                $pRes = $conn->query("SELECT stock, name FROM products WHERE id = $pid");
                $product = $pRes->fetch_assoc();

                if ($product['stock'] < $qty) {
                    throw new Exception("Sản phẩm '" . $product['name'] . "' không đủ tồn kho (Hiện có: " . $product['stock'] . ")");
                }

                // Thực hiện trừ kho
                $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
            }
        }

        // 3. LOGIC HOÀN KHO: Nếu đơn đã trừ kho mà bị 'cancelled' (Hủy) thì phải cộng lại
        if (in_array($old_status, $active_statuses) && $status === 'cancelled') {
            $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
            while ($item = $items->fetch_assoc()) {
                $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE id = {$item['product_id']}");
            }
        }

        // 4. CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
        $stmtUpdate = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmtUpdate->bind_param("si", $status, $order_id);
        $stmtUpdate->execute();

        // 5. LOGIC TÍNH VIP (Giữ nguyên của bạn)
        if ($status === 'delivered') {
            $resTotal = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE user_id = $userId AND status = 'delivered'");
            $totalSpent = $resTotal->fetch_assoc()['total_spent'] ?? 0;

            $newVip = 'none';
            if ($totalSpent >= 50000000) $newVip = 'vàng';
            elseif ($totalSpent >= 10000000) $newVip = 'bạc';
            elseif ($totalSpent >= 2000000) $newVip = 'đồng';

            $stmtVip = $conn->prepare("UPDATE users SET vip_level = ? WHERE id = ?");
            $stmtVip->bind_param("si", $newVip, $userId);
            $stmtVip->execute();
        }

        $conn->commit(); // Xác nhận mọi thay đổi thành công
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback(); // Có lỗi (như thiếu kho) thì hủy bỏ toàn bộ
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}