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
            <a href="trangchu.html">
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
        <h2>Sửa Sản Phẩm</h2>
        <form class="product-form">
            <label for="loai">Loại sản phẩm</label>
            <select id="loai" name="loai" required>
                <option value="dien-thoai">Điện thoại</option>
                <option value="">Chọn loại</option>
                <option value="laptop">Laptop</option>
                <option value="ban-phim">Bàn phím</option>
                <option value="chuot">Chuột</option>
            </select>

            <label for="ma">Mã sản phẩm</label>
            <input type="text" id="ma" name="ma" placeholder="Nhập mã sản phẩm" value="SP01" required />

            <label for="ten">Tên sản phẩm</label>
            <input type="text" id="ten" name="ten" placeholder="Nhập tên sản phẩm" value="Iphone 17 Pro Max" required />

            <label for="gia">Giá</label>
            <input type="text" id="gia" name="gia" placeholder="Nhập giá sản phẩm" value="39,000,000" required />

            <label for="mo-ta">Mô tả sản phẩm</label>
            <textarea id="mo-ta" name="mo-ta" rows="4" required>
iPhone 17 Pro Max với màn hình Super Retina XDR 6,9″, chip A19 Pro mạnh mẽ, camera triple 48 MP.
</textarea>


            <div class="form-actions">
                <a href="#" class="btn-add">
                    <i class="fas fa-edit"></i> Sửa Hình Ảnh
                </a>
                <a href="admin-QLSP.html" class="btn-add">
                    <i class="fas fa-check"></i> Hoàn Thành
                </a>
            </div>
        </form>

    </div>

</body>

</html>