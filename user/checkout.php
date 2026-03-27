<?php
// 1. Khởi động session đầu tiên để tránh lỗi Headers already sent
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA ĐĂNG NHẬP (Bắt buộc)
// Nếu không có user_id trong session, đá ngay sang trang login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?from=checkout');
    exit; // Dừng mọi hoạt động xử lý bên dưới
}

// 3. Import các file cấu hình và lớp xử lý
require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../api/address.php'; 
require_once '../includes/functions.php';

// 4. Khởi tạo đối tượng và kiểm tra giỏ hàng
$cart = new Cart($conn);
$items = $cart->getItems();

// Nếu giỏ hàng trống, không cho thanh toán, quay về trang giỏ hàng
if (empty($items)) {
    header('Location: cart.php');
    exit;
}

// 5. Lấy thông tin người dùng và tính toán
$auth = new Auth($conn);
$user = $auth->getCurrentUser();

// Kiểm tra nếu session tồn tại nhưng user không có trong DB (trường hợp bị xóa/khóa tài khoản)
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$address_tool = new Address($conn);

// Lấy thông tin mặc định từ profile
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

// --- XỬ LÝ ĐẶT HÀNG KHI SUBMIT FORM ---
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
      <p class="text-muted fs-5">Mã đơn hàng: <span class="text-primary fw-bold">#<?php echo $order_id; ?></span></p>
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
                  <label class="form-check-label fw-bold text-primary" for="addr_default">Sử dụng địa chỉ mặc
                    định</label>
                </div>
                <div class="ms-4">
                  <div><strong><?php echo htmlspecialchars($user['username']); ?></strong> |
                    <?php echo htmlspecialchars($phone_default); ?></div>
                  <div class="text-muted small">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                    <?php echo !empty($address_default) ? htmlspecialchars($address_default) : '<span class="text-danger">Bạn chưa cập nhật địa chỉ!</span>'; ?>
                  </div>
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
                    <select class="form-select" id="city" name="city"></select>
                  </div>
                  <div class="col-md-6">
                    <label class="small fw-bold mb-1">Phường/Xã</label>
                    <select class="form-select" id="ward" name="ward">
                      <option value="">Chọn Phường/Xã</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <input type="text" name="house_number" class="form-control"
                      placeholder="Số nhà, tên đường chi tiết...">
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
                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_cash">Tiền mặt (COD)</label>
              </div>
              <div class="col-md-6">
                <input type="radio" class="btn-check" name="payment" id="pay_bank" value="transfer"
                  onchange="toggleBankInfo()">
                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_bank">Chuyển khoản</label>
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
          <div class="card border-0 shadow-sm rounded-4 p-4">
            <?php foreach ($items as $item): ?>
            <div class="d-flex justify-content-between mb-2">
              <span class="small"><?php echo htmlspecialchars($item['name']); ?>
                x<?php echo $item['quantity']; ?></span>
              <span class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
            </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between text-danger fw-bold fs-4">
              <span>Tổng tiền:</span>
              <span><?php echo formatPrice($total); ?></span>
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

function loadProvinces() {
  const citySelect = document.getElementById("city");
  if (citySelect.options.length > 0) return;
  fetch('../api/get_location.php?action=get_provinces')
    .then(res => res.json())
    .then(data => {
      citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành</option>';
      data.forEach(p => citySelect.options.add(new Option(p.name, p.code)));
    });
  // Tương tự cho Ward...
}
</script>