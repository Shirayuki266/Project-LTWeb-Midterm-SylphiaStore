<?php
session_start();
require_once 'includes/config.php';
?>
<?php include 'header.php'; ?>
<div class="container mt-5">
  <h1>Giỏ hàng của bạn</h1>
  <?php if (empty($_SESSION['cart'])): ?>
  <div class="text-center py-5">
    <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
    <h3>Giỏ hàng trống</h3>
    <a href="sanpham.php" class="btn btn-danger btn-lg">Mua sắm ngay</a>
  </div>
  <?php else: ?>
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h5>Sản phẩm trong giỏ (<span id="cart-count"><?php echo count($_SESSION['cart']); ?></span>)</h5>
        </div>
        <div class="cart-items">
          <?php foreach ($_SESSION['cart'] as $id => $item): ?>
          <div class="row align-items-center py-3 border-bottom">
            <div class="col-2">
              <img src="../images/<?php echo htmlspecialchars($item['img']); ?>" class="img-fluid"
                alt="<?php echo htmlspecialchars($item['name']); ?>">
            </div>
            <div class="col-4">
              <h6><?php echo htmlspecialchars($item['name']); ?></h6>
            </div>
            <div class="col-2">
              <input type="number" class="form-control qty-input" value="<?php echo $item['qty']; ?>" min="1">
            </div>
            <div class="col-2 text-end">
              <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>₫
            </div>
            <div class="col-2 text-end">
              <button class="btn btn-sm btn-outline-danger remove-item" data-id="<?php echo $id; ?>">Xóa</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5>Tổng kết</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <span>Tạm tính:</span>
            <span
              id="subtotal"><?php echo number_format(array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $_SESSION['cart'])), 0, ',', '.'); ?>₫</span>
          </div>
          <hr>
          <a href="thanhtoan.php" class="btn btn-danger w-100 btn-lg">Tiến hành thanh toán</a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<script>
// Cart JS
document.querySelectorAll('.remove-item').forEach(btn => {
  btn.onclick = function() {
    // AJAX remove
  };
});
</script>
<?php include 'footer.php'; ?>