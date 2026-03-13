<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../api/address.php';

$auth = new Auth($conn);
$user = $auth->getCurrentUser();
$address = new Address($conn);
$provinces = $address->getProvinces();

// Handle address update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $provinceId = (int)$_POST['province_id'];
    $districtId = (int)$_POST['district_id'];
    $wardId = (int)$_POST['ward_id'];
    $streetAddress = trim($_POST['street_address']);

    $stmt = $conn->prepare("UPDATE danh_sach_nguoi_dung SET province_id = ?, district_id = ?, ward_id = ?, street_address = ? WHERE id = ?");
    $stmt->bind_param("iiiis", $provinceId, $districtId, $wardId, $streetAddress, $_SESSION['user_id']);
    $stmt->execute();

    $user = $auth->getCurrentUser(); // Refresh user data
    $success = "Địa chỉ đã được cập nhật!";
}

// Get orders (legacy schema)
$stmt = $conn->prepare("SELECT * FROM donhang WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tài khoản - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">Sylphia Shop</a>
      <a href="logout.php" class="btn btn-light ms-auto">Đăng xuất</a>
    </div>
  </nav>

  <div class="container my-5">
    <div class="row">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5>Thông tin cá nhân</h5>
          </div>
          <div class="card-body">
            <p><strong>Tên:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($user['phonenumber'] ?? ''); ?></p>

            <?php if (isset($success)): ?>
              <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <h6 class="mt-3">Địa chỉ mặc định:</h6>
            <?php
            $currentAddress = '';
            if ($user['province_id']) {
              $currentAddress = $address->getFullAddress($user['province_id'], $user['district_id'], $user['ward_id'], $user['street_address']);
            }
            ?>
            <p id="current_address"><?php echo $currentAddress ?: 'Chưa thiết lập'; ?></p>

            <button class="btn btn-primary" onclick="toggleAddressForm()">Chỉnh sửa địa chỉ</button>

            <form id="addressForm" method="POST" style="display: none;" class="mt-3">
              <input type="hidden" name="update_address" value="1">
              <div class="row">
                <div class="col-md-4 mb-2">
                  <select name="province_id" id="profile_province" class="form-control">
                    <option value="">Chọn Tỉnh/Thành phố</option>
                    <?php foreach ($provinces as $province): ?>
                      <option value="<?php echo $province['id']; ?>" <?php echo ($user['province_id'] ?? '') == $province['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($province['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-2">
                  <select name="district_id" id="profile_district" class="form-control" disabled>
                    <option value="">Chọn Quận/Huyện</option>
                  </select>
                </div>
                <div class="col-md-4 mb-2">
                  <select name="ward_id" id="profile_ward" class="form-control" disabled>
                    <option value="">Chọn Phường/Xã</option>
                  </select>
                </div>
              </div>
              <div class="mb-2">
                <input type="text" name="street_address" id="profile_street" class="form-control" placeholder="Số nhà, tên đường" value="<?php echo htmlspecialchars($user['street_address'] ?? ''); ?>">
              </div>
              <button type="submit" class="btn btn-success">Lưu địa chỉ</button>
              <button type="button" class="btn btn-secondary" onclick="toggleAddressForm()">Hủy</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <h3>Lịch sử đơn hàng</h3>
        <?php if (empty($orders)): ?>
        <div class="alert alert-info">
          Chưa có đơn hàng nào.
        </div>
        <?php else: ?>
        <div class="row g-3">
          <?php foreach ($orders as $order):
            $status = $order['trangthai'] ?? 'pending';
            $statusClass = ['pending'=>'warning', 'paid'=>'info', 'shipped'=>'primary', 'delivered'=>'success', 'cancelled'=>'danger'][$status] ?? 'secondary';
          ?>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <h6>Mã đơn #<?php echo $order['id']; ?></h6>
                <p>Ngày: <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
                <?php if (!empty($order['dia_chi'])): ?>
                <p><small>Địa chỉ: <?php echo htmlspecialchars($order['dia_chi']); ?></small></p>
                <?php endif; ?>
                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                <p class="fw-bold mt-2"><?php echo formatPrice($order['tongtien']); ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleAddressForm() {
      const form = document.getElementById('addressForm');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function loadDistricts(provinceId, selectedDistrictId = null) {
      const districtSelect = document.getElementById('profile_district');
      const wardSelect = document.getElementById('profile_ward');

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
      const wardSelect = document.getElementById('profile_ward');

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

    function updateCurrentAddress() {
      const provinceSelect = document.getElementById('profile_province');
      const districtSelect = document.getElementById('profile_district');
      const wardSelect = document.getElementById('profile_ward');
      const streetInput = document.getElementById('profile_street');
      const displayDiv = document.getElementById('current_address');

      const provinceId = provinceSelect.value;
      const districtId = districtSelect.value;
      const wardId = wardSelect.value;
      const streetAddress = streetInput.value.trim();

      if (provinceId && districtId && wardId) {
        fetch(`../api/address_api.php?action=get_full_address&province_id=${provinceId}&district_id=${districtId}&ward_id=${wardId}&street=${encodeURIComponent(streetAddress)}`)
          .then(response => response.text())
          .then(address => {
            displayDiv.textContent = address;
          });
      } else {
        displayDiv.textContent = 'Chưa thiết lập';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const provinceSelect = document.getElementById('profile_province');
      const districtSelect = document.getElementById('profile_district');
      const wardSelect = document.getElementById('profile_ward');
      const streetInput = document.getElementById('profile_street');

      // Load saved address if exists
      const savedProvinceId = '<?php echo $user['province_id'] ?? ''; ?>';
      const savedDistrictId = '<?php echo $user['district_id'] ?? ''; ?>';
      const savedWardId = '<?php echo $user['ward_id'] ?? ''; ?>';

      if (savedProvinceId) {
        loadDistricts(savedProvinceId, savedDistrictId);
      }

      provinceSelect.addEventListener('change', function() {
        loadDistricts(this.value);
        updateCurrentAddress();
      });

      districtSelect.addEventListener('change', function() {
        loadWards(this.value);
        updateCurrentAddress();
      });

      wardSelect.addEventListener('change', updateCurrentAddress);
      streetInput.addEventListener('input', updateCurrentAddress);
    });
  </script>
</body>

</html>