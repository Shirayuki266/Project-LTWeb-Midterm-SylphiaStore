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

// Database helpers
function table_exists($conn, $name) {
    $nameEscaped = mysqli_real_escape_string($conn, $name);
    $res = $conn->query("SHOW TABLES LIKE '" . $nameEscaped . "'");
    return $res && $res->num_rows > 0;
}

function get_user_table($conn) {
    // Prefer the modern schema tables. Legacy support for old table remains.
    if (table_exists($conn, 'users')) {
        return 'users';
    }
    if (table_exists($conn, 'account')) {
        return 'account';
    }
    if (table_exists($conn, 'danh_sach_nguoi_dung')) {
        return 'danh_sach_nguoi_dung';
    }
    return null;
}

// Validate register
function validate_register($data) {
    global $conn; // Access the database connection
    $errors = [];
    if (strlen($data['username']) < 3) $errors[] = 'Username ≥3 chars';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';
    if (strlen($data['password']) < 6) $errors[] = 'Password ≥6 chars';
    if ($data['password'] !== $data['confirm_password']) $errors[] = 'Passwords mismatch';

    $table = get_user_table($conn);
    if (!$table) {
        $errors[] = 'Database not initialized. Missing users table.';
        return $errors;
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM $table WHERE email = ?");
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