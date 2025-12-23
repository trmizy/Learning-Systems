<?php
$pageTitle = 'Đăng ký nguyện vọng - Thí sinh THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .wish-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .wish-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
    }
    
    .wish-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    }
    
    .wish-card.disabled {
        opacity: 0.5;
        pointer-events: none;
        background: #f8f9fa;
    }
    
    .wish-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-approved {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    .status-rejected {
        background: #f8d7da;
        color: #842029;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-graduation-cap me-2"></i>
                        Đăng ký nguyện vọng THPT
                    </h4>
                </div>
                
                <div class="card-body">
                    <!-- Thông tin thí sinh -->
                    <div class="alert alert-info border-0 mb-4">
                        <h5 class="alert-heading">
                            <i class="fa-solid fa-user-circle me-2"></i>
                            Thông tin thí sinh
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($thongTinTS['hoTen']); ?></p>
                                <p class="mb-1"><strong>CCCD:</strong> <?php echo htmlspecialchars($thongTinTS['soCCCD']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>SĐT:</strong> <?php echo htmlspecialchars($thongTinTS['soDienThoai'] ?? 'Chưa cập nhật'); ?></p>
                                <p class="mb-1"><strong>Giới tính:</strong> <?php echo htmlspecialchars($thongTinTS['gioiTinh'] === 'M' ? 'Nam' : 'Nữ'); ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($daDangKy): ?>
                        <!-- Hiển thị nguyện vọng đã đăng ký -->
                        <div class="alert alert-success border-0 mb-4">
                            <h5 class="alert-heading">
                                <i class="fa-solid fa-check-circle me-2"></i>
                                Bạn đã đăng ký <?php echo count($nguyenVongHienTai); ?> nguyện vọng
                            </h5>
                            <p class="mb-0 small">
                                <?php 
                                $checkNext = (new NguyenVongModel())->kiemTraNguyenVongTiepTheo($maThiSinh);
                                echo htmlspecialchars($checkNext['message']); 
                                ?>
                            </p>
                        </div>

                        <?php foreach ($nguyenVongHienTai as $nv): ?>
                        <div class="wish-card active">
                            <div class="d-flex align-items-start">
                                <div class="wish-number me-3">
                                    <?php echo $nv['thuTuUuTien']; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">
                                        <?php echo htmlspecialchars($nv['tenTruong']); ?>
                                        <span class="status-badge <?php 
                                            echo $nv['trangThai'] === 'CHO_DUYET' ? 'status-pending' : 
                                                ($nv['trangThai'] === 'DA_DUYET' ? 'status-approved' : 'status-rejected');
                                        ?> ms-2">
                                            <i class="fa-solid fa-<?php 
                                                echo $nv['trangThai'] === 'CHO_DUYET' ? 'clock' : 
                                                    ($nv['trangThai'] === 'DA_DUYET' ? 'check' : 'times');
                                            ?> me-1"></i>
                                            <?php 
                                            $statusMap = [
                                                'CHO_DUYET' => 'Chờ duyệt',
                                                'DA_DUYET' => 'Đã duyệt',
                                                'TU_CHOI' => 'Từ chối'
                                            ];
                                            echo $statusMap[$nv['trangThai']] ?? $nv['trangThai'];
                                            ?>
                                        </span>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <i class="fa-solid fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($nv['diaChiTruong']); ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fa-solid fa-calendar me-2"></i>
                                        Đăng ký: <?php echo date('d/m/Y H:i', strtotime($nv['ngayDangKy'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if ($checkNext['allowed']): ?>
                            <!-- ⚠️ HIỂN THỊ FORM ĐĂNG KÝ TIẾP NGAY -->
                            <div class="alert alert-info border-0 mb-4 mt-4">
                                <i class="fa-solid fa-arrow-right me-2"></i>
                                Bạn có thể tiếp tục đăng ký <strong>nguyện vọng <?php echo $checkNext['nextPriority']; ?></strong> ngay bên dưới.
                            </div>

                            <?php if (empty($danhSachTruong)): ?>
                                <!-- ⚠️ TH: Không còn trường nào để chọn -->
                                <div class="alert alert-warning border-0 mb-4">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    Tất cả các trường đã được chọn. Bạn không thể đăng ký thêm nguyện vọng.
                                </div>
                            <?php else: ?>
                                <!-- ⚠️ Form đăng ký - CHỈ HIỂN THỊ trường chưa chọn -->
                                <form method="POST" action="/public/index.php?action=ts-dang-ky-nguyen-vong">
                                    <div class="wish-card">
                                        <div class="d-flex align-items-start">
                                            <div class="wish-number me-3"><?php echo $checkNext['nextPriority']; ?></div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-3">Nguyện vọng <?php echo $checkNext['nextPriority']; ?></h5>
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <label class="form-label">Chọn trường <span class="text-danger">*</span></label>
                                                        <select name="maTruong_<?php echo $checkNext['nextPriority']; ?>" class="form-select" required>
                                                            <option value="">-- Chọn trường --</option>
                                                            <?php foreach ($danhSachTruong as $truong): ?>
                                                            <option value="<?php echo htmlspecialchars($truong['maTruong']); ?>">
                                                                <?php echo htmlspecialchars($truong['tenTruong']); ?> 
                                                                (<?php echo htmlspecialchars($truong['diaChi']); ?>)
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="form-text">
                                                            <i class="fa-solid fa-info-circle me-1"></i>
                                                            Không được chọn trùng trường đã đăng ký ở nguyện vọng trước
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Thứ tự</label>
                                                        <input type="number" name="thuTu_<?php echo $checkNext['nextPriority']; ?>" class="form-control" value="<?php echo $checkNext['nextPriority']; ?>" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa-solid fa-paper-plane me-2"></i>Đăng ký nguyện vọng <?php echo $checkNext['nextPriority']; ?>
                                        </button>
                                        <a href="/public/index.php" class="btn btn-secondary btn-lg">
                                            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                                        </a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Đã đủ 3 nguyện vọng -->
                            <div class="alert alert-success border-0 mt-4">
                                <h5 class="alert-heading">
                                    <i class="fa-solid fa-check-double me-2"></i>
                                    Hoàn tất đăng ký
                                </h5>
                                <p class="mb-0">Bạn đã đăng ký đủ 3 nguyện vọng. Vui lòng chờ kết quả xét tuyển.</p>
                            </div>

                            <div class="mt-4">
                                <a href="/public/index.php" class="btn btn-primary">
                                    <i class="fa-solid fa-home me-2"></i>Về trang chủ
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Nút hủy nguyện vọng cuối -->
                        <?php if (count($nguyenVongHienTai) > 0): ?>
                        <div class="mt-3">
                            <form method="POST" action="/public/index.php?action=ts-huy-nguyen-vong" onsubmit="return confirm('Bạn có chắc muốn hủy nguyện vọng cuối cùng?');" class="d-inline">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fa-solid fa-trash me-2"></i>Hủy nguyện vọng cuối cùng
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Form đăng ký nguyện vọng 1 -->
                        <form method="POST" action="/public/index.php?action=ts-dang-ky-nguyen-vong">
                            <div class="alert alert-info border-0 mb-4">
                                <i class="fa-solid fa-info-circle me-2"></i>
                                Bạn chưa đăng ký nguyện vọng nào. Hãy bắt đầu với <strong>nguyện vọng 1</strong>.
                            </div>

                            <?php if (empty($danhSachTruong)): ?>
                                <!-- ⚠️ TH: Không có trường nào -->
                                <div class="alert alert-danger border-0 mb-4">
                                    <i class="fa-solid fa-exclamation-circle me-2"></i>
                                    Hiện không có trường nào để đăng ký. Vui lòng liên hệ quản trị viên.
                                </div>
                            <?php else: ?>
                                <div class="wish-card">
                                    <div class="d-flex align-items-start">
                                        <div class="wish-number me-3">1</div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-3">Nguyện vọng 1</h5>
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <label class="form-label">Chọn trường <span class="text-danger">*</span></label>
                                                    <select name="maTruong_1" class="form-select" required>
                                                        <option value="">-- Chọn trường --</option>
                                                        <?php foreach ($danhSachTruong as $truong): ?>
                                                        <option value="<?php echo htmlspecialchars($truong['maTruong']); ?>">
                                                            <?php echo htmlspecialchars($truong['tenTruong']); ?> 
                                                            (<?php echo htmlspecialchars($truong['diaChi']); ?>)
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Thứ tự</label>
                                                    <input type="number" name="thuTu_1" class="form-control" value="1" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Đăng ký nguyện vọng 1
                                    </button>
                                    <a href="/public/index.php" class="btn btn-secondary btn-lg">
                                        <i class="fa-solid fa-times me-2"></i>Hủy
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
