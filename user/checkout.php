<?php
// 1. Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?from=checkout');
    exit;
}

// 3. Import các file cấu hình
require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../api/address.php'; 
require_once '../includes/functions.php';

// 4. Khởi tạo đối tượng
$cart = new Cart($conn);
$allItems = $cart->getItems(); 

// --- LỌC SẢN PHẨM ĐƯỢC CHỌN ---
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

if (!empty($selectedIds)) {
    $items = array_filter($allItems, function($item) use ($selectedIds) {
        return in_array($item['id'], $selectedIds);
    });
} else {
    $items = $allItems;
}

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

// 5. Lấy thông tin người dùng
$auth = new Auth($conn);
$user = $auth->getCurrentUser();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$address_tool = new Address($conn);
$address_default = !empty($user['address']) ? $user['address'] : '';
$phone_default = !empty($user['phone']) ? $user['phone'] : 'Chưa cập nhật SĐT';

// --- TÍNH TOÁN HÓA ĐƠN ---
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

// --- XỬ LÝ ĐẶT HÀNG ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_option = $_POST['address_option'] ?? 'default';
    
    if ($address_option === 'new') {
        $p_code = $_POST['city'] ?? '';
        $w_code = $_POST['ward'] ?? '';
        $house = $_POST['house_number'] ?? '';
        $final_address = $address_tool->getFullAddressShort($p_code, $w_code, $house);
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
                $cart->remove($item['id']); 
            }
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
      <div class="mb-4"><i class="fas fa-check-circle fa-5x text-success"></i></div>
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
          <div class="alert alert-danger"><?php echo $error; ?></div>
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
                    <?php echo !empty($address_default) ? htmlspecialchars($address_default) : 'Chưa cập nhật địa chỉ'; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="border rounded-4 p-3 mb-2" style="cursor: pointer;"
              onclick="document.getElementById('addr_new').click()">
              <input class="form-check-input me-2" type="radio" name="address_option" id="addr_new" value="new"
                onchange="toggleAddressNew()">
              <label class="form-check-label fw-bold" for="addr_new">Giao đến địa chỉ khác</label>
              <div id="new_address_section" class="mt-3 d-none border-top pt-3">
                <div class="row g-3">
                  <div class="col-md-6"><select class="form-select" id="city" name="city"></select></div>
                  <div class="col-md-6"><select class="form-select" id="ward" name="ward">
                      <option>Chọn Phường/Xã</option>
                    </select></div>
                  <div class="col-12"><input type="text" name="house_number" class="form-control"
                      placeholder="Số nhà, tên đường..."></div>
                </div>
              </div>
            </div>
          </div>

          <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
            <h5 class="fw-bold mb-3">Phương thức thanh toán</h5>
            <div class="row g-2">
              <div class="col-md-6">
                <input type="radio" class="btn-check" name="payment" id="pay_cash" value="cash" checked
                  onchange="togglePaymentInfo()">
                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_cash">Tiền mặt (COD)</label>
              </div>
              <div class="col-md-6">
                <input type="radio" class="btn-check" name="payment" id="pay_bank" value="transfer"
                  onchange="togglePaymentInfo()">
                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="pay_bank">Chuyển khoản</label>
              </div>
            </div>

            <div id="bank_info" class="mt-3 p-4 rounded-4 d-none animate__animated animate__fadeIn"
              style="background: #f0f7ff; border: 2px dashed #0066cc;">
              <div class="row align-items-center">
                <div class="col-sm-7">
                  <h6 class="fw-bold text-primary mb-3">Thông tin tài khoản:</h6>
                  <p class="mb-1"><strong>Ngân hàng:</strong> MB Bank</p>
                  <p class="mb-1"><strong>Số TK:</strong> 0123456789</p>
                  <p class="mb-1"><strong>Chủ TK:</strong> NGUYEN THE BAO</p>
                  <p class="mb-0 text-muted small">Nội dung: <span class="text-danger fw-bold">THANHTOAN
                      <?php echo time(); ?></span></p>
                </div>
                <div class="col-sm-5 text-center mt-3 mt-sm-0">
                  <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=2|99|0123456789|||NGUYEN THE BAO|0|0"
                    alt="QR Payment" class="img-fluid border p-2 bg-white rounded-3 shadow-sm">
                  <div class="small text-muted mt-2">Quét mã để thanh toán</div>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow">Xác Nhận Đặt
            Hàng</button>
        </form>
      </div>

      <div class="col-lg-5">
        <h4 class="fw-bold mb-4">Sản phẩm đã chọn</h4>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <?php foreach ($items as $item): ?>
          <div class="d-flex justify-content-between mb-2">
            <span class="small"><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
            <span class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
          </div>
          <?php endforeach; ?>
          <hr>
          <div class="d-flex justify-content-between mb-2 small text-muted">
            <span>Tạm tính:</span><span><?php echo formatPrice($subtotal); ?></span>
          </div>
          <div class="d-flex justify-content-between text-danger fw-bold fs-4">
            <span>Tổng cộng:</span><span><?php echo formatPrice($total); ?></span>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>
// Xử lý ẩn hiện địa chỉ mới
function toggleAddressNew() {
  const section = document.getElementById("new_address_section");
  const isNew = document.getElementById("addr_new").checked;
  section.classList.toggle('d-none', !isNew);
  if (isNew) loadProvinces();
}

// Load danh sách tỉnh thành
function loadProvinces() {
  const citySelect = document.getElementById("city");
  if (citySelect.options.length > 0) return;
  fetch('../api/get_location.php?action=get_provinces')
    .then(res => res.json())
    .then(data => {
      citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành</option>';
      data.forEach(p => citySelect.options.add(new Option(p.name, p.code)));
    });
}

// Xử lý ẩn hiện thông tin ngân hàng và QR
function togglePaymentInfo() {
  const bankInfo = document.getElementById("bank_info");
  const isTransfer = document.getElementById("pay_bank").checked;

  if (isTransfer) {
    bankInfo.classList.remove('d-none');
    bankInfo.scrollIntoView({
      behavior: 'smooth',
      block: 'nearest'
    });
  } else {
    bankInfo.classList.add('d-none');
  }
}
</script>