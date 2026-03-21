<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';

class Auth {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function tableExists($name) {
        $nameEscaped = mysqli_real_escape_string($this->conn, $name);
        $sql = "SHOW TABLES LIKE '" . $nameEscaped . "'";
        $res = $this->conn->query($sql);
        return $res && $res->num_rows > 0;
    }

    private function getUserTable() {
        if ($this->tableExists('users')) return 'users';
        if ($this->tableExists('danh_sach_nguoi_dung')) return 'danh_sach_nguoi_dung';
        return null;
    }

    // 1. ĐĂNG KÝ (Đã sửa cột address_default thành address)
    public function register($data) {
        $errors = validate_register($this->conn, $data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $table = $this->getUserTable();
        if (!$table) {
            return ['success' => false, 'error' => 'Bảng người dùng không tồn tại'];
        }

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // SỬA TẠI ĐÂY: Đổi address_default thành address để khớp DB
        $stmt = $this->conn->prepare(
            "INSERT INTO users (username, password, email, phone, address, role)
             VALUES (?, ?, ?, ?, ?, 'customer')"
        );

        $stmt->bind_param(
            "sssss",
            $data['username'],
            $hash,
            $data['email'],
            $data['phone'],
            $data['address'] // Chuỗi địa chỉ đã nối từ form đăng ký
        );

        if (!$stmt->execute()) {
            return ['success' => false, 'error' => $stmt->error];
        }

        $user_id = $this->conn->insert_id;

        // Lưu Session để đăng nhập ngay sau khi đăng ký
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $data['username'];
        $_SESSION['user_type'] = 'user';

        return [
            'success' => true,
            'user' => ['id' => $user_id, 'username' => $data['username']]
        ];
    }

    // 2. ĐĂNG NHẬP NGƯỜI DÙNG
    public function userLogin($username, $password) {
        $table = $this->getUserTable();
        if (!$table) return ['success' => false, 'error' => 'Hệ thống bận'];

        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = 'user';
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'error' => 'Sai tên đăng nhập hoặc mật khẩu'];
    }

    // 3. ĐĂNG NHẬP ADMIN
    public function adminLogin($username, $password) {
        $table = $this->tableExists('account') ? 'account' : ($this->tableExists('admins') ? 'admins' : $this->getUserTable());
        
        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && ($password == $admin['password'] || password_verify($password, $admin['password']))) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Sai tài khoản Admin'];
    }

    public function isLoggedIn($type = null) {
        if ($type === 'admin') {
            return isset($_SESSION['admin_id']) && $_SESSION['user_type'] === 'admin';
        }
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    // 4. LẤY THÔNG TIN USER HIỆN TẠI
    public function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $id = $_SESSION['user_id'];
            $table = $this->getUserTable();
            
            $stmt = $this->conn->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }
}

$auth = new Auth($conn);