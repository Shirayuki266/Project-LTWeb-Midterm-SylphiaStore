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
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Sylphia Shop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>
  <div class="d-flex min-vh-100 bg-light">

    <!-- Sidebar -->
    <aside class="bg-dark text-white vh-100 p-3 shadow-lg flex-shrink-0" style="width:260px;">

      <div class="sidebar-header p-3 pb-4 mb-4 border-bottom">
        <h4 class="mb-0 fw-bold">
          <i class="fas fa-store me-2"></i>Sylphia Admin
        </h4>
      </div>

      <nav class="nav flex-column">

        <a class="nav-link text-white px-3 py-2 mb-2 rounded" href="dashboard.php">
          <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>

        <a class="nav-link text-white px-3 py-2 mb-2 rounded" href="admin-QLSP.php">
          <i class="fas fa-box me-2"></i> Sản phẩm
        </a>

        <a class="nav-link text-white px-3 py-2 mb-2 rounded" href="admin-QLDonHang.php">
          <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
        </a>

        <a class="nav-link text-white px-3 py-2 mb-2 rounded" href="admin-QLKho.php">
          <i class="fas fa-warehouse me-2"></i> Kho hàng
        </a>

        <a class="nav-link text-white px-3 py-2 mb-2 rounded" href="admin-QLKH.php">
          <i class="fas fa-users me-2"></i> Khách hàng
        </a>

        <a class="nav-link text-white px-3 py-2 mb-2 rounded" href="admin-QLGia.php">
          <i class="fas fa-tags me-2"></i> Giá cả
        </a>

      </nav>

      <div class="sidebar-footer mt-auto p-3">
        <a href="admin-logout.php" class="btn btn-outline-light w-100">
          <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
        </a>
      </div>

    </aside>

    <!-- Main content -->
    <main class="flex-grow-1 p-4"></main>