<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// 1. Lấy thông tin đơn hàng gốc
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("<div class='container mt-5 alert alert-danger rounded-4 shadow-sm'>
            <i class='fas fa-exclamation-circle me-2'></i>Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.
         </div>");
}

// 2. Xử lý logic Hủy đơn hàng (Chỉ khi trạng thái là 'pending')
$message_status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    if ($order['status'] === 'pending') {
        $stmt_cancel = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt_cancel->bind_param("ii", $order_id, $user_id);
        
        if ($stmt_cancel->execute()) {
            $order['status'] = 'cancelled'; // Cập nhật biến local để hiển thị ngay
            $message_status = "<div class='alert alert-success small py-2 border-0 shadow-sm mb-3'><i class='fas fa-check-circle me-1'></i> Đã hủy đơn hàng thành công.</div>";
        } else {
            $message_status = "<div class='alert alert-danger small py-2 border-0 shadow-sm mb-3'>Lỗi hệ thống, không thể hủy đơn.</div>";
        }
    }
}

// 3. Lấy danh sách sản phẩm chi tiết
$stmt_items = $conn->prepare("
    SELECT oi.*, p.name, p.image, p.description 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$details = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Chi tiết đơn hàng #" . $order_id;
include 'header.php';
?>

<main class="bg-light py-5">
  <div class="container">
    <div class="mb-4">
      <a href="profile.php" class="btn btn-white shadow-sm rounded-pill px-4 btn-sm fw-bold text-secondary">
        <i class="fas fa-chevron-left me-2"></i>Quay lại Hồ sơ
      </a>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
          <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-box text-primary me-2"></i>Chi tiết kiện hàng</h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
              <?php echo count($details); ?> Sản phẩm
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr class="small text-muted text-uppercase">
                    <th class="ps-4 py-3">Sản phẩm</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end pe-4">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($details as $item): 
                      $img = trim($item['image']);
                      $path = (strpos($img, 'http') === 0) ? $img : "../images/" . str_replace('images/', '', $img);
                  ?>
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center py-2">
                        <img src="<?php echo $path; ?>" class="rounded-3 border shadow-sm p-1 bg-white"
                          style="width:60px; height:60px; object-fit:contain;"
                          onerror="this.src='../images/logoshop.png'">
                        <div class="ms-3">
                          <div class="fw-bold text-dark mb-0 small"><?php echo htmlspecialchars($item['name']); ?></div>
                          <div class="text-primary fw-bold" style="font-size:0.8rem;">
                            <?php echo formatPrice($item['price']); ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="text-muted">x<?php echo $item['quantity']; ?></span>
                    </td>
                    <td class="text-end pe-4 fw-bold text-dark">
                      <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 90px;">
          <div class="card-body p-4">
            <h6 class="fw-bold mb-4 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
              Mã đơn hàng: #<?php echo $order_id; ?>
            </h6>

            <?php echo $message_status; ?>

            <div class="mb-4">
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Ngày đặt:</span>
                <span
                  class="fw-bold small text-dark"><?php echo date('d/m/Y - H:i', strtotime($order['created_at'])); ?></span>
              </div>
              <div class="d-flex justify-content-between mb-3 align-items-center">
                <span class="text-muted small">Trạng thái:</span>
                <span>
                  <?php 
                    $status_map = [
                        'pending' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3">Chờ xử lý</span>',
                        'confirmed' => '<span class="badge bg-info-subtle text-info border border-info-subtle px-3">Đã xác nhận</span>',
                        'delivered' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-3">Đã giao hàng</span>',
                        'cancelled' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3">Đã hủy</span>'
                    ];
                    echo $status_map[$order['status']] ?? $order['status'];
                  ?>
                </span>
              </div>
            </div>

            <div class="p-3 rounded-4 bg-light border-0 mb-4" style="border-left: 4px solid #0066cc !important;">
              <label class="fw-bold small mb-2 d-block text-primary"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                nhận hàng</label>
              <div class="text-dark fw-bold" style="font-size: 0.9rem; line-height: 1.5;">
                <?php echo htmlspecialchars($order['address']); ?>
              </div>
            </div>

            <div class="border-top pt-3 mb-4">
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Tiền hàng:</span>
                <span class="text-dark"><?php echo formatPrice($order['total'] ); ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Phí ship:</span>
                <span class="text-dark">0₫</span>
              </div>
              <div class="d-flex justify-content-between mt-3 align-items-center">
                <span class="fw-bold text-dark">Tổng cộng:</span>
                <span class="text-danger fw-bold fs-4"><?php echo formatPrice($order['total']); ?></span>
              </div>
            </div>

            <div class="d-grid gap-2">
              <?php if ($order['status'] === 'pending'): ?>
              <form method="POST" onsubmit="return confirmCancel()">
                <input type="hidden" name="cancel_order" value="1">
                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill fw-bold btn-sm py-2">
                  <i class="fas fa-times-circle me-1"></i> Hủy đơn hàng
                </button>
              </form>
              <?php endif; ?>

              <a href="products.php" class="btn btn-light w-100 rounded-pill btn-sm text-muted border py-2">
                Tiếp tục mua sắm
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>
function confirmCancel() {
  return confirm("Bạn có chắc chắn muốn hủy đơn hàng này không? Sau khi hủy, trạng thái sẽ không thể thay đổi lại.");
}
</script>

<style>
.btn-white {
  background: #fff;
  border: 1px solid #eee;
}

.btn-white:hover {
  background: #f8f9fa;
  color: #0066cc;
}

.bg-warning-subtle {
  background-color: #fff3cd;
}

.bg-info-subtle {
  background-color: #cff4fc;
}

.bg-success-subtle {
  background-color: #d1e7dd;
}

.bg-danger-subtle {
  background-color: #f8d7da;
}
</style>