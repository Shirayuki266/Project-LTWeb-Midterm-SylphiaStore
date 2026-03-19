<?php
// 1. Phải khởi động session để kiểm tra login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php'; // Đảm bảo nạp Class Auth

$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn(); // Kiểm tra trạng thái đăng nhập

/* LẤY DANH MỤC */
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
  .nav-link {
    font-weight: 500;
    transition: 0.3s;
  }

  .nav-link:hover {
    color: #0066cc !important;
  }

  .active-nav {
    color: #0066cc !important;
    font-weight: bold;
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
            <a class="nav-link <?php echo ($current_page == 'index') ? 'active-nav' : ''; ?>" href="index.php">
              <i class="fa-solid fa-house"></i> Home
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'products') ? 'active-nav' : ''; ?>" href="products.php">
              <i class="fa-solid fa-shop"></i> Shop
            </a>
          </li>

          <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link" href="cart.php">
              <i class="fas fa-shopping-cart"></i> Giỏ hàng
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="profile.php">
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