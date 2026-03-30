<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$page_title = 'Sản phẩm - Sylphia Shop';
$current_page = 'products';

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
              onclick="setPriceRange(0, 10000000, this)">Dưới 10tr</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(10000000, 20000000, this)">10tr - 20tr</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(20000000, 30000000, this)">20tr - 30tr</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 price-tag"
              onclick="setPriceRange(30000000, 999999999, this)">Trên 30tr</button>
            <button class="btn btn-link btn-sm text-decoration-none text-muted"
              onclick="setPriceRange(0, 999999999, null)">Xoá lọc</button>
          </div>

          <div id="searchStatus"
            class="mb-4 d-none p-3 bg-white rounded-4 shadow-sm border-start border-primary border-4">
            <div class="d-flex align-items-center">
              <i class="fas fa-filter text-primary me-3 fs-5"></i>
              <span id="statusText" class="text-dark fw-medium"></span>
            </div>
          </div>

          <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4" id="productsGrid">
            <div class="col-12 text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">Đang tải sản phẩm...</p>
            </div>
          </div>

          <nav id="paginationNav" class="mt-5 d-none">
            <ul class="pagination justify-content-center border-0" id="paginationList"></ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

<script src="../sylphia_shop.js/common.js"></script>
<script src="../sylphia_shop.js/cart.js"></script>
<script src="../sylphia_shop.js/products.js"></script>
<script src="../sylphia_shop.js/product-detail.js"></script>