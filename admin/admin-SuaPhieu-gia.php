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
            <a href="admin-TongQuan.html">
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
        <h2>Sửa Lợi Nhuận Loại Sản Phẩm</h2>
        <form class="product-form">
            <label for="loai">Loại sản phẩm</label>
            <select id="loai" name="loai" required>
                <option value="dien-thoai">Điện thoại</option>
                <option value="">Chọn loại</option>
                <option value="laptop">Laptop</option>
                <option value="ban-phim">Bàn phím</option>
                <option value="chuot">Chuột</option>
            </select>

            <label for="ten">Phần Trăm Lợi Nhuận</label>
            <input type="number" id="loinhuan" name="loinhuan" placeholder="Nhập phần trăm lợi nhuận" value="10"
                required />
            <hr class="divider" />
            <div class="form-actions">
                <a href="admin-QLGia.html" class="btn-add">
                    <i class="fas fa-check"></i> Xong
                </a>
            </div>
        </form>
    </div>
</body>

</html>