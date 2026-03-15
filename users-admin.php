<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = '<div class="alert alert-success">Xóa thành công!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Lỗi: ' . $e->getMessage() . '</div>';
    }
}

// Fetch customers
$customers = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="row g-4">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h3 mb-0 fw-bold">Quản lý Users/Admin</h2>
    </div>
    <?php echo $message ?? ''; ?>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="input-group">
          <span class="input-group-text">
            <i class="fas fa-search"></i>
          </span>
          <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm username/email...">
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 fw-semibold">Danh sách users (<?php echo count($customers); ?>)</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody id="customersTable">
              <?php foreach ($customers as $user): ?>
              <tr>
                <th><?php echo $user['id']; ?></th>
                <td>
                  <div class="d-flex align-items-center">
                    <div
                      class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                      style="width: 40px; height: 40px;">
                      <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <div>
                      <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    </div>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                <td>
                  <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : 'bg-success'; ?>">
                    <?php echo ucfirst($user['role'] ?? 'user'); ?>
                  </span>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                <td>
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-info" title="Chi tiết">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" title="Sửa role">
                      <i class="fas fa-user-edit"></i>
                    </button>
                    <a href="?delete=1&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Xóa user này?')" title="Xóa">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($customers)): ?>
              <tr>
                <td colspan="7" class="text-center py-5">
                  <i class="fas fa-users fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">Chưa có user nào</h5>
                  <p class="text-muted">Tạo user đầu tiên qua register.</p>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('searchInput')?.addEventListener('input', function() {
  const term = this.value.toLowerCase();
  document.querySelectorAll('#customersTable tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(term) ? '' : 'none';
  });
});
</script>

<?php include 'admin-footer.php'; ?>