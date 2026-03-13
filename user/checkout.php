<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';

$cart = new Cart($conn);
$items = $cart->getItems();
$total = $cart->getTotal();
$user = (new Auth($conn))->getCurrentUser();

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$address = $user['address_default'] ?? $user['street_address'] ?? '';
$phone = $user['phone'] ?? $user['sdt'] ?? $user['phonenumber'] ?? '';

if ($_POST) {
    $address = trim($_POST['address'] ?? $address);

    // Create order in new schema
    $stmt = $conn->prepare("INSERT INTO orders (user_id, tongtien, trangthai, dia_chi) VALUES (?, ?, 'pending', ?)");
    $stmt->bind_param("ids", $_SESSION['user_id'], $total, $address);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Order items
    foreach ($items as $item) {
        $stmt = $conn->prepare("INSERT INTO orders_items (donhang_id, sanpham_id, soluong, gia) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    $cart->clear(); // Clear cart
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container my-5">
    <?php if (isset($success)): ?>
    <div class="alert alert-success">
      <h4>Đặt hàng thành công! Mã đơn: #<?php echo $order_id; ?></h4>
      <p>Tổng tiền: <?php echo formatPrice($total); ?></p>
      <a href="profile.php" class="btn btn-primary">Xem đơn hàng</a>
    </div>
    <?php else: ?>
    <div class="row">
      <div class="col-md-7">
        <h2>Thông tin giao hàng</h2>
        <form method="POST" id="checkoutForm">
          <div class="mb-3">
            <label>Người nhận</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
          </div>
          <div class="mb-3">
            <label>SĐT</label>
            <input type="tel" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" readonly>
          </div>
          <div class="mb-3">
            <label>Địa chỉ giao hàng</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ giao hàng" required><?php echo htmlspecialchars($address); ?></textarea>
          </div>

          <h5>Phương thức thanh toán</h5>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment" value="cash" id="cash" checked>
            <label class="form-check-label" for="cash">Tiền mặt (COD)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment" value="transfer" id="transfer">
            <label class="form-check-label" for="transfer">Chuyển khoản</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment" value="online" id="online">
            <label class="form-check-label" for="online">Thanh toán online (VNPay/MoMo - Coming soon)</label>
          </div>

          <button type="submit" class="btn btn-success btn-lg w-100 mt-4">Đặt hàng
            (<?php echo formatPrice($total); ?>)</button>
        </form>
      </div>

      <div class="col-md-5">
        <h4>Đơn hàng của bạn</h4>
        <div class="card">
          <ul class="list-group list-group-flush">
            <?php foreach ($items as $item): ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
              <span><?php echo formatPrice($item['subtotal']); ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
          <div class="card-footer">
            <h5>Tổng: <strong><?php echo formatPrice($total); ?></strong></h5>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
