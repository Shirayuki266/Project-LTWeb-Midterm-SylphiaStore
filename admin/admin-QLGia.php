<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. LẤY THỐNG KÊ LỢI NHUẬN */
$avg_profit_pct = 0;
// Kiểm tra sự tồn tại của cột cost_price để tránh lỗi SQL
$res_avg_pct = $conn->query("
    SELECT AVG(((price - IFNULL(cost_price, 0)) / NULLIF(cost_price, 0)) * 100) as avg_pct 
    FROM products 
    WHERE cost_price IS NOT NULL AND cost_price > 0
");
if ($res_avg_pct) {
    $row = $res_avg_pct->fetch_assoc();
    $avg_profit_pct = $row['avg_pct'] ?? 0;
}

$avg_order_profit = 0;
try {
    $res_rev = $conn->query("
        SELECT AVG(total - (SELECT SUM(IFNULL(cost_price, 0) * quantity) FROM order_items WHERE order_id = orders.id)) as avg_rev 
        FROM orders WHERE status = 'delivered'
    ");
    if ($res_rev) {
        $row_rev = $res_rev->fetch_assoc();
        $avg_order_profit = $row_rev['avg_rev'] ?? 0;
    }
} catch (Exception $e) {
    $avg_order_profit = 0; 
}

/* 3. TÌM KIẾM VÀ LẤY DANH SÁCH SẢN PHẨM */
$search_val = $_GET['searchProduct'] ?? '';
$search_param = "%$search_val%";
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE ? 
    ORDER BY p.id DESC
");
$stmt->bind_param("s", $search_param);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold">
      <i class="fas fa-tags me-2 text-success"></i>Quản lý Giá & Lợi nhuận
    </h2>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm bg-primary text-white">
        <div class="card-body p-4">
          <h6 class="text-uppercase small fw-bold opacity-75">Tỉ lệ lợi nhuận TB</h6>
          <h2 class="display-6 fw-bold mb-0"><?php echo number_format($avg_profit_pct, 1); ?>%</h2>
          <small>Tính trên giá vốn sản phẩm</small>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm bg-success text-white">
        <div class="card-body p-4">
          <h6 class="text-uppercase small fw-bold opacity-75">Lợi nhuận TB / Đơn hàng</h6>
          <h2 class="display-6 fw-bold mb-0"><?php echo formatPrice($avg_order_profit); ?></h2>
          <small>Dựa trên đơn hàng đã giao</small>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <form method="GET" class="row g-2">
        <div class="col-md-10">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
            <input type="text" name="searchProduct" class="form-control border-start-0"
              placeholder="Tìm sản phẩm để điều chỉnh giá..." value="<?php echo htmlspecialchars($search_val); ?>">
          </div>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white border-bottom py-3">
      <h6 class="mb-0 fw-bold">Bảng giá sản phẩm (<?php echo count($products); ?>)</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Sản phẩm</th>
              <th>Danh mục</th>
              <th>Giá vốn</th>
              <th>Giá bán</th>
              <th>Giảm giá</th>
              <th>Lợi nhuận (%)</th>
              <th class="text-end pe-3">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): 
                            $cost = $p['cost_price'] ?? 0;
                            $price = $p['price'] ?? 0;
                            $profit_margin = ($cost > 0) ? (($price - $cost) / $cost) * 100 : 0;
                        ?>
            <tr>
              <td class="ps-3">
                <div class="d-flex align-items-center">
                  <img src="../uploads/<?php echo htmlspecialchars($p['image'] ?? 'no-image.png'); ?>" width="40"
                    height="40" class="rounded border me-2" onerror="this.src='https://placehold.co/50x50?text=None'">
                  <span class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></span>
                </div>
              </td>
              <td><span
                  class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></span>
              </td>
              <td class="text-muted"><?php echo formatPrice($cost); ?></td>
              <td class="fw-bold text-primary"><?php echo formatPrice($price); ?></td>
              <td>
                <?php if(!empty($p['discount_price'])): ?>
                <span class="text-danger fw-bold"><?php echo formatPrice($p['discount_price']); ?></span>
                <?php else: ?>
                <span class="text-muted small">Không giảm</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="fw-bold <?php echo ($profit_margin < 15) ? 'text-danger' : 'text-success'; ?>">
                  <?php echo number_format($profit_margin, 1); ?>%
                </span>
              </td>
              <td class="text-end pe-3">
                <a href="admin-QLSP.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-edit me-1"></i>Sửa giá
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'admin-footer.php'; ?>