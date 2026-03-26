<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* 1. KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* 2. XỬ LÝ THÊM TÀI KHOẢN MỚI */
if (isset($_POST['add_customer'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Ghép chuỗi địa chỉ đầy đủ
    $p = $_POST['province_name'] ?? '';
    $d = $_POST['district_name'] ?? '';
    $w = $_POST['ward_name'] ?? '';
    $detail = $_POST['address_detail'] ?? '';
    $full_address = trim("$detail, $w, $d, $p", ", ");
    
    $address  = mysqli_real_escape_string($conn, $full_address);
    $password = password_hash('123456', PASSWORD_DEFAULT);
    $role     = 'customer';
    $status   = 1;

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['msg_error'] = "Lỗi: Email này đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, phone, address, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $username, $email, $phone, $address, $password, $role, $status);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Thêm khách hàng thành công! MK mặc định: 123456";
        }
    }
    header("Location: admin-QLKH.php");
    exit();
}

/* 3. XỬ LÝ RESET & KHÓA (Giữ nguyên logic cũ) */
if (isset($_GET['reset_id'])) {
    $id = (int)$_GET['reset_id'];
    $new_pw = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_pw, $id);
    $stmt->execute();
    $_SESSION['msg'] = "Đã reset mật khẩu KH #$id";
    header("Location: admin-QLKH.php"); exit();
}

if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['id'];
    $new_status = (int)$_GET['set'];
    $conn->query("UPDATE users SET status = $new_status WHERE id = $id");
    header("Location: admin-QLKH.php"); exit();
}

$message = $_SESSION['msg'] ?? '';
$error = $_SESSION['msg_error'] ?? '';
unset($_SESSION['msg'], $_SESSION['msg_error']);

$customers = $conn->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>

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

  <div class="card shadow-sm border-0 mb-4">
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
        <tbody id="customersTable">
          <?php foreach ($customers as $c): ?>
          <tr class="<?= ($c['status'] == 0) ? 'table-light opacity-75' : '' ?>">
            <td class="ps-3 text-muted">#<?= $c['id'] ?></td>
            <td>
              <div class="fw-bold"><?= htmlspecialchars($c['username']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
            </td>
            <td>
              <small class="text-muted d-block text-truncate" style="max-width: 250px;"
                title="<?= htmlspecialchars($c['address']) ?>">
                <i
                  class="fas fa-map-marker-alt me-1"></i><?= $c['address'] ? htmlspecialchars($c['address']) : 'Chưa cập nhật' ?>
              </small>
            </td>
            <td>
              <span class="badge bg-<?= $c['status'] == 1 ? 'success' : 'danger' ?> shadow-sm">
                <?= $c['status'] == 1 ? 'Hoạt động' : 'Bị khóa' ?>
              </span>
            </td>
            <td class="text-end pe-3">
              <div class="btn-group shadow-sm bg-white rounded">
                <button class="btn btn-sm btn-outline-secondary" onclick="viewCustomer(<?= $c['id'] ?>)"><i
                    class="fas fa-eye text-info"></i></button>
                <a href="?reset_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"
                  onclick="return confirm('Reset mật khẩu về 123456?')"><i class="fas fa-key text-warning"></i></a>
                <a href="?toggle_status=1&id=<?= $c['id'] ?>&set=<?= $c['status'] == 1 ? 0 : 1 ?>"
                  class="btn btn-sm btn-outline-secondary">
                  <i class="fas fa-user-<?= $c['status'] == 1 ? 'slash text-danger' : 'check text-success' ?>"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form action="" method="POST" class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Thêm khách hàng mới</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Họ tên</label>
            <input type="text" name="username" class="form-control" placeholder="Nguyễn Văn A" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Số điện thoại</label>
            <input type="text" name="phone" class="form-control" placeholder="0901234567">
          </div>
          <div class="col-md-12">
            <label class="form-label fw-bold">Email (Tên đăng nhập)</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
          </div>

          <div class="col-md-4">
            <label class="form-label text-primary fw-bold">Tỉnh/Thành phố</label>
            <select id="province" class="form-select" required>
              <option value="">Chọn Tỉnh/TP</option>
            </select>
            <input type="hidden" name="province_name" id="province_name">
          </div>
          <div class="col-md-4">
            <label class="form-label text-primary fw-bold">Quận/Huyện</label>
            <select id="district" class="form-select" disabled required>
              <option value="">Chọn Quận/Huyện</option>
            </select>
            <input type="hidden" name="district_name" id="district_name">
          </div>
          <div class="col-md-4">
            <label class="form-label text-primary fw-bold">Phường/Xã</label>
            <select id="ward" class="form-select" disabled required>
              <option value="">Chọn Phường/Xã</option>
            </select>
            <input type="hidden" name="ward_name" id="ward_name">
          </div>
          <div class="col-md-12">
            <label class="form-label fw-bold">Số nhà, tên đường</label>
            <input type="text" name="address_detail" class="form-control" placeholder="VD: 123 Đường ABC..." required>
          </div>
        </div>
        <div class="alert alert-info mt-3 mb-0 py-2">
          <small><i class="fas fa-info-circle me-1"></i> Mật khẩu mặc định: <strong>123456</strong></small>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="submit" name="add_customer" class="btn btn-primary px-4">Lưu khách hàng</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-user-circle me-2"></i>Chi tiết khách hàng</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" id="customerDetails">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
        </div>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.min.js"></script>
<script>
// 1. XỬ LÝ API ĐỊA CHỈ (TỈNH -> HUYỆN -> XÃ)
const host = "https://provinces.open-api.vn/api/";
var renderData = (array, select) => {
  let row = ' <option disable value="">Chọn</option>';
  array.forEach(element => {
    row += `<option data-id="${element.code}" value="${element.name}">${element.name}</option>`
  });
  document.querySelector("#" + select).innerHTML = row;
}

// Lấy Tỉnh
fetch(host + "?depth=1")
  .then(res => res.json())
  .then(data => renderData(data, "province"));

// Lấy Huyện
document.querySelector("#province").addEventListener("change", function() {
  let id = this.options[this.selectedIndex].dataset.id;
  document.querySelector("#province_name").value = this.value;
  if (id) {
    fetch(host + "p/" + id + "?depth=2")
      .then(res => res.json())
      .then(data => {
        renderData(data.districts, "district");
        document.querySelector("#district").disabled = false;
        document.querySelector("#ward").innerHTML = '<option value="">Chọn Phường/Xã</option>';
        document.querySelector("#ward").disabled = true;
      });
  }
});

// Lấy Xã
document.querySelector("#district").addEventListener("change", function() {
  let id = this.options[this.selectedIndex].dataset.id;
  document.querySelector("#district_name").value = this.value;
  if (id) {
    fetch(host + "d/" + id + "?depth=2")
      .then(res => res.json())
      .then(data => {
        renderData(data.wards, "ward");
        document.querySelector("#ward").disabled = false;
      });
  }
});

document.querySelector("#ward").addEventListener("change", function() {
  document.querySelector("#ward_name").value = this.value;
});

// 2. AJAX XEM CHI TIẾT
function viewCustomer(id) {
  fetch(`../api/get_customer.php?id=${id}`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const c = data.customer;
        document.getElementById("customerDetails").innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between"><span>ID:</span> <strong>#${c.id}</strong></div>
                    <div class="list-group-item"><span>Địa chỉ:</span> <br><strong>${c.address || 'N/A'}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>Hạng:</span> <strong class="text-primary">${c.vip_level || 'Thành viên'}</strong></div>
                    <div class="list-group-item d-flex justify-content-between"><span>SĐT:</span> <strong>${c.phone || 'N/A'}</strong></div>
                </div>`;
        new bootstrap.Modal(document.getElementById('customerModal')).show();
      }
    });
}

function viewCustomer(id) {
  // 1. Reset nội dung về trạng thái Loading mỗi khi mở modal mới
  document.getElementById("customerDetails").innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Đang tải thông tin...</p>
        </div>`;

  // 2. Gọi API lấy dữ liệu
  fetch(`../api/get_customer.php?id=${id}`)
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const c = res.customer;

        // Xử lý các trường hợp dữ liệu null/trống để tránh lỗi hiển thị
        const phone = c.phone || '<i>Chưa cập nhật</i>';
        const address = c.address || '<i>Chưa có địa chỉ cụ thể</i>';
        const dateJoined = c.created_at ? new Date(c.created_at).toLocaleDateString('vi-VN') : 'N/A';
        const statusBadge = c.status == 1 ?
          '<span class="badge bg-success">Đang hoạt động</span>' :
          '<span class="badge bg-danger">Bị khóa</span>';

        // 3. Render HTML vào modal-body
        document.getElementById("customerDetails").innerHTML = `
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small uppercase">Mã khách hàng</span>
                        <strong class="text-dark">#${c.id}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small">Họ và tên</span>
                        <strong class="text-primary">${c.username}</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small">Email</span>
                        <span class="fw-bold text-dark">${c.email}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small">Số điện thoại</span>
                        <span class="fw-bold text-success">${phone}</span>
                    </div>
                    <div class="list-group-item py-3">
                        <span class="text-muted small d-block mb-2">Địa chỉ giao hàng</span>
                        <div class="p-3 bg-light rounded border border-dashed">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <span class="text-dark">${address}</span>
                        </div>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small">Ngày đăng ký</span>
                        <span>${dateJoined}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted small">Trạng thái</span>
                        ${statusBadge}
                    </div>
                </div>
            `;

        // 4. Kích hoạt Modal (nếu chưa mở)
        var myModal = new bootstrap.Modal(document.getElementById('customerModal'));
        myModal.show();
      } else {
        alert("Lỗi: " + res.message);
      }
    })
    .catch(err => {
      console.error("Lỗi hệ thống:", err);
      document.getElementById("customerDetails").innerHTML =
        `<div class="p-4 text-danger text-center">Không thể kết nối đến máy chủ.</div>`;
    });
}
</script>

<?php include 'admin-footer.php'; ?>