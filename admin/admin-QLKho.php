<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. LẤY DANH SÁCH SẢN PHẨM (Sử dụng IFNULL để tránh lỗi hiển thị) */
$products = $conn->query("
  SELECT p.*, c.name as category_name 
  FROM products p 
  LEFT JOIN categories c ON p.category_id = c.id 
  ORDER BY p.id DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold">
      <i class="fas fa-warehouse me-2 text-info"></i>Quản lý kho hàng
    </h2>
  </div>

  <?php if (empty($products)): ?>
  <div class="text-center py-5 bg-white rounded shadow-sm">
    <i class="fas fa-boxes fa-4x text-muted mb-4"></i>
    <h4 class="text-muted">Chưa có sản phẩm trong kho</h4>
    <p class="text-muted mb-4">Thêm sản phẩm qua <a href="admin-QLSP.php" class="text-primary text-decoration-none">Quản
        lý SP</a></p>
  </div>
  <?php else: ?>
  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-white border-bottom py-3">
      <h6 class="mb-0 fw-bold">Tồn kho sản phẩm (<?php echo count($products); ?>)</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">ID</th>
              <th>Sản phẩm</th>
              <th>Danh mục</th>
              <th>Tồn kho</th>
              <th>Giá bán</th>
              <th>Trạng thái</th>
              <th class="text-end pe-3">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): 
                            // Sửa lỗi Undefined index bằng ??
                            $stock = $product['stock'] ?? 0;
                            if ($stock == 0) {
                                $status = 'Hết hàng';
                                $badge = 'bg-danger';
                            } elseif ($stock < 5) {
                                $status = 'Sắp hết';
                                $badge = 'bg-warning text-dark';
                            } else {
                                $status = 'Đầy đủ';
                                $badge = 'bg-success';
                            }
                        ?>
            <tr>
              <td class="ps-3 text-muted">#<?php echo $product['id']; ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="../uploads/<?php echo htmlspecialchars($product['image'] ?? 'no-image.png'); ?>"
                    class="rounded-2 border shadow-sm me-3" width="45" height="45"
                    onerror="this.src='https://placehold.co/50x50?text=None'">
                  <div>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($product['name']); ?></div>
                    <small
                      class="text-muted"><?php echo mb_substr($product['description'] ?? '', 0, 40, 'UTF-8'); ?>...</small>
                  </div>
                </div>
              </td>
              <td>
                <span
                  class="badge bg-light text-dark border"><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></span>
              </td>
              <td>
                <span class="fw-bold fs-5 text-dark"><?php echo number_format($stock); ?></span>
                <?php if ($stock < 5): ?>
                <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">CẢNH BÁO</span>
                <?php endif; ?>
              </td>
              <td><span class="text-primary fw-bold"><?php echo number_format($product['price'] ?? 0); ?>₫</span></td>
              <td>
                <span class="badge <?php echo $badge; ?> px-3 py-2" style="min-width: 85px;">
                  <?php echo $status; ?>
                </span>
              </td>
              <td class="text-end pe-3">
                <div class="btn-group btn-group-sm">
                  <a href="admin-QLSP.php?edit=<?php echo $product['id']; ?>" class="btn btn-outline-secondary"
                    title="Cập nhật">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button class="btn btn-outline-success" onclick="updateStock(<?php echo $product['id']; ?>)"
                    title="Nhập kho">
                    <i class="fas fa-plus"></i>
                  </button>
                  <a href="#" class="btn btn-outline-danger" onclick="return confirm('Xác nhận xóa bản ghi kho này?')"
                    title="Xóa">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="stockForm" class="modal-content shadow border-0">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Cập nhật tồn kho</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="stockProductId" name="id">
        <div class="mb-3">
          <label class="form-label fw-bold">Số lượng nhập thêm</label>
          <input type="number" name="quantity" class="form-control form-control-lg" min="1" required
            placeholder="Nhập số lượng...">
          <small class="text-muted mt-1 d-block italic">* Số lượng này sẽ cộng dồn vào tồn kho hiện tại.</small>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-primary px-4" onclick="saveStock()">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

<script>
function updateStock(id) {
  document.getElementById('stockProductId').value = id;
  new bootstrap.Modal(document.getElementById('stockModal')).show();
}

function saveStock() {
  const formData = new FormData(document.getElementById('stockForm'));
  fetch('api/inventory.php', {
    method: 'POST',
    body: formData
  }).then(r => r.json()).then(data => {
    if (data.success) {
      alert('Cập nhật tồn kho thành công!');
      location.reload();
    } else {
      alert('Lỗi: ' + data.error);
    }
  }).catch(err => {
    alert('Lỗi kết nối máy chủ!');
  });
}
</script>

<?php include 'admin-footer.php'; ?>