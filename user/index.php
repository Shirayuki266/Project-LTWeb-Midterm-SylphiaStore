<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
// require_once '../api/products.php'; // Use AJAX instead
require_once '../includes/functions.php';

/* HOT PRODUCTS - Direct query to avoid class */
$hotProducts = $conn->query("SELECT id, name, price, image FROM products ORDER BY id DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

/* FLASH SALE */
$flashSaleProducts = $conn->query("SELECT id, name, price, image FROM products ORDER BY RAND() LIMIT 4")->fetch_all(MYSQLI_ASSOC);

/* CATEGORIES */
$cats = $conn->query("SELECT * FROM categories LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$page_title = 'Trang chủ';
include 'header.php';
?>

<!-- HERO -->
<section class="bg-primary text-white py-5">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-lg-6">
        <h1 class="fw-bold display-5">Công nghệ chính hãng</h1>
        <p class="lead">Điện thoại • Laptop • Phụ kiện</p>
        <a href="products.php" class="btn btn-light btn-lg">
          Mua ngay
        </a>

      </div>

      <div class="col-lg-6 text-center">
        <img src="../images/loggo_intro.png" class="img-fluid rounded shadow">
      </div>

    </div>
  </div>
</section>



<!-- FLASH SALE -->
<section class="py-5 bg-danger text-white">

  <div class="container">

    <h2 class="mb-4">
      🔥 Flash Sale
    </h2>

    <div class="row g-4">
      <?php foreach($flashSaleProducts as $product): ?>

      <div class="col-md-3">
        <?php include '../includes/product-card.php'; ?>
      </div>

      <?php endforeach; ?>

    </div>

  </div>

</section>


<!-- HOT PRODUCTS -->
<section class="py-5">

  <div class="container">

    <h2 class="mb-4">
      ⭐ Sản phẩm bán chạy
    </h2>

    <div class="row g-4">

      <?php foreach($hotProducts as $product): ?>

      <div class="col-md-3">
        <?php include '../includes/product-card.php'; ?>
      </div>

      <?php endforeach; ?>

    </div>

  </div>

</section>
<?php include 'footer.php'; ?>