<?php

require_once "db.php";
require_once "Products.php";

$productsObj = new Products($conn);

$data = $productsObj->getProducts();

echo json_encode($data);