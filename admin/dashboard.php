<?php
session_start();
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../api/auth.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    header('Location: login.php');
    exit;
}

/* 2. LẤY THỐNG KÊ TỔNG QUAN */
$total_users = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) AS cnt FROM orders")->fetch_assoc()['cnt'] ?? 0;
$total_revenue = $conn->query("SELECT COALESCE(SUM(total), 0) AS rev FROM orders WHERE status != 'cancelled'")->fetch_assoc()['rev'] ?? 0;

// Lấy danh sách sắp hết hàng (Dựa trên cột stock trực tiếp từ products hoặc inventory)
$low_stock = $conn->query("SELECT name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?? [];

$recent_orders = $conn->query("
    SELECT o.id, u.username, o.total, o.status, o.created_at 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC) ?? [];

function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-info text-dark',
        'shipping' => 'bg-primary',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    return $classes[$status] ?? 'bg-secondary';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Sylphia Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <?php include 'header.php'; ?>

  <div class="container-fluid py-4 px-md-4">
    <div class="mb-4">
      <h2 class="fw-bold h4">Tổng Quan Hệ Thống</h2>
      <p class="text-muted small">Chào mừng trở lại, quản trị viên!</p>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 bg-white">
          <div class="card-body d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
              <i class="fas fa-users fa-lg"></i>
            </div>
            <div>
              <div class="text-secondary small fw-bold text-uppercase">Khách hàng</div>
              <h3 class="fw-bold mb-0"><?php echo number_format($total_users); ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card border-0 border-start border-success border-4 shadow-sm h-100 bg-white">
          <div class="card-body d-flex align-items-center">
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
              <i class="fas fa-shopping-cart fa-lg"></i>
            </div>
            <div>
              <div class="text-secondary small fw-bold text-uppercase">Đơn hàng</div>
              <h3 class="fw-bold mb-0"><?php echo number_format($total_orders); ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card border-0 border-start border-info border-4 shadow-sm h-100 bg-white">
          <div class="card-body d-flex align-items-center">
            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
              <i class="fas fa-wallet fa-lg"></i>
            </div>
            <div>
              <div class="text-secondary small fw-bold text-uppercase">Doanh thu</div>
              <h3 class="fw-bold mb-0"><?php echo formatPrice($total_revenue); ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 bg-white">
          <div class="card-body d-flex align-items-center">
            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
              <i class="fas fa-exclamation-triangle fa-lg"></i>
            </div>
            <div>
              <div class="text-secondary small fw-bold text-uppercase">Sắp hết hàng</div>
              <h3 class="fw-bold mb-0"><?php echo count($low_stock); ?></h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Đơn hàng gần đây</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3">ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th class="text-end pe-3">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($recent_orders as $order): ?>
                  <tr>
                    <td class="ps-3 text-muted">#<?php echo $order['id']; ?></td>
                    <td class="fw-bold"><?php echo htmlspecialchars($order['username']); ?></td>
                    <td class="text-success fw-bold"><?php echo formatPrice($order['total']); ?></td>
                    <td>
                      <span class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                      </span>
                    </td>
                    <td class="text-end pe-3">
                      <a href="admin-QLDonHang.php?view=<?php echo $order['id']; ?>"
                        class="btn btn-sm btn-light border">Chi tiết</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100 bg-white">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-rocket me-2 text-danger"></i>Lối tắt quản lý</h5>
          </div>
          <div class="card-body">
            <div class="row g-2">
              <?php
                        $actions = [
                            ['url' => 'admin-QLSP.php',      'icon' => 'box',          'title' => 'Sản phẩm',   'color' => 'primary'],
                            ['url' => 'admin-QLDonHang.php', 'icon' => 'file-invoice', 'title' => 'Đơn hàng',   'color' => 'success'],
                            ['url' => 'admin-QLGia.php',      'icon' => 'tags',         'title' => 'Quản lý giá','color' => 'info'],
                            ['url' => 'admin-QLKho.php',      'icon' => 'warehouse',    'title' => 'Kho hàng',   'color' => 'warning'],
                            ['url' => 'admin-QLPhieuNH.php', 'icon' => 'receipt',      'title' => 'Phiếu nhập', 'color' => 'secondary'],
                            ['url' => 'admin-QLKH.php',      'icon' => 'users',        'title' => 'Khách hàng', 'color' => 'dark']
                        ];
                        foreach($actions as $action): ?>
              <div class="col-6">
                <a href="<?php echo $action['url']; ?>"
                  class="btn btn-outline-light border shadow-sm w-100 py-3 d-flex flex-column align-items-center text-decoration-none">
                  <div
                    class="bg-<?php echo $action['color']; ?> bg-opacity-10 text-<?php echo $action['color']; ?> p-2 rounded-circle mb-2">
                    <i class="fas fa-<?php echo $action['icon']; ?> fa-lg"></i>
                  </div>
                  <span class="small fw-bold text-dark"><?php echo $action['title']; ?></span>
                </a>
              </div>
              <?php endforeach; ?>
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