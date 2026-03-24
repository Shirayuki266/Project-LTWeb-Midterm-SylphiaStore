<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra đăng nhập
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

// Nếu giỏ hàng trống thì quay về trang giỏ hàng
if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$auth = new Auth($conn);
$user = $auth->getCurrentUser();
$address_tool = new Address($conn);

// ĐỒNG BỘ ĐỊA CHỈ: Lấy địa chỉ từ cột 'address' trong bảng users làm gốc
$address_default = !empty($user['address']) ? $user['address'] : '';
$phone_default = !empty($user['phone']) ? $user['phone'] : 'Chưa cập nhật SĐT';

// --- TÍNH TOÁN HÓA ĐƠN ---
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Tính giảm giá theo VIP
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

// --- XỬ LÝ ĐẶT HÀNG ---
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

    if (empty($final_address)) {
        $error = "Vui lòng cập nhật địa chỉ trước khi đặt hàng!";
    } else {
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
}

$page_title = 'Thanh toán - Sylphia Shop';
include 'header.php';
?>

<main class="bg-light py-5">
  <div class="container">
    <?php if (isset($success)): ?>
    <div class="card border-0 shadow-lg p-5 text-center rounded-4 animate__animated animate__zoomIn">
      <div class="mb-4">
        <i class="fas fa-check-circle fa-5x text-success"></i>
      </div>
      <h2 class="fw-bold text-dark">Đặt hàng thành công!</h2>
      <p class="text-muted fs-5">Mã đơn hàng của bạn là: <span
          class="text-primary fw-bold">#<?php echo $order_id; ?></span></p>
      <p class="small text-secondary">Cảm ơn bạn đã tin tưởng Sylphia Shop. Chúng tôi sẽ sớm liên hệ xác nhận.</p>
      <div class="d-flex justify-content-center gap-3 mt-4">
        <a href="profile.php" class="btn btn-outline-primary rounded-pill px-4">Xem đơn hàng</a>
        <a href="index.php" class="btn btn-primary rounded-pill px-4 shadow">Tiếp tục mua sắm</a>
      </div>
    </div>
    <?php else: ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <h4 class="mb-4 fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Thanh toán đơn hàng</h4>

        <form method="POST" id="checkoutForm">
          <?php if(isset($error)): ?>
          <div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo $error; ?></div>
          <?php endif; ?>

          <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
            <h5 class="fw-bold mb-3">Địa chỉ nhận hàng</h5>

            <div class="mb-3">
              <div
                class="border rounded-4 p-3 <?php echo !empty($address_default) ? 'border-primary bg-light' : 'border-danger-subtle'; ?>"
                style="cursor: pointer;" onclick="document.getElementById('addr_default').click()">
                <div class="d-flex align-items-center mb-2">
                  <input class="form-check-input me-2" type="radio" name="address_option" id="addr_default"
                    value="default" checked onchange="toggleAddressNew()">
                  <label class="form-check-label fw-bold text-primary" for="addr_default">Sử dụng địa chỉ hiện tại (Mặc
                    định)</label>
                </div>
                <div class="ms-4">
                  <div class="mb-1"><strong><?php echo htmlspecialchars($user['username']); ?></strong> |
                    <?php echo htmlspecialchars($phone_default); ?></div>
                  <div class="text-muted small">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                    <?php echo !empty($address_default) ? htmlspecialchars($address_default) : '<span class="text-danger">Bạn chưa cập nhật địa chỉ trong hồ sơ!</span>'; ?>
                  </div>
                  <?php if(empty($address_default)): ?>
                  <a href="profile.php" class="btn btn-link btn-sm p-0 text-decoration-none mt-1">Cập nhật ngay <i
                      class="fas fa-external-link-alt ms-1"></i></a>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="border rounded-4 p-3 mb-2" style="cursor: pointer;"
              onclick="document.getElementById('addr_new').click()">
              <div class="d-flex align-items-center">
                <input class="form-check-input me-2" type="radio" name="address_option" id="addr_new" value="new"
                  onchange="toggleAddressNew()">
                <label class="form-check-label fw-bold" for="addr_new">Giao đến địa chỉ khác</label>
              </div>
              <div id="new_address_section" class="mt-3 d-none border-top pt-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="small fw-bold mb-1">Tỉnh/Thành phố</label>
                    <select class="form-select border-primary-subtle shadow-none" id="city" name="city"></select>
                  </div>
                  <div class="col-md-6">
                    <label class="small fw-bold mb-1">Phường/Xã</label>
                    <select class="form-select border-primary-subtle shadow-none" id="ward" name="ward">
                      <option value="">Chọn Phường/Xã</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="small fw-bold mb-1">Số nhà, tên đường chi tiết</label>
                    <input type="text" name="house_number" class="form-control shadow-none"
                      placeholder="VD: 123 Đường ABC...">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
            <h5 class="fw-bold mb-3">Phương thức thanh toán</h5>
            <div class="row g-2">
              <div class="col-md-6">
                <input type="radio" class="btn-check" name="payment" id="pay_cash" value="cash" checked
                  onchange="toggleBankInfo()">
                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_cash">
                  <i class="fas fa-money-bill-wave d-block mb-2 fs-4"></i> Tiền mặt (COD)
                </label>
              </div>
              <div class="col-md-6">
                <input type="radio" class="btn-check" name="payment" id="pay_bank" value="transfer"
                  onchange="toggleBankInfo()">
                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_bank">
                  <i class="fas fa-university d-block mb-2 fs-4"></i> Chuyển khoản
                </label>
              </div>
            </div>

            <div id="bank_info" class="mt-3 p-3 rounded-4 d-none animate__animated animate__fadeIn"
              style="background: #f0f7ff; border: 1px dashed #0066cc;">
              <div class="row align-items-center">
                <div class="col-8">
                  <p class="mb-1 small"><strong>MB Bank:</strong> 0123456789</p>
                  <p class="mb-1 small"><strong>Chủ TK:</strong> NGUYEN THE BAO</p>
                  <p class="mb-0 small text-muted">Nội dung: <span class="text-primary fw-bold">THANH TOAN
                      SYLPHIA</span></p>
                </div>
                <div class="col-4 text-center">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=ChuyenKhoanSylphiaShop"
                    class="border p-1 bg-white rounded">
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow">Xác Nhận Đặt
            Hàng</button>
        </form>
      </div>

      <div class="col-lg-5">
        <div class="sticky-top" style="top: 100px;">
          <h4 class="fw-bold mb-4">Đơn hàng của bạn</h4>
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4">
              <div class="list-group list-group-flush mb-3">
                <?php foreach ($items as $item): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                  <div class="me-auto">
                    <div class="fw-bold small text-dark"><?php echo htmlspecialchars($item['name']); ?></div>
                    <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?></small>
                  </div>
                  <span
                    class="fw-bold text-dark small"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                </div>
                <?php endforeach; ?>
              </div>

              <div class="bg-light p-3 rounded-4">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                  <span>Tạm tính:</span>
                  <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                <?php if($discount_amount > 0): ?>
                <div class="d-flex justify-content-between mb-2 small text-success">
                  <span>Ưu đãi VIP (<?php echo $vip_level; ?>):</span>
                  <span>-<?php echo formatPrice($discount_amount); ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                  <span>Phí vận chuyển:</span>
                  <span><?php echo formatPrice($shipping); ?></span>
                </div>
                <hr class="my-2 opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold text-dark">Tổng tiền:</span>
                  <span class="text-danger fw-bold fs-3"><?php echo formatPrice($total); ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>
function toggleAddressNew() {
  const section = document.getElementById("new_address_section");
  if (document.getElementById("addr_new").checked) {
    section.classList.remove('d-none');
    loadProvinces();
  } else {
    section.classList.add('d-none');
  }
}

function toggleBankInfo() {
  const bankInfo = document.getElementById("bank_info");
  if (document.getElementById("pay_bank").checked) {
    bankInfo.classList.remove('d-none');
  } else {
    bankInfo.classList.add('d-none');
  }
}

function loadProvinces() {
  const citySelect = document.getElementById("city");
  if (citySelect.options.length > 0) return; // Đã load rồi thì không load lại

  fetch('../api/get_location.php?action=get_provinces')
    .then(res => res.json())
    .then(data => {
      citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành</option>';
      data.forEach(p => citySelect.options.add(new Option(p.name, p.code)));
    });

  citySelect.onchange = () => {
    const wardSelect = document.getElementById("ward");
    wardSelect.length = 1;
    if (citySelect.value) {
      fetch(`../api/get_location.php?action=get_wards&province_code=${citySelect.value}`)
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) data.forEach(w => wardSelect.options.add(new Option(w.name, w.code)));
        });
    }
  };
}
</script>

<style>
.btn-outline-primary:hover {
  color: white;
}

.bg-light-subtle {
  background-color: #f8f9fa;
}

.form-check-input:checked {
  background-color: #0066cc;
  border-color: #0066cc;
}

/* Animation mượt cho các bảng thông tin */
.animate__animated {
  --animate-duration: 0.5s;
}
</style>