<?php
/**
 * api/cart.php - COMPLETED & SECURE VERSION
 */
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
        $stmt->close();
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
     * Cập nhật số lượng (Dùng cho trang Giỏ hàng)
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

    /**
     * [MODIFIED] Tối ưu hóa hiệu năng: Prepare 1 lần dùng cho cả vòng lặp
     */
    public function getItems() {
        $items = [];
        if (empty($_SESSION['cart'])) return $items;

        $stmt = $this->conn->prepare("SELECT id, name, price, image, stock FROM products WHERE id = ?");

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();

            if ($prod) {
                // Bảo mật: Đảm bảo số lượng trong giỏ <= tồn kho thực tế
                if ($qty > $prod['stock']) {
                    $qty = $prod['stock'];
                    $_SESSION['cart'][$pid] = $qty;
                }
                
                if ($qty <= 0) continue;

                $prod['quantity'] = (int)$qty;
                $prod['subtotal'] = (float)$prod['price'] * $qty;
                $items[] = $prod;
            }
        }
        $stmt->close();
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

    /**
     * [MODIFIED] Xử lý Request tập trung & Bảo mật Input
     */
    public function handleCart() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Vui lòng đăng nhập để thực hiện chức năng này!"
            ]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // Lấy action và bảo mật dữ liệu đầu vào
        $action = $_POST['action'] ?? $_GET['action'] ?? $input['action'] ?? '';
        $id     = filter_var($_POST['id'] ?? $_GET['id'] ?? $input['id'] ?? 0, FILTER_VALIDATE_INT);
        
        /** Chặn lỗi số lượng âm bằng max(1, ...) */
        $qty    = max(1, filter_var($_POST['qty'] ?? $_GET['qty'] ?? $input['qty'] ?? 1, FILTER_VALIDATE_INT));

        $response = ["success" => false];

        switch ($action) {
            case 'add':
                $res = $this->add($id, $qty);
                $response = array_merge(["success" => true], $res);
                break;

            case 'update':
                $res = $this->update($id, $qty);
                $response = array_merge(["success" => true], $res);
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
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Khởi tạo và thực thi nếu gọi trực tiếp qua API
$cart = new Cart($conn);
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $cart->handleCart();
}