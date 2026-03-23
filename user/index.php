<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nạp các file cấu hình và tiện ích
require_once '../api/db.php';
require_once '../includes/functions.php'; // Đường dẫn đúng để ra khỏi thư mục user/

// 2. KIỂM TRA TRẠNG THÁI TÀI KHOẢN (Tính năng Khóa User)
// Hàm này được định nghĩa trong ../includes/functions.php
if (!check_user_active($conn)) {
    header("Location: login.php?error=account_locked");
    exit();
}

/* 3. LẤY DỮ LIỆU SẢN PHẨM (Chỉ lấy sản phẩm đang bật status = 1) */
// Hot Products
$hotProducts = $conn->query("SELECT id, name, price, image FROM products WHERE status = 1 ORDER BY id DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Flash Sale
$flashSaleProducts = $conn->query("SELECT id, name, price, image FROM products WHERE status = 1 ORDER BY RAND() LIMIT 4")->fetch_all(MYSQLI_ASSOC);

// Categories (Lưu ý: Chỉ dùng WHERE status = 1 nếu bảng categories có cột này)
$catQuery = "SELECT * FROM categories LIMIT 8";
$cats = $conn->query($catQuery)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Trang chủ';
include 'header.php';
?>

<section class="py-5" style="background-color: #f5f5f7;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 px-lg-5">
        <span class="badge rounded-pill bg-white text-dark border px-3 py-2 mb-3 shadow-sm">NEW GENERATION</span>
        <h1 class="fw-bold display-3 mb-4" style="color: #1d1d1f; letter-spacing: -1px;">
          The future is <br><span class="text-primary">In your hands.</span>
        </h1>
        <p class="lead text-secondary mb-5">
          Trải nghiệm kỹ thuật chính xác kết hợp với thiết kế tinh tế. <br>
          Dòng Sylphia Titanium định nghĩa lại công nghệ di động.
        </p>
        <div class="d-flex gap-3">
          <a href="products.php" class="btn btn-dark btn-lg rounded-pill px-5 py-3 shadow text-decoration-none">Khám phá
            ngay</a>
          <a href="#"
            class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 border-secondary-subtle text-decoration-none">Xem
            Video</a>
        </div>
      </div>
      <div class="col-lg-6 text-center mt-5 mt-lg-0">
        <img src="../images/loggo_intro.png" class="img-fluid rounded-5 shadow-lg"
          style="max-height: 550px; object-fit: cover;"
          onerror="this.src='https://placehold.co/600x400?text=Sylphia+Shop'">
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <div class="mb-5">
      <h2 class="fw-bold display-5">Flash Sale</h2>
      <p class="text-muted">Tìm kiếm người bạn đồng hành cho phong cách sống số của bạn.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-7">
        <div class="card border-0 rounded-5 shadow-lg overflow-hidden h-100"
          style="background-color: #ffffff; min-height: 450px;">
          <div class="p-5 h-100 d-flex flex-column">
            <h3 class="fw-bold display-6 mb-1">iPhone</h3>
            <p class="text-secondary fs-5">Unmatched Performance.</p>
            <div class="text-center mt-auto">
              <img src="../images/iphone-17-pro-max.jpg" class="img-fluid" style="width: 70%; object-fit: contain;"
                onerror="this.src='https://placehold.co/300x400?text=iPhone'">
            </div>
            <a href="products.php" class="text-decoration-none fw-bold text-dark mt-4 d-inline-block">Explore All →</a>
          </div>
        </div>
      </div>

      <div class="col-md-5">
        <div class="row g-4 h-100">
          <div class="col-12">
            <div class="card border-0 rounded-5 shadow-sm overflow-hidden"
              style="background-color: #f2f2f2; height: 210px;">
              <div class="d-flex align-items-center h-100">
                <div class="p-4 flex-grow-1">
                  <h3 class="fw-bold mb-1">MacBook</h3>
                  <p class="text-secondary small mb-3">Work from anywhere with power.</p>
                  <a href="products.php" class="text-success text-decoration-none fw-bold">View Range</a>
                </div>
                <div class="h-100" style="width: 40%;">
                  <img src="../images/Macbook.jpg" class="h-100 w-100" style="object-fit: cover;">
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card border-0 rounded-5 shadow-sm p-4 text-center h-100" style="background-color: #d1e8e2;">
              <h5 class="fw-bold">Watch</h5>
              <img src="../images/Apple Watch.jpg" class="img-fluid mt-2"
                style="max-height: 100px; object-fit: contain;">
            </div>
          </div>
          <div class="col-6">
            <div class="card border-0 rounded-5 shadow-sm p-4 text-center bg-dark text-white h-100">
              <h5 class="fw-bold">Phụ Kiện</h5>
              <img src="../images/Tai Nghe.jpg" class="img-fluid mt-2" style="max-height: 100px; object-fit: contain;">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" style="background-color: #fafafa;">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="fw-bold mb-0">⭐ Sản phẩm bán chạy</h2>
        <p class="text-muted mb-0">Những lựa chọn được yêu thích nhất tuần này.</p>
      </div>
      <a href="products.php" class="btn btn-outline-primary rounded-pill">Xem tất cả</a>
    </div>

    <div class="row g-4">
      <?php if(count($hotProducts) > 0): ?>
      <?php foreach($hotProducts as $product): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <?php include '../includes/product-card.php'; ?>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="col-12 text-center py-5">
        <p class="text-muted">Hiện chưa có sản phẩm nổi bật nào.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>