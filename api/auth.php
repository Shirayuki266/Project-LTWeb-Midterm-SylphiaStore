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

    private function getUserTable() {
        // Prefer the modern schema, but keep legacy support.
        if (function_exists('get_user_table')) {
            return get_user_table($this->conn);
        }
        if ($this->tableExists('users')) return 'users';
        if ($this->tableExists('danh_sach_nguoi_dung')) return 'danh_sach_nguoi_dung';
        return null;
    }

    // Register user (supports legacy schema `danh_sach_nguoi_dung` and newer `users` schema)
    public function register($data) {
        $errors = validate_register($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $table = get_user_table($this->conn);
        if (!$table) {
            return ['success' => false, 'error' => 'Database not initialized. Missing users table.'];
        }

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($table === 'users') {
            $stmt = $this->conn->prepare("INSERT INTO users (username, password, email, phone, address_default) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $data['username'], $hash, $data['email'], $data['phone'], $data['address']);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO danh_sach_nguoi_dung (username, password, email, phonenumber) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $data['username'], $hash, $data['email'], $data['phone']);
        }

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
        $table = $this->getUserTable();
        if (!$table) {
            return ['success' => false, 'error' => 'Authentication unavailable'];
        }

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

    // Admin login (supports `account`, `admins`, or legacy `danh_sach_nguoi_dung`)
    public function adminLogin($username, $password) {
        $table = $this->tableExists('account') ? 'account' : ($this->tableExists('admins') ? 'admins' : $this->getUserTable());
        if (!$table) {
            return ['success' => false, 'error' => 'Authentication unavailable'];
        }

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
            $table = $this->getUserTable();
            if (!$table) return null;

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