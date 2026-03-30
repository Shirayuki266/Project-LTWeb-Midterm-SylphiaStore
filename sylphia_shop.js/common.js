// 1. Phím tắt truy cập DOM
const $ = (id) => document.getElementById(id);

// 2. Định dạng tiền tệ
const formatPrice = (p) =>
  new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(p);

// 3. Hàm AJAX Cart dùng chung
async function ajaxCart(action, data = {}) {
  try {
    const params = new URLSearchParams();
    params.append("action", action);
    for (const key in data) {
      params.append(key, data[key]);
    }

    const res = await fetch("../api/cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params.toString(),
    });

    if (!res.ok) throw new Error("Mạng không ổn định");
    const result = await res.json();

    if (result.code === 401) {
      showToast(result.message || "Vui lòng đăng nhập!", "danger");
      setTimeout(() => {
        window.location.href =
          "login.php?redirect=" + encodeURIComponent(window.location.href);
      }, 1500);
      return { success: false };
    }
    return result;
  } catch (error) {
    console.error("AJAX Error:", error);
    showToast("Lỗi kết nối hệ thống!", "danger");
    return { success: false };
  }
}

// 4. Hàm hiển thị thông báo Toast
function showToast(message, type = "success") {
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container position-fixed bottom-0 end-0 p-3";
    container.style.zIndex = "1100";
    document.body.appendChild(container);
  }

  const toastId = "toast-" + Date.now();
  const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-white bg-${type === "success" ? "success" : "danger"} border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`;

  container.insertAdjacentHTML("beforeend", toastHtml);
  const toastEl = document.getElementById(toastId);
  const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
  bsToast.show();
  toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
}

window.$ = $;
window.formatPrice = formatPrice;
window.ajaxCart = ajaxCart;
window.showToast = showToast;
