<?php
session_start();
require_once 'includes/config.php';

// Handle GET params
$search = $_GET['search'] ?? '';
$category = $_GET['loai'] ?? '';
$sort = $_GET['sort'] ?? '';

// Build query
$sql = "SELECT s.*, l.ten_loai FROM sanpham s LEFT JOIN loaisp l ON s.loai = l.id WHERE 1=1";
$params = [];
$types = '';
if ($search) {
    $sql .= " AND s.ten LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}
if ($category) {
    $sql .= " AND s.loai = ?";
    $params[] = $category;
    $types .= 'i';
}
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY gia ASC"; break;
    case 'price_desc': $sql .= " ORDER BY gia DESC"; break;
    default: $sql .= " ORDER BY id DESC";
}

$sql .= " LIMIT 20"; // Pagination later

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
mysqli_stmt_close($stmt);
?>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h1 class="mb-4">Sản Phẩm</h1>

  <!-- Filters -->
  <div class="row mb-4">
    <div class="col-md-4">
      <select class="form-select" onchange="filterProducts(this.value)">
        <option value="">Tất cả loại</option>
        <?php 
        $cats = mysqli_query($conn, "SELECT * FROM loaisp");
        while ($cat = mysqli_fetch_assoc($cats)) {
            $selected = ($category == $cat['id']) ? 'selected' : '';
            echo "<option value='{$cat['id']}' $selected>{$cat['ten_loai']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-md-4">
      <select class="form-select" onchange="sortProducts(this.value)">
        <option value="">Sắp xếp mặc định</option>
        <option value="price_asc" <?php echo $sort=='price_asc'?'selected':'';?>>Giá thấp - cao</option>
        <option value="price_desc" <?php echo $sort=='price_desc'?'selected':'';?>>Giá cao - thấp</option>
      </select>
    </div>
  </div>

  <!-- Products Grid -->
  <div class="row g-4">
    <?php foreach ($products as $product): 
      $discount_price = $product['giamgia'] > 0 ? $product['giamgia'] : $product['gia'];
    ?>
    <div class="col-lg-3 col-md-6">
      <div class="card h-100 shadow-sm product-card">
        <img src="../images/<?php echo htmlspecialchars($product['hinh']); ?>" class="card-img-top"
          alt="<?php echo htmlspecialchars($product['ten']); ?>">
        <div class="card-body">
          <h6 class="card-title"><?php echo htmlspecialchars($product['ten']); ?></h6>
          <div class="rating mb-2">
            <?php for($i=1; $i<=5; $i++): ?>
            <i class="fas fa-star<?php echo $i <= round($product['rating']) ? '' : '-empty'; ?> text-warning"></i>
            <?php endfor; ?>
            <span class="text-muted">(<?php echo $product['rating']; ?>)</span>
          </div>
          <div class="price-group">
            <span class="price"><?php echo number_format($discount_price, 0, ',', '.'); ?>₫</span>
            <?php if ($product['giamgia'] > 0): ?>
            <span
              class="old-price text-muted text-decoration-line-through"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-footer bg-transparent">
          <a href="chitietsanpham.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-danger w-100">Xem chi
            tiết</a>
          <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-danger w-100 mt-1">Thêm giỏ
            hàng</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($products)): ?>
  <div class="text-center py-5">
    <h3>Không tìm thấy sản phẩm</h3>
    <p>Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc.</p>
  </div>
  <?php endif; ?>
</div>

<script>
function filterProducts(cat) {
  const url = new URL(window.location);
  if (cat) url.searchParams.set('loai', cat);
  else url.searchParams.delete('loai');
  window.location = url;
}

function sortProducts(sort) {
  const url = new URL(window.location);
  if (sort) url.searchParams.set('sort', sort);
  else url.searchParams.delete('sort');
  window.location = url;
}

function addToCart(id) {
  // AJAX add to cart
  alert('Đã thêm vào giỏ hàng!');
}
</script>
<?php include 'footer.php'; ?>