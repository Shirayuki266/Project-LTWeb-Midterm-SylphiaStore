// sylphia_shop.js/cart.js - Cart management functions (Global compatible, no import)

// Global utils from common.js: $, formatPrice, ajaxCart, showToast

window.updateCartUI = function () {
  let subtotal = 0,
    count = 0;
  document.querySelectorAll(".item-checkbox:checked").forEach((cb) => {
    const row = cb.closest(".cart-item-row");
    const price = parseFloat(row.dataset.price);
    const qty = parseInt(row.querySelector(".qty-val").value);
    subtotal += price * qty;
    count++;
    row.classList.add("selected");
  });
  document.querySelectorAll(".item-checkbox:not(:checked)").forEach((cb) => {
    cb.closest(".cart-item-row").classList.remove("selected");
  });
  const shipping = subtotal > 0 && subtotal < 500000 ? 30000 : 0;

  const total = subtotal + shipping;
  document.getElementById("selected-count").innerText = count;
  document.getElementById("summary-subtotal").innerText = formatPrice(subtotal);

  document.getElementById("summary-shipping").innerText =
    shipping === 0 ? "Miễn phí" : formatPrice(shipping);

  document.getElementById("summary-total").innerText = formatPrice(total);
};

window.ajaxUpdateCart = async function (id, qty) {
  const row = document.querySelector(`[data-id="\${id}"]`);
  const input = row.querySelector(".qty-val");
  const oldQty = parseInt(input.value);
  input.value = qty;
  updateCartUI();
  try {
    const data = await ajaxCart("update", { id, qty });
    if (data.success) {
      const badge = document.getElementById("cart-count-badge");
      if (badge && data.totalItems !== undefined)
        badge.textContent = data.totalItems;
      return data;
    } else {
      input.value = oldQty;
      updateCartUI();
      showToast(data.message || "Cập nhật thất bại", "danger");
    }
  } catch (e) {
    input.value = oldQty;
    updateCartUI();
    showToast("Lỗi kết nối", "danger");
  }
};

window.goToCheckout = function () {
  const selectedIds = Array.from(
    document.querySelectorAll(".item-checkbox:checked"),
  ).map((cb) => cb.value);
  if (selectedIds.length === 0)
    return showToast("Vui lòng chọn ít nhất 1 sản phẩm!", "danger");

  window.location.href = `checkout.php?ids=${selectedIds.join(",")}`;
};

// Cart page init
if (document.getElementById("cartTable")) {
  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("checkAll").addEventListener("change", function () {
      document
        .querySelectorAll(".item-checkbox")
        .forEach((cb) => (cb.checked = this.checked));
      updateCartUI();
    });
    document
      .querySelectorAll(".item-checkbox")
      .forEach((cb) => (cb.onchange = updateCartUI));
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
    document.querySelectorAll(".remove-item").forEach((btn) => {
      btn.onclick = function () {
        if (confirm("Bỏ sản phẩm khỏi giỏ?")) {
          ajaxCart("remove", { id: this.dataset.id }).then((data) => {
            if (data.success) {
              row.remove();
              updateCartUI();
              const badge = document.getElementById("cart-count-badge");
              if (badge) badge.textContent = data.totalItems || 0;
            }
          });
        }
      };
    });
    updateCartUI();
  });
}
window.addToCart = async function (productId) {
  // 1. Gọi hàm ajaxCart đã có logic check 401/đăng nhập
  // Hàm này sẽ tự showToast màu đỏ nếu chưa đăng nhập
  const result = await window.ajaxCart("add", { id: productId, qty: 1 });

  // 2. Chỉ hiện thông báo xanh nếu thực sự thành công
  if (result && result.success) {
    window.showToast("✅ Đã thêm vào giỏ hàng!", "success");

    // Cập nhật số lượng trên badge (nếu có)
    const badge = document.getElementById("cart-count-badge");
    if (badge) badge.innerText = result.totalItems;
  }
};
