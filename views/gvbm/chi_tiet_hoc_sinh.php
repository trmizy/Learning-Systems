<?php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['gvbm', 'gvcn', 'ttbm']);

// Tiêu đề trang
$pageTitle = 'Chi tiết học sinh - THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .student-detail-card {
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .student-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
    }
    
    .student-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .info-row {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .info-row:hover {
        background: rgba(102, 126, 234, 0.05);
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #6c757d;
    }
    
    .grade-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/public/index.php?action=gvbm-dashboard">
                    <i class="fa-solid fa-house"></i> Trang chủ
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="/public/index.php?action=lop_giang_day">Lớp giảng dạy</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/public/index.php?action=chi_tiet_lop&maLop=<?php echo urlencode($hocSinh['maLop'] ?? ''); ?>">
                    <?php echo htmlspecialchars($hocSinh['tenLop'] ?? 'Lớp'); ?>
                </a>
            </li>
            <li class="breadcrumb-item active">
                <?php echo htmlspecialchars($hocSinh['hoTen'] ?? 'Chi tiết học sinh'); ?>
            </li>
        </ol>
    </nav>

    <!-- Student Detail Card -->
    <div class="card student-detail-card mb-4">
        <!-- Header -->
        <div class="student-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?php echo $hocSinh['anhDaiDien'] ?? '/assets/images/default-avatar.png'; ?>" 
                         alt="Avatar" 
                         class="student-avatar">
                </div>
                <div class="col">
                    <h3 class="mb-2"><?php echo htmlspecialchars($hocSinh['hoTen'] ?? ''); ?></h3>
                    <p class="mb-1">
                        <i class="fa-solid fa-id-card me-2"></i>
                        Mã học sinh: <strong><?php echo htmlspecialchars($hocSinh['maHS'] ?? ''); ?></strong>
                    </p>
                    <p class="mb-0">
                        <i class="fa-solid fa-users me-2"></i>
                        Lớp: <strong><?php echo htmlspecialchars($hocSinh['tenLop'] ?? ''); ?></strong>
                        <span class="mx-3">|</span>
                        Năm học: <strong><?php echo htmlspecialchars($hocSinh['namHoc'] ?? '2024-2025'); ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Thông tin cá nhân -->
                <div class="col-lg-6">
                    <div class="p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fa-solid fa-user text-primary me-2"></i>
                            Thông tin cá nhân
                        </h5>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">Ngày sinh:</div>
                                <div class="col-8">
                                    <?php echo $hocSinh['ngaySinh'] ? date('d/m/Y', strtotime($hocSinh['ngaySinh'])) : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">Giới tính:</div>
                                <div class="col-8"><?php echo htmlspecialchars($hocSinh['gioiTinh'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">CCCD/CMND:</div>
                                <div class="col-8"><?php echo htmlspecialchars($hocSinh['soCCCD'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">Email:</div>
                                <div class="col-8">
                                    <a href="mailto:<?php echo htmlspecialchars($hocSinh['email'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($hocSinh['email'] ?? 'N/A'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">Số điện thoại:</div>
                                <div class="col-8"><?php echo htmlspecialchars($hocSinh['sdt'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">Địa chỉ:</div>
                                <div class="col-8"><?php echo htmlspecialchars($hocSinh['diaChi'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-4 info-label">Trạng thái:</div>
                                <div class="col-8">
                                    <span class="badge bg-<?php echo ($hocSinh['trangThai'] ?? '') === 'DANGHOC' ? 'success' : 'secondary'; ?>">
                                        <?php echo $hocSinh['trangThai'] === 'DANGHOC' ? 'Đang học' : 'Khác'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kết quả học tập -->
                <div class="col-lg-6 border-start">
                    <div class="p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fa-solid fa-chart-line text-success me-2"></i>
                            Kết quả học tập
                        </h5>
                        
                        <div class="info-row">
                            <div class="row align-items-center">
                                <div class="col-6 info-label">Điểm trung bình:</div>
                                <div class="col-6 text-end">
                                    <span class="grade-badge bg-primary text-white">
                                        <?php echo number_format($diemTrungBinh ?? 0, 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row align-items-center">
                                <div class="col-6 info-label">Xếp loại học lực:</div>
                                <div class="col-6 text-end">
                                    <?php
                                    $dtb = $diemTrungBinh ?? 0;
                                    $xepLoai = $dtb >= 8 ? 'Giỏi' : ($dtb >= 6.5 ? 'Khá' : ($dtb >= 5 ? 'Trung bình' : 'Yếu'));
                                    $badgeColor = $dtb >= 8 ? 'success' : ($dtb >= 6.5 ? 'primary' : ($dtb >= 5 ? 'warning' : 'danger'));
                                    ?>
                                    <span class="grade-badge bg-<?php echo $badgeColor; ?> text-white">
                                        <?php echo $xepLoai; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row align-items-center">
                                <div class="col-6 info-label">Hạnh kiểm:</div>
                                <div class="col-6 text-end">
                                    <span class="grade-badge bg-info text-white">Tốt</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="/public/index.php?action=xem-diem-chi-tiet&maHS=<?php echo urlencode($hocSinh['maHS'] ?? ''); ?>" 
                               class="btn btn-primary w-100 mb-2">
                                <i class="fa-solid fa-chart-bar me-2"></i>Xem bảng điểm chi tiết
                            </a>
                            <a href="/public/index.php?action=hs-xem-tkb&maHS=<?php echo urlencode($hocSinh['maHS'] ?? ''); ?>" 
                               class="btn btn-outline-success w-100">
                                <i class="fa-solid fa-calendar me-2"></i>Xem thời khóa biểu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mb-4">
        <a href="/public/index.php?action=chi_tiet_lop&maLop=<?php echo urlencode($hocSinh['maLop'] ?? ''); ?>" 
           class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
            <i class="fa-solid fa-print me-2"></i>In thông tin
        </button>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
