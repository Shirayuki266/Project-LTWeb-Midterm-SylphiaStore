let state = {
  cat:
    parseInt(new URLSearchParams(window.location.search).get("category")) || 0,
  page: 1,
  search: "",
  min: 0,
  max: 999999999,
  sort: "id_desc",
};

window.loadProducts = async function () {
  const grid = $("productsGrid");
  grid.innerHTML = `<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Đang tìm sản phẩm...</p></div>`;
  try {
    const min_p = isNaN(state.min) || state.min === "" ? 0 : state.min;
    const max_p = isNaN(state.max) || state.max === "" ? 999999999 : state.max;
    const query = new URLSearchParams({
      category: state.cat,
      page: state.page,
      search: state.search,
      min_price: min_p,
      max_price: max_p,
      sort: state.sort,
    }).toString();
    const res = await fetch(`../api/products.php?${query}`);
    if (!res.ok) throw new Error("Server Error");
    const result = await res.json();
    grid.innerHTML = "";
    window.updateSearchStatus();
    if (!result.products?.length) {
      grid.innerHTML = `<div class="col-12 text-center py-5 text-muted">Không tìm thấy sản phẩm phù hợp.</div>`;
      $("paginationNav")?.classList.add("d-none");
      return;
    }
    grid.innerHTML = result.products
      .map(
        (p) => `
      <div class="col">
        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
          <div class="p-3 bg-white d-flex align-items-center justify-content-center" style="height:180px">
            <img src="${p.image}" class="img-fluid" style="max-height:100%; object-fit:contain" onerror="this.src='../images/logoshop.png'">
          </div>
          <div class="card-body d-flex flex-column text-center pt-0">
            <small class="text-muted text-uppercase fw-bold" style="font-size:0.55rem">${p.category_name || "Sản phẩm"}</small>
            <h6 class="fw-bold text-truncate mb-2" style="font-size: 0.9rem;">${p.name}</h6>
            <div class="text-primary fw-bold mb-3">${formatPrice(p.price)}</div>
            <div class="mt-auto d-flex gap-2">
              <a href="product-detail.php?id=${p.id}" class="btn btn-outline-dark btn-sm flex-fill rounded-pill">Chi tiết</a>
              <button onclick="addToCart(${p.id})" class="btn btn-dark btn-sm rounded-pill px-3"><i class="fas fa-cart-plus"></i></button>
            </div>
          </div>
        </div>
      </div>`,
      )
      .join("");
    window.updatePagination(result.pagination);
  } catch (e) {
    console.error(e);
    grid.innerHTML = `<div class="col-12 text-center py-5 text-danger"><i class="fas fa-exclamation-triangle mb-2 fs-3"></i>Không thể kết nối.</div>`;
  }
};

window.loadCategories = async function () {
  const res = await fetch("../api/categories.php");
  const cats = await res.json();
  const list = $("categoriesList");
  let html = `<button class="list-group-item list-group-item-action ${state.cat == 0 ? "active" : ""}" onclick="filterCat(0,this)"><i class="fas fa-border-all me-2"></i>Tất cả</button>`;
  cats.forEach(
    (c) =>
      (html += `<button class="list-group-item list-group-item-action ${state.cat == c.id ? "active" : ""}" onclick="filterCat(${c.id},this)"><i class="fas fa-chevron-right me-2 small"></i>${c.name}</button>`),
  );
  list.innerHTML = html;
};

window.filterCat = function (id, btn) {
  state.cat = id;
  state.page = 1;
  document
    .querySelectorAll("#categoriesList .list-group-item")
    .forEach((b) => b.classList.remove("active"));
  btn.classList.add("active");
  loadProducts();
  window.history.pushState({}, "", `products.php?category=${id}`);
  window.scrollTo({ top: 0, behavior: "smooth" });
};

window.setPriceRange = function (min, max) {
  state.min = min;
  state.max = max;
  state.page = 1;
  $("minPriceInput").value = min > 0 ? min : "";
  $("maxPriceInput").value = max < 999999999 ? max : "";

  // Highlight nút vừa chọn
  document.querySelectorAll(".price-tag").forEach((btn) => {
    btn.classList.replace("btn-primary", "btn-outline-primary");
    // Nếu text của nút khớp với khoảng giá (ví dụ), bạn có thể add lại class active ở đây
  });

  loadProducts();
};

window.updateSearchStatus = function () {
  const statusDiv = $("searchStatus"),
    statusText = $("statusText");
  const hasSearch =
      state.search && typeof state.search.trim === "function"
        ? state.search.trim()
        : "",
    hasPrice = state.min > 0 || state.max < 999999999;
  if (hasSearch || hasPrice) {
    statusDiv.classList.remove("d-none");
    statusText.innerHTML = hasSearch
      ? `Kết quả cho: <span class="text-primary fw-bold">"${state.search}"</span>`
      : "Sản phẩm theo giá lọc";
  } else statusDiv.classList.add("d-none");
};

window.updatePagination = function (pag) {
  const nav = $("paginationNav"),
    list = $("paginationList");
  if (!pag || pag.pages <= 1) return nav.classList.add("d-none");
  nav.classList.remove("d-none");
  let html = `<li class="page-item ${state.page == 1 ? "disabled" : ""}">
    <a class="page-link rounded-pill border-0 shadow-sm me-2" onclick="goToPage(${state.page - 1})">Trước</a></li>`;
  for (let i = 1; i <= pag.pages; i++) {
    html += `<li class="page-item ${i == state.page ? "active" : ""}">
      <a class="page-link rounded-pill mx-1 border-0 shadow-sm" onclick="goToPage(${i})">${i}</a></li>`;
  }
  html += `<li class="page-item ${state.page == pag.pages ? "disabled" : ""}">
    <a class="page-link rounded-pill border-0 shadow-sm" onclick="goToPage(${state.page + 1})">Sau</a></li>`;
  list.innerHTML = html;
};

window.goToPage = function (p) {
  state.page = p;
  loadProducts();
  window.scrollTo({ top: 0, behavior: "smooth" });
};

// Products page init
if ($("productsGrid")) {
  document.addEventListener("DOMContentLoaded", function () {
    loadCategories();
    loadProducts();
    let timer;
    const bindInput = (id, key, delay = 0) => {
      const el = $(id);
      if (!el) return;
      el.oninput = (e) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          let val = e.target.value;

          if (key === "search") {
            // Nếu là tìm kiếm, lấy giá trị thật (kể cả rỗng)
            state.search = val;
          } else {
            // Nếu là giá, nếu rỗng thì mới gán giá trị mặc định
            if (val === "") {
              state[key] = key === "min" ? 0 : 999999999;
            } else {
              state[key] = parseFloat(val);
            }
          }

          state.page = 1;
          window.loadProducts();
        }, delay);
      };
    };
    bindInput("searchInput", "search", 500);
    bindInput("minPriceInput", "min", 300);
    bindInput("maxPriceInput", "max", 300);
    $("sortSelect").onchange = (e) => {
      state.sort = e.target.value;
      loadProducts();
    };
  });
}
