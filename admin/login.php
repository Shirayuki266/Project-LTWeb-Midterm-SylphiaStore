<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
$error = '';

if ($_POST) {
    $result = $auth->adminLogin($_POST['username'], $_POST['password']);
    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result['error'];
    }
}

if ($auth->isLoggedIn('admin')) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
  }

  .card {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
  <div class="card p-5" style="max-width: 400px; width: 100%;">
    <div class="text-center mb-4">
      <i class="fas fa-store fa-4x text-primary mb-3"></i>
      <h3>Sylphia Admin</h3>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-bold">Tên đăng nhập</label>
        <input type="text" name="username" class="form-control" required value="admin">
      </div>
      <div class="mb-4">
        <label class="form-label fw-bold">Mật khẩu</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
    </form>

    <div class="text-center mt-3">
      <small>Demo: admin / password</small>
    </div>
  </div>
</body>

</html>