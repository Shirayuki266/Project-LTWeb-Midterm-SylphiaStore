<?php
// 1. Khởi động session để kiểm tra trạng thái đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn(); 
$user = $isLoggedIn ? $auth->getCurrentUser() : null;

/* LẤY DANH MỤC SẢN PHẨM ĐỂ HIỂN THỊ THANH MENU PHỤ */
$result = $conn->query("SELECT id, name FROM categories ORDER BY id ASC");
$cats = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title ?? 'Sylphia Shop'; ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
  :root {
    --sylphia-blue: #0066cc;
  }

  /* Định dạng menu chính */
  .nav-link {
    font-weight: 600 !important;
    color: #444 !important;
    transition: all 0.2s ease;
    padding: 0.5rem 1rem !important;
  }

  .nav-link:hover {
    color: var(--sylphia-blue) !important;
    transform: translateY(-1px);
  }

  /* Hiệu ứng trang đang xem (Active) */
  .active-nav {
    color: var(--sylphia-blue) !important;
    font-weight: 800 !important;
    position: relative;
  }

  /* Gạch chân nhỏ dưới mục đang chọn trên Desktop */
  @media (min-width: 992px) {
    .active-nav::after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 1rem;
      right: 1rem;
      height: 3px;
      background: var(--sylphia-blue);
      border-radius: 10px;
    }
  }

  /* Thanh danh mục nhanh bên dưới */
  .category-bar {
    background: #fff;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
  }

  .cat-link {
    color: #666;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: 0.2s;
  }

  .cat-link:hover {
    color: var(--sylphia-blue);
  }

  /* Bo tròn nút bấm */
  .btn-pill {
    border-radius: 50px !important;
    font-weight: 600;
  }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-2">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="../images/logoshop.png" height="42" alt="Sylphia Logo"
          onerror="this.src='https://via.placeholder.com/150x42?text=Sylphia+Shop'">
      </a>

      <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
        <ul class="navbar-nav align-items-center">

          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'index') ? 'active-nav' : ''; ?>"
              href="index.php">
              <i class="fas fa-home me-1"></i> Home
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'products') ? 'active-nav' : ''; ?>"
              href="products.php">
              <i class="fas fa-store me-1"></i> Shop
            </a>
          </li>

          <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'cart') ? 'active-nav' : ''; ?>"
              href="cart.php">
              <i class="fas fa-shopping-cart me-1"></i> Giỏ hàng
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php echo (isset($current_page) && $current_page == 'profile') ? 'active-nav' : ''; ?>"
              href="profile.php">
              <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($user['username']); ?>
            </a>
          </li>

          <li class="nav-item ms-lg-2">
            <a class="btn btn-outline-danger btn-sm btn-pill px-3" href="logout.php">
              <i class="fas fa-sign-out-alt"></i> Out
            </a>
          </li>
          <?php else: ?>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-primary btn-sm btn-pill px-4 shadow-sm" href="login.php">
              <i class="fas fa-user-lock me-1"></i> Đăng nhập
            </a>
          </li>
          <?php endif; ?>

        </ul>
      </div>
    </div>
  </nav>

  <div class="category-bar py-2 shadow-sm mb-4">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-center gap-4">
        <?php foreach ($cats as $cat): ?>
        <a href="products.php?category=<?php echo $cat['id']; ?>" class="cat-link text-uppercase">
          <?php echo htmlspecialchars($cat['name']); ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>