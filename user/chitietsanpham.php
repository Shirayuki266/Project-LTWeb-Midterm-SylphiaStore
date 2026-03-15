<?php
session_start();
require_once 'includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: sanpham.php');
    exit;
}

$sql = "SELECT s.*, l.ten_loai FROM sanpham s LEFT JOIN loaisp l ON s.loai = l.id WHERE s.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    header('Location: sanpham.php');
    exit;
}
?>
<?php include 'header.php'; ?>
<div class="container mt-5">
  <div class="row">
    <div class="col-lg-6">
      <div id="productCarousel" class="carousel slide shadow">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="../images/<?php echo htmlspecialchars($product['hinh']); ?>" class="d-block w-100"
              alt="<?php echo htmlspecialchars($product['ten']); ?>">
          </div>
        </div>
      </div>
      <div class="small-images mt-3 d-flex gap-2 justify-content-center">
        <img src="../images/<?php echo htmlspecialchars($product['hinh']); ?>" class="img-thumbnail active-thumb"
          alt="Thumb">
        <!-- More thumbs if multiple images -->
      </div>
    </div>
    <div class="col-lg-6">
      <h1><?php echo htmlspecialchars($product['ten']); ?></h1>
      <p class="text-muted"><?php echo htmlspecialchars($product['ten_loai']); ?></p>
      <div class="rating mb-3">
        <?php for($i=1; $i<=5; $i++): ?>
        <i class="fas fa-star<?php echo $i <= round($product['rating']) ? '' : '-empty'; ?> text-warning"></i>
        <?php endfor; ?>
        <span class="ms-2"><?php echo $product['rating']; ?>/5 (123 đánh giá)</span>
      </div>
      <hr>
      <div class="price-group mb-4">
        <span class="h3 text-danger"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</span>
        <?php if ($product['giamgia'] > 0): ?>
        <span
          class="h5 text-muted text-decoration-line-through ms-3"><?php echo number_format($product['giamgia'], 0, ',', '.'); ?>₫</span>
        <?php endif; ?>
      </div>
      <form>
        <div class="row g-3 align-items-end mb-4">
          <div class="col-md-4">
            <label>Số lượng</label>
            <input type="number" class="form-control" value="1" min="1" id="quantity">
          </div>
          <div class="col-md-8">
            <button type="button" onclick="addToCart(<?php echo $product['id']; ?>)"
              class="btn btn-danger btn-lg w-100">Thêm vào giỏ hàng</button>
          </div>
        </div>
      </form>
      <ul class="list-unstyled">
        <li><i class="fas fa-shipping-fast text-success me-2"></i> Miễn phí vận chuyển toàn quốc</li>
        <li><i class="fas fa-undo text-primary me-2"></i> Đổi trả trong 30 ngày</li>
        <li><i class="fas fa-shield-alt text-info me-2"></i> Bảo hành chính hãng</li>
      </ul>
      <div class="mt-4">
        <h5>Mô tả</h5>
        <p><?php echo nl2br(htmlspecialchars($product['mota'] ?? 'Sản phẩm chất lượng cao từ thương hiệu uy tín.')); ?>
        </p>
      </div>
    </div>
  </div>
</div>
<script src="../vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script>
function addToCart(id) {
  const qty = document.getElementById('quantity').value;
  // AJAX to cart.php
  fetch('cart.php?action=add', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: id,
      qty: qty
    })
  }).then(res => res.json()).then(data => {
    alert('Thêm giỏ hàng thành công!');
  });
}
</script>
<?php include 'footer.php'; ?>