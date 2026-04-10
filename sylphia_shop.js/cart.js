/**
 * sylphia_shop.js/cart.js - Quản lý giỏ hàng
 */

// 1. Cập nhật giao diện (UI) giỏ hàng khi người dùng tích chọn hoặc đổi số lượng
window.updateCartUI = function () {
  let subtotal = 0,
    count = 0;

  // Xử lý các dòng được chọn
  document.querySelectorAll(".item-checkbox:checked").forEach((cb) => {
    const row = cb.closest(".cart-item-row");
    if (!row) return;

    const price = parseFloat(row.dataset.price) || 0;
    const qtyInput = row.querySelector(".qty-val");
    const qty = parseInt(qtyInput ? qtyInput.value : 0);

    subtotal += price * qty;
    count++;
    row.classList.add("selected", "table-active");
  });

  // Xử lý các dòng không được chọn
  document.querySelectorAll(".item-checkbox:not(:checked)").forEach((cb) => {
    const row = cb.closest(".cart-item-row");
    if (row) row.classList.remove("selected", "table-active");
  });

  // Tính phí vận chuyển (Ví dụ: Dưới 500k phí 30k, trên miễn phí)
  const shipping = subtotal > 0 && subtotal < 500000 ? 30000 : 0;
  const total = subtotal + shipping;

  // Đổ dữ liệu ra HTML
  if ($("selected-count")) $("selected-count").innerText = count;
  if ($("summary-subtotal"))
    $("summary-subtotal").innerText = formatPrice(subtotal);
  if ($("summary-shipping")) {
    $("summary-shipping").innerText =
      shipping === 0 ? "Miễn phí" : formatPrice(shipping);
  }
  if ($("summary-total")) $("summary-total").innerText = formatPrice(total);
};

// 2. Cập nhật số lượng qua AJAX
window.ajaxUpdateCart = async function (id, qty) {
  const row = document.querySelector(`[data-id="${id}"]`);
  if (!row) return;

  const input = row.querySelector(".qty-val");
  const oldQty = parseInt(input.value);

  // Tạm thời cập nhật UI để tạo cảm giác nhanh (Optimistic UI)
  input.value = qty;
  updateCartUI();

  const data = await ajaxCart("update", { id, qty });

  if (data && data.success) {
    const badge = $("cart-count-badge");
    if (badge && data.totalItems !== undefined)
      badge.textContent = data.totalItems;
  } else {
    // Nếu lỗi (hoặc chưa đăng nhập), trả lại số lượng cũ
    input.value = oldQty;
    updateCartUI();
    if (data && data.message) showToast(data.message, "danger");
  }
};

// 3. Thêm vào giỏ hàng (Dùng ở trang danh sách/chi tiết sản phẩm)
window.addToCart = async function (productId) {
  const result = await ajaxCart("add", { id: productId, qty: 1 });

  if (result && result.success) {
    showToast("✅ Đã thêm vào giỏ hàng!", "success");
    const badge = $("cart-count-badge");
    if (badge && result.totalItems !== undefined)
      badge.innerText = result.totalItems;
  }
};

// 4. Chuyển sang trang thanh toán
window.goToCheckout = function () {
  const selectedIds = Array.from(
    document.querySelectorAll(".item-checkbox:checked"),
  ).map((cb) => cb.value);

  if (selectedIds.length === 0) {
    return showToast("Vui lòng chọn ít nhất 1 sản phẩm!", "danger");
  }

  window.location.href = `checkout.php?ids=${selectedIds.join(",")}`;
};

// 5. Khởi tạo các sự kiện trên trang Giỏ hàng
if (document.getElementById("cartTable")) {
  document.addEventListener("DOMContentLoaded", function () {
    // Sự kiện Check All
    const checkAll = $("checkAll");
    if (checkAll) {
      checkAll.addEventListener("change", function () {
        document.querySelectorAll(".item-checkbox").forEach((cb) => {
          cb.checked = this.checked;
        });
        updateCartUI();
      });
    }

    // Sự kiện từng Checkbox lẻ
    document.querySelectorAll(".item-checkbox").forEach((cb) => {
      cb.onchange = updateCartUI;
    });

    // Nút tăng/giảm số lượng
    document.querySelectorAll(".qty-inc, .qty-dec").forEach((btn) => {
      btn.onclick = function () {
        const input = this.parentNode.querySelector(".qty-val");
        let qty = parseInt(input.value);
        qty = this.classList.contains("qty-inc")
          ? qty + 1
          : Math.max(1, qty - 1);
        ajaxUpdateCart(this.dataset.id, qty);
      };
    });

    // Nút xóa sản phẩm
    document.querySelectorAll(".remove-item").forEach((btn) => {
      btn.onclick = async function () {
        const row = this.closest(".cart-item-row");
        if (confirm("Bỏ sản phẩm này khỏi giỏ hàng?")) {
          const data = await ajaxCart("remove", { id: this.dataset.id });
          if (data && data.success) {
            row.remove();
            updateCartUI();
            const badge = $("cart-count-badge");
            if (badge) badge.textContent = data.totalItems || 0;
            showToast("Đã xóa sản phẩm", "success");
          }
        }
      };
    });

    // Chạy lần đầu để tính toán tiền
    updateCartUI();
  });
}
