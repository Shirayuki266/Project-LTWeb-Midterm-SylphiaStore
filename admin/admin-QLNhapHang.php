<?php
session_start();
require_once '../api/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$msg = '';
$err = '';
$editOrder = null;
$editItems = [];

// Complete order request (chốt phiếu)
if (isset($_GET['complete_id'])) {
    $complete_id = (int)$_GET['complete_id'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT status FROM purchase_orders WHERE id = ?");
        $stmt->bind_param('i', $complete_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) throw new Exception('Phiếu không tồn tại');
        if ($order['status'] !== 'pending') throw new Exception('Phiếu đã chốt, không thể chốt lại');

        $details = $conn->query("SELECT product_id, quantity, import_price FROM purchase_order_details WHERE purchase_order_id = $complete_id");
        while ($item = $details->fetch_assoc()) {
            $pid = (int)$item['product_id'];
            $qty = (int)$item['quantity'];
            $imp_price = floatval($item['import_price']);

            $pRes = $conn->query("SELECT stock, cost_price, profit_percent FROM products WHERE id = $pid");
            $product = $pRes->fetch_assoc();
            if (!$product) continue;

            $stock_old = (int)($product['stock'] ?? 0);
            $cost_old = floatval($product['cost_price'] ?? 0);
            $margin = floatval($product['profit_percent'] ?? 0);

            $totalQty = $stock_old + $qty;
            $newCost = ($totalQty > 0) ? (($stock_old * $cost_old) + ($qty * $imp_price)) / $totalQty : $imp_price;
            $newPrice = $newCost * (1 + ($margin/100));

            $upd = $conn->prepare("UPDATE products SET stock = ?, cost_price = ?, price = ? WHERE id = ?");
            $upd->bind_param('iddi', $totalQty, $newCost, $newPrice, $pid);
            $upd->execute();
        }

        $conn->query("UPDATE purchase_orders SET status='completed' WHERE id=$complete_id");
        $conn->commit();
        $msg = 'Chốt phiếu nhập thành công.';
    } catch (Exception $e) {
        $conn->rollback();
        $err = 'Lỗi chốt phiếu: ' . $e->getMessage();
    }
    header('Location: admin-QLNhapHang.php?msg=' . urlencode($msg) . '&err=' . urlencode($err));
    exit();
}

// Get order info for edit
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM purchase_orders WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editOrder = $stmt->get_result()->fetch_assoc();
    if ($editOrder) {
        $itemsRes = $conn->query("SELECT d.*, p.name as product_name FROM purchase_order_details d JOIN products p ON d.product_id = p.id WHERE d.purchase_order_id = $edit_id");
        while ($r = $itemsRes->fetch_assoc()) {
            $editItems[] = $r;
        }
    }
}

// Save order (create or update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_order'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $supplier = trim($_POST['supplier_name'] ?? '');
    $data_items = json_decode($_POST['items_json'] ?? '[]', true);

    if ($supplier === '') {
        $err = 'Nhà cung cấp không được để trống.';
    } elseif (!is_array($data_items) || count($data_items) === 0) {
        $err = 'Vui lòng chọn ít nhất 1 sản phẩm.';
    } else {
        $total_amount = 0;
        foreach ($data_items as $item) {
            $qty = (int)$item['quantity'];
            $price = floatval($item['import_price']);
            $total_amount += $qty * $price;
            if ($qty <= 0 || $price <= 0) {
                $err = 'Số lượng và giá nhập phải > 0.';
                break;
            }
        }
    }

    if (!$err) {
        try {
            $conn->begin_transaction();
            if ($order_id > 0) {
                // update only pending
                $check = $conn->query("SELECT status FROM purchase_orders WHERE id=$order_id")->fetch_assoc();
                if (!$check || $check['status'] !== 'pending') {
                    throw new Exception('Không thể sửa phiếu đã hoàn thành.');
                }
                $stmt = $conn->prepare("UPDATE purchase_orders SET supplier_name=?, total_amount=? WHERE id=?");
                $stmt->bind_param('sdi', $supplier, $total_amount, $order_id);
                $stmt->execute();

                $conn->query("DELETE FROM purchase_order_details WHERE purchase_order_id=$order_id");
            } else {
                $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_name, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
                $stmt->bind_param('sd', $supplier, $total_amount);
                $stmt->execute();
                $order_id = $conn->insert_id;
            }

            $stmtDet = $conn->prepare("INSERT INTO purchase_order_details (purchase_order_id, product_id, quantity, import_price) VALUES (?, ?, ?, ?)");
            foreach ($data_items as $item) {
                $prod_id = (int)$item['product_id'];
                $qty = (int)$item['quantity'];
                $iprice = floatval($item['import_price']);
                $stmtDet->bind_param('iiid', $order_id, $prod_id, $qty, $iprice);
                $stmtDet->execute();
            }

            $conn->commit();
            $msg = $order_id > 0 ? 'Lưu phiếu nhập thành công.' : 'Tạo phiếu nhập thành công.';

            header('Location: admin-QLNhapHang.php?msg=' . urlencode($msg));
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $err = 'Lỗi ghi dữ liệu: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách phiếu nhập
$purchase_orders = $conn->query("SELECT * FROM purchase_orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
// Lấy danh sách sản phẩm để chọn
$allProducts = $conn->query("SELECT id, name, stock, cost_price, price FROM products WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="container-fluid py-4 px-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold">Quản lý Phiếu Nhập Hàng</h2>
    <a href="admin-QLPhieuNH.php" class="btn btn-secondary">Xem danh sách phiếu</a>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
  <div class="alert alert-danger border-0 shadow-sm mb-4"><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <form id="orderForm" method="POST" class="row g-3">
        <input type="hidden" name="action_save_order" value="1">
        <input type="hidden" name="order_id" id="order_id" value="<?php echo (int)($editOrder['id'] ?? 0); ?>">
        <input type="hidden" name="items_json" id="items_json" value="">

        <div class="col-md-4">
          <label class="form-label">Nhà cung cấp</label>
          <input type="text" name="supplier_name" id="supplier_name" class="form-control" required value="<?php echo htmlspecialchars($editOrder['supplier_name'] ?? ''); ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label">Tìm sản phẩm</label>
          <input type="text" id="product_search" class="form-control" placeholder="Gõ tên sản phẩm để tìm" autocomplete="off">
        </div>
        <div class="col-md-2">
          <label class="form-label"> &nbsp;</label>
          <button type="button" class="btn btn-outline-primary w-100" onclick="toggleAvailableList()">Danh sách SP</button>
        </div>
        <div class="col-md-2">
          <label class="form-label"> &nbsp;</label>
          <button type="submit" class="btn btn-success w-100" id="saveOrderBtn"><?php echo $editOrder ? 'Lưu phiếu' : 'Tạo phiếu'; ?></button>
        </div>

        <div class="col-12" id="availableProducts" style="display:none;max-height:240px;overflow:auto;">
          <div class="list-group">
            <?php foreach ($allProducts as $p): ?>
            <button type="button" class="list-group-item list-group-item-action" data-id="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo $p['cost_price']; ?>" onclick="addProductFromList(this)">
              #<?php echo $p['id']; ?> <?php echo htmlspecialchars($p['name']); ?> - Tồn: <?php echo number_format($p['stock']); ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </form>

      <div class="mt-3">
        <h5 class="mb-2">Sản phẩm trong phiếu</h5>
        <table class="table table-bordered table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>SP</th>
              <th class="text-center" style="width:100px;">Số lượng</th>
              <th class="text-end" style="width:140px;">Giá nhập</th>
              <th class="text-end" style="width:140px;">Thành tiền</th>
              <th class="text-end" style="width:80px;">Xóa</th>
            </tr>
          </thead>
          <tbody id="orderItemsBody">
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-end fw-bold">Tổng</td>
              <td class="text-end fw-bold" id="orderTotal">0</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <h5 class="mb-3">Danh sách các phiếu nhập</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nhà cung cấp</th>
              <th>Ngày tạo</th>
              <th>Tổng tiền</th>
              <th>Trạng thái</th>
              <th class="text-end">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$purchase_orders): ?>
            <tr><td colspan="6" class="text-center text-muted">Chưa có phiếu nhập nào.</td></tr>
            <?php endif; ?>
            <?php foreach ($purchase_orders as $po): ?>
            <tr>
              <td>#<?php echo $po['id']; ?></td>
              <td><?php echo htmlspecialchars($po['supplier_name']); ?></td>
              <td><?php echo date('d/m/Y H:i', strtotime($po['created_at'])); ?></td>
              <td class="text-end text-primary fw-bold"><?php echo number_format($po['total_amount']); ?> </td>
              <td>
                <span class="badge <?php echo $po['status']=='completed' ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo ucfirst($po['status']); ?></span>
              </td>
              <td class="text-end">
                <a href="?edit_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-outline-secondary">Sửa</a>
                <?php if ($po['status'] == 'pending'): ?>
                <a href="?complete_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Chốt phiếu và cập nhật kho + giá vốn?')">Chốt</a>
                <?php endif; ?>
                <a href="admin-QLPhieuNH.php?view=<?php echo $po['id']; ?>" class="btn btn-sm btn-outline-info">Chi tiết</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const allItems = [];
const editPayload = <?php echo json_encode(!empty($editItems) ? $editItems : [], JSON_UNESCAPED_UNICODE); ?>;

function toggleAvailableList() {
    const block = document.getElementById('availableProducts');
    block.style.display = block.style.display === 'none' ? 'block' : 'none';
}

function addProductFromList(btn) {
    const item = {
        product_id: btn.dataset.id,
        name: btn.dataset.name,
        quantity: 1,
        import_price: Number(btn.dataset.price) || 0
    };
    addOrderItem(item);
}

function addOrderItem(item) {
    const existed = allItems.find(i => i.product_id == item.product_id);
    if (existed) {
        existed.quantity += 1;
    } else {
        allItems.push(item);
    }
    renderItems();
}

function renderItems() {
    const tbody = document.getElementById('orderItemsBody');
    tbody.innerHTML = '';
    let total = 0;

    allItems.forEach((item, index) => {
        const sub = item.quantity * item.import_price;
        total += sub;
        tbody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td class="text-center"><input type="number" class="form-control form-control-sm" min="1" value="${item.quantity}" onchange="updateQty(${index}, this.value)"></td>
                <td class="text-end"><input type="number" class="form-control form-control-sm text-end" min="0" value="${item.import_price}" onchange="updatePrice(${index}, this.value)"></td>
                <td class="text-end">${new Intl.NumberFormat('vi-VN').format(sub)} </td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">x</button></td>
            </tr>
        `;
    });

    document.getElementById('orderTotal').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' ';
    document.getElementById('items_json').value = JSON.stringify(allItems);
}

function updateQty(i, qty) {
    allItems[i].quantity = Math.max(1, parseInt(qty));
    renderItems();
}

function updatePrice(i, price) {
    allItems[i].import_price = Math.max(0, parseFloat(price));
    renderItems();
}

function removeItem(i) {
    allItems.splice(i, 1);
    renderItems();
}

window.addEventListener('load', function() {
    const searchInput = document.getElementById('product_search');
    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        document.querySelectorAll('#availableProducts button').forEach(b => {
            const text = b.dataset.name.toLowerCase();
            b.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    if (editPayload.length > 0) {
        editPayload.forEach(i => {
            addOrderItem({product_id: i.product_id, name: i.product_name, quantity: i.quantity, import_price: i.import_price});
        });
        document.getElementById('supplier_name').value = '<?php echo htmlspecialchars($editOrder['supplier_name'] ?? ''); ?>';
        document.getElementById('order_id').value = '<?php echo (int)($editOrder['id'] ?? 0); ?>';
        document.getElementById('saveOrderBtn').innerText = 'Cập nhật phiếu';
    }
});
</script>

<?php include 'admin-footer.php'; ?>
