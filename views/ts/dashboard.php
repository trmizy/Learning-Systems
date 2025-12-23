<?php
// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['ts']); // Role thí sinh

// Lấy thông tin thí sinh
require_once __DIR__ . '/../../models/ts/DashboardModel.php';

$dashboardModel = new DashboardModel();
$user = $_SESSION['auth'] ?? [];

// Lấy thông tin thí sinh từ username
$thongTinTS = null;
if ($user && isset($user['username'])) {
    $thongTinTS = $dashboardModel->getThongTinThiSinh($user['username']);
}

$fullName = $thongTinTS ? $thongTinTS['hoTen'] : 'Thí sinh';
$candidateId = $thongTinTS ? $thongTinTS['maThiSinh'] : 'TS0000';


// Thống kê
$stats = [
    'exam_score' => $thongTinTS ? $dashboardModel->getDiemThiTongKet($thongTinTS['maThiSinh']) : 0,
    'wish_registered' => $thongTinTS ? $dashboardModel->demNguyenVongDaDangKy($thongTinTS['maThiSinh']) : 0,
    'documents_submitted' => $thongTinTS ? $dashboardModel->demHoSoDaNop($thongTinTS['maThiSinh']) : 0,
    'days_to_exam' => 45, // Tính từ ngày thi
];

$pageTitle = 'Trang thí sinh - THPT';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .candidate-dashboard {
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
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
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
    
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .feature-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
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
        margin: 0 auto 1rem;
    }
    
    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    
    .welcome-banner h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid white;
        color: white;
        background: transparent;
    }
    
    .quick-action-btn:hover {
        background: white;
        color: #667eea;
        transform: translateY(-2px);
    }
    
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 2rem;
        border-left: 2px dashed #667eea;
    }
    
    .timeline-item:last-child {
        border-left: 0;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #667eea;
    }
    
    .timeline-item.completed::before {
        background: #11998e;
    }
</style>

<div class="candidate-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-graduation-cap me-2"></i>
                    Chào mừng thí sinh, <?php echo htmlspecialchars($fullName); ?>!
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-id-card me-2"></i>Số báo danh: <?php echo htmlspecialchars($candidateId); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/public/index.php?action=ts-profile" class="quick-action-btn btn">
                    <i class="fa-solid fa-user me-2"></i>Hồ sơ của tôi
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo number_format($stats['exam_score'], 2); ?></h3>
                        <p><i class="fa-solid fa-chart-line me-2"></i>Điểm tổng kết</p>
                    </div>
                    <i class="fa-solid fa-trophy fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['wish_registered']; ?>/3</h3>
                        <p><i class="fa-solid fa-heart me-2"></i>Nguyện vọng</p>
                    </div>
                    <i class="fa-solid fa-list-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['documents_submitted']; ?></h3>
                        <p><i class="fa-solid fa-folder-open me-2"></i>Hồ sơ đã nộp</p>
                    </div>
                    <i class="fa-solid fa-file-circle-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['days_to_exam']; ?></h3>
                        <p><i class="fa-solid fa-calendar-days me-2"></i>Ngày tới kỳ thi</p>
                    </div>
                    <i class="fa-solid fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions - 3 chức năng chính -->
    <div class="row g-4 mb-4">
        <!-- Xem điểm thi -->
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-chart-bar"></i>
                    </div>
                    <h5 class="card-title fw-bold">Điểm thi</h5>
                    <p class="text-muted small">Xem điểm các môn thi THPT Quốc gia</p>
                    <a href="/public/index.php?action=ts-xem-diem" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-eye me-2"></i>Xem điểm thi
                    </a>
                </div>
            </div>
        </div>

        <!-- Đăng ký nguyện vọng -->
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <h5 class="card-title fw-bold">Nguyện vọng</h5>
                    <p class="text-muted small">Đăng ký nguyện vọng vào các trường THPT</p>
                    <a href="/public/index.php?action=ts-nguyen-vong" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Đăng ký nguyện vọng
                    </a>
                </div>
            </div>
        </div>

        <!-- Hồ sơ cá nhân -->
        <div class="col-md-4">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h5 class="card-title fw-bold">Hồ sơ cá nhân</h5>
                    <p class="text-muted small">Xem và cập nhật thông tin cá nhân</p>
                    <a href="/public/index.php?action=ts-profile" class="btn btn-danger w-100 mt-3">
                        <i class="fa-solid fa-address-card me-2"></i>Xem hồ sơ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row g-4">
        <!-- Timeline đăng ký -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-timeline text-primary me-2"></i>
                        Lộ trình đăng ký
                    </h5>
                    
                    <div class="timeline-item completed">
                        <h6 class="fw-bold text-success">
                            <i class="fa-solid fa-check-circle me-2"></i>Đăng ký tài khoản
                        </h6>
                        <p class="text-muted small mb-0">Hoàn thành đăng ký thông tin cá nhân</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h6 class="fw-bold">
                            <i class="fa-regular fa-circle me-2"></i>Xem điểm thi
                        </h6>
                        <p class="text-muted small mb-0">Tra cứu điểm thi THPT Quốc gia</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h6 class="fw-bold">
                            <i class="fa-regular fa-circle me-2"></i>Đăng ký nguyện vọng
                        </h6>
                        <p class="text-muted small mb-0">Chọn trường và ngành học mong muốn</p>
                    </div>
                    
                    <div class="timeline-item">
                        <h6 class="fw-bold">
                            <i class="fa-regular fa-circle me-2"></i>Xét tuyển
                        </h6>
                        <p class="text-muted small mb-0">Chờ kết quả xét tuyển từ trường</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hướng dẫn nhanh -->
        <div class="col-lg-6">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-circle-info text-info me-2"></i>
                        Hướng dẫn nhanh
                    </h5>
                    
                    <div class="alert alert-primary" role="alert">
                        <i class="fa-solid fa-lightbulb me-2"></i>
                        <strong>Lưu ý quan trọng:</strong> Bạn có thể đăng ký tối đa 3 nguyện vọng. Hãy sắp xếp theo thứ tự ưu tiên từ cao đến thấp.
                    </div>
                    
                    <div class="list-group">
                        <a href="/public/index.php?action=ts-huong-dan" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-book-open text-primary me-2"></i>
                            Hướng dẫn đăng ký nguyện vọng
                        </a>
                        <a href="/public/index.php?action=ts-diem-chuan" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-chart-line text-success me-2"></i>
                            Xem điểm chuẩn các trường
                        </a>
                        <a href="/public/index.php?action=ts-chi-tieu" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-users text-warning me-2"></i>
                            Chỉ tiêu tuyển sinh
                        </a>
                        <a href="/public/index.php?action=ts-lien-he" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-headset text-danger me-2"></i>
                            Liên hệ hỗ trợ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Notice -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-triangle-exclamation fa-2x me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Thông báo quan trọng</h6>
                        <p class="mb-0">
                            Thời gian đăng ký nguyện vọng: <strong>01/07/2025 - 31/07/2025</strong>. 
                            Vui lòng hoàn tất đăng ký trước thời hạn để không bỏ lỡ cơ hội.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
