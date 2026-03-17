<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?from=checkout');
    exit;
}

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$cart = new Cart($conn);
$items = $cart->getItems();

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$auth = new Auth($conn);
$user = $auth->getCurrentUser();

$address = $user['address_default'] ?? $user['street_address'] ?? '';
$phone = $user['phone'] ?? $user['sdt'] ?? $user['phonenumber'] ?? '';

/* TÍNH TIỀN */

/* TÍNH TIỀN & ƯU ĐÃI VIP */

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// 1. Xác định % giảm giá dựa trên vip_level
$vip_level = $user['vip_level'] ?? 'none';
$discount_percent = 0;

switch ($vip_level) {
    case 'đồng': $discount_percent = 2; break;
    case 'bạc':   $discount_percent = 5; break;
    case 'vàng':  $discount_percent = 10; break;
    default:      $discount_percent = 0; break;
}

// 2. Tính số tiền giảm giá
$discount_amount = ($subtotal * $discount_percent) / 100;

// 3. Tính tổng cuối cùng
$shipping = 30000;
$total = ($subtotal - $discount_amount) + $shipping;

/* CREATE ORDER */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $address = trim($_POST['address'] ?? $address);
    $payment_method = $_POST['payment'] ?? 'cash'; // Lấy phương thức thanh toán từ form

    // Sửa câu lệnh INSERT theo đúng tên cột trong ảnh của bạn
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, address, payment_method, status, total)
        VALUES (?, ?, ?, 'pending', ?)
    ");

    // "isdd" tương ứng: user_id (int), address (string), payment_method (string), total (double/decimal)
    $stmt->bind_param("issd", $_SESSION['user_id'], $address, $payment_method, $total);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;

        foreach ($items as $item) {
            $stmt_item = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt_item->bind_param(
                "iiid",
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            );
            $stmt_item->execute();
        }

        $cart->clear();
        $success = true;
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>

<?php include 'header.php'; ?>

<div class="container my-5">

  <?php if (isset($success)): ?>

  <div class="alert alert-success">

    <h4>Đặt hàng thành công! Mã đơn: #<?php echo $order_id; ?></h4>

    <p>Tổng tiền: <?php echo formatPrice($total); ?></p>

    <a href="profile.php" class="btn btn-primary">Xem đơn hàng</a>
    <a href="index.php" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>

  </div>

  <?php else: ?>

  <div class="row">

    <!-- SHIPPING INFO -->

    <div class="col-md-7">

      <h2>Thông tin giao hàng</h2>

      <form method="POST">

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

          <textarea name="address" class="form-control" rows="3"
            required><?php echo htmlspecialchars($address); ?></textarea>

        </div>

        <h5>Phương thức thanh toán</h5>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment" value="cash" checked>
          <label class="form-check-label">Tiền mặt (COD)</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment" value="transfer">
          <label class="form-check-label">Chuyển khoản</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment" value="online">
          <label class="form-check-label">Thanh toán online (VNPay / MoMo - Coming soon)</label>
        </div>

        <button type="submit" class="btn btn-success btn-lg w-100 mt-4">
          Đặt hàng (<?php echo formatPrice($total); ?>)
        </button>

      </form>

    </div>


    <!-- ORDER SUMMARY -->

    <div class="col-md-5">

      <h4>Đơn hàng của bạn</h4>

      <div class="card">

        <ul class="list-group list-group-flush">

          <?php foreach ($items as $item): 
$item_total = $item['price'] * $item['quantity'];
?>

          <li class="list-group-item d-flex justify-content-between">

            <span>
              <?php echo htmlspecialchars($item['name']); ?>
              (x<?php echo $item['quantity']; ?>)
            </span>

            <span>
              <?php echo formatPrice($item_total); ?>
            </span>

          </li>

          <?php endforeach; ?>

        </ul>

        <div class="card-footer">
          <div class="d-flex justify-content-between">
            <span>Tạm tính</span>
            <span><?php echo formatPrice($subtotal); ?></span>
          </div>

          <?php if ($discount_percent > 0): ?>
          <div class="d-flex justify-content-between text-success">
            <span>Ưu đãi VIP (<?php echo ucfirst($vip_level); ?> -<?php echo $discount_percent; ?>%)</span>
            <span>-<?php echo formatPrice($discount_amount); ?></span>
          </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between">
            <span>Phí vận chuyển</span>
            <span><?php echo formatPrice($shipping); ?></span>
          </div>

          <hr>

          <h5 class="d-flex justify-content-between">
            <span>Tổng cộng:</span>
            <strong class="text-danger"><?php echo formatPrice($total); ?></strong>
          </h5>
        </div>

      </div>

    </div>

  </div>

  <?php endif; ?>

</div>

<?php include 'footer.php'; ?>