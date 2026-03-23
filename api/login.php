<?php
session_start();
require_once 'db.php'; // Đảm bảo đường dẫn tới file kết nối CSDL chính xác
require_once 'auth.php'; // Nếu bạn dùng class Auth

// Thiết lập phản hồi JSON nếu bạn dùng Ajax, hoặc xử lý POST thông thường
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
        exit;
    }

    // 1. Truy vấn lấy thông tin user (Bao gồm cả cột status)
    $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // 2. Kiểm tra sự tồn tại của user và khớp mật khẩu
    if ($user && password_verify($password, $user['password'])) {
        
        // 3. KIỂM TRA TRẠNG THÁI TÀI KHOẢN (QUAN TRỌNG NHẤT)
        // Dựa trên cấu trúc bảng của bạn: 1 là Hoạt động, 0 là Bị khóa
        if ((int)$user['status'] === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Tài khoản của bạn đã bị khóa bởi quản trị viên. Vui lòng liên hệ hỗ trợ!'
            ]);
            exit;
        }

        // 4. Nếu mọi thứ hợp lệ, tiến hành tạo Session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        echo json_encode([
            'success' => true, 
            'message' => 'Đăng nhập thành công!',
            'redirect' => 'index.php'
        ]);
    } else {
        // Sai tên đăng nhập hoặc mật khẩu
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác!']);
    }
    exit;
}

// Nếu truy cập trực tiếp file này mà không phải POST
echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ!']);