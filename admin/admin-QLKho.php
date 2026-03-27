<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. LẤY DỮ LIỆU BỘ LỌC & TÌM KIẾM */
$search     = $_GET['search'] ?? '';
$fromDate   = $_GET['from_date'] ?? date('Y-m-01');
$toDate     = $_GET['to_date']   ?? date('Y-m-d');
$alertLimit = isset($_GET['alert_limit']) ? (int)$_GET['alert_limit'] : 10;
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

/* 3. TRUY VẤN SQL TỔNG HỢP */
$sql = "
    SELECT 
        p.*, c.name as category_name,
        (SELECT IFNULL(SUM(oi.quantity), 0) 
         FROM order_items oi 
         JOIN orders o ON oi.order_id = o.id 
         WHERE oi.product_id = p.id 
         AND o.status IN ('processing', 'shipping', 'delivered', 'completed') 
         AND o.created_at BETWEEN ? AND ?) as total_sold
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE ?
  ";

  $types = "sss";

  if ($categoryId > 0) {
    $sql .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $categoryId;
  }

  $sql .= "
    ORDER BY p.stock ASC
";

$stmt = $conn->prepare($sql);
$start = $fromDate . " 00:00:00";
$end   = $toDate . " 23:59:59";
$searchTerm = "%$search%";
$params = [$start, $end, $searchTerm];
if ($categoryId > 0) {
    $params[] = $categoryId;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php'; 
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold text-dark">
      <i class="fas fa-warehouse me-2 text-primary"></i>Quản lý Tồn kho
    </h2>
  </div>

  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
          <label class="form-label small fw-bold text-secondary">Tìm kiếm sản phẩm</label>
          <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0" placeholder="Tên sản phẩm..."
              value="<?= htmlspecialchars($search) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-secondary">Khoảng thời gian báo cáo</label>
          <div class="input-group shadow-sm">
            <input type="date" name="from_date" class="form-control" value="<?= $fromDate ?>">
            <span class="input-group-text border-0">đến</span>
            <input type="date" name="to_date" class="form-control" value="<?= $toDate ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold text-secondary">Loại sản phẩm</label>
          <select name="category_id" class="form-select shadow-sm">
            <option value="0">Tất cả danh mục</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold text-danger">Cảnh báo nếu tồn ≤</label>
          <input type="number" name="alert_limit" class="form-control shadow-sm" value="<?= $alertLimit ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
            <i class="fas fa-filter me-1"></i> Lọc dữ liệu
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-4 py-3 border-0">Sản phẩm</th>
            <th class="text-center border-0">Đã bán (Xuất)</th>
            <th class="text-center border-0">Tồn hiện tại</th>
            <th class="border-0">Trạng thái</th>
            <th class="text-end pe-4 border-0">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): 
                        $stock = (int)$p['stock'];
                        $isLow = ($stock <= $alertLimit);
                        $imgPath = "../uploads/" . ($p['image'] ?? 'no-image.png');
                    ?>
          <tr class="<?= $isLow ? 'table-danger' : '' ?>">
            <td class="ps-4">
              <div class="d-flex align-items-center">
                <img src="<?= $imgPath ?>" class="rounded-3 border me-3 shadow-sm" width="50" height="50"
                  style="object-fit: cover;" onerror="this.src='https://placehold.co/100x100?text=None'">
                <div>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                  <small class="text-muted"><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></small>
                </div>
              </div>
            </td>
            <td class="text-center fw-bold text-primary fs-5"><?= number_format($p['total_sold']) ?></td>
            <td class="text-center fw-bold fs-5"><?= number_format($stock) ?></td>
            <td>
              <?php if ($stock <= 0): ?>
              <span class="badge bg-dark px-3 py-2 text-uppercase" style="font-size: 0.7rem;">Hết hàng</span>
              <?php elseif ($isLow): ?>
              <span class="badge bg-danger px-3 py-2 text-uppercase" style="font-size: 0.7rem;">Sắp hết</span>
              <?php else: ?>
              <span class="badge bg-success px-3 py-2 text-uppercase" style="font-size: 0.7rem;">An toàn</span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-4">
              <div class="btn-group shadow-sm">
                <button class="btn btn-sm btn-outline-dark"
                  onclick="openLookupModal(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')" title="Lịch sử">
                  <i class="fas fa-history"></i>
                </button>
                <button class="btn btn-sm btn-success"
                  onclick="openStockModal(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')" title="Nhập kho">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="stockForm" class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold">Nhập thêm hàng</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="stockProductId" name="id">
        <p class="mb-3 text-muted">Sản phẩm: <strong id="stockProductName" class="text-dark"></strong></p>
        <div class="mb-3">
          <label class="form-label fw-bold small">Số lượng nhập thêm</label>
          <input type="number" name="quantity" class="form-control form-control-lg border-2" min="1" required>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-success px-4" onclick="executeSaveStock()">Cập nhật ngay</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="lookupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold">Kiểm tra tồn kho quá khứ</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <p class="mb-3 fs-5">Sản phẩm: <strong id="lookupName" class="text-primary"></strong></p>
        <div class="mb-4 text-start">
          <label class="form-label fw-bold small text-muted text-uppercase">Chọn ngày muốn tra cứu</label>
          <div class="input-group">
            <input type="date" id="lookupDate" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-primary px-4" onclick="executeLookup()">Kiểm tra</button>
          </div>
        </div>
        <div id="lookupResultBox" class="p-4 bg-light rounded-4 d-none">
          <h2 class="fw-bold text-dark mb-1" id="res_stock">--</h2>
          <p class="mb-0 text-muted small text-uppercase">Sản phẩm tồn kho vào cuối ngày</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let stockModalObj, lookupModalObj, currentLookupId;

document.addEventListener('DOMContentLoaded', function() {
  stockModalObj = new bootstrap.Modal(document.getElementById('stockModal'));
  lookupModalObj = new bootstrap.Modal(document.getElementById('lookupModal'));
});

function openStockModal(id, name) {
  document.getElementById('stockProductId').value = id;
  document.getElementById('stockProductName').innerText = name;
  stockModalObj.show();
}

function openLookupModal(id, name) {
  currentLookupId = id;
  document.getElementById('lookupName').innerText = name;
  document.getElementById('lookupResultBox').classList.add('d-none');
  lookupModalObj.show();
}

function executeLookup() {
  const date = document.getElementById('lookupDate').value;
  const resBox = document.getElementById('lookupResultBox');
  resBox.classList.remove('d-none');
  document.getElementById('res_stock').innerText = '...';

  fetch(`../api/get_stock_at_time.php?id=${currentLookupId}&date=${date}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) document.getElementById('res_stock').innerText = data.stock;
      else alert('Lỗi: ' + data.message);
    });
}

function executeSaveStock() {
  const formData = new FormData(document.getElementById('stockForm'));
  fetch('../api/inventory.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Nếu có footer toast thì dùng location.href để hiện thông báo
        location.href = 'admin-QLKho.php?msg=success';
      } else {
        alert('Lỗi: ' + data.error);
      }
    })
    .catch(() => alert('Lỗi kết nối máy chủ!'));
}
</script>

<?php include 'admin-footer.php'; ?>