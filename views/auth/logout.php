<?php
// Tệp: views/auth/logout.php

$pageTitle = 'Đăng xuất - Hệ thống quản lý trường THPT';
$pageHeader = 'Đăng xuất khỏi hệ thống';

ob_start(); 
?>

<div class="text-center">
    <div class="spinner-border text-primary mb-3" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mb-3">Đang đăng xuất khỏi hệ thống...</p>
    <p class="small text-muted">Bạn sẽ được chuyển hướng sau 2 giây.</p>
    
    <script>
        setTimeout(() => {
            window.location.href = '/controllers/auth/login.php';
        }, 2000);
    </script>
</div>

<?php 
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>