<?php
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    if ($customer) {
        echo json_encode(['success' => true, 'customer' => $customer]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>