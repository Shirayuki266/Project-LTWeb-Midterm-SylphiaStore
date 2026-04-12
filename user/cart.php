<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);

// 1. Kiểm tra đăng nhập
if (!$auth->isLoggedIn()) {
    header("Location: login.php?from=cart.php");
    exit(); 
}

$cart = new Cart($conn);
$items = $cart->getItems();
$total_items_in_cart = $cart->getTotalItems();

$_SESSION['cart_count'] = $total_items_in_cart;
$page_title = "Giỏ hàng - Sylphia Shop";
include 'header.php';
?>

<style>
/* Hiệu ứng checkbox chuyên nghiệp */
.cart-checkbox {
  width: 22px;
  height: 22px;
  cursor: pointer;
  border: 2px solid #dee2e6;
  border-radius: 6px;
}

.cart-checkbox:checked {
  background-color: #0066cc;
  border-color: #0066cc;
}

.product-img {
  width: 70px;
  height: 70px;
  object-fit: contain;
  background: #fff;
  border-radius: 10px;
  border: 1px solid #eee;
}

.sticky-summary {
  position: sticky;
  top: 90px;
  z-index: 10;
}

.cart-item-row.selected {
  background-color: #f8fbff;
}
</style>

<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">
      <i class="fas fa-shopping-cart text-primary me-2"></i>Giỏ hàng
    </h2>
    <span id="top-cart-count" class="badge bg-primary rounded-pill px-3 py-2">Tổng <?php echo $total_items_in_cart; ?>
      món</span>
  </div>

  <?php if (empty($items)): ?>
  <div class="card border-0 shadow-sm text-center py-5 rounded-4">
    <div class="card-body">
      <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="120" class="mb-3 opacity-50">
      <h4 class="text-muted">Giỏ hàng của bạn đang trống</h4>
      <a href="products.php" class="btn btn-primary px-4 mt-3 rounded-pill">Tiếp tục mua sắm</a>
    </div>
  </div>
  <?php else: ?>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
          <table class="table align-middle mb-0" id="cartTable">
            <thead class="table-light">
              <tr>
                <th class="ps-4" width="50">
                  <input type="checkbox" class="form-check-input cart-checkbox" id="checkAll" checked>
                </th>
                <th>Sản phẩm</th>
                <th>Đơn giá</th>
                <th width="130">Số lượng</th>
                <th>Thành tiền</th>
                <th class="text-center">Xóa</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr class="cart-item-row selected" data-id="<?php echo $item['id']; ?>"
                data-price="<?php echo $item['price']; ?>" data-stock="<?php echo $item['stock']; ?>">
                <td class="ps-4">
                  <input type="checkbox" class="form-check-input cart-checkbox item-checkbox"
                    value="<?php echo $item['id']; ?>" checked>
                </td>
                <td>
                  <div class="d-flex align-items-center py-2">
                    <img src="<?php echo htmlspecialchars(getProductImagePath($item['image'])); ?>"
                      class="product-img me-3">
                    <div>
                      <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($item['name']); ?></div>
                      <small class="text-muted">ID: #<?php echo $item['id']; ?></small>
                    </div>
                  </div>
                </td>
                <td><span class="fw-medium"><?php echo formatPrice($item['price']); ?></span></td>
                <td>
                  <div class="input-group input-group-sm border rounded-pill overflow-hidden">
                    <button class="btn btn-link text-dark border-0 qty-dec" data-id="<?php echo $item['id']; ?>"><i
                        class="fas fa-minus"></i></button>
                    <input type="text" class="form-control border-0 text-center qty-val fw-bold bg-white"
                      value="<?php echo $item['quantity']; ?>" readonly>
                    <button class="btn btn-link text-dark border-0 qty-inc" data-id="<?php echo $item['id']; ?>"><i
                        class="fas fa-plus"></i></button>
                  </div>
                </td>
                <td><span
                    class="text-primary fw-bold subtotal-item"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                </td>
                <td class="text-center">
                  <button class="btn btn-link text-danger p-0 remove-item" data-id="<?php echo $item['id']; ?>">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="sticky-summary">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-4">Chi tiết thanh toán</h5>

          <div class="d-flex justify-content-between mb-3 text-muted">
            <span>Tạm tính (<span id="selected-count">0</span> món):</span>
            <span class="fw-bold text-dark" id="summary-subtotal">0₫</span>
          </div>

          <div class="d-flex justify-content-between mb-3 text-muted">
            <span>Phí giao hàng:</span>
            <span class="fw-bold text-success" id="summary-shipping">30.000₫</span>
          </div>

          <hr class="my-4 opacity-50">

          <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="fw-bold fs-5">Tổng cộng:</span>
            <div class="text-end">
              <span class="text-danger fw-bold fs-3 d-block" id="summary-total">0₫</span>
              <small class="text-muted small">(Đã bao gồm VAT)</small>
            </div>
          </div>

          <button onclick="goToCheckout()"
            class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold shadow-sm mb-3">
            MUA HÀNG NGAY
          </button>

          <a href="products.php" class="btn btn-outline-secondary w-100 py-2 rounded-pill border-0">
            <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</main>

<script src="../sylphia_shop.js/common.js"></script>
<script src="../sylphia_shop.js/cart.js"></script>

<?php include 'footer.php'; ?>