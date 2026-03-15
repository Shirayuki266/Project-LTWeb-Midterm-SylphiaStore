<?php
session_start();

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$result = $auth->userLogin($username, $password);

if ($result['success']) {

$return = $_GET['return_url'] ?? 'index.php';

header("Location: $return");
exit;

} else {

$error = "Sai tài khoản hoặc mật khẩu";

}
}

include 'header.php';
?>

<div class="container-fluid">

  <div class="row vh-100">

    <!-- LEFT IMAGE -->
    <div class="col-lg-6 d-none d-lg-block p-0">

      <img src="../images/logo_login.png" class="w-100 h-100 object-fit-cover">

    </div>

    <!-- RIGHT LOGIN -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">

      <div class="card shadow-lg border-0 rounded-4 p-4" style="width:420px;">

        <h3 class="text-center mb-4">
          Đăng nhập
        </h3>

        <?php if($error): ?>

        <div class="alert alert-danger">
          <?php echo $error; ?>
        </div>

        <?php endif; ?>

        <form method="POST" class="p-3">

          <div class="input-group mb-4">

            <span class="input-group-text bg-white border-end-0 px-3 py-3">
              <i class="fas fa-user text-secondary fs-5"></i>
            </span>

            <input type="text" name="username" class="form-control border-start-0 py-3 fs-5" placeholder="Username"
              required>

          </div>

          <div class="input-group mb-4">

            <span class="input-group-text bg-white border-end-0 px-3 py-3">
              <i class="fas fa-lock text-secondary fs-5"></i>
            </span>

            <input type="password" name="password" class="form-control border-start-0 py-3 fs-5" placeholder="Mật khẩu"
              required>

          </div>

          <button class="btn btn-primary w-100 rounded-pill py-3 fs-5 mb-4">
            Đăng nhập
          </button>

          <div class="d-flex justify-content-between fs-6">

            <a href="#" class="text-decoration-none text-secondary">
              Quên mật khẩu?
            </a>

            <a href="register.php" class="text-decoration-none text-secondary">
              Tạo tài khoản mới
            </a>

          </div>

        </form>

      </div>

    </div>

  </div>

</div>

<?php include 'footer.php'; ?>