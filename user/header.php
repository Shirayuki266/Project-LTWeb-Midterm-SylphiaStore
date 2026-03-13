<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sylphia Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/sanpham.css">
  <link rel="icon" type="image/png" href="../images/logo-web-removebg-preview.png">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container">
      <a class="navbar-brand" href="../user/trangchu.php">
        <img src="../images/logo-web-removebg-preview.png" alt="Logo" height="40">
        Sylphia Shop
      </a>
      <form class="d-flex mx-auto" style="max-width: 500px;" action="sanpham.php" method="get">
        <input class="form-control me-2" type="search" name="search" placeholder="Tìm sản phẩm..."
          value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button class="btn btn-light" type="submit"><i class="fas fa-search"></i></button>
      </form>
      <div class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user'])): ?>
        <a class="nav-link" href="giohang.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
        <a class="nav-link" href="trangcanhan.php"><?php echo $_SESSION['user']; ?></a>
        <?php else: ?>
        <a class="nav-link" href="dangnhap.php">Đăng nhập</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>