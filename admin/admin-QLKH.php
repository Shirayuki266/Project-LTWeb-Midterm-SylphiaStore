<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. XỬ LÝ CÁC HÀNH ĐỘNG (POST/GET) */

// THÊM KHÁCH HÀNG
if (isset($_POST['add_customer'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $p = $_POST['province_name'] ?? '';
    $d = $_POST['district_name'] ?? '';
    $w = $_POST['ward_name'] ?? '';
    $detail = $_POST['address_detail'] ?? '';
    $full_address = trim("$detail, $w, $d, $p", ", ");
    
    $address  = mysqli_real_escape_string($conn, $full_address);
    $password = password_hash('123456', PASSWORD_DEFAULT); // Mật khẩu mặc định
    $role     = 'customer';
    $status   = 1;

    // Kiểm tra trùng email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['msg_error'] = "Lỗi: Email này đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, address, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $username, $email, $phone, $address, $password, $role, $status);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Thêm khách hàng thành công!";
        }
    }
    header("Location: admin-QLKH.php"); exit();
}

// XÓA KHÁCH HÀNG
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    // Lưu ý: Nếu có ràng buộc FK với bảng orders, lệnh này có thể lỗi nếu khách đã có đơn hàng
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Đã xóa khách hàng #$id";
    } else {
        $_SESSION['msg_error'] = "Không thể xóa khách hàng này (có thể do ràng buộc dữ liệu)";
    }
    header("Location: admin-QLKH.php"); exit();
}

// RESET MẬT KHẨU
if (isset($_GET['reset_id'])) {
    $id = (int)$_GET['reset_id'];
    $new_pw = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_pw, $id);
    $stmt->execute();
    $_SESSION['msg'] = "Đã reset mật khẩu KH #$id về '123456'";
    header("Location: admin-QLKH.php"); exit();
}

// KHÓA / MỞ KHÓA
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['id'];
    $new_status = (int)$_GET['set'];
    $conn->query("UPDATE users SET status = $new_status WHERE id = $id");
    $_SESSION['msg'] = "Đã cập nhật trạng thái khách hàng #$id";
    header("Location: admin-QLKH.php"); exit();
}

/* 3. LẤY THÔNG SỐ TÌM KIẾM VÀ LỌC */
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$f_status = isset($_GET['f_status']) ? $_GET['f_status'] : '';

$message = $_SESSION['msg'] ?? '';
$error = $_SESSION['msg_error'] ?? '';
unset($_SESSION['msg'], $_SESSION['msg_error']);

/* 4. TRUY VẤN DANH SÁCH */
$sql = "SELECT * FROM users WHERE role = 'customer'";
if (!empty($search)) {
    $sql .= " AND (id = '$search' OR username LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($f_status !== '') {
    $sql .= " AND status = " . (int)$f_status;
}
$sql .= " ORDER BY created_at DESC";
$customers = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include 'header.php'; 
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 fw-bold text-dark"><i class="fas fa-users-cog me-2 text-primary"></i>Quản lý Khách hàng</h2>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
      <i class="fas fa-plus me-2"></i>Thêm khách hàng
    </button>
  </div>

  <?php if ($message): ?>
  <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= $message ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light border-bottom">
      <form action="" method="GET" class="row g-2">
        <div class="col-md-6">
          <input type="text" name="search" class="form-control" placeholder="Tìm ID, tên, SĐT..."
            value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
          <select name="f_status" class="form-select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="1" <?= $f_status === '1' ? 'selected' : '' ?>>Đang hoạt động</option>
            <option value="0" <?= $f_status === '0' ? 'selected' : '' ?>>Đang bị khóa</option>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">Lọc</button>
        </div>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">ID</th>
            <th>Khách hàng</th>
            <th>Địa chỉ</th>
            <th>Trạng thái</th>
            <th class="text-end pe-3">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($customers) > 0): ?>
          <?php foreach ($customers as $c): ?>
          <tr class="<?= ($c['status'] == 0) ? 'table-light opacity-75' : '' ?>">
            <td class="ps-3 text-muted">#<?= $c['id'] ?></td>
            <td>
              <div class="fw-bold"><?= htmlspecialchars($c['username']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
              <div class="small text-primary fw-bold"><?= htmlspecialchars($c['phone']) ?></div>
            </td>
            <td>
              <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                <?= $c['address'] ? htmlspecialchars($c['address']) : '<i>Chưa cập nhật</i>' ?>
              </small>
            </td>
            <td>
              <span class="badge bg-<?= $c['status'] == 1 ? 'success' : 'danger' ?>">
                <?= $c['status'] == 1 ? 'Hoạt động' : 'Bị khóa' ?>
              </span>
            </td>
            <td class="text-end pe-3">
              <div class="btn-group bg-white rounded shadow-sm">
                <button class="btn btn-sm btn-outline-secondary" onclick="viewCustomer(<?= $c['id'] ?>)" title="Xem">
                  <i class="fas fa-eye text-info"></i>
                </button>
                <a href="?reset_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Reset mật khẩu về 123456?')" title="Reset Pass">
                  <i class="fas fa-key text-warning"></i>
                </a>
                <a href="?toggle_status=1&id=<?= $c['id'] ?>&set=<?= $c['status'] == 1 ? 0 : 1 ?>"
                  class="btn btn-sm btn-outline-secondary" title="<?= $c['status'] == 1 ? 'Khóa' : 'Mở' ?>">
                  <i class="fas fa-user-<?= $c['status'] == 1 ? 'slash text-danger' : 'check text-success' ?>"></i>
                </a>
                <a href="?delete_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Xóa vĩnh viễn khách hàng này?')" title="Xóa">
                  <i class="fas fa-trash text-danger"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr>
            <td colspan="5" class="text-center py-5 text-muted">Không tìm thấy dữ liệu.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="" method="POST" class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Thêm khách hàng mới</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-bold">Tên đăng nhập</label>
          <input type="text" name="username" class="form-control" required placeholder="Nhập tên khách hàng">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Email</label>
          <input type="email" name="email" class="form-control" required placeholder="example@gmail.com">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">Số điện thoại</label>
          <input type="text" name="phone" class="form-control" required placeholder="09xxx">
        </div>

        <label class="form-label fw-bold">Địa chỉ</label>
        <div class="row g-2 mb-2">
          <div class="col-4"><select id="province" class="form-select form-select-sm" required>
              <option value="">Tỉnh</option>
            </select></div>
          <div class="col-4"><select id="district" class="form-select form-select-sm" disabled required>
              <option value="">Huyện</option>
            </select></div>
          <div class="col-4"><select id="ward" class="form-select form-select-sm" disabled required>
              <option value="">Xã</option>
            </select></div>
        </div>
        <input type="hidden" name="province_name" id="province_name">
        <input type="hidden" name="district_name" id="district_name">
        <input type="hidden" name="ward_name" id="ward_name">
        <input type="text" name="address_detail" class="form-control" placeholder="Số nhà, tên đường...">
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="submit" name="add_customer" class="btn btn-primary px-4">Lưu thông tin</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-bold">Chi tiết khách hàng</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="customerDetails"></div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
// --- XỬ LÝ API ĐỊA CHỈ ---
const host = "https://provinces.open-api.vn/api/";
var renderData = (array, select) => {
  let row = '<option value="">Chọn</option>';
  array.forEach(element => {
    row += `<option data-id="${element.code}" value="${element.name}">${element.name}</option>`
  });
  document.querySelector("#" + select).innerHTML = row;
}

// Load Tỉnh
fetch(host + "?depth=1").then(res => res.json()).then(data => renderData(data, "province"));

document.querySelector("#province").addEventListener("change", function() {
  let id = this.options[this.selectedIndex].dataset.id;
  document.querySelector("#province_name").value = this.value; // Gán tên tỉnh
  if (id) {
    fetch(host + "p/" + id + "?depth=2").then(res => res.json()).then(data => {
      renderData(data.districts, "district");
      document.querySelector("#district").disabled = false;
    });
  }
});

document.querySelector("#district").addEventListener("change", function() {
  let id = this.options[this.selectedIndex].dataset.id;
  document.querySelector("#district_name").value = this.value; // Gán tên huyện
  if (id) {
    fetch(host + "d/" + id + "?depth=2").then(res => res.json()).then(data => {
      renderData(data.wards, "ward");
      document.querySelector("#ward").disabled = false;
    });
  }
});

document.querySelector("#ward").addEventListener("change", function() {
  document.querySelector("#ward_name").value = this.value; // Gán tên xã
});

// --- XEM CHI TIẾT (AJAX) ---
function viewCustomer(id) {
  const detailContainer = document.getElementById("customerDetails");
  detailContainer.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

  const modalEl = document.getElementById('customerModal');
  const myModal = new bootstrap.Modal(modalEl);
  myModal.show();

  fetch(`../api/get_customer.php?id=${id}`)
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const c = res.customer;
        detailContainer.innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between"><span>ID:</span> <strong>#${c.id}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Họ tên:</span> <strong>${c.username}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Email:</span> <strong>${c.email}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>SĐT:</span> <strong class="text-success">${c.phone || 'N/A'}</strong></div>
                    <div class="list-group-item"><span>Địa chỉ:</span> <br><small class="text-dark">${c.address || 'N/A'}</small></div>
                </div>`;
      } else {
        detailContainer.innerHTML = `<div class="p-3 text-danger text-center">Không tìm thấy dữ liệu</div>`;
      }
    })
    .catch(e => {
      detailContainer.innerHTML = `<div class="p-3 text-danger text-center">Lỗi kết nối máy chủ</div>`;
    });
}
</script>

<?php include 'admin-footer.php'; ?>