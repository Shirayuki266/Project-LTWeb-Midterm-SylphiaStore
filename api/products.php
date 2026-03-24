<?php
require_once 'db.php';

header('Content-Type: application/json');

$category = $_GET['category'] ?? 0;
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 999999999;
$sort = $_GET['sort'] ?? 'id_desc';

$limit = 12;
$offset = ($page - 1) * $limit;

$where = "WHERE status = 1";

if ($category > 0) {
    $where .= " AND category_id = $category";
}

if ($search != '') {
    $search = $conn->real_escape_string($search);
    $where .= " AND name LIKE '%$search%'";
}

$where .= " AND price BETWEEN $min_price AND $max_price";

$order = "ORDER BY id DESC";

switch($sort){
    case 'name_asc': $order = "ORDER BY name ASC"; break;
    case 'name_desc': $order = "ORDER BY name DESC"; break;
    case 'price_asc': $order = "ORDER BY price ASC"; break;
    case 'price_desc': $order = "ORDER BY price DESC"; break;
}

$totalQuery = $conn->query("SELECT COUNT(*) as total FROM products $where");
$total = $totalQuery->fetch_assoc()['total'];

$sql = "
SELECT id,name,image,price,cost_price,profit_percent
FROM products
$where
$order
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

$products = [];

while($row = $result->fetch_assoc()){
    $products[] = $row;
}

echo json_encode([
    "products"=>$products,
    "pagination"=>[
        "total"=>$total,
        "page"=>$page,
        "pages"=>ceil($total/$limit)
    ]
]);