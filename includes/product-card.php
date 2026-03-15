<?php
if (!isset($product)) return;
?>

<div class="card h-100 shadow-sm border-0">

  <img src="<?php echo $product['image']; ?>" class="card-img-top p-3" style="height:200px;object-fit:contain"
    onerror="this.src='../images/logoshop.png'">

  <div class="card-body text-center">

    <h6 class="fw-bold">
      <?php echo htmlspecialchars($product['name']); ?>
    </h6>

    <div class="fw-bold text-primary fs-5">
      <?php echo number_format($product['price']); ?>đ
    </div>

  </div>

  <div class="card-footer bg-white border-0 text-center">

    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">

      Chi tiết

    </a>

    <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">

      <i class="fas fa-cart-plus"></i> Thêm giỏ

    </button>

  </div>

</div>