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
    $new_password = password_hash('123456', PASSWORD_DEFAULT); 
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Đã reset mật khẩu khách hàng #$id về mặc định: 123456";
    } else {
        $_SESSION['error'] = "Lỗi khi reset mật khẩu!";
    }
    // Chuyển hướng về chính trang này để tránh lặp lại hành động khi F5
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

/* 3. XỬ LÝ XÓA KHÁCH HÀNG */
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['message'] = "Đã xóa khách hàng thành công";
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

/* 4. LẤY DANH SÁCH */
$customers = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold"><i class="fas fa-users-cog me-2"></i>Quản lý khách hàng</h2>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
        <input type="text" id="searchInput" class="form-control border-start-0"
          placeholder="Tìm tên, email hoặc số điện thoại...">
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
            <th>Liên hệ</th>
            <th>Ngày tạo</th>
            <th class="text-end pe-3">Hành động</th>
          </tr>
        </thead>
        <tbody id="customersTable">
          <?php foreach ($customers as $c): ?>
          <tr>
            <td class="ps-3 text-muted">#<?php echo $c['id']; ?></td>
            <td>
              <div class="fw-bold"><?php echo htmlspecialchars($c['username'] ?? 'User'); ?></div>
              <small class="text-muted">Tài khoản khách</small>
            </td>
            <td>
              <div><i class="fas fa-envelope me-1 small"></i><?php echo htmlspecialchars($c['email'] ?? 'N/A'); ?></div>
              <div class="small text-muted"><i
                  class="fas fa-phone me-1"></i><?php echo htmlspecialchars($c['phone'] ?? 'N/A'); ?></div>
            </td>
            <td>
              <span class="badge bg-light text-dark border">
                <?php echo isset($c['created_at']) ? date('d/m/Y', strtotime($c['created_at'])) : '--'; ?>
              </span>
            </td>
            <td class="text-end pe-3">
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-info" onclick="viewCustomer(<?php echo $c['id']; ?>)"
                  title="Chi tiết">
                  <i class="fas fa-eye"></i>
                </button>
                <a href="?reset_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-warning"
                  onclick="return confirm('Bạn có chắc muốn đặt lại mật khẩu khách hàng này về 123456?')"
                  title="Reset mật khẩu">
                  <i class="fas fa-key"></i>
                </a>
                <a href="?delete=1&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('Xác nhận xóa vĩnh viễn khách hàng này?')" title="Xóa">
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

<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Thông tin chi tiết</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="customerDetails">
      </div>
    </div>
  </div>
</div>

<?php include 'admin-footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Search thực tế
document.getElementById("searchInput").addEventListener("input", function() {
  let val = this.value.toLowerCase();
  document.querySelectorAll("#customersTable tr").forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
  });
});

// View chi tiết
function viewCustomer(id) {
  fetch(`../api/get_customer.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const c = data.customer;
        document.getElementById("customerDetails").innerHTML = `
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span>ID:</span> <strong>#${c.id}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Username:</span> <strong>${c.username}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Email:</span> <strong>${c.email}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>SĐT:</span> <strong>${c.phone || 'N/A'}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Ngày đăng ký:</span> <strong>${new Date(c.created_at).toLocaleDateString('vi-VN')}</strong></li>
            </ul>
        `;
        // Hiển thị Modal
        var myModal = new bootstrap.Modal(document.getElementById('customerModal'));
        myModal.show();
      } else {
        alert("Lỗi: " + data.message);
      }
    })
    .catch(err => {
      console.error("Lỗi Fetch:", err);
      alert("Không thể kết nối tới máy chủ!");
    });
}
</script>