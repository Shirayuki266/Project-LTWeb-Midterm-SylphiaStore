<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý sản phẩm & loại | Sylphia Shop</title>
    <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css" />
    <link rel="stylesheet" href="../css/admin.css" />
    <link rel="stylesheet" href="../css/admin-phieu.css" />
</head>

<body>
    <header class="main-header">
        <div class="logo">
            <a href="admin-TongQuan.php">
                <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
                <span class="shop-name">Sylphia Shop</span>
            </a>
        </div>

        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm sản phẩm..." />
        </div>

        <div class="user-profile">
            <div class="notifications">
                <i class="fas fa-bell"></i>
            </div>
            <img src="../images/avatar.jpg" alt="Admin" class="avatar" />
        </div>
    </header>
    <div class="center-panel">
        <h2>Thêm sản phẩm mới</h2>
        <form class="product-form">
            <label for="loai">Loại sản phẩm</label>
            <select id="loai" name="loai" required>
                <option value="">Chọn loại</option>
                <option value="dien-thoai">Điện thoại</option>
                <option value="laptop">Laptop</option>
                <option value="ban-phim">Bàn phím</option>
                <option value="chuot">Chuột</option>
            </select>

            <label for="ma">Mã sản phẩm</label>
            <input type="text" id="ma" name="ma" placeholder="Nhập mã sản phẩm" required />

            <label for="ten">Tên sản phẩm</label>
            <input type="text" id="ten" name="ten" placeholder="Nhập tên sản phẩm" required />

            <label for="gia">Giá</label>
            <input type="number" id="gia" name="gia" placeholder="Nhập giá sản phẩm" required />

            <label for="mo-ta">Mô tả sản phẩm</label>
            <textarea id="mo-ta" name="mo-ta" placeholder="Nhập mô tả..." rows="4" required></textarea>

            <div class="form-actions">
                <a href="#" class="btn-add">
                    <i class="fas fa-image"></i> Thêm Hình Ảnh
                </a>
                <a href="admin-QLSP.php" class="btn-add">
                    <i class="fas fa-check"></i> Thêm Sản Phẩm
                </a>
            </div>

        </form>

    </div>

</body>

</html>