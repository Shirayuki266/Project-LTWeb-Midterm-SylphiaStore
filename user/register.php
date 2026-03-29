<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nối chuỗi địa chỉ đầy đủ giống logic trong file Shipping
    $street = trim($_POST['address'] ?? '');
    $ward_name = $_POST['ward_name'] ?? ''; 
    $city_name = $_POST['city_name'] ?? ''; 

    $full_address = $street;
    if (!empty($ward_name)) $full_address .= ", " . $ward_name;
    if (!empty($city_name)) $full_address .= ", " . $city_name;

    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'address' => $full_address,
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

<style>
/* Style cho thông báo lỗi giống hệ thống Shipping */
.error-message {
  color: #e74c3c;
  font-size: 0.75rem;
  display: none;
  margin-top: 4px;
  font-weight: 500;
}

.input-error {
  border-color: #e74c3c !important;
  background-color: #fff6f6;
}
</style>

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

        <form method="POST" id="registerForm" novalidate>
          <input type="hidden" name="city_name" id="city_name">
          <input type="hidden" name="ward_name" id="ward_name">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold">Tên đăng nhập <span class="text-danger">*</span></label>
              <input type="text" name="username" id="username" class="form-control"
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
              <span id="err-username" class="error-message"></span>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" id="email" class="form-control"
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
              <span id="err-email" class="error-message"></span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Số điện thoại <span class="text-danger">*</span></label>
            <input type="tel" name="phone" id="phone" class="form-control"
              value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            <span id="err-phone" class="error-message"></span>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold text-primary">Tỉnh/Thành phố <span
                  class="text-danger">*</span></label>
              <select class="form-select shadow-none" id="city" name="city"></select>
              <span id="err-city" class="error-message"></span>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label small fw-bold text-primary">Phường/Xã <span class="text-danger">*</span></label>
              <select class="form-select shadow-none" id="ward" name="ward">
                <option value="">Chọn Phường/Xã</option>
              </select>
              <span id="err-ward" class="error-message"></span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Số nhà, tên đường <span class="text-danger">*</span></label>
            <input type="text" name="address" id="address" class="form-control" placeholder="VD: 123 Đường ABC..."
              value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
            <span id="err-address" class="error-message"></span>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Mật khẩu <span class="text-danger">*</span></label>
            <input type="password" name="password" id="password" class="form-control">
            <span id="err-password" class="error-message"></span>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
            <span id="err-confirm_password" class="error-message"></span>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 rounded-pill fw-bold shadow-sm">
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
const $ = (id) => document.getElementById(id);

// 1. Hàm kiểm tra lỗi Inline 
function validateField(id) {
  const el = $(id);
  if (!el) return true;

  const val = el.value.trim();
  let err = "";

  // Kiểm tra trống (Ưu tiên hàng đầu)
  if (val === "") {
    err = (id === "city" || id === "ward") ? "* Vui lòng chọn trường này." : "* Thông tin này không được để trống.";
  } else {
    // Kiểm tra định dạng cụ thể
    switch (id) {
      case 'username':
        if (val.length < 5) err = "* Tên đăng nhập phải ít nhất 5 ký tự.";
        else if (/[^A-Za-z0-9]/.test(val)) err = "* Chỉ dùng chữ cái và số.";
        break;
      case 'email':
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(val)) err = "* Email không hợp lệ.";
        break;
      case 'phone':
        if (!/^0\d{9}$/.test(val)) err = "* SĐT phải gồm 10 số (bắt đầu bằng 0).";
        break;
      case 'address':
        if (!val.includes(" ")) err = "* Vui lòng nhập chi tiết số nhà và tên đường.";
        break;
      case 'password':
        if (val.length < 6) err = "* Mật khẩu phải tối thiểu 6 ký tự.";
        break;
      case 'confirm_password':
        if (val !== $('password').value) err = "* Mật khẩu xác nhận không khớp.";
        break;
    }
  }

  // Hiển thị lỗi lên giao diện
  const errEl = $("err-" + id);
  if (errEl) {
    if (err) {
      errEl.innerText = err;
      errEl.style.display = "block";
      el.classList.add("input-error");
    } else {
      errEl.style.display = "none";
      el.classList.remove("input-error");
    }
  }
  return err === "";
}

// 2. Logic tải địa chỉ 
fetch('../api/get_location.php?action=get_provinces')
  .then(res => res.json())
  .then(data => {
    $('city').innerHTML = '<option value="">Chọn Tỉnh/TP</option>';
    data.forEach(p => $('city').options.add(new Option(p.name, p.code)));
  });

$('city').onchange = () => {
  validateField('city');
  $('ward').length = 1;
  $('city_name').value = $('city').options[$('city').selectedIndex].text;

  if ($('city').value) {
    fetch(`../api/get_location.php?action=get_wards&province_code=${$('city').value}`)
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data)) {
          data.forEach(w => $('ward').options.add(new Option(w.name, w.code)));
        }
      });
  }
};

$('ward').onchange = () => {
  validateField('ward');
  $('ward_name').value = $('ward').options[$('ward').selectedIndex].text;
};

// 3. Lắng nghe sự kiện để kiểm tra thời gian thực 
const inputs = ["username", "email", "phone", "address", "password", "confirm_password"];
inputs.forEach(id => {
  $(id).oninput = () => validateField(id);
});

// 4. Kiểm tra lần cuối khi nhấn Submit 
$('registerForm').onsubmit = function(e) {
  let isValid = true;
  const allFields = [...inputs, "city", "ward"];

  allFields.forEach(id => {
    if (!validateField(id)) isValid = false;
  });

  if (!isValid) {
    e.preventDefault(); // Chặn gửi form nếu còn lỗi
    alert("Vui lòng kiểm tra và sửa các thông tin bị lỗi màu đỏ!");
  }
};
</script>