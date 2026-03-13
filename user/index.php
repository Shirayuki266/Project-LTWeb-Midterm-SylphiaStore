<?php
session_start();
require_once '../api/db.php';
require_once '../api/products.php';
require_once '../includes/functions.php';

$productsObj = new Products($conn);
$featured = $productsObj->getProducts('', 0, 0, 0, 1, 6)['data']; // Top 6
$cats = $conn->query("SELECT * FROM product_type")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css"> <!-- Use existing or create -->
</head>

<body>
  <header class="bg-primary text-white p-3">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-3">
          <a href="index.php" class="navbar-brand">
            <img src="../images/logo-web-removebg-preview.png" alt="Logo" height="50">
            Sylphia Shop
          </a>
        </div>
        <div class="col-md-5">
          <form action="products.php" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Tìm kiếm sản phẩm...">
            <button class="btn btn-outline-light"><i class="fas fa-search"></i></button>
          </form>
        </div>
        <div class="col-md-4 text-end">
          <a href="cart.php" class="btn btn-outline-light me-2"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
          <?php if (isset($_SESSION['user_id'])): ?>
          <a href="profile.php" class="btn btn-outline-light me-2">Xin chào,
            <?php echo $_SESSION['username'] ?? ''; ?></a>
          <a href="logout.php" class="btn btn-light">Đăng xuất</a>
          <?php else: ?>
          <a href="login.php" class="btn btn-outline-light me-2">Đăng nhập</a>
          <a href="register.php" class="btn btn-light">Đăng ký</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="container my-5">
    <!-- Hero -->
    <section class="hero bg-dark text-white text-center py-5 mb-5 rounded">
      <div class="hero-content">
        <h1 class="display-4">Chào mừng đến Sylphia Shop!</h1>
        <p class="lead">Khám phá hàng ngàn sản phẩm chất lượng cao với giá tốt nhất</p>
        <a href="products.php" class="btn btn-primary btn-lg">Mua sắm ngay</a>
      </div>
    </section>

    <!-- Categories -->
    <section class="mb-5">
      <h2 class="text-center mb-4">Danh mục nổi bật</h2>
      <div class="row g-3">
        <?php foreach ($cats as $cat): ?>
        <div class="col-md-3">
          <a href="products.php?category=<?php echo $cat['id']; ?>" class="card h-100 text-center border-0 shadow-sm">
            <div class="card-body py-4">
              <i class="fas fa-laptop fa-3x text-primary mb-3"></i>
              <h5><?php echo htmlspecialchars($cat['ten_loai']); ?></h5>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Featured Products -->
    <section>
      <h2 class="text-center mb-4">Sản phẩm nổi bật</h2>
      <div class="row g-4">
        <?php foreach ($featured as $p): $disp_price = $p['discount_price'] ?: $p['price']; ?>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="card h-100 shadow-sm">
            <img src="../images/<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top"
              alt="<?php echo htmlspecialchars($p['name']); ?>">
            <div class="card-body">
              <h6 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h6>
              <?php echo renderStars($p['rating']); ?>
              <div class="price mt-2">
                <strong><?php echo formatPrice($disp_price); ?></strong>
                <?php if ($p['discount_price'] && $p['discount_price'] < $p['price']): ?>
                <small class="text-muted text-decoration-line-through"><?php echo formatPrice($p['price']); ?></small>
                <?php endif; ?>
              </div>
            </div>
            <div class="card-footer bg-transparent">
              <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary w-100 mb-1">Chi
                tiết</a>
              <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn btn-primary w-100">Giỏ hàng</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="text-center mt-4">
        <a href="products.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
      </div>
    </section>
  </main>

  <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
      <p>&copy; 2024 Sylphia Shop. All rights reserved. | Hotline: 0917 997 997</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function addToCart(id) {
    fetch('api/cart.php?action=add&id=' + id, {
        method: 'POST'
      })
      .then(res => res.json())
      .then(data => alert(data.message || 'Added to cart!'))
      .catch(err => alert('Error'));
  }
  </script>
</body>

</html>