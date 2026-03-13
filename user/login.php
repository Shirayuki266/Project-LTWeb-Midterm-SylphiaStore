<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
$error = '';

if ($_POST) {
    $result = $auth->userLogin($_POST['username'], $_POST['password']);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['error'];
    }
}

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
  body {
    background: #f8f9fa;
  }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg">
          <div class="card-body p-5">
            <div class="text-center mb-4">
              <img src="../images/logo-web-removebg-preview.png" alt="Logo" height="60" class="mb-3">
              <h3>Đăng nhập</h3>
              <p class="text-muted">Chào mừng quay lại!</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
              <div class="mb-3">
                <label class="form-label">Tên đăng nhập</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                  <input type="text" name="username" class="form-control" required minlength="3"
                    value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>
              </div>
              <div class="mb-4">
                <label class="form-label">Mật khẩu</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  <input type="password" name="password" class="form-control" required minlength="6">
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>

            <div class="text-center mt-4">
              <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
              <a href="index.php" class="btn btn-link">← Quay lại trang chủ</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    const pass = document.querySelector('input[name="password"]').value;
    if (pass.length < 6) {
      e.preventDefault();
      alert('Mật khẩu ít nhất 6 ký tự');
    }
  });
  </script>
</body>

</html>