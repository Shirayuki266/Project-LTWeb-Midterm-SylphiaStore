<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

// 1. KIỂM TRA ĐĂNG NHẬP ADMIN
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// 2. KHỞI TẠO BỘ LỌC
$fromDate   = $_GET['from_date'] ?? date('Y-m-01'); // Mặc định từ đầu tháng
$toDate     = $_GET['to_date']   ?? date('Y-m-d');    // Mặc định đến hôm nay
$alertLimit = isset($_GET['alert_limit']) ? (int)$_GET['alert_limit'] : 10; // Hạn mức cảnh báo
$categoryFilter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$productKeyword = trim($_GET['product_name'] ?? '');

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$whereConditions = [];
if ($categoryFilter > 0) {
  $whereConditions[] = "p.category_id = $categoryFilter";
}
if ($productKeyword !== '') {
  $safeKeyword = $conn->real_escape_string($productKeyword);
  $whereConditions[] = "p.name LIKE '%$safeKeyword%'";
}
$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// 3. TRUY VẤN DỮ LIỆU THỐNG KÊ
// Truy vấn này lấy tồn hiện tại và tính toán lượng xuất dựa trên hóa đơn thành công
$sql = "
    SELECT 
        p.id, p.name, p.image, p.stock as current_stock,
        c.name as category_name,
        -- Tính tổng lượng xuất (đã bán) trong khoảng thời gian
        (SELECT IFNULL(SUM(oi.quantity), 0) 
         FROM order_items oi 
         JOIN orders o ON oi.order_id = o.id 
         WHERE oi.product_id = p.id 
         AND o.status != 'cancelled' 
     AND o.created_at BETWEEN ? AND ?) as total_exported,
    -- Tính tổng lượng nhập (phiếu nhập đã hoàn thành) trong khoảng thời gian
    (SELECT IFNULL(SUM(pod.quantity), 0)
     FROM purchase_order_details pod
     JOIN purchase_orders po ON pod.purchase_order_id = po.id
     WHERE pod.product_id = p.id
     AND po.status = 'completed'
     AND po.created_at BETWEEN ? AND ?) as total_imported
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $whereSql
    ORDER BY p.stock ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
}

$startTime = $fromDate . " 00:00:00";
$endTime   = $toDate . " 23:59:59";
$stmt->bind_param("ssss", $startTime, $endTime, $startTime, $endTime);

if (!$stmt->execute()) {
    die("Lỗi thực thi câu lệnh: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Lỗi lấy kết quả: " . $stmt->error);
}

$inventory = $result->fetch_all(MYSQLI_ASSOC);
if (!is_array($inventory)) {
    $inventory = [];
}

include 'header.php';
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Thống kê Kho & Báo cáo</h2>
  </div>

  <!-- Thông báo báo cáo đã được lọc -->
  <?php if (!empty($_GET)): ?>
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Báo cáo được lọc:</strong> Từ ngày <strong><?= date('d/m/Y', strtotime($fromDate)) ?></strong> 
    đến ngày <strong><?= date('d/m/Y', strtotime($toDate)) ?></strong> 
    (Hạn mức cảnh báo: ≤ <strong><?= $alertLimit ?></strong> sản phẩm)
    <?php if ($productKeyword !== ''): ?>
    - Tên sản phẩm chứa: <strong><?= htmlspecialchars($productKeyword) ?></strong>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-bold">Khoảng thời gian báo cáo (Nhập - Xuất)</label>
          <div class="input-group shadow-sm">
            <input type="date" name="from_date" class="form-control" value="<?= $fromDate ?>">
            <span class="input-group-text">đến</span>
            <input type="date" name="to_date" class="form-control" value="<?= $toDate ?>">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold">Loại sản phẩm</label>
          <select name="category_id" class="form-select shadow-sm">
            <option value="0">Tất cả danh mục</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $categoryFilter === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-bold">Tên sản phẩm</label>
          <input type="text" name="product_name" class="form-control shadow-sm" placeholder="Nhập tên sản phẩm..."
            value="<?= htmlspecialchars($productKeyword) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-bold text-danger">Hạn mức cảnh báo sắp hết hàng</label>
          <input type="number" name="alert_limit" class="form-control shadow-sm" value="<?= $alertLimit ?>" min="0">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
            <i class="fas fa-filter me-1"></i> Xem báo cáo
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Thống kê tóm tắt -->
  <?php 
  if (!empty($inventory)) {
    $total_stock = array_sum(array_column($inventory, 'current_stock'));
    $total_sold = array_sum(array_column($inventory, 'total_exported'));
    $total_imported = array_sum(array_column($inventory, 'total_imported'));
    $low_items = count(array_filter($inventory, function($item) use ($alertLimit) { return $item['current_stock'] <= $alertLimit; }));
  ?>
  <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-4">
    <div class="col">
      <div class="card border-0 shadow-sm bg-white">
        <div class="card-body text-center">
          <div class="text-primary mb-2"><i class="fas fa-box fa-2x"></i></div>
          <div class="small text-muted">Tổng số sản phẩm</div>
          <div class="h5 fw-bold"><?= count($inventory) ?></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm bg-white">
        <div class="card-body text-center">
          <div class="text-primary mb-2"><i class="fas fa-download fa-2x"></i></div>
          <div class="small text-muted">Lượng đã nhập</div>
          <div class="h5 fw-bold text-primary"><?= number_format($total_imported) ?></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm bg-white">
        <div class="card-body text-center">
          <div class="text-success mb-2"><i class="fas fa-plus-circle fa-2x"></i></div>
          <div class="small text-muted">Lượng bán được</div>
          <div class="h5 fw-bold text-success"><?= number_format($total_sold) ?></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm bg-white">
        <div class="card-body text-center">
          <div class="text-info mb-2"><i class="fas fa-warehouse fa-2x"></i></div>
          <div class="small text-muted">Tổng tồn kho</div>
          <div class="h5 fw-bold text-info"><?= number_format($total_stock) ?></div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card border-0 shadow-sm bg-white">
        <div class="card-body text-center">
          <div class="text-danger mb-2"><i class="fas fa-exclamation-circle fa-2x"></i></div>
          <div class="small text-muted">Sắp hết hàng</div>
          <div class="h5 fw-bold text-danger"><?= $low_items ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>

  <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-4 py-3">Sản phẩm</th>
            <th class="text-center">Đã nhập</th>
            <th class="text-center">Đã xuất (Bán)</th>
            <th class="text-center">Tồn hiện tại</th>
            <th>Trạng thái & Cảnh báo</th>
            <th class="text-end pe-4">Tra cứu</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($inventory)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
              <strong>Không có dữ liệu</strong>
              <p class="small mt-2">Khoảng thời gian từ <strong><?= date('d/m/Y', strtotime($fromDate)) ?></strong> đến <strong><?= date('d/m/Y', strtotime($toDate)) ?></strong> không có sản phẩm nào</p>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($inventory as $item): 
                        $isLow = ($item['current_stock'] <= $alertLimit);
                    ?>
          <tr class="<?= $isLow ? 'table-danger' : '' ?>">
            <td class="ps-4">
              <div class="d-flex align-items-center">
                <img src="../uploads/<?= htmlspecialchars($item['image'] ?? 'no-image.png') ?>"
                  class="rounded-3 border me-3" width="50" height="50" style="object-fit: cover;"
                  onerror="this.src='https://placehold.co/100x100?text=None'">
                <div>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></div>
                  <small class="text-muted"><?= htmlspecialchars($item['category_name'] ?? 'Chưa phân loại') ?></small>
                </div>
              </div>
            </td>
            <td class="text-center fw-bold text-primary fs-5"><?= number_format($item['total_imported']) ?></td>
            <td class="text-center fw-bold text-primary fs-5"><?= number_format($item['total_exported']) ?></td>
            <td class="text-center fw-bold fs-5"><?= number_format($item['current_stock']) ?></td>
            <td>
              <?php if ($isLow): ?>
              <span class="badge bg-danger px-3 py-2">
                <i class="fas fa-exclamation-triangle me-1"></i> Sắp hết (≤ <?= $alertLimit ?>)
              </span>
              <?php else: ?>
              <span class="badge bg-success px-3 py-2">Hàng còn đủ</span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-4">
              <button class="btn btn-sm btn-dark rounded-pill px-3"
                onclick="openLookupModal(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>')">
                <i class="fas fa-history me-1"></i> Tra tồn kho cũ
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="lookupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold">Tra cứu tồn kho theo ngày</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-3">Sản phẩm: <strong id="lookupProductName" class="text-primary"></strong></p>
        <label class="form-label fw-bold">Chọn thời điểm muốn xem lượng tồn:</label>
        <div class="input-group mb-4">
          <input type="date" id="targetLookupDate" class="form-control" value="<?= date('Y-m-d') ?>">
          <button class="btn btn-primary px-4" id="btnRunLookup">Kiểm tra</button>
        </div>
        <div id="lookupResult" class="text-center py-4 bg-light rounded-3 d-none">
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentLookupId = null;
const lookupModal = new bootstrap.Modal(document.getElementById('lookupModal'));

function openLookupModal(id, name) {
  currentLookupId = id;
  document.getElementById('lookupProductName').innerText = name;
  document.getElementById('lookupResult').classList.add('d-none');
  lookupModal.show();
}

document.getElementById('btnRunLookup').addEventListener('click', function() {
  const date = document.getElementById('targetLookupDate').value;
  const resultBox = document.getElementById('lookupResult');

  resultBox.classList.remove('d-none');
  resultBox.innerHTML = '<div class="spinner-border text-primary"></div>';

  // Gọi API để tính tồn kho lùi ngày
  fetch(`../api/get_stock_at_time.php?id=${currentLookupId}&date=${date}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        resultBox.innerHTML = `
                    <div class="small text-muted mb-1 text-uppercase">Số lượng tồn vào ngày ${date}</div>
                    <h2 class="fw-bold text-dark mb-0">${data.stock} sản phẩm</h2>
                `;
      } else {
        resultBox.innerHTML = `<div class="text-danger">${data.message}</div>`;
      }
    })
    .catch(() => {
      resultBox.innerHTML = '<div class="text-danger">Lỗi kết nối API!</div>';
    });
});
</script>

<?php include 'admin-footer.php'; ?>