<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn();

$page_title = 'Sản phẩm';
$current_page = 'products';

include 'header.php';
?>

<main class="flex-fill">

  <div class="py-5">
    <div class="container-fluid">

      <div class="row">

        <!-- SIDEBAR -->
        <div class="col-lg-3">

          <div class="card mb-4 shadow-sm">

            <div class="card-header fw-bold">
              <i class="fas fa-list me-2"></i>Danh mục
            </div>

            <div class="list-group list-group-flush" id="categoriesList">
            </div>

          </div>

        </div>


        <!-- MAIN -->
        <div class="col-lg-9">

          <!-- Search + Filter -->
          <div class="row mb-4">

            <div class="col-md-5">
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fas fa-search"></i>
                </span>

                <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm sản phẩm...">
              </div>
            </div>

            <div class="col-md-4">

              <div class="input-group">

                <span class="input-group-text">Giá</span>

                <input type="number" id="minPrice" class="form-control" placeholder="Từ">

                <span class="input-group-text">-</span>

                <input type="number" id="maxPrice" class="form-control" placeholder="Đến">

              </div>

            </div>

            <div class="col-md-3">

              <select class="form-select" id="sortSelect">

                <option value="id_desc">Mới nhất</option>
                <option value="name_asc">Tên A-Z</option>
                <option value="name_desc">Tên Z-A</option>
                <option value="price_asc">Giá tăng</option>
                <option value="price_desc">Giá giảm</option>

              </select>

            </div>

          </div>


          <!-- RESULT INFO -->
          <div class="d-flex justify-content-between align-items-center mb-4">

            <div id="resultsInfo" class="text-muted">
              Đang tải...
            </div>

            <nav id="paginationNav" class="d-none">

              <ul class="pagination pagination-sm mb-0">

                <li class="page-item">
                  <a class="page-link prev-page" href="#">Trước</a>
                </li>

                <li class="page-item">
                  <a class="page-link next-page" href="#">Sau</a>
                </li>

              </ul>

            </nav>

          </div>


          <!-- PRODUCTS GRID -->
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="productsGrid">
          </div>

        </div>

      </div>

    </div>
  </div>

</main>

<?php include 'footer.php'; ?>


<script>
let currentCategory = 0
let currentPage = 1
let searchTerm = ''
let minPrice = 0
let maxPrice = 999999999
let currentSort = 'id_desc'


function buildQueryParams() {

  return new URLSearchParams({

    category: currentCategory,
    page: currentPage,
    search: searchTerm,
    min_price: minPrice,
    max_price: maxPrice,
    sort: currentSort

  }).toString()

}


/* LOAD PRODUCTS */

async function loadProducts() {

  const grid = document.getElementById("productsGrid")

  grid.innerHTML = `
<div class="col-12 text-center py-5">
<div class="spinner-border text-primary"></div>
</div>
`

  const url = `../api/products.php?${buildQueryParams()}`

  try {

    const res = await fetch(url)
    const result = await res.json()

    grid.innerHTML = ''

    if (result.products.length === 0) {

      grid.innerHTML = `
<div class="col-12 text-center py-5">
<h5>Không tìm thấy sản phẩm</h5>
</div>
`

      return
    }

    let html = ""

    result.products.forEach(p => {

      html += `

<div class="col">

<div class="card h-100 shadow-sm border-0">

<img src="${p.image}"
class="card-img-top p-3"
style="height:220px;object-fit:contain"
onerror="this.src='https://via.placeholder.com/220x220?text=No+Image'">

<div class="card-body d-flex flex-column">

<h6 class="fw-semibold">${p.name}</h6>

<div class="text-primary fw-bold fs-5 mb-3">
${formatPrice(p.price)}
</div>

<div class="mt-auto d-flex gap-2">

<a href="product-detail.php?id=${p.id}"
class="btn btn-outline-primary btn-sm flex-fill">

<i class="fas fa-eye"></i> Chi tiết

</a>

<button onclick="addToCart(${p.id})"
class="btn btn-primary btn-sm flex-fill">

<i class="fas fa-cart-plus"></i>

</button>

</div>

</div>

</div>

</div>

`

    })

    grid.innerHTML = html

    updatePagination(result.pagination)
    updateResultsInfo(result.pagination)

  } catch (err) {

    console.error(err)

  }

}


/* LOAD CATEGORIES */

async function loadCategories() {

  try {

    const res = await fetch('../api/categories.php')
    const categories = await res.json()

    const list = document.getElementById("categoriesList")

    list.innerHTML = `
<button class="list-group-item list-group-item-action active"
onclick="filterCategory(0,this)">
Tất cả
</button>
`

    categories.forEach(c => {

      list.innerHTML += `
<button class="list-group-item list-group-item-action"
onclick="filterCategory(${c.id},this)">
${c.name}
</button>
`

    })

  } catch (e) {

    console.error(e)

  }

}


/* FILTER CATEGORY */

function filterCategory(id, btn) {

  currentCategory = id
  currentPage = 1

  document.querySelectorAll("#categoriesList .list-group-item")
    .forEach(b => b.classList.remove("active"))

  btn.classList.add("active")

  loadProducts()

}


/* PAGINATION */

function updatePagination(pag) {

  const nav = document.getElementById("paginationNav")

  if (!pag || pag.pages <= 1) {

    nav.classList.add("d-none")
    return

  }

  nav.classList.remove("d-none")

}


/* RESULT INFO */

function updateResultsInfo(pag) {

  if (!pag) return

  document.getElementById("resultsInfo").textContent =

    `Hiển thị ${(currentPage-1)*12+1} - ${Math.min(currentPage*12,pag.total)} của ${pag.total} sản phẩm`

}


/* FORMAT PRICE */

function formatPrice(price) {

  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)

}



/* INIT */

document.addEventListener("DOMContentLoaded", function() {

  loadCategories()
  loadProducts()

})
</script>