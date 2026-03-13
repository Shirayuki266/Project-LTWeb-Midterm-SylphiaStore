<?php
session_start();
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/auth.php';

$auth = new Auth($conn);
if (! $auth->isLoggedIn('admin')) {
    header('Location: admin-DangNhap.php');
    exit;
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$fromDate = $_GET['from'] ?? '';
$toDate = $_GET['to'] ?? '';
$search = $_GET['q'] ?? '';

$where = [];
$params = [];
$types = '';

if ($statusFilter) {
    $where[] = 'd.trangthai = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
if ($search) {
    $where[] = '(u.username LIKE ? OR u.email LIKE ? OR u.phonenumber LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}
if ($fromDate) {
    $where[] = 'd.created_at >= ?';
    $params[] = $fromDate;
    $types .= 's';
}
if ($toDate) {
    $where[] = 'd.created_at <= ?';
    $params[] = $toDate;
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT d.*, u.username, u.email, u.phonenumber FROM donhang d JOIN danh_sach_nguoi_dung u ON d.user_id = u.id $whereSql ORDER BY d.created_at DESC LIMIT 200";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Stats
$stats = $conn->query("SELECT COUNT(*) as total, SUM(tongtien) as revenue FROM donhang")->fetch_assoc();
$statusCountsRes = $conn->query("SELECT trangthai, COUNT(*) as cnt FROM donhang GROUP BY trangthai");
$statusCounts = [];
while ($row = $statusCountsRes->fetch_assoc()) {
    $statusCounts[$row['trangthai']] = $row['cnt'];
}

$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$orderDetail = null;
$orderItems = [];
if ($viewId) {
    $stmt = $conn->prepare("SELECT d.*, u.username, u.email, u.phonenumber FROM donhang d JOIN danh_sach_nguoi_dung u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $orderDetail = $stmt->get_result()->fetch_assoc();

    if ($orderDetail) {
        $stmt = $conn->prepare("SELECT di.*, s.ten AS product_name, s.hinh as product_image FROM donhang_items di LEFT JOIN sanpham s ON di.sanpham_id = s.id WHERE di.donhang_id = ?");
        $stmt->bind_param('i', $viewId);
        $stmt->execute();
        $orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

function statusBadge($status) {
    $map = [
        'pending' => 'warning',
        'paid' => 'info',
        'shipping' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    $cls = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-$cls\">" . htmlspecialchars(ucfirst($status)) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Quản Lý Đơn Hàng | Sylphia Shop</title>
  <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css" />
  <link rel="stylesheet" href="../css/admin.css" />
  <link rel="stylesheet" href="../css/admin-modal.css" />
</head>

<body>
  <div class="admin-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="logo">
        <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
        Sylphia Shop
      </div>
      <ul class="sidebar-menu">
        <li><a href="admin-TongQuan.php"><i class="fas fa-home"></i>Tổng Quan</a></li>
        <li><a href="admin-QLSP.php"><i class="fas fa-box"></i>Sản phẩm</a></li>
        <li><a href="admin-QLPhieuNH.php"><i class="fas fa-receipt"></i>Nhập Hàng</a></li>
        <li><a href="admin-QLKH.php"><i class="fas fa-users"></i>Khách hàng</a></li>
        <li><a href="admin-QLGia.php"><i class="fas fa-tags"></i>Quản lý giá bán</a></li>
        <li><a href="admin-QLDonHang.php" class="active"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
        <li><a href="admin-QLKho.php"><i class="fas fa-warehouse"></i>Tồn kho</a></li>
        <li><a href="../user/trangchu.php"><i class="fas fa-house-user"></i>Trang Chủ</a></li>
        <li><a href="admin-DangNhap.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <div class="top-nav">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <form style="display:inline;" action="admin-QLDonHang.php" method="get">
            <input type="text" name="q" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>" />
          </form>
        </div>
        <div class="user-profile">
          <div class="notifications"><i class="fas fa-bell"></i></div>
          <img src="../images/avatar.jpg" alt="Admin" class="avatar" />
          <span class="admin-name">Admin</span>
        </div>
      </div>

      <div class="dashboard">
        <h1>Quản lý đơn hàng</h1>

        <div class="stats-grid">
          <div class="card">
            <i class="fas fa-shopping-cart"></i>
            <div>
              <h3><?php echo number_format($stats['total'] ?? 0); ?></h3>
              <p>Tổng đơn hàng</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-dollar-sign"></i>
            <div>
              <h3><?php echo formatPrice($stats['revenue'] ?? 0); ?></h3>
              <p>Doanh thu</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-clock"></i>
            <div>
              <h3><?php echo number_format($statusCounts['pending'] ?? 0); ?></h3>
              <p>Chờ xử lý</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-check"></i>
            <div>
              <h3><?php echo number_format($statusCounts['delivered'] ?? 0); ?></h3>
              <p>Đã giao</p>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header d-flex justify-content-between align-items-center">
            <h2>Danh sách đơn hàng</h2>
            <a href="admin-QLDonHang.php" class="btn">Tải lại</a>
          </div>

          <div class="filter-box" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
            <form method="get" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
              <div>
                <label>Trạng thái</label>
                <select name="status">
                  <option value="">Tất cả</option>
                  <option value="pending" <?php echo $statusFilter==='pending' ? 'selected' : ''; ?>>Mới đặt</option>
                  <option value="paid" <?php echo $statusFilter==='paid' ? 'selected' : ''; ?>>Đã thanh toán</option>
                  <option value="shipping" <?php echo $statusFilter==='shipping' ? 'selected' : ''; ?>>Đang giao</option>
                  <option value="delivered" <?php echo $statusFilter==='delivered' ? 'selected' : ''; ?>>Đã giao</option>
                  <option value="cancelled" <?php echo $statusFilter==='cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
              </div>

              <div>
                <label>Từ ngày</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($fromDate); ?>" />
              </div>

              <div>
                <label>Đến ngày</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($toDate); ?>" />
              </div>

              <button class="btn" type="submit">Lọc</button>
              <a class="btn btn-secondary" href="admin-QLDonHang.php">Xóa lọc</a>
            </form>
          </div>

          <div class="table-responsive">
            <table class="manage-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Khách hàng</th>
                  <th>Ngày</th>
                  <th>Trạng thái</th>
                  <th>Tổng tiền</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                  <td colspan="6" class="text-center">Không tìm thấy đơn hàng.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                  <td>#<?php echo $order['id']; ?></td>
                  <td><?php echo htmlspecialchars($order['username']); ?> <br/><small><?php echo htmlspecialchars($order['phonenumber']); ?></small></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                  <td><?php echo statusBadge($order['trangthai']); ?></td>
                  <td><?php echo formatPrice($order['tongtien']); ?></td>
                  <td>
                    <a href="admin-QLDonHang.php?view=<?php echo $order['id']; ?>" class="btn"><i class="fas fa-eye"></i> Xem</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php if ($orderDetail): ?>
          <div class="panel" style="margin-top:1.5rem;">
            <h3>Chi tiết đơn #<?php echo $orderDetail['id']; ?></h3>
            <div class="row" style="gap:1rem;">
              <div class="card" style="flex:1;">
                <div class="card-body">
                  <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($orderDetail['username']); ?></p>
                  <p><strong>Email:</strong> <?php echo htmlspecialchars($orderDetail['email']); ?></p>
                  <p><strong>SĐT:</strong> <?php echo htmlspecialchars($orderDetail['phonenumber']); ?></p>
                  <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($orderDetail['created_at'])); ?></p>
                  <p><strong>Trạng thái:</strong> <?php echo statusBadge($orderDetail['trangthai']); ?></p>
                  <p><strong>Tổng tiền:</strong> <?php echo formatPrice($orderDetail['tongtien']); ?></p>
                </div>
              </div>

              <div class="card" style="flex:1;">
                <div class="card-body">
                  <h4>Danh sách sản phẩm</h4>
                  <table class="manage-table" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sản phẩm</th>
                        <th>SL</th>
                        <th>Giá</th>
                        <th>Tạm tính</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($orderItems as $item): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($item['product_name'] ?? 'Không rõ'); ?></td>
                        <td><?php echo $item['soluong']; ?></td>
                        <td><?php echo formatPrice($item['gia']); ?></td>
                        <td><?php echo formatPrice($item['gia'] * $item['soluong']); ?></td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</body>

</html>
