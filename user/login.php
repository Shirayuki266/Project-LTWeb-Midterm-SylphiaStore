<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);

/* 1. NẾU ĐÃ ĐĂNG NHẬP THÌ VỀ TRANG CHỦ (Không cho ở lại trang Login) */
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = "";
$success_msg = "";

/* 2. KIỂM TRA THÔNG BÁO TỪ TRANG ĐĂNG KÝ */
if (isset($_GET['register']) && $_GET['register'] === 'success') {
    $success_msg = "Đăng ký thành công! Vui lòng đăng nhập.";
}

/* 3. XỬ LÝ ĐĂNG NHẬP */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $auth->userLogin($username, $password);

    if ($result['success']) {
        // Lấy trang cần quay lại, nếu không có thì về index.php
        $return = $_GET['return_url'] ?? 'index.php';
        
        // Bảo mật: Tránh bị redirect sang trang web lạ (XSS/Open Redirect)
        if (strpos($return, 'http') !== false) { $return = 'index.php'; }

        header("Location: $return");
        exit;
    } else {
        $error = "Sai tài khoản hoặc mật khẩu";
    }
}

include 'header.php';
?>

<div class="container-fluid bg-white">
  <div class="row vh-100">
    <div class="col-lg-6 d-none d-lg-block p-0">
      <img src="../images/logo_login.png" class="w-100 h-100 object-fit-cover" style="filter: brightness(0.9);">
    </div>

    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
      <div class="card shadow-lg border-0 rounded-4 p-4" style="width:420px; border-radius: 25px !important;">

        <div class="text-center mb-4">
          <h3 class="fw-bold text-dark">Chào mừng trở lại</h3>
          <p class="text-muted">Đăng nhập vào Sylphia Shop của bạn</p>
        </div>

        <?php if($error): ?>
        <div class="alert alert-danger border-0 small text-center rounded-3">
          <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if($success_msg): ?>
        <div class="alert alert-success border-0 small text-center rounded-3">
          <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="px-2">
          <div class="mb-3">
            <label class="form-label small fw-bold">Tên đăng nhập</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user"></i></span>
              <input type="text" name="username" class="form-control border-start-0 py-2" placeholder="Nhập username"
                required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-lock"></i></span>
              <input type="password" name="password" class="form-control border-start-0 py-2" placeholder="••••••••"
                required>
            </div>
          </div>

          <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm mb-4">
            Đăng nhập
          </button>

          <div class="d-flex justify-content-between small">
            <a href="#" class="text-decoration-none text-muted">Quên mật khẩu?</a>
            <a href="register.php" class="text-decoration-none fw-bold text-primary">Tạo tài khoản mới</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>