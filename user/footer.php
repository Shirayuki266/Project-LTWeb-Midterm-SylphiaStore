<?php // Modern Bootstrap Footer - Closes all user pages ?>
</main>

<!-- Bootstrap 5 Footer -->
<footer class="bg-dark text-light py-5 mt-5">
  <div class="container">
    <!-- Main Footer Content -->
    <div class="row row-cols-1 row-cols-lg-4 g-4 mb-4">
      <!-- Column 1: Brand -->
      <div class="col">
        <div>
          <img src="../images/logoshop.png" alt="Sylphia Shop" height="45" class="mb-3">
          <p class="mb-3">
            Sylphia Shop - Nơi hội tụ những sản phẩm công nghệ <strong>chất lượng cao</strong> với
            <strong>giá tốt nhất thị trường</strong> và dịch vụ <strong>chuẩn 5 sao</strong>.
          </p>
          <div class="d-flex gap-2">
            <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-2" aria-label="Facebook"><i
                class="fab fa-facebook-f"></i></a>
            <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-2" aria-label="Instagram"><i
                class="fab fa-instagram"></i></a>
            <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-2" aria-label="TikTok"><i
                class="fab fa-tiktok"></i></a>
            <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-2" aria-label="YouTube"><i
                class="fab fa-youtube"></i></a>
            <a href="#" class="btn btn-sm btn-outline-light rounded-circle p-2" aria-label="Zalo"><i
                class="fab fa-whatsapp"></i></a>
          </div>
        </div>
      </div>

      <!-- Column 2: Services -->
      <div class="col">
        <h5 class="footer-title mb-3">Dịch vụ</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="#" class="text-light text-decoration-none"><i class="fas fa-truck me-2"></i> Giao
              hàng toàn quốc</a></li>
          <li class="mb-2"><a href="#" class="text-light text-decoration-none"><i class="fas fa-undo me-2"></i> Đổi trả
              30 ngày</a></li>
          <li class="mb-2"><a href="#" class="text-light text-decoration-none"><i class="fas fa-shield-alt me-2"></i>
              Chính hãng 100%</a></li>
          <li class="mb-2"><a href="#" class="text-light text-decoration-none"><i class="fas fa-headset me-2"></i> Hỗ
              trợ 24/7</a></li>
          <li class="mb-2"><a href="#" class="text-light text-decoration-none"><i class="fas fa-credit-card me-2"></i>
              Thanh toán đa dạng</a></li>
        </ul>
      </div>

      <!-- Column 3: Quick Links -->
      <div class="col">
        <h5 class="footer-title mb-3">Liên kết nhanh</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="products.php" class="text-light text-decoration-none">Tất cả sản phẩm</a></li>
          <li class="mb-2"><a href="products.php?category=1" class="text-light text-decoration-none">Điện thoại</a></li>
          <li class="mb-2"><a href="products.php?category=2" class="text-light text-decoration-none">Laptop</a></li>
          <li class="mb-2"><a href="products.php?category=3" class="text-light text-decoration-none">Phụ kiện</a></li>
          <li class="mb-2"><a href="#" class="text-light text-decoration-none">Tin tức công nghệ</a></li>
        </ul>
      </div>

      <!-- Column 4: Contact & Newsletter -->
      <div class="col">
        <h5 class="footer-title mb-3">Liên hệ</h5>
        <div class="contact-info mb-4">
          <p class="mb-2"><i class="fas fa-phone-alt me-2"></i><strong>1900-xxx-xxx</strong></p>
          <p class="mb-2"><i class="fas fa-envelope me-2"></i><strong>support@sylphia.com</strong></p>
          <p><i class="fas fa-map-marker-alt me-2"></i><strong>123 Tech Street, TP.HCM</strong></p>
        </div>
        <!-- Newsletter -->
        <h6 class="mb-2">Nhận ưu đãi mới nhất</h6>
        <form class="newsletter-form">
          <div class="input-group">
            <input type="email" class="form-control" placeholder="Nhập email của bạn" required>
            <button class="btn btn-primary" type="submit">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bottom Section -->
    <hr class="my-4 opacity-25">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h6 class="mb-0">Phương thức thanh toán:</h6>
        <div class="d-flex gap-2 mt-2">
          <img src="../images/momo.png" alt="Momo" height="30">
          <img src="../images/visa.png" alt="Visa" height="30">
          <img src="../images/fb-logo.png" alt="Facebook Pay" height="30">
          <span class="badge bg-success">COD</span>
        </div>
      </div>
      <div class="col-md-9 text-md-end">
        <p class="mb-0">&copy; 2024 <strong>Sylphia Shop</strong>. Tất cả quyền được bảo lưu. |
          <a href="#" class="text-light text-decoration-none">Chính sách bảo mật & Điều khoản dịch vụ</a> |
        </p>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11">
</div>

<script>
function showBootstrapToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
  toast.setAttribute('role', 'alert');
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'} me-2"></i>
        ${message}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  `;
  document.querySelector('.toast-container').appendChild(toast);
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();
  toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

// Global addToCart with Bootstrap toast
window.addToCart = function(id, qty = 1) {
  const btn = event ? event.target.closest('button') : null;
  if (btn) {
    const original = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang thêm...';
    btn.disabled = true;
  }

  fetch('../api/cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'add',
        id,
        qty
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showBootstrapToast('✅ Đã thêm vào giỏ hàng!');
        // Update badges
        document.querySelectorAll('.badge').forEach(b => {
          if (b.closest('a[href="cart.php"]')) {
            const current = parseInt(b.textContent) || 0;
            b.textContent = current + qty;
          }
        });
      } else {
        showBootstrapToast('❌ Có lỗi xảy ra!');
      }
    })
    .catch(() => showBootstrapToast('❌ Lỗi kết nối!'))
    .finally(() => {
      if (btn) {
        btn.innerHTML = '<i class="fas fa-cart-plus"></i> Thêm';
        btn.disabled = false;
      }
    });
};

// Newsletter
document.querySelector('.newsletter-form')?.addEventListener('submit', e => {
  e.preventDefault();
  showBootstrapToast('📧 Đã đăng ký nhận tin!');
  e.target.querySelector('input').value = '';
});

// Smooth scroll
document.querySelectorAll('a[href^=\"#"]').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    const target = document.querySelector(a.getAttribute('href'));
    target?.scrollIntoView({
      behavior: 'smooth'
    });
  });
});
</script>
</body>

</html>