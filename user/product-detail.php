<?php
require_once '../api/db.php';
require_once '../api/products.php';

$id = (int)($_GET['id'] ?? 0);
$productsObj = new Products($conn);
$product = $productsObj->getById($id);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Related products same cat
$related = $productsObj->getProducts('', $product['category_id'], 0, 0, 1, 4)['data'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['name']); ?> - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">← Sylphia Shop</a>
    </div>
  </nav>

  <div class="container my-5">
    <div class="row">
      <div class="col-lg-6">
        <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid rounded shadow">
      </div>
      <div class="col-lg-6">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="text-muted">Danh mục: <?php echo htmlspecialchars($product['cat_name']); ?></p>
        <?php echo renderStars($product['rating']); ?>
        <h3><?php echo formatPrice($product['sell_price'] ?: ($product['discount_price'] ?: $product['price'])); ?></h3>
        <p class="text-success fs-5">Còn <?php echo $product['stock']; ?> sản phẩm</p>

        <div class="mb-4">
          <label>Số lượng:</label>
          <input type="number" id="qty" class="form-control w-25 d-inline ms-3" value="1" min="1"
            max="<?php echo $product['stock']; ?>">
          <button onclick="addToCart(<?php echo $id; ?>)" class="btn btn-success btn-lg ms-3">Thêm giỏ hàng</button>
        </div>

        <div class="mt-4">
          <h6>Mô tả:</h6>
          <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
      </div>
    </div>

    <!-- Related -->
    <?php if ($related): ?>
    <div class="mt-5">
      <h3>Sản phẩm liên quan</h3>
      <div class="row g-3">
        <?php foreach ($related as $r): ?>
        <div class="col-md-3">
          <div class="card">
            <img src="../images/<?php echo $r['image']; ?>" class="card-img-top">
            <div class="card-body">
              <h6><?php echo htmlspecialchars($r['name']); ?></h6>
              <a href="product-detail.php?id=<?php echo $r['id']; ?>" class="btn btn-primary">Xem</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function addToCart(id) {
    const qty = document.getElementById('qty').value;
    fetch('../api/cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'add',
        id,
        qty
      })
    }).then(res => res.json()).then(data => {
      if (data.success) alert('Đã thêm ' + qty + ' sản phẩm!');
    });
  }
  </script>
</body>

</html>