<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);

// 1. CHỈ xóa thông tin đăng nhập, KHÔNG xóa toàn bộ $_SESSION
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['role']); // Nếu bạn có phân quyền

// 2. KHÔNG DÙNG $_SESSION = array(); 
// 3. KHÔNG DÙNG session_destroy();
// 4. KHÔNG xóa Session Cookie (vì xóa là mất định danh giỏ hàng ngay)

// 5. Chặn Cache (Giữ lại phần này là đúng)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 6. Chuyển hướng
header('Location: login.php'); 
exit;
?>