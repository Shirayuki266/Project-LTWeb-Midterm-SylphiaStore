/**
 * common.js - Các hàm dùng chung cho hệ thống
 */

// 1. Phím tắt truy cập nhanh DOM
const $ = (id) => document.getElementById(id);

// 2. Định dạng tiền tệ VND
const formatPrice = (p) =>
  new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(p);

// 3. Hàm AJAX Cart chính
async function ajaxCart(action, data = {}) {
  try {
    const params = new URLSearchParams();
    params.append("action", action);
    for (const key in data) {
      params.append(key, data[key]);
    }

    // Gửi yêu cầu tới API
    const res = await fetch("../api/cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params.toString(),
    });

    // Xử lý lỗi 401: Chưa đăng nhập
    if (res.status === 401) {
      showToast("Vui lòng đăng nhập để thực hiện!", "danger");
      setTimeout(() => {
        const currentUrl = encodeURIComponent(window.location.href);
        window.location.href = `login.php?redirect=${currentUrl}`;
      }, 1500);
      return { success: false, code: 401 };
    }

    // Xử lý các lỗi HTTP khác (ví dụ: 404, 500)
    if (!res.ok) {
      throw new Error(`Lỗi phản hồi từ máy chủ: ${res.status}`);
    }

    // Nếu ok, phân giải JSON trả về
    const result = await res.json();
    return result;

  } catch (error) {
    console.error("AJAX Error:", error);
    showToast("Lỗi kết nối: " + error.message, "danger");
    return { success: false };
  }
}

// 4. Hàm hiển thị Toast (Thông báo nổi)
function showToast(message, type = "success") {
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container position-fixed bottom-0 end-0 p-3";
    container.style.zIndex = "1100";
    document.body.appendChild(container);
  }

  const toastId = `toast-${Date.now()}`;
  const bgColor = type === "success" ? "bg-success" : "bg-danger";
  
  const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`;

  container.insertAdjacentHTML("beforeend", toastHtml);
  const toastEl = document.getElementById(toastId);
  
  if (typeof bootstrap !== 'undefined') {
    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
    bsToast.show();
    toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
  } else {
    // Fallback nếu chưa load được Bootstrap JS
    console.warn("Bootstrap JS chưa được tải!");
    alert(message);
  }
}

// Gắn vào window để gọi được từ file JS khác
window.$ = $;
window.formatPrice = formatPrice;
window.ajaxCart = ajaxCart;
window.showToast = showToast;