<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);

$sql = "SELECT p.name AS product_name, d.quantity, d.import_price
        FROM purchase_order_details d
        JOIN products p ON d.product_id = p.id
        WHERE d.purchase_order_id = $id";

$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);