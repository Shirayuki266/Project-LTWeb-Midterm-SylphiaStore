<?php
// 1. Hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    header('Location: login.php');
    exit;
}

$message = '';

/* 2. XỬ LÝ ẨN SẢN PHẨM (SOFT DELETE) */
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE products SET status = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Đã tạm ẩn sản phẩm!";
    }
}

/* 2.1 XỬ LÝ KHÔI PHỤC SẢN PHẨM */
if (isset($_GET['restore']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE products SET status = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Đã khôi phục sản phẩm để tiếp tục bán!";
    }
}

/* 3. XỬ LÝ THÊM/SỬA SẢN PHẨM (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $description = trim($_POST['description']);
        $discount_price = (isset($_POST['discount_price']) && $_POST['discount_price'] !== '') ? (float)$_POST['discount_price'] : NULL;
        $image = $_POST['current_image'] ?? 'no-image.png';

        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $file_name = time() . '_' . uniqid() . '.' . $ext;
                if (!is_dir("../uploads/")) mkdir("../uploads/", 0777, true);
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], "../uploads/" . $file_name)) {
                    $image = $file_name;
                }
            }
        }

        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, image, category_id, discount_price, description, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sdsids", $name, $price, $image, $category_id, $discount_price, $description);
            $stmt->execute();
            $message = "Thêm thành công!";
        } elseif (isset($_POST['edit'])) {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, image=?, category_id=?, discount_price=?, description=? WHERE id=?");
            $stmt->bind_param("sdsidsi", $name, $price, $image, $category_id, $discount_price, $description, $id);
            $stmt->execute();
            $message = "Cập nhật thành công!";
        }
    } catch (Exception $e) { $message = "Lỗi: " . $e->getMessage(); }
}

/* 4. LẤY DỮ LIỆU */
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.status DESC, p.id DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4 px-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold">🛠 Quản Lý Sản Phẩm</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="prepareAdd()">
      <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
    </button>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-3">ID</th>
            <th>Sản phẩm</th>
            <th>Trạng thái</th>
            <th>Giá bán</th>
            <th>Hình ảnh</th>
            <th class="text-end pe-3">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr class="<?php echo $p['status'] == 0 ? 'bg-light text-muted opacity-75' : ''; ?>">
            <td class="ps-3">#<?php echo $p['id']; ?></td>
            <td>
              <div class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></div>
              <small><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></small>
            </td>
            <td>
              <?php if($p['status'] == 1): ?>
              <span class="badge bg-success">Đang bán</span>
              <?php else: ?>
              <span class="badge bg-secondary">Ngừng bán</span>
              <?php endif; ?>
            </td>
            <td class="fw-bold text-primary"><?php echo number_format($p['price']); ?>₫</td>
            <td>
              <?php 
                            $img = $p['image'];
                            $src = (strpos($img, 'http') === 0) ? $img : "../uploads/".($img ?: 'no-image.png');
                            ?>
              <img src="<?php echo $src; ?>" width="50" height="50" class="rounded border">
            </td>
            <td class="text-end pe-3">
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo $p['id']; ?>)">
                  <i class="fas fa-edit"></i>
                </button>

                <?php if($p['status'] == 1): ?>
                <a href="?delete=1&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-warning"
                  onclick="return confirm('Ẩn sản phẩm này khỏi cửa hàng?')">
                  <i class="fas fa-eye-slash"></i>
                </a>
                <?php else: ?>
                <a href="?restore=1&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-success text-white"
                  onclick="return confirm('Mở bán lại sản phẩm này?')">
                  <i class="fas fa-undo"></i> Khôi phục
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

<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="modalTitle">Sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" name="id" id="prod-id">
        <input type="hidden" name="current_image" id="prod-current-image">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-bold">Tên sản phẩm</label>
            <input type="text" name="name" id="prod-name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Danh mục</label>
            <select name="category_id" id="prod-category" class="form-select">
              <?php foreach ($categories as $c): ?>
              <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Giá bán</label>
            <input type="number" name="price" id="prod-price" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Giá cũ (nếu giảm giá)</label>
            <input type="number" name="discount_price" id="prod-discount" class="form-control">
          </div>
          <div class="col-12 text-center">
            <label class="form-label fw-bold d-block text-start">Ảnh đại diện</label>
            <input type="file" name="image_upload" class="form-control mb-2">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Mô tả chi tiết</label>
            <textarea name="description" id="prod-desc" rows="4" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="submit" name="add" id="btn-add" class="btn btn-primary px-4">Thêm mới</button>
        <button type="submit" name="edit" id="btn-edit" class="btn btn-success px-4" style="display:none;">Lưu thay
          đổi</button>
      </div>
    </form>
  </div>
</div>

<script>
function prepareAdd() {
  document.getElementById('modalTitle').innerText = "Thêm sản phẩm mới";
  document.getElementById('prod-id').value = "";
  document.getElementById('btn-add').style.display = "block";
  document.getElementById('btn-edit').style.display = "none";
  document.querySelector('#productModal form').reset();
  document.getElementById('prod-current-image').value = "no-image.png";
}

function editProduct(id) {
  fetch(`../api/get_product.php?id=${id}`).then(r => r.json()).then(data => {
    if (data.success) {
      const p = data.product;
      document.getElementById('modalTitle').innerText = "Chỉnh sửa: " + p.name;
      document.getElementById('prod-id').value = p.id;
      document.getElementById('prod-name').value = p.name;
      document.getElementById('prod-category').value = p.category_id;
      document.getElementById('prod-price').value = p.price;
      document.getElementById('prod-discount').value = p.discount_price || '';
      document.getElementById('prod-current-image').value = p.image;
      document.getElementById('prod-desc').value = p.description;
      document.getElementById('btn-add').style.display = "none";
      document.getElementById('btn-edit').style.display = "block";
      new bootstrap.Modal(document.getElementById('productModal')).show();
    }
  });
}
</script>

<?php include 'admin-footer.php'; ?>