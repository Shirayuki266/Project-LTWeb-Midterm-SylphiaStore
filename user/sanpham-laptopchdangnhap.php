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
      <a href="trangchu.php" class="logo">
        <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
        Sylphia Shop
      </a>
    </div>

    <div class="icons">
      <form action="sanphamip-chuadangnhap.php" method="get">
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
      <a href="trangchu.php">Trang Chủ</a>
      <a href="sanpham.php">Sản Phẩm</a>
      <a href="#lienhe">Liên Hệ</a>
      <a href="dangnhap.php">Giỏ Hàng</a>
      <a href="dangnhap.php">Đăng nhập</a>
      <a href="dangky.php">Đăng Ký</a>
    </nav>
  </header>

  <!-- Danh sách sản phẩm -->
  <main class="product-page">
    <h1>Tìm Kiếm Theo Kết Quả "Laptop"</h1>

    <!-- Form tìm kiếm nâng cao -->
    <section class="search-section">
      <form class="search-form" method="get" action="#">
        <!-- Chọn phân loại -->
        <div class="fake-select">
          <div class="selected">Chọn phân loại ▾</div>
          <div class="options">
            <a href="sanphamip-chuadangnhap.php">Điện thoại</a>
            <a href="sanpham-laptopchdangnhap.php">Laptop</a>
            <a href="sanphamphukien-chuadangnhap.php">Phụ kiện</a>
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
        <a href="../user/sanphamip-chuadangnhap.php" class="price-select">Mặc định</a>
        <a href="../user/sanphamipthap-chuadangnhap.php" class="price-select">Giá tăng dần</a>
        <a href="../user/sanphamipcao-chuadangnhap.php" class="price-select">Giá giảm dần</a>
        <!-- Nút tìm kiếm -->
        <button type="" class="btn-search">Tìm kiếm</button>
      </form>
    </section>
    <!-- Lưới sản phẩm -->
    <div class="product-grid">
      <div class="product-card">
        <img src="../images/laptop-Asus.png" alt="Laptop Asus" />
        <h3>Laptop Asus</h3>
        <p class="price">22.890.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/laptop-asus-tuf-gaming.webp" alt="Laptop ASUS TUF Gaming F16 FX607VJ-RL034W" />
        <h3>Laptop ASUS TUF Gaming F16 FX607VJ-RL034W</h3>
        <p class="price">21.990.000₫</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi
          tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/laptopnitro.webp" alt="Laptop Gaming Acer Nitro V ANV15-51-57B2" />
        <h3>Laptop Gaming Acer Nitro V ANV15-51-57B2</h3>
        <p class="price">23.290.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>


      <div class="product-card">
        <img src="../images/laptopacernitro.webp" alt="Laptop Acer Gaming Nitro Lite 16 NL16-71G-71UJ" />
        <h3>Laptop Acer Gaming Nitro Lite 16 NL16-71G-71UJ</h3>
        <p class="price">22.890.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/laptopmis.webp" alt="Laptop MSI Katana 15 B13UDXK-2270VN V2" />
        <h3>Laptop MSI Katana 15 B13UDXK-2270VN V2</h3>
        <p class="price">20.490.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/laptoplenovo.webp" alt="Laptop Lenovo LOQ 15IRX10 83JE00PEVN" />
        <h3>Laptop Lenovo LOQ 15IRX10 83JE00PEVN</h3>
        <p class="price">31.490.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>
      <div class="product-card">
        <img src="../images/macbook.webp" alt="Apple MacBook Air M2 2024 8CPU 8GPU 16GB 256GB" />
        <h3>Apple MacBook Air M2 2024 8CPU 8GPU 16GB 256GB</h3>
        <p class="price">19.790.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>
      <div class="product-card">
        <img src="../images/macbook1.webp" alt="MacBook Air M4 13 inch 2025 10CPU 10GPU 16GB 512GB" />
        <h3>MacBook Air M4 13 inch 2025 10CPU 10GPU 16GB 512GB </h3>
        <p class="price">30.790.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>
      <div class="product-card">
        <img src="../images/asusrog.webp" alt="Laptop ASUS ROG Strix G16 G615JMR-S5155W" />
        <h3>Laptop ASUS ROG Strix G16 G615JMR-S5155W</h3>
        <p class="price">41.990.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>

      <div class="product-card">
        <img src="../images/asusrogzephyrus.webp" alt="Laptop ASUS ROG Zephyrus G14 GA403WR-QS156WS" />
        <h3>Laptop ASUS ROG Zephyrus G14 GA403WR-QS156WS</h3>
        <p class="price">65.990.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>
      <div class="product-card">
        <img src="../images/dell.webp" alt="Laptop Dell XPS 13 9350 71058714" />
        <h3>Laptop Dell XPS 13 9350 71058714</h3>
        <p class="price">57.990.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
      </div>
      <div class="product-card">
        <img src="../images/dellinspiron.webp" alt="Laptop Dell Inspiron 14 5441 N4O10441W1" />
        <h3>Laptop Dell Inspiron 14 5441 N4O10441W1</h3>
        <p class="price">28.990.000đ</p>
        <a href="chitietsanpham.php" class="buy-btn">Xem chi tiết</a>
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