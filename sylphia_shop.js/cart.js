/**
 * sylphia_shop.js/cart.js - Quản lý giỏ hàng tối ưu
 */

// Dùng `$` từ common.js, không khai báo lại để tránh lỗi redeclare

window.updateCartUI = function () {
  let subtotal = 0;
  let selectedCount = 0;
  let totalQuantity = 0;

  const allRows = document.querySelectorAll(".cart-item-row");

  allRows.forEach((row) => {
    const checkbox = row.querySelector(".item-checkbox");
    const price = parseFloat(row.dataset.price) || 0;
    const qtyInput = row.querySelector(".qty-val");
    const qty = parseInt(qtyInput ? qtyInput.value : 1);
    totalQuantity += qty;

    if (checkbox && checkbox.checked) {
      subtotal += price * qty;
      selectedCount++;
      row.classList.add("selected", "table-active");
    } else {
      row.classList.remove("selected", "table-active");
    }

    const rowSubtotal = row.querySelector(".subtotal-item");
    if (rowSubtotal) {
      rowSubtotal.innerText = (price * qty).toLocaleString("vi-VN") + "₫";
    }
  });

  // 2. Tính toán phí vận chuyển
  const shipping = subtotal > 0 && subtotal < 500000 ? 30000 : 0;
  const total = subtotal + shipping;

  // 3. Đổ dữ liệu ra giao diện thanh toán
  if ($("selected-count")) $("selected-count").innerText = selectedCount;
  if ($("summary-subtotal"))
    $("summary-subtotal").innerText = subtotal.toLocaleString("vi-VN") + "₫";
  if ($("summary-shipping")) {
    $("summary-shipping").innerText =
      subtotal === 0
        ? "0₫"
        : shipping === 0
          ? "Miễn phí"
          : shipping.toLocaleString("vi-VN") + "₫";
  }
  if ($("summary-total"))
    $("summary-total").innerText = total.toLocaleString("vi-VN") + "₫";

  // 4. ĐỒNG BỘ BADGE - Điểm quan trọng nhất
  // Cập nhật tất cả các nơi hiển thị số lượng giỏ hàng trên Header/Badge
  const badge = $("cart-count-badge");
  const topBadge = $("top-cart-count");
  const headerBadge = document.querySelector(".header-cart-badge"); // Giả sử bạn có class này trên Header

  [badge, topBadge, headerBadge].forEach((el) => {
    if (el) {
      if (el.id === "top-cart-count")
        el.textContent = `Tổng ${totalQuantity} món`;
      else el.textContent = totalQuantity;
    }
  });

  // 5. Kiểm tra giỏ hàng trống để làm mới giao diện PHP
  if (actualItemsCount === 0 && $("cartTable")) {
    // Nếu xóa hết, ép trang tải lại để PHP render view "Giỏ hàng trống"
    location.reload();
  }
};

window.ajaxUpdateCart = async function (id, qty) {
  const row = document.querySelector(`.cart-item-row[data-id="${id}"]`);
  if (!row) return;

  const input = row.querySelector(".qty-val");
  const oldQty = input.value;

  input.value = qty;
  updateCartUI();

  try {
    const data = await ajaxCart("update", { id, qty });
    if (!data || !data.success) {
      input.value = oldQty;
      updateCartUI();
      if (data?.message) showToast(data.message, "danger");
    }
  } catch (err) {
    input.value = oldQty;
    updateCartUI();
  }
};

window.initCartEvents = function () {
  const checkAll = $("checkAll");
  if (checkAll) {
    checkAll.onclick = function () {
      document
        .querySelectorAll(".item-checkbox")
        .forEach((cb) => (cb.checked = this.checked));
      updateCartUI();
    };
  }

  // Sử dụng Delegation để tránh mất sự kiện khi thay đổi DOM (nếu có)
  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("item-checkbox")) {
      updateCartUI();
    }
  });

  document.querySelectorAll(".qty-inc, .qty-dec").forEach((btn) => {
    btn.onclick = function () {
      const input = this.parentNode.querySelector(".qty-val");
      let qty = parseInt(input.value);
      const row = this.closest(".cart-item-row");
      const stock = parseInt(row.dataset.stock) || 0;

      if (this.classList.contains("qty-inc")) {
        if (qty >= stock) {
          showToast(
            `Không thể thêm! Chỉ còn ${stock} sản phẩm trong kho.`,
            "danger",
          );
          return;
        }
        qty += 1;
      } else {
        qty = Math.max(1, qty - 1);
      }

      ajaxUpdateCart(this.dataset.id, qty);
    };
  });

  document.querySelectorAll(".remove-item").forEach((btn) => {
    btn.onclick = async function () {
      const row = this.closest(".cart-item-row");
      if (confirm("Xóa sản phẩm này khỏi giỏ hàng?")) {
        const data = await ajaxCart("remove", { id: this.dataset.id });
        if (data && data.success) {
          row.remove(); // Xóa khỏi màn hình ngay lập tức
          updateCartUI(); // Gọi lại hàm để đếm lại số dòng và cập nhật Badge
          showToast("Đã xóa sản phẩm", "success");
        }
      }
    };
  });
};

document.addEventListener("DOMContentLoaded", () => {
  if ($("cartTable") || document.querySelector(".cart-item-row")) {
    initCartEvents();
    updateCartUI();
  }
});
