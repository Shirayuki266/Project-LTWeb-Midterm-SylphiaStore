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
         AND o.created_at BETWEEN ? AND ?) as total_exported
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.stock ASC
";

$stmt = $conn->prepare($sql);
$startTime = $fromDate . " 00:00:00";
$endTime   = $toDate . " 23:59:59";
$stmt->bind_param("ss", $startTime, $endTime);
$stmt->execute();
$inventory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Thống kê Kho & Báo cáo</h2>
  </div>

  <div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-bold">Khoảng thời gian báo cáo (Nhập - Xuất)</label>
          <div class="input-group shadow-sm">
            <input type="date" name="from_date" class="form-control" value="<?= $fromDate ?>">
            <span class="input-group-text">đến</span>
            <input type="date" name="to_date" class="form-control" value="<?= $toDate ?>">
          </div>
        </div>
        <div class="col-md-3">
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

  <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-4 py-3">Sản phẩm</th>
            <th class="text-center">Đã xuất (Bán)</th>
            <th class="text-center">Tồn hiện tại</th>
            <th>Trạng thái & Cảnh báo</th>
            <th class="text-end pe-4">Tra cứu</th>
          </tr>
        </thead>
        <tbody>
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