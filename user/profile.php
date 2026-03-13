<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);
$user = $auth->getCurrentUser();

$table = get_user_table($conn);
$addressField = $table === 'account' ? 'street_address' : 'address_default';
$phone = $user['phone'] ?? $user['sdt'] ?? $user['phonenumber'] ?? '';

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $address = trim($_POST['address'] ?? '');

    if ($table === 'account') {
        $stmt = $conn->prepare("UPDATE account SET street_address = ? WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE users SET address_default = ? WHERE id = ?");
    }
    $stmt->bind_param("si", $address, $_SESSION['user_id']);
    $stmt->execute();

    $user = $auth->getCurrentUser(); // Refresh user data
    $success = "Địa chỉ đã được cập nhật!";
}

// Get orders (new schema)
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$address = $user[$addressField] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tài khoản - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">Sylphia Shop</a>
      <a href="logout.php" class="btn btn-light ms-auto">Đăng xuất</a>
    </div>
  </nav>

  <div class="container my-5">
    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5>Thông tin cá nhân</h5>
          </div>
          <div class="card-body">
            <p><strong>Tên:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($phone); ?></p>

            <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <h6 class="mt-3">Địa chỉ mặc định:</h6>
            <p id="current_address"><?php echo $address ?: 'Chưa thiết lập'; ?></p>

            <button class="btn btn-primary" onclick="toggleAddressForm()">Chỉnh sửa địa chỉ</button>

            <form id="addressForm" method="POST" style="display: none;" class="mt-3">
              <input type="hidden" name="update_address" value="1">
              <div class="mb-2">
                <textarea name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ giao hàng"
                  required><?php echo htmlspecialchars($address); ?></textarea>
              </div>
              <button type="submit" class="btn btn-success">Lưu địa chỉ</button>
              <button type="button" class="btn btn-secondary" onclick="toggleAddressForm()">Hủy</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <h3>Lịch sử đơn hàng</h3>
        <?php if (empty($orders)): ?>
        <div class="alert alert-info">
          Chưa có đơn hàng nào.
        </div>
        <?php else: ?>
        <div class="row g-3">
          <?php foreach ($orders as $order):
            $status = $order['trangthai'] ?? 'pending';
            $statusClass = ['pending'=>'warning', 'paid'=>'info', 'shipped'=>'primary', 'delivered'=>'success', 'cancelled'=>'danger'][$status] ?? 'secondary';
          ?>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <h6>Mã đơn #<?php echo $order['id']; ?></h6>
                <p>Ngày: <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
                <?php if (!empty($order['dia_chi'])): ?>
                <p><small>Địa chỉ: <?php echo htmlspecialchars($order['dia_chi']); ?></small></p>
                <?php endif; ?>
                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                <p class="fw-bold mt-2"><?php echo formatPrice($order['tongtien']); ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function toggleAddressForm() {
    const form = document.getElementById('addressForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  }
  </script>
</body>

</html>