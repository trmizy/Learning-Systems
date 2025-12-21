<?php
// filepath: d:\Hk1_2025\PTUD_Nhom4\Đồ Án Nhóm\Learning_System\views\nhanvienso\dashboard.php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['nhanvienso']);

// Tiêu đề trang và header chung
$pageTitle = 'Sở Giáo dục & Đào tạo - THPT';
require_once __DIR__ . '/../layouts/header.php';

// Lấy user hiện tại
$user = current_user() ?: [];
$fullName = isset($user['full_name']) ? $user['full_name'] : 'Nhân viên Sở';
$position = isset($user['position']) ? $user['position'] : 'Chuyên viên';
$department = isset($user['department']) ? $user['department'] : 'Phòng Giáo dục THPT';

// Lấy user hiện tại
$user = current_user() ?: [];
$fullName   = isset($user['full_name']) ? $user['full_name'] : 'Nhân viên Sở';
$position   = isset($user['position']) ? $user['position'] : 'Chuyên viên';
$department = isset($user['department']) ? $user['department'] : 'Phòng Giáo dục THPT';

// Kết nối DB
require_once __DIR__ . '/../../config/database.php';

// Số liệu thống kê toàn hệ thống (default = 0, sẽ override bằng dữ liệu thật)
$systemStats = [
    'total_schools'         => 0,
    'total_students'        => 0,
    'total_teachers'        => 0,
    'total_thisinh'         => 0, // ⚠️ THAY ĐỔI: Tổng số thí sinh
    'thisinh_registered'    => 0, // ⚠️ THAY ĐỔI: Thí sinh đã đăng ký nguyện vọng
    'admission_rate'        => 0, // %
];

try {
    if (class_exists('Database')) {
        $conn = Database::getInstance()->getConnection();

        // 1. Tổng số trường THPT
        $stmt = $conn->query("SELECT COUNT(*) FROM truong");
        if ($stmt) {
            $systemStats['total_schools'] = (int) $stmt->fetchColumn();
        }

        // 2. Tổng số học sinh
        $stmt = $conn->query("SELECT COUNT(*) FROM hocsinh");
        if ($stmt) {
            $systemStats['total_students'] = (int) $stmt->fetchColumn();
        }

        // 3. Tổng số giáo viên (theo bảng giaovienbomon)
        $stmt = $conn->query("SELECT COUNT(*) FROM giaovienbomon");
        if ($stmt) {
            $systemStats['total_teachers'] = (int) $stmt->fetchColumn();
        }

        // ⚠️ THAY ĐỔI 4: Tổng số thí sinh
        $stmt = $conn->query("SELECT COUNT(*) FROM thisinh");
        if ($stmt) {
            $systemStats['total_thisinh'] = (int) $stmt->fetchColumn();
        }

        // ⚠️ THAY ĐỔI 5: Số thí sinh đã đăng ký nguyện vọng (DISTINCT từ bảng nguyenvong)
        $stmt = $conn->query("SELECT COUNT(DISTINCT maThiSinh) FROM nguyenvong");
        if ($stmt) {
            $systemStats['thisinh_registered'] = (int) $stmt->fetchColumn();
        }

        // 6. Tỷ lệ đăng ký = (số thí sinh đã đăng ký NV / tổng thí sinh) * 100
        if ($systemStats['total_thisinh'] > 0) {
            $systemStats['admission_rate'] = round(
                ($systemStats['thisinh_registered'] / $systemStats['total_thisinh']) * 100,
                1
            );
        } else {
            $systemStats['admission_rate'] = 0;
        }
    }
} catch (Exception $e) {
    // Nếu lỗi DB, giữ nguyên các giá trị default
    error_log("SoGD Dashboard error: " . $e->getMessage());
}

// ⚠️ XÓA: Không cần $admissionData nữa

// ⚠️ THAY ĐỔI: Lấy thống kê điểm từ bảng THISINH
$scoreStats = [
    'highest_score' => 0,
    'avg_score' => 0,
    'count_below' => 0,
    'count_above' => 0,
    'year' => date('Y'), // Năm hiện tại
];

try {
    if (class_exists('Database')) {
        $conn = Database::getInstance()->getConnection();

        // Lấy thống kê từ bảng thisinh
        $stmtScores = $conn->query("
            SELECT 
                MAX(diem) as highest_score,
                ROUND(AVG(diem), 2) as avg_score,
                SUM(CASE WHEN diem < 20 THEN 1 ELSE 0 END) as count_below,
                SUM(CASE WHEN diem >=20 THEN 1 ELSE 0 END) as count_above,
                YEAR(CURDATE()) as year
            FROM thisinh
            WHERE diem IS NOT NULL
        ");
        
        if ($stmtScores) {
            $result = $stmtScores->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $scoreStats = [
                    'highest_score' => $result['highest_score'] ?? 0,
                    'avg_score' => $result['avg_score'] ?? 0,
                    'count_below' => $result['count_below'] ?? 0,
                    'count_above' => $result['count_above'] ?? 0,
                    'year' => $result['year'] ?? date('Y'),
                ];
            }
        }
    }
} catch (Exception $e) {
    error_log("Dashboard score stats error: " . $e->getMessage());
}
?>

<style>
    .sogd-dashboard {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: all 0.5s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
    }
    
    .stat-card:hover::before {
        top: -20%;
        right: -20%;
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-card.orange {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .stat-card p {
        margin: 0;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }
    
    .feature-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background: white;
        overflow: hidden;
        position: relative;
    }
    
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .feature-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .welcome-banner {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 2.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(44, 62, 80, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    
    .welcome-banner h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .department-badge {
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        display: inline-block;
        margin-top: 0.5rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .school-item {
        padding: 1.25rem;
        border-radius: 12px;
        background: white;
        border: 2px solid #e9ecef;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .school-item:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateX(5px);
    }
    
    .chart-card {
        padding: 1.5rem;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        border-left: 4px solid #667eea;
        margin-bottom: 1rem;
    }
    
    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: hidden;
    }
    
    .progress-custom .progress-bar {
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .report-item {
        padding: 1rem;
        background: white;
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .report-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .admission-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .notification-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .notification-timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, #667eea, #764ba2);
    }
    
    .timeline-item {
        position: relative;
        padding: 1rem 0;
        padding-left: 1.5rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -0.25rem;
        top: 1.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
    }
    
    .timeline-item.priority-high::before {
        border-color: #f5576c;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 87, 108, 0.7); }
        50% { box-shadow: 0 0 0 10px rgba(245, 87, 108, 0); }
    }
</style>

<div class="sogd-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-building-columns me-2"></i>
                    Sở Giáo dục & Đào tạo
                </h2>
                <p class="mb-2">
                    <i class="fa-solid fa-user me-2"></i><?php echo htmlspecialchars($fullName); ?>
                    <span class="mx-2">-</span>
                    <i class="fa-solid fa-briefcase me-2"></i><?php echo htmlspecialchars($position); ?>
                </p>
                <span class="department-badge">
                    <i class="fa-solid fa-building me-2"></i><?php echo htmlspecialchars($department); ?>
                </span>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex flex-column gap-2">
                    <span class="text-white">
                        <i class="fa-solid fa-calendar me-2"></i>
                        Năm học: 2023-2024
                    </span>
                    <span class="text-white">
                        <i class="fa-solid fa-clock me-2"></i>
                        <span id="currentTime"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- System Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $systemStats['total_schools']; ?></h3>
                        <p><i class="fa-solid fa-school me-2"></i>Trường THPT</p>
                    </div>
                    <i class="fa-solid fa-building fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($systemStats['total_students']); ?></h3>
                        <p><i class="fa-solid fa-user-graduate me-2"></i>Học sinh</p>
                    </div>
                    <i class="fa-solid fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($systemStats['total_teachers']); ?></h3>
                        <p><i class="fa-solid fa-chalkboard-user me-2"></i>Giáo viên</p>
                    </div>
                    <i class="fa-solid fa-user-tie fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card orange">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $systemStats['admission_rate']; ?>%</h3>
                        <p><i class="fa-solid fa-graduation-cap me-2"></i>Tỷ lệ đỗ</p>
                    </div>
                    <i class="fa-solid fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions Grid -->
    <div class="row g-4 mb-4">
        <!-- Quản lý tuyển sinh -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-file-arrow-up"></i>
                    </div>
                    <h5 class="card-title fw-bold">Tuyển sinh</h5>
                    <p class="text-muted small">Upload điểm, công bố kết quả</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/public/index.php?action=nhanvienso-tuyen-sinh-upload" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-upload me-1"></i>Upload điểm
                        </a>
                        <a href="/public/index.php?action=nhanvienso-tuyen-sinh-list" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-list me-1"></i>Kết quả
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quản lý trường -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-school-flag"></i>
                    </div>
                    <h5 class="card-title fw-bold">Quản lý trường</h5>
                    <p class="text-muted small">Giám sát, cấp tài khoản</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/modules/sogd/schools/list.php" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-building me-1"></i>Danh sách
                        </a>
                        <a href="/public/index.php?action=schoolAccount_nhanvienso" class="btn btn-outline-success btn-sm">
                            <i class="fa-solid fa-user-plus me-1"></i>Tài khoản
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Báo cáo & Thống kê -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h5 class="card-title fw-bold">Báo cáo</h5>
                    <p class="text-muted small">Thống kê, xuất báo cáo</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="index.php?action=xem_bao_cao" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-chart-line me-1"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chỉ tiêu & Kế hoạch -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h5 class="card-title fw-bold">Chỉ tiêu</h5>
                    <p class="text-muted small">Kế hoạch, chỉ tiêu tuyển sinh</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="/public/index.php?action=targets_nhanvienso" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-chart-bar me-1"></i>Phân bổ chỉ tiêu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admission Overview & Score Statistics - GỘP THÀNH 1 HÀNG -->
    <div class="row g-4">
        <!-- Tổng quan tuyển sinh -->
        <div class="col-lg-6">
            <div class="card feature-card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                        Tổng quan tuyển sinh
                    </h5>
                    
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Số lượng thí sinh</span>
                            <span class="badge bg-primary"><?php echo number_format($systemStats['total_thisinh']); ?></span>
                        </div>
                        <div class="progress-custom">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Số thí sinh đã đăng ký nguyện vọng</span>
                            <span class="badge bg-success"><?php echo number_format($systemStats['thisinh_registered']); ?></span>
                        </div>
                        <div class="progress-custom">
                            <?php 
                            $percentage = $systemStats['total_thisinh'] > 0 
                                ? ($systemStats['thisinh_registered']/$systemStats['total_thisinh'])*100 
                                : 0;
                            ?>
                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, #11998e, #38ef7d);"></div>
                        </div>
                        <div class="small text-muted mt-2">
                            Tỷ lệ đăng ký: <?php echo $systemStats['admission_rate']; ?>%
                        </div>
                    </div>

                    <a href="/public/index.php?action=nhanvienso-tuyen-sinh-list" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-cog me-2"></i>Quản lý tuyển sinh
                    </a>
                </div>
            </div>
        </div>

        <!-- Thống kê điểm thi -->
        <div class="col-lg-6">
            <div class="card feature-card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-chart-bar text-success me-2"></i>
                        Thống kê điểm thi (Năm <?php echo $scoreStats['year']; ?>)
                    </h5>
                    
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fa-solid fa-trophy me-2"></i>Điểm cao nhất
                            </span>
                            <span class="badge bg-success px-3 py-2 fs-5">
                                <?php echo number_format($scoreStats['highest_score'], 1); ?>
                            </span>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fa-solid fa-calculator me-2"></i>Điểm trung bình
                            </span>
                            <span class="badge bg-primary px-3 py-2 fs-5">
                                <?php echo number_format($scoreStats['avg_score'], 2); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div class="chart-card bg-danger bg-opacity-10 border-danger">
                                <div class="text-center">
                                    <div class="small text-muted mb-1">
                                        <i class="fa-solid fa-arrow-down me-1"></i>Điểm dưới 20
                                    </div>
                                    <div class="fs-4 fw-bold text-danger">
                                        <?php echo number_format($scoreStats['count_below']); ?>
                                    </div>
                                    <div class="small text-muted">thí sinh</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="chart-card bg-success bg-opacity-10 border-success">
                                <div class="text-center">
                                    <div class="small text-muted mb-1">
                                        <i class="fa-solid fa-arrow-up me-1"></i>Điểm từ 20 trở lên
                                    </div>
                                    <div class="fs-4 fw-bold text-success">
                                        <?php echo number_format($scoreStats['count_above']); ?>
                                    </div>
                                    <div class="small text-muted">thí sinh</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="/public/index.php?action=thong-ke-diem" class="btn btn-outline-success w-100 mt-3">
                        <i class="fa-solid fa-chart-line me-2"></i>Chi tiết thống kê
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Tools -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-file-excel text-success me-2"></i>Import/Export dữ liệu
                    </h6>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-success w-100 mb-2">
                        <i class="fa-solid fa-file-import me-2"></i>Import Excel
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-file-export me-2"></i>Export Báo cáo
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-globe text-primary me-2"></i>Công bố thông tin
                    </h6>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fa-solid fa-trophy me-2"></i>Điểm chuẩn
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-success w-100">
                        <i class="fa-solid fa-list me-2"></i>Kết quả TS
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-chart-column text-warning me-2"></i>Biểu đồ & Thống kê
                    </h6>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-warning w-100">
                        <i class="fa-solid fa-chart-pie me-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-shield-halved text-danger me-2"></i>Kiểm tra dữ liệu
                    </h6>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-danger w-100">
                        <i class="fa-solid fa-check-double me-2"></i>Xác minh
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN');
        document.getElementById('currentTime').textContent = timeString;
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>