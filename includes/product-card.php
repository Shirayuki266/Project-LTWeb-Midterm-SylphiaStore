<?php
if (!isset($product)) return;

// 1. Logic xử lý ảnh
$imgRaw = trim($product['image']);
$finalPath = (strpos($imgRaw, 'http') === 0) ? $imgRaw : "../uploads/" . ($imgRaw ?: 'logoshop.png');

// 2. Dữ liệu từ SQL
$name = htmlspecialchars($product['name']);
$price = number_format($product['price'], 0, ',', '.');
$category = htmlspecialchars($product['category_name'] ?? 'Thiết bị');
$stock = (int)($product['stock'] ?? 0);
$unit = htmlspecialchars($product['unit'] ?? 'Chiếc');
?>

<a href="product-detail.php?id=<?php echo $product['id']; ?>"
  class="card h-100 border-0 shadow-sm rounded-4 text-decoration-none text-dark product-entry-card">

  <div class="bg-light d-flex align-items-center justify-content-center p-3 position-relative"
    style="height: 200px; border-radius: 1rem 1rem 0 0; overflow: hidden;">

    <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1">
      <?php if($stock <= 0): ?>
      <span class="badge bg-secondary fw-normal shadow-sm">Hết hàng</span>
      <?php elseif($stock <= 10): ?>
      <span class="badge bg-warning text-dark fw-normal shadow-sm">Sắp hết hàng</span>
      <?php else: ?>
      <span class="badge bg-danger fw-normal shadow-sm">Bán chạy</span>
      <?php endif; ?>
    </div>

    <img src="<?php echo $finalPath; ?>" class="img-fluid main-img" style="max-height: 100%; object-fit: contain;"
      onerror="this.src='../images/logoshop.png'">
  </div>

  <div class="card-body p-3 d-flex flex-column text-center">
    <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">
      <?php echo $category; ?>
    </div>

    <h6 class="card-title fw-bold mb-2 text-dark"
      style="font-size: 0.95rem; height: 2.8em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
      <?php echo $name; ?>
    </h6>

    <div class="text-primary fw-bolder fs-5 mb-0">
      <?php echo $price; ?>đ
    </div>
    <small class="text-muted mb-3" style="font-size: 0.7rem;">/ <?php echo $unit; ?></small>

    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
      <small class="text-muted" style="font-size: 0.75rem;">
        <i class="bi bi-box-seam me-1"></i> Kho: <strong><?php echo $stock; ?></strong>
      </small>
      <span class="text-primary small fw-bold" style="font-size: 0.75rem;">
        Chi tiết <i class="bi bi-chevron-right"></i>
      </span>
    </div>
  </div>
</a>

<style>
/* CSS bổ trợ để tăng tính tương tác khi hover */
.product-entry-card {
  transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
  border: 1px solid transparent !important;
}

.product-entry-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12) !important;
  border: 1px solid #0d6efd22 !important;
}

.product-entry-card .main-img {
  transition: transform 0.5s ease;
}

.product-entry-card:hover .main-img {
  transform: scale(1.08);
}

.product-entry-card:hover .card-title {
  color: #0d6efd !important;
}
</style>