<?php
session_start();
require_once '../api/db.php';
require_once '../api/products.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$productsObj = new Products($conn);
$search = $_GET['search'] ?? '';
$category = (int)($_GET['category'] ?? 0);
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$page = (int)($_GET['page'] ?? 1);

$result = $productsObj->getProducts($search, $category, $min_price, $max_price, $page);
$products = $result['data'];
$pages = $result['pages'];
$cats = $conn->query("SELECT * FROM product_type")->fetch_all(MYSQLI_ASSOC);

$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sản phẩm - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
  <!-- Header (reuse from index or inline mini) -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="../user/index.php">Sylphia Shop</a>
      <form class="d-flex me-auto" method="GET">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control me-2"
          placeholder="Tìm kiếm...">
        <button class="btn btn-outline-light"><i class="fas fa-search"></i></button>
      </form>
      <div>
        <a href="cart.php" class="btn btn-outline-light me-2"><i class="fas fa-shopping-cart"></i></a>
        <?php if ($isLoggedIn): ?>
        <a href="profile.php" class="btn btn-light me-2">Tài khoản</a>
        <?php else: ?>
        <a href="login.php" class="btn btn-light">Đăng nhập</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <div class="container my-5">
    <div class="row">
      <!-- Filters -->
      <div class="col-lg-3">
        <div class="card mb-4">
          <div class="card-header"><strong>Bộ lọc</strong></div>
          <div class="card-body">
            <h6>Danh mục</h6>
            <ul class="list-unstyled">
              <li><a href="?<?php echo http_build_query(array_diff_key($_GET, ['category'=>1])); ?>"
                  class="text-decoration-none <?php echo !$category ? 'fw-bold' : ''; ?>">Tất cả</a></li>
              <?php foreach ($cats as $cat): $sel = $category == $cat['id'] ? 'fw-bold' : ''; $cat_name = $cat['ten_loai']; ?>
              <li><a
                  href="?category=<?php echo $cat['id']; ?>&<?php echo http_build_query(array_diff_key($_GET, ['category'=>1])); ?>"
                  class="<?php echo $sel; ?>"><?php echo $cat_name; ?></a></li>
              <?php endforeach; ?>
            </ul>
            <h6 class="mt-4">Khoảng giá</h6>
            <form method="GET">
              <input type="hidden" name="search" value="<?php echo $search; ?>">
              <input type="hidden" name="category" value="<?php echo $category; ?>">
              <div class="mb-3">
                <input type="number" name="min_price" value="<?php echo $min_price ?: ''; ?>"
                  class="form-control form-control-sm" placeholder="Từ">
              </div>
              <div class="mb-3">
                <input type="number" name="max_price" value="<?php echo $max_price ?: ''; ?>"
                  class="form-control form-control-sm" placeholder="Đến">
              </div>
              <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Products -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Kết quả tìm kiếm (<?php echo count($products); ?>)</h2>
          <div>
            <span>Sắp xếp: </span>
            <a href="?sort=price_asc&<?php echo http_build_query(array_diff_key($_GET, ['sort'=>1])); ?>"
              class="btn btn-sm btn-outline-primary">Giá ↑</a>
            <a href="?sort=price_desc&<?php echo http_build_query(array_diff_key($_GET, ['sort'=>1])); ?>"
              class="btn btn-sm btn-outline-primary">Giá ↓</a>
          </div>
        </div>

        <div class="row g-4">
          <?php if (empty($products)): ?>
          <div class="col-12 text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4>Không tìm thấy sản phẩm</h4>
            <p>Thử thay đổi từ khóa hoặc bộ lọc</p>
          </div>
          <?php else: ?>
          <?php foreach ($products as $p): $disp_price = $p['discount_price'] ?: $p['price']; ?>
          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
              <img src="../images/<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top"
                alt="<?php echo htmlspecialchars($p['name']); ?>">
              <div class="card-body">
                <h6><?php echo htmlspecialchars($p['name']); ?></h6>
                <p class="small"><?php echo htmlspecialchars(substr($p['description'] ?? '', 0, 100)); ?>...</p>
                <?php echo renderStars($p['rating']); ?>
                <div class="mt-2">
                  <strong><?php echo formatPrice($disp_price); ?></strong>
                  <?php if ($p['discount_price'] && $p['discount_price'] < $p['price']): ?>
                  <small
                    class="ms-2 text-muted text-decoration-line-through"><?php echo formatPrice($p['price']); ?></small>
                  <?php endif; ?>
                  <small class="badge bg-success ms-2">Còn hàng</small>
                </div>
              </div>
              <div class="card-footer pt-0">
                <div class="d-grid gap-1">
                  <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary btn-sm">Chi
                    tiết</a>
                  <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn btn-primary btn-sm">Thêm giỏ
                    hàng</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <nav class="mt-5">
          <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $pages; $i++): $active = $i == $page ? 'active' : ''; ?>
            <li class="page-item <?php echo $active; ?>">
              <a class="page-link"
                href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page'=>1])); ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function addToCart(id) {
    if (confirm('Thêm vào giỏ hàng?')) {
      // AJAX call stub
      fetch('../api/cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'add',
          id: id,
          qty: 1
        })
      }).then(res => res.json()).then(data => {
        if (data.success) alert('Đã thêm!');
      });
    }
  }
  </script>
</body>

</html>