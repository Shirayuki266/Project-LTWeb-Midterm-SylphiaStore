<?php
session_start();
require_once '../api/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['product_id'] ?? 0);
    
    if (isset($_FILES['product_image']) && $id > 0) {
        $file = $_FILES['product_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Định dạng ảnh không hỗ trợ']);
            exit;
        }

        // Tạo tên file duy nhất
        $fileName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = '../uploads/';
        
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Cập nhật Database - đường dẫn tương đối để hiển thị
            $dbPath = '../uploads/' . $fileName;
            $stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $dbPath, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'newPath' => $dbPath]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật Database']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể lưu file vào thư mục uploads']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    }
    exit;
}