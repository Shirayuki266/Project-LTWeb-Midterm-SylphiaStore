<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý sản phẩm & loại | Sylphia Shop</title>
    <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css" />
    <link rel="stylesheet" href="../css/admin.css" />
</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="logo">
                <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
                Sylphia Shop
            </div>

            <ul class="sidebar-menu">
                <li><a href="admin-TongQuan.php"><i class="fas fa-home"></i>Tổng Quan</a></li>
                <li><a href="admin-QLSP.php" class="active"><i class="fas fa-box"></i>Sản phẩm</a></li>
                <li><a href="admin-QLPhieuNH.php"><i class="fas fa-receipt"></i>Nhập Hàng</a></li>
                <li><a href="admin-QLKH.php"><i class="fas fa-users"></i>Khách hàng</a></li>
                <li><a href="admin-QLGia.php"><i class="fas fa-tags"></i>Quản lý giá bán</a></li>
                <li><a href="admin-QLDonHang.php"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
                <li><a href="admin-QLKho.php"><i class="fas fa-warehouse"></i>Tồn kho</a></li>
                <li><a href="../user/trangchu.php"><i class="fas fa-house-user"></i>Trang Chủ</a></li>
                <li><a href="admin-DangNhap.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- MAIN -->
        <div class="main-content">
            <div class="top-nav">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm..." />
                </div>

                <div class="user-profile">
                    <div class="notifications"><i class="fas fa-bell"></i></div>
                    <img src="../images/avatar.jpg" alt="Admin" class="avatar" />
                    <span class="admin-name">Admin</span>
                </div>
            </div>

            <div class="dashboard">
                <h1>Quản lý sản phẩm & loại hàng</h1>
                <div class="stats-grid">
                    <div class="card">
                        <i class="fas fa-box"></i>
                        <div>
                            <h3>2,280</h3>
                            <p>Tổng Sản phẩm</p>
                        </div>
                    </div>

                    <div class="card">
                        <i class="fas fa-tags"></i>
                        <div>
                            <h3>5</h3>
                            <p>Tổng Loại Sản Phẩm</p>
                        </div>
                    </div>
                </div>
                <!-- LOẠI SẢN PHẨM -->
                <div class="manage-panel">
                    <h2><i class="fas fa-tags"></i>Loại sản phẩm</h2>
                    <form class="manage-form">
                        <input type="text" placeholder="Mã loại" required />
                        <input type="text" placeholder="Tên loại" required />

                        <a href="admin-phieu-loaisp.php">
                            <i class="fas fa-plus"></i>Thêm
                        </a>
                        <a href="#">
                            <i class="fas fa-search"></i>Tìm Kiếm
                        </a>
                    </form>

                    <table class="manage-table">
                        <thead>
                            <tr>
                                <th>Mã loại</th>
                                <th>Tên loại</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>L01</td>
                                <td>Điện thoại</td>
                                <td>
                                    <a href="admin-SuaPhieu-loaisp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn info-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>L02</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-loaisp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn info-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>L03</td>
                                <td>Bàn Phím</td>
                                <td>
                                    <a href="admin-SuaPhieu-loaisp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn info-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>L04</td>
                                <td>Thẻ Cào/Sim</td>
                                <td>
                                    <a href="admin-SuaPhieu-loaisp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn info-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>L05</td>
                                <td>Chuột</td>
                                <td>
                                    <a href="admin-SuaPhieu-loaisp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn info-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SẢN PHẨM -->
                <div class="manage-panel">
                    <h2><i class="fas fa-box"></i> Sản phẩm</h2>
                    <form class="manage-form">
                        <select required>
                            <option value="">-- Chọn loại sản phẩm --</option>
                            <option value="Điện Thoại">Điện thoại</option>
                            <option value="Máy Tính">Máy tính bảng</option>
                            <option value="Bàn Phím">Bàn Phím</option>
                            <option value="Thẻ Cào/Sim">Thẻ Cào/Sim</option>
                            <option value="Chuột">Chuột</option>
                        </select>
                        <input type="text" placeholder="Mã sản phẩm" required />
                        <input type="text" placeholder="Tên sản phẩm" required />
                        <input type="text" placeholder="Mô Tả sản phẩm" required />
                        <input type="text" placeholder="Giá" required />
                        <a href="admin-phieu-sp.php" class="btn-add">
                            <i class="fas fa-plus"></i> Thêm
                        </a>
                        <button type="submit" class="btn"><i class="fas fa-search"></i> Tìm Kiếm</button>

                    </form>

                    <table class="manage-table">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Loại</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SP01</td>
                                <td>Iphone 17 Pro Max</td>
                                <td>39.000.000₫</td>
                                <td>Điện thoại</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>

                            </tr>
                            <tr>
                                <td>SP02</td>
                                <td>Iphone 17 Mini</td>
                                <td>31.000.000₫</td>
                                <td>Điện thoại</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>SP03</td>
                                <td>Asus ROG Zephyrus G14</td>
                                <td>45.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP04</td>
                                <td>Asus TUF Gaming F15</td>
                                <td>30.500.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP05</td>
                                <td>Asus VivoBook S15</td>
                                <td>20.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP06</td>
                                <td>Asus ZenBook 14</td>
                                <td>28.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP07</td>
                                <td>MacBook Air M2</td>
                                <td>27.500.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP08</td>
                                <td>MacBook Pro M1</td>
                                <td>39.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>SP09</td>
                                <td>Bàn phím cơ RK61</td>
                                <td>1.500.000₫</td>
                                <td>Bàn Phím</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP10</td>
                                <td>Bàn phím cơ GK61</td>
                                <td>1.200.000₫</td>
                                <td>Bàn Phím</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>SP11</td>
                                <td>Chuột Logitech G502</td>
                                <td>950.000₫</td>
                                <td>Chuột</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP12</td>
                                <td>Chuột Razer DeathAdder V2</td>
                                <td>1.200.000₫</td>
                                <td>Chuột</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP13</td>
                                <td>Chuột SteelSeries Rival 3</td>
                                <td>700.000₫</td>
                                <td>Chuột</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP14</td>
                                <td>Chuột Logitech MX Master 3</td>
                                <td>1.700.000₫</td>
                                <td>Chuột</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>SP15</td>
                                <td>Asus ExpertBook B9</td>
                                <td>35.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP16</td>
                                <td>Asus ROG Strix G15</td>
                                <td>42.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP17</td>
                                <td>Iphone 16 Pro</td>
                                <td>28.500.000₫</td>
                                <td>Điện thoại</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP18</td>
                                <td>Asus ZenBook Flip 13</td>
                                <td>32.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP19</td>
                                <td>Bàn phím cơ HyperX Alloy</td>
                                <td>1.400.000₫</td>
                                <td>Bàn Phím</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>SP20</td>
                                <td>Asus VivoBook 15</td>
                                <td>21.000.000₫</td>
                                <td>Laptop</td>
                                <td>
                                    <a href="admin-SuaPhieu-sp.php" class="btn info-sp">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn lock-sp">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>
</body>

</html>