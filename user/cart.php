<?php
session_start();

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

/* CHECK LOGIN */
$auth = new Auth($conn);

if (!$auth->isLoggedIn()) {
    header("Location: login.php?return_url=cart.php");
    exit;
}

/* CART */
$cart = new Cart($conn);

$items = $cart->getItems();
$total = $cart->getTotal();
$total_items = $cart->getTotalItems();

/* SAVE COUNT FOR HEADER BADGE */
$_SESSION['cart_count'] = $total_items;

$page_title = "Giỏ hàng";
$current_page = "cart";
?>

<!DOCTYPE html>
<html lang="vi">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Giỏ hàng - Sylphia Shop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css">

</head>

<body>

  <?php include 'header.php'; ?>

  <main class="container my-5">

    <h2 class="mb-4">
      <i class="fas fa-shopping-cart text-primary me-2"></i>
      Giỏ hàng (<?php echo $total_items; ?> sản phẩm)
    </h2>

    <?php if(empty($items)): ?>

    <div class="text-center py-5">

      <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>

      <h4 class="text-muted">Giỏ hàng của bạn đang trống</h4>

      <p class="text-muted">Hãy thêm sản phẩm để tiếp tục mua sắm</p>

      <a href="products.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i>
        Quay lại mua sắm
      </a>

    </div>

    <?php else: ?>

    <div class="row g-4">

      <!-- CART ITEMS -->
      <div class="col-lg-8">

        <div class="card shadow-sm">

          <div class="card-header bg-light">
            <strong>Sản phẩm trong giỏ</strong>
          </div>

          <div class="table-responsive">

            <table class="table align-middle mb-0">

              <thead class="table-light">

                <tr>
                  <th>Sản phẩm</th>
                  <th>Đơn giá</th>
                  <th>Số lượng</th>
                  <th>Thành tiền</th>
                  <th></th>
                </tr>

              </thead>

              <tbody>

                <?php foreach($items as $item): ?>

                <tr>

                  <td>

                    <div class="d-flex align-items-center">

                      <img src="../images/<?php echo htmlspecialchars($item['image']); ?>"
                        style="width:60px;height:60px;object-fit:cover" class="rounded me-3">

                      <div>

                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>

                        <br>

                        <small class="text-muted">
                          <?php echo htmlspecialchars($item['description']); ?>
                        </small>

                      </div>

                    </div>

                  </td>

                  <td>
                    <strong><?php echo formatPrice($item['price']); ?></strong>
                  </td>

                  <td>

                    <div class="input-group input-group-sm" style="max-width:120px">

                      <button class="btn btn-outline-secondary qty-dec" data-id="<?php echo $item['id']; ?>">-</button>

                      <input type="number" class="form-control text-center qty" value="<?php echo $item['quantity']; ?>"
                        readonly>

                      <button class="btn btn-outline-secondary qty-inc" data-id="<?php echo $item['id']; ?>">+</button>

                    </div>

                  </td>

                  <td id="subtotal-<?php echo $item['id']; ?>">

                    <strong><?php echo formatPrice($item['subtotal']); ?></strong>

                  </td>

                  <td>

                    <button class="btn btn-outline-danger btn-sm remove-cart-item" data-id="<?php echo $item['id']; ?>">

                      <i class="fas fa-trash"></i>

                    </button>

                  </td>

                </tr>

                <?php endforeach; ?>

              </tbody>

            </table>

          </div>

        </div>

      </div>

      <!-- ORDER SUMMARY -->
      <div class="col-lg-4">

        <div class="card shadow-sm sticky-top" style="top:20px">

          <div class="card-header bg-light">
            <strong>Tổng đơn hàng</strong>
          </div>

          <div class="card-body">

            <div class="d-flex justify-content-between mb-2">

              <span>Tạm tính:</span>

              <span id="cart-subtotal">
                <?php echo formatPrice($total); ?>
              </span>

            </div>

            <div class="d-flex justify-content-between mb-2">

              <span>Phí vận chuyển:</span>

              <span>30,000₫</span>

            </div>

            <hr>

            <div class="d-flex justify-content-between fw-bold text-success fs-5 mb-3">

              <span>Tổng tiền:</span>

              <span id="cart-total">

                <?php echo formatPrice($total + 30000); ?>

              </span>

            </div>

            <a href="checkout.php" class="btn btn-success w-100 mb-2">

              <i class="fas fa-credit-card me-2"></i>
              Thanh toán

            </a>

            <a href="products.php" class="btn btn-outline-secondary w-100">

              <i class="fas fa-arrow-left me-2"></i>
              Tiếp tục mua sắm

            </a>

          </div>

        </div>

      </div>

    </div>

    <?php endif; ?>

  </main>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  /* UPDATE CART */
  function updateCartItem(id, qty) {

    fetch('../api/cart.php', {

        method: 'POST',

        headers: {
          'Content-Type': 'application/json'
        },

        body: JSON.stringify({
          action: 'update',
          id: id,
          qty: qty
        })

      })
      .then(res => res.json())
      .then(data => {

        if (data.success) {

          location.reload();

        } else {

          alert('Lỗi cập nhật giỏ hàng');

        }

      });

  }

  /* REMOVE ITEM */
  document.querySelectorAll('.remove-cart-item').forEach(btn => {

    btn.onclick = function() {

      const id = this.dataset.id;

      if (confirm('Xóa sản phẩm khỏi giỏ hàng?')) {

        fetch('../api/cart.php', {

            method: 'POST',

            headers: {
              'Content-Type': 'application/json'
            },

            body: JSON.stringify({
              action: 'remove',
              id: id
            })

          })
          .then(res => res.json())
          .then(data => {

            if (data.success) {

              location.reload();

            }

          });

      }

    }

  });

  /* QUANTITY BUTTON */
  document.querySelectorAll('.qty-inc,.qty-dec').forEach(btn => {

    btn.onclick = function() {

      const id = this.dataset.id;

      const input = this.parentNode.querySelector('.qty');

      let qty = parseInt(input.value);

      if (this.classList.contains('qty-inc')) qty++;
      else if (qty > 1) qty--;

      updateCartItem(id, qty);

    }

  });
  </script>

</body>

</html>