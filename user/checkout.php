<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?from=checkout');
    exit;
}

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../api/address.php'; 
require_once '../includes/functions.php';

$cart = new Cart($conn);
$items = $cart->getItems();

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$auth = new Auth($conn);
$user = $auth->getCurrentUser();
$address_tool = new Address($conn);

$address_default = $user['address_default'] ?? $user['street_address'] ?? 'Chưa cập nhật địa chỉ';
$phone_default = $user['phone'] ?? $user['sdt'] ?? $user['phonenumber'] ?? 'Chưa có SĐT';

// TÍNH TOÁN HÓA ĐƠN
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$vip_level = $user['vip_level'] ?? 'none';
$discount_percent = 0;
switch (mb_strtolower($vip_level, 'UTF-8')) {
    case 'đồng': $discount_percent = 2; break;
    case 'bạc':   $discount_percent = 5; break;
    case 'vàng':  $discount_percent = 10; break;
    default:      $discount_percent = 0; break;
}

$discount_amount = ($subtotal * $discount_percent) / 100;
$shipping = 30000;
$total = ($subtotal - $discount_amount) + $shipping;

// XỬ LÝ ĐẶT HÀNG
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_option = $_POST['address_option'] ?? 'default';
    if ($address_option === 'new') {
        $p_code = $_POST['city'] ?? '';
        $w_code = $_POST['ward'] ?? '';
        $house = $_POST['house_number'] ?? '';
        $note = $_POST['order_note'] ?? '';
        $final_address = $address_tool->getFullAddressShort($p_code, $w_code, $house);
        if (!empty($note)) $final_address .= " (Ghi chú: $note)";
    } else {
        $final_address = $address_default;
    }

    $payment_method = $_POST['payment'] ?? 'cash';

    $stmt = $conn->prepare("INSERT INTO orders (user_id, address, payment_method, status, total) VALUES (?, ?, ?, 'pending', ?)");
    $stmt->bind_param("issd", $_SESSION['user_id'], $final_address, $payment_method, $total);
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        foreach ($items as $item) {
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_item->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt_item->execute();
        }
        $cart->clear();
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Thanh toán - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  .card {
    border-radius: 12px;
    border: none;
  }

  .form-check {
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #dee2e6;
  }

  .form-check:hover {
    background-color: #f8f9fa !important;
    border-color: #0d6efd;
  }

  .sticky-summary {
    position: sticky;
    top: 20px;
  }

  .bank-box {
    background: #f0f7ff;
    border: 1px dashed #0d6efd !important;
  }
  </style>
</head>

<body class="bg-light">
  <?php include 'header.php'; ?>

  <div class="container my-5">
    <?php if (isset($success)): ?>
    <div class="alert alert-success shadow p-5 text-center">
      <i class="fas fa-check-circle fa-4x mb-3 text-success"></i>
      <h2 class="fw-bold">Đặt hàng thành công!</h2>
      <p class="lead">Mã đơn hàng: <strong>#<?php echo $order_id; ?></strong></p>
      <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-3">Tiếp tục mua sắm</a>
    </div>
    <?php else: ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <h3 class="mb-4 fw-bold">Thanh toán</h3>
        <form method="POST" id="checkoutForm">

          <div class="card shadow-sm mb-4 p-4">
            <h5 class="fw-bold mb-3">Địa chỉ nhận hàng</h5>

            <div class="form-check p-3 rounded mb-3 bg-white position-relative">
              <input class="form-check-input ms-0 me-2" type="radio" name="address_option" id="addr_default"
                value="default" checked onchange="toggleAddressNew()">
              <label class="form-check-label fw-bold" for="addr_default">Sử dụng địa chỉ mặc định</label>
              <div class="ms-4 mt-2 small p-2 bg-light rounded">
                <div><i class="fas fa-user me-2 text-muted"></i><?php echo htmlspecialchars($user['username']); ?></div>
                <div><i class="fas fa-phone me-2 text-muted"></i><?php echo htmlspecialchars($phone_default); ?></div>
                <div><i
                    class="fas fa-map-marker-alt me-2 text-muted"></i><?php echo htmlspecialchars($address_default); ?>
                </div>
              </div>
            </div>

            <div class="form-check p-3 rounded mb-3 bg-white">
              <input class="form-check-input ms-0 me-2" type="radio" name="address_option" id="addr_new" value="new"
                onchange="toggleAddressNew()">
              <label class="form-check-label fw-bold" for="addr_new">Giao đến địa chỉ mới</label>
            </div>

            <div id="new_address_section" class="mt-2 p-3 border border-primary rounded-3 d-none">
              <div class="row g-3">
                <div class="col-md-6"><label class="small fw-bold">Tỉnh/Thành</label><select
                    class="form-select shadow-none" id="city" name="city"></select></div>
                <div class="col-md-6"><label class="small fw-bold">Phường/Xã</label><select
                    class="form-select shadow-none" id="ward" name="ward"></select></div>
                <div class="col-12"><label class="small fw-bold">Số nhà, tên đường</label><input type="text"
                    name="house_number" class="form-control" placeholder="VD: 123 Đường ABC..."></div>
              </div>
            </div>
          </div>

          <div class="card shadow-sm mb-4 p-4">
            <h5 class="fw-bold mb-3">Phương thức thanh toán</h5>

            <div class="form-check p-2 rounded mb-2">
              <input class="form-check-input" type="radio" name="payment" value="cash" checked id="pay_cod"
                onchange="togglePaymentBox()">
              <label class="form-check-label fw-bold" for="pay_cod"><i
                  class="fas fa-money-bill-wave text-success me-2"></i>Tiền mặt (COD)</label>
            </div>

            <div class="form-check p-2 rounded mb-2">
              <input class="form-check-input" type="radio" name="payment" value="transfer" id="pay_bank"
                onchange="togglePaymentBox()">
              <label class="form-check-label fw-bold" for="pay_bank"><i
                  class="fas fa-university text-primary me-2"></i>Chuyển khoản</label>
            </div>

            <div id="bank_info" class="p-3 bank-box rounded mb-3 d-none animate__animated animate__fadeIn">
              <p class="mb-1 small"><strong>MB Bank:</strong> 0123456789</p>
              <p class="mb-1 small"><strong>Chủ TK:</strong> NGUYEN THE BAO</p>
              <div class="text-center mt-2">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=ChuyenKhoanBao" width="100"
                  class="border p-1 bg-white">
              </div>
            </div>

            <div class="form-check p-2 rounded mb-2 opacity-75">
              <input class="form-check-input" type="radio" name="payment" value="online" id="pay_online"
                onchange="togglePaymentBox()">
              <label class="form-check-label fw-bold" for="pay_online"><i class="fab fa-cc-visa text-info me-2"></i>Trực
                tuyến (VNPay/Momo)</label>
            </div>

            <div id="online_msg" class="alert alert-warning py-2 small d-none mt-2">Tính năng đang phát triển!</div>

          </div>
          <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold">XÁC NHẬN ĐẶT
            HÀNG</button>
        </form>
      </div>

      <div class="col-lg-5">
        <div class="sticky-summary">
          <h4 class="fw-bold mb-4">Đơn hàng</h4>
          <div class="card shadow-sm p-4 text-center bg-white">
            <ul class="list-group list-group-flush mb-3">
              <?php foreach ($items as $item): ?>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><?php echo $item['name']; ?> x<?php echo $item['quantity']; ?></span>
                <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
              </li>
              <?php endforeach; ?>
            </ul>
            <hr>
            <div class="d-flex justify-content-between mb-2"><span>Tạm
                tính</span><span><?php echo formatPrice($subtotal); ?></span></div>
            <div class="d-flex justify-content-between mb-3"><span>Phí
                ship</span><span><?php echo formatPrice($shipping); ?></span></div>
            <h5 class="d-flex justify-content-between fw-bold text-danger">
              <span>Tổng:</span><span><?php echo formatPrice($total); ?></span>
            </h5>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script>
  const city = document.getElementById("city");
  const ward = document.getElementById("ward");
  const addrNew = document.getElementById("new_address_section");
  const bankBox = document.getElementById("bank_info");
  const onlineMsg = document.getElementById("online_msg");

  function toggleAddressNew() {
    if (document.getElementById("addr_new").checked) addrNew.classList.remove('d-none');
    else addrNew.classList.add('d-none');
  }

  function togglePaymentBox() {
    if (document.getElementById("pay_bank").checked) bankBox.classList.remove('d-none');
    else bankBox.classList.add('d-none');

    if (document.getElementById("pay_online").checked) onlineMsg.classList.remove('d-none');
    else onlineMsg.classList.add('d-none');
  }

  fetch('../api/get_location.php?action=get_provinces').then(res => res.json()).then(data => {
    city.innerHTML = '<option value="">Chọn Tỉnh</option>';
    data.forEach(p => city.options.add(new Option(p.name, p.code)));
  });

  city.onchange = () => {
    ward.length = 1;
    if (city.value) {
      fetch(`../api/get_location.php?action=get_wards&province_code=${city.value}`).then(res => res.json()).then(
        data => {
          if (Array.isArray(data)) data.forEach(w => ward.options.add(new Option(w.name, w.code)));
        });
    }
  };
  </script>
</body>

</html>