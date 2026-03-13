<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../api/address.php';

$cart = new Cart($conn);
$items = $cart->getItems();
$total = $cart->getTotal();
$user = (new Auth($conn))->getCurrentUser();
$address = new Address($conn);
$provinces = $address->getProvinces();

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

if ($_POST) {
    // Get full address
    $provinceId = (int)$_POST['province_id'];
    $districtId = (int)$_POST['district_id'];
    $wardId = (int)$_POST['ward_id'];
    $streetAddress = trim($_POST['street_address']);
    $fullAddress = $address->getFullAddress($provinceId, $districtId, $wardId, $streetAddress);

    // Create order in legacy schema (donhang + donhang_items)
    $stmt = $conn->prepare("INSERT INTO donhang (user_id, tongtien, trangthai, dia_chi) VALUES (?, ?, 'pending', ?)");
    $stmt->bind_param("ids", $_SESSION['user_id'], $total, $fullAddress);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Order items
    foreach ($items as $item) {
        $stmt = $conn->prepare("INSERT INTO donhang_items (donhang_id, sanpham_id, soluong, gia) VALUES (?, ?, ?, ?)");
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
            <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['phonenumber'] ?? ''); ?>" readonly>
          </div>
          <div class="mb-3">
            <label>Địa chỉ giao hàng</label>
            <div class="row">
              <div class="col-md-4 mb-2">
                <select name="province_id" id="province" class="form-control" required>
                  <option value="">Chọn Tỉnh/Thành phố</option>
                  <?php foreach ($provinces as $province): ?>
                    <option value="<?php echo $province['id']; ?>" <?php echo ($user['province_id'] ?? '') == $province['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($province['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4 mb-2">
                <select name="district_id" id="district" class="form-control" required disabled>
                  <option value="">Chọn Quận/Huyện</option>
                </select>
              </div>
              <div class="col-md-4 mb-2">
                <select name="ward_id" id="ward" class="form-control" required disabled>
                  <option value="">Chọn Phường/Xã</option>
                </select>
              </div>
            </div>
            <div class="mb-2">
              <input type="text" name="street_address" class="form-control" placeholder="Số nhà, tên đường (không bắt buộc)" value="<?php echo htmlspecialchars($user['street_address'] ?? ''); ?>">
            </div>
            <div id="full_address_display" class="text-muted small mt-1"></div>
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
  <script>
    function loadDistricts(provinceId, selectedDistrictId = null) {
      const districtSelect = document.getElementById('district');
      const wardSelect = document.getElementById('ward');

      if (!provinceId) {
        districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wardSelect.disabled = true;
        return;
      }

      fetch(`../api/address_api.php?action=get_districts&province_id=${provinceId}`)
        .then(response => response.json())
        .then(data => {
          districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
          data.forEach(district => {
            const option = document.createElement('option');
            option.value = district.id;
            option.textContent = district.name;
            if (selectedDistrictId && district.id == selectedDistrictId) {
              option.selected = true;
            }
            districtSelect.appendChild(option);
          });
          districtSelect.disabled = false;
          if (selectedDistrictId) {
            loadWards(selectedDistrictId);
          }
        });
    }

    function loadWards(districtId, selectedWardId = null) {
      const wardSelect = document.getElementById('ward');

      if (!districtId) {
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wardSelect.disabled = true;
        return;
      }

      fetch(`../api/address_api.php?action=get_wards&district_id=${districtId}`)
        .then(response => response.json())
        .then(data => {
          wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
          data.forEach(ward => {
            const option = document.createElement('option');
            option.value = ward.id;
            option.textContent = ward.name;
            if (selectedWardId && ward.id == selectedWardId) {
              option.selected = true;
            }
            wardSelect.appendChild(option);
          });
          wardSelect.disabled = false;
        });
    }

    function updateFullAddress() {
      const provinceSelect = document.getElementById('province');
      const districtSelect = document.getElementById('district');
      const wardSelect = document.getElementById('ward');
      const streetInput = document.getElementById('street_address');
      const displayDiv = document.getElementById('full_address_display');

      const provinceId = provinceSelect.value;
      const districtId = districtSelect.value;
      const wardId = wardSelect.value;
      const streetAddress = streetInput.value.trim();

      if (provinceId && districtId && wardId) {
        fetch(`../api/address_api.php?action=get_full_address&province_id=${provinceId}&district_id=${districtId}&ward_id=${wardId}&street=${encodeURIComponent(streetAddress)}`)
          .then(response => response.text())
          .then(address => {
            displayDiv.textContent = 'Địa chỉ đầy đủ: ' + address;
          });
      } else {
        displayDiv.textContent = '';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const provinceSelect = document.getElementById('province');
      const districtSelect = document.getElementById('district');
      const wardSelect = document.getElementById('ward');
      const streetInput = document.getElementById('street_address');

      // Load saved address if exists
      const savedProvinceId = '<?php echo $user['province_id'] ?? ''; ?>';
      const savedDistrictId = '<?php echo $user['district_id'] ?? ''; ?>';
      const savedWardId = '<?php echo $user['ward_id'] ?? ''; ?>';

      if (savedProvinceId) {
        loadDistricts(savedProvinceId, savedDistrictId);
        // Also load wards if district is selected
        if (savedDistrictId) {
          loadWards(savedDistrictId, savedWardId);
        }
      }

      provinceSelect.addEventListener('change', function() {
        loadDistricts(this.value);
        updateFullAddress();
      });

      districtSelect.addEventListener('change', function() {
        loadWards(this.value);
        updateFullAddress();
      });

      wardSelect.addEventListener('change', updateFullAddress);
      streetInput.addEventListener('input', updateFullAddress);
    });
  </script>
</body>

</html>