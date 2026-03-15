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
$fromDate = $_GET['from'] ?? '';
$toDate = $_GET['to'] ?? '';

// 2. Khởi tạo mặc định để tránh lỗi "Undefined variable"
$where = [];
$params = [];
$types = '';
$whereSql = ''; 

// 3. Xây dựng câu lệnh điều kiện (chỉ thêm nếu có dữ liệu)
if ($statusFilter) {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}
if ($search) {
    $where[] = '(u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}
if ($fromDate) {
    $where[] = 'o.created_at >= ?';
    $params[] = $fromDate . ' 00:00:00';
    $types .= 's';
}
if ($toDate) {
    $where[] = 'o.created_at <= ?';
    $params[] = $toDate . ' 23:59:59';
    $types .= 's';
}

// Chỉ gán chuỗi WHERE nếu mảng điều kiện không trống
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

// 4. Chuẩn bị câu lệnh SQL
$sql = "SELECT o.*, u.username, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        $whereSql 
        ORDER BY o.created_at DESC LIMIT 200";

$stmt = $conn->prepare($sql);

// Chỉ thực hiện bind_param nếu có tham số truyền vào
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function statusBadge($status) {
    $map = [
        'pending' => 'warning', 
        'confirmed' => 'info', 
        'shipping' => 'primary', 
        'delivered' => 'success', 
        'cancelled' => 'danger'
    ];
    $cls = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-$cls fw-semibold\">" . ucfirst(htmlspecialchars($status)) . "</span>";
}
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold">Quản lý Đơn hàng <span
        class="badge bg-primary fs-6"><?php echo count($orders); ?></span></h2>
  </div>

  <div class="card shadow-sm border-0 rounded-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-3">ID</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Trạng thái</th>
            <th>Tổng tiền</th>
            <th class="text-end pe-3">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td class="ps-3">#<?php echo $order['id']; ?></td>
            <td>
              <div class="fw-bold"><?php echo htmlspecialchars($order['username']); ?></div>
              <small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
            </td>
            <td><?php echo date('d/m H:i', strtotime($order['created_at'])); ?></td>
            <td><?php echo statusBadge($order['status']); ?></td>
            <td class="text-success fw-bold"><?php echo number_format($order['total']); ?> ₫</td>
            <td class="text-end pe-3">
              <button class="btn btn-sm btn-outline-primary px-3 rounded-pill"
                onclick="openOrderDetails(<?php echo $order['id']; ?>)">
                <i class="fas fa-eye me-1"></i>Xem
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
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 bg-light rounded-top-4">
        <h5 class="modal-title fw-bold">
          <i class="fas fa-box-open me-2 text-primary"></i>
          Chi tiết sản phẩm - Đơn hàng <span id="modalOrderId" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="table-responsive rounded-3 border">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Sản phẩm</th>
                <th class="text-center">SL</th>
                <th class="text-end">Đơn giá</th>
                <th class="text-end pe-3">Thành tiền</th>
              </tr>
            </thead>
            <tbody id="orderItemsContent">
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<script>
function openOrderDetails(orderId) {
  document.getElementById('modalOrderId').innerText = '#' + orderId;
  const content = document.getElementById('orderItemsContent');
  content.innerHTML =
    '<tr><td colspan="4" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';

  const myModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
  myModal.show();

  // Gọi API lấy dữ liệu từ bảng order_items
  fetch(`../api/get_order_items.php?order_id=${orderId}`)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = '';
      if (data.length === 0) {
        content.innerHTML = '<tr><td colspan="4" class="text-center py-4">Không tìm thấy sản phẩm.</td></tr>';
        return;
      }
      data.forEach(item => {
        content.innerHTML += `
                <tr>
                    <td>
                        <div class="fw-bold">${item.product_name}</div>
                        <small class="text-muted small">Mã SP: #${item.product_id}</small>
                    </td>
                    <td class="text-center fw-bold">${item.quantity}</td>
                    <td class="text-end">${new Intl.NumberFormat('vi-VN').format(item.price)}₫</td>
                    <td class="text-end fw-bold text-primary pe-3">${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)}₫</td>
                </tr>
            `;
      });
    })
    .catch(err => {
      content.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Lỗi kết nối máy chủ.</td></tr>';
    });
}
</script>

<?php include 'admin-footer.php'; ?>