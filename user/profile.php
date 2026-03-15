<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        /* UPDATE PROFILE */
        if (isset($_POST['update_profile'])) {

            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);

            $stmt = $conn->prepare("
                UPDATE users
                SET username=?, email=?, phone=?, address=?
                WHERE id=?
                LIMIT 1
            ");

            $stmt->bind_param(
                "iiiii",
                $username,
                $email,
                $phone,
                $address,
                $_SESSION['user_id']
            );

            $stmt->execute();

            $message = "Cập nhật thông tin thành công!";
            $user = $auth->getCurrentUser();
        }

        /* CHANGE PASSWORD */
        if (isset($_POST['change_password'])) {

            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            if ($new !== $confirm) {
                $message = "Mật khẩu xác nhận không khớp!";
            }

            elseif (strlen($new) < 6) {
                $message = "Mật khẩu phải có ít nhất 6 ký tự!";
            }

            elseif (!password_verify($current, $user['password'])) {
                $message = "Mật khẩu hiện tại không đúng!";
            }

            else {

                $hash = password_hash($new, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("
                    UPDATE users
                    SET password=?
                    WHERE id=?
                    LIMIT 1
                ");

                $stmt->bind_param("si",$hash,$_SESSION['user_id']);
                $stmt->execute();

                $message = "Đổi mật khẩu thành công!";
            }
        }

    }
    catch(Exception $e){
        $message = "Có lỗi xảy ra.";
    }
}

/* LOAD USER ORDERS */

$stmt = $conn->prepare("
SELECT id, status, total, address, created_at
FROM orders
WHERE user_id=?
ORDER BY id DESC
LIMIT 10
");

$stmt->bind_param("i",$_SESSION['user_id']);
$stmt->execute();

$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Tài khoản</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body class="bg-light">

  <div class="container py-5">

    <div class="row">

      <!-- LEFT MENU -->

      <div class="col-md-3">

        <div class="card shadow-sm">

          <div class="card-body text-center">

            <img src="../images/avatar.jpg" class="rounded-circle mb-3" width="90">

            <h5><?php echo htmlspecialchars($user['username']); ?></h5>

            <p class="text-muted small">
              <?php echo htmlspecialchars($user['email']); ?>
            </p>

          </div>

          <div class="list-group list-group-flush">

            <button class="list-group-item list-group-item-action active" onclick="showTab('profile',this)">
              Thông tin cá nhân
            </button>

            <button class="list-group-item list-group-item-action" onclick="showTab('orders',this)">
              Đơn hàng
            </button>

            <button class="list-group-item list-group-item-action" onclick="showTab('security',this)">
              Bảo mật
            </button>

          </div>

        </div>

      </div>


      <!-- CONTENT -->

      <div class="col-md-9">

        <?php if($message): ?>

        <div class="alert alert-info">
          <?php echo htmlspecialchars($message); ?>
        </div>

        <?php endif; ?>


        <!-- PROFILE TAB -->

        <div id="profile-tab" class="tab-content">

          <div class="card shadow-sm">

            <div class="card-body">

              <h4 class="mb-4">Thông tin cá nhân</h4>

              <form method="POST">

                <input type="hidden" name="update_profile" value="1">

                <div class="row">

                  <div class="col-md-6 mb-3">

                    <label class="form-label">Username</label>

                    <input type="text" name="username" class="form-control"
                      value="<?php echo htmlspecialchars($user['username']); ?>" required>

                  </div>

                  <div class="col-md-6 mb-3">

                    <label class="form-label">Email</label>

                    <input type="email" name="email" class="form-control"
                      value="<?php echo htmlspecialchars($user['email']); ?>" required>

                  </div>

                </div>

                <div class="row">

                  <div class="col-md-6 mb-3">

                    <label class="form-label">Phone</label>

                    <input type="text" name="phone" class="form-control"
                      value="<?php echo htmlspecialchars($user['phone']); ?>">

                  </div>

                  <div class="col-md-6 mb-3">

                    <label class="form-label">Address</label>

                    <input type="text" name="address" class="form-control"
                      value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">

                  </div>

                </div>

                <button class="btn btn-primary">
                  Cập nhật
                </button>

              </form>

            </div>

          </div>

        </div>


        <!-- ORDERS TAB -->

        <div id="orders-tab" class="tab-content d-none">

          <div class="card shadow-sm">

            <div class="card-body">

              <h4 class="mb-4">Lịch sử đơn hàng</h4>

              <?php if(empty($orders)): ?>

              <p class="text-muted">
                Bạn chưa có đơn hàng nào.
              </p>

              <?php else: ?>

              <table class="table">

                <thead>

                  <tr>
                    <th>ID</th>
                    <th>Ngày</th>
                    <th>Tổng</th>
                    <th>Trạng thái</th>
                  </tr>

                </thead>

                <tbody>

                  <?php foreach($orders as $o): ?>

                  <tr>

                    <td>#<?php echo $o['id']; ?></td>

                    <td>
                      <?php echo date('d/m/Y',strtotime($o['created_at'])); ?>
                    </td>

                    <td class="fw-bold text-primary">
                      <?php echo formatPrice($o['tongtien']); ?>
                    </td>

                    <td>
                      <?php echo htmlspecialchars($o['trangthai']); ?>
                    </td>

                  </tr>

                  <?php endforeach; ?>

                </tbody>

              </table>

              <?php endif; ?>

            </div>

          </div>

        </div>


        <!-- SECURITY TAB -->

        <div id="security-tab" class="tab-content d-none">

          <div class="card shadow-sm">

            <div class="card-body">

              <h4 class="mb-4">Đổi mật khẩu</h4>

              <form method="POST">

                <input type="hidden" name="change_password" value="1">

                <div class="mb-3">

                  <label class="form-label">Mật khẩu hiện tại</label>

                  <input type="password" name="current_password" class="form-control" required>

                </div>

                <div class="mb-3">

                  <label class="form-label">Mật khẩu mới</label>

                  <input type="password" name="new_password" class="form-control" required>

                </div>

                <div class="mb-3">

                  <label class="form-label">Xác nhận mật khẩu</label>

                  <input type="password" name="confirm_password" class="form-control" required>

                </div>

                <button class="btn btn-primary">
                  Đổi mật khẩu
                </button>

              </form>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- KẾT THÚC CONTAINER -->

  </div>

  <?php include 'footer.php'; ?>

  <script>
  function showTab(tab, btn) {

    document.querySelectorAll('.tab-content').forEach(t => {
      t.classList.add('d-none');
    });

    document.querySelectorAll('.list-group-item').forEach(b => {
      b.classList.remove('active');
    });

    document.getElementById(tab + '-tab').classList.remove('d-none');

    btn.classList.add('active');

  }
  </script>

</body>

</html>