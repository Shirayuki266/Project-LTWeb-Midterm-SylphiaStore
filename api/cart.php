<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';

class Cart {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Lấy số lượng tồn kho thực tế từ Database
     */
    private function getProductStock($product_id) {
        $stmt = $this->conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? (int)$res['stock'] : 0;
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (Có kiểm tra tồn kho)
     */
    public function add($product_id, $qty = 1) {
        $stock = $this->getProductStock($product_id);
        $current_in_cart = $_SESSION['cart'][$product_id] ?? 0;
        $new_qty = $current_in_cart + $qty;

        $status = "success";
        $message = "";

        if ($new_qty > $stock) {
            $_SESSION['cart'][$product_id] = $stock;
            $status = "warning";
            $message = "Chỉ còn $stock sản phẩm trong kho. Đã cập nhật tối đa vào giỏ hàng.";
        } else {
            $_SESSION['cart'][$product_id] = $new_qty;
        }

        return [
            "status" => $status,
            "message" => $message,
            "totalItems" => $this->getTotalItems()
        ];
    }

    /**
     * Cập nhật số lượng (Dùng cho trang Giỏ hàng - Có kiểm tra tồn kho)
     */
    public function update($product_id, $qty) {
        $stock = $this->getProductStock($product_id);
        
        $status = "success";
        $message = "";

        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } elseif ($qty > $stock) {
            $_SESSION['cart'][$product_id] = $stock;
            $status = "warning";
            $message = "Số lượng yêu cầu vượt quá tồn kho ($stock).";
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }

        return [
            "status" => $status,
            "message" => $message,
            "totalItems" => $this->getTotalItems()
        ];
    }

    public function remove($product_id) {
        unset($_SESSION['cart'][$product_id]);
        return $this->getTotalItems();
    }

    public function clear() {
        $_SESSION['cart'] = [];
        return 0;
    }

    public function getItems() {
        $items = [];
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $stmt = $this->conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();

            if ($prod) {
                // Đảm bảo số lượng trong giỏ không bao giờ lớn hơn kho (phòng trường hợp admin sửa kho sau khi khách đã bỏ hàng vào giỏ)
                if ($qty > $prod['stock']) {
                    $qty = $prod['stock'];
                    $_SESSION['cart'][$pid] = $qty;
                }
                $prod['quantity'] = $qty;
                $prod['subtotal'] = $prod['price'] * $qty;
                $items[] = $prod;
            }
        }
        return $items;
    }

    public function getTotal() {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    public function getTotalItems() {
        return isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    }

    public function handleCart() {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $action = $_POST['action'] ?? $_GET['action'] ?? $input['action'] ?? '';
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? $input['id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? $_GET['qty'] ?? $input['qty'] ?? 1);

        $response = ["success" => false];

        switch ($action) {
            case 'add':
                $res = $this->add($id, $qty);
                $response = [
                    "success" => true,
                    "status" => $res['status'],
                    "message" => $res['message'],
                    "totalItems" => $res['totalItems']
                ];
                break;

            case 'update':
                $res = $this->update($id, $qty);
                $response = [
                    "success" => true,
                    "status" => $res['status'],
                    "message" => $res['message'],
                    "totalItems" => $res['totalItems']
                ];
                break;

            case 'remove':
                $response = ["success" => true, "totalItems" => $this->remove($id)];
                break;

            case 'clear':
                $this->clear();
                $response = ["success" => true, "totalItems" => 0];
                break;

            case 'items':
                $response = [
                    "success" => true,
                    "items" => $this->getItems(),
                    "total" => $this->getTotal(),
                    "totalItems" => $this->getTotalItems()
                ];
                break;

            default:
                $response = ["success" => false, "error" => "Invalid action"];
        }
        echo json_encode($response);
        exit;
    }
}

$cart = new Cart($conn);
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $cart->handleCart();
}