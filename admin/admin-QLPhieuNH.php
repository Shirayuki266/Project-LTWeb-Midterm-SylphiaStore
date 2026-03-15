<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* CHECK ADMIN LOGIN */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

/* =============================
   1. XỬ LÝ HOÀN THÀNH PHIẾU NHẬP
============================= */
if (isset($_GET['complete_id'])) {

    $id = intval($_GET['complete_id']);

    $conn->begin_transaction();

    try {

        $stmt = $conn->prepare("UPDATE purchase_orders SET status='completed' WHERE id=? AND status='pending'");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {

            $items = $conn->query("
                SELECT product_id, quantity 
                FROM purchase_order_details 
                WHERE purchase_order_id = $id
            ");

            while ($item = $items->fetch_assoc()) {

                $pid = intval($item['product_id']);
                $qty = intval($item['quantity']);

                $conn->query("
                    INSERT INTO inventory (product_id, stock)
                    VALUES ($pid,$qty)
                    ON DUPLICATE KEY UPDATE stock = stock + $qty
                ");
            }
        }

        $conn->commit();

        header("Location: admin-QLPhieuNH.php?msg=success");
        exit();

    } catch (Exception $e) {

        $conn->rollback();
        header("Location: admin-QLPhieuNH.php?msg=error");
        exit();
    }
}

/* =============================
   2. THỐNG KÊ
============================= */

$total_orders = $conn->query("
SELECT COUNT(*) as cnt 
FROM purchase_orders
")->fetch_assoc()['cnt'] ?? 0;

$total_items_imported = $conn->query("
SELECT SUM(pod.quantity) as qty
FROM purchase_order_details pod
JOIN purchase_orders po ON pod.purchase_order_id = po.id
WHERE po.status='completed'
")->fetch_assoc()['qty'] ?? 0;

$total_items_pending = $conn->query("
SELECT SUM(pod.quantity) as qty
FROM purchase_order_details pod
JOIN purchase_orders po ON pod.purchase_order_id = po.id
WHERE po.status='pending'
")->fetch_assoc()['qty'] ?? 0;

/* =============================
   3. TÌM KIẾM
============================= */

$search_code = $conn->real_escape_string($_GET['code'] ?? '');
$from_date = $conn->real_escape_string($_GET['from'] ?? '');
$to_date = $conn->real_escape_string($_GET['to'] ?? '');

$where = "WHERE 1=1";

if ($search_code) {
    $where .= " AND po.code LIKE '%$search_code%'";
}

if ($from_date) {
    $where .= " AND po.order_date >= '$from_date'";
}

if ($to_date) {
    $where .= " AND po.order_date <= '$to_date'";
}

/* =============================
   4. LẤY DANH SÁCH PHIẾU NHẬP
============================= */

$sql = "
SELECT 
    po.id,
    po.code,
    po.order_date,
    po.status,

    GROUP_CONCAT(p.name SEPARATOR '<br>') as product_names,

    SUM(pod.quantity) as total_qty,

    SUM(pod.import_price * pod.quantity) as total_cost

FROM purchase_orders po

LEFT JOIN purchase_order_details pod
ON po.id = pod.purchase_order_id

LEFT JOIN products p
ON pod.product_id = p.id

$where

GROUP BY po.id

ORDER BY po.order_date DESC
";

$result = $conn->query($sql);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

?>

<?php include 'header.php'; ?>

<div class="main-content">

  <div class="dashboard">

    <h1>Quản lý phiếu nhập sản phẩm</h1>

    <?php if(isset($_GET['msg']) && $_GET['msg']=='success'): ?>

    <div class="alert success">
      Cập nhật kho thành công
    </div>

    <?php endif; ?>

    <div class="stats-grid">

      <div class="card">
        <i class="fas fa-receipt"></i>
        <div>
          <h3><?php echo $total_orders; ?></h3>
          <p>Tổng Phiếu Nhập Hàng</p>
        </div>
      </div>

      <div class="card">
        <i class="fas fa-box"></i>
        <div>
          <h3><?php echo number_format($total_items_imported); ?></h3>
          <p>Sản Phẩm Đã Vào Kho</p>
        </div>
      </div>

      <div class="card">
        <i class="fas fa-shopping-cart"></i>
        <div>
          <h3><?php echo number_format($total_items_pending); ?></h3>
          <p>Sản Phẩm Đang Đợi</p>
        </div>
      </div>

    </div>

    <div class="panel">

      <form class="form-grid manage-form" method="GET">

        <div class="form-group">
          <label>Mã phiếu nhập:</label>
          <input type="text" name="code" value="<?php echo htmlspecialchars($search_code); ?>" placeholder="VD: PN01">
        </div>

        <div class="form-group">
          <label>Ngày nhập Từ:</label>
          <input type="date" name="from" value="<?php echo $from_date; ?>">
        </div>

        <div class="form-group">
          <label>Đến Ngày nhập:</label>
          <input type="date" name="to" value="<?php echo $to_date; ?>">
        </div>

        <div class="d-flex align-items-end">

          <a href="admin-phieu-nh-process.php" class="btn-add me-2">
            <i class="fas fa-plus"></i> Thêm
          </a>

          <button type="submit" class="btn">
            <i class="fas fa-search"></i> Tìm Kiếm
          </button>

        </div>

      </form>

      <h2><i class="fas fa-file-invoice"></i> Danh sách phiếu nhập</h2>

      <table class="manage-table">

        <thead>

          <tr>
            <th>Mã phiếu</th>
            <th>Ngày nhập</th>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Tổng tiền nhập</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>

        </thead>

        <tbody>

          <?php if (empty($orders)): ?>

          <tr>
            <td colspan="7" class="text-center">Không tìm thấy dữ liệu</td>
          </tr>

          <?php else: foreach ($orders as $row): ?>

          <tr>

            <td><strong><?php echo $row['code']; ?></strong></td>

            <td>
              <?php echo date('d/m/Y', strtotime($row['order_date'])); ?>
            </td>

            <td>
              <small><?php echo $row['product_names']; ?></small>
            </td>

            <td>
              <?php echo $row['total_qty']; ?>
            </td>

            <td>
              <?php echo number_format($row['total_cost']); ?> đ
            </td>

            <td>

              <?php if ($row['status'] == 'completed'): ?>

              <span class="status active">
                Hoàn thành
              </span>

              <?php else: ?>

              <span class="status locked">
                Chưa hoàn thành
              </span>

              <?php endif; ?>

            </td>

            <td class="action-buttons">

              <?php if ($row['status'] == 'pending'): ?>

              <a href="admin-SuaPhieu-nh.php?id=<?php echo $row['id']; ?>" class="btn info">
                <i class="fas fa-edit"></i>
              </a>

              <a href="?complete_id=<?php echo $row['id']; ?>" class="btn lock"
                onclick="return confirm('Xác nhận hoàn thành? Dữ liệu sẽ được cộng vào kho và không thể sửa lại.')">

                <i class="fas fa-check"></i>

              </a>

              <?php else: ?>

              <button class="btn info disabled">
                <i class="fas fa-edit"></i>
              </button>

              <button class="btn lock disabled">
                <i class="fas fa-check"></i>
              </button>

              <?php endif; ?>

            </td>

          </tr>

          <?php endforeach; endif; ?>

        </tbody>

      </table>

    </div>
  </div>
</div>

<?php include 'admin-footer.php'; ?>