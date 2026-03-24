<?php
// 1. Khởi động session để kiểm tra trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn(); 

/* LẤY DANH MỤC SẢN PHẨM */
$result = $conn->query("SELECT MIN(id) as id, name FROM categories GROUP BY name ORDER BY name");
$cats = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $page_title ?? 'Sylphia Shop'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
  /* 1. Thiết lập mặc định cho tất cả link điều hướng */
  .nav-link {
    font-weight: 500 !important;
    transition: all 0.3s ease;
    color: #333 !important;
    /* Màu xám đậm mặc định */
    opacity: 0.85;
  }

  .nav-link:hover {
    color: #0066cc !important;
    opacity: 1;
  }

  /* 2. Class kích hoạt khi ở đúng trang: CƯỠNG ÉP màu xanh và độ đậm */
  .active-nav {
    color: #0066cc !important;
    /* Màu xanh thương hiệu */
    font-weight: 700 !important;
    /* Tăng độ đậm lên mức tối đa */
    opacity: 1 !important;
  }

  /* 3. Đảm bảo icon bên trong cũng đổi màu và đậm lên */
  .active-nav i {
    color: #0066cc !important;
    /* Thủ thuật làm icon FontAwesome trông dày hơn để cân bằng với chữ */
    -webkit-text-stroke: 0.5px #0066cc;
  }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="../images/logoshop.png" height="45" class="me-2">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
        <ul class="navbar-nav align-items-center">

          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'index') ? 'active-nav' : ''; ?>"
              href="index.php">
              <i class="fa-solid fa-house"></i> Home
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'products') ? 'active-nav' : ''; ?>"
              href="products.php">
              <i class="fa-solid fa-shop"></i> Shop
            </a>
          </li>

          <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'cart') ? 'active-nav' : ''; ?>"
              href="cart.php">
              <i class="fas fa-shopping-cart"></i> Giỏ hàng
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'profile') ? 'active-nav' : ''; ?>"
              href="profile.php">
              <i class="fa-solid fa-user"></i> Hồ sơ
            </a>
          </li>

          <li class="nav-item ms-lg-3">
            <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">
              <i class="fa-solid fa-power-off"></i> Đăng xuất
            </a>
          </li>
          <?php else: ?>
          <li class="nav-item ms-lg-3">
            <a class="btn btn-primary btn-sm rounded-pill px-4" href="login.php">
              <i class="fa-solid fa-user-lock"></i> Đăng nhập
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <section class="bg-white border-top border-bottom py-2">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-center gap-4">
        <?php foreach ($cats as $cat): ?>
        <a href="products.php?category=<?php echo $cat['id']; ?>"
          class="text-dark text-decoration-none small fw-semibold">
          <?php echo htmlspecialchars($cat['name']); ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>