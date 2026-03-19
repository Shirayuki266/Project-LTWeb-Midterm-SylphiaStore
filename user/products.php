<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';
if (!$auth->isLoggedIn()) {
    // Đuổi ngay ra trang login
    header("Location: login.php");
    exit(); // Bắt buộc phải có exit để dừng load dữ liệu bên dưới
}
$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn();

$page_title = 'Sản phẩm';
$current_page = 'products';

include 'header.php';
?>

<main class="flex-fill bg-light">
  <div class="py-5">
    <div class="container-fluid">
      <div class="row">

        <div class="col-lg-3">
          <div class="card mb-4 border-0 shadow-sm rounded-4 " style="top: 60px;">
            <div class="card-header bg-white fw-bold border-0 pt-3">
              <i class="fas fa-list me-2 text-primary"></i>Danh mục
            </div>
            <div class="list-group list-group-flush p-2" id="categoriesList">
            </div>
          </div>
        </div>

        <div class="col-lg-9">
          <div class="row mb-4 g-3">
            <div class="col-md-5">
              <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                <span class="input-group-text border-0 bg-white ps-3">
                  <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-0" placeholder="Tìm tên sản phẩm...">
              </div>
            </div>

            <div class="col-md-4">
              <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border">
                <span class="input-group-text border-0 bg-white">Giá</span>
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

          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="productsGrid">
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
// 1. LẤY THÔNG TIN TỪ URL NGAY KHI VÀO TRANG
const urlParams = new URLSearchParams(window.location.search);
let state = {
  cat: parseInt(urlParams.get('category')) || 0, // Đọc ?category=X từ Header
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

/* 2. HÀM LOAD SẢN PHẨM CHÍNH */
async function loadProducts() {
  const grid = $('productsGrid');

  // Hiển thị Spinner chuẩn Bootstrap ở giữa màn hình
  grid.innerHTML = `
        <div class="col-12 d-flex flex-column align-items-center justify-content-center" style="min-height: 50vh;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <h5 class="mt-3 text-secondary fw-light">Đang tìm sản phẩm...</h5>
        </div>`;

  try {
    const query = new URLSearchParams({
      category: state.cat,
      page: state.page,
      search: state.search,
      min_price: state.min,
      max_price: state.max,
      sort: state.sort
    }).toString();

    const res = await fetch(`../api/products.php?${query}`);
    const result = await res.json();

    grid.innerHTML = '';

    if (!result.products?.length) {
      grid.innerHTML =
        `<div class="col-12 text-center py-5"><h5 class="text-muted">Không tìm thấy sản phẩm phù hợp</h5></div>`;
      if ($('paginationNav')) $('paginationNav').classList.add('d-none');
      return;
    }

    grid.innerHTML = result.products.map(p => `
            <div class="col">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden product-card">
                    <div class="p-3 bg-white d-flex align-items-center justify-content-center" style="height:180px">
                        <img src="${p.image}" class="img-fluid" style="max-height:100%; object-fit:contain" 
                            onerror="this.src='https://via.placeholder.com/200'">
                    </div>
                    <div class="card-body d-flex flex-column pt-0 text-center">
                        <h6 class="fw-bold text-truncate mb-2">${p.name}</h6>
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
    grid.innerHTML = `<div class="col-12 text-center py-5 text-danger">Lỗi kết nối. Vui lòng thử lại!</div>`;
  }
}

/* 3. LOAD DANH MỤC VÀ TỰ ĐỘNG ACTIVE THEO URL */
async function loadCategories() {
  const res = await fetch('../api/categories.php');
  const cats = await res.json();
  const list = $('categoriesList');

  let html = `<button class="list-group-item list-group-item-action border-0 rounded-3 mb-1 ${state.cat == 0 ? 'active' : ''}" 
                onclick="filterCat(0,this)">Tất cả</button>`;

  cats.forEach(c => {
    const activeClass = (state.cat == c.id) ? 'active' : '';
    html += `<button class="list-group-item list-group-item-action border-0 rounded-3 mb-1 ${activeClass}" 
                 onclick="filterCat(${c.id},this)">${c.name}</button>`;
  });
  list.innerHTML = html;
}

function filterCat(id, btn) {
  state.cat = id;
  state.page = 1;
  document.querySelectorAll('#categoriesList .list-group-item').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadProducts();
  // Cập nhật URL trình duyệt để đồng bộ với Header
  window.history.pushState({}, '', `products.php?category=${id}`);
}

/* 4. PHÂN TRANG */
function updatePagination(pag) {
  const nav = $('paginationNav');
  if (!pag || pag.pages <= 1) return nav.classList.add('d-none');
  nav.classList.remove('d-none');

  let html =
    `<li class="page-item ${state.page==1?'disabled':''}"><a class="page-link rounded-pill border-0 shadow-sm me-2" href="javascript:void(0)" onclick="goToPage(${state.page-1})">Trước</a></li>`;
  for (let i = 1; i <= pag.pages; i++) {
    html +=
      `<li class="page-item ${i==state.page?'active':''}"><a class="page-link rounded-pill mx-1 border-0 shadow-sm" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a></li>`;
  }
  html +=
    `<li class="page-item ${state.page==pag.pages?'disabled':''}"><a class="page-link rounded-pill border-0 shadow-sm" href="javascript:void(0)" onclick="goToPage(${state.page+1})">Sau</a></li>`;
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

/* 5. KHỞI TẠO VÀ LẮNG NGHE TÌM KIẾM */
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
        state[key] = e.target.value;
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