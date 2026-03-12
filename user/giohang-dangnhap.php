<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Giỏ Hàng - Sylphia Shop</title>
  <link rel="stylesheet" href="../css/cart.css" />
  <link rel="stylesheet" href="../css/header.css" />
  <link rel="stylesheet" href="../css/footer.css" />
  <link rel="icon" type="image/png" href="../images/logo-web-removebg-preview.png" />
</head>

<body>
  <!-- HEADER -->
  <header>
    <div class="logo">
      <a href="trangchu-dangnhap.php">
        <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
        Sylphia Shop
      </a>
    </div>
    <div class="icons">
      <form action="sanphamip.php" method="get">
        <span class="search-icon">
          <!-- SVG kính lúp -->
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </span>

        <input type="text" name="keyword" placeholder="Tìm kiếm..." />

        <span class="filter-icon">
          <!-- SVG phễu -->
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3h18L13 14v7H11v-7L3 3z" />
          </svg>
        </span>
      </form>
    </div>

    <nav>
      <a href="trangchu-dangnhap.php">Trang Chủ</a>
      <a href="sanpham-dangnhap.php">Sản Phẩm</a>
      <a href="#lienhe">Liên Hệ</a>
      <a href="giohang-dangnhap.php">Giỏ Hàng</a>
      <a href="trangcanhan.php">Thông Tin Cá Nhân</a>
      <a href="trangchu.php">Đăng Xuất</a>
    </nav>
  </header>
  <div class="page-wrapper">
    <!-- MAIN GIỎ HÀNG -->
    <main class="cart-container">
      <h1>🛒 Giỏ Hàng Của Bạn</h1>

      <div class="cart-table-wrapper">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Sản phẩm</th>
              <th>Giá</th>
              <th>Số lượng</th>
              <th>Tổng</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="product-cell">
                <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 17" class="product-img" />
                <span>Iphone 17 Pro Max</span>
              </td>
              <td>37.250.000 VNĐ</td>
              <td class="quantity-cell">
                <div class="quantity-wrapper">
                  <button type="button" class="qty-btn">-</button>
                  <input type="number" min="1" value="1" class="quantity-input" />
                  <button type="button" class="qty-btn">+</button>
                </div>
              </td>
              <td>37.250.000 VNĐ</td>
              <td><button class="remove-btn">Xóa</button></td>
            </tr>

            <tr>
              <td class="product-cell">
                <img src="../images/laptop-Asus.png" alt="Laptop Asus" class="product-img" />
                <span>Laptop Asus</span>
              </td>
              <td>22.890.000 VNĐ</td>
              <td class="quantity-cell">
                <div class="quantity-wrapper">
                  <button type="button" class="qty-btn">-</button>
                  <input type="number" min="1" value="3" class="quantity-input" />
                  <button type="button" class="qty-btn">+</button>
                </div>
              </td>
              <td>68.670.000 VNĐ</td>
              <td><button class="remove-btn">Xóa</button></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TỔNG THANH TOÁN -->
      <div class="cart-summary">
        <p>
          <strong>Tổng cộng:</strong>
          <span class="total-price">60.140.000đ</span>
        </p>
        <a href="sanpham-dangnhap.php" class="checkout-btn">Mua Sắm Tiếp</a>
        <a href="thanhtoan.php" class="checkout-btn">Thanh Toán</a>
      </div>
    </main>
  </div>

  <!-- FOOTER -->
  <footer class="footer" id="lienhe">
    <div class="container">
      <!-- Cột 1 -->
      <div class="footer-col">
        <h3>SYLPHIASHOP</h3>
        <p class="hotline">HOTLINE: 0917 997 997</p>
        <p class="store-address">
          Store 1: 01 Quang Trung, P.1, Q.Gò Vấp, TP.HCM - 0913.846535
        </p>
        <p class="store-address">
          Store 2: 02 Lê Hồng Phong, Q.10 TP.HCM - 0913.846535
        </p>
        <p class="store-address">
          Store 3: 03 Trần Quang Khải, Q.1 TP.HCM - 0913.846535
        </p>
        <p class="store-address">
          Store 4: 04 Võ Văn Ngân, P.Bình Thọ, Thủ Đức - 0913.846535
        </p>
        <p class="store-address">
          Store 5: 05 Nguyễn Văn Rập, Trảng Bàng, Tây Ninh - 0913.846535
        </p>
      </div>

      <!-- Cột 2 -->
      <div class="footer-col">
        <h3>QUY ĐỊNH & CHÍNH SÁCH</h3>
        <ul>
          <li><a href="#">Quy định hình thức thanh toán</a></li>
          <li><a href="#">Chính sách vận chuyển hàng</a></li>
          <li><a href="#">Chính sách bảo hành</a></li>
          <li><a href="#">Chính sách đổi trả</a></li>
          <li><a href="#">Chính sách kiểm hàng</a></li>
          <li><a href="#">Chính sách bảo mật</a></li>
        </ul>
      </div>

      <!-- Cột 3 -->
      <div class="footer-col">
        <h3>HÌNH THỨC THANH TOÁN</h3>
        <div class="payment-methods">
          <img src="../images/visa.png" alt="Visa" class="payment-icon" />
        </div>
        <div class="momo">
          <img src="../images/momo.png" alt="MOMO" />
        </div>
      </div>

      <!-- Cột 4 -->
      <div class="footer-col">
        <h3>HỆ THỐNG FANPAGE</h3>
        <ul>
          <li><a href="#">SYLPHIASHOP GÒ VẤP</a></li>
          <li><a href="#">SYLPHIASHOP QUẬN 10</a></li>
          <li><a href="#">SYLPHIASHOP QUẬN 1</a></li>
          <li><a href="#">SYLPHIASHOP THỦ ĐỨC</a></li>
          <li><a href="#">SYLPHIASHOP TÂY NINH</a></li>
          <li><a href="#">SYLPHIASHOP TIỀN GIANG</a></li>
          <li><a href="#">SYLPHIASHOP LONG AN</a></li>
        </ul>
      </div>
    </div>

    <!-- Dòng bản quyền -->
    <div class="footer-bottom">
      <p>© 2025 SylphiaShop. All rights reserved.</p>
      <p>Email: support@sylphiashop.vn | Hotline: 0917 997 997</p>
    </div>
  </footer>
</body>

</html>