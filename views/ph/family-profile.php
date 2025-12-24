<?php
// Tiêu đề trang
$pageTitle = 'Quản lý gia đình - THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .family-profile {
        animation: fadeIn 0.5s ease;
    }
    
    .parent-info-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    
    .child-profile-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .child-profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .child-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        padding: 1.5rem;
        border-bottom: 3px solid #667eea;
    }
    
    .child-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: bold;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .child-body {
        padding: 1.5rem;
    }
    
    .info-row {
        padding: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.3s ease;
    }
    
    .info-row:hover {
        background: rgba(102, 126, 234, 0.05);
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .stat-badge {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 600;
        display: inline-block;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-badge.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stat-badge.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .stat-badge.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .btn-view-detail {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-view-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .no-children {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    
    .no-children i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<div class="family-profile">
    <!-- Parent Info Banner -->
    <div class="parent-info-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-users me-2"></i>
                    Quản lý gia đình
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-user me-2"></i>Phụ huynh: <strong><?php echo htmlspecialchars($thongTinPH['hoTen'] ?? 'N/A'); ?></strong>
                    <span class="mx-3">|</span>
                    <i class="fa-solid fa-children me-2"></i>Số con em: <strong><?php echo count($danhSachCon); ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/public/index.php?action=ph-dashboard" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left me-2"></i>Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Danh sách con -->
    <?php if (empty($danhSachCon)): ?>
        <div class="card child-profile-card">
            <div class="card-body no-children">
                <i class="fa-solid fa-user-slash d-block"></i>
                <h5>Chưa có thông tin con em</h5>
                <p class="text-muted">Vui lòng liên hệ nhà trường để cập nhật thông tin.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($danhSachCon as $con): ?>
        <div class="card child-profile-card">
            <!-- Header -->
            <div class="child-header">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="child-avatar-large mx-auto">
                            <?php echo strtoupper(mb_substr($con['hoTen'], 0, 1, 'UTF-8')); ?>
                        </div>
                    </div>
                    <div class="col-md-7 mt-3 mt-md-0">
                        <h4 class="mb-2 fw-bold">
                            <?php echo htmlspecialchars($con['hoTen']); ?>
                            <?php if ($con['gioiTinh'] == 'Nam'): ?>
                                <i class="fa-solid fa-mars text-primary"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-venus text-danger"></i>
                            <?php endif; ?>
                        </h4>
                        <p class="mb-1">
                            <span class="badge bg-primary me-2">
                                <i class="fa-solid fa-id-card me-1"></i><?php echo htmlspecialchars($con['maHS']); ?>
                            </span>
                            <span class="badge bg-success me-2">
                                <i class="fa-solid fa-users me-1"></i><?php echo htmlspecialchars($con['tenLop'] ?? 'Chưa có lớp'); ?>
                            </span>
                            <span class="status-badge status-active">
                                <i class="fa-solid fa-circle-check me-1"></i>Đang học
                            </span>
                        </p>
                        <!-- Thống kê nhanh -->
                        <div class="mt-2">
                            <span class="stat-badge primary">
                                <i class="fa-solid fa-book me-1"></i><?php echo $con['thongKe']['soMonHoc']; ?> môn
                            </span>
                            <span class="stat-badge success">
                                <i class="fa-solid fa-star me-1"></i>ĐTB: <?php echo $con['thongKe']['diemTB']; ?>
                            </span>
                            <span class="stat-badge info">
                                <i class="fa-solid fa-file-lines me-1"></i><?php echo $con['thongKe']['soDonXinPhep']; ?> đơn
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Body -->
            <div class="child-body">
                <div class="row">
                    <!-- Thông tin cá nhân -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">
                            <i class="fa-solid fa-user text-primary me-2"></i>Thông tin cá nhân
                        </h6>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-cake-candles me-2"></i>Ngày sinh
                                </span>
                                <span class="fw-semibold"><?php echo date('d/m/Y', strtotime($con['ngaySinh'])); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-envelope me-2"></i>Email
                                </span>
                                <span class="fw-semibold"><?php echo htmlspecialchars($con['email'] ?? 'Chưa có'); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-phone me-2"></i>Số điện thoại
                                </span>
                                <span class="fw-semibold"><?php echo htmlspecialchars($con['sdt'] ?? 'Chưa có'); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-location-dot me-2"></i>Địa chỉ
                                </span>
                                <span class="fw-semibold text-end"><?php echo htmlspecialchars($con['diaChi'] ?? 'Chưa có'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin học tập -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">
                            <i class="fa-solid fa-graduation-cap text-success me-2"></i>Thông tin học tập
                        </h6>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-layer-group me-2"></i>Khối
                                </span>
                                <span class="fw-semibold">Khối <?php echo htmlspecialchars($con['khoi'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-calendar me-2"></i>Năm học
                                </span>
                                <span class="fw-semibold"><?php echo htmlspecialchars($con['namHoc'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-chalkboard-user me-2"></i>GVCN
                                </span>
                                <span class="fw-semibold"><?php echo htmlspecialchars($con['tenGVCN'] ?? 'Chưa có'); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">
                                    <i class="fa-solid fa-phone me-2"></i>SĐT GVCN
                                </span>
                                <span class="fw-semibold"><?php echo htmlspecialchars($con['sdtGVCN'] ?? 'Chưa có'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <hr class="my-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="/public/index.php?action=ph-xem-diem&maHS=<?php echo htmlspecialchars($con['maHS']); ?>" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-chart-bar me-1"></i>Xem điểm
                    </a>
                    <a href="/public/index.php?action=ph-xem-tkb&maHS=<?php echo htmlspecialchars($con['maHS']); ?>" 
                       class="btn btn-sm btn-outline-success">
                        <i class="fa-solid fa-calendar me-1"></i>Thời khóa biểu
                    </a>
                    <a href="/public/index.php?action=ph-leave-create&maHS=<?php echo htmlspecialchars($con['maHS']); ?>" 
                       class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-file-medical me-1"></i>Tạo đơn xin nghỉ
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;"
                       class="btn btn-sm btn-outline-info">
                        <i class="fa-solid fa-comments me-1"></i>Liên hệ GVCN
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
