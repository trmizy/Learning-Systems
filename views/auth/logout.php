<?php
// Tệp: views/auth/logout.php

// 1. Cấu hình các biến mà Layout cần
$pageTitle = 'Đăng xuất thành công';
$pageHeader = 'Đăng Xuất'; // Layout của bạn có dòng echo $pageHeader, nên cần biến này

// 2. Bắt đầu lấy nội dung HTML
ob_start(); 
?>

<div class="text-center">
    <div class="mb-4">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <h5 class="text-success mb-3">
        <?php echo isset($thongBaoDangXuat) ? $thongBaoDangXuat : 'Đang đăng xuất...'; ?>
    </h5>

    <p class="text-muted mb-0">Hệ thống đang chuyển hướng về trang đăng nhập...</p>
    
    <script>
        setTimeout(() => {
            // Sửa đường dẫn này thành đường dẫn thực tế tới trang login của bạn
            window.location.href = '/controllers/auth/login.php'; 
        }, 2000); // 2 giây
    </script>
</div>

<?php 
// 3. Kết thúc lấy nội dung và gán vào biến $content
$content = ob_get_clean();

// 4. Gọi file Layout
// LƯU Ý QUAN TRỌNG: Kiểm tra xem file layout.php nằm ở đâu so với file logout.php này
// Nếu cùng thư mục views/auth/:
if (file_exists(__DIR__ . '/layout.php')) {
    require __DIR__ . '/layout.php';
} 
// Nếu layout nằm ở thư mục cha views/:
else if (file_exists(__DIR__ . '/../layout.php')) {
    require __DIR__ . '/../layout.php';
} else {
    echo "Lỗi: Không tìm thấy file layout.php. Kiểm tra lại đường dẫn!";
}
?>