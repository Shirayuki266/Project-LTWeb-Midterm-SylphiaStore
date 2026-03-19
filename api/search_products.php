<?php
require_once 'db.php';
require_once '../includes/functions.php';

$q = $_GET['q'] ?? '';
$min = $_GET['min'] ?? 0;
$max = $_GET['max'] ?? 999999999;
$sort = $_GET['sort'] ?? 'id_desc';

// Xử lý câu lệnh sắp xếp
$order_by = "id DESC";
if ($sort == 'price_asc') $order_by = "price ASC";
if ($sort == 'price_desc') $order_by = "price DESC";
if ($sort == 'name_asc') $order_by = "name ASC";

// Truy vấn SQL an toàn (Sử dụng Prepared Statements)
$sql = "SELECT * FROM products WHERE name LIKE ? AND price BETWEEN ? AND ? ORDER BY $order_by";
$stmt = $conn->prepare($sql);
$search_query = "%$q%";
$stmt->bind_param("sdd", $search_query, $min, $max);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($product = $result->fetch_assoc()) {
        // Gọi file card sản phẩm mà bạn đã làm ở các bước trước
        include '../includes/product-card.php'; 
    }
} else {
    echo '<div class="col-12 text-center py-5"><p class="text-muted">Không tìm thấy sản phẩm nào.</p></div>';
}