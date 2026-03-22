<?php
if (!isset($product)) return;

// LOGIC XỬ LÝ ĐƯỜNG DẪN ẢNH ĐỂ KHÔNG BỊ LỖI 404
$imgRaw = trim($product['image']);
if (strpos($imgRaw, 'http') === 0) {
    // Nếu là link ảnh từ internet
    $finalPath = $imgRaw;
} else {
    // Nếu là ảnh nội bộ, ưu tiên tìm trong thư mục uploads (nơi Admin lưu file)
    // Nếu không thấy trong uploads thì tìm trong images
    if (file_exists("../uploads/" . $imgRaw) && !empty($imgRaw)) {
        $finalPath = "../uploads/" . $imgRaw;
    } else {
        $finalPath = "../images/" . ($imgRaw ?: 'logoshop.png');
    }
}
?>

<div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden product-card-hover">
  <div class="p-3 bg-light d-flex align-items-center justify-content-center" style="height:220px;">
    <img src="<?php echo $finalPath; ?>" class="img-fluid" style="max-height: 100%; object-fit: contain;"
      onerror="this.src='../images/logoshop.png'">
  </div>

  <div class="card-body text-center">
    <h6 class="fw-bold mb-2 text-truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
      <?php echo htmlspecialchars($product['name']); ?>
    </h6>

    <div class="fw-bold text-primary fs-5 mb-3">
      <?php echo number_format($product['price']); ?>đ
    </div>
  </div>

  <div class="card-footer bg-white border-0 text-center pb-4">
    <div class="d-grid gap-2 d-md-block">
      <a href="product-detail.php?id=<?php echo $product['id']; ?>"
        class="btn btn-outline-dark btn-sm rounded-pill px-3">
        Chi tiết
      </a>

      <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"
        onclick="addToCart(<?php echo $product['id']; ?>)">
        <i class="fas fa-cart-plus me-1"></i> Thêm giỏ
      </button>
    </div>
  </div>
</div>

<style>
/* Hiệu ứng di chuột cho card sản phẩm chuyên nghiệp hơn */
.product-card-hover {
  transition: transform 0.3s ease, shadow 0.3s ease;
}

.product-card-hover:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}
</style>