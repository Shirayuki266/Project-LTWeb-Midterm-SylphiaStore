<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../api/products.php';

if (! (new Auth($conn))->isLoggedIn('admin')) {
    header('Location: login.php');
    exit;
}

// Stats (legacy schema)
$total_users = $conn->query("SELECT COUNT(*) as cnt FROM danh_sach_nguoi_dung")->fetch_assoc()['cnt'];
$total_orders = $conn->query("SELECT COUNT(*) as cnt FROM donhang")->fetch_assoc()['cnt'];
$total_revenue = $conn->query("SELECT SUM(tongtien) as rev FROM donhang WHERE trangthai != 'cancelled'")->fetch_assoc()['rev'];
$low_stock = []; // No inventory tracking in current schema

$recent_orders = $conn->query("SELECT * FROM donhang ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Admin Dashboard - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
      <div>
        <span class="navbar-text me-3">Admin</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Đăng xuất</a>
      </div>
    </div>
  </nav>

  <div class="container-fluid mt-4">
    <div class="row">
      <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h3><?php echo $total_users; ?></h3>
                <p>Tổng khách hàng</p>
              </div>
              <i class="fas fa-users fa-3x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-success text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h3><?php echo $total_orders; ?></h3>
                <p>Tổng đơn hàng</p>
              </div>
              <i class="fas fa-shopping-bag fa-3x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-info text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h3><?php echo formatPrice($total_revenue ?: 0); ?></h3>
                <p>Doanh thu</p>
              </div>
              <i class="fas fa-dollar-sign fa-3x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h3><?php echo count($low_stock); ?></h3>
                <p>Hàng sắp hết</p>
              </div>
              <i class="fas fa-exclamation-triangle fa-3x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between">
            <h5>Đơn hàng gần đây</h5>
            <a href="orders.php" class="btn btn-sm btn-primary">Xem tất cả</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Mã đơn</th>
                    <th>Khách</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_orders as $o):
                        $statusClass = str_replace('success', 'bg-success', str_replace('warning', 'bg-warning', $o['trangthai']));
                    ?>
                  <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td>User <?php echo $o['user_id']; ?></td>
                    <td><?php echo formatPrice($o['tongtien']); ?></td>
                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($o['trangthai']); ?></span></td>
                    <td><?php echo date('d/m H:i', strtotime($o['created_at'])); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h6>Cảnh báo tồn kho</h6>
          </div>
          <div class="card-body">
            <?php if (empty($low_stock)): ?>
            <p class="text-success">Tất cả ổn!</p>
            <?php else: ?>
            <ul class="list-unstyled">
              <?php foreach ($low_stock as $item): ?>
              <li class="text-warning"><i class="fas fa-exclamation-triangle"></i> <?php echo $item['name']; ?>:
                <?php echo $item['stock']; ?> sp</li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6>Nhanh</h6>
          </div>
          <div class="card-body">
            <a href="products-admin.php" class="btn btn-outline-primary w-100 mb-2">Quản lý SP</a>
            <a href="orders-admin.php" class="btn btn-outline-success w-100 mb-2">Đơn hàng</a>
            <a href="inventory.php" class="btn btn-outline-warning w-100 mb-2">Nhập hàng</a>
            <a href="users-admin.php" class="btn btn-outline-info w-100">Khách hàng</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>