<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);

// 1. Xóa dữ liệu trong Class Auth
$auth->logout();

// 2. Xóa sạch mọi biến Session trên Server
$_SESSION = array();

// 3. Hủy Cookie session trên trình duyệt để trình duyệt không nhận diện lại
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hủy diệt Session hoàn toàn
session_destroy();

// 5. Quan trọng: Chặn Cache để khi nhấn nút Back không quay lại được trang cũ
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 6. Bay thẳng ra trang Login
header('Location: login.php'); 
exit;
?>