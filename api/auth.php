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
        // Some MariaDB versions don’t allow parameter binding in SHOW TABLES.
        // Use escaping and string interpolation instead.
        $nameEscaped = mysqli_real_escape_string($this->conn, $name);
        $sql = "SHOW TABLES LIKE '" . $nameEscaped . "'";
        $res = $this->conn->query($sql);
        return $res && $res->num_rows > 0;
    }

    // Register user (uses legacy schema: danh_sach_nguoi_dung)
    public function register($data) {
        $errors = validate_register($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO danh_sach_nguoi_dung (username, password, email, phonenumber) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['username'], $hash, $data['email'], $data['phone']);

        if ($stmt->execute()) {
            $user_id = $this->conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $data['username'];
            $_SESSION['user_type'] = 'user';
            return ['success' => true, 'user' => ['id' => $user_id, 'username' => $data['username']]];
        }
        return ['success' => false, 'error' => 'Registration failed'];
    }

    // User login
    public function userLogin($username, $password) {
        $table = $this->tableExists('danh_sach_nguoi_dung') ? 'danh_sach_nguoi_dung' : 'users';
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

    // Admin login (falls back to legacy user table if no admins table)
    public function adminLogin($username, $password) {
        $table = $this->tableExists('admins') ? 'admins' : 'danh_sach_nguoi_dung';
        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';
            return ['success' => true, 'admin' => $admin];
        }
        return ['success' => false, 'error' => 'Sai tên đăng nhập hoặc mật khẩu'];
    }

    public function isLoggedIn($type = null) {
        if ($type === 'admin') {
            return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
        }
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    public function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $id = $_SESSION['user_id'];
            $table = $this->tableExists('danh_sach_nguoi_dung') ? 'danh_sach_nguoi_dung' : 'users';
            $stmt = $this->conn->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }
}

// Usage
$auth = new Auth($conn);
?>