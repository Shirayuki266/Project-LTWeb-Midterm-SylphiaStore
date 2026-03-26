<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sylphia_shop2'; // Chỉ để 1 cái tên duy nhất bạn đang dùng

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>