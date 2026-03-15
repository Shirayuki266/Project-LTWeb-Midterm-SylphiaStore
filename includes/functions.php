<?php
// Common utilities ≤300L

/* =============================
   PAGINATION
============================= */

if (!function_exists('paginate')) {
function paginate($conn, $sql, $page = 1, $perPage = 12, $params = [], $types = '')
{
    if (!$conn) {
        return ['data'=>[], 'pages'=>0, 'current'=>1, 'total'=>0];
    }

    $page = (int)$page;
    $perPage = (int)$perPage;

    $offset = ($page - 1) * $perPage;

    $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
    $stmt = $conn->prepare($countSql);

    if(!$stmt){
        return ['data'=>[], 'pages'=>0, 'current'=>1, 'total'=>0];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : ['total'=>0];

    $total = $row['total'] ?? 0;
    $pages = $perPage ? ceil($total / $perPage) : 1;

    $stmt->close();

    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        return ['data'=>[], 'pages'=>$pages, 'current'=>$page, 'total'=>$total];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return [
        'data'=>$data,
        'pages'=>$pages,
        'current'=>$page,
        'total'=>$total
    ];
}
}


/* =============================
   FORMAT PRICE
============================= */

if (!function_exists('formatPrice')) {
function formatPrice($price)
{
    $price = $price ?? 0;
    return number_format($price, 0, ',', '.') . '₫';
}
}


/* =============================
   DATABASE HELPERS
============================= */

if (!function_exists('table_exists')) {
function table_exists($conn, $name)
{
    if(!$conn) return false;

    $nameEscaped = mysqli_real_escape_string($conn, $name);

    $res = $conn->query("SHOW TABLES LIKE '$nameEscaped'");

    return ($res && $res->num_rows > 0);
}
}


if (!function_exists('get_user_table')) {
function get_user_table($conn)
{
    if(!$conn) return null;

    if (table_exists($conn,'users')) return 'users';

    if (table_exists($conn,'account')) return 'account';

    if (table_exists($conn,'danh_sach_nguoi_dung')) return 'danh_sach_nguoi_dung';

    return null;
}
}

/* =============================
   VALIDATE REGISTER
============================= */

if (!function_exists('validate_register')) {
function validate_register($conn, $data)
{
    $errors = [];

    if (strlen($data['username'] ?? '') < 3)
        $errors[] = 'Username phải ≥ 3 ký tự';

    if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL))
        $errors[] = 'Email không hợp lệ';

    if (strlen($data['password'] ?? '') < 6)
        $errors[] = 'Password phải ≥ 6 ký tự';

    if (($data['password'] ?? '') !== ($data['confirm_password'] ?? ''))
        $errors[] = 'Mật khẩu không khớp';

    $table = get_user_table($conn);

    if (!$table) {
        $errors[] = 'Database chưa khởi tạo';
        return $errors;
    }

    if (empty($errors)) {

        // check email
        $stmt = $conn->prepare("SELECT id FROM $table WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $errors[] = "Email đã tồn tại";
        }

        $stmt->close();

        // check username
        $stmt = $conn->prepare("SELECT id FROM $table WHERE username = ?");
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $errors[] = "Username đã tồn tại";
        }

        $stmt->close();
    }

    return $errors;
}
}


/* =============================
   SANITIZE INPUT
============================= */

if (!function_exists('sanitize')) {
function sanitize($conn, $data)
{
    if(!$conn) return htmlspecialchars(trim($data));

    return htmlspecialchars(
        mysqli_real_escape_string($conn, trim($data))
    );
}
}


/* =============================
   STAR RATING
============================= */

if (!function_exists('renderStars')) {
function renderStars($rating)
{
    $rating = $rating ?? 0;

    $html = '';

    for ($i=1;$i<=5;$i++) {

        $class = $i <= round($rating)
            ? 'fas fa-star text-warning'
            : 'far fa-star text-warning';

        $html .= "<i class='$class'></i>";
    }

    return $html;
}
}


/* =============================
   STOCK (LEGACY DB SUPPORT)
============================= */

if (!function_exists('getStock')) {
function getStock($conn, $id)
{
    return 100;
}
}

?>