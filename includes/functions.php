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

if (!function_exists('getProductImagePath')) {
function getProductImagePath($image)
{
    $image = trim((string)$image);
    if ($image === '') {
        return '../images/no-image.png';
    }

    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }

    if (strpos($image, '../uploads/') === 0) {
        return $image;
    }

    if (strpos($image, '/uploads/') === 0) {
        return substr($image, 1);
    }

    if (strpos($image, 'uploads/') === 0) {
        return '../' . $image;
    }

    return '../uploads/' . ltrim($image, '/');
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
        if (!$conn) return 0;
        $id = (int)$id;
        $res = $conn->query("SELECT stock FROM products WHERE id = $id");
        if ($res && $row = $res->fetch_assoc()) {
            return (int)$row['stock'];
        }
        return 0;
    }
}
/* =============================
    CHECK USER STATUS (Sửa lỗi vẫn vào được khi bị khóa)
============================= */

if (!function_exists('check_user_active')) {
    function check_user_active($conn)
    {
        // Nếu chưa đăng nhập thì không cần check
        if (!isset($_SESSION['user_id'])) return true;

        $user_id = (int)$_SESSION['user_id'];
        $table = get_user_table($conn);

        if (!$table) return true;

        // Truy vấn trực tiếp từ DB để lấy trạng thái mới nhất
        $stmt = $conn->prepare("SELECT status FROM $table WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        // Nếu user không tồn tại hoặc status = 0 (Bị khóa)
        if (!$user || (isset($user['status']) && (int)$user['status'] === 0)) {
            // Hủy toàn bộ phiên làm việc
            session_unset();
            session_destroy();
            return false;
        }

        return true;
    }
}
?>