<?php
session_start();
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    header('Location: login.php');
    exit;
}

// 1. Khởi tạo các biến bộ lọc
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['q'] ?? '';

// 2. Xây dựng câu lệnh SQL lọc dữ liệu
$where = [];
$params = [];
$types = '';

if ($statusFilter) {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
if ($search) {
    $where[] = '(u.username LIKE ? OR u.phone LIKE ? OR o.id = ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = (int)$search;
    $types .= 'ssi';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT o.*, u.username, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        $whereSql 
        ORDER BY o.created_at DESC LIMIT 100";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">📦 Quản lý Đơn hàng</h2>
    <div class="d-flex gap-2">
      <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control" placeholder="Tìm tên, SĐT, mã đơn..."
          value="<?php echo htmlspecialchars($search); ?>">
        <select name="status" class="form-select" onchange="this.form.submit()">
          <option value="">Tất cả trạng thái</option>
          <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
          <option value="confirmed" <?php echo $statusFilter == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
          <option value="shipping" <?php echo $statusFilter == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
          <option value="delivered" <?php echo $statusFilter == 'delivered' ? 'selected' : ''; ?>>Đã giao</option>
          <option value="cancelled" <?php echo $statusFilter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
        </select>
        <button type="submit" class="btn btn-primary">Lọc</button>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-3">Mã đơn</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Trạng thái (Cập nhật)</th>
            <th>Tổng tiền</th>
            <th class="text-end pe-3">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td class="ps-3 fw-bold">#<?php echo $order['id']; ?></td>
            <td>
              <div><?php echo htmlspecialchars($order['username']); ?></div>
              <small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
            </td>
            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
            <td>
              <select class="form-select form-select-sm w-auto"
                onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Chờ xử lý
                </option>
                <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>✅ Xác nhận
                </option>
                <option value="shipping" <?php echo $order['status'] == 'shipping' ? 'selected' : ''; ?>>🚚 Đang giao
                </option>
                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>🎉 Đã giao
                </option>
                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Hủy đơn
                </option>
              </select>
            </td>
            <td class="fw-bold text-danger"><?php echo number_format($order['total']); ?> ₫</td>
            <td class="text-end pe-3">
              <button class="btn btn-sm btn-info text-white rounded-pill px-3"
                onclick="openOrderDetails(<?php echo $order['id']; ?>)">
                <i class="fas fa-search me-1"></i> Xem chi tiết
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Chi tiết đơn hàng <span id="modalOrderId"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Sản phẩm</th>
              <th class="text-center">Số lượng</th>
              <th class="text-end pe-3">Thành tiền</th>
            </tr>
          </thead>
          <tbody id="orderItemsContent">
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<script>
// 1. Hàm cập nhật trạng thái đơn hàng (Điều chỉnh)
function updateOrderStatus(orderId, newStatus) {
  if (!confirm("Bạn muốn đổi trạng thái đơn hàng #" + orderId + "?")) return;

  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('status', newStatus);

  fetch('../api/update_order_status.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        alert("Đã cập nhật trạng thái!");
      } else {
        alert("Lỗi: " + data.message);
      }
    })
    .catch(err => alert("Lỗi kết nối hệ thống!"));
}

// 2. Hàm xem chi tiết sản phẩm đơn hàng (Xem)
function openOrderDetails(orderId) {
  document.getElementById('modalOrderId').innerText = '#' + orderId;
  const content = document.getElementById('orderItemsContent');
  content.innerHTML =
    '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

  const myModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
  myModal.show();

  fetch(`../api/get_order_items.php?order_id=${orderId}`)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = '';
      if (data.length === 0) {
        content.innerHTML = '<tr><td colspan="3" class="text-center py-4">Không có dữ liệu sản phẩm.</td></tr>';
        return;
      }
      data.forEach(item => {
        content.innerHTML += `
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold">${item.product_name}</div>
                            <small class="text-muted">Giá: ${new Intl.NumberFormat('vi-VN').format(item.price)}₫</small>
                        </td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end pe-3 fw-bold text-primary">${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)}₫</td>
                    </tr>
                `;
      });
    })
    .catch(err => {
      content.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Không thể tải dữ liệu.</td></tr>';
    });
}
</script>

<?php include 'admin-footer.php'; ?>