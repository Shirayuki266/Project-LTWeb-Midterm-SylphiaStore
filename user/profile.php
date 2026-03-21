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
        /* 1. CẬP NHẬT THÔNG TIN CÁ NHÂN */
        if (isset($_POST['update_profile'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            
            $p_code = $_POST['city'] ?? '';
            $w_code = $_POST['ward'] ?? '';
            $street = trim($_POST['street_address'] ?? '');

            if (!empty($p_code) && !empty($w_code)) {
                require_once '../api/address.php';
                $address_tool = new Address($conn);
                $full_address = $address_tool->getFullAddressShort($p_code, $w_code, $street);
            } else {
                $full_address = $user['address'] ?? ''; 
            }

            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=? WHERE id=? LIMIT 1");
            $stmt->bind_param("ssssi", $username, $email, $phone, $full_address, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $message = "Cập nhật thông tin thành công!";
                $user['username'] = $username;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['address'] = $full_address;
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
                $message = "Mật khẩu tối thiểu 6 ký tự!";
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

/* 3. LOGIC VIP & ĐƠN HÀNG */
$userId = $_SESSION['user_id'];
$resTotal = $conn->query("SELECT SUM(total) as total_spent FROM orders WHERE user_id = $userId AND status = 'delivered'");
$totalSpent = $resTotal->fetch_assoc()['total_spent'] ?? 0;
$vip_level = $user['vip_level'] ?? 'none';

function translateStatus($status) {
    $map = [
        'pending'   => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
        'confirmed' => '<span class="badge bg-info text-dark">Đã xác nhận</span>',
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

$stmt = $conn->prepare("
    SELECT o.id, o.status, o.total, o.created_at, 
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_summary
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.id DESC LIMIT 10
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Tài khoản - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  .bg-bronze {
    background-color: #cd7f32;
    color: #fff;
  }

  .list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }

  .text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 250px;
  }
  </style>
</head>

<body class="bg-light">

  <div class="container py-5">
    <div class="row">
      <div class="col-md-4 col-lg-3 mb-4">
        <div class="card shadow-sm border-0 mb-4 text-center p-3">
          <img src="../images/avatar.jpg" class="rounded-circle mb-3 border p-1 mx-auto" width="90"
            onerror="this.src='https://via.placeholder.com/90'">
          <h5 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($user['username']); ?></h5>
          <div class="mb-2"><?php echo getVipBadge($vip_level); ?></div>
        </div>
        <div class="list-group shadow-sm">
          <button class="list-group-item list-group-item-action active" onclick="showTab('profile', this)"><i
              class="fa fa-user me-2"></i> Hồ sơ</button>
          <button class="list-group-item list-group-item-action" onclick="showTab('orders', this)"><i
              class="fa fa-shopping-bag me-2"></i> Đơn hàng</button>
          <button class="list-group-item list-group-item-action" onclick="showTab('security', this)"><i
              class="fa fa-lock me-2"></i> Bảo mật</button>
        </div>
      </div>

      <div class="col-md-8 col-lg-9">
        <?php if($message): ?>
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm"><?php echo $message; ?><button
            type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div id="profile-tab" class="tab-content card border-0 shadow-sm p-4">
          <h4 class="mb-4 fw-bold text-primary"><i class="fas fa-user-edit me-2"></i>Thông tin cá nhân</h4>
          <form method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Tên người dùng</label>
                <input type="text" name="username" class="form-control"
                  value="<?php echo htmlspecialchars($user['username']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Email</label>
                <input type="email" name="email" class="form-control"
                  value="<?php echo htmlspecialchars($user['email']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Số điện thoại</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="fas fa-phone-alt text-secondary small"></i></span>
                  <input type="text" name="phone" class="form-control"
                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Địa chỉ hiện tại</label>
                <div class="p-2 border rounded bg-light text-truncate"
                  title="<?php echo htmlspecialchars($user['address'] ?? 'Chưa có'); ?>" style="height: 38px;">
                  <i class="fas fa-map-marker-alt text-danger me-2 small"></i>
                  <small
                    class="fw-medium text-dark"><?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'Chưa cập nhật'; ?></small>
                </div>
              </div>

              <div class="col-12 mt-4">
                <h6 class="fw-bold border-bottom pb-2 text-secondary small text-uppercase">Cập nhật địa chỉ mới</h6>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-primary">Tỉnh/Thành phố mới</label>
                <select class="form-select shadow-none border-primary-subtle" id="city" name="city"></select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-primary">Phường/Xã mới</label>
                <select class="form-select shadow-none border-primary-subtle" id="ward" name="ward">
                  <option value="">Chọn Phường/Xã</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-bold">Số nhà, tên đường mới</label>
                <input type="text" name="street_address" class="form-control shadow-none"
                  placeholder="Bỏ trống nếu muốn giữ nguyên địa chỉ cũ">
              </div>
            </div>
            <div class="mt-4 text-end">
              <button class="btn btn-primary px-5 rounded-pill fw-bold shadow"><i class="fas fa-save me-2"></i>Cập nhật
                hồ sơ</button>
            </div>
          </form>
        </div>

        <div id="orders-tab" class="tab-content d-none card border-0 shadow-sm p-4">
          <h4 class="mb-4 fw-bold text-primary"><i class="fas fa-shopping-bag me-2"></i>Lịch sử đơn hàng</h4>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr class="small text-uppercase">
                  <th>Mã đơn</th>
                  <th>Sản phẩm</th>
                  <th class="text-center">Tổng tiền</th>
                  <th>Trạng thái</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($orders)): ?>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">Bạn chưa có đơn hàng nào.</td>
                </tr>
                <?php else: ?>
                <?php foreach($orders as $o): ?>
                <tr>
                  <td class="fw-bold">#<?php echo $o['id']; ?></td>
                  <td>
                    <div class="text-truncate-2 small text-muted"
                      title="<?php echo htmlspecialchars($o['product_summary']); ?>">
                      <?php echo htmlspecialchars($o['product_summary']); ?></div>
                  </td>
                  <td class="text-center fw-bold text-primary"><?php echo formatPrice($o['total']); ?></td>
                  <td><?php echo translateStatus($o['status']); ?></td>
                  <td><a href="order_detail.php?id=<?php echo $o['id']; ?>"
                      class="btn btn-sm btn-outline-primary rounded-pill px-3">Chi tiết</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div id="security-tab" class="tab-content d-none card border-0 shadow-sm p-4">
          <h4 class="mb-4 fw-bold text-danger"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h4>
          <form method="POST" style="max-width: 500px;">
            <input type="hidden" name="change_password" value="1">
            <div class="mb-3"><label class="form-label small fw-bold">Mật khẩu hiện tại</label><input type="password"
                name="current_password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label small fw-bold">Mật khẩu mới</label><input type="password"
                name="new_password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label small fw-bold">Xác nhận mật khẩu</label><input type="password"
                name="confirm_password" class="form-control" required></div>
            <button class="btn btn-danger px-4 rounded-pill fw-bold">Xác nhận đổi mật khẩu</button>
          </form>
        </div>

      </div>
    </div>
  </div>

  <script>
  const city = document.getElementById("city");
  const ward = document.getElementById("ward");

  fetch('../api/get_location.php?action=get_provinces').then(res => res.json()).then(data => {
    city.innerHTML = '<option value="">Chọn Tỉnh/TP</option>';
    if (Array.isArray(data)) data.forEach(p => city.options.add(new Option(p.name, p.code)));
  });

  city.onchange = () => {
    ward.length = 1;
    if (city.value) {
      fetch(`../api/get_location.php?action=get_wards&province_code=${city.value}`).then(res => res.json()).then(
        data => {
          if (Array.isArray(data)) data.forEach(w => ward.options.add(new Option(w.name, w.code)));
        });
    }
  };

  function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('d-none'));
    document.querySelectorAll('.list-group-item').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId + '-tab').classList.remove('d-none');
    btn.classList.add('active');
  }
  </script>
</body>

</html>