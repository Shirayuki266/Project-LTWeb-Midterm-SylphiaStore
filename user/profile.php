<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';
if (!$auth->isLoggedIn()) {
    // Đuổi ngay ra trang login
    header("Location: login.php");
    exit(); // Bắt buộc phải có exit để dừng load dữ liệu bên dưới
}
$auth = new Auth($conn);

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        /* 1. CẬP NHẬT THÔNG TIN CÁ NHÂN */
        if (isset($_POST['update_profile'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);

            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=? WHERE id=? LIMIT 1");
            $stmt->bind_param("ssssi", $username, $email, $phone, $address, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $message = "Cập nhật thông tin thành công!";
                $user = $auth->getCurrentUser();
            } else {
                $message = "Lỗi cập nhật: " . $conn->error;
            }
        }

        /* 2. ĐỔI MẬT KHẨU */
        if (isset($_POST['change_password'])) {
            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            if ($new !== $confirm) {
                $message = "Mật khẩu xác nhận không khớp!";
            } elseif (strlen($new) < 6) {
                $message = "Mật khẩu phải có ít nhất 6 ký tự!";
            } elseif (!password_verify($current, $user['password'])) {
                $message = "Mật khẩu hiện tại không đúng!";
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=? LIMIT 1");
                $stmt->bind_param("si", $hash, $_SESSION['user_id']);
                $stmt->execute();
                $message = "Đổi mật khẩu thành công!";
            }
        }
    } catch(Exception $e) {
        $message = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

/* 3. LOGIC TÍNH TIẾN TRÌNH VIP */
$userId = $_SESSION['user_id'];
$resTotal = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE user_id = $userId AND status = 'delivered'");
$totalSpent = $resTotal->fetch_assoc()['total_spent'] ?? 0;

$vip_level = $user['vip_level'] ?? 'none';
$next_milestone = 0;
$next_level = "";

if ($totalSpent < 2000000) {
    $next_milestone = 2000000;
    $next_level = "VIP Đồng";
} elseif ($totalSpent < 10000000) {
    $next_milestone = 10000000;
    $next_level = "VIP Bạc";
} elseif ($totalSpent < 50000000) {
    $next_milestone = 50000000;
    $next_level = "VIP Vàng";
}

$percent = ($next_milestone > 0) ? ($totalSpent / $next_milestone) * 100 : 100;
if ($percent > 100) $percent = 100;

// Hàm bổ trợ hiển thị trạng thái tiếng Việt
function translateStatus($status) {
    $map = [
        'pending'   => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
        'confirmed' => '<span class="badge bg-info">Đã xác nhận</span>',
        'delivered' => '<span class="badge bg-success">Đã giao hàng</span>',
        'cancelled' => '<span class="badge bg-danger">Đã hủy</span>'
    ];
    return $map[$status] ?? $status;
}
function getVipBadge($level) {
    $level = mb_strtolower($level, 'UTF-8');

    $map = [
        'none'  => ['label' => 'Thành viên thường', 'class' => 'bg-secondary'],
        'đồng'  => ['label' => 'VIP Đồng',          'class' => 'bg-bronze'],
        'bạc'   => ['label' => 'VIP Bạc',           'class' => 'bg-info text-dark'],
        'vàng'  => ['label' => 'VIP Vàng',          'class' => 'bg-warning text-dark']
    ];

    $data = $map[$level] ?? $map['none'];
    return "<span class='badge {$data['class']} shadow-sm'>{$data['label']}</span>";
}

/* 4. TẢI ĐƠN HÀNG */
$stmt = $conn->prepare("SELECT id, status, total, address, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tài khoản của tôi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  .bg-bronze {
    background-color: #cd7f32;
    color: #fff;
  }

  .bg-info {
    background-color: #C0C0C0 !important;
    color: #333 !important;
  }

  .bg-warning {
    background-color: #FFD700 !important;
  }

  .progress {
    border-radius: 10px;
    background-color: #e9ecef;
  }
  </style>
</head>

<body class="bg-light">

  <div class="container py-5">
    <div class="row">
      <div class="col-md-4 col-lg-3 mb-4">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body text-center">
            <img src="../images/avatar.jpg" class="rounded-circle mb-3 border p-1" width="90" alt="Avatar">
            <h5 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h5>
            <div class="mb-3"><?php echo getVipBadge($vip_level); ?></div>
            <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
          </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="fas fa-crown text-warning"></i> Tiến trình VIP</h6>
            <p class="small text-muted mb-2">Tổng chi tiêu: <strong><?php echo number_format($totalSpent); ?>₫</strong>
            </p>

            <div class="progress mb-2" style="height: 8px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                style="width: <?php echo $percent; ?>%"></div>
            </div>

            <?php if ($next_milestone > 0): ?>
            <p class="x-small text-muted mb-0" style="font-size: 0.75rem;">
              Mua thêm <strong><?php echo number_format($next_milestone - $totalSpent); ?>₫</strong> để lên
              <strong><?php echo $next_level; ?></strong>
            </p>
            <?php else: ?>
            <p class="small text-success fw-bold mb-0">Bạn đã đạt cấp VIP Vàng!</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="list-group list-group-flush shadow-sm rounded">
          <button class="list-group-item list-group-item-action active" onclick="showTab('profile', this)">
            <i class="fa fa-user me-2 text-center" style="width: 20px;"></i> Hồ sơ
          </button>
          <button class="list-group-item list-group-item-action" onclick="showTab('orders', this)">
            <i class="fa fa-shopping-bag me-2 text-center" style="width: 20px;"></i> Đơn hàng
          </button>
          <button class="list-group-item list-group-item-action" onclick="showTab('security', this)">
            <i class="fa fa-lock me-2 text-center" style="width: 20px;"></i> Bảo mật
          </button>
        </div>
      </div>

      <div class="col-md-8 col-lg-9">
        <?php if($message): ?>
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4">
          <?php echo htmlspecialchars($message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div id="profile-tab" class="tab-content">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <h4 class="mb-4">Thông tin cá nhân</h4>
              <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Tên người dùng</label>
                    <input type="text" name="username" class="form-control"
                      value="<?php echo htmlspecialchars($user['username']); ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                      value="<?php echo htmlspecialchars($user['email']); ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                      value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address" class="form-control"
                      value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                  </div>
                </div>
                <button class="btn btn-primary px-4">Lưu thay đổi</button>
              </form>
            </div>
          </div>
        </div>

        <div id="orders-tab" class="tab-content d-none">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <h4 class="mb-4">Lịch sử đơn hàng</h4>
              <?php if(empty($orders)): ?>
              <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-light mb-3"></i>
                <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
                <a href="index.php" class="btn btn-primary btn-sm px-4">Mua sắm ngay</a>
              </div>
              <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Mã đơn</th>
                      <th>Ngày đặt</th>
                      <th>Tổng tiền</th>
                      <th>Trạng thái</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr>
                      <td class="fw-bold">#<?php echo $o['id']; ?></td>
                      <td><?php echo date('d/m/Y', strtotime($o['created_at'])); ?></td>
                      <td class="fw-bold text-primary"><?php echo formatPrice($o['total']); ?></td>
                      <td><?php echo translateStatus($o['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div id="security-tab" class="tab-content d-none">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <h4 class="mb-4">Bảo mật tài khoản</h4>
              <form method="POST">
                <input type="hidden" name="change_password" value="1">
                <div class="mb-3 col-md-8">
                  <label class="form-label">Mật khẩu hiện tại</label>
                  <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3 col-md-8">
                  <label class="form-label">Mật khẩu mới</label>
                  <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3 col-md-8">
                  <label class="form-label">Xác nhận mật khẩu mới</label>
                  <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button class="btn btn-danger px-4">Đổi mật khẩu</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script>
  function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('d-none'));
    document.querySelectorAll('.list-group-item').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId + '-tab').classList.remove('d-none');
    btn.classList.add('active');
  }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>