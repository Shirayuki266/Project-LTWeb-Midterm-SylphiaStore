<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. XỬ LÝ LƯU DỮ LIỆU TẠI CHỖ (Gửi về chính trang này) */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_update'])) {
    $id = intval($_POST['id']);
    $cost = floatval($_POST['cost_price']);
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE products SET cost_price = ?, price = ? WHERE id = ?");
    $stmt->bind_param("ddi", $cost, $price, $id);
    $stmt->execute();
    
    // Chuyển hướng về trang hiện tại để tránh nộp form lại khi F5
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['searchProduct']) ? "?searchProduct=".$_GET['searchProduct'] : ""));
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
        <div class="col-md-10">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="searchProduct" class="form-control border-start-0"
              placeholder="Tìm tên sản phẩm để điều chỉnh giá..." value="<?php echo htmlspecialchars($search_val); ?>">
          </div>
        </div>
        <div class="col-md-2">
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
                            $margin = ($cost > 0) ? (($price - $cost) / $cost) * 100 : 0;
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
              <button type="button" class="btn btn-sm btn-outline-primary btn-trigger-edit"
                data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>"
                data-cost="<?php echo $cost; ?>" data-price="<?php echo $price; ?>">
                <i class="fas fa-edit me-1"></i>Sửa giá
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

<div class="modal fade" id="modalPriceEdit" tabindex="-1" aria-hidden="true border-0">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow border-0">
      <form action="" method="POST">
        <input type="hidden" name="action_update" value="1">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold">Sửa giá: <span id="title_name" class="text-primary"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="field_id">
          <div class="mb-3">
            <label class="form-label fw-bold small">GIÁ VỐN (VNĐ)</label>
            <input type="number" name="cost_price" id="field_cost" class="form-control form-control-lg" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label fw-bold small text-success">LỢI NHUẬN (%)</label>
              <input type="number" id="field_margin" class="form-control border-success" step="0.1">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label fw-bold small text-primary">GIÁ BÁN MỚI</label>
              <input type="number" name="price" id="field_price" class="form-control border-primary fw-bold" required>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary px-4 fw-bold">LƯU THAY ĐỔI</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const editModal = new bootstrap.Modal(document.getElementById('modalPriceEdit'));
  const fCost = document.getElementById('field_cost');
  const fMargin = document.getElementById('field_margin');
  const fPrice = document.getElementById('field_price');

  // Mở và đổ dữ liệu
  document.querySelectorAll('.btn-trigger-edit').forEach(btn => {
    btn.addEventListener('click', function() {
      const cost = parseFloat(this.dataset.cost) || 0;
      const price = parseFloat(this.dataset.price) || 0;

      document.getElementById('field_id').value = this.dataset.id;
      document.getElementById('title_name').innerText = this.dataset.name;
      fCost.value = cost;
      fPrice.value = price;
      fMargin.value = cost > 0 ? ((price - cost) / cost * 100).toFixed(1) : 0;

      editModal.show();
    });
  });

  // Tính Giá bán
  const updateP = () => {
    const cost = parseFloat(fCost.value) || 0;
    const margin = parseFloat(fMargin.value) || 0;
    fPrice.value = Math.round(cost * (1 + margin / 100));
  };

  // Tính % Lợi nhuận
  const updateM = () => {
    const cost = parseFloat(fCost.value) || 0;
    const price = parseFloat(fPrice.value) || 0;
    if (cost > 0) fMargin.value = ((price - cost) / cost * 100).toFixed(1);
  };

  fCost.addEventListener('input', updateP);
  fMargin.addEventListener('input', updateP);
  fPrice.addEventListener('input', updateM);
});
</script>

<?php include 'admin-footer.php'; ?>