<?php
require_once '../api/auth.php';
$auth->logout();
header('Location: index.php');
exit;
?>