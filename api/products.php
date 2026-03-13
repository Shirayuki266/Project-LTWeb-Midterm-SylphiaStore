<?php
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../includes/functions.php';

class Products {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Search + filter + paginate
    public function getProducts($search = '', $category = 0, $min_price = 0, $max_price = 0, $page = 1, $perPage = 12) {

        $sql = "SELECT 
                    p.id,
                    p.ten AS name,
                    p.gia AS price,
                    p.hinh AS image,
                    p.mota AS description,
                    p.rating,
                    p.giamgia AS discount_price,
                    p.loai AS category_id,
                    pt.ten_loai AS cat_name,
                    p.gia_von AS cost_price,
                    p.so_luong_ton AS stock,
                    p.ty_le_loi_nhuan AS profit_margin
                FROM product p
                LEFT JOIN product_type pt ON p.loai = pt.id
                WHERE 1=1";

        $params = [];
        $types = '';

        if ($search) {
            $sql .= " AND p.name LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
        }

        if ($category) {
            $sql .= " AND p.product_type_id = ?";
            $params[] = $category;
            $types .= 'i';
        }

        if ($min_price) {
            $sql .= " AND p.price >= ?";
            $params[] = $min_price;
            $types .= 'd';
        }

        if ($max_price) {
            $sql .= " AND p.price <= ?";
            $params[] = $max_price;
            $types .= 'd';
        }

        $sql .= " ORDER BY p.id DESC";

        return paginate($this->conn, $sql, $page, $perPage, $params, $types);
    }

    public function getById($id) {

        $stmt = $this->conn->prepare("SELECT 
            p.id,
            p.ten AS name,
            p.gia AS price,
            p.hinh AS image,
            p.mota AS description,
            p.rating,
            p.giamgia AS discount_price,
            p.loai AS category_id,
            pt.ten_loai AS cat_name,
            p.gia_von AS cost_price,
            p.so_luong_ton AS stock,
            p.ty_le_loi_nhuan AS profit_margin
            FROM product p
            LEFT JOIN product_type pt ON p.loai = pt.id
            WHERE p.id = ?");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $prod = $stmt->get_result()->fetch_assoc();

        if ($prod) {
            $prod['sell_price'] = $prod['discount_price'] ? $prod['discount_price'] : $prod['price'];
        }

        return $prod;
    }

    // Admin CRUD (stub)
    public function create($data, $file) {
        return true;
    }

    public function updateStock($product_id, $delta) {
        return false;
    }

    public function getLowStock() {
        return [];
    }
}

$products = new Products($conn);
?>