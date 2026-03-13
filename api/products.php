<?php
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';

class Products {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Search + filter + paginate (maps schema fields to view-friendly keys)
    public function getProducts($search = '', $category = 0, $min_price = 0, $max_price = 0, $page = 1, $perPage = 12) {
        $sql = "SELECT 
                    s.id,
                    s.ten AS name,
                    s.gia AS price,
                    s.hinh AS image,
                    s.mota AS description,
                    s.rating,
                    s.giamgia AS discount_price,
                    s.loai AS category_id,
                    l.ten_loai AS cat_name,
                    s.gia_von AS cost_price,
                    s.so_luong_ton AS stock,
                    s.ty_le_loi_nhuan AS profit_margin
                FROM sanpham s
                LEFT JOIN loaisp l ON s.loai = l.id
                WHERE 1=1";
        $params = []; $types = '';

        if ($search) {
            $sql .= " AND s.ten LIKE ?";
            $params[] = "%$search%"; $types .= 's';
        }
        if ($category) {
            $sql .= " AND s.loai = ?";
            $params[] = $category; $types .= 'i';
        }
        if ($min_price) {
            $sql .= " AND s.gia >= ?";
            $params[] = $min_price; $types .= 'd';
        }
        if ($max_price) {
            $sql .= " AND s.gia <= ?";
            $params[] = $max_price; $types .= 'd';
        }
        $sql .= " ORDER BY s.id DESC";

        return paginate($this->conn, $sql, $page, $perPage, $params, $types);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT 
            s.id,
            s.ten AS name,
            s.gia AS price,
            s.hinh AS image,
            s.mota AS description,
            s.rating,
            s.giamgia AS discount_price,
            s.loai AS category_id,
            l.ten_loai AS cat_name,
            s.gia_von AS cost_price,
            s.so_luong_ton AS stock,
            s.ty_le_loi_nhuan AS profit_margin
            FROM sanpham s
            LEFT JOIN loaisp l ON s.loai = l.id
            WHERE s.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();
        if ($prod) {
            $prod['sell_price'] = $prod['discount_price'] ? $prod['discount_price'] : $prod['price'];
        }
        return $prod;
    }

    // Admin CRUD (stubs)
    public function create($data, $file) {
        // Handle img upload, insert etc. (stub)
        return true;
    }

    public function updateStock($product_id, $delta) {
        // Schema does not include stock tracking table, so this is a no-op.
        return false;
    }

    public function getLowStock() {
        // No stock tracking in current schema.
        return [];
    }
}

$products = new Products($conn);
?>