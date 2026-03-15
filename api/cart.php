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

        return $this->getTotalItems();
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

            $stmt = $this->conn->prepare(
                "SELECT id,name,price,image,description 
                 FROM products 
                 WHERE id=?"
            );

            $stmt->bind_param("i",$pid);
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

        if (!isset($_SESSION['cart'])) return 0;

        return array_sum($_SESSION['cart']);
    }

    /* ======================
       HÀM XỬ LÝ API CART
    ====================== */

    public function handleCart() {

        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = [];

        $action = $_POST['action']
            ?? $_GET['action']
            ?? $input['action']
            ?? '';

        $id = (int)(
            $_POST['id']
            ?? $_GET['id']
            ?? $input['id']
            ?? 0
        );

        $qty = (int)(
            $_POST['qty']
            ?? $_GET['qty']
            ?? $input['qty']
            ?? 1
        );

        $response = ["success"=>false];

        switch ($action) {

            case 'add':

                $response = [
                    "success"=>true,
                    "totalItems"=>$this->add($id,$qty)
                ];

            break;

            case 'update':

                $response = [
                    "success"=>true,
                    "totalItems"=>$this->update($id,$qty)
                ];

            break;

            case 'remove':

                $response = [
                    "success"=>true,
                    "totalItems"=>$this->remove($id)
                ];

            break;

            case 'clear':

                $this->clear();

                $response = [
                    "success"=>true,
                    "totalItems"=>0
                ];

            break;

            case 'count':

                $response = [
                    "success"=>true,
                    "totalItems"=>$this->getTotalItems()
                ];

            break;

            case 'items':

                $response = [
                    "success"=>true,
                    "items"=>$this->getItems(),
                    "total"=>$this->getTotal(),
                    "totalItems"=>$this->getTotalItems()
                ];

            break;

            default:

                $response = [
                    "success"=>false,
                    "error"=>"Invalid action"
                ];
        }

        echo json_encode($response);
        exit;
    }
}

$cart = new Cart($conn);


/* ======================
CHẠY API KHI GỌI FILE
====================== */

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $cart->handleCart();
}
?>