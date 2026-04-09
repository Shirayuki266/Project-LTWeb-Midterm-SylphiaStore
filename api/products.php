<?php
// api/products.php - SECURE VERSION
require_once 'db.php';
require_once '../includes/functions.php'; // Đảm bảo paginate() đã sẵn sàng

header('Content-Type: application/json; charset=utf-8');

/** * [MODIFIED] 1. VALIDATE & SANITIZE INPUTS
 * Đảm bảo dữ liệu đầu vào đúng kiểu, tránh lỗi logic và SQL Injection sơ đẳng.
 */
$category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$search = trim($_GET['search'] ?? '');
$min_price = max(0, filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_INT) ?: 0);
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_INT) ?: 999999999;
$sort = $_GET['sort'] ?? 'id_desc';

/**
 * [MODIFIED] 2. BUILD SECURE QUERY WITH PREPARED STATEMENTS
 */
$base_sql = "SELECT id, name, image, price, cost_price, profit_percent 
             FROM products WHERE status = 1";

$params = [];
$types = '';

if ($category > 0) {
    $base_sql .= " AND category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

if ($search) {
    $base_sql .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$base_sql .= " AND price BETWEEN ? AND ?";
array_push($params, $min_price, $max_price);
$types .= 'ii';

/**
 * [MODIFIED] 3. APPEND SORTING BEFORE PAGINATION
 * Duy trì logic sắp xếp cũ nhưng dùng White-list để an toàn tuyệt đối.
 */
$order_map = [
    'name_asc'   => 'name ASC',
    'name_desc'  => 'name DESC', 
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC'
];
$order_by = $order_map[$sort] ?? 'id DESC';
$base_sql .= " ORDER BY $order_by";

try {
    /**
     * [MODIFIED] 4. EXECUTE PAGINATION (Reuse logic)
     */
    $result = paginate($conn, $base_sql, $page, 12, $params, $types);
    
    // Giữ nguyên cấu trúc JSON trả về để không làm gãy Frontend
    $products = array_map(function($row) {
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'image' => $row['image'],
            'price' => (float)$row['price'],
            'cost_price' => (float)$row['cost_price'],
            'profit_percent' => (float)$row['profit_percent']
        ];
    }, $result['data']);

    echo json_encode([
        'success'    => true,
        'products'   => $products,
        'pagination' => [
            'total' => $result['total'],
            'page'  => $result['current'],
            'pages' => $result['pages']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    /**
     * [MODIFIED] 5. SECURE ERROR LOGGING
     * Không lộ lỗi DB ra ngoài, ghi log để debug nội bộ.
     */
    error_log("API Error (products.php): " . $e->getMessage()); 
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal Server Error',
        'message' => 'Đã xảy ra lỗi hệ thống, vui lòng thử lại sau.' 
    ], JSON_UNESCAPED_UNICODE);
}
?>