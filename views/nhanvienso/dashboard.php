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
    'admission_target'      => 0,
    'admission_registered'  => 0,
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

        // 4. Chỉ tiêu tuyển sinh (tổng tongChiTieu trong bảng chitieutuyensinh)
        $stmt = $conn->query("SELECT COALESCE(SUM(tongChiTieu), 0) FROM chitieutuyensinh");
        if ($stmt) {
            $systemStats['admission_target'] = (int) $stmt->fetchColumn();
        }

        // 5. Số lượng hồ sơ đăng ký (số thí sinh trong bảng thisinh)
        $stmt = $conn->query("SELECT COUNT(*) FROM thisinh");
        if ($stmt) {
            $systemStats['admission_registered'] = (int) $stmt->fetchColumn();
        }

        // 6. Tỷ lệ đỗ = (số trúng tuyển / chỉ tiêu) * 100
        // Hiện chưa có cột trúng tuyển nên tạm dùng số thisinh / chỉ tiêu (hoặc để 0 nếu muốn)
        if ($systemStats['admission_target'] > 0) {
            $systemStats['admission_rate'] = round(
                ($systemStats['admission_registered'] / $systemStats['admission_target']) * 100,
                1
            );
        } else {
            $systemStats['admission_rate'] = 0;
        }
    }
} catch (Exception $e) {
    // Nếu lỗi DB, giữ nguyên các giá trị default
    // error_log("SoGD Dashboard error: " . $e->getMessage());
}


// Thống kê theo trường
$schoolStats = [
    ['school' => 'THPT Lê Quý Đôn', 'students' => 2487, 'teachers' => 152, 'pass_rate' => 98.5, 'status' => 'active'],
    ['school' => 'THPT Nguyễn Huệ', 'students' => 2315, 'teachers' => 145, 'pass_rate' => 97.8, 'status' => 'active'],
    ['school' => 'THPT Trần Phú', 'students' => 2198, 'teachers' => 138, 'pass_rate' => 96.5, 'status' => 'active'],
    ['school' => 'THPT Phan Châu Trinh', 'students' => 2089, 'teachers' => 132, 'pass_rate' => 95.2, 'status' => 'active'],
];

// Dữ liệu tuyển sinh
$admissionData = [
    'pending_review' => 3,
    'approved' => 25,
    'rejected' => 2,
    'total_applications' => 15840,
];

// Thống kê điểm tuyển sinh
$scoreStats = [
    ['subject' => 'Toán', 'avg_score' => 7.8, 'highest' => 10.0, 'lowest' => 3.5],
    ['subject' => 'Văn', 'avg_score' => 7.5, 'highest' => 9.8, 'lowest' => 4.0],
    ['subject' => 'Anh', 'avg_score' => 7.2, 'highest' => 9.5, 'lowest' => 3.8],
];

// Báo cáo cần xử lý
$pendingReports = [
    ['school' => 'THPT Lê Quý Đôn', 'type' => 'Học vụ cuối năm', 'date' => '2024-03-15', 'status' => 'pending'],
    ['school' => 'THPT Nguyễn Huệ', 'type' => 'Tuyển sinh', 'date' => '2024-03-14', 'status' => 'pending'],
    ['school' => 'THPT Trần Phú', 'type' => 'Cơ sở vật chất', 'date' => '2024-03-13', 'status' => 'pending'],
];

// Thông báo quan trọng
$notifications = [
    ['title' => 'Họp triển khai kế hoạch tuyển sinh 2024', 'date' => '2024-03-25 08:00', 'type' => 'meeting', 'priority' => 'high'],
    ['title' => 'Hạn nộp báo cáo học vụ năm học 2023-2024', 'date' => '2024-03-30', 'type' => 'deadline', 'priority' => 'high'],
    ['title' => 'Kiểm tra định kỳ các trường THPT', 'date' => '2024-04-05', 'type' => 'inspection', 'priority' => 'medium'],
];
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
                        <a href="/modules/sogd/admission/upload.php" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-upload me-1"></i>Upload điểm
                        </a>
                        <a href="/modules/sogd/admission/results.php" class="btn btn-outline-primary btn-sm">
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
                        <a href="/modules/sogd/schools/accounts.php" class="btn btn-outline-success btn-sm">
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
                        <a href="/modules/sogd/reports/dashboard.php" class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-chart-line me-1"></i>Dashboard
                        </a>
                        <a href="/modules/sogd/reports/export.php" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-file-export me-1"></i>Xuất file
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
                        <a href="/controllers/nhanvienso/targetsController.php" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-chart-bar me-1"></i>Phân bổ chỉ tiêu
                        </a>
                        <a href="/modules/sogd/targets/planning.php" class="btn btn-outline-info btn-sm">
                            <i class="fa-solid fa-clipboard-list me-1"></i>Kế hoạch
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Admission Overview -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                        Tổng quan tuyển sinh
                    </h5>
                    
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Chỉ tiêu tuyển sinh</span>
                            <span class="badge bg-primary"><?php echo number_format($systemStats['admission_target']); ?></span>
                        </div>
                        <div class="progress-custom">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Số lượng đăng ký</span>
                            <span class="badge bg-success"><?php echo number_format($systemStats['admission_registered']); ?></span>
                        </div>
                        <div class="progress-custom">
                            <div class="progress-bar" style="width: <?php echo ($systemStats['admission_registered']/$systemStats['admission_target'])*100; ?>%; background: linear-gradient(90deg, #11998e, #38ef7d);"></div>
                        </div>
                        <div class="small text-muted mt-2">
                            Vượt chỉ tiêu <?php echo number_format($systemStats['admission_registered'] - $systemStats['admission_target']); ?> học sinh
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-3">
                            <div class="text-center">
                                <div class="admission-badge bg-warning text-dark"><?php echo $admissionData['pending_review']; ?></div>
                                <div class="small text-muted mt-1">Chờ duyệt</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <div class="admission-badge bg-success text-white"><?php echo $admissionData['approved']; ?></div>
                                <div class="small text-muted mt-1">Đã duyệt</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <div class="admission-badge bg-danger text-white"><?php echo $admissionData['rejected']; ?></div>
                                <div class="small text-muted mt-1">Từ chối</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <div class="admission-badge bg-info text-white"><?php echo number_format($admissionData['total_applications']); ?></div>
                                <div class="small text-muted mt-1">Tổng hồ sơ</div>
                            </div>
                        </div>
                    </div>

                    <a href="/modules/sogd/admission/manage.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-cog me-2"></i>Quản lý tuyển sinh
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Schools by Performance -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-trophy text-warning me-2"></i>
                        Trường đạt thành tích cao
                    </h5>
                    <?php foreach ($schoolStats as $school): ?>
                    <div class="school-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($school['school']); ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-users me-1"></i><?php echo number_format($school['students']); ?> HS
                                    <span class="mx-2">|</span>
                                    <i class="fa-solid fa-chalkboard-user me-1"></i><?php echo $school['teachers']; ?> GV
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success mb-1">Đỗ: <?php echo $school['pass_rate']; ?>%</div>
                            </div>
                        </div>
                        <div class="progress-custom">
                            <div class="progress-bar" style="width: <?php echo $school['pass_rate']; ?>%; background: linear-gradient(90deg, #11998e, #38ef7d);"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/sogd/schools/rankings.php" class="btn btn-outline-warning w-100 mt-3">
                        <i class="fa-solid fa-ranking-star me-2"></i>Xem bảng xếp hạng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports & Notifications -->
    <div class="row g-4 mt-2">
        <!-- Score Statistics -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-chart-bar text-success me-2"></i>
                        Thống kê điểm tuyển sinh
                    </h5>
                    <?php foreach ($scoreStats as $score): ?>
                    <div class="chart-card">
                        <div class="fw-semibold mb-2"><?php echo htmlspecialchars($score['subject']); ?></div>
                        <div class="row g-2 small">
                            <div class="col-4">
                                <div class="text-muted">Trung bình</div>
                                <div class="fw-bold text-primary"><?php echo $score['avg_score']; ?></div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">Cao nhất</div>
                                <div class="fw-bold text-success"><?php echo $score['highest']; ?></div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted">Thấp nhất</div>
                                <div class="fw-bold text-danger"><?php echo $score['lowest']; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/sogd/statistics/scores.php" class="btn btn-outline-success w-100 mt-3">
                        <i class="fa-solid fa-chart-line me-2"></i>Chi tiết thống kê
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Reports -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-file-lines text-warning me-2"></i>
                        Báo cáo chờ xử lý (<?php echo count($pendingReports); ?>)
                    </h5>
                    <?php foreach ($pendingReports as $report): ?>
                    <div class="report-item">
                        <div class="fw-semibold mb-1"><?php echo htmlspecialchars($report['school']); ?></div>
                        <div class="small text-muted mb-2">
                            <?php echo htmlspecialchars($report['type']); ?>
                            <span class="mx-2">|</span>
                            <i class="fa-solid fa-calendar me-1"></i><?php echo $report['date']; ?>
                        </div>
                        <button class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-eye me-1"></i>Xem báo cáo
                        </button>
                    </div>
                    <?php endforeach; ?>
                    <a href="/modules/sogd/reports/pending.php" class="btn btn-outline-warning w-100 mt-3">
                        <i class="fa-solid fa-list me-2"></i>Xem tất cả
                    </a>
                </div>
            </div>
        </div>

        <!-- Important Notifications -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-bell text-info me-2"></i>
                        Lịch trình quan trọng
                    </h5>
                    <div class="notification-timeline">
                        <?php foreach ($notifications as $notif): ?>
                        <div class="timeline-item priority-<?php echo $notif['priority']; ?>">
                            <div class="fw-semibold mb-1"><?php echo htmlspecialchars($notif['title']); ?></div>
                            <div class="small text-muted">
                                <i class="fa-solid fa-clock me-1"></i><?php echo $notif['date']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/modules/sogd/calendar.php" class="btn btn-outline-info w-100 mt-3">
                        <i class="fa-solid fa-calendar me-2"></i>Xem lịch đầy đủ
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
                    <a href="/modules/sogd/data/import.php" class="btn btn-outline-success w-100 mb-2">
                        <i class="fa-solid fa-file-import me-2"></i>Import Excel
                    </a>
                    <a href="/modules/sogd/data/export.php" class="btn btn-outline-primary w-100">
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
                    <a href="/modules/sogd/public/scores.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fa-solid fa-trophy me-2"></i>Điểm chuẩn
                    </a>
                    <a href="/modules/sogd/public/results.php" class="btn btn-outline-success w-100">
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
                    <a href="/modules/sogd/charts/overview.php" class="btn btn-outline-warning w-100">
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
                    <a href="/modules/sogd/validation/check.php" class="btn btn-outline-danger w-100">
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