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
    header('Location: index.php'); // Sửa lại thành index.php cho hợp lý
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Thu thập dữ liệu và nối chuỗi địa chỉ đầy đủ trước khi gửi vào Auth
    $p_code = $_POST['city'] ?? '';
    $w_code = $_POST['ward'] ?? '';
    $street = trim($_POST['address'] ?? '');

    // Bạn có thể xử lý nối chuỗi ở đây hoặc trong Class Auth tùy thiết kế của bạn
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'address' => $street,
        'ward' => $w_code,      // Gửi mã code phường/xã
        'city' => $p_code       // Gửi mã code tỉnh/tp
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
    <div class="col-lg-6 d-none d-lg-block p-0">
      <img src="../images/logo_login.png" class="w-100 h-100 object-fit-cover">
    </div>

    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
      <div class="card shadow-lg border-0 rounded-4 p-4" style="width:450px; max-height:95vh; overflow:auto;">
        <h3 class="text-center mb-4 fw-bold text-primary">Tạo tài khoản</h3>

        <?php if ($error): ?>
        <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold">Tên đăng nhập</label>
              <input type="text" name="username" class="form-control" required
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold">Email</label>
              <input type="email" name="email" class="form-control" required
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Số điện thoại</label>
            <input type="tel" name="phone" class="form-control" required
              value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold text-primary">Tỉnh/Thành phố</label>
              <select class="form-select shadow-none" id="city" name="city" required></select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold text-primary">Phường/Xã</label>
              <select class="form-select shadow-none" id="ward" name="ward" required>
                <option value="">Chọn Phường/Xã</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Số nhà, tên đường</label>
            <input type="text" name="address" class="form-control" placeholder="VD: 123 Đường ABC..."
              value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>

          <button class="btn btn-primary btn-lg w-100 mb-3 rounded-pill fw-bold shadow-sm">
            Tạo tài khoản
          </button>

          <div class="text-center small">
            Đã có tài khoản? <a href="login.php" class="text-decoration-none fw-bold">Đăng nhập ngay</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
const city = document.getElementById("city");
const ward = document.getElementById("ward");

// 1. Tải danh sách 34 tỉnh
fetch('../api/get_location.php?action=get_provinces')
  .then(res => res.json())
  .then(data => {
    city.innerHTML = '<option value="">Chọn Tỉnh/TP</option>';
    data.forEach(p => city.options.add(new Option(p.name, p.code)));
  });

// 2. Khi chọn Tỉnh -> Tải thẳng Xã (Bỏ qua Huyện)
city.onchange = () => {
  ward.length = 1;
  if (city.value) {
    fetch(`../api/get_location.php?action=get_wards&province_code=${city.value}`)
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data)) {
          data.forEach(w => ward.options.add(new Option(w.name, w.code)));
        }
      });
  }
};
</script>