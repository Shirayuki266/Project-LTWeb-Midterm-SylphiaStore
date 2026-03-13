<?php
// Legacy page; redirect to modern login page
header('Location: login.php');
exit;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập | Sylphia Shop</title>
  <link rel="stylesheet" href="../css/login.css" />
  <link rel="stylesheet" href="../css/header.css" />
  <link rel="icon" type="image/png" href="../images/logo-web-removebg-preview.png" />
</head>

<body>
  <!-- HEADER -->
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
      <a href="dangky.php">Đăng ký</a>
    </nav>
  </header>

  <!-- FORM -->
  <main class="login-container">
    <form class="login-form" id="loginForm" action="#" method="post">
      <h1>Đăng Nhập</h1>

      <?php if ($error_msg): ?>
      <p style="color: red; text-align: center;"><?php echo $error_msg; ?></p>
      <?php endif; ?>

      <label for="username">Tên đăng nhập</label>
      <input type="text" id="username" name="username" required />

      <label for="password">Mật khẩu</label>
      <input type="password" id="password" name="password" required />

      <a href="#" class="forgot-link"> Quên mật khẩu? </a>

      <button type="submit" name="login" class="btn primary full">Đăng Nhập</button>

      <div class="social-login">
        <input type="image" id="googleBtn" src="../images/gg-logo.webp" alt="Google" title="Đăng nhập Google" />
        <input type="image" id="facebookBtn" src="../images/fb-logo.png" alt="Facebook" title="Đăng nhập Facebook" />
      </div>

      <div class="register-section">
        <span>Bạn chưa có tài khoản?</span>
        <a href="dangky.php" class="btn secondary small">
          Đăng ký ngay
        </a>
      </div>
    </form>
  </main>

  <!-- FOOTER -->
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
  <script>
  const form = document.getElementById("loginForm");
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
  </script>
</body>

</html>