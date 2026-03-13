<?php
// Common utilities ≤300L

// Pagination
function paginate($conn, $sql, $page = 1, $perPage = 12, $params = [], $types = '') {
    $offset = ($page - 1) * $perPage;
    $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
    $stmt = $conn->prepare($countSql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $pages = ceil($total / $perPage);
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $perPage; $params[] = $offset; $types .= 'ii';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return ['data' => $data, 'pages' => $pages, 'current' => $page, 'total' => $total];
}

// Price format
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

// Validate register
function validate_register($data) {
    global $conn; // Access the database connection
    $errors = [];
    if (strlen($data['username']) < 3) $errors[] = 'Username ≥3 chars';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
    if (strlen($data['password']) < 6) $errors[] = 'Password ≥6 chars';
    if ($data['password'] !== $data['confirm_password']) $errors[] = 'Passwords mismatch';

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM danh_sach_nguoi_dung WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email đã được sử dụng';
        }
        $stmt->close();
    }

    return $errors;
}

// Sanitize input
function sanitize($conn, $data) {
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

// Stars rating
function renderStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= round($rating) ? 'fas fa-star' : 'far fa-star';
        $html .= "<i class='$class text-warning'></i>";
    }
    return $html;
}

// Temp compat for old DB (no stock column)
function getStock($conn, $id) {
    return 100; // Dummy
}
?>