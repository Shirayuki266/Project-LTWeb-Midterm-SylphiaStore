<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Nạp file kết nối - Dùng đường dẫn tuyệt đối để tránh lỗi null
require_once __DIR__ . '/../api/db.php'; 
require_once __DIR__ . '/../includes/functions.php';

if (!check_user_active($conn)) {
    header("Location: login.php?error=account_locked");
    exit();
}

/* --- LOGIC PHÂN TRANG --- */
$limit = 8; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số để chia trang
$total_result = $conn->query("SELECT COUNT(*) FROM products WHERE status = 1");
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

// Truy vấn sản phẩm theo trang
$sql_hot = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 1 
            ORDER BY p.id DESC 
            LIMIT $limit OFFSET $offset";
$hotProducts = $conn->query($sql_hot)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Trang chủ';
$current_page = 'index';
include 'header.php';
?>

<section class="py-5" style="background-color: #f5f5f7;">
  <div class="container text-center text-lg-start">
    <div class="row align-items-center">
      <div class="col-lg-6 px-lg-5">
        <span class="badge rounded-pill bg-white text-dark border px-3 py-2 mb-3 shadow-sm">NEW GENERATION</span>
        <h1 class="fw-bold display-4 mb-4">The future is <br><span class="text-primary">In your hands.</span></h1>
        <p class="lead text-secondary mb-5">Dòng Sylphia Titanium định nghĩa lại công nghệ di động.</p>
        <a href="products.php" class="btn btn-dark btn-lg rounded-pill px-5 py-3 shadow">Khám phá ngay</a>
      </div>
      <div class="col-lg-6 mt-5 mt-lg-0">
        <img src="../images/loggo_intro.png" class="img-fluid rounded-5 shadow-lg"
          style="max-height: 450px; object-fit: cover;">
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4 px-2">
      <h2 class="fw-bold mb-0">⭐ Sản phẩm nổi bật</h2>
      <span class="text-muted small">Trang <?php echo $page; ?>/<?php echo $total_pages; ?></span>
    </div>

    <div class="row g-3 g-md-4">
      <?php if(count($hotProducts) > 0): ?>
      <?php foreach($hotProducts as $product): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <?php include '../includes/product-card.php'; ?>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="col-12 text-center py-5">
        <div class="alert alert-light border shadow-sm">Không tìm thấy sản phẩm nào.</div>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-5">
      <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
          <a class="page-link border-0 shadow-sm rounded-pill me-2 px-3" href="?page=<?php echo $page-1; ?>">Trước</a>
        </li>
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
          <a class="page-link border-0 shadow-sm rounded-circle mx-1 d-flex align-items-center justify-content-center"
            style="width: 40px; height: 40px;" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
          <a class="page-link border-0 shadow-sm rounded-pill ms-2 px-3" href="?page=<?php echo $page+1; ?>">Sau</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>