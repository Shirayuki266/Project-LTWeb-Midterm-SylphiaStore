<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý nhập sản phẩm | Sylphia Shop</title>

    <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css" />
    <link rel="stylesheet" href="../css/admin.css" />
</head>

<body>
    <div class="admin-layout">
        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar">
            <div class="logo">
                <img src="../images/logo-web-removebg-preview.png" alt="Logo" />
                Sylphia Shop
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-TongQuan.html"><i class="fas fa-home"></i>Tổng Quan</a></li>
                <li><a href="admin-QLSP.html"><i class="fas fa-box"></i>Sản phẩm</a></li>
                <li><a href="admin-QLPhieuNH.html" class="active"><i class="fas fa-receipt"></i>Nhập Hàng</a></li>
                <li><a href="admin-QLKH.html"><i class="fas fa-users"></i>Khách hàng</a></li>
                <li><a href="admin-QLGia.html"><i class="fas fa-tags"></i>Quản lý giá bán</a></li>
                <li><a href="admin-QLDonHang.html"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
                <li><a href="admin-QLKho.html"><i class="fas fa-warehouse"></i>Tồn kho</a></li>
                <li><a href="../user/trangchu.html"><i class="fas fa-house-user"></i>Trang Chủ</a></li>
                <li><a href="admin-DangNhap.html"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
            </ul>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="main-content">
            <div class="top-nav">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm phiếu nhập..." />
                </div>
                <div class="user-profile">
                    <div class="notifications"><i class="fas fa-bell"></i></div>
                    <img src="../images/avatar.jpg" alt="Admin" class="avatar" />
                    <span class="admin-name">Admin</span>
                </div>
            </div>

            <div class="dashboard">
                <h1>Quản lý phiếu nhập sản phẩm</h1>
                <div class="stats-grid">
                    <div class="card">
                        <i class="fas fa-receipt"></i>
                        <div>
                            <h3>156</h3>
                            <p>Tổng Phiếu Nhập Hàng</p>
                        </div>
                    </div>

                    <div class="card">
                        <i class="fas fa-box"></i>
                        <div>
                            <h3>2,971</h3>
                            <p>Tổng Sản Phẩm Đã Nhập Hàng</p>
                        </div>
                    </div>

                    <div class="card">
                        <i class="fas fa-shopping-cart"></i>
                        <div>
                            <h3>204</h3>
                            <p>Tổng Sản Phẩm Đang Nhập Hàng</p>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <!-- Form Thêm phiếu nhập -->
                    <form class="form-grid manage-form">
                        <div class="form-group">
                            <label>Mã phiếu nhập:</label>
                            <input type="text" placeholder="VD: PN01" />
                        </div>
                        <div class="form-group">
                            <label>Ngày nhập Từ:</label>
                            <input type="date" />
                        </div>
                        <div class="form-group">
                            <label> Đến Ngày nhập:</label>
                            <input type="date" />
                        </div>
                        <a href="admin-phieu-nh.html" class="btn-add">
                            <i class="fas fa-plus"></i> Thêm
                        </a>
                        <button type="button" class="btn"><i class="fas fa-search"></i> Tìm Kiếm</button>
                    </form>
                    <h2><i class="fas fa-file-invoice"></i> Danh sách phiếu nhập</h2>
                    <h3>Lưu ý: Phiếu Chỉ Ở trạng thái hoàn thành sau khi admin nhấn nút hoàn thành</h3>
                    <!-- Bảng phiếu nhập -->
                    <table class="manage-table">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Ngày nhập</th>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá nhập</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PN01</td>
                                <td>2025-11-02</td>
                                <td>Iphone 17 Pro Max</td>
                                <td>10</td>
                                <td>25,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN02</td>
                                <td>2025-11-01</td>
                                <td>MacBook Air M3</td>
                                <td>5</td>
                                <td>35,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN03</td>
                                <td>2025-11-03</td>
                                <td>iPad Pro M6</td>
                                <td>8</td>
                                <td>20,000,000</td>
                                <td><span class="status locked">Chưa hoàn thành</span></td>
                                <td class="action-buttons">
                                    <a href="admin-SuaPhieu-nh.html" class="btn"><i class="fas fa-edit"></i></a>
                                    <a class="btn"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>PN04</td>
                                <td>2025-11-03</td>
                                <td>Galaxy S25 Ultra</td>
                                <td>12</td>
                                <td>22,500,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN05</td>
                                <td>2025-11-04</td>
                                <td>MacBook Pro M3</td>
                                <td>6</td>
                                <td>40,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN06</td>
                                <td>2025-11-05</td>
                                <td>AirPods Max</td>
                                <td>15</td>
                                <td>8,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN07</td>
                                <td>2025-11-06</td>
                                <td>iMac M3</td>
                                <td>4</td>
                                <td>55,000,000</td>
                                <td><span class="status locked">Chưa hoàn thành</span></td>
                                <td class="action-buttons">
                                    <a href="admin-SuaPhieu-nh.html" class="btn"><i class="fas fa-edit"></i></a>
                                    <a class="btn"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>PN08</td>
                                <td>2025-11-06</td>
                                <td>Galaxy Tab S9</td>
                                <td>7</td>
                                <td>18,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN09</td>
                                <td>2025-11-07</td>
                                <td>Apple Watch Series 11</td>
                                <td>20</td>
                                <td>12,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN10</td>
                                <td>2025-11-08</td>
                                <td>Mac Mini M3</td>
                                <td>3</td>
                                <td>30,000,000</td>
                                <td><span class="status locked">Chưa hoàn thành</span></td>
                                <td class="action-buttons">
                                    <a href="admin-SuaPhieu-nh.html" class="btn"><i class="fas fa-edit"></i></a>
                                    <a class="btn"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>PN11</td>
                                <td>2025-11-08</td>
                                <td>iPhone SE 5G</td>
                                <td>25</td>
                                <td>15,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN12</td>
                                <td>2025-11-09</td>
                                <td>iPad Mini M6</td>
                                <td>9</td>
                                <td>14,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN13</td>
                                <td>2025-11-10</td>
                                <td>Galaxy Buds Pro</td>
                                <td>18</td>
                                <td>5,000,000</td>
                                <td><span class="status locked">Chưa hoàn thành</span></td>
                                <td class="action-buttons">
                                    <a href="admin-SuaPhieu-nh.html" class="btn"><i class="fas fa-edit"></i></a>
                                    <a class="btn"><i class="fas fa-check"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>PN14</td>
                                <td>2025-11-10</td>
                                <td>MacBook Pro 16 M3</td>
                                <td>2</td>
                                <td>50,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>PN15</td>
                                <td>2025-11-11</td>
                                <td>iMac Pro M3</td>
                                <td>3</td>
                                <td>60,000,000</td>
                                <td><span class="status active">Hoàn thành</span></td>
                                <td class="action-buttons">
                                    <button class="btn info disabled"><i class="fas fa-edit"></i></button>
                                    <button class="btn lock disabled"><i class="fas fa-check"></i></button>
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