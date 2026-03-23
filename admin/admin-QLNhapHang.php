<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Lấy danh sách phiếu nhập
$query = "SELECT * FROM purchase_orders ORDER BY created_at DESC";
$result = $conn->query($query);
$purchase_orders = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

function getStatusBadge($status) {
    $map = [
        'pending'   => 'bg-warning text-dark',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    $text = ($status == 'pending') ? 'Chờ nhập' : (($status == 'completed') ? 'Đã nhập' : 'Đã hủy');
    return "<span class='badge $cls px-3 py-2'>$text</span>";
}
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold mb-0">
      <i class="fas fa-file-import me-2 text-secondary"></i>Quản lý Phiếu Nhập Hàng
    </h2>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addImportModal">
      <i class="fas fa-plus me-2"></i>Tạo phiếu mới
    </button>
  </div>

  <div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">ID</th>
            <th>Nhà cung cấp</th>
            <th>Ngày tạo</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th class="text-end pe-3">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($purchase_orders)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">Chưa có phiếu nhập hàng nào.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($purchase_orders as $po): ?>
          <tr>
            <td class="ps-3 fw-bold">#<?php echo $po['id']; ?></td>
            <td><?php echo htmlspecialchars($po['supplier_name']); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($po['created_at'])); ?></td>
            <td class="text-primary fw-bold"><?php echo number_format($po['total_amount']); ?>₫</td>
            <td><?php echo getStatusBadge($po['status']); ?></td>
            <td class="text-end pe-3">
              <button class="btn btn-sm btn-outline-secondary rounded-pill"
                onclick="viewDetails(<?php echo $po['id']; ?>)">
                <i class="fas fa-eye me-1"></i>Chi tiết
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 bg-light rounded-top-4">
        <h5 class="modal-title fw-bold">Chi tiết phiếu nhập #<span id="displayPoId"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="table-responsive rounded-3 border">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Sản phẩm</th>
                <th class="text-center">Số lượng</th>
                <th class="text-end">Giá nhập</th>
                <th class="text-end pe-3">Thành tiền</th>
              </tr>
            </thead>
            <tbody id="detailContent">
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function viewDetails(poId) {
  document.getElementById('displayPoId').innerText = poId;
  const content = document.getElementById('detailContent');
  content.innerHTML =
    '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm me-2"></div> Đang tải...</td></tr>';

  const detailModalElement = document.getElementById('detailModal');
  const myModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
  myModal.show();

  fetch(`../api/get_purchase_details.php?id=${poId}`)
    .then(r => r.json())
    .then(data => {
      if (data.length === 0) {
        content.innerHTML =
          '<tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu chi tiết.</td></tr>';
        return;
      }
      content.innerHTML = '';
      let totalAmount = 0;
      data.forEach(item => {
        let subtotal = item.import_price * item.quantity;
        totalAmount += subtotal;
        content.innerHTML += `
                    <tr>
                        <td class="fw-medium">${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${new Intl.NumberFormat('vi-VN').format(item.import_price)}₫</td>
                        <td class="text-end fw-bold pe-3">${new Intl.NumberFormat('vi-VN').format(subtotal)}₫</td>
                    </tr>
                `;
      });
      // Thêm dòng tổng cộng ở cuối bảng trong Modal
      content.innerHTML += `
                <tr class="table-light">
                    <td colspan="3" class="text-end fw-bold">Tổng tiền phiếu:</td>
                    <td class="text-end fw-bold text-primary pe-3 fs-5">${new Intl.NumberFormat('vi-VN').format(totalAmount)}₫</td>
                </tr>
            `;
    })
    .catch(err => {
      content.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Lỗi kết nối dữ liệu!</td></tr>';
    });
}
</script>

<?php include 'admin-footer.php'; ?>