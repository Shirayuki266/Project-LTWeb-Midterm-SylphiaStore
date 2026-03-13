<?php
// Legacy page; redirect to modern register page
header('Location: register.php');
exit;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng ký | Sylphia Shop</title>
  <link rel="stylesheet" href="../css/register.css" />
  <link rel="stylesheet" href="../css/login.css" />
  <link rel="stylesheet" href="../css/header.css" />
  <link rel="icon" type="image/png" href="../images/logo-web-removebg-preview.png" />
</head>

<body>
  <!-- Header giữ phong cách trang chủ -->
  <header>
    <div class="logo">
      <a href="trangchu.php">
        <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
        Sylphia Shop
      </a>
    </div>
    <nav>
      <a href="trangchu.php">Trang Chủ</a>
      <a href="#">Liên Hệ</a>
      <a href="dangnhap.php">Đăng nhập</a>
    </nav>
  </header>

  <!-- Form đăng ký -->
  <main class="register-container">
    <form class="register-form" action="#" method="post">
      <h1>Đăng Ký Tài Khoản</h1>

      <label for="username">Tên đăng ký</label>
      <input type="text" id="username" name="username" required />

      <label for="number-phone">Số điện thoại</label>
      <input type="text" id="number-phone" name="number-phone" required />

      <label for="password">Mật khẩu</label>
      <input type="password" id="password" name="password" required />

      <label for="password2">Xác nhận mật khẩu</label>
      <input type="password" id="password2" name="password2" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <div class="social-login">
        <input type="image" id="googleBtn" src="../images/gg-logo.webp" alt="Google" title="Đăng nhập Google" />
        <input type="image" id="facebookBtn" src="../images/fb-logo.png" alt="Facebook" title="Đăng nhập Facebook" />
      </div>

      <?php if ($success_msg): ?>
      <p style="color: green; text-align: center;"><?php echo $success_msg; ?></p>
      <?php endif; ?>
      <?php if ($error_msg): ?>
      <p style="color: red; text-align: center;"><?php echo $error_msg; ?></p>
      <?php endif; ?>

      <div class="button-group">
        <a href="dangnhap.php" class="btn secondary">Đăng nhập</a>
        <button type="submit" class="btn primary">Đăng ký</button>
      </div>
    </form>
  </main>

  <!-- Footer -->
  <footer>
    <p>© 2025 SylphiaShop. All rights reserved.</p>
    <p>Email: support@sylphiashop.vn | Hotline: 0917 997 997</p>
  </footer>
  <!-- POPUP -->
  <div class="popup-overlay" id="popup">
    <div class="popup-box">
      <h2>🎉 Đăng nhập thành công!</h2>
      <p id="popupMsg">Chào mừng bạn quay lại!</p>
      <button id="okBtn">OK</button>
    </div>
  </div>

  <!-- JS -->
  <!-- JS -->
  <script>
  const popup = document.getElementById("popup");
  const popupMsg = document.getElementById("popupMsg");
  const okBtn = document.getElementById("okBtn");
  const googleBtn = document.getElementById("googleBtn");
  const facebookBtn = document.getElementById("facebookBtn");

  function showPopup(message) {
    popupMsg.textContent = message;
    popup.classList.add("show");
  }

  okBtn.addEventListener("click", () => {
    popup.classList.remove("show");
    setTimeout(() => {
      window.location.href = "../user/trangchu-dangnhap.php";
    }, 300);
  });

  googleBtn.addEventListener("click", (e) => {
    e.preventDefault();
    showPopup("Đăng ký nhanh bằng Google thành công 🌐");
  });

  facebookBtn.addEventListener("click", (e) => {
    e.preventDefault();
    showPopup("Đăng ký nhanh bằng Facebook thành công 👍");
  });
  </script>
</body>

</html>