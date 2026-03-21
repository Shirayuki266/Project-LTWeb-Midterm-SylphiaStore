<?php
session_start();
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

// 1. Lấy thông tin chung của đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("<div class='container mt-5 alert alert-danger'>Đơn hàng không tồn tại hoặc bạn không có quyền xem.</div>");
}

// 2. Lấy danh sách sản phẩm (Dùng biến $details)
$stmt_items = $conn->prepare("
    SELECT oi.*, p.name, p.image, p.description 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$details = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Chi tiết đơn hàng #<?php echo $order_id; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  .product-img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 8px;
  }

  .card {
    border-radius: 15px;
  }
  </style>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="mb-4">
      <a href="profile.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Quay lại hồ sơ
      </a>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-basket text-primary me-2"></i>Sản phẩm đã đặt</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr class="text-muted small">
                    <th class="ps-0">Sản phẩm</th>
                    <th class="text-center">Đơn giá</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($details as $item): 
                      // Xử lý đường dẫn ảnh gọn gàng
                      $img = $item['image'];
                      $path = (strpos($img, 'http') === 0) ? $img : "../images/" . str_replace('images/', '', $img);
                  ?>
                  <tr>
                    <td class="ps-0">
                      <div class="d-flex align-items-center">
                        <img src="<?php echo $path; ?>" class="product-img border me-3"
                          onerror="this.src='https://placehold.co/80x80?text=No+Image'">
                        <div>
                          <div class="fw-bold small"><?php echo htmlspecialchars($item['name']); ?></div>
                          <div class="text-muted extra-small" style="font-size: 0.75rem; max-width: 200px;">
                            <?php echo htmlspecialchars($item['description'] ?? ''); ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="text-center small"><?php echo formatPrice($item['price']); ?></td>
                    <td class="text-center">
                      <span
                        class="badge rounded-pill bg-light text-dark border px-3">x<?php echo $item['quantity']; ?></span>
                    </td>
                    <td class="text-end fw-bold text-primary">
                      <?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2 text-uppercase" style="font-size: 0.9rem;">Hóa đơn
              #<?php echo $order_id; ?></h5>

            <div class="mb-4">
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Ngày đặt:</span>
                <span class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Trạng thái:</span>
                <span>
                  <?php 
                        $status_map = [
                            'pending' => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
                            'confirmed' => '<span class="badge bg-info text-dark">Đã xác nhận</span>',
                            'delivered' => '<span class="badge bg-success">Đã giao hàng</span>',
                            'cancelled' => '<span class="badge bg-danger">Đã hủy</span>'
                        ];
                        echo $status_map[$order['status']] ?? $order['status'];
                    ?>
                </span>
              </div>
              <div class="d-flex justify-content-between mb-2 small">
                <span class="text-muted">Thanh toán:</span>
                <span
                  class="badge bg-light text-dark border"><?php echo ($order['payment_method'] == 'cash' ? 'Tiền mặt' : 'Chuyển khoản'); ?></span>
              </div>
            </div>

            <div class="bg-light p-3 rounded-3 mb-4">
              <label class="fw-bold small mb-2 d-block"><i class="fas fa-truck text-primary me-2"></i>Địa chỉ nhận
                hàng</label>
              <div class="small text-muted" style="line-height: 1.5;"><?php echo htmlspecialchars($order['address']); ?>
              </div>
            </div>

            <div class="border-top pt-3">
              <div class="d-flex justify-content-between mb-2 small text-muted">
                <span>Tiền hàng</span>
                <span><?php echo formatPrice($order['total'] - 30000); ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2 small text-muted">
                <span>Phí vận chuyển</span>
                <span>30.000₫</span>
              </div>
              <div class="d-flex justify-content-between mt-3 align-items-center">
                <span class="fw-bold">Tổng thanh toán</span>
                <span class="text-danger fw-bold h4 mb-0"><?php echo formatPrice($order['total']); ?></span>
              </div>
            </div>

            <?php if ($order['status'] === 'pending'): ?>
            <button class="btn btn-outline-danger w-100 mt-4 rounded-pill btn-sm">Yêu cầu hủy đơn</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>
</body>

</html>