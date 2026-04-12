<?php
session_start();
header('Content-Type: application/json');

require_once '../api/db.php';
require_once '../api/auth.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn('admin')) {
    echo json_encode(['success' => false, 'message' => '❌ Chỉ admin mới được phép!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['product_image']) || !isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => '❌ Dữ liệu không hợp lệ!']);
    exit;
}

$file = $_FILES['product_image'];
$product_id = (int)$_POST['product_id'];

// 1. Kiểm tra lỗi upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => '❌ Lỗi upload: ' . $file['error']]);
    exit;
}

// 2. Validate type & size (max 5MB)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowed_mimes)) {
    echo json_encode(['success' => false, 'message' => '❌ Chỉ chấp nhận JPG, PNG, WEBP!']);
    exit;
}
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => '❌ File quá lớn (max 5MB)!']);
    exit;
}

// 3. Get extension
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
    $ext = ($mime === 'image/jpeg') ? 'jpg' : substr($mime, 6); // fallback
}

// 4. Unique name & path
$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
$new_name = 'prod_' . uniqid() . '.' . $ext;
$upload_path = $upload_dir . $new_name;
$db_path = 'uploads/' . $new_name;

// 5. Move file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => '❌ Không thể lưu file (check quyền thư mục)!']);
    exit;
}

// 6. Get & delete old image
$stmt = $conn->prepare('SELECT image FROM products WHERE id = ?');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$old_row = $result->fetch_assoc();
$old_image = $old_row ? trim($old_row['image'] ?? '') : '';

if ($old_image && $old_image !== 'no-image.png' && $old_image !== '' && strpos($old_image, 'http') !== 0 && file_exists($upload_dir . $old_image)) {
    unlink($upload_dir . $old_image);
}

// 7. Update DB
$stmt = $conn->prepare('UPDATE products SET image = ? WHERE id = ?');
$stmt->bind_param('si', $db_path, $product_id);
if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => '✅ Upload thành công!',
        'path' => $db_path
    ]);
} else {
    unlink($upload_path); // rollback
    echo json_encode(['success' => false, 'message' => '❌ Lỗi DB: ' . $conn->error]);
}
?>