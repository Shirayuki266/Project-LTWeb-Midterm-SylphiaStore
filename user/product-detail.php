<?php
session_start();
require_once '../api/db.php';
require_once '../api/auth.php';
require_once '../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if(!$product){
    header("Location: products.php");
    exit;
}

$auth = new Auth($conn);
$isLoggedIn = $auth->isLoggedIn();

/* related products */
$related = [];

$stmt = $conn->prepare("
SELECT * FROM products 
WHERE category_id=? AND id!=?
LIMIT 4
");

$stmt->bind_param("ii",$product['category_id'],$id);
$stmt->execute();

$res = $stmt->get_result();

while($row=$res->fetch_assoc()){
    $related[]=$row;
}
?>

<?php
$page_title = $product['name'];
$current_page = 'product-detail';
include 'header.php';
?>

<div class="container py-5">

  <div class="row g-5">

    <!-- IMAGE -->
    <div class="col-lg-6">

      <div class="card border-0 shadow-sm">

        <img id="mainImage" src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid p-4"
          style="height:420px;object-fit:contain">

      </div>

      <div class="d-flex gap-2 mt-3">

        <img src="<?php echo htmlspecialchars($product['image']); ?>" style="width:70px;height:70px;object-fit:contain"
          class="border rounded p-1" onclick="changeImage(this.src)">

      </div>

    </div>


    <!-- INFO -->
    <div class="col-lg-6">

      <h2 class="fw-bold mb-3">
        <?php echo htmlspecialchars($product['name']); ?>
      </h2>

      <div class="mb-3 text-warning fs-5">
        ⭐ ⭐ ⭐ ⭐ ⭐
        <span class="text-muted fs-6">(5.0)</span>
      </div>

      <div class="fs-2 fw-bold text-primary mb-4">
        <?php echo formatPrice($product['price']); ?>
      </div>

      <div class="mb-3">
        <i class="fas fa-box me-2"></i>
        <?php if($product['stock']>0): ?>
        Còn <b><?php echo $product['stock']; ?></b> sản phẩm
        <?php else: ?>
        <span class="text-danger">Hết hàng</span>
        <?php endif; ?>
      </div>

      <hr>

      <div class="mb-4">

        <h5>Mô tả</h5>

        <p class="text-muted">
          <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>

      </div>


      <!-- QUANTITY -->
      <div class="d-flex align-items-center mb-4">

        <span class="me-3">Số lượng:</span>

        <button class="btn btn-outline-secondary" onclick="changeQty(-1)">-</button>

        <input id="qty" value="1" class="form-control text-center mx-2" style="width:70px" readonly>

        <button class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>

      </div>


      <!-- BUTTONS -->
      <div class="d-flex gap-3">

        <button class="btn btn-primary" onclick="addToCart()">

          <i class="fas fa-cart-plus me-2"></i>
          Thêm vào giỏ

        </button>

        <button class="btn btn-success" onclick="buyNow()">

          <i class="fas fa-bolt me-2"></i>
          Mua ngay

        </button>

      </div>

    </div>

  </div>

</div>


<!-- RELATED PRODUCTS -->

<?php if(!empty($related)): ?>

<div class="container pb-5">

  <h3 class="mb-4">Sản phẩm liên quan</h3>

  <div class="row g-4">

    <?php foreach($related as $p): ?>

    <div class="col-md-3">

      <div class="card h-100 shadow-sm border-0">

        <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top p-3"
          style="height:200px;object-fit:contain">

        <div class="card-body">

          <h6 class="fw-semibold">
            <?php echo htmlspecialchars($p['name']); ?>
          </h6>

          <div class="text-primary fw-bold mb-2">
            <?php echo formatPrice($p['price']); ?>
          </div>

          <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary btn-sm">

            <i class="fas fa-eye"></i>
            Chi tiết

          </a>

        </div>

      </div>

    </div>

    <?php endforeach; ?>

  </div>

</div>

<?php endif; ?>


<?php include 'footer.php'; ?>


<script>
function changeImage(src) {

  document.getElementById("mainImage").src = src

}

function changeQty(delta) {

  let input = document.getElementById("qty")

  let qty = parseInt(input.value)

  qty += delta

  if (qty < 1) qty = 1

  input.value = qty

}

function addToCart() {

  let qty = document.getElementById("qty").value

  fetch("../api/cart.php", {

      method: "POST",

      headers: {
        "Content-Type": "application/json"
      },

      body: JSON.stringify({
        action: "add",
        id: <?php echo $id;?>,
        qty: parseInt(qty)
      })

    })
    .then(res => res.json())
    .then(data => {

      if (data.success) {

        alert("Đã thêm vào giỏ hàng")

      } else {

        alert("Lỗi thêm giỏ")

      }

    })

}

function buyNow() {

  addToCart()

  setTimeout(() => {
    window.location = "checkout.php"
  }, 400)

}
</script>