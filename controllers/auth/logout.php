<?php
session_start();
// THAY THẾ BẰNG ĐOẠN NÀY
    session_unset();
    session_destroy();
    $thongBaoDangXuat = "Đăng xuất thành công!";
// Use absolute path with __DIR__
require_once __DIR__ . '/../../views/auth/logout.php';  

?>
