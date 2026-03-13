<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "sylphia_shop"; // Tên database mới của bạn

// 1. Kết nối server
$conn = new mysqli($servername, $username, $password);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// 2. Tạo Database nếu chưa có
$sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Đã xác nhận Database: $db_name <br>";
}

// 3. Chọn Database
$conn->select_db($db_name);

// 4. Đọc file SQL (Đảm bảo file nằm trong thư mục sql/)
$path = 'sql/sylphia_shop.sql'; 

if (file_exists($path)) {
    $sql_file = file_get_contents($path);

    // 5. Chạy lệnh Import tự động
    if ($conn->multi_query($sql_file)) {
        // Cần vòng lặp này để xử lý hết các lệnh trong file SQL
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "<h2 style='color: green;'>Chúc mừng! Database đã được thiết lập tự động. Giờ bạn có thể vào web!</h2>";
    } else {
        echo "<h2 style='color: red;'>Lỗi khi chạy lệnh SQL: </h2>" . $conn->error;
    }
} else {
    echo "<h2 style='color: orange;'>Lỗi: Không tìm thấy file SQL tại đường dẫn: $path </h2>";
}

$conn->close();
?>