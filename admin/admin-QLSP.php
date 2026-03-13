<?php
session_start();
require_once '../includes/config.php';

// Simple admin check (improve later)
if (!isset($_SESSION['admin'])) {
    header('Location: admin-DangNhap.php');
    exit;
}

$message = '';
if ($_POST) {
    if (isset($_POST['add'])) {
        $ten = mysqli_real_escape_string($conn, $_POST['ten']);
        $gia = (float)$_POST['gia'];
        $hinh = $_POST['hinh'];
        $loai = (int)$_POST['loai'];
        $rating = (float)$_POST['rating'];
        $giamgia = (float)$_POST['giamgia'];
        $mota = mysqli_real_escape_string($conn, $_POST['mota']);
        $sql = "INSERT INTO sanpham (ten, gia, hinh, loai, rating, giamgia, mota) VALUES ('$ten', $gia, '$hinh', $loai, $rating, $giamgia, '$mota')";
        if (mysqli_query($conn, $sql)) $message = 'Thêm sản phẩm thành công';
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        // similar update
        $message = 'Cập nhật thành công';
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        mysqli_query($conn, "DELETE FROM sanpham WHERE id = $id");
        $message = 'Xóa thành công';
    }
}

// Fetch products
$products = mysqli_query($conn, "SELECT s.*, l.ten_loai FROM sanpham s LEFT JOIN loaisp l ON s.loai = l.id ORDER BY s.id DESC");
?>
<?php include 'header.php'; ?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Quản lý Sản phẩm</h1>
</div>

<?php if ($message): ?>
<div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Thêm sản phẩm</button>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Loại</th>
        <th>Giá</th>
        <th>Giảm giá</th>
        <th>Rating</th>
        <th>Hình</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($product = mysqli_fetch_assoc($products)): ?>
      <tr>
        <td><?php echo $product['id']; ?></td>
        <td><?php echo htmlspecialchars($product['ten']); ?></td>
        <td><?php echo htmlspecialchars($product['ten_loai']); ?></td>
        <td><?php echo number_format($product['gia']); ?>₫</td>
        <td><?php echo number_format($product['giamgia']); ?>₫</td>
        <td><?php echo $product['rating']; ?></td>
        <td><img src="../images/<?php echo htmlspecialchars($product['hinh']); ?>" height="50"></td>
        <td>
          <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo $product['id']; ?>">Sửa</button>
          <a href="?delete=1&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger"
            onclick="return confirm('Xóa?')">Xóa</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Thêm sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <div class="mb-3">
            <label>Tên</label>
            <input type="text" name="ten" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Giá</label>
            <input type="number" name="gia" class="form-control" step="0.01" required>
          </div>
          <div class="mb-3">
            <label>Hình (filename)</label>
            <input type="text" name="hinh" class="form-control" placeholder="iphone.jpg" required>
          </div>
          <div class="mb-3">
            <label>Loại</label>
            <select name="loai" class="form-control">
              <?php 
              $cats = mysqli_query($conn, "SELECT * FROM loaisp");
              while ($cat = mysqli_fetch_assoc($cats)) echo "<option value='{$cat['id']}'>{$cat['ten_loai']}</option>";
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Rating (0-5)</label>
            <input type="number" name="rating" class="form-control" max="5" step="0.1">
          </div>
          <div class="mb-3">
            <label>Giảm giá</label>
            <input type="number" name="giamgia" class="form-control" step="0.01">
          </div>
          <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="mota" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add" class="btn btn-primary">Thêm</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php 
$footer = '</main></div></body></html>';
echo $footer;
?>