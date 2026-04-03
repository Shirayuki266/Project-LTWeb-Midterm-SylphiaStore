<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/auth.php';

$auth = new auth($conn);

// Kiểm tra đăng nhập admin
if (!$auth->isLoggedIn('admin')) {
    header("Location: login.php");
    exit;
}

// Lấy tên file hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Sylphia Shop</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
  body {
    font-family: 'Inter', sans-serif;
  }

  .sidebar {
    width: 260px;
    transition: all 0.3s;
    z-index: 1000;
  }

  .nav-link {
    color: rgba(255, 255, 255, 0.7);
    transition: 0.2s;
    margin-bottom: 5px;
  }

  .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
  }

  .nav-link.active {
    color: #fff;
    background: #0d6efd;
    font-weight: 600;
  }

  .nav-link i {
    width: 20px;
    text-align: center;
  }

  /* Giúp các icon thẳng hàng */
  .main-content {
    flex-grow: 1;
    min-height: 100vh;
    background-color: #f8f9fa;
  }
  </style>
</head>

<body>
  <div class="d-flex">
    <aside class="sidebar bg-dark text-white vh-100 p-3 shadow-lg flex-shrink-0 position-sticky top-0">

      <div class="sidebar-header p-3 pb-4 mb-4 border-bottom border-secondary">
        <h4 class="mb-0 fw-bold text-info">
          <i class="fas fa-user-shield me-2"></i>Sylphia Admin
        </h4>
      </div>

      <nav class="nav flex-column">
        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"
          href="dashboard.php">
          <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-QLDanhMuc.php') ? 'active' : ''; ?>"
          href="admin-QLDanhMuc.php">
          <i class="fas fa-list me-2"></i> Danh mục
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-QLSP.php') ? 'active' : ''; ?>"
          href="admin-QLSP.php">
          <i class="fas fa-box me-2"></i> Sản phẩm
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-QLDonHang.php') ? 'active' : ''; ?>"
          href="admin-QLDonHang.php">
          <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-QLKH.php') ? 'active' : ''; ?>"
          href="admin-QLKH.php">
          <i class="fas fa-users me-2"></i> Khách hàng
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-QLGia.php') ? 'active' : ''; ?>"
          href="admin-QLGia.php">
          <i class="fas fa-tags me-2"></i> Giá cả
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-QLPhieuNH.php') ? 'active' : ''; ?>"
          href="admin-QLPhieuNH.php">
          <i class="fas fa-file-import me-2"></i> Phiếu Nhập Hàng
        </a>

        <a class="nav-link rounded px-3 py-2 <?php echo ($current_page == 'admin-inventory.php') ? 'active' : ''; ?>"
          href="admin-inventory.php">
          <i class="fa-solid fa-chart-line me-2"></i> Thống kê Kho và Báo cáo
        </a>

      </nav>

      <div class="sidebar-footer mt-auto pt-4 border-top border-secondary">
        <div class="px-3 mb-3 small text-secondary">Đang đăng nhập:
          <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></div>
        <a href="admin-logout.php" class="btn btn-danger w-100 shadow-sm"
          onclick="return confirm('Bạn muốn đăng xuất?')">
          <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
        </a>
      </div>

    </aside>

    <main class="main-content p-0">
      <header class="navbar navbar-white sticky-top bg-white p-3 shadow-sm mb-4">
        <div class="container-fluid">
          <span class="navbar-brand mb-0 h1 fs-6 text-muted">Hệ thống quản trị /
            <span class="text-dark fw-bold"><?php echo str_replace(['admin-', '.php'], '', $current_page); ?></span>
          </span>

          <div class="ms-auto text-muted small">
            <i class="fas fa-user-shield me-1"></i>
            Đăng nhập: <strong
              class="text-dark"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
          </div>
        </div>
      </header>