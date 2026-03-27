<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

/* ==========================================================
   1. XỬ LÝ HOÀN THÀNH PHIẾU NHẬP (CẬP NHẬT KHO & GIÁ VỐN)
   ========================================================== */
if (isset($_GET['complete_id'])) {
    $id = intval($_GET['complete_id']);
    $conn->begin_transaction(); // Đảm bảo an toàn dữ liệu (Transaction)

    try {
        // Kiểm tra trạng thái phiếu trước khi xử lý
        $stmt = $conn->prepare("SELECT status FROM purchase_orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if ($order && $order['status'] === 'pending') {
            // Lấy chi tiết sản phẩm trong phiếu
            $items = $conn->query("SELECT product_id, quantity, import_price FROM purchase_order_details WHERE purchase_order_id = $id");

            while ($item = $items->fetch_assoc()) {
                $pid = intval($item['product_id']);
                $qty_new = intval($item['quantity']);
                $price_new = floatval($item['import_price']);

                // Lấy thông tin kho và giá hiện tại của sản phẩm
                $p_res = $conn->query("SELECT stock, cost_price, profit_percent FROM products WHERE id = $pid");
                $p_old = $p_res->fetch_assoc();
                
                $qty_old = intval($p_old['stock'] ?? 0);
                $price_old = floatval($p_old['cost_price'] ?? 0);
                $margin = floatval($p_old['profit_percent'] ?? 0);

                // CÔNG THỨC GIÁ VỐN BÌNH QUÂN GIA QUYỀN
                $total_qty = $qty_old + $qty_new;
                $new_cost_price = ($total_qty > 0) 
                    ? (($qty_old * $price_old) + ($qty_new * $price_new)) / $total_qty 
                    : $price_new;

                // Tự động tính lại giá bán mới
                $new_price = $new_cost_price * (1 + ($margin / 100));

                // Cập nhật bảng Products: Kho + Giá vốn + Giá bán
                $update_p = $conn->prepare("UPDATE products SET stock = ?, cost_price = ?, price = ? WHERE id = ?");
                $update_p->bind_param("iddi", $total_qty, $new_cost_price, $new_price, $pid);
                $update_p->execute();
            }

            // Chuyển trạng thái phiếu sang Hoàn thành
            $conn->query("UPDATE purchase_orders SET status='completed' WHERE id=$id");
            
            $conn->commit();
            header("Location: admin-QLPhieuNH.php?msg=success");
        }
    } catch (Exception $e) {
        $conn->rollback(); // Hủy bỏ nếu có lỗi xảy ra
        header("Location: admin-QLPhieuNH.php?msg=error");
    }
    exit();
}

/* ==========================================================
   2. TRUY VẤN DANH SÁCH (ĐÃ KHỚP CỘT TRONG ẢNH DB CỦA BẠN)
   ========================================================== */
$search_supplier = $conn->real_escape_string($_GET['supplier'] ?? '');
$from_date = $_GET['from'] ?? '';
$to_date = $_GET['to'] ?? '';

$where = "WHERE 1=1";
if ($search_supplier) $where .= " AND po.supplier_name LIKE '%$search_supplier%'";
if ($from_date) $where .= " AND po.created_at >= '$from_date 00:00:00'";
if ($to_date) $where .= " AND po.created_at <= '$to_date 23:59:59'";

$sql = "SELECT po.id, po.supplier_name, po.total_amount, po.status, po.created_at,
        GROUP_CONCAT(p.name SEPARATOR '<br>') as product_names,
        SUM(pod.quantity) as total_qty
        FROM purchase_orders po
        LEFT JOIN purchase_order_details pod ON po.id = pod.purchase_order_id
        LEFT JOIN products p ON pod.product_id = p.id
        $where
        GROUP BY po.id
        ORDER BY po.created_at DESC";

$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold mb-0">
      <i class="fas fa-file-import me-2 text-primary"></i>Quản lý Phiếu Nhập Hàng
    </h2>
    <a href="admin-QLNhapHang.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
      <i class="fas fa-plus me-2"></i>Tạo phiếu mới
    </a>
  </div>

  <?php if(isset($_GET['msg'])): ?>
  <div
    class="alert <?php echo $_GET['msg'] == 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show border-0 shadow-sm">
    <i class="fas <?php echo $_GET['msg'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
    <?php echo $_GET['msg'] == 'success' ? 'Đã hoàn thành phiếu nhập. Kho và giá vốn đã được cập nhật!' : 'Lỗi hệ thống, vui lòng kiểm tra lại.'; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-body">
      <form class="row g-3" method="GET">
        <div class="col-md-4">
          <input type="text" name="supplier" class="form-control" placeholder="Tìm theo nhà cung cấp..."
            value="<?php echo htmlspecialchars($search_supplier); ?>">
        </div>
        <div class="col-md-3">
          <input type="date" name="from" class="form-control" value="<?php echo $from_date; ?>">
        </div>
        <div class="col-md-3">
          <input type="date" name="to" class="form-control" value="<?php echo $to_date; ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-dark w-100">Lọc dữ liệu</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Mã Phiếu</th>
            <th>Nhà cung cấp</th>
            <th>Ngày tạo</th>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th class="text-end pe-4">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">Không tìm thấy phiếu nhập nào.</td>
          </tr>
          <?php else: foreach ($orders as $po): ?>
          <tr>
            <td class="ps-4 fw-bold text-dark">#<?php echo $po['id']; ?></td>
            <td><?php echo htmlspecialchars($po['supplier_name']); ?></td>
            <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($po['created_at'])); ?></td>
            <td class="small text-truncate" style="max-width: 200px;"><?php echo $po['product_names']; ?></td>
            <td><span class="badge bg-light text-dark border"><?php echo number_format($po['total_qty']); ?></span></td>
            <td class="fw-bold text-primary"><?php echo number_format($po['total_amount']); ?>₫</td>
            <td>
              <?php if ($po['status'] == 'completed'): ?>
              <span class="badge bg-success-subtle text-success border border-success px-3 py-2">Đã nhập</span>
              <?php else: ?>
              <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 text-dark">Chờ
                chốt</span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-4">
              <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <button class="btn btn-sm btn-white border" onclick="viewDetails(<?php echo $po['id']; ?>)"
                  title="Xem chi tiết">
                  <i class="fas fa-eye"></i>
                </button>
                <?php if ($po['status'] == 'pending'): ?>
                <a href="admin-QLNhapHang.php?edit_id=<?php echo $po['id']; ?>"
                  class="btn btn-sm btn-white border text-primary" title="Sửa phiếu">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="?complete_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-success text-white"
                  onclick="return confirm('Chốt phiếu này? Kho sẽ được cộng và giá vốn sẽ thay đổi.')">
                  <i class="fas fa-check"></i> Chốt
                </a>
                <?php else: ?>
                <button class="btn btn-sm btn-light border disabled"><i class="fas fa-lock text-muted"></i></button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'admin-footer.php'; ?>
  const content = document.getElementById('detailContent');
  content.innerHTML =
    '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm me-2"></div> Đang tải...</td></tr>';

  const detailModalElement = document.getElementById('detailModal');
  const myModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
  myModal.show();

  fetch(`../api/get_purchase_details.php?id=${poId}`)
    .then(r => r.json())
    .then(data => {
      if (data.length === 0) {
        content.innerHTML =
          '<tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu chi tiết.</td></tr>';
        return;
      }
      content.innerHTML = '';
      let totalAmount = 0;
      data.forEach(item => {
        let subtotal = item.import_price * item.quantity;
        totalAmount += subtotal;
        content.innerHTML += `
                    <tr>
                        <td class="fw-medium">${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${new Intl.NumberFormat('vi-VN').format(item.import_price)}₫</td>
                        <td class="text-end fw-bold pe-3">${new Intl.NumberFormat('vi-VN').format(subtotal)}₫</td>
                    </tr>
                `;
      });
      // Thêm dòng tổng cộng ở cuối bảng trong Modal
      content.innerHTML += `
                <tr class="table-light">
                    <td colspan="3" class="text-end fw-bold">Tổng tiền phiếu:</td>
                    <td class="text-end fw-bold text-primary pe-3 fs-5">${new Intl.NumberFormat('vi-VN').format(totalAmount)}₫</td>
                </tr>
            `;
    })
    .catch(err => {
      content.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Lỗi kết nối dữ liệu!</td></tr>';
    });
}
</script>

<?php include 'admin-footer.php'; ?>