<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trang Quản Trị | Sylphia Shop</title>

  <!-- Font & Icon -->
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
        <li><a href="admin-TongQuan.php" class="active"><i class="fas fa-home"></i>Tổng Quan</a></li>
        <li><a href="admin-QLSP.php"><i class="fas fa-box"></i>Sản phẩm</a></li>
        <li><a href="admin-QLPhieuNH.php"><i class="fas fa-receipt"></i>Nhập Hàng</a></li>
        <li><a href="admin-QLKH.php"><i class="fas fa-users"></i>Khách hàng</a></li>
        <li><a href="admin-QLGia.php"><i class="fas fa-tags"></i>Quản lý giá bán</a></li>
        <li><a href="admin-QLDonHang.php"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
        <li><a href="admin-QLKho.php"><i class="fas fa-warehouse"></i>Tồn kho</a></li>
        <li><a href="../user/trangchu.php"><i class="fas fa-house-user"></i>Trang Chủ</a></li>
        <li><a href="admin-DangNhap.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>

      </ul>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
      <!-- TOP NAV -->
      <div class="top-nav">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Tìm kiếm..." />
        </div>

        <div class="user-profile">
          <div class="notifications">
            <i class="fas fa-bell"></i>
          </div>
          <img src="../images/avatar.jpg" alt="Admin" class="avatar" />
          <span class="admin-name">Admin</span>
        </div>
      </div>

      <!-- DASHBOARD -->
      <div class="dashboard">
        <h1>Xin chào, Quản Trị Viên 👋</h1>

        <div class="stats-grid">
          <div class="card">
            <i class="fas fa-box"></i>
            <div>
              <h3>2,280</h3>
              <p>Tổng Sản phẩm</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-shopping-cart"></i>
            <div>
              <h3>345</h3>
              <p>Tổng Đơn hàng</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-users"></i>
            <div>
              <h3>1,281</h3>
              <p>Tổng Khách hàng</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-chart-line"></i>
            <div>
              <h3>+24%</h3>
              <p>Tăng trưởng</p>
            </div>
          </div>
        </div>

        <div class="panel">
          <h2>Hoạt động gần đây</h2>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nhân Viên</th>
                <th>Hoạt động</th>
                <th>Thời gian</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>#001</td>
                <td>Minh</td>
                <td>Tạo đơn hàng mới</td>
                <td>Hôm nay, 09:45</td>
              </tr>
              <tr>
                <td>#002</td>
                <td>Trang</td>
                <td>Thêm sản phẩm</td>
                <td>Hôm qua, 21:30</td>
              </tr>
              <tr>
                <td>#003</td>
                <td>Huy</td>
                <td>Cập nhật giá bán</td>
                <td>Hôm qua, 18:10</td>
              </tr>
              <tr>
                <td>#004</td>
                <td>Minh</td>
                <td>Thêm sản phẩm mới</td>
                <td>Hôm nay, 10:15</td>
              </tr>
              <tr>
                <td>#005</td>
                <td>Trang</td>
                <td>Xóa sản phẩm</td>
                <td>Hôm nay, 10:40</td>
              </tr>
              <tr>
                <td>#006</td>
                <td>Huy</td>
                <td>Nhập hàng</td>
                <td>Hôm nay, 11:05</td>
              </tr>
              <tr>
                <td>#007</td>
                <td>Minh</td>
                <td>Chỉnh sửa thông tin khách hàng</td>
                <td>Hôm nay, 11:30</td>
              </tr>
              <tr>
                <td>#008</td>
                <td>Trang</td>
                <td>Thêm khách hàng mới</td>
                <td>Hôm nay, 11:50</td>
              </tr>
              <tr>
                <td>#009</td>
                <td>Huy</td>
                <td>Cập nhật tồn kho</td>
                <td>Hôm nay, 12:10</td>
              </tr>
              <tr>
                <td>#010</td>
                <td>Minh</td>
                <td>Thêm phiếu nhập mới</td>
                <td>Hôm nay, 12:30</td>
              </tr>
              <tr>
                <td>#011</td>
                <td>Trang</td>
                <td>Thay đổi giá sản phẩm</td>
                <td>Hôm nay, 12:50</td>
              </tr>
              <tr>
                <td>#012</td>
                <td>Huy</td>
                <td>Nhập hàng mới</td>
                <td>Hôm nay, 13:10</td>
              </tr>
              <tr>
                <td>#013</td>
                <td>Minh</td>
                <td>Chỉnh sửa sản phẩm</td>
                <td>Hôm nay, 13:30</td>
              </tr>
              <tr>
                <td>#014</td>
                <td>Trang</td>
                <td>Thêm đơn hàng</td>
                <td>Hôm nay, 13:50</td>
              </tr>
              <tr>
                <td>#015</td>
                <td>Huy</td>
                <td>Cập nhật thông tin khách hàng</td>
                <td>Hôm nay, 14:10</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>

</html>