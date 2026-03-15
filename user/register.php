<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);

/* Nếu đã đăng nhập thì chuyển về trang chủ */
if ($auth->isLoggedIn()) {
    header('Location: register.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'address' => trim($_POST['address'] ?? ''),
        'ward' => trim($_POST['ward'] ?? ''),
        'district' => trim($_POST['district'] ?? ''),
        'city' => trim($_POST['city'] ?? '')
    ];

    $result = $auth->register($data);

    if ($result['success']) {

        header("Location: login.php?register=success");
        exit;

    } else {

        $error = $result['error'] ?? implode("<br>", $result['errors'] ?? []);

    }
}
?>

<?php include 'header.php'; ?>

<div class="container-fluid">
  <div class="row vh-100">

    <!-- LEFT IMAGE -->
    <div class="col-lg-6 d-none d-lg-block p-0">
      <img src="../images/logo_login.png" class="w-100 h-100 object-fit-cover">
    </div>

    <!-- RIGHT REGISTER -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">

      <div class="card shadow-lg border-0 rounded-4 p-4" style="width:450px; max-height:90vh; overflow:auto;">

        <h3 class="text-center mb-4">
          Tạo tài khoản
        </h3>

        <?php if ($error): ?>
        <div class="alert alert-danger">
          <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST">

          <div class="row">

            <div class="col-md-6 mb-3">
              <label class="form-label">Tên đăng nhập</label>
              <input type="text" name="username" class="form-control" required
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

          </div>

          <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input type="tel" name="phone" class="form-control" required
              value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Địa chỉ</label>
            <input type="text" name="address" class="form-control"
              value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
          </div>

          <div class="row">

            <div class="col-md-4 mb-3">
              <input type="text" name="ward" class="form-control" placeholder="Phường/Xã"
                value="<?php echo htmlspecialchars($_POST['ward'] ?? ''); ?>">
            </div>

            <div class="col-md-4 mb-3">
              <input type="text" name="district" class="form-control" placeholder="Quận/Huyện"
                value="<?php echo htmlspecialchars($_POST['district'] ?? ''); ?>">
            </div>

            <div class="col-md-4 mb-3">
              <input type="text" name="city" class="form-control" placeholder="Tỉnh/TP"
                value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
            </div>

          </div>

          <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>

          <button class="btn btn-primary w-100 mb-3">
            Tạo tài khoản
          </button>

          <div class="text-center">
            Đã có tài khoản?
            <a href="login.php">Đăng nhập</a>
          </div>

        </form>

      </div>

    </div>

  </div>
</div>

<?php include 'footer.php'; ?>
```