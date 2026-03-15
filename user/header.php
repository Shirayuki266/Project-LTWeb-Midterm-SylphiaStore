<?php
require_once '../api/db.php';

/* LẤY DANH MỤC */
$result = $conn->query("
SELECT MIN(id) as id, name
FROM categories
GROUP BY name
ORDER BY name
");

$cats = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php echo $page_title ?? 'Sylphia Shop'; ?></title>

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FONT AWESOME -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

  <!-- HEADER -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">

    <div class="container">

      <!-- LOGO -->
      <a class="navbar-brand d-flex align-items-center" href="index.php">

        <img src="../images/logoshop.png" height="45" class="me-2">

      </a>

      <!-- MOBILE BUTTON -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">

        <span class="navbar-toggler-icon"></span>

      </button>

      <!-- MENU -->
      <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">

        <ul class="navbar-nav">

          <li class="nav-item">
            <a class="nav-link" href="index.php">
              <i class="fa-solid fa-house"></i> Home
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="products.php">
              <i class="fa-solid fa-shop"></i> Shop
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="cart.php">
              <i class="fas fa-shopping-cart"></i> Cart
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="profile.php">
              <i class="fa-solid fa-circle-info"></i> Profile
            </a>
          </li>


          <li class="nav-item">
            <a class="nav-link" href="login.php">
              <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
            </a>
          </li>

        </ul>

      </div>

    </div>

  </nav>


  <!-- THANH DANH MỤC -->
  <section class="bg-white border-top border-bottom py-2">

    <div class="container">

      <div class="d-flex flex-wrap justify-content-center gap-4">

        <?php foreach ($cats as $cat): ?>

        <a href="products.php?category=<?php echo $cat['id']; ?>" class="text-dark text-decoration-none fw-semibold">

          <?php echo htmlspecialchars($cat['name']); ?>

        </a>

        <?php endforeach; ?>

      </div>

    </div>

  </section>