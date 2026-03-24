<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/cart.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

/* 1. KHỞI TẠO ĐỐI TƯỢNG AUTH TRƯỚC */
$auth = new Auth($conn);

/* 2. GẮN Ổ KHÓA TẠI ĐÂY */
if (!$auth->isLoggedIn()) {
    // Nếu chưa đăng nhập, đuổi ngay ra trang login
    // Thêm return_url để sau khi login xong nó tự quay lại giỏ hàng
    header("Location: login.php?return_url=cart.php");
    exit(); 
}

/* 3. NẾU ĐÃ ĐĂNG NHẬP THÌ MỚI CHẠY TIẾP CODE DƯỚI NÀY */
$cart = new Cart($conn);
$items = $cart->getItems();
$total = $cart->getTotal();
$total_items = $cart->getTotalItems();

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  /* 1. Sửa lỗi Sticky đè lên Header */
  .sticky-summary {
    position: -webkit-sticky;
    position: sticky;
    top: 100px;
    /* Khoảng cách an toàn để không chạm vào Header */
    z-index: 10;
    margin-bottom: 2rem;
  }

  /* 2. Style cho ảnh sản phẩm trong giỏ */
  .product-img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    background: #fff;
    border-radius: 12px;
    padding: 5px;
    border: 1px solid #f0f0f0;
  }

  /* 3. Tinh chỉnh Card tóm tắt */
  .card-summary {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
  }

  .btn-checkout {
    background: #0066cc;
    border: none;
    transition: all 0.3s ease;
  }

  .btn-checkout:hover {
    background: #0052a3;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
  }
  </style>
</head>

<body class="bg-light">
  <?php include 'header.php'; ?>

  <main class="container my-5">
    <h2 class="mb-4 fw-bold">
      <i class="fas fa-shopping-bag text-primary me-2"></i>
      Giỏ hàng của bạn (<?php echo $total_items; ?>)
    </h2>

    <?php if (empty($items)): ?>
    <div class="card shadow-sm text-center py-5">
      <div class="card-body">
        <i class="fas fa-cart-plus fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Giỏ hàng đang trống</h4>
        <p class="mb-4 text-secondary">Hãy chọn những món đồ ưng ý cho mình nhé!</p>
        <a href="products.php" class="btn btn-primary px-5 rounded-pill shadow-sm">Mua sắm ngay</a>
      </div>
    </div>
    <?php else: ?>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card shadow-sm overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4 py-3">Sản phẩm</th>
                  <th>Giá</th>
                  <th>Số lượng</th>
                  <th>Tổng</th>
                  <th class="text-center">Xóa</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                  <td class="ps-4">
                    <div class="d-flex align-items-center py-2">
                      <?php 
                                                $imgRaw = trim($item['image']);
                                                // LOGIC THÔNG MINH: Kiểm tra nếu là Link Web (http/https)
                                                if (strpos($imgRaw, 'http') === 0) {
                                                    $finalPath = $imgRaw;
                                                } else {
                                                    // Nếu là file nội bộ, xử lý dọn dẹp tên file
                                                    $imgFile = str_replace('images/', '', $imgRaw);
                                                    $finalPath = "../images/" . $imgFile;
                                                }
                                            ?>
                      <img src="<?php echo $finalPath; ?>" class="product-img me-3 shadow-sm"
                        onerror="this.src='https://placehold.co/80x80?text=Loi+Anh'">
                      <div>
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($item['name']); ?></div>
                        <small class="text-muted d-block text-truncate" style="max-width: 150px;">
                          <?php echo htmlspecialchars($item['description']); ?>
                        </small>
                      </div>
                    </div>
                  </td>
                  <td><span class="fw-medium"><?php echo formatPrice($item['price']); ?></span></td>
                  <td>
                    <div class="input-group input-group-sm qty-input border rounded-pill overflow-hidden">
                      <button class="btn btn-light border-0 qty-dec" data-id="<?php echo $item['id']; ?>"><i
                          class="fas fa-minus"></i></button>
                      <input type="text" class="form-control border-0 text-center qty fw-bold bg-white"
                        value="<?php echo $item['quantity']; ?>" readonly>
                      <button class="btn btn-light border-0 qty-inc" data-id="<?php echo $item['id']; ?>"><i
                          class="fas fa-plus"></i></button>
                    </div>
                  </td>
                  <td><span class="text-primary fw-bold"><?php echo formatPrice($item['subtotal']); ?></span></td>
                  <td class="text-center pe-3">
                    <button class="btn btn-link text-danger remove-cart-item p-0" data-id="<?php echo $item['id']; ?>">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="sticky-summary">
          <div class="card card-summary">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-4 border-bottom pb-2">Tóm tắt đơn hàng</h5>

              <div class="d-flex justify-content-between mb-3 text-secondary">
                <span>Tạm tính (<?php echo $total_items; ?> sản phẩm):</span>
                <span class="fw-bold text-dark"><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
              </div>

              <div class="d-flex justify-content-between mb-3 text-secondary">
                <span>Phí vận chuyển:</span>
                <span class="fw-bold text-success">30.000₫</span>
              </div>

              <hr class="my-4 opacity-25">

              <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="fw-bold fs-5">Tổng cộng:</span>
                <div class="text-end">
                  <span class="text-primary fw-bold fs-3 d-block">
                    <?php echo number_format($total + 30000, 0, ',', '.'); ?>₫
                  </span>
                  <small class="text-muted">(Đã bao gồm VAT)</small>
                </div>
              </div>

              <a href="checkout.php" class="btn btn-primary btn-lg w-100 py-3 rounded-pill fw-bold">
                Thanh toán ngay
              </a>

              <a href="products.php"
                class="btn btn-light btn-lg w-100 py-3 rounded-pill mt-3 border text-muted shadow-sm">
                <i class="fas fa-arrow-left me-2 small"></i>Quay lại mua sắm
              </a>

              <div class="mt-4 pt-3 border-top text-center">
                <small class="text-muted d-block mb-2">Hỗ trợ thanh toán:</small>
                <div class="d-flex justify-content-center gap-3 opacity-50 fs-4">
                  <i class="fab fa-cc-visa"></i>
                  <i class="fab fa-cc-mastercard"></i>
                  <i class="fas fa-money-bill-wave"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </main>

  <?php include 'footer.php'; ?>

  <script>
  /* Xử lý tăng giảm số lượng AJAX */
  function updateCart(id, qty) {
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
        if (data.success) location.reload();
      });
  }

  document.querySelectorAll('.qty-inc, .qty-dec').forEach(btn => {
    btn.onclick = function() {
      const input = this.parentNode.querySelector('.qty');
      let qty = parseInt(input.value);
      qty = this.classList.contains('qty-inc') ? qty + 1 : (qty > 1 ? qty - 1 : 1);
      updateCart(this.dataset.id, qty);
    }
  });

  document.querySelectorAll('.remove-cart-item').forEach(btn => {
    btn.onclick = function() {
      if (confirm('Bạn có muốn xóa sản phẩm này?')) {
        fetch('../api/cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              action: 'remove',
              id: this.dataset.id
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) location.reload();
          });
      }
    }
  });
  </script>
</body>

</html>