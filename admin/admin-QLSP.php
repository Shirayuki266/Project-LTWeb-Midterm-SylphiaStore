<?php
// 1. Debug & Khởi tạo
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    header('Location: login.php');
    exit;
}

$message = '';

/* 1.1 BỘ LỌC TÌM KIẾM */
$keyword = trim($_GET['q'] ?? '');
$filterCategoryId = (int)($_GET['category_id'] ?? 0);
$filterStatus = $_GET['status_filter'] ?? 'all';
if (!in_array($filterStatus, ['all', '1', '0'], true)) {
  $filterStatus = 'all';
}

/* 2. XỬ LÝ XOÁ SẢN PHẨM */
if (isset($_GET['delete']) && isset($_GET['id'])) {
  try {
    $id = (int)$_GET['id'];

    $dependencyQueries = [
      "order_items" => "SELECT COUNT(*) AS total FROM order_items WHERE product_id = ?",
      "purchase_order_details" => "SELECT COUNT(*) AS total FROM purchase_order_details WHERE product_id = ?"
    ];

    $hasDependencies = false;
    foreach ($dependencyQueries as $query) {
      $check_stmt = $conn->prepare($query);
      $check_stmt->bind_param("i", $id);
      $check_stmt->execute();

      if ((int)($check_stmt->get_result()->fetch_assoc()['total'] ?? 0) > 0) {
        $hasDependencies = true;
        break;
      }
    }

    if (!$hasDependencies) {
      $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $message = "Đã xoá vĩnh viễn sản phẩm khỏi hệ thống!";
    } else {
      $stmt = $conn->prepare("UPDATE products SET status = 0 WHERE id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $message = "Sản phẩm đã có lịch sử phát sinh nên hệ thống chuyển sang trạng thái ẨN thay vì xoá.";
    }
  } catch (mysqli_sql_exception $e) {
    $message = "Không thể xoá sản phẩm vì vẫn còn dữ liệu liên quan. Hệ thống nên chuyển sản phẩm sang ẨN.";
  }
}

/* 2.1 KHÔI PHỤC */
if (isset($_GET['restore']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE products SET status = 1 WHERE id = $id");
    $message = "Đã hiển thị lại sản phẩm!";
}

/* 3. XỬ LÝ THÊM/SỬA */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name']);
        $category_id = (int)$_POST['category_id'];
        $unit = trim($_POST['unit']);
        $cost_price = (float)$_POST['cost_price'];
        $profit_margin = (float)$_POST['profit_margin']; // Form gửi lên margin
        $description = trim($_POST['description']);
        $status = (int)$_POST['status'];
        $price = $cost_price * (1 + ($profit_margin / 100));

        $image = $_POST['current_image'] ?? 'no-image.png';
        if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
            $file_name = time() . '_' . $_FILES['image_upload']['name'];
            if (move_uploaded_file($_FILES['image_upload']['tmp_name'], "../uploads/" . $file_name)) {
                $image = $file_name;
            }
        }

        if (isset($_POST['add'])) {
            $stock = (int)$_POST['stock']; // Sửa theo tên cột DB là stock
            $stmt = $conn->prepare("INSERT INTO products (name, category_id, unit, cost_price, profit_percent, price, description, image, stock, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sisdddssii", $name, $category_id, $unit, $cost_price, $profit_margin, $price, $description, $image, $stock, $status);
            $stmt->execute();
            $message = "Thêm sản phẩm thành công!";
        } elseif (isset($_POST['edit'])) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, unit=?, cost_price=?, profit_percent=?, price=?, description=?, image=?, status=? WHERE id=?");
            $stmt->bind_param("sisdddssii", $name, $category_id, $unit, $cost_price, $profit_margin, $price, $description, $image, $status, $id);
            $stmt->execute();
            $message = "Cập nhật sản phẩm thành công!";
        }
    } catch (Exception $e) { $message = "Lỗi: " . $e->getMessage(); }
}

/* 4. LẤY DỮ LIỆU */
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$types = '';
$params = [];

if ($keyword !== '') {
  $sql .= " AND (p.name LIKE ? OR p.unit LIKE ? OR c.name LIKE ?)";
  $kw = "%$keyword%";
  $types .= 'sss';
  $params[] = $kw;
  $params[] = $kw;
  $params[] = $kw;
}

if ($filterCategoryId > 0) {
  $sql .= " AND p.category_id = ?";
  $types .= 'i';
  $params[] = $filterCategoryId;
}

if ($filterStatus !== 'all') {
  $sql .= " AND p.status = ?";
  $types .= 'i';
  $params[] = (int)$filterStatus;
}

$sql .= " ORDER BY p.id DESC";

$stmtProducts = $conn->prepare($sql);
if ($types !== '') {
  $stmtProducts->bind_param($types, ...$params);
}
$stmtProducts->execute();
$products = $stmtProducts->get_result()->fetch_all(MYSQLI_ASSOC);

$preserveParams = [];
if ($keyword !== '') $preserveParams['q'] = $keyword;
if ($filterCategoryId > 0) $preserveParams['category_id'] = $filterCategoryId;
if ($filterStatus !== 'all') $preserveParams['status_filter'] = $filterStatus;
$preserveQuery = http_build_query($preserveParams);
?>

<?php include 'header.php'; ?>

<style>
.product-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 5px;
}
</style>

<div class="container-fluid py-4 px-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold"><i class="fas fa-boxes me-2"></i>Quản Lý Sản Phẩm</h2>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#productModal"
      onclick="prepareAdd()">
      <i class="fas fa-plus me-2"></i>Thêm sản phẩm
    </button>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Tìm kiếm</label>
          <input type="text" name="q" class="form-control" placeholder="Tên sản phẩm, đơn vị, danh mục..."
            value="<?php echo htmlspecialchars($keyword); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Danh mục</label>
          <select name="category_id" class="form-select">
            <option value="0">Tất cả danh mục</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?php echo (int)$c['id']; ?>" <?php echo $filterCategoryId === (int)$c['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($c['name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Trạng thái</label>
          <select name="status_filter" class="form-select">
            <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>Tất cả</option>
            <option value="1" <?php echo $filterStatus === '1' ? 'selected' : ''; ?>>Đang bán</option>
            <option value="0" <?php echo $filterStatus === '0' ? 'selected' : ''; ?>>Ẩn</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Lọc</button>
          <a href="admin-QLSP.php" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-3">ID</th>
            <th>Thông tin</th>
            <th>Giá Vốn / Bán</th>
            <th>Kho</th>
            <th>Trạng thái</th>
            <th class="text-end pe-3">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($products)): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Không tìm thấy sản phẩm phù hợp bộ lọc.</td>
          </tr>
          <?php endif; ?>
          <?php foreach ($products as $p): ?>
          <tr class="<?php echo $p['status'] == 0 ? 'bg-light opacity-75' : ''; ?>">
            <td class="ps-3 text-muted">#<?php echo $p['id']; ?></td>
            <td>
              <div class="d-flex align-items-center py-2">
                <?php 
                                    $imgRaw = trim($p['image']);
                                    if (strpos($imgRaw, 'http') === 0) {
                                        $finalPath = $imgRaw;
                                    } else {
                                        $finalPath = file_exists("../uploads/" . $imgRaw) ? "../uploads/" . $imgRaw : "../images/" . $imgRaw;
                                    }
                                ?>
                <img src="<?php echo $finalPath; ?>" class="product-img me-3 shadow-sm"
                  onerror="this.src='https://placehold.co/80x80?text=No+Img'">
                <div>
                  <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['name']); ?></div>
                  <small class="text-muted d-block"><?php echo htmlspecialchars($p['unit']); ?> |
                    <?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></small>
                </div>
              </div>
            </td>
            <td>
              <div class="small text-muted">Vốn: <?php echo number_format($p['cost_price'] ?? 0); ?>đ</div>
              <div class="fw-bold text-primary">Bán: <?php echo number_format($p['price'] ?? 0); ?>đ</div>
            </td>
            <td><span class="badge bg-light text-dark border"><?php echo number_format($p['stock'] ?? 0); ?></span></td>
            <td>
              <span class="badge <?php echo $p['status'] ? 'bg-success' : 'bg-secondary'; ?>">
                <?php echo $p['status'] ? 'Đang bán' : 'Ẩn'; ?>
              </span>
            </td>
            <td class="text-end pe-3">
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo $p['id']; ?>)">
                  <i class="fas fa-edit"></i>
                </button>
                <?php if($p['status'] == 1): ?>
                <a href="?delete=1&id=<?php echo $p['id']; ?><?php echo $preserveQuery ? '&' . $preserveQuery : ''; ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('Xác nhận xoá/ẩn?')">
                  <i class="fas fa-trash"></i>
                </a>
                <?php else: ?>
                <a href="?restore=1&id=<?php echo $p['id']; ?><?php echo $preserveQuery ? '&' . $preserveQuery : ''; ?>" class="btn btn-sm btn-outline-success">
                  <i class="fas fa-undo"></i>
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

<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" enctype="multipart/form-data" id="productForm" class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="fw-bold mb-0" id="modalTitle">Sản phẩm</h5>
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
            <label class="form-label fw-bold">Loại</label>
            <select name="category_id" id="prod-category" class="form-select">
              <?php foreach ($categories as $c): ?>
              <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">ĐVT</label>
            <input type="text" name="unit" id="prod-unit" class="form-control" placeholder="Cái, Bộ..." required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold text-primary">Số lượng tồn kho</label>
            <input type="number" name="stock" id="prod-stock" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Hiện trạng</label>
            <select name="status" id="prod-status" class="form-select">
              <option value="1">Hiển thị (Đang bán)</option>
              <option value="0">Ẩn (Ngừng bán)</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold text-danger">Giá vốn</label>
            <input type="number" name="cost_price" id="prod-cost" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold text-success">Lợi nhuận (%)</label>
            <input type="number" name="profit_margin" id="prod-margin" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Hình ảnh</label>
            <div id="preview-box" class="mb-2 d-none">
              <img id="img-preview" src="" width="100" class="rounded border shadow-sm">
            </div>
            <input type="file" name="image_upload" class="form-control" onchange="previewImg(this)">
          </div>
          <div class="col-12">
            <label class="form-label fw-bold">Mô tả</label>
            <textarea name="description" id="prod-desc" rows="3" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add" id="btn-add" class="btn btn-primary px-4">Thêm mới</button>
        <button type="submit" name="edit" id="btn-edit" class="btn btn-success px-4" style="display:none;">Lưu thay
          đổi</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function prepareAdd() {
  document.getElementById('modalTitle').innerText = "Thêm sản phẩm mới";
  document.getElementById('prod-id').value = "";
  document.getElementById('productForm').reset();
  document.getElementById('btn-add').style.display = "block";
  document.getElementById('btn-edit').style.display = "none";
  document.getElementById('preview-box').classList.add('d-none');
}

function editProduct(id) {
  fetch(`../api/get_product.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const p = data.product;
        document.getElementById('modalTitle').innerText = "Sửa: " + p.name;
        document.getElementById('prod-id').value = p.id;
        document.getElementById('prod-name').value = p.name;
        document.getElementById('prod-category').value = p.category_id;
        document.getElementById('prod-unit').value = p.unit;
        // ÁNH XẠ ĐÚNG TÊN CỘT TỪ API
        document.getElementById('prod-stock').value = p.stock;
        document.getElementById('prod-cost').value = p.cost_price;
        document.getElementById('prod-margin').value = p.profit_percent;
        document.getElementById('prod-status').value = p.status;
        document.getElementById('prod-desc').value = p.description;
        document.getElementById('prod-current-image').value = p.image;

        if (p.image) {
          let imgPath = (p.image.indexOf('http') === 0) ? p.image : (p.image.indexOf('images/') === 0 ? "../" + p
            .image : "../uploads/" + p.image);
          document.getElementById('img-preview').src = imgPath;
          document.getElementById('preview-box').classList.remove('d-none');
        }

        document.getElementById('btn-add').style.display = "none";
        document.getElementById('btn-edit').style.display = "block";
        new bootstrap.Modal(document.getElementById('productModal')).show();
      }
    });
}

function previewImg(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('img-preview').src = e.target.result;
      document.getElementById('preview-box').classList.remove('d-none');
    }
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
<?php include 'admin-footer.php'; ?>