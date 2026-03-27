<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Thêm hoặc sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Tên danh mục không được để trống.';
    } else {
        if ($id > 0) {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param('ssi', $name, $description, $id);
            if ($stmt->execute()) {
                $message = 'Cập nhật danh mục thành công.';
            } else {
                $error = 'Lỗi cập nhật: ' . $stmt->error;
            }
        } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param('ss', $name, $description);
            if ($stmt->execute()) {
                $message = 'Thêm danh mục thành công.';
            } else {
                $error = 'Lỗi thêm: ' . $stmt->error;
            }
        }
    }
    header('Location: admin-QLDanhMuc.php?msg=' . urlencode($message) . '&err=' . urlencode($error));
    exit();
}

// Xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Nếu có sản phẩm thuộc loại này, không xóa trực tiếp (hoặc cần báo)
    $productCount = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE category_id = $id")->fetch_assoc()['cnt'] ?? 0;
    if ($productCount > 0) {
        $error = 'Không thể xóa: danh mục đang chứa sản phẩm.';
    } else {
        $conn->query("DELETE FROM categories WHERE id = $id");
        $message = 'Đã xóa danh mục.';
    }
    header('Location: admin-QLDanhMuc.php?msg=' . urlencode($message) . '&err=' . urlencode($error));
    exit();
}

if (isset($_GET['msg'])) $message = $_GET['msg'];
if (isset($_GET['err'])) $error = $_GET['err'];

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold h4">Quản lý Danh mục</h2>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <form action="" method="POST" class="row g-3 align-items-end">
        <input type="hidden" name="id" id="category-id" value="0">
        <div class="col-md-4">
          <label class="form-label">Tên danh mục</label>
          <input type="text" name="name" id="category-name" class="form-control" placeholder="Ví dụ: Điện thoại" required>
        </div>
        <div class="col-md-5">
          <label class="form-label">Mô tả</label>
          <input type="text" name="description" id="category-description" class="form-control" placeholder="Ví dụ: Điện thoại Apple iPhone">
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary w-100" id="category-submit">Thêm danh mục</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Mô tả</th>
            <th class="text-end">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
          <tr>
            <td>#<?php echo $cat['id']; ?></td>
            <td><?php echo htmlspecialchars($cat['name']); ?></td>
            <td><?php echo htmlspecialchars($cat['description'] ?? ''); ?></td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary me-1" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>" data-description="<?php echo htmlspecialchars($cat['description'] ?? '', ENT_QUOTES); ?>" onclick="editCategory(this)">Sửa</button>
              <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xác nhận xóa?')">Xóa</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function editCategory(button) {
    const id = button.dataset.id;
    const name = button.dataset.name;
  const description = button.dataset.description || '';
    document.getElementById('category-id').value = id;
    document.getElementById('category-name').value = name;
  document.getElementById('category-description').value = description;
    document.getElementById('category-submit').innerText = 'Lưu thay đổi';
}
</script>

<?php include 'admin-footer.php'; ?>
