<?php
require_once '../api/db.php';

// Xác định bảng admin dựa trên logic của auth.php
$tables = ['account', 'admins', 'users'];
$found_table = null;

foreach ($tables as $t) {
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    if ($res && $res->num_rows > 0) {
        $found_table = $t;
        break;
    }
}

if (!$found_table) {
    die("Không tìm thấy bảng lưu tài khoản (account/admins/users). Vui lòng kiểm tra lại DB.");
}

// Tạo mật khẩu mới (Mã hóa để an toàn hơn)
$new_pass = password_hash('123456', PASSWORD_DEFAULT);

$sql = "UPDATE $found_table SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $new_pass);

if ($stmt->execute()) {
    echo "<h3>Thành công!</h3>";
    echo "Tài khoản: <b>admin</b><br>";
    echo "Mật khẩu mới: <b>123456</b><br><br>";
    echo "<span style='color:red'>LƯU Ý: Hãy xóa file <b>fix_admin.php</b> này ngay lập tức sau khi đăng nhập thành công.</span><br>";
    echo "<a href='login.php'>Đi đến trang đăng nhập</a>";
} else {
    echo "Lỗi khi cập nhật: " . $conn->error;
}
?>