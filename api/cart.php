<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Cart class (session-based)
class Cart {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    public function add($product_id, $qty = 1) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
        return $this->getTotalItems();
    }

    public function update($product_id, $qty) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }

    public function remove($product_id) {
        unset($_SESSION['cart'][$product_id]);
    }

    public function getItems() {
        $items = [];
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $stmt = $this->conn->prepare("SELECT id, ten AS name, gia AS price, hinh AS image FROM sanpham WHERE id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();
            if ($prod) {
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
        return array_sum($_SESSION['cart']);
    }

    public function clear() {
        $_SESSION['cart'] = [];
    }
}

$cart = new Cart($conn);

// If this file is accessed directly, act as a simple JSON API.
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json; charset=utf-8');

    $input = [];
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $input = $decoded;
    }

    $action = $_REQUEST['action'] ?? $input['action'] ?? '';
    $id = (int)($_REQUEST['id'] ?? $input['id'] ?? 0);
    $qty = (int)($_REQUEST['qty'] ?? $input['qty'] ?? $input['quantity'] ?? 1);

    $response = ['success' => false];

    switch ($action) {
        case 'add':
            $cart->add($id, $qty);
            $response = ['success' => true, 'totalItems' => $cart->getTotalItems()];
            break;
        case 'update':
            $cart->update($id, $qty);
            $response = ['success' => true];
            break;
        case 'remove':
            $cart->remove($id);
            $response = ['success' => true];
            break;
        case 'clear':
            $cart->clear();
            $response = ['success' => true];
            break;
        case 'count':
            $response = ['success' => true, 'totalItems' => $cart->getTotalItems()];
            break;
        default:
            $response = ['success' => false, 'error' => 'Invalid action'];
    }

    echo json_encode($response);
    exit;
}
?>