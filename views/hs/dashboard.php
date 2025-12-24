<?php
// filepath: d:\Hk1_2025\PTUD_Nhom4\Đồ Án Nhóm\Learning_System\views\hs\dashboard.php

// Bảo vệ & kiểm tra quyền
require_once __DIR__ . '/../../middlewares/AuthGuard.php';
require_role(['hs']); // ⚠️ THAY ĐỔI: 'hocsinh' → 'hs'

// Lấy thông tin học sinh từ database
require_once __DIR__ . '/../../models/hs/DiemHSModel.php';
require_once __DIR__ . '/../../models/hs/DashboardModel.php';

$diemModel = new DiemModel();
$dashboardModel = new DashboardModel();

// Lấy thông tin user từ session
$user = $_SESSION['auth'] ?? [];
$maHS = null;
if ($user && isset($user['username'])) {
    $maHS = $diemModel->getMaHocSinhByUsername($user['username']);
}

// Lấy thông tin chi tiết học sinh
$thongTinHS = null;
$maLop = null;
if ($maHS) {
    $thongTinHS = $diemModel->getThongTinHocSinh($maHS);
    $maLop = $thongTinHS['maLop'] ?? null;
}

// Gán giá trị cho hiển thị
$fullName = $thongTinHS ? $thongTinHS['hoTen'] : 'Học sinh';
$studentId = $thongTinHS ? $thongTinHS['maHS'] : 'HS0000';
$className = $thongTinHS ? $thongTinHS['tenLop'] : 'Chưa có lớp';

// Tiêu đề trang và header chung
$pageTitle = 'Trang học sinh - THPT';
require_once __DIR__ . '/../layouts/header.php';

// ⚠️ FIX: Lấy dữ liệu THỰC TẾ từ database
$stats = [
    'attendance_rate' => 95.5, // TODO: Tính từ bảng diemdanh khi có
    'gpa_semester' => $maHS ? $dashboardModel->getDiemTrungBinhHocKy($maHS, 'HK1', '2024-2025') : 0,
    'conduct_rating' => $maHS ? $dashboardModel->getHanhKiem($maHS, 'HK1', '2024-2025') : 'Tốt',
    'pending_requests' => $maHS ? $dashboardModel->demDonChoPheDuyet($maHS) : 0,
    'unread_notifications' => 5, // TODO: Đếm từ bảng thongbao khi có
];

// ⚠️ FIX: Lấy dữ liệu THỰC TẾ thay vì demo
$recentGrades = [];
$todaySchedule = [];

if ($maHS) {
    // Lấy điểm gần đây THỰC TẾ
    $recentGrades = $dashboardModel->getDiemGanDay($maHS, 3);
}

if ($maLop) {
    // Lấy lịch học hôm nay THỰC TẾ
    $todaySchedule = $dashboardModel->getLichHocHomNay($maLop);
}

// Thông báo (giữ demo vì chưa có bảng thongbao)
$notifications = [
    ['title' => 'Thông báo nghỉ Tết Nguyên đán', 'type' => 'BGH', 'date' => '2024-03-15', 'unread' => true],
    ['title' => 'Họp phụ huynh học kỳ 2', 'type' => 'GVCN', 'date' => '2024-03-14', 'unread' => true],
    ['title' => 'Nộp học phí tháng 3', 'type' => 'Kế toán', 'date' => '2024-03-10', 'unread' => false],
];
?>

<style>
    .student-dashboard {
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
        box-shadow: 0 8px 24px rgba(17, 153, 142, 0.3);
    }
    
    .stat-card.success:hover {
        box-shadow: 0 12px 32px rgba(17, 153, 142, 0.4);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 8px 24px rgba(245, 87, 108, 0.3);
    }
    
    .stat-card.warning:hover {
        box-shadow: 0 12px 32px rgba(245, 87, 108, 0.4);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 8px 24px rgba(79, 172, 254, 0.3);
    }
    
    .stat-card.info:hover {
        box-shadow: 0 12px 32px rgba(79, 172, 254, 0.4);
    }
    
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stat-card p {
        margin: 0;
        opacity: 0.9;
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
    
    .schedule-item {
        padding: 1rem;
        border-left: 4px solid #667eea;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
        border-radius: 8px;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .schedule-item:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        transform: translateX(5px);
    }
    
    .grade-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 1.2rem;
    }
    
    .grade-excellent {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .grade-good {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .grade-average {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .notification-item {
        padding: 1rem;
        border-radius: 12px;
        background: white;
        border: 1px solid #e9ecef;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .notification-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #667eea, #764ba2);
    }
    
    .notification-item.unread {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, white 100%);
        border-color: #667eea;
        font-weight: 500;
    }
    
    .notification-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .badge-type {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
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
    
    .hover-link {
        transition: all 0.3s ease;
        padding: 0.5rem;
        border-radius: 8px;
    }
    
    .hover-link:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        transform: translateX(5px);
    }
    
    @media (max-width: 767.98px) {
        .stat-card h3 {
            font-size: 2rem;
        }
        .welcome-banner h2 {
            font-size: 1.5rem;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
    }
</style>

<div class="student-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fa-solid fa-hand-wave me-2"></i>
                    Xin chào, <?php echo htmlspecialchars($fullName); ?>!
                </h2>
                <p class="mb-0">
                    <i class="fa-solid fa-id-card me-2"></i>Mã số: <?php echo htmlspecialchars($studentId); ?>
                    <span class="mx-3">|</span>
                    <i class="fa-solid fa-users me-2"></i>Lớp: <?php echo htmlspecialchars(str_replace('Lop ', '', $className)); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/public/index.php?action=hs-profile" class="quick-action-btn btn">
                    <i class="fa-solid fa-user me-2"></i>Hồ sơ cá nhân
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
                        <h3><?php echo $stats['attendance_rate']; ?>%</h3>
                        <p><i class="fa-solid fa-calendar-check me-2"></i>Điểm danh</p>
                    </div>
                    <i class="fa-solid fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['gpa_semester']; ?></h3>
                        <p><i class="fa-solid fa-star me-2"></i>Điểm TB HK</p>
                    </div>
                    <i class="fa-solid fa-trophy fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['pending_requests']; ?></h3>
                        <p><i class="fa-solid fa-file-lines me-2"></i>Đơn chờ duyệt</p>
                    </div>
                    <i class="fa-solid fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="stat-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3><?php echo $stats['unread_notifications']; ?></h3>
                        <p><i class="fa-solid fa-bell me-2"></i>Thông báo mới</p>
                    </div>
                    <i class="fa-solid fa-envelope fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Functions Grid -->
    <div class="row g-4 mb-4">
        <!-- Xem điểm -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fa-solid fa-chart-bar"></i>
                    </div>
                    <h5 class="card-title fw-bold">Bảng điểm</h5>
                    <p class="text-muted small">Xem điểm chi tiết theo môn, kỳ học</p>
                    <a href="/public/index.php?action=hs-xem-diem" class="btn btn-primary w-100 mt-3">
                        <i class="fa-solid fa-eye me-2"></i>Xem điểm
                    </a>
                </div>
            </div>
        </div>

        <!-- Thời khóa biểu -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thời khóa biểu</h5>
                    <p class="text-muted small">Lịch học trong tuần, phòng học</p>
                    <a href="/public/index.php?action=hs-xem-tkb" class="btn btn-success w-100 mt-3">
                        <i class="fa-solid fa-calendar me-2"></i>Xem TKB
                    </a>
                </div>
            </div>
        </div>

        <!-- Đơn xin nghỉ -->

        <!-- Thông báo -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <h5 class="card-title fw-bold">Thông báo</h5>
                    <p class="text-muted small">Tin từ BGH, GVCN, môn học</p>
                    <a href="/public/index.php?action=hs-thong-bao" class="btn btn-info w-100 mt-3">
                        <i class="fa-solid fa-bell me-2"></i>Xem tất cả
                    </a>
                </div>
            </div>
        </div>

        <!-- Hạnh kiểm & Học lực - THÊM CARD MỚI -->
        <div class="col-md-6 col-xl-3">
            <div class="card feature-card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa-solid fa-medal"></i>
                    </div>
                    <h5 class="card-title fw-bold">Hạnh kiểm & Học lực</h5>
                    <p class="text-muted small">Xem hạnh kiểm, học lực các kỳ</p>
                    <a href="/public/index.php?action=hs-hanh-kiem-hoc-luc" class="btn btn-danger w-100 mt-3">
                        <i class="fa-solid fa-award me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Today's Schedule - FIX: Hiển thị dữ liệu thực -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-clock text-primary me-2"></i>
                        Lịch học hôm nay
                    </h5>
                    <?php if (empty($todaySchedule)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-calendar-xmark fa-3x mb-3 d-block"></i>
                            <p>Hôm nay không có lịch học</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todaySchedule as $lesson): ?>
                        <div class="schedule-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-primary">
                                        Tiết <?php echo htmlspecialchars($lesson['period']); ?>: 
                                        <?php echo htmlspecialchars($lesson['subject']); ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fa-solid fa-chalkboard-user me-1"></i>
                                        <?php echo htmlspecialchars($lesson['teacher'] ?? 'Chưa phân công'); ?>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">
                                    <i class="fa-solid fa-door-open me-1"></i>
                                    <?php echo htmlspecialchars($lesson['room'] ?? 'N/A'); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="/public/index.php?action=hs-xem-tkb" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fa-solid fa-calendar-week me-2"></i>Xem lịch tuần
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Grades - FIX: Hiển thị dữ liệu thực -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-award text-warning me-2"></i>
                        Điểm số gần đây
                    </h5>
                    <?php if (empty($recentGrades)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fa-solid fa-chart-simple fa-3x mb-3 d-block"></i>
                            <p>Chưa có điểm nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentGrades as $grade): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($grade['subject']); ?></div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-calendar me-1"></i>
                                    <?php echo htmlspecialchars($grade['date']); ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="grade-badge <?php 
                                    $score = floatval($grade['score']);
                                    echo $score >= 8 ? 'grade-excellent' : 
                                        ($score >= 6.5 ? 'grade-good' : 'grade-average'); 
                                ?>">
                                    <?php echo number_format($score, 1); ?>
                                </div>
                                <div class="small text-muted mt-1"><?php echo htmlspecialchars($grade['type']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="/public/index.php?action=hs-xem-diem" class="btn btn-outline-warning w-100 mt-3">
                        <i class="fa-solid fa-chart-line me-2"></i>Xem tất cả điểm
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">
                        <i class="fa-solid fa-bell text-info me-2"></i>
                        Thông báo mới
                        <?php if ($stats['unread_notifications'] > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </h5>
                    <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo $notif['unread'] ? 'unread' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-semibold mb-1"><?php echo htmlspecialchars($notif['title']); ?></div>
                                <div class="small text-muted">
                                    <span class="badge-type bg-primary text-white me-2">
                                        <?php echo htmlspecialchars($notif['type']); ?>
                                    </span>
                                    <i class="fa-solid fa-clock me-1"></i><?php echo $notif['date']; ?>
                                </div>
                            </div>
                            <?php if ($notif['unread']): ?>
                            <span class="badge bg-danger rounded-circle" style="width: 10px; height: 10px; padding: 0;"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="btn btn-outline-info w-100 mt-3">
                        <i class="fa-solid fa-envelope-open me-2"></i>Xem tất cả
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Quick Links -->
    <div class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-medal text-warning me-2"></i>Xếp loại học lực
                    </h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Học kỳ này:</span>
                        <span class="badge bg-success px-3 py-2">Giỏi</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Hạnh kiểm:</span>
                        <span class="badge bg-info px-3 py-2"><?php echo $stats['conduct_rating']; ?></span>
                    </div>
                    <a href="/public/index.php?action=hs-hanh-kiem-hoc-luc" class="btn btn-outline-warning w-100 mt-3">
                        <i class="fa-solid fa-chart-simple me-2"></i>Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-book text-primary me-2"></i>Học liệu
                    </h6>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="d-block text-decoration-none text-dark mb-2 hover-link">
                        <i class="fa-solid fa-file-pdf text-danger me-2"></i>Tài liệu học tập
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="d-block text-decoration-none text-dark mb-2 hover-link">
                        <i class="fa-solid fa-file-lines text-warning me-2"></i>Đề thi - Đáp án
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="d-block text-decoration-none text-dark hover-link">
                        <i class="fa-solid fa-book-open text-success me-2"></i>Thư viện
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card feature-card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-headset text-success me-2"></i>Hỗ trợ
                    </h6>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="d-block text-decoration-none text-dark mb-2 hover-link">
                        <i class="fa-solid fa-comments text-primary me-2"></i>Liên hệ GVCN
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="d-block text-decoration-none text-dark mb-2 hover-link">
                        <i class="fa-solid fa-message text-info me-2"></i>Góp ý, phản hồi
                    </a>
                    <a href="#" onclick="alert('Chức năng đang phát triển'); return false;" class="d-block text-decoration-none text-dark hover-link">
                        <i class="fa-solid fa-circle-question text-warning me-2"></i>Câu hỏi thường gặp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>