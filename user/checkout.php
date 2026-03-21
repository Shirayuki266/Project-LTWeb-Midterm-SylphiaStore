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

/* 1. TÍNH TOÁN HÓA ĐƠN */
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

/* 2. XỬ LÝ ĐẶT HÀNG */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_option = $_POST['address_option'] ?? 'default';
    if ($address_option === 'new') {
        $p_code = $_POST['city'] ?? '';
        $w_code = $_POST['ward'] ?? '';
        $house = $_POST['house_number'] ?? '';
        $note = $_POST['order_note'] ?? '';
        
        // SỬ DỤNG HÀM MỚI (Chỉ nối Tỉnh và Xã)
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
    } else {
        $error = "Lỗi hệ thống: " . $conn->error;
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
  }

  .form-check:hover {
    background-color: #f8f9fa !important;
  }

  .sticky-summary {
    position: sticky;
    top: 20px;
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
        <h3 class="mb-4 fw-bold">Thông tin thanh toán</h3>
        <form method="POST" id="checkoutForm">
          <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3">Địa chỉ nhận hàng</h5>

              <div class="form-check p-3 border rounded mb-3 bg-white shadow-sm position-relative">
                <input class="form-check-input ms-0 me-2" type="radio" name="address_option" id="addr_default"
                  value="default" checked>
                <label class="form-check-label fw-bold text-dark" for="addr_default">
                  <i class="fas fa-map-marker-alt text-danger me-1"></i> Sử dụng địa chỉ mặc định
                </label>

                <div class="ms-4 mt-2 p-2 bg-light rounded-3 border-start border-primary border-3">
                  <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-user-circle text-secondary me-2 small"></i>
                    <span class="small"><strong>Người nhận:</strong>
                      <?php echo htmlspecialchars($user['username']); ?></span>
                  </div>
                  <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-phone-alt text-secondary me-2 small"></i>
                    <span class="small"><strong>SĐT:</strong> <span
                        class="text-primary fw-bold"><?php echo htmlspecialchars($phone_default); ?></span></span>
                  </div>
                  <div class="d-flex align-items-start">
                    <i class="fas fa-home text-secondary me-2 mt-1 small"></i>
                    <span class="small text-muted"><strong>Địa chỉ:</strong>
                      <?php echo htmlspecialchars($address_default); ?></span>
                  </div>
                </div>

                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-primary px-2 py-1"
                  style="font-size: 0.65rem;">
                  Mặc định
                </span>
              </div>

              <div class="form-check p-3 border rounded mb-3 bg-white">
                <input class="form-check-input ms-0 me-2" type="radio" name="address_option" id="addr_new" value="new">
                <label class="form-check-label fw-bold" for="addr_new">Giao đến địa chỉ mới</label>
              </div>

              <div id="new_address_section" class="mt-3 p-3 border border-primary rounded-3 bg-white d-none">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="small fw-bold text-primary">Tỉnh/Thành</label>
                    <select class="form-select shadow-none" id="city" name="city"></select>
                  </div>
                  <div class="col-md-6">
                    <label class="small fw-bold text-primary">Phường/Xã</label>
                    <select class="form-select shadow-none" id="ward" name="ward"></select>
                  </div>
                  <div class="col-12">
                    <label class="small fw-bold">Số nhà, tên đường</label>
                    <input type="text" name="house_number" class="form-control" placeholder="VD: 123 Đường ABC...">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3">Phương thức thanh toán</h5>
              <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="payment" value="cash" checked id="pay_cod">
                <label class="form-check-label" for="pay_cod">Tiền mặt (COD)</label>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold">Xác nhận đặt
            hàng</button>
        </form>
      </div>

      <div class="col-lg-5">
        <div class="sticky-summary">
          <h4 class="fw-bold mb-4">Đơn hàng của bạn</h4>
          <div class="card shadow-sm">
            <ul class="list-group list-group-flush">
              <?php foreach ($items as $item): $item_total = $item['price'] * $item['quantity']; ?>
              <li class="list-group-item d-flex justify-content-between py-3">
                <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                <span class="fw-bold"><?php echo formatPrice($item_total); ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
            <div class="card-footer bg-white p-4">
              <div class="d-flex justify-content-between mb-2"><span>Tạm
                  tính</span><span><?php echo formatPrice($subtotal); ?></span></div>
              <div class="d-flex justify-content-between mb-3"><span>Phí
                  ship</span><span><?php echo formatPrice($shipping); ?></span></div>
              <hr>
              <h5 class="d-flex justify-content-between fw-bold"><span>Tổng:</span><span
                  class="text-danger"><?php echo formatPrice($total); ?></span></h5>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script>
  const city = document.getElementById("city");
  const ward = document.getElementById("ward");
  const section = document.getElementById('new_address_section');
  const rdoNew = document.getElementById('addr_new');
  const rdoDef = document.getElementById('addr_default');

  fetch('../api/get_location.php?action=get_provinces')
    .then(res => res.json())
    .then(data => {
      city.innerHTML = '<option value="">Chọn Tỉnh Thành</option>';
      data.forEach(p => city.options.add(new Option(p.name, p.code)));
    });

  city.onchange = () => {
    ward.length = 1;
    if (city.value) {
      fetch(`../api/get_location.php?action=get_wards&province_code=${city.value}`)
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            data.forEach(w => ward.options.add(new Option(w.name, w.code)));
          }
        });
    }
  };

  function toggleForm() {
    if (rdoNew.checked) {
      section.classList.remove('d-none');
    } else {
      section.classList.add('d-none');
    }
  }
  rdoNew.addEventListener('change', toggleForm);
  rdoDef.addEventListener('change', toggleForm);
  </script>
</body>

</html>