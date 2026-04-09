<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

function ensureImportCountColumn(mysqli $conn): void
{
    $check = $conn->query("SHOW COLUMNS FROM products LIKE 'import_count'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE products ADD COLUMN import_count INT NOT NULL DEFAULT 0");
    }
}

$id = (int)($_GET['id'] ?? 0);

ensureImportCountColumn($conn);

$sql = "SELECT p.name AS product_name, d.quantity, d.import_price, p.import_count
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