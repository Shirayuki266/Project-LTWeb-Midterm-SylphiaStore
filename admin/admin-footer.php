</main>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index: 1099"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * 1. HÀM HIỂN THỊ THÔNG BÁO (TOAST)
 * @param {string} message - Nội dung thông báo
 * @param {string} type - Loại thông báo: 'success', 'danger', 'warning', 'info'
 */
function showAdminToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');

  // Định nghĩa màu sắc và icon cho từng loại
  const config = {
    success: {
      bg: 'bg-success text-white',
      icon: 'fa-check-circle'
    },
    danger: {
      bg: 'bg-danger text-white',
      icon: 'fa-times-circle'
    },
    warning: {
      bg: 'bg-warning text-dark',
      icon: 'fa-exclamation-triangle'
    },
    info: {
      bg: 'bg-info text-white',
      icon: 'fa-info-circle'
    }
  };

  const theme = config[type] || config.success;

  const toastHtml = `
        <div class="toast align-items-center ${theme.bg} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <i class="fas ${theme.icon} me-2"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      `;

  container.insertAdjacentHTML('beforeend', toastHtml);
  const toastElement = container.lastElementChild;
  const toast = new bootstrap.Toast(toastElement, {
    delay: 4000
  }); // Tự ẩn sau 4 giây
  toast.show();

  // Xóa bỏ HTML của toast sau khi ẩn để tránh làm nặng trang
  toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}

/**
 * 2. TỰ ĐỘNG BẮT THÔNG BÁO TỪ URL (PHP REDIRECT)
 * Ví dụ: index.php?msg=success
 */
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);

  if (urlParams.has('msg')) {
    const msgType = urlParams.get('msg');
    const detailMessage = urlParams.get('detail');

    if (msgType === 'success') {
      showAdminToast(detailMessage || 'Thực hiện thao tác thành công!');
    } else if (msgType === 'error') {
      showAdminToast(detailMessage || 'Đã có lỗi xảy ra, vui lòng thử lại.', 'danger');
    } else if (msgType === 'warning') {
      showAdminToast(detailMessage || 'Lưu ý: Dữ liệu có thể chưa hoàn thiện.', 'warning');
    }

    // Làm sạch URL (xóa tham số msg) để tránh hiện lại khi F5 trang
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  // 3. XỬ LÝ ACTIVE MENU (Nếu header chưa làm)
  const currentPath = window.location.pathname.split('/').pop();
  document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    if (link.getAttribute('href') === currentPath) {
      link.classList.add('active');
    }
  });
});
</script>
</body>

</html>