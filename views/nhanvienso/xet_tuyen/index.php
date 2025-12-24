<?php
$pageTitle = 'Xét tuyển';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Xét tuyển tự động</h2>
                <a href="/public/index.php?action=nhanvienso-dashboard" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa-solid fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Thống kê trước khi xét tuyển -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-users fa-3x text-primary mb-3"></i>
                            <h3 class="mb-0"><?php echo number_format($stats['tong_thi_sinh']); ?></h3>
                            <p class="text-muted mb-0">Tổng thí sinh</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
                            <h3 class="mb-0"><?php echo number_format($stats['tong_trung_tuyen']); ?></h3>
                            <p class="text-muted mb-0">Đã trúng tuyển</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-percentage fa-3x text-warning mb-3"></i>
                            <h3 class="mb-0"><?php echo $stats['ty_le_trung_tuyen']; ?>%</h3>
                            <p class="text-muted mb-0">Tỷ lệ trúng tuyển</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kiểm tra điều kiện -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-clipboard-check me-2"></i>Kiểm tra điều kiện</h5>
                </div>
                <div class="card-body">
                    <?php if ($readyCheck['ready']): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            <strong>Sẵn sàng xét tuyển!</strong> Tất cả điều kiện đều đã đáp ứng.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            <strong>Chưa đủ điều kiện:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($readyCheck['errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nút chạy xét tuyển -->
            <div class="card">
                <div class="card-body text-center p-5">
                    <i class="fa-solid fa-robot fa-4x text-primary mb-4"></i>
                    <h4 class="mb-3">Chạy thuật toán xét tuyển tự động</h4>
                    <p class="text-muted mb-4">
                        Hệ thống sẽ tự động xét NV1 → NV2 → NV3 theo điểm chuẩn đã thiết lập.
                    </p>
                    
                    <form method="POST" action="/public/index.php?action=nhanvienso-xet-tuyen-chay" 
                          onsubmit="return confirm('⚠️ Xác nhận chạy xét tuyển?\n\nHệ thống sẽ cập nhật trạng thái tất cả nguyện vọng.');">
                        <button type="submit" 
                                class="btn btn-primary btn-lg px-5" 
                                <?php echo !$readyCheck['ready'] ? 'disabled' : ''; ?>>
                            <i class="fa-solid fa-play me-2"></i>Bắt đầu xét tuyển
                        </button>
                    </form>

                    <?php if ($stats['tong_trung_tuyen'] > 0): ?>
                        <div class="mt-3">
                            <a href="/public/index.php?action=nhanvienso-xet-tuyen-ket-qua" class="btn btn-outline-success">
                                <i class="fa-solid fa-list me-2"></i>Xem kết quả hiện tại
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
