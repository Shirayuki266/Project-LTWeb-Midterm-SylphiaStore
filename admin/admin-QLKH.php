<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. XỬ LÝ RESET MẬT KHẨU */
if (isset($_GET['reset_id'])) {
    $id = (int)$_GET['reset_id'];
    $new_pw = password_hash('123456', PASSWORD_DEFAULT); 
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_pw, $id);
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Đã reset mật khẩu KH #$id về: 123456";
    }
    header("Location: admin-QLKH.php");
    exit();
}

/* 3. XỬ LÝ KHÓA/MỞ KHÓA (Điều khiển cột status có sẵn) */
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['id'];
    $new_status = (int)$_GET['set']; // 0: Khóa, 1: Mở
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    $_SESSION['msg'] = ($new_status == 0) ? "Đã khóa tài khoản #$id" : "Đã mở khóa tài khoản #$id";
    header("Location: admin-QLKH.php");
    exit();
}

$message = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

/* 4. LẤY DANH SÁCH KHÁCH HÀNG (Chỉ lấy role 'customer') */
$sql = "SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC";
$customers = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold text-dark"><i class="fas fa-users-cog me-2 text-primary"></i>Quản lý Khách hàng</h2>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= $message ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
        <input type="text" id="searchInput" class="form-control border-start-0"
          placeholder="Tìm tên, email, SĐT hoặc mã khách hàng...">
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0 rounded-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">ID</th>
            <th>Khách hàng</th>
            <th>Trạng thái</th>
            <th>Ngày tham gia</th>
            <th class="text-end pe-3">Hành động</th>
          </tr>
        </thead>
        <tbody id="customersTable">
          <?php foreach ($customers as $c): ?>
          <tr class="<?= ($c['status'] == 0) ? 'table-light opacity-75' : '' ?>">
            <td class="ps-3 text-muted">#<?= $c['id'] ?></td>
            <td>
              <div class="fw-bold"><?= htmlspecialchars($c['username']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
            </td>
            <td>
              <?php if($c['status'] == 1): ?>
              <span class="badge bg-success shadow-sm">Hoạt động</span>
              <?php else: ?>
              <span class="badge bg-danger shadow-sm">Bị khóa</span>
              <?php endif; ?>
            </td>
            <td><small><?= date('d/m/Y', strtotime($c['created_at'])) ?></small></td>
            <td class="text-end pe-3">
              <div class="btn-group shadow-sm bg-white rounded">
                <button class="btn btn-sm btn-outline-secondary" onclick="viewCustomer(<?= $c['id'] ?>)"
                  title="Xem chi tiết">
                  <i class="fas fa-eye text-info"></i>
                </button>
                <a href="?reset_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Đặt lại mật khẩu về 123456?')" title="Reset mật khẩu">
                  <i class="fas fa-key text-warning"></i>
                </a>
                <?php if($c['status'] == 1): ?>
                <a href="?toggle_status=1&id=<?= $c['id'] ?>&set=0" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Khóa tài khoản này?')" title="Khóa tài khoản">
                  <i class="fas fa-user-slash text-danger"></i>
                </a>
                <?php else: ?>
                <a href="?toggle_status=1&id=<?= $c['id'] ?>&set=1" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Mở khóa tài khoản này?')" title="Mở khóa tài khoản">
                  <i class="fas fa-user-check text-success"></i>
                </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">Chi tiết khách hàng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="customerDetails"></div>
    </div>
  </div>
</div>

<?php include 'admin-footer.php'; ?>

<script>
// Search Client-side
document.getElementById("searchInput").addEventListener("input", function() {
  let val = this.value.toLowerCase();
  document.querySelectorAll("#customersTable tr").forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
  });
});

// Load Modal chi tiết
function viewCustomer(id) {
  fetch(`../api/get_customer.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const c = data.customer;
        document.getElementById("customerDetails").innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between"><span>ID:</span> <strong>#${c.id}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Hạng:</span> <strong class="text-uppercase text-primary">${c.vip_level}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Họ tên:</span> <strong>${c.username}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Email:</span> <strong>${c.email}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>SĐT:</span> <strong>${c.phone || 'N/A'}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Địa chỉ:</span> <strong>${c.address || 'N/A'}</strong></div>
                </div>
            `;
        new bootstrap.Modal(document.getElementById('customerModal')).show();
      }
    });
}
</script>