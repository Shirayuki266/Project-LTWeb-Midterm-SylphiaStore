<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nạp các file cấu hình
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

// 2. Kiểm tra đăng nhập
$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$page_title = 'Sản phẩm - Sylphia Shop';
$current_page = 'products'; // Active màu xanh trên Header

include 'header.php';
?>

<main class="flex-fill bg-light" style="min-height: 100vh;">
  <div class="py-5">
    <div class="container-fluid px-lg-5">
      <div class="row g-4 align-items-start">

        <aside class="col-lg-3 d-none d-lg-block">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white fw-bold border-0 pt-3 pb-2">
              <i class="fas fa-th-large me-2 text-primary"></i>Danh mục sản phẩm
            </div>
            <div class="list-group list-group-flush p-2" id="categoriesList">
            </div>
          </div>

          <div class="mt-3 p-3 bg-white rounded-4 shadow-sm border-start border-primary border-4">
            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.6rem;">Hỗ trợ khách
              hàng</small>
            <strong class="text-dark" style="font-size: 0.9rem;">1900.xxxx.xx</strong>
          </div>
        </aside>

        <div class="col-lg-9">
          <div class="row mb-4 g-3">
            <div class="col-md-5">
              <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                <span class="input-group-text border-0 bg-white ps-3"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="searchInput" class="form-control border-0" placeholder="Tìm tên sản phẩm...">
              </div>
            </div>
            <div class="col-md-4">
              <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                <span class="input-group-text border-0 bg-white ps-3">Giá</span>
                <input type="number" id="minPriceInput" class="form-control border-0 px-2" placeholder="Từ">
                <span class="input-group-text border-0 bg-white">-</span>
                <input type="number" id="maxPriceInput" class="form-control border-0 px-2" placeholder="Đến">
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select shadow-sm border rounded-pill ps-3" id="sortSelect">
                <option value="id_desc">Mới nhất</option>
                <option value="price_asc">Giá thấp đến cao</option>
                <option value="price_desc">Giá cao đến thấp</option>
                <option value="name_asc">Tên A-Z</option>
              </select>
            </div>
          </div>

          <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
            <small class="text-secondary fw-bold text-uppercase me-2" style="font-size: 0.7rem;">Lọc nhanh:</small>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(0, 10000000)">Dưới 10tr</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(10000000, 20000000)">10tr - 20tr</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(20000000, 30000000)">20tr - 30tr</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(30000000, 999999999)">Trên 30tr</button>
            <button class="btn btn-link btn-sm text-decoration-none text-muted"
              onclick="setPriceRange(0, 999999999)">Xoá lọc</button>
          </div>

          <div id="searchStatus"
            class="mb-4 d-none p-3 bg-white rounded-4 shadow-sm border-start border-primary border-4">
            <div class="d-flex align-items-center">
              <i class="fas fa-filter text-primary me-3 fs-5"></i>
              <span id="statusText" class="text-dark fw-medium"></span>
            </div>
          </div>

          <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4" id="productsGrid">
          </div>

          <nav id="paginationNav" class="mt-5 d-none">
            <ul class="pagination justify-content-center" id="paginationList"></ul>
          </nav>
        </div>

      </div>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>
// --- 1. QUẢN LÝ TRẠNG THÁI (STATE) ---
const urlParams = new URLSearchParams(window.location.search);
let state = {
  cat: parseInt(urlParams.get('category')) || 0,
  page: 1,
  search: '',
  min: 0,
  max: 999999999,
  sort: 'id_desc'
};

const $ = id => document.getElementById(id);
const formatPrice = p => new Intl.NumberFormat('vi-VN', {
  style: 'currency',
  currency: 'VND'
}).format(p);

// --- 2. HÀM CORE: LOAD SẢN PHẨM ---
async function loadProducts() {
  const grid = $('productsGrid');
  grid.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Đang tìm sản phẩm...</p>
        </div>`;

  try {
    // CHUẨN HÓA GIÁ TRỊ TRƯỚC KHI GỬI (Sửa lỗi xóa trắng ô input)
    const min_p = (state.min === '' || isNaN(state.min)) ? 0 : state.min;
    const max_p = (state.max === '' || isNaN(state.max)) ? 999999999 : state.max;

    const query = new URLSearchParams({
      category: state.cat,
      page: state.page,
      search: state.search,
      min_price: min_p,
      max_price: max_p,
      sort: state.sort
    }).toString();

    const res = await fetch(`../api/products.php?${query}`);

    // Kiểm tra nếu Server phản hồi lỗi (ví dụ lỗi 500)
    if (!res.ok) throw new Error("Server Error");

    const result = await res.json();

    grid.innerHTML = '';
    updateSearchStatus();

    if (!result.products || result.products.length === 0) {
      grid.innerHTML = `<div class="col-12 text-center py-5 text-muted">Không tìm thấy sản phẩm phù hợp.</div>`;
      $('paginationNav').classList.add('d-none');
      return;
    }

    grid.innerHTML = result.products.map(p => `
            <div class="col">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden product-card-hover">
                    <div class="p-3 bg-white d-flex align-items-center justify-content-center" style="height:180px">
                        <img src="${p.image}" class="img-fluid" style="max-height:100%; object-fit:contain" 
                             onerror="this.src='../images/logoshop.png'">
                    </div>
                    <div class="card-body d-flex flex-column text-center pt-0">
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.55rem">${p.category_name || 'Sản phẩm'}</small>
                        <h6 class="fw-bold text-truncate mb-2" style="font-size: 0.9rem;">${p.name}</h6>
                        <div class="text-primary fw-bold mb-3">${formatPrice(p.price)}</div>
                        <div class="mt-auto d-flex gap-2">
                            <a href="product-detail.php?id=${p.id}" class="btn btn-outline-dark btn-sm flex-fill rounded-pill">Chi tiết</a>
                            <button onclick="addToCart(${p.id})" class="btn btn-dark btn-sm rounded-pill px-3">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`).join('');

    updatePagination(result.pagination);
  } catch (e) {
    console.error(e);
    grid.innerHTML = `<div class="col-12 text-center py-5 text-danger">
            <i class="fas fa-exclamation-triangle mb-2 fs-3"></i><br>
            Không thể kết nối đến máy chủ. Vui lòng thử lại sau.
        </div>`;
  }
}

// --- 3. DANH MỤC & LỌC ---
async function loadCategories() {
  const res = await fetch('../api/categories.php');
  const cats = await res.json();
  const list = $('categoriesList');

  let html = `<button class="list-group-item list-group-item-action ${state.cat == 0 ? 'active' : ''}" 
                onclick="filterCat(0,this)"><i class="fas fa-border-all me-2"></i>Tất cả</button>`;

  cats.forEach(c => {
    html += `<button class="list-group-item list-group-item-action ${state.cat == c.id ? 'active' : ''}" 
                 onclick="filterCat(${c.id},this)"><i class="fas fa-chevron-right me-2 small"></i>${c.name}</button>`;
  });
  list.innerHTML = html;
}

function filterCat(id, btn) {
  state.cat = id;
  state.page = 1;
  document.querySelectorAll('#categoriesList .list-group-item').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadProducts();
  window.history.pushState({}, '', `products.php?category=${id}`);
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

function setPriceRange(min, max) {
  state.min = min;
  state.max = max;
  state.page = 1;
  $('minPriceInput').value = min > 0 ? min : '';
  $('maxPriceInput').value = max < 999999999 ? max : '';

  document.querySelectorAll('.price-tag').forEach(btn => btn.classList.replace('btn-primary', 'btn-outline-primary'));
  if (event && event.target.classList.contains('price-tag')) {
    event.target.classList.replace('btn-outline-primary', 'btn-primary');
  }
  loadProducts();
}

function updateSearchStatus() {
  const statusDiv = $('searchStatus');
  const statusText = $('statusText');
  const hasSearch = state.search && state.search.trim() !== "";
  const hasPrice = state.min > 0 || (state.max > 0 && state.max < 999999999);

  if (hasSearch || hasPrice) {
    statusDiv.classList.remove('d-none');
    let msg = hasSearch ? `Kết quả cho: <span class="text-primary fw-bold">"${state.search}"</span>` :
      `Sản phẩm theo giá lọc`;
    statusText.innerHTML = msg;
  } else {
    statusDiv.classList.add('d-none');
  }
}

// --- 4. PHÂN TRANG ---
function updatePagination(pag) {
  const nav = $('paginationNav');
  if (!pag || pag.pages <= 1) return nav.classList.add('d-none');
  nav.classList.remove('d-none');

  let html = `<li class="page-item ${state.page == 1 ? 'disabled' : ''}">
        <a class="page-link rounded-pill border-0 shadow-sm me-2" href="javascript:void(0)" onclick="goToPage(${state.page - 1})">Trước</a>
    </li>`;

  for (let i = 1; i <= pag.pages; i++) {
    html += `<li class="page-item ${i == state.page ? 'active' : ''}">
            <a class="page-link rounded-pill mx-1 border-0 shadow-sm" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a>
        </li>`;
  }

  html += `<li class="page-item ${state.page == pag.pages ? 'disabled' : ''}">
        <a class="page-link rounded-pill border-0 shadow-sm" href="javascript:void(0)" onclick="goToPage(${state.page + 1})">Sau</a>
    </li>`;
  $('paginationList').innerHTML = html;
}

const goToPage = p => {
  state.page = p;
  loadProducts();
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

// --- 5. KHỞI CHẠY (EVENT LISTENERS) ---
document.addEventListener("DOMContentLoaded", () => {
  loadCategories();
  loadProducts();

  let timer;
  const bind = (id, key, delay = 0) => {
    const el = $(id);
    if (!el) return;
    el.oninput = (e) => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        let val = e.target.value;

        // Xử lý logic xóa trắng input
        if (val === '') {
          if (key === 'min') val = 0;
          if (key === 'max') val = 999999999;
        }

        state[key] = val;
        state.page = 1;
        loadProducts();
      }, delay);
    };
  };

  bind('searchInput', 'search', 500);
  bind('minPriceInput', 'min', 300);
  bind('maxPriceInput', 'max', 300);

  $('sortSelect').onchange = (e) => {
    state.sort = e.target.value;
    loadProducts();
  };
});
</script>