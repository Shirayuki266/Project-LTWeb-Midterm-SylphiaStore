<?php

require_once 'db.php';

if($conn){
    echo "✅ Kết nối database thành công";
}else{
    echo "❌ Lỗi kết nối";
}

?>