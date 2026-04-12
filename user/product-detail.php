<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../api/cart.php';
require_once '../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

// 1. Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit;
}

// 2. Kiểm tra trạng thái đăng nhập
$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn();

/* related products */
$related = [];
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id=? AND id!=? LIMIT 4");
$stmt->bind_param("ii", $product['category_id'], $id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $related[] = $row;
}

$page_title = $product['name'];
$current_page = 'product-detail';
include 'header.php';
?>

<div class="container py-5">
  <div class="row g-5">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
        <img id="mainImage" src="<?php echo !empty($product['image']) ? '../uploads/' . htmlspecialchars($product['image']) : '../images/no-image.png'; ?>" class="img-fluid p-4"
          style="height:420px;object-fit:contain" onerror="this.src='../images/no-image.png'">

        <div class="position-absolute bottom-0 end-0 m-3">
          <label for="uploadImg" class="btn btn-dark btn-sm rounded-circle shadow" title="Đổi ảnh">
            <i class="fas fa-camera"></i>
          </label>
          <input type="file" id="uploadImg" hidden accept="image/*" onchange="uploadProductImage(this)">
        </div>
      </div>

      <div id="uploadProgress" class="progress mt-2 d-none" style="height: 5px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <img src="<?php echo !empty($product['image']) ? '../uploads/' . htmlspecialchars($product['image']) : '../images/no-image.png'; ?>"
          style="width:70px;height:70px;object-fit:contain;cursor:pointer" class="border rounded p-1 shadow-sm"
          onclick="changeImage(this.src)">
      </div>
    </div>

    <div class="col-lg-6">
      <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h2>
      <div class="mb-3 text-warning fs-5">
      </div>
      <div class="fs-2 fw-bold text-primary mb-4">
        <?php echo formatPrice($product['price']); ?>
      </div>

      <div class="mb-3">
        <i class="fas fa-box me-2"></i>
        <?php if($product['stock'] > 0): ?>
        Còn <b id="stockDisplay"><?php echo $product['stock']; ?></b> sản phẩm trong kho
        <?php else: ?>
        <span class="text-danger fw-bold">Hết hàng</span>
        <?php endif; ?>
      </div>

      <hr>
      <div class="mb-4">
        <h5 class="fw-bold">Mô tả sản phẩm</h5>
        <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
      </div>

      <div class="d-flex align-items-center mb-4">
        <span class="me-3 fw-semibold">Số lượng:</span>
        <div class="input-group" style="width: 140px;">
          <button class="btn btn-outline-secondary rounded-start-pill" type="button" onclick="changeQty(-1)">-</button>
          <input id="qty" value="1" class="form-control text-center bg-white" readonly>
          <button class="btn btn-outline-secondary rounded-end-pill" type="button" onclick="changeQty(1)">+</button>
        </div>
      </div>

      <div class="d-flex gap-3">
        <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold" onclick="addToCart()"
          <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
          <i class="fas fa-cart-plus me-2"></i> Thêm vào giỏ
        </button>
        <button class="btn btn-success px-4 py-2 rounded-pill fw-bold" onclick="buyNow()"
          <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
          <i class="fas fa-bolt me-2"></i> Mua ngay
        </button>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($related)): ?>
<div class="container pb-5">
  <hr class="my-5">
  <h3 class="mb-4 fw-bold">Sản phẩm liên quan</h3>
  <div class="row g-4">
    <?php foreach($related as $p): ?>
    <div class="col-md-3">
      <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
        <img src="<?php echo !empty($p['image']) ? '../uploads/' . htmlspecialchars($p['image']) : '../images/no-image.png'; ?>" class="card-img-top p-3"
          style="height:200px;object-fit:contain" onerror="this.src='../images/no-image.png'">
        <div class="card-body">
          <h6 class="fw-semibold text-truncate"><?php echo htmlspecialchars($p['name']); ?></h6>
          <div class="text-primary fw-bold mb-2"><?php echo formatPrice($p['price']); ?></div>
          <a href="product-detail.php?id=<?php echo $p['id']; ?>"
            class="btn btn-outline-primary btn-sm w-100 rounded-pill">Chi tiết</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>

<script>
// Dữ liệu từ PHP
const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
const productId = <?php echo $id; ?>;
const stockMax = <?php echo (int)$product['stock']; ?>;

function changeImage(src) {
  document.getElementById("mainImage").src = src;
}

/**
 * Xử lý tăng giảm số lượng tại giao diện
 */
function changeQty(delta) {
  let input = document.getElementById("qty");
  let currentQty = parseInt(input.value);
  let newQty = currentQty + delta;

  if (newQty < 1) return;

  if (newQty > stockMax) {
    alert("⚠️ Rất tiếc, kho hàng chỉ còn " + stockMax + " sản phẩm.");
    newQty = stockMax;
  }

  input.value = newQty;
}

/**
 * Kiểm tra quyền đăng nhập
 */
function checkAccess() {
  if (!isLoggedIn) {
    alert("🔒 Bạn cần đăng nhập để thực hiện chức năng này!");
    window.location.href = "login.php?from=product-detail.php?id=" + productId;
    return false;
  }
  return true;
}

/**
 * Thêm vào giỏ hàng
 */
function addToCart() {
  if (!checkAccess()) return;
  if (stockMax <= 0) return alert("Hết hàng!");

  let qty = parseInt(document.getElementById("qty").value);

  fetch("../api/cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        action: "add",
        id: productId,
        qty: qty
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (data.status === "warning") {
          alert("⚠️ " + data.message);
        } else {
          alert("✅ Đã thêm vào giỏ hàng!");
        }
        // Cập nhật Badge giỏ hàng trên Header nếu có
        const cartBadge = document.getElementById('cart-count-badge');
        if (cartBadge) cartBadge.innerText = data.totalItems;
      } else {
        alert("❌ Lỗi: " + (data.message || "Không thể thêm vào giỏ"));
      }
    })
    .catch(err => console.error("Error:", err));
}

/**
 * Mua ngay (Thêm vào giỏ và đi tới thanh toán)
 */
function buyNow() {
  if (!checkAccess()) return;
  if (stockMax <= 0) return alert("Hết hàng!");

  let qty = parseInt(document.getElementById("qty").value);

  fetch("../api/cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        action: "add",
        id: productId,
        qty: qty
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (data.status === "warning") {
          alert("⚠️ " + data.message);
        }
        window.location.href = "checkout.php";
      } else {
        alert("❌ Có lỗi xảy ra khi xử lý Mua ngay.");
      }
    });
}

function uploadProductImage(input) {
  if (!input.files || !input.files[0]) return;

  const file = input.files[0];
  const formData = new FormData();
  formData.append('product_image', file);
  formData.append('product_id', productId); // Biến productId đã có sẵn ở đầu script của cậu

  // Hiển thị thanh loading
  const progress = document.getElementById('uploadProgress');
  progress.classList.remove('d-none');

  fetch('process-upload.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      progress.classList.add('d-none');
      if (data.success) {
        // Cập nhật ảnh mới ngay lập tức trên giao diện
        document.getElementById('mainImage').src = data.newPath + '?t=' + new Date().getTime();
        alert("✅ Cập nhật ảnh thành công!");
      } else {
        alert("❌ Lỗi: " + data.message);
      }
    })
    .catch(err => {
      progress.classList.add('d-none');
      console.error(err);
      alert("❌ Có lỗi xảy ra khi kết nối server.");
    });
}
</script>