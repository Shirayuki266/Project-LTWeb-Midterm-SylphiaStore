<?php
session_start();
require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';

$cart = new Cart($conn);
$items = $cart->getItems();
$total = $cart->getTotal();
$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giỏ hàng - Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <nav class="navbar navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">Sylphia Shop</a>
      <a href="checkout.php" class="btn btn-light">Thanh toán</a>
    </div>
  </nav>

  <div class="container my-5">
    <h2>Giỏ hàng của bạn (<?php echo count($items); ?> sản phẩm)</h2>

    <?php if (empty($items)): ?>
    <div class="alert alert-info text-center">
      <i class="fas fa-shopping-cart fa-3x mb-3"></i>
      <h4>Giỏ hàng trống</h4>
      <a href="products.php" class="btn btn-primary">Mua sắm ngay</a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Tạm tính</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <img src="../images/<?php echo $item['image']; ?>" width="50" class="me-3">
              <?php echo htmlspecialchars($item['name']); ?>
            </td>
            <td><?php echo formatPrice($item['price']); ?></td>
            <td>
              <input type="number" class="form-control w-75 d-inline qty-input" value="<?php echo $item['quantity']; ?>"
                min="1" data-id="<?php echo $item['id']; ?>">
            </td>
            <td><?php echo formatPrice($item['subtotal']); ?></td>
            <td>
              <button class="btn btn-sm btn-danger remove-item" data-id="<?php echo $item['id']; ?>">Xóa</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="row justify-content-end">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h5>Tổng cộng: <?php echo formatPrice($total); ?></h5>
            <a href="checkout.php" class="btn btn-success w-100">Tiến hành thanh toán</a>
            <button onclick="clearCart()" class="btn btn-outline-secondary w-100 mt-2">Xóa tất cả</button>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function updateCart(id, qty) {
    fetch('../api/cart.php', {
      method: 'POST',
      body: JSON.stringify({
        action: 'update',
        id,
        qty
      })
    });
  }

  function removeItem(id) {
    if (confirm('Xóa sản phẩm?')) {
      fetch('../api/cart.php', {
          method: 'POST',
          body: JSON.stringify({
            action: 'remove',
            id
          })
        })
        .then(() => location.reload());
    }
  }

  function clearCart() {
    if (confirm('Xóa toàn bộ giỏ?')) {
      fetch('../api/cart.php', {
          method: 'POST',
          body: JSON.stringify({
            action: 'clear'
          })
        })
        .then(() => location.reload());
    }
  }

  document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', function() {
      updateCart(this.dataset.id, this.value);
    });
  });

  document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', () => removeItem(btn.dataset.id));
  });
  </script>