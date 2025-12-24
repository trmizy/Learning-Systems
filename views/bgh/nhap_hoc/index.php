<?php
$pageTitle = 'Nhập học tự động';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa-solid fa-user-plus me-2 text-primary"></i>Nhập học tự động</h2>
                <a href="/public/index.php" class="btn btn-secondary">
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

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa-solid fa-check-circle me-2"></i>
                    <?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Thống kê tổng quan -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-user-check fa-3x text-success mb-3"></i>
                            <h3 class="mb-0"><?php echo $readyCheck['soThiSinhDau'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Thí sinh đậu chờ nhập học</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-school fa-3x text-primary mb-3"></i>
                            <h3 class="mb-0"><?php echo $readyCheck['soLopKhoi10'] ?? 0; ?></h3>
                            <p class="text-muted mb-0">Lớp khối 10 sẵn sàng</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fa-solid fa-users fa-3x text-info mb-3"></i>
                            <h3 class="mb-0"><?php echo ($readyCheck['soLopKhoi10'] ?? 0) * 30; ?></h3>
                            <p class="text-muted mb-0">Chỗ trống tối đa</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kiểm tra điều kiện -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-clipboard-check me-2"></i>Kiểm tra điều kiện nhập học</h5>
                </div>
                <div class="card-body">
                    <?php if ($readyCheck['ready']): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            <strong>Sẵn sàng nhập học!</strong> Tất cả điều kiện đều đã đáp ứng.
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

            <!-- Thống kê lớp khối 10 -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-door-open me-2"></i>Thống kê lớp khối 10</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã lớp</th>
                                    <th>Tên lớp</th>
                                    <th>Sĩ số hiện tại</th>
                                    <th>Chỗ trống</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($thongKeLop)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Chưa có lớp khối 10 nào trong hệ thống
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($thongKeLop as $lop): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lop['maLop']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($lop['tenLop']); ?></strong></td>
                                        <td><?php echo $lop['siSo']; ?> / 30</td>
                                        <td>
                                            <span class="badge bg-<?php echo $lop['choTrong'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $lop['choTrong']; ?> chỗ
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($lop['siSo'] >= 30): ?>
                                                <span class="badge bg-danger">Đầy</span>
                                            <?php elseif ($lop['siSo'] >= 25): ?>
                                                <span class="badge bg-warning">Gần đầy</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Còn chỗ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Danh sách thí sinh đậu -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>Danh sách thí sinh đậu (Top 100)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Mã thí sinh</th>
                                    <th>Họ tên</th>
                                    <th>Điểm</th>
                                    <th>Trường trúng tuyển</th>
                                    <th>NV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($thiSinhDau)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Không có thí sinh đậu nào chờ nhập học
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($thiSinhDau as $index => $ts): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($ts['maThiSinh']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($ts['hoTen']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-success fs-6"><?php echo number_format($ts['diem'], 2); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($ts['tenTruong']); ?></td>
                                        <td>NV<?php echo $ts['thuTuUuTien']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Nút xác nhận nhập học -->
            <div class="card">
                <div class="card-body text-center p-5">
                    <i class="fa-solid fa-graduation-cap fa-4x text-primary mb-4"></i>
                    <h4 class="mb-3">Xác nhận nhập học tự động</h4>
                    <p class="text-muted mb-4">
                        Hệ thống sẽ tự động:<br>
                        1. Chuyển thí sinh đậu → Học sinh khối 10<br>
                        2. Phân lớp thông minh theo điểm (cao → thấp)<br>
                        3. Tạo tài khoản tự động (username = maHS, password = 123456)<br>
                        4. Cập nhật sĩ số từng lớp
                    </p>
                    
                    <form method="POST" action="/public/index.php?action=bgh-nhap-hoc-xac-nhan" 
                          onsubmit="return confirm('⚠️ Xác nhận chạy nhập học tự động?\n\nQuá trình này không thể hoàn tác!');">
                        <button type="submit" 
                                class="btn btn-primary btn-lg px-5" 
                                <?php echo !$readyCheck['ready'] ? 'disabled' : ''; ?>>
                            <i class="fa-solid fa-play me-2"></i>Bắt đầu nhập học
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
