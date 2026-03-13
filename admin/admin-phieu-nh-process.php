<?php
require_once '../api/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin'])) {
    header('Location: admin-DangNhap.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_phieu = trim($_POST['ma_phieu'] ?? '');
    $ngay_nhap = $_POST['ngay_nhap'] ?? '';
    $products = $_POST['products'] ?? [];

    if (empty($ma_phieu) || empty($ngay_nhap) || empty($products)) {
        $message = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        $conn->begin_transaction();
        try {
            // Insert vào phieu_nhap_hang
            $stmt = $conn->prepare("INSERT INTO phieu_nhap_hang (ma_phieu, ngay_nhap, trang_thai) VALUES (?, ?, 'pending')");
            $stmt->bind_param("ss", $ma_phieu, $ngay_nhap);
            $stmt->execute();
            $phieu_id = $conn->insert_id;

            // Process từng sản phẩm
            foreach ($products as $product) {
                $ten_sp = trim($product['ten_sp']);
                $gia_nhap = (float)$product['gia_nhap'];
                $so_luong = (int)$product['so_luong'];
                $loai_sp = (int)$product['loai_sp'];

                if ($ten_sp && $gia_nhap > 0 && $so_luong > 0) {
                    // Tìm sản phẩm existing hoặc tạo mới
                    $stmt = $conn->prepare("SELECT id FROM sanpham WHERE ten = ? AND loai = ?");
                    $stmt->bind_param("si", $ten_sp, $loai_sp);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $existing = $result->fetch_assoc();

                    if ($existing) {
                        $sp_id = $existing['id'];
                    } else {
                        // Tạo sản phẩm mới với giá bán tạm thời
                        $stmt = $conn->prepare("INSERT INTO sanpham (ten, gia, loai, gia_von, so_luong_ton, ty_le_loi_nhuan) VALUES (?, ?, ?, ?, 0, 0.3)");
                        $stmt->bind_param("sdii", $ten_sp, $gia_nhap * 1.3, $loai_sp, $gia_nhap);
                        $stmt->execute();
                        $sp_id = $conn->insert_id;
                    }

                    // Gọi procedure sp_nhap_hang
                    $stmt = $conn->prepare("CALL sp_nhap_hang(?, ?, ?)");
                    $stmt->bind_param("idi", $sp_id, $gia_nhap, $so_luong);
                    $stmt->execute();

                    // Insert vào chi tiết phiếu nhập
                    $stmt = $conn->prepare("INSERT INTO phieu_nhap_hang_chi_tiet (phieu_id, san_pham_id, so_luong, gia_nhap) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiid", $phieu_id, $sp_id, $so_luong, $gia_nhap);
                    $stmt->execute();
                }
            }

            // Update trạng thái phiếu
            $stmt = $conn->prepare("UPDATE phieu_nhap_hang SET trang_thai = 'completed' WHERE id = ?");
            $stmt->bind_param("i", $phieu_id);
            $stmt->execute();

            $conn->commit();
            $message = 'Phiếu nhập hàng đã được tạo thành công!';
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách loại sản phẩm
$categories = $conn->query("SELECT id, ten_loai FROM loaisp ORDER BY ten_loai");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Nhập Hàng | Sylphia Shop</title>
    <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-phieu.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <a href="admin-TongQuan.php">
                <img src="../images/logo-web-removebg-preview.png" alt="Logo">
                <span class="shop-name">Sylphia Shop</span>
            </a>
        </div>
        <div class="user-profile">
            <img src="../images/avatar.jpg" alt="Admin" class="avatar">
        </div>
    </header>

    <div class="center-panel">
        <h2>Tạo Phiếu Nhập Hàng</h2>
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Lỗi') === 0 ? 'alert-danger' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="product-form">
            <div class="form-group">
                <label for="ma_phieu">Mã Phiếu Nhập:</label>
                <input type="text" id="ma_phieu" name="ma_phieu" required>
            </div>

            <div class="form-group">
                <label for="ngay_nhap">Ngày Nhập:</label>
                <input type="date" id="ngay_nhap" name="ngay_nhap" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <hr class="divider">
            <h3>Sản Phẩm Nhập</h3>
            <div id="products-container">
                <div class="product-item">
                    <div class="form-group">
                        <label>Loại Sản Phẩm:</label>
                        <select name="products[0][loai_sp]" required>
                            <option value="">Chọn loại</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['ten_loai']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tên Sản Phẩm:</label>
                        <input type="text" name="products[0][ten_sp]" required>
                    </div>

                    <div class="form-group">
                        <label>Giá Nhập (VNĐ):</label>
                        <input type="number" name="products[0][gia_nhap]" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Số Lượng:</label>
                        <input type="number" name="products[0][so_luong]" min="1" required>
                    </div>

                    <button type="button" class="btn btn-danger remove-product" style="display:none;">Xóa</button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="add-product" class="btn-add">
                    <i class="fas fa-plus"></i> Thêm Sản Phẩm
                </button>
            </div>

            <hr class="divider">
            <div class="form-actions">
                <button type="submit" class="btn-add">
                    <i class="fas fa-save"></i> Tạo Phiếu Nhập
                </button>
                <a href="admin-QLPhieuNH.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </form>
    </div>

    <script>
        let productIndex = 1;

        $('#add-product').click(function() {
            const container = $('#products-container');
            const newItem = container.find('.product-item').first().clone();
            newItem.find('input, select').val('');
            newItem.find('input[name*="products[0]"]').each(function() {
                $(this).attr('name', $(this).attr('name').replace('[0]', '[' + productIndex + ']'));
            });
            newItem.find('select[name*="products[0]"]').each(function() {
                $(this).attr('name', $(this).attr('name').replace('[0]', '[' + productIndex + ']'));
            });
            newItem.find('.remove-product').show();
            container.append(newItem);
            productIndex++;
        });

        $(document).on('click', '.remove-product', function() {
            $(this).closest('.product-item').remove();
        });
    </script>
</body>
</html>