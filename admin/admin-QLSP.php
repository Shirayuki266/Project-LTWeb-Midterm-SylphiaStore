<?php
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

/* XỬ LÝ FORM (THÊM/SỬA) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $rating = (float)($_POST['rating'] ?? 5.0);
        $discount_price = (isset($_POST['discount_price']) && $_POST['discount_price'] !== '') ? (float)$_POST['discount_price'] : NULL;
        $description = trim($_POST['description']);
        $image = $_POST['current_image'] ?? 'no-image.png';

        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $file_name = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], "../uploads/" . $file_name)) {
                    $image = $file_name;
                }
            }
        }

        if (isset($_POST['add'])) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, image, category_id, rating, discount_price, description, stock) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sdsidds", $name, $price, $image, $category_id, $rating, $discount_price, $description);
            $stmt->execute();
        } elseif (isset($_POST['edit'])) {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, image=?, category_id=?, rating=?, discount_price=?, description=? WHERE id=?");
            $stmt->bind_param("sdsiddsi", $name, $price, $image, $category_id, $rating, $discount_price, $description, $id);
            $stmt->execute();
        }
        $message = "Thao tác thành công!";
    } catch (Exception $e) { $message = "Lỗi: " . $e->getMessage(); }
}

/* LẤY DỮ LIỆU */
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12 px-md-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold">Quản Lý Sản Phẩm</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="prepareAdd()">
          <i class="fas fa-plus me-2"></i>Thêm sản phẩm
        </button>
      </div>

      <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="border-0">ID</th>
                <th class="border-0">Sản phẩm</th>
                <th class="border-0">Danh mục</th>
                <th class="border-0">Giá bán</th>
                <th class="border-0">Tồn kho</th>
                <th class="border-0">Đánh giá</th>
                <th class="border-0">Hình ảnh</th>
                <th class="border-0 text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $p): ?>
              <tr>
                <td class="text-muted">#<?php echo $p['id']; ?></td>
                <td class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></td>
                <td><span
                    class="badge bg-info text-dark"><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></span>
                </td>
                <td>
                  <div class="text-primary fw-bold"><?php echo number_format($p['price']); ?>₫</div>
                  <?php if(!empty($p['discount_price'])): ?>
                  <small
                    class="text-muted text-decoration-line-through"><?php echo number_format($p['discount_price']); ?>₫</small>
                  <?php else: ?>
                  <small class="text-muted small">Nguyên giá</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?php echo ($p['stock'] ?? 0) <= 5 ? 'bg-danger' : 'bg-success'; ?>">
                    <?php echo $p['stock'] ?? 0; ?>
                  </span>
                </td>
                <td>
                  <i class="fas fa-star text-warning me-1"></i>
                  <?php echo number_format($p['rating'] ?? 0, 1); ?>
                </td>
                <td>
                  <img src="../uploads/<?php echo htmlspecialchars($p['image'] ?? 'no-image.png'); ?>" width="45"
                    height="45" class="rounded border shadow-sm"
                    onerror="this.src='https://placehold.co/50x50?text=No+Img'">
                </td>
                <td class="text-end">
                  <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo $p['id']; ?>)"><i
                        class="fas fa-edit"></i></button>
                    <a href="?delete=1&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Xóa?')"><i class="fas fa-trash"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" enctype="multipart/form-data" class="modal-content border-0">
      <div class="modal-header border-0">
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
            <label class="form-label fw-bold">Giá niêm yết cũ (nếu có)</label>
            <input type="number" name="discount_price" id="prod-discount" class="form-control">
          </div>
          <div class="col-md-12">
            <label class="form-label fw-bold">Hình ảnh</label>
            <input type="file" name="image_upload" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Mô tả</label>
            <textarea name="description" id="prod-desc" rows="3" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="submit" name="add" id="btn-add" class="btn btn-primary px-4">Thêm sản phẩm</button>
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
}

function editProduct(id) {
  fetch(`../api/get_product.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
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