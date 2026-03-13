<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);
$error = '';

if ($_POST) {
    $data = [
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'address' => trim($_POST['address'])
    ];
    $result = $auth->register($data);
    if ($result['success']) {
        header('Location: login.php?reg=success');
        exit;
    } else {
        $error = implode('<br>', $result['errors'] ?? [$result['error'] ?? 'Error']);
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
  <title>Đăng ký - Sylphia Shop</title>
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
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg">
          <div class="card-body p-5">
            <div class="text-center mb-5">
              <img src="../images/logo-web-removebg-preview.png" alt="Logo" height="60" class="mb-3">
              <h3>Tạo tài khoản mới</h3>
              <p class="text-muted">Đăng ký để mua sắm nhanh hơn!</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên đăng nhập</label>
                  <input type="text" name="username" class="form-control" required minlength="3"
                    value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" required
                    value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">SĐT</label>
                  <input type="tel" name="phone" class="form-control" required
                    value="<?php echo $_POST['phone'] ?? ''; ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Địa chỉ mặc định</label>
                  <textarea name="address" class="form-control"
                    rows="2"><?php echo $_POST['address'] ?? ''; ?></textarea>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required minlength="6">
              </div>
              <div class="mb-4">
                <label class="form-label">Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
              </div>
              <button type="submit" class="btn btn-primary w-100">Tạo tài khoản</button>
            </form>

            <div class="text-center mt-4">
              <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
              <a href="index.php" class="btn btn-link">← Trang chủ</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.getElementById('registerForm').addEventListener('submit', function(e) {
    const pass = document.querySelector('[name="password"]').value;
    const confirm = document.querySelector('[name="confirm_password"]').value;
    if (pass.length < 6 || pass !== confirm) {
      e.preventDefault();
      alert('Mật khẩu không hợp lệ hoặc không khớp!');
      return false;
    }
    return true;
  });
  </script>
</body>

</html>