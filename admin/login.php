<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
$error = '';

// 1. Nếu đã đăng nhập rồi thì chuyển thẳng vào trang quản trị
if ($auth->isLoggedIn('admin')) {
    header('Location: dashboard.php'); // Sửa từ dashboard.php thành admin-TongQuan.php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->adminLogin($username, $password);
    
    if ($result['success']) {
        // 2. Thiết lập thêm biến session để tương thích với các trang cũ đang dùng $_SESSION['admin_logged_in']
        $_SESSION['admin_logged_in'] = true; 
        
        header('Location: dashboard.php'); // Chuyển hướng đúng trang tổng quan của bạn
        exit;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập Quản trị - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
  body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .login-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    width: 100%;
    max-width: 400px;
    padding: 2.5rem;
  }
  </style>
</head>

<body>
  <div class="login-card">
    <div class="text-center mb-4">
      <i class="fas fa-user-shield fa-4x text-primary mb-3"></i>
      <h3 class="fw-bold">Sylphia Admin</h3>
      <p class="text-muted">Vui lòng đăng nhập để quản trị</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <div><?php echo htmlspecialchars($error); ?></div>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Tên đăng nhập</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="username" class="form-control" required placeholder="admin">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Mật khẩu</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
        ĐĂNG NHẬP <i class="fas fa-sign-in-alt ms-1"></i>
      </button>
    </form>
  </div>
</body>

</html>