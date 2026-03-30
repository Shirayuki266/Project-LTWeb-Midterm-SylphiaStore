// sylphia_shop.js/checkout-forms.js - Checkout and forms
// Global $ , fetchProvinces, fetchWards from common.js

// Checkout address
window.toggleAddressNew = function () {
  const section = $("new_address_section"),
    isNew = $("#addr_new").checked;
  section.classList.toggle("d-none", !isNew);
  if (isNew) initProvinces();
};

window.initProvinces = async function () {
  const citySelect = $("city");
  if (citySelect.options.length > 1) return;
  const provinces = await fetchProvinces();
  provinces.forEach((p) => citySelect.options.add(new Option(p.name, p.code)));
};

window.loadWards = async function (provinceCode) {
  const wardSelect = $("ward");
  wardSelect.innerHTML = '<option value="">Đang tải...</option>';
  if (!provinceCode) {
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    return;
  }
  const wards = await fetchWards(provinceCode);
  wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
  wards.forEach((w) => wardSelect.options.add(new Option(w.name, w.code)));
};

window.togglePaymentInfo = function () {
  const bankInfo = $("bank_info"),
    isTransfer = $("#pay_bank").checked;
  bankInfo.classList.toggle("d-none", !isTransfer);
  if (isTransfer)
    bankInfo.scrollIntoView({ behavior: "smooth", block: "nearest" });
};

// Register form validation
window.initRegisterValidation = function () {
  const validateField = (id) => {
    // [Same validation logic from register.php - extracted]
    const el = $(id),
      val = el.value.trim(),
      errEl = $("err-" + id);
    let err = "";
    if (val === "") err = "* Bắt buộc";
    // ... add other validations
    if (errEl) {
      errEl.innerText = err;
      errEl.style.display = err ? "block" : "none";
      el.classList.toggle("input-error", !!err);
    }
    return !err;
  };
  [
    "username",
    "email",
    "phone",
    "address",
    "password",
    "confirm_password",
    "city",
    "ward",
  ].forEach((id) => {
    $(id).oninput = () => validateField(id);
  });
  $("registerForm").onsubmit = (e) => {
    let valid = true;
    [
      "username",
      "email",
      "phone",
      "address",
      "password",
      "confirm_password",
      "city",
      "ward",
    ].forEach((id) => {
      if (!validateField(id)) valid = false;
    });
    if (!valid) {
      e.preventDefault();
      alert("Sửa lỗi màu đỏ!");
    }
  };
};

// Page-specific init
document.addEventListener("DOMContentLoaded", function () {
  if ($("new_address_section")) {
    $("#city").onchange = () => loadWards($("#city").value);
    togglePaymentInfo();
  }
  if ($("registerForm")) initRegisterValidation();
});
