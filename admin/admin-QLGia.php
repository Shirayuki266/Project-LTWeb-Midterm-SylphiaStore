<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. CẬP NHẬT TỶ LỆ LỢI NHUẬN TỪNG SẢN PHẨM */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_margin'])) {
  $id = intval($_POST['id'] ?? 0);
  $profit_percent = floatval($_POST['profit_percent'] ?? 0);
  if ($profit_percent < 0) {
    $profit_percent = 0;
  }

  $stmt = $conn->prepare("UPDATE products SET profit_percent = ?, price = (IFNULL(cost_price, 0) * (1 + (? / 100))) WHERE id = ?");
  $stmt->bind_param("ddi", $profit_percent, $profit_percent, $id);
  $stmt->execute();

  $redirectParams = [];
  if (isset($_GET['searchProduct']) && $_GET['searchProduct'] !== '') {
    $redirectParams['searchProduct'] = $_GET['searchProduct'];
  }
  if (isset($_GET['searchCost']) && $_GET['searchCost'] !== '') {
    $redirectParams['searchCost'] = $_GET['searchCost'];
  }
  if (isset($_GET['searchPrice']) && $_GET['searchPrice'] !== '') {
    $redirectParams['searchPrice'] = $_GET['searchPrice'];
  }
  if (isset($_GET['searchMargin']) && $_GET['searchMargin'] !== '') {
    $redirectParams['searchMargin'] = $_GET['searchMargin'];
  }

  $queryString = !empty($redirectParams) ? ('?' . http_build_query($redirectParams)) : '';
  header("Location: " . $_SERVER['PHP_SELF'] . $queryString);
  exit();
}

/* 3. LẤY THỐNG KÊ LỢI NHUẬN */
$avg_profit_pct = 0;
$res_avg_pct = $conn->query("SELECT AVG(((price - IFNULL(cost_price, 0)) / NULLIF(cost_price, 0)) * 100) as avg_pct FROM products WHERE cost_price > 0");
if ($res_avg_pct) {
    $row = $res_avg_pct->fetch_assoc();
    $avg_profit_pct = $row['avg_pct'] ?? 0;
}

/* 4. TÌM KIẾM & TRUY VẤN DANH SÁCH */
$search_val = $_GET['searchProduct'] ?? '';
$search_cost = $_GET['searchCost'] ?? '';
$search_price = $_GET['searchPrice'] ?? '';
$search_margin = $_GET['searchMargin'] ?? '';
$search_param = "%$search_val%";
$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE ?
";

$bind_types = "s";
$bind_values = [$search_param];

if ($search_cost !== '' && is_numeric($search_cost)) {
  $sql .= " AND IFNULL(p.cost_price, 0) >= ?";
  $bind_types .= "d";
  $bind_values[] = floatval($search_cost);
}

if ($search_price !== '' && is_numeric($search_price)) {
  $sql .= " AND IFNULL(p.price, 0) >= ?";
  $bind_types .= "d";
  $bind_values[] = floatval($search_price);
}

if ($search_margin !== '' && is_numeric($search_margin)) {
  $sql .= " AND IFNULL(p.profit_percent, 0) >= ?";
  $bind_types .= "d";
  $bind_values[] = floatval($search_margin);
}

$sql .= " ORDER BY p.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($bind_types, ...$bind_values);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid py-4 px-md-4">
  <h2 class="h3 mb-4 fw-bold text-success"><i class="fas fa-tags me-2"></i>Quản lý Giá & Lợi nhuận</h2>

  <div class="row g-3 mb-4">
    <div class="col-md-12">
      <div class="card border-0 shadow-sm bg-primary text-white p-3">
        <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Tỉ lệ lợi nhuận TB</h6>
        <h2 class="fw-bold mb-0"><?php echo number_format($avg_profit_pct, 1); ?>%</h2>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <form method="GET" action="" class="row g-2">
        <div class="col-lg-3 col-md-6">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="searchProduct" class="form-control border-start-0"
              placeholder="Tìm tên sản phẩm..." value="<?php echo htmlspecialchars($search_val); ?>">
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <input type="number" name="searchCost" class="form-control" min="0" step="0.001"
            placeholder="Giá vốn từ..." value="<?php echo htmlspecialchars($search_cost); ?>">
        </div>

        <div class="col-lg-3 col-md-6">
          <input type="number" name="searchPrice" class="form-control" min="0" step="0.001"
            placeholder="Giá bán từ..." value="<?php echo htmlspecialchars($search_price); ?>">
        </div>

        <div class="col-lg-2 col-md-6">
          <input type="number" name="searchMargin" class="form-control" min="0" step="0.1"
            placeholder="Lợi nhuận % từ..." value="<?php echo htmlspecialchars($search_margin); ?>">
        </div>

        <div class="col-lg-1 col-md-12">
          <button type="submit" class="btn btn-primary w-100 fw-bold">Tìm kiếm</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0 rounded-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Sản phẩm</th>
            <th>Giá vốn</th>
            <th>Giá bán</th>
            <th>Lợi nhuận (%)</th>
            <th class="text-end pe-3">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($products) > 0): ?>
          <?php foreach ($products as $p): 
                            $cost = $p['cost_price'] ?? 0;
                            $price = $p['price'] ?? 0;
                            $margin = floatval($p['profit_percent'] ?? 0);
                        ?>
          <tr>
            <td class="ps-3">
              <span class="fw-bold d-block"><?php echo htmlspecialchars($p['name']); ?></span>
              <small class="text-muted"><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></small>
            </td>
            <td><?php echo number_format($cost); ?> đ</td>
            <td class="text-primary fw-bold"><?php echo number_format($price); ?> đ</td>
            <td>
              <span class="badge <?php echo ($margin < 15) ? 'bg-danger' : 'bg-success'; ?>">
                <?php echo number_format($margin, 1); ?>%
              </span>
            </td>
            <td class="text-end pe-3">
              <button
                type="button"
                class="btn btn-sm btn-outline-primary btn-trigger-edit"
                data-id="<?php echo $p['id']; ?>"
                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                data-cost="<?php echo floatval($cost); ?>"
                data-margin="<?php echo floatval($margin); ?>">
                <i class="fas fa-edit me-1"></i>Sửa
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">Không tìm thấy sản phẩm nào.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalMarginEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="">
        <input type="hidden" name="action_update_margin" value="1">
        <input type="hidden" name="id" id="field_id">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold">Cập nhật lợi nhuận: <span id="field_title_name" class="text-primary"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold">Giá vốn hiện tại</label>
            <input type="text" id="field_cost" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold text-success">Tỷ lệ lợi nhuận (%)</label>
            <input type="number" name="profit_percent" id="field_margin" class="form-control" min="0" step="0.1" required>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalElement = document.getElementById('modalMarginEdit');
  const editModal = new bootstrap.Modal(modalElement);
  const fId = document.getElementById('field_id');
  const fName = document.getElementById('field_title_name');
  const fCost = document.getElementById('field_cost');
  const fMargin = document.getElementById('field_margin');

  const formatVn = value => new Intl.NumberFormat('vi-VN').format(Math.round(value));

  document.querySelectorAll('.btn-trigger-edit').forEach(btn => {
    btn.addEventListener('click', function() {
      const cost = parseFloat(this.dataset.cost || '0') || 0;
      const margin = parseFloat(this.dataset.margin || '0') || 0;

      fId.value = this.dataset.id;
      fName.textContent = this.dataset.name;
      fCost.value = formatVn(cost) + ' đ';
      fCost.dataset.raw = String(cost);
      fMargin.value = margin.toFixed(1);

      editModal.show();
    });
  });
});
</script>

<?php include 'admin-footer.php'; ?>