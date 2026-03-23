<?php
session_start();
require_once '../api/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $cost = floatval($_POST['cost_price']);
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE products SET cost_price = ?, price = ? WHERE id = ?");
    $stmt->bind_param("ddi", $cost, $price, $id);
    $stmt->execute();

    header("Location: " . $_SERVER['HTTP_REFERER']); 
    exit();
}