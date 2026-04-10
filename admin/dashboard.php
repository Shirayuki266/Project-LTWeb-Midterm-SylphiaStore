<?php
session_start();
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../api/auth.php';

/* 1. KIỂM TRA ĐĂNG NHẬP (Theo đúng logic file bạn gửi) */
$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    header('Location: login.php');
    exit;
}

/* 2. LẤY THỐNG KÊ TỔNG QUAN */
// Gộp các câu lệnh đếm để tối ưu hiệu suất
$stats_result = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM orders) AS total_orders,
        (SELECT SUM(total) FROM orders WHERE status != 'cancelled') AS total_revenue
");
$stats = $stats_result->fetch_assoc();

/* 3. LẤY DANH SÁCH CẢNH BÁO TỒN KHO (Sắp hết hàng) */
// Lấy những sản phẩm có tồn kho thấp nhất (dưới 10)
$low_stock_query = "
    SELECT p.id, p.name, p.stock, c.name AS category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock <= 10
    ORDER BY p.stock ASC 
    LIMIT 6
";
$low_stock = $conn->query($low_stock_query)->fetch_all(MYSQLI_ASSOC) ?? [];

/* 4. LẤY ĐƠN HÀNG MỚI NHẤT */
$recent_orders_query = "
    SELECT o.id, u.username, o.total, o.status, o.created_at 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC LIMIT 5
";
$recent_orders = $conn->query($recent_orders_query)->fetch_all(MYSQLI_ASSOC) ?? [];

/* 5. HÀM TRẠNG THÁI (Dùng Class Bootstrap) */
function getStatusBadge($status) {
    $status = strtolower($status);
    $classes = [
        'pending'   => 'bg-warning text-dark',
        'delivered' => 'bg-success',
        'shipping'  => 'bg-primary',
        'cancelled' => 'bg-danger'
    ];
    $class = $classes[$status] ?? 'bg-secondary';
    return "<span class='badge $class'>" . ucfirst($status) . "</span>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sylphia Admin - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <?php include 'header.php'; ?>

  <div class="container-fluid py-4 px-md-4">

    <div class="mb-4">
      <h2 class="fw-bold h4">Tổng Quan Hệ Thống</h2>
      <p class="text-muted small">Chào mừng trở lại, quản trị viên <strong>Bảo</strong>!</p>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4 p-3 h-100">
          <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
              <i class="fas fa-users text-primary fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small fw-bold text-uppercase">Khách hàng</div>
              <h3 class="fw-bold mb-0"><?= number_format($stats['total_users']) ?></h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4 p-3 h-100">
          <div class="d-flex align-items-center">
            <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
              <i class="fas fa-shopping-cart text-success fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small fw-bold text-uppercase">Đơn hàng</div>
              <h3 class="fw-bold mb-0"><?= number_format($stats['total_orders']) ?></h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-info border-4 p-3 h-100">
          <div class="d-flex align-items-center">
            <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
              <i class="fas fa-wallet text-info fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small fw-bold text-uppercase">Doanh thu</div>
              <h3 class="fw-bold mb-0"><?= formatPrice($stats['total_revenue'] ?? 0) ?></h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100 bg-white">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Đơn hàng gần đây</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3 border-0">ID</th>
                  <th class="border-0">Khách hàng</th>
                  <th class="border-0">Tổng tiền</th>
                  <th class="border-0">Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($recent_orders as $order): ?>
                <tr>
                  <td class="ps-3 text-muted fw-bold">#<?= $order['id'] ?></td>
                  <td class="fw-bold"><?= htmlspecialchars($order['username']) ?></td>
                  <td class="text-success fw-bold"><?= formatPrice($order['total']) ?></td>
                  <td><?= getStatusBadge($order['status']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-white border-0 text-center py-3">
            <a href="admin-QLDonHang.php" class="small text-decoration-none">Xem toàn bộ đơn hàng <i
                class="fas fa-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100 bg-white">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Cảnh báo kho</h5>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <?php foreach ($low_stock as $item): ?>
              <div class="list-group-item py-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="text-truncate" style="max-width: 75%;">
                    <div class="fw-bold text-dark small text-uppercase"><?= htmlspecialchars($item['name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></small>
                  </div>
                  <span class="badge <?= $item['stock'] <= 2 ? 'bg-dark' : 'bg-danger' ?> rounded-pill">
                    Còn: <?= $item['stock'] ?>
                  </span>
                </div>
                <div class="progress" style="height: 5px;">
                  <div class="progress-bar <?= $item['stock'] <= 2 ? 'bg-dark' : 'bg-danger' ?>" role="progressbar"
                    style="width: <?= ($item['stock'] / 10) * 100 ?>%"></div>
                </div>
                <div class="mt-2 text-end">
                  <a href="admin-QLPhieuNH.php?search=<?= urlencode($item['name']) ?>"
                    class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size: 0.7rem;">
                    NHẬP KHO
                  </a>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if(empty($low_stock)): ?>
              <div class="text-center py-5">
                <i class="fas fa-check-circle text-success fa-3x mb-3 opacity-25"></i>
                <p class="text-muted">Kho hàng hiện tại đã an toàn.</p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin-footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>