<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Quản Lý Đơn Hàng | Sylphia Shop</title>
  <link rel="stylesheet" href="../fontawesome-free-7.1.0-web/css/all.min.css" />
  <link rel="stylesheet" href="../css/admin.css" />
  <link rel="stylesheet" href="../css/admin-modal.css" />
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
        <li><a href="admin-QLSP.php"><i class="fas fa-box"></i>Sản phẩm</a></li>
        <li><a href="admin-QLPhieuNH.php"><i class="fas fa-receipt"></i>Nhập Hàng</a></li>
        <li><a href="admin-QLKH.php"><i class="fas fa-users"></i>Khách hàng</a></li>
        <li><a href="admin-QLGia.php"><i class="fas fa-tags"></i>Quản lý giá bán</a></li>
        <li><a href="admin-QLDonHang.php" class="active"><i class="fas fa-shopping-cart"></i>Đơn hàng</a></li>
        <li><a href="admin-QLKho.php"><i class="fas fa-warehouse"></i>Tồn kho</a></li>
        <li><a href="../user/trangchu.php"><i class="fas fa-house-user"></i>Trang Chủ</a></li>
        <li><a href="admin-DangNhap.php"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
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
        <h1>Quản lý đơn hàng</h1>
        <div class="stats-grid">
          <div class="card">
            <i class="fas fa-box"></i>
            <div>
              <h3>2,280</h3>
              <p>Tổng Đơn Hàng</p>
            </div>
          </div>

          <div class="card">
            <i class="fas fa-shopping-cart"></i>
            <div>
              <h3>345</h3>
              <p>Tổng Đơn hàng</p>
            </div>
          </div>
        </div>

        <div class="panel">
          <!-- Bộ lọc đơn hàng -->
          <div class="filter-box">
            <label>Từ ngày:</label>
            <select>
              <option>--Ngày--</option>
              <!-- 1-31 -->
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5</option>
              <option>6</option>
              <option>7</option>
              <option>8</option>
              <option>9</option>
              <option>10</option>
              <option>11</option>
              <option>12</option>
              <option>13</option>
              <option>14</option>
              <option>15</option>
              <option>16</option>
              <option>17</option>
              <option>18</option>
              <option>19</option>
              <option>20</option>
              <option>21</option>
              <option>22</option>
              <option>23</option>
              <option>24</option>
              <option>25</option>
              <option>26</option>
              <option>27</option>
              <option>28</option>
              <option>29</option>
              <option>30</option>
              <option>31</option>
            </select>
            <select>
              <option>--Tháng--</option>
              <!-- 1-12 -->
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5</option>
              <option>6</option>
              <option>7</option>
              <option>8</option>
              <option>9</option>
              <option>10</option>
              <option>11</option>
              <option>12</option>
            </select>
            <select>
              <option>--Năm--</option>
              <option>2023</option>
              <option>2024</option>
              <option>2025</option>
            </select>

            <label>Đến ngày:</label>
            <select>
              <option>--Ngày--</option>
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5</option>
              <option>6</option>
              <option>7</option>
              <option>8</option>
              <option>9</option>
              <option>10</option>
              <option>11</option>
              <option>12</option>
              <option>13</option>
              <option>14</option>
              <option>15</option>
              <option>16</option>
              <option>17</option>
              <option>18</option>
              <option>19</option>
              <option>20</option>
              <option>21</option>
              <option>22</option>
              <option>23</option>
              <option>24</option>
              <option>25</option>
              <option>26</option>
              <option>27</option>
              <option>28</option>
              <option>29</option>
              <option>30</option>
              <option>31</option>
            </select>
            <select>
              <option>--Tháng--</option>
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>5</option>
              <option>6</option>
              <option>7</option>
              <option>8</option>
              <option>9</option>
              <option>10</option>
              <option>11</option>
              <option>12</option>
            </select>
            <select>
              <option>--Năm--</option>
              <option>2023</option>
              <option>2024</option>
              <option>2025</option>
            </select>

            <label>Tình trạng:</label>
            <select>
              <option>--Tất cả--</option>
              <option>Mới đặt</option>
              <option>Đã xử lý</option>
              <option>Đã giao</option>
              <option>Đã hủy</option>
            </select>
            <button>Tra cứu</button>
          </div>

          <table class="manage-table">
            <thead>
              <tr>
                <th>ID Đơn</th>
                <th>Khách hàng</th>
                <th>Ngày đặt</th>
                <th>Tình trạng</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>#1001</td>
                <td> Lê Đức Anh </td>
                <td>01/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view1" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update1" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1002</td>
                <td> Nguyễn Thế Bảo </td>
                <td>02/11/2025</td>
                <td>Đã giao</td>
                <td>
                  <label for="view2" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1003</td>
                <td>Huỳnh Khánh Duy </td>
                <td>03/11/2025</td>
                <td>Đã hủy</td>
                <td>
                  <label for="view3" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1004</td>
                <td>Võ Đồng Gia Bảo </td>
                <td>04/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view4" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update4" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1005</td>
                <td>Trần Bảo Châu</td>
                <td>05/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view5" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update5" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1006</td>
                <td>Phạm Minh Tuấn</td>
                <td>05/11/2025</td>
                <td>Đã xử lý</td>
                <td>
                  <label for="view6" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1007</td>
                <td>Nguyễn Thị Huyền</td>
                <td>06/11/2025</td>
                <td>Đã giao</td>
                <td>
                  <label for="view7" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1008</td>
                <td>Đặng Hoàng Phúc</td>
                <td>06/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view8" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update8" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1009</td>
                <td>Võ Ngọc Hân</td>
                <td>07/11/2025</td>
                <td>Đã hủy</td>
                <td>
                  <label for="view9" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1010</td>
                <td>Nguyễn Đức Long</td>
                <td>07/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view10" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update10" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1011</td>
                <td>Huỳnh Thanh Hà</td>
                <td>08/11/2025</td>
                <td>Đã xử lý</td>
                <td>
                  <label for="view11" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1012</td>
                <td>Ngô Minh Khang</td>
                <td>08/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view12" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update12" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1013</td>
                <td>Lý Bảo Yến</td>
                <td>09/11/2025</td>
                <td>Đã giao</td>
                <td>
                  <label for="view13" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>
              <tr>
                <td>#1014</td>
                <td>Phan Anh Dũng</td>
                <td>10/11/2025</td>
                <td>Mới đặt</td>
                <td>
                  <label for="view14" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <label for="update14" class="btn"><i class="fas fa-edit"></i> Cập nhật</label>
                </td>
              </tr>
              <tr>
                <td>#1015</td>
                <td>Trương Gia Khiêm</td>
                <td>10/11/2025</td>
                <td>Đã xử lý</td>
                <td>
                  <label for="view15" class="btn"><i class="fas fa-eye"></i> Xem</label>
                  <button class="btn disabled-select"><i class="fas fa-edit"></i> Cập nhật</button>
                </td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== POPUP XEM CHI TIẾT ===== -->
  <input type="checkbox" id="view1" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view1" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1001</h2>
      <p><b>Khách hàng:</b> Lê Đức Anh </p>
      <p><b>Ngày đặt:</b>01/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> iPhone 16 Pro Max</p>
      <p><b>Tổng tiền:</b> 32.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view2" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view2" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1002</h2>
      <p><b>Khách hàng:</b> Nguyễn Thế Bảo </p>
      <p><b>Ngày đặt:</b>02/11/2025</p>
      <p><b>Tình trạng:</b> Đã giao</p>
      <p><b>Sản phẩm:</b> Laptop Dell XPS</p>
      <p><b>Tổng tiền:</b> 15.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view3" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view3" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1003</h2>
      <p><b>Khách hàng:</b> Huỳnh Khánh Duy </p>
      <p><b>Ngày đặt:</b>03/11/2025</p>
      <p><b>Tình trạng:</b> Đã hủy</p>
      <p><b>Sản phẩm:</b> iPhone 14 Pro</p>
      <p><b>Tổng tiền:</b> 20.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view4" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view4" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1004</h2>
      <p><b>Khách hàng:</b> VÕ Đồng Gia Bảo</p>
      <p><b>Ngày đặt:</b>04/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> Laptop MacBook Air M2</p>
      <p><b>Tổng tiền:</b> 35.000.000đ</p>
    </div>
  </div>
  <input type="checkbox" id="view5" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view5" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1005</h2>
      <p><b>Khách hàng:</b> Trần Bảo Châu</p>
      <p><b>Ngày đặt:</b> 05/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> Tai nghe Sony WH-1000XM5</p>
      <p><b>Tổng tiền:</b> 9.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view6" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view6" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1006</h2>
      <p><b>Khách hàng:</b> Phạm Minh Tuấn</p>
      <p><b>Ngày đặt:</b> 05/11/2025</p>
      <p><b>Tình trạng:</b> Đã xử lý</p>
      <p><b>Sản phẩm:</b> Màn hình LG UltraWide</p>
      <p><b>Tổng tiền:</b> 7.500.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view7" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view7" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1007</h2>
      <p><b>Khách hàng:</b> Nguyễn Thị Huyền</p>
      <p><b>Ngày đặt:</b> 06/11/2025</p>
      <p><b>Tình trạng:</b> Đã giao</p>
      <p><b>Sản phẩm:</b> Đồng hồ Samsung Watch 7</p>
      <p><b>Tổng tiền:</b> 8.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view8" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view8" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1008</h2>
      <p><b>Khách hàng:</b> Đặng Hoàng Phúc</p>
      <p><b>Ngày đặt:</b> 06/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> Chuột Logitech G Pro</p>
      <p><b>Tổng tiền:</b> 2.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view9" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view9" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1009</h2>
      <p><b>Khách hàng:</b> Võ Ngọc Hân</p>
      <p><b>Ngày đặt:</b> 07/11/2025</p>
      <p><b>Tình trạng:</b> Đã hủy</p>
      <p><b>Sản phẩm:</b> Loa JBL Flip 6</p>
      <p><b>Tổng tiền:</b> 3.500.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view10" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view10" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1010</h2>
      <p><b>Khách hàng:</b> Nguyễn Đức Long</p>
      <p><b>Ngày đặt:</b> 07/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> Máy lọc không khí Sharp</p>
      <p><b>Tổng tiền:</b> 4.500.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view11" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view11" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1011</h2>
      <p><b>Khách hàng:</b> Huỳnh Thanh Hà</p>
      <p><b>Ngày đặt:</b> 08/11/2025</p>
      <p><b>Tình trạng:</b> Đã xử lý</p>
      <p><b>Sản phẩm:</b> iPad Air 6</p>
      <p><b>Tổng tiền:</b> 16.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view12" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view12" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1012</h2>
      <p><b>Khách hàng:</b> Ngô Minh Khang</p>
      <p><b>Ngày đặt:</b> 08/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> Bàn phím cơ Akko</p>
      <p><b>Tổng tiền:</b> 1.800.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view13" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view13" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1013</h2>
      <p><b>Khách hàng:</b> Lý Bảo Yến</p>
      <p><b>Ngày đặt:</b> 09/11/2025</p>
      <p><b>Tình trạng:</b> Đã giao</p>
      <p><b>Sản phẩm:</b> iPhone 15</p>
      <p><b>Tổng tiền:</b> 24.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view14" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view14" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1014</h2>
      <p><b>Khách hàng:</b> Phan Anh Dũng</p>
      <p><b>Ngày đặt:</b> 10/11/2025</p>
      <p><b>Tình trạng:</b> Mới đặt</p>
      <p><b>Sản phẩm:</b> MacBook Pro M3</p>
      <p><b>Tổng tiền:</b> 45.000.000đ</p>
    </div>
  </div>

  <input type="checkbox" id="view15" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="view15" class="close"><i class="fas fa-times"></i></label>
      <h2>Chi tiết đơn #1015</h2>
      <p><b>Khách hàng:</b> Trương Gia Khiêm</p>
      <p><b>Ngày đặt:</b> 10/11/2025</p>
      <p><b>Tình trạng:</b> Đã xử lý</p>
      <p><b>Sản phẩm:</b> Tivi Samsung QLED 55"</p>
      <p><b>Tổng tiền:</b> 18.000.000đ</p>
    </div>
  </div>
  <!-- ===== POPUP CẬP NHẬT ===== -->
  <input type="checkbox" id="update1" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update1" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1001</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>

  <input type="checkbox" id="update4" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update4" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1004</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>

  <input type="checkbox" id="update5" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update5" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1005</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>
  <!-- Popup cập nhật đơn #1008 -->
  <input type="checkbox" id="update8" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update8" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1008</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>

  <!-- Popup cập nhật đơn #1010 -->
  <input type="checkbox" id="update10" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update10" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1010</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>

  <!-- Popup cập nhật đơn #1012 -->
  <input type="checkbox" id="update12" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update12" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1012</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>

  <!-- Popup cập nhật đơn #1014 -->
  <input type="checkbox" id="update14" class="modal-toggle" />
  <div class="modal">
    <div class="modal-box">
      <label for="update14" class="close"><i class="fas fa-times"></i></label>
      <h2>Cập nhật đơn #1014</h2>
      <form action="admin-QLDonHang.php">
        <p>Chọn tình trạng mới:</p>
        <select>
          <option>Mới đặt</option>
          <option>Đã xử lý</option>
          <option>Đã giao</option>
          <option>Đã hủy</option>
        </select>
        <button class="btn" type="submit">Lưu</button>
      </form>
    </div>
  </div>



</body>

</html>