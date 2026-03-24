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
$search       = $_GET['q'] ?? '';
$wardFilter   = $_GET['ward'] ?? '';     
$fromDate     = $_GET['from_date'] ?? ''; 
$toDate       = $_GET['to_date'] ?? '';   

// 2. Xây dựng câu lệnh SQL lọc dữ liệu
$where = [];
$params = [];
$types = '';

if ($statusFilter) {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
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
if ($wardFilter) {
    $where[] = 'o.address LIKE ?';
    $params[] = "%$wardFilter%";
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
        ORDER BY o.created_at DESC LIMIT 1000";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark"><i class="fas fa-clipboard-list me-2 text-primary"></i>Quản lý Đơn hàng</h2>
  </div>

  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label small fw-bold text-secondary">Tìm kiếm khách hàng</label>
          <input type="text" name="q" class="form-control shadow-sm" placeholder="Tên, SĐT, mã đơn..."
            value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold text-secondary">Khu vực (Phường)</label>
          <input type="text" name="ward" class="form-control shadow-sm" placeholder="Tên phường..."
            value="<?= htmlspecialchars($wardFilter) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold text-secondary">Trạng thái</label>
          <select name="status" class="form-select shadow-sm">
            <option value="">Tất cả</option>
            <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>⏳ Chưa xử lý</option>
            <option value="confirmed" <?= $statusFilter == 'confirmed' ? 'selected' : '' ?>>✅ Đã xác nhận</option>
            <option value="delivered" <?= $statusFilter == 'delivered' ? 'selected' : '' ?>>🎉 Giao thành công</option>
            <option value="cancelled" <?= $statusFilter == 'cancelled' ? 'selected' : '' ?>>❌ Đã hủy</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold text-secondary">Từ ngày</label>
          <input type="date" name="from_date" class="form-control shadow-sm" value="<?= $fromDate ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold text-secondary">Đến ngày</label>
          <input type="date" name="to_date" class="form-control shadow-sm" value="<?= $toDate ?>">
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 fw-bold shadow">Lọc</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-3 py-3">Mã đơn</th>
            <th>Khách hàng</th>
            <th>Phường/Địa chỉ</th>
            <th>Ngày đặt</th>
            <th>Trạng thái (Cập nhật)</th>
            <th>Tổng tiền</th>
            <th class="text-end pe-3">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($orders)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào.</td>
          </tr>
          <?php endif; ?>
          <?php foreach ($orders as $order): ?>
          <tr>
            <td class="ps-3 fw-bold text-primary">#<?= $order['id'] ?></td>
            <td>
              <div class="fw-bold"><?= htmlspecialchars($order['username']) ?></div>
              <small class="text-muted"><i
                  class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($order['phone']) ?></small>
            </td>
            <td>
              <small class="d-block text-truncate" style="max-width: 180px;"
                title="<?= htmlspecialchars($order['address']) ?>">
                <?= htmlspecialchars($order['address']) ?>
              </small>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            <td>
              <select class="form-select form-select-sm w-auto border-0 bg-light fw-bold"
                onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Giao thành công
                </option>
                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
              </select>
            </td>
            <td class="fw-bold text-danger"><?= number_format($order['total']) ?> ₫</td>
            <td class="text-end pe-3">
              <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm"
                onclick="openOrderDetails(this)" data-id="<?= $order['id'] ?>"
                data-customer="<?= htmlspecialchars($order['username']) ?>"
                data-phone="<?= htmlspecialchars($order['phone']) ?>"
                data-address="<?= htmlspecialchars($order['address']) ?>"
                data-date="<?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>"
                data-status="<?= $order['status'] ?>" data-total="<?= number_format($order['total']) ?> ₫">
                <i class="fas fa-search-plus me-1"></i> Chi tiết
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
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>ĐƠN HÀNG <span id="modalOrderId"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row mb-4 border-bottom pb-3">
          <div class="col-md-6 border-end">
            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Khách hàng</label>
            <p class="fw-bold mb-3 fs-5" id="info_customer"></p>
            <label class="text-muted small fw-bold text-uppercase mb-1 d-block">Địa chỉ nhận hàng</label>
            <p class="mb-0 text-dark" id="info_address"></p>
          </div>
          <div class="col-md-6 ps-md-4">
            <div class="mb-3">
              <label class="text-muted small fw-bold text-uppercase d-block">Ngày tạo đơn</label>
              <span class="text-dark" id="info_date"></span>
            </div>
            <div class="row">
              <div class="col-6">
                <label class="text-muted small fw-bold text-uppercase d-block">Trạng thái</label>
                <span id="info_status" class="badge p-2"></span>
              </div>
              <div class="col-6 text-end">
                <label class="text-muted small fw-bold text-uppercase d-block text-danger">Tổng thanh toán</label>
                <h4 class="fw-bold text-danger mb-0" id="info_total"></h4>
              </div>
            </div>
          </div>
        </div>

        <h6 class="fw-bold mb-3 text-secondary text-uppercase small"><i class="fas fa-box-open me-2"></i>Sản phẩm đã mua
        </h6>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th class="py-2">Tên sản phẩm</th>
                <th class="text-center py-2" width="100">SL</th>
                <th class="text-end py-2" width="150">Thành tiền</th>
              </tr>
            </thead>
            <tbody id="orderItemsContent"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function openOrderDetails(btn) {
  const d = btn.dataset;

  // Đổ dữ liệu vào Modal
  document.getElementById('modalOrderId').innerText = '#' + d.id;
  document.getElementById('info_customer').innerText = d.customer + ' - ' + d.phone;
  document.getElementById('info_address').innerText = d.address;
  document.getElementById('info_date').innerText = d.date;
  document.getElementById('info_total').innerText = d.total;

  // Xử lý màu sắc Trạng thái
  const badge = document.getElementById('info_status');
  const statusMap = {
    'pending': {
      text: 'CHƯA XỬ LÝ',
      class: 'bg-warning text-dark'
    },
    'confirmed': {
      text: 'ĐÃ XÁC NHẬN',
      class: 'bg-info'
    },
    'delivered': {
      text: 'GIAO THÀNH CÔNG',
      class: 'bg-success'
    },
    'cancelled': {
      text: 'ĐÃ HỦY',
      class: 'bg-danger'
    }
  };
  const current = statusMap[d.status] || {
    text: d.status,
    class: 'bg-secondary'
  };
  badge.innerText = current.text;
  badge.className = 'badge p-2 ' + current.class;

  // Load danh sách sản phẩm
  const content = document.getElementById('orderItemsContent');
  content.innerHTML =
    '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

  const myModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
  myModal.show();

  fetch(`../api/get_order_items.php?order_id=${d.id}`)
    .then(r => r.json())
    .then(data => {
      content.innerHTML = '';
      data.forEach(item => {
        content.innerHTML += `
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">${item.product_name}</div>
                            <small class="text-muted">Đơn giá: ${new Intl.NumberFormat('vi-VN').format(item.price)}₫</small>
                        </td>
                        <td class="text-center fw-bold">${item.quantity}</td>
                        <td class="text-end fw-bold text-primary">${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)}₫</td>
                    </tr>`;
      });
    })
    .catch(() => content.innerHTML =
      '<tr><td colspan="3" class="text-center text-danger">Không thể tải dữ liệu sản phẩm.</td></tr>');
}

function updateOrderStatus(id, status) {
  if (!confirm("Bạn chắc chắn muốn thay đổi trạng thái đơn hàng này?")) return;
  const form = new FormData();
  form.append('order_id', id);
  form.append('status', status);

  fetch('../api/update_order_status.php', {
      method: 'POST',
      body: form
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        alert("Cập nhật trạng thái thành công!");
        location.reload(); // Reload để cập nhật màu sắc và logic lọc
      } else {
        alert("Lỗi: " + res.message);
      }
    })
    .catch(() => alert("Lỗi kết nối máy chủ!"));
}
</script>

<?php include 'admin-footer.php'; ?>