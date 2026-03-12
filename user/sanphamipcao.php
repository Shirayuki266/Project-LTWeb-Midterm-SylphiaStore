<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sản Phẩm | Sylphia Shop</title>

  <!-- Liên kết CSS -->
  <link rel="icon" type="image/png" href="../images/logo-web-removebg-preview.png" />
  <link rel="stylesheet" href="../css/header.css" />
  <link rel="stylesheet" href="../css/footer.css" />
  <link rel="stylesheet" href="../css/sanpham.css" />
  <link rel="stylesheet" href="../css/timkiem.css" />
  <link rel="stylesheet" href="../css/user-trangchu.css" />
</head>

<body>
  <!-- Header -->
  <header>
    <div class="logo">
      <a href="trangchu-dangnhap.php" class="logo">
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

  <!-- Danh sách sản phẩm -->
  <main class="product-page">
    <h1>Tìm Kiếm Theo Kết Quả "Điện Thoại"</h1>

    <!-- Form tìm kiếm nâng cao -->
    <section class="search-section">
      <form class="search-form" method="get" action="#">
        <!-- Chọn phân loại -->
        <div class="fake-select">
          <div class="selected">Chọn phân loại ▾</div>
          <div class="options">
            <a href="sanphamip.php">Điện thoại</a>
            <a href="sanphamlaptop.php">Laptop</a>
            <a href="sanphamphukien.php">Phụ kiện</a>
          </div>
        </div>

        <!-- Chọn khoảng giá -->
        <div class="price-range">
          <label for="price-select">Khoảng giá:</label>
          <select name="price" id="price-select" class="price-select">
            <option value="">Chọn khoảng giá</option>
            <option value="0-1000000">Dưới 1 triệu</option>
            <option value="1000000-5000000">1 triệu - 5 triệu</option>
            <option value="5000000-10000000">5 triệu - 10 triệu</option>
            <option value="10000000-20000000">10 triệu - 20 triệu</option>
            <option value="20000000-50000000">20 triệu - 50 triệu</option>
            <option value="50000000-">Trên 50 triệu</option>
          </select>
        </div>

        <label for="sort-select">Sắp xếp:</label>
        <a href="../user/sanphamip.php" class="price-select">Mặc định</a>
        <a href="../user/sanphamipthap.php" class="price-select">Giá tăng dần</a>
        <a href="../user/sanphamipcao.php" class="price-select">Giá giảm dần</a>
        <!-- Nút tìm kiếm -->
        <button type="" class="btn-search">Tìm kiếm</button>
      </form>
    </section>

    <!-- Lưới sản phẩm -->
    <div class="product-grid">
      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 17 Pro Max 1TB" />
        <h3>iPhone 17 Pro Max 1TB</h3>
        <p class="price">46.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 17 Pro Max 512GB" />
        <h3>iPhone 17 Pro Max 512GB</h3>
        <p class="price">42.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 17 Pro 256GB" />
        <h3>iPhone 17 Pro 256GB</h3>
        <p class="price">34.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 16 Pro Max 512GB" />
        <h3>iPhone 16 Pro Max 512GB</h3>
        <p class="price">33.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 17 Plus 256GB" />
        <h3>iPhone 17 Plus 256GB</h3>
        <p class="price">31.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 16 Pro 256GB" />
        <h3>iPhone 16 Pro 256GB</h3>
        <p class="price">29.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 15 Pro Max 256GB (Mới)" />
        <h3>iPhone 15 Pro Max 256GB (Mới)</h3>
        <p class="price">28.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 17 128GB" />
        <h3>iPhone 17 128GB</h3>
        <p class="price">26.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 16 Plus 128GB" />
        <h3>iPhone 16 Plus 128GB</h3>
        <p class="price">24.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 15 Plus 128GB (Mới)" />
        <h3>iPhone 15 Plus 128GB</h3>
        <p class="price">24.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 16 128GB" />
        <h3>iPhone 16 128GB</h3>
        <p class="price">23.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 14 Plus 256GB (Mới)" />
        <h3>iPhone 14 Plus 256GB</h3>
        <p class="price">22.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 15 Pro 128GB (Cũ 99%)" />
        <h3>iPhone 15 Pro 128GB (Cũ)</h3>
        <p class="price">22.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 14 Pro Max 128GB (Cũ)" />
        <h3>iPhone 14 Pro Max 128GB (Cũ)</h3>
        <p class="price">21.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 15 128GB (Cũ)" />
        <h3>iPhone 15 128GB (Likenew)</h3>
        <p class="price">19.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 14 Pro 128GB (Cũ)" />
        <h3>iPhone 14 Pro 128GB (Cũ)</h3>
        <p class="price">19.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 14 128GB (Mới)" />
        <h3>iPhone 14 128GB</h3>
        <p class="price">17.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 13 Pro Max 256GB (Cũ)" />
        <h3>iPhone 13 Pro Max 256GB (Cũ)</h3>
        <p class="price">17.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 13 128GB (Mới)" />
        <h3>iPhone 13 128GB (Mới)</h3>
        <p class="price">15.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 13 Mini 128GB (Cũ)" />
        <h3>iPhone 13 Mini 128GB (Cũ)</h3>
        <p class="price">13.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone SE 4 64GB" />
        <h3>iPhone SE 4 64GB</h3>
        <p class="price">12.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 12 Pro Max 128GB (Cũ)" />
        <h3>iPhone 12 Pro Max 128GB (Cũ)</h3>
        <p class="price">11.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 12 64GB (Cũ)" />
        <h3>iPhone 12 64GB (Cũ)</h3>
        <p class="price">9.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone 11 64GB (Cũ)" />
        <h3>iPhone 11 64GB (Cũ)</h3>
        <p class="price">6.990.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/iphone-17-pro-max.jpg" alt="Iphone XR 64GB (Cũ)" />
        <h3>iPhone XR 64GB (Cũ)</h3>
        <p class="price">4.500.000đ</p>
        <a href="chitietsanpham-dangnhap.php" class="buy-btn">Xem chi tiết</a>
      </div>
    </div>
    <div class="pagination">
      <a href="#">Prev</a>
      <a href="#" class="current-page">1</a>
      <a href="#">2</a>
      <a href="#">3</a>
      <a href="#">Next</a>
    </div>
  </main>

  <!-- Footer Section -->
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
          <img src="../images/visa.png" alt="Visa" />
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