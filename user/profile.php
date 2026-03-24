<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../api/address.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$message = '';

// XỬ LÝ CẬP NHẬT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_profile'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            
            $p_code = $_POST['city'] ?? '';
            $w_code = $_POST['ward'] ?? '';
            $street = trim($_POST['street_address'] ?? '');

            if (!empty($p_code) && !empty($w_code)) {
                $address_tool = new Address($conn);
                $full_address = $address_tool->getFullAddressShort($p_code, $w_code, $street);
            } else {
                $full_address = $user['address'] ?? ''; 
            }

            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, address=? WHERE id=? LIMIT 1");
            $stmt->bind_param("ssssi", $username, $email, $phone, $full_address, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $message = "Cập nhật thông tin thành công!";
                // Cập nhật lại biến $user để hiển thị ngay lập tức
                $user['username'] = $username;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['address'] = $full_address;
            }
        }

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
        $message = "Có lỗi: " . $e->getMessage();
    }
}

// LOGIC VIP & ĐƠN HÀNG
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

// Lấy 10 đơn hàng gần nhất
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

$current_page = 'profile';
$page_title = 'Tài khoản - Sylphia Shop';
include 'header.php';
?>

<style>
.bg-bronze {
  background-color: #cd7f32;
  color: #fff;
}

.list-group-item.active {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  fw-bold;
}

.text-truncate-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<main class="bg-light py-5">
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-lg-3 mb-4">
        <div class="card shadow-sm border-0 mb-4 text-center p-4 rounded-4">
          <div class="position-relative d-inline-block mx-auto mb-3">
            <img src="../images/avatar.jpg" class="rounded-circle border p-1" width="100" height="100"
              style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/100'">
            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle"
              style="width:15px; height:15px;"></span>
          </div>
          <h5 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($user['username']); ?></h5>
          <div class="mb-2"><?php echo getVipBadge($vip_level); ?></div>
        </div>

        <div class="list-group shadow-sm rounded-4 overflow-hidden border-0">
          <button class="list-group-item list-group-item-action active border-0 py-3 fw-bold"
            onclick="showTab('profile', this)">
            <i class="fa fa-user me-2"></i> Hồ sơ của <?php echo htmlspecialchars($user['username']); ?>
          </button>
          <button class="list-group-item list-group-item-action border-0 py-3 fw-bold text-secondary"
            onclick="showTab('orders', this)">
            <i class="fa fa-shopping-bag me-2"></i> Lịch sử đơn hàng
          </button>
          <button class="list-group-item list-group-item-action border-0 py-3 fw-bold text-danger"
            onclick="showTab('security', this)">
            <i class="fa fa-lock me-2"></i> Đổi mật khẩu
          </button>
        </div>
      </div>

      <div class="col-md-8 col-lg-9">
        <?php if($message): ?>
        <div class="alert alert-primary alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3">
          <i class="fas fa-info-circle me-2"></i><?php echo $message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div id="profile-tab" class="tab-content card border-0 shadow-sm p-4 rounded-4">
          <h4 class="mb-4 fw-bold text-dark border-bottom pb-3">
            <i class="fas fa-id-card me-2 text-primary"></i>Thông tin của
            <?php echo htmlspecialchars($user['username']); ?>
          </h4>
          <form method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">Tên hiển thị</label>
                <input type="text" name="username" class="form-control rounded-3"
                  value="<?php echo htmlspecialchars($user['username']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Email cá nhân</label>
                <input type="email" name="email" class="form-control rounded-3"
                  value="<?php echo htmlspecialchars($user['email']); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Số điện thoại</label>
                <input type="text" name="phone" class="form-control rounded-3"
                  value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold text-primary">Địa chỉ gốc (Hiện tại)</label>
                <div class="p-2 border rounded-3 bg-light text-truncate" style="height: 38px;">
                  <i class="fas fa-map-marker-alt text-danger me-2 small"></i>
                  <small
                    class="fw-bold text-dark"><?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : 'Chưa cập nhật địa chỉ'; ?></small>
                </div>
              </div>

              <div class="col-12 mt-4 pt-3 border-top">
                <h6 class="fw-bold text-secondary small text-uppercase mb-3"><i class="fas fa-edit me-2"></i>Thay đổi
                  địa chỉ nhận hàng</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label small">Tỉnh/Thành phố</label>
                    <select class="form-select border-primary-subtle" id="city" name="city"></select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Phường/Xã</label>
                    <select class="form-select border-primary-subtle" id="ward" name="ward">
                      <option value="">Chọn Phường/Xã</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label small">Số nhà, tên đường</label>
                    <input type="text" name="street_address" class="form-control" placeholder="Nhập địa chỉ cụ thể...">
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-4 text-end">
              <button class="btn btn-primary px-5 rounded-pill fw-bold shadow">Cập nhật hồ sơ</button>
            </div>
          </form>
        </div>

        <div id="orders-tab" class="tab-content d-none card border-0 shadow-sm p-4 rounded-4">
          <h4 class="mb-4 fw-bold text-dark border-bottom pb-3"><i class="fas fa-history me-2 text-primary"></i>Lịch sử
            mua hàng</h4>
          <div class="table-responsive">
            <table class="table table-hover align-middle border-0">
              <thead class="table-light">
                <tr class="small text-uppercase">
                  <th class="border-0">Mã đơn</th>
                  <th class="border-0">Sản phẩm</th>
                  <th class="border-0 text-center">Tổng tiền</th>
                  <th class="border-0">Trạng thái</th>
                  <th class="border-0">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($orders)): ?>
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">Bạn chưa có đơn hàng nào.</td>
                </tr>
                <?php else: ?>
                <?php foreach($orders as $o): ?>
                <tr>
                  <td class="fw-bold text-primary">#<?php echo $o['id']; ?></td>
                  <td>
                    <div class="text-truncate-1 small text-muted" style="max-width:250px;">
                      <?php echo htmlspecialchars($o['product_summary']); ?></div>
                  </td>
                  <td class="text-center fw-bold text-dark"><?php echo formatPrice($o['total']); ?></td>
                  <td><?php echo translateStatus($o['status']); ?></td>
                  <td><a href="order_detail.php?id=<?php echo $o['id']; ?>"
                      class="btn btn-sm btn-outline-primary rounded-pill px-3">Xem chi tiết</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div id="security-tab" class="tab-content d-none card border-0 shadow-sm p-4 rounded-4">
          <h4 class="mb-4 fw-bold text-danger border-bottom pb-3"><i class="fas fa-shield-alt me-2"></i>Bảo mật tài
            khoản</h4>
          <form method="POST" style="max-width: 450px;">
            <input type="hidden" name="change_password" value="1">
            <div class="mb-3">
              <label class="form-label small fw-bold">Mật khẩu hiện tại</label>
              <input type="password" name="current_password" class="form-control rounded-3" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Mật khẩu mới</label>
              <input type="password" name="new_password" class="form-control rounded-3" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">Xác nhận mật khẩu</label>
              <input type="password" name="confirm_password" class="form-control rounded-3" required>
            </div>
            <button class="btn btn-danger px-4 rounded-pill fw-bold w-100 shadow">Đổi mật khẩu ngay</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
// Logic Load Địa chỉ
const city = document.getElementById("city");
const ward = document.getElementById("ward");

fetch('../api/get_location.php?action=get_provinces')
  .then(res => res.json())
  .then(data => {
    city.innerHTML = '<option value="">Chọn Tỉnh/TP</option>';
    if (Array.isArray(data)) data.forEach(p => city.options.add(new Option(p.name, p.code)));
  });

city.onchange = () => {
  ward.length = 1;
  if (city.value) {
    fetch(`../api/get_location.php?action=get_wards&province_code=${city.value}`)
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data)) data.forEach(w => ward.options.add(new Option(w.name, w.code)));
      });
  }
};

// Logic Chuyển Tab
function showTab(tabId, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.add('d-none'));
  document.querySelectorAll('.list-group-item').forEach(b => {
    b.classList.remove('active');
    b.classList.add('text-secondary');
  });

  document.getElementById(tabId + '-tab').classList.remove('d-none');
  btn.classList.add('active');
  btn.classList.remove('text-secondary');
}
</script>

<?php include 'footer.php'; ?>